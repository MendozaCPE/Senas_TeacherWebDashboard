<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Gesture;
use App\Models\GestureMedia;
use App\Models\GestureModule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * AdminMediaController — System Media management for Admins.
 *
 * "System Media" = files in the `gesture_media` table, stored at
 *   storage/app/public/sign_language_media/{Category}/{filename}
 *
 * STRICT SEPARATION (enforced at backend / query level):
 *   - Every query touches ONLY the `gesture_media` table.
 *   - `teacher_media` is NEVER queried here, not even read.
 *   - The admin middleware stack blocks teachers from reaching any route.
 *
 * File-replace strategy (preserving original filename):
 *   When the Admin replaces a file, the new file is stored at the exact
 *   same `file_path` as the old one. Because lesson_contents / gesture
 *   records reference the path, they automatically serve the new content.
 */
class AdminMediaController extends Controller
{
    // ── Index ─────────────────────────────────────────────────────────────────

    /**
     * GET /admin/media
     * Display ALL system (gesture) media for the Admin.
     */
    public function index(Request $request)
    {
        // Load ONLY gesture_media — never teacher_media
        $query = GestureMedia::with(['gesture.module', 'module'])
            ->orderBy('order')
            ->orderBy('media_id');

        // Optional search
        $search = trim($request->input('search', ''));
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('file_name', 'like', "%{$search}%")
                  ->orWhereHas('gesture', fn ($g) =>
                      $g->where('display_name', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                  )
                  ->orWhereHas('module', fn ($m) =>
                      $m->where('display_name', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                  );
            });
        }

        // Type filter
        $type = $request->input('type', 'all');
        if ($type !== 'all') {
            if ($type === 'gif') {
                $query->where('mime_type', 'image/gif');
            } else {
                $query->where('media_type', $type);
            }
        }

        $allMedia = $query->get();

        // Sort
        $sort = $request->input('sort', 'newest');
        $allMedia = match ($sort) {
            'oldest' => $allMedia->sortBy('created_at')->values(),
            'alpha'  => $allMedia->sortBy(fn ($m) =>
                            $m->gesture ? ($m->gesture->display_name ?? $m->gesture->name) : $m->file_name
                        )->values(),
            default  => $allMedia->sortByDesc('created_at')->values(),
        };

        // Stats
        $stats = [
            'total'  => $allMedia->count(),
            'images' => $allMedia->where('media_type', 'image')->count(),
            'videos' => $allMedia->where('media_type', 'video')->count(),
            'gifs'   => $allMedia->filter(fn ($m) => $m->mime_type === 'image/gif')->count(),
        ];

        // Load modules for the module filter dropdown
        $modules = GestureModule::ordered()->get();

        // JS-safe payload — mirrors the shape used by the teacher media view
        $mediaJs = $allMedia->values()->map(function ($item, $index) {
            $mediaType = $item->mime_type === 'image/gif' ? 'gif' : $item->media_type;

            // Build display title — priority:
            // 1. gesture_media.display_name (Admin set this explicitly)
            // 2. gesture.display_name (from gesture record)
            // 3. prettify gesture.name (e.g. "TWO" → "Two")
            // 4. prettify file_name (e.g. "28_Two.mp4" → "28 Two")
            if ($item->display_name) {
                $displayTitle = $item->display_name;
            } elseif ($item->gesture && $item->gesture->display_name) {
                $displayTitle = $item->gesture->display_name;
            } elseif ($item->gesture) {
                $displayTitle = $this->prettifyFilename($item->gesture->name);
            } else {
                $displayTitle = $this->prettifyFilename($item->file_name);
            }

            return [
                'index'      => $index,
                'id'         => 'sys_' . $item->media_id,
                'raw_id'     => $item->media_id,
                'source'     => 'system',
                'title'      => $displayTitle,
                'file_name'  => $item->file_name,
                'file_path'  => $item->file_path,
                'url'        => asset('storage/' . $item->file_path) . '?v=' . ($item->updated_at ? $item->updated_at->timestamp : time()),
                'media_type' => $mediaType,
                'mime_type'  => $item->mime_type,
                'module'     => $item->module
                                    ? ($item->module->display_name ?? $item->module->name)
                                    : ($item->gesture && $item->gesture->module
                                        ? ($item->gesture->module->display_name ?? $item->gesture->module->name)
                                        : null),
                'owner'      => 'System',
                'created_at' => $item->created_at
                                    ? $item->created_at->format('M j, Y')
                                    : null,
                'description' => $item->gesture ? ($item->gesture->description ?? '') : '',
                'is_primary'  => (bool) $item->is_primary,
            ];
        })->values()->toArray();

        return view('admin.media', compact(
            'allMedia', 'search', 'type', 'sort', 'stats', 'mediaJs', 'modules'
        ));
    }

    // ── Upload ────────────────────────────────────────────────────────────────

    /**
     * POST /admin/media/upload
     *
     * Upload a new system media file.
     * The admin provides a title and optionally picks a module/folder.
     * A gesture_media record is created without a gesture_id (nullable),
     * OR linked to an existing gesture if gesture_id is supplied.
     * File is stored in sign_language_media/{folder}/.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file'       => 'required|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,mkv|max:204800',
            'title'      => 'required|string|max:255',
            'folder'     => 'nullable|string|max:100|regex:/^[a-zA-Z0-9_ -]+$/',
            'gesture_id' => 'nullable|integer|exists:gestures,gesture_id',
            'is_primary' => 'nullable|boolean',
        ]);

        $file      = $request->file('file');
        $mimeType  = $file->getMimeType();
        $ext       = strtolower($file->getClientOriginalExtension());
        $mediaType = $this->detectMediaType($mimeType, $ext);

        // Determine storage subfolder
        $folder = $request->input('folder', 'Alphabets');

        // Build filename from title + original extension
        // e.g. title "Good Morning" + .mp4 → Good_Morning.mp4
        $ext      = strtolower($file->getClientOriginalExtension());
        $slug     = preg_replace('/[^a-zA-Z0-9._-]/', '_', trim($request->input('title')));
        $slug     = trim($slug, '_');
        $filename = $slug . '.' . $ext;

        $path = $file->storeAs("sign_language_media/{$folder}", $filename, 'public');

        // Optionally link to a gesture
        $gestureId = $request->filled('gesture_id') ? $request->integer('gesture_id') : null;
        $moduleId  = null;

        if ($gestureId) {
            $gesture  = Gesture::with('module')->find($gestureId);
            $moduleId = $gesture?->module_id;

            $isPrimary = $request->boolean('is_primary', false);
            if ($isPrimary && $mediaType === 'image') {
                GestureMedia::where('gesture_id', $gestureId)
                    ->where('media_type', 'image')
                    ->update(['is_primary' => false]);
            }
        } else {
            $isPrimary = false;
        }

        $maxOrder = $gestureId
            ? (GestureMedia::where('gesture_id', $gestureId)->max('order') ?? 0)
            : 0;

        $media = GestureMedia::create([
            'gesture_id' => $gestureId,
            'module_id'  => $moduleId,
            'media_type' => $mediaType === 'gif' ? 'image' : $mediaType,
            'file_path'  => $path,
            'file_name'  => $filename,
            'mime_type'  => $mimeType,
            'file_size'  => $file->getSize(),
            'is_primary' => $isPrimary,
            'order'      => $maxOrder + 1,
        ]);

        $title = $request->input('title');

        AuditLog::record(
            action: 'system_media_uploaded',
            module: 'system_media',
            description: "Admin uploaded System Media: \"{$title}\" ({$media->file_name}) in folder {$folder}",
            userId: Auth::id(),
            userName: Auth::user()->name,
            userRole: Auth::user()->role,
            subjectType: GestureMedia::class,
            subjectId: $media->media_id,
            newValues: [
                'title'      => $title,
                'file_name'  => $media->file_name,
                'file_path'  => $media->file_path,
                'media_type' => $media->media_type,
                'file_size'  => $media->file_size,
                'folder'     => $folder,
                'gesture_id' => $gestureId,
            ],
        );

        return response()->json([
            'success'    => true,
            'media_id'   => $media->media_id,
            'url'        => asset('storage/' . $path),
            'media_type' => $mediaType,
            'file_name'  => $media->file_name,
        ]);
    }

    // ── Edit metadata ─────────────────────────────────────────────────────────

    /**
     * POST /admin/media/{id}/edit
     * Update display name, description, and is_primary for a gesture_media record.
     * If the record is linked to a gesture, updates the gesture's display_name/description.
     * For standalone media (no gesture), updates file_name display via a stored title.
     */
    public function edit(Request $request, int $id): JsonResponse
    {
        $media = GestureMedia::with('gesture')->where('media_id', $id)->firstOrFail();

        $request->validate([
            'display_name' => 'required|string|max:255',
            'description'  => 'nullable|string|max:1000',
            'is_primary'   => 'nullable|boolean',
        ]);

        $oldValues = [
            'display_name' => $media->display_name,
            'is_primary'   => $media->is_primary,
        ];

        // Save directly to gesture_media.display_name — persists independently
        $media->display_name = $request->input('display_name');
        $media->is_primary   = $request->boolean('is_primary', $media->is_primary);
        $media->save();

        AuditLog::record(
            action: 'system_media_edited',
            module: 'system_media',
            description: "Admin edited System Media: \"{$media->file_name}\" — display name set to \"{$media->display_name}\"",
            userId: Auth::id(),
            userName: Auth::user()->name,
            userRole: Auth::user()->role,
            subjectType: GestureMedia::class,
            subjectId: $media->media_id,
            oldValues: $oldValues,
            newValues: [
                'display_name' => $media->display_name,
                'is_primary'   => $media->is_primary,
            ],
        );

        return response()->json(['success' => true]);
    }

    /**
     * POST /admin/media/{id}/stage
     *
     * Step 1: Upload the replacement file to a TEMPORARY path.
     * Nothing is changed yet — the original file is completely untouched.
     * Returns a temp_key the client sends back on confirm.
     */
    public function stageReplace(Request $request, int $id): JsonResponse
    {
        $media = GestureMedia::where('media_id', $id)->firstOrFail();

        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,mkv|max:204800',
        ]);

        $file     = $request->file('file');
        $tempKey  = 'replace_' . $id . '_' . uniqid();
        $tempPath = 'system_media_tmp/' . $tempKey . '.' . strtolower($file->getClientOriginalExtension());

        // Store in a private temp area (not public) — safe, not accessible via URL
        Storage::disk('local')->putFileAs(
            'system_media_tmp',
            $file,
            basename($tempPath)
        );

        return response()->json([
            'success'       => true,
            'temp_key'      => $tempKey,
            'original_name' => $media->file_name,
            'original_path' => $media->file_path,
            'new_size'      => $file->getSize(),
            'new_mime'      => $file->getMimeType(),
        ]);
    }

    // ── Confirm replace (step 2 of 2) ────────────────────────────────────────

    /**
     * POST /admin/media/{id}/confirm-replace
     *
     * Step 2 (after admin confirms the warning dialog):
     *   1. Move temp file → exact original file_path (atomic rename where possible)
     *   2. If move fails, clean up temp and return error — original untouched
     *   3. Update mime/size in DB, keep file_name & file_path unchanged
     *   4. Write audit log
     */
    public function confirmReplace(Request $request, int $id): JsonResponse
    {
        $media = GestureMedia::where('media_id', $id)->firstOrFail();

        $request->validate([
            'temp_key' => 'required|string|max:120',
        ]);

        $tempKey  = $request->input('temp_key');
        // Find the temp file — extension may vary, so glob for it
        $tmpFiles = Storage::disk('local')->files('system_media_tmp');
        $tempPath = null;
        foreach ($tmpFiles as $f) {
            if (str_starts_with(basename($f), $tempKey)) {
                $tempPath = $f;
                break;
            }
        }

        if (! $tempPath || ! Storage::disk('local')->exists($tempPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Staged file not found or expired. Please try uploading again.',
            ], 422);
        }

        $oldValues = [
            'file_name' => $media->file_name,
            'file_path' => $media->file_path,
            'file_size' => $media->file_size,
            'mime_type' => $media->mime_type,
        ];

        try {
            // Read the staged file contents
            $contents = Storage::disk('local')->get($tempPath);
            $newMime  = Storage::disk('local')->mimeType($tempPath);
            $newSize  = Storage::disk('local')->size($tempPath);

            // Delete the old public file
            Storage::disk('public')->delete($media->file_path);

            // Write the new file at the EXACT same public path (same filename)
            $written = Storage::disk('public')->put($media->file_path, $contents);

            if (! $written) {
                throw new \RuntimeException('Failed to write replacement file to storage.');
            }

            // Verify the file actually exists now
            if (! Storage::disk('public')->exists($media->file_path)) {
                throw new \RuntimeException('Replacement file was written but cannot be verified at the original path.');
            }

            // Update DB — only size & mime change; file_name and file_path stay identical
            $media->mime_type   = $newMime;
            $media->file_size   = $newSize;
            $media->updated_at  = now();   // bump timestamp → new ?v= cache buster on next load
            $media->save();

            // Clean up temp file
            Storage::disk('local')->delete($tempPath);

        } catch (\Throwable $e) {
            // Clean up temp on any failure
            Storage::disk('local')->delete($tempPath);

            return response()->json([
                'success' => false,
                'message' => 'Replace failed: ' . $e->getMessage() . '. The original file was not modified.',
            ], 500);
        }

        AuditLog::record(
            action: 'system_media_replaced',
            module: 'system_media',
            description: "Admin replaced System Media: \"{$media->file_name}\" — original filename preserved, lessons auto-update",
            userId: Auth::id(),
            userName: Auth::user()->name,
            userRole: Auth::user()->role,
            subjectType: GestureMedia::class,
            subjectId: $media->media_id,
            oldValues: $oldValues,
            newValues: [
                'file_name' => $media->file_name,
                'file_path' => $media->file_path,
                'file_size' => $media->file_size,
                'mime_type' => $media->mime_type,
            ],
        );

        return response()->json([
            'success'   => true,
            'url'       => asset('storage/' . $media->file_path),
            'file_name' => $media->file_name,
        ]);
    }

    // ── Cancel staged replace ─────────────────────────────────────────────────

    /**
     * POST /admin/media/{id}/cancel-replace
     * Admin cancelled after uploading — clean up the temp file.
     */
    public function cancelReplace(Request $request, int $id): JsonResponse
    {
        $request->validate(['temp_key' => 'required|string|max:120']);
        $tempKey = $request->input('temp_key');

        $tmpFiles = Storage::disk('local')->files('system_media_tmp');
        foreach ($tmpFiles as $f) {
            if (str_starts_with(basename($f), $tempKey)) {
                Storage::disk('local')->delete($f);
                break;
            }
        }

        return response()->json(['success' => true]);
    }

    // ── Update (legacy, kept for compatibility) ───────────────────────────────

    /**
     * POST /admin/media/{id}
     * Thin wrapper — now delegates to the two-step stage/confirm flow.
     * Kept so any existing direct POST still works (returns a helpful message).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Please use the Replace button which now requires confirmation.',
        ], 422);
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    /**
     * DELETE /admin/media/{id}
     *
     * Safe delete: if the gesture still has other media records, removes only
     * this record. If it's the LAST media for the gesture, warns the Admin.
     * Pass ?force=true to override.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $media = GestureMedia::with('gesture')->where('media_id', $id)->firstOrFail();

        // Count remaining media for this gesture
        $siblingsCount = GestureMedia::where('gesture_id', $media->gesture_id)
            ->where('media_id', '!=', $id)
            ->count();

        $force = $request->boolean('force', false);

        if ($siblingsCount === 0 && ! $force) {
            $gestureName = $media->gesture
                ? ($media->gesture->display_name ?? $media->gesture->name)
                : $media->file_name;

            return response()->json([
                'success'  => false,
                'blocked'  => true,
                'message'  => "This is the only media for gesture \"{$gestureName}\". "
                            . "Deleting it will leave the gesture without any media. "
                            . "Pass force=true to delete anyway.",
            ], 422);
        }

        $gestureLabel = $media->gesture
            ? ($media->gesture->display_name ?? $media->gesture->name)
            : $media->file_name;

        AuditLog::record(
            action: 'system_media_deleted',
            module: 'system_media',
            description: "Admin deleted System Media: \"{$media->file_name}\""
                       . ($siblingsCount === 0 ? " — WARNING: last media for this gesture" : ''),
            userId: Auth::id(),
            userName: Auth::user()->name,
            userRole: Auth::user()->role,
            subjectType: GestureMedia::class,
            subjectId: $media->media_id,
            oldValues: [
                'file_name'  => $media->file_name,
                'file_path'  => $media->file_path,
                'media_type' => $media->media_type,
                'gesture'    => $gestureLabel,
            ],
        );

        Storage::disk('public')->delete($media->file_path);
        $media->delete();

        return response()->json(['success' => true]);
    }

    // ── AJAX helpers ──────────────────────────────────────────────────────────

    /**
     * GET /admin/media/{id}/info
     * Returns a single gesture_media record as JSON (for the edit modal).
     */
    public function info(int $id): JsonResponse
    {
        $media = GestureMedia::with('gesture.module', 'module')
            ->where('media_id', $id)
            ->firstOrFail();

        return response()->json([
            'media_id'   => $media->media_id,
            'file_name'  => $media->file_name,
            'file_path'  => $media->file_path,
            'url'        => asset('storage/' . $media->file_path),
            'media_type' => $media->media_type,
            'mime_type'  => $media->mime_type,
            'file_size'  => $media->file_size,
            'is_primary' => $media->is_primary,
            'gesture'    => $media->gesture
                ? [
                    'gesture_id'   => $media->gesture->gesture_id,
                    'name'         => $media->gesture->display_name ?? $media->gesture->name,
                    'module_name'  => $media->gesture->module
                                        ? ($media->gesture->module->display_name ?? $media->gesture->module->name)
                                        : null,
                  ]
                : null,
        ]);
    }

    /**
     * GET /admin/api/gestures
     * Returns all gestures (with module) for the upload-modal gesture selector.
     */
    public function gestures(Request $request): JsonResponse
    {
        $gestures = Gesture::with('module')
            ->orderBy('module_id')
            ->orderBy('name')
            ->get()
            ->map(function ($g) {
                return [
                    'gesture_id'  => $g->gesture_id,
                    'name'        => $g->display_name ?? $g->name,
                    'module_id'   => $g->module_id,
                    'module_name' => $g->module
                                        ? ($g->module->display_name ?? $g->module->name)
                                        : null,
                ];
            });

        return response()->json(['gestures' => $gestures]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function detectMediaType(string $mimeType, string $ext): string
    {
        if ($mimeType === 'image/gif' || $ext === 'gif') return 'gif';
        if (str_starts_with($mimeType, 'video/') || in_array($ext, ['mp4', 'mov', 'avi', 'mkv'])) return 'video';
        return 'image';
    }

    /**
     * Convert a raw filename into a clean human-readable display title.
     *
     * Rules (presentation only — actual file is never touched):
     *   1. Strip the file extension
     *   2. Replace underscores and hyphens with spaces
     *   3. Apply Title Case
     *
     * Examples:
     *   four.mp4              → Four
     *   hello_world.mp4       → Hello World
     *   sign-language-basic   → Sign Language Basic
     *   basic_sign_language   → Basic Sign Language
     */
    private function prettifyFilename(string $name): string
    {
        // Remove extension (anything after the last dot)
        $withoutExt = preg_replace('/\.[^.]+$/', '', $name);

        // Replace underscores and hyphens with spaces
        $spaced = str_replace(['_', '-'], ' ', $withoutExt);

        // Collapse multiple spaces
        $clean = preg_replace('/\s+/', ' ', trim($spaced));

        // Title Case
        return ucwords(strtolower($clean));
    }

    /**
     * Map a GestureModule to the physical subfolder name used in sign_language_media/.
     */
    private function moduleFolderName(?GestureModule $module): string
    {
        if (! $module) return 'Alphabets';

        return match (true) {
            str_contains(strtolower($module->name ?? ''), 'number') => 'Numbers',
            str_contains(strtolower($module->name ?? ''), 'greeting') => 'Greetings',
            str_contains(strtolower($module->name ?? ''), 'survival') => 'Survival',
            default => 'Alphabets',
        };
    }
}
