<?php

namespace App\Http\Controllers;

use App\Models\GestureMedia;
use App\Models\GestureModule;
use App\Models\Teacher;
use App\Models\TeacherMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    // ── Helpers ───────────────────────────────────────────────────────────────

    private function resolveTeacher(): ?Teacher
    {
        $user = Auth::user();
        return $user ? $user->teacher : null;
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    /**
     * GET /media
     * Display the combined media gallery page.
     */
    public function index(Request $request)
    {
        $teacher = $this->resolveTeacher();

        // ── 1. System media from gesture_media table ──────────────────────────
        $systemMediaQuery = GestureMedia::with(['gesture', 'module'])
            ->orderBy('order')
            ->orderBy('media_id');

        // ── 2. Teacher uploads — ONLY the current teacher's own uploads ─────────
        // Never expose another teacher's uploads.
        if (! $teacher) {
            // No teacher profile linked — show nothing in uploads section
            $uploadedMediaQuery = TeacherMedia::where('teacher_id', 0); // returns empty
        } else {
            $uploadedMediaQuery = TeacherMedia::with('teacher.user')
                ->where('teacher_id', $teacher->id)
                ->orderBy('created_at', 'desc');
        }

        // Apply search filter
        $search = $request->input('search', '');
        if ($search) {
            $systemMediaQuery->where(function ($q) use ($search) {
                $q->where('file_name', 'like', "%{$search}%")
                  ->orWhereHas('gesture', fn ($g) => $g->where('display_name', 'like', "%{$search}%")
                                                        ->orWhere('name', 'like', "%{$search}%"));
            });
            $uploadedMediaQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('file_name', 'like', "%{$search}%");
            });
        }

        // Apply type filter
        $type = $request->input('type', 'all');   // all | image | video | gif
        if ($type !== 'all') {
            if ($type === 'gif') {
                // GIFs are images stored with mime_type = image/gif
                $systemMediaQuery->where('mime_type', 'image/gif');
            } else {
                $systemMediaQuery->where('media_type', $type);
            }
            $uploadedMediaQuery->where('media_type', $type);
        }

        // Apply source filter
        $source = $request->input('source', 'all');  // all | system | uploaded

        $systemMedia   = $source !== 'uploaded' ? $systemMediaQuery->get() : collect();
        $uploadedMedia = $source !== 'system'   ? $uploadedMediaQuery->get() : collect();

        // Sort
        $sort = $request->input('sort', 'newest');

        // ── Build unified collection ──────────────────────────────────────────
        $systemItems = $systemMedia->map(function ($item) {
            return [
                'id'         => 'sys_' . $item->media_id,
                'source'     => 'system',
                'title'      => $item->gesture ? ($item->gesture->display_name ?? $item->gesture->name) : $item->file_name,
                'file_name'  => $item->file_name,
                'file_path'  => $item->file_path,
                'url'        => asset('storage/' . $item->file_path),
                'media_type' => $this->resolveMediaType($item->mime_type, $item->media_type),
                'mime_type'  => $item->mime_type,
                'file_size'  => $item->file_size,
                'module'     => $item->module ? $item->module->display_name : null,
                'owner'      => 'System',
                'created_at' => $item->created_at,
                'raw'        => $item,
            ];
        });

        $uploadedItems = $uploadedMedia->map(function ($item) {
            return [
                'id'         => 'upl_' . $item->media_id,
                'source'     => 'uploaded',
                'title'      => $item->title,
                'file_name'  => $item->file_name,
                'file_path'  => $item->file_path,
                'url'        => asset('storage/' . $item->file_path),
                'media_type' => $item->media_type,
                'mime_type'  => $item->mime_type,
                'file_size'  => $item->file_size,
                'module'     => null,
                'owner'      => $item->teacher && $item->teacher->user
                                    ? $item->teacher->user->name
                                    : 'Teacher',
                'created_at' => $item->created_at,
                'raw'        => $item,
            ];
        });

        $allMedia = $systemItems->concat($uploadedItems);

        // Sort combined collection
        switch ($sort) {
            case 'oldest':
                $allMedia = $allMedia->sortBy('created_at')->values();
                break;
            case 'alpha':
                $allMedia = $allMedia->sortBy('title')->values();
                break;
            default: // newest
                $allMedia = $allMedia->sortByDesc('created_at')->values();
        }

        // Stats
        $stats = [
            'total'    => $allMedia->count(),
            'system'   => $systemItems->count(),
            'uploaded' => $uploadedItems->count(),
            'images'   => $allMedia->where('media_type', 'image')->count(),
            'videos'   => $allMedia->where('media_type', 'video')->count(),
            'gifs'     => $allMedia->where('media_type', 'gif')->count(),
        ];

        // ── Build JS-safe data for preview navigator ──────────────────────────
        $mediaJs = $allMedia->values()->map(function ($item, $index) {
            return [
                'index'       => $index,
                'id'          => $item['id'],
                'source'      => $item['source'],
                'title'       => $item['title'],
                'file_name'   => $item['file_name'],
                'url'         => $item['url'],
                'media_type'  => $item['media_type'],
                'mime_type'   => $item['mime_type'],
                'module'      => $item['module'],
                'owner'       => $item['owner'],
                'created_at'  => $item['created_at']
                                    ? \Carbon\Carbon::parse($item['created_at'])->format('M j, Y')
                                    : null,
                'description' => $item['source'] === 'uploaded'
                                    ? ($item['raw']->description ?? '')
                                    : '',
                'raw_id'      => $item['source'] === 'uploaded'
                                    ? $item['raw']->media_id
                                    : null,
            ];
        })->values()->toArray();

        return view('media', compact(
            'allMedia',
            'systemItems',
            'uploadedItems',
            'teacher',
            'search',
            'type',
            'source',
            'sort',
            'stats',
            'mediaJs'
        ));
    }

    // ── Upload ────────────────────────────────────────────────────────────────

    /**
     * POST /media/upload
     * Upload a new teacher media file.
     */
    public function upload(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (! $teacher) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'file'        => 'required|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,mkv|max:102400',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $file      = $request->file('file');
        $mimeType  = $file->getMimeType();
        $mediaType = $this->detectMediaType($mimeType, $file->getClientOriginalExtension());

        // Store under uploaded_media/
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
        $filename = time() . '_' . uniqid() . '_' . $safeName;
        $path     = $file->storeAs('uploaded_media', $filename, 'public');

        $media = TeacherMedia::create([
            'teacher_id'  => $teacher->id,
            'title'       => $request->input('title'),
            'file_name'   => $file->getClientOriginalName(),
            'file_path'   => $path,
            'mime_type'   => $mimeType,
            'file_size'   => $file->getSize(),
            'media_type'  => $mediaType,
            'description' => $request->input('description'),
        ]);

        return response()->json([
            'success'    => true,
            'media_id'   => $media->media_id,
            'url'        => asset('storage/' . $path),
            'media_type' => $mediaType,
            'title'      => $media->title,
        ]);
    }

    // ── Update ────────────────────────────────────────────────────────────────

    /**
     * PUT /media/{id}
     * Update title / description of an uploaded media item.
     */
    public function update(Request $request, int $id)
    {
        $teacher = $this->resolveTeacher();
        $media   = TeacherMedia::where('media_id', $id)
                               ->where('teacher_id', $teacher?->id)
                               ->firstOrFail();

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        // Optionally replace the file
        if ($request->hasFile('file')) {
            $request->validate([
                'file' => 'file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,mkv|max:102400',
            ]);

            // Delete old file
            Storage::disk('public')->delete($media->file_path);

            $file     = $request->file('file');
            $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
            $filename = time() . '_' . uniqid() . '_' . $safeName;
            $path     = $file->storeAs('uploaded_media', $filename, 'public');

            $media->file_name  = $file->getClientOriginalName();
            $media->file_path  = $path;
            $media->mime_type  = $file->getMimeType();
            $media->file_size  = $file->getSize();
            $media->media_type = $this->detectMediaType($file->getMimeType(), $file->getClientOriginalExtension());
        }

        $media->title       = $request->input('title');
        $media->description = $request->input('description');
        $media->save();

        return response()->json([
            'success' => true,
            'url'     => asset('storage/' . $media->file_path),
            'title'   => $media->title,
        ]);
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    /**
     * DELETE /media/{id}
     * Delete an uploaded media item belonging to the current teacher.
     */
    public function destroy(int $id)
    {
        $teacher = $this->resolveTeacher();
        $media   = TeacherMedia::where('media_id', $id)
                               ->where('teacher_id', $teacher?->id)
                               ->firstOrFail();

        Storage::disk('public')->delete($media->file_path);
        $media->delete();

        return response()->json(['success' => true]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function detectMediaType(string $mimeType, string $ext): string
    {
        $ext = strtolower($ext);
        if ($mimeType === 'image/gif' || $ext === 'gif') {
            return 'gif';
        }
        if (str_starts_with($mimeType, 'video/') || in_array($ext, ['mp4', 'mov', 'avi', 'mkv'])) {
            return 'video';
        }
        return 'image';
    }

    private function resolveMediaType(?string $mimeType, string $dbType): string
    {
        if ($mimeType === 'image/gif') {
            return 'gif';
        }
        return $dbType; // 'image' or 'video' as stored in gesture_media
    }
}
