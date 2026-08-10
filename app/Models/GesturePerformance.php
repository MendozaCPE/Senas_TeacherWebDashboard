<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GesturePerformance extends Model
{
    use HasFactory;

    protected $primaryKey = 'performance_id';
    
    protected $fillable = [
        'student_id',
        'gesture_id',
        'module_id',
        'attempts',
        'successful_attempts',
        'wrong_attempts',
        'consecutive_wrong',
        'first_attempt_at',
        'last_attempt_at',
        'mastered_at',
        'is_mastered',
        'mastery_level',
        'session_id',
    ];

    protected $casts = [
        'is_mastered' => 'boolean',
        'first_attempt_at' => 'datetime',
        'last_attempt_at' => 'datetime',
        'mastered_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function gesture()
    {
        return $this->belongsTo(Gesture::class, 'gesture_id', 'gesture_id');
    }

    public function module()
    {
        return $this->belongsTo(GestureModule::class, 'module_id', 'module_id');
    }

public static function updateOrCreatePerformance(
    int $studentId,
    int $gestureId,
    int $moduleId,
    array $data
) {
    $performance = self::firstOrNew([
        'student_id' => $studentId,
        'gesture_id' => $gestureId,
    ]);

    if (!$performance->module_id) {
        $performance->module_id = $moduleId;
    }

    // ✅ ADD to existing values
    $performance->attempts += $data['attempts'] ?? 0;
    $performance->successful_attempts += $data['successful_attempts'] ?? 0;
    $performance->wrong_attempts += $data['wrong_attempts'] ?? 0;
    
    // 🔥 FIX: Ensure attempts = successful + wrong
    // This is more reliable than the max() approach
    $totalFromParts = $performance->successful_attempts + $performance->wrong_attempts;
    if ($performance->attempts < $totalFromParts) {
        // If attempts is less than the sum, use the sum
        $performance->attempts = $totalFromParts;
    } elseif ($performance->attempts > $totalFromParts) {
        // If attempts is greater, keep it but also update wrong_attempts
        // This usually means wrong_attempts wasn't tracked properly
        $performance->wrong_attempts = $performance->attempts - $performance->successful_attempts;
    }

    // Set first attempt time if not set
    if (!$performance->first_attempt_at && $performance->attempts > 0) {
        $performance->first_attempt_at = now();
    }

    // Update last attempt time
    if ($performance->attempts > 0) {
        $performance->last_attempt_at = now();
    }

    // Update mastery level based on performance
    $performance->updateMasteryLevel();

    // If mastered, set mastered_at
    if ($performance->is_mastered && !$performance->mastered_at) {
        $performance->mastered_at = now();
    }

    // Set session ID if provided
    if (isset($data['session_id'])) {
        $performance->session_id = $data['session_id'];
    }

    $performance->save();
    return $performance;
}
    /**
 * Update mastery level based on performance metrics
 * 🔥 UPDATED: More forgiving criteria for unlocking
 */
public function updateMasteryLevel()
{
    if ($this->attempts < 1) {
        $this->mastery_level = 'needs_practice';
        $this->is_mastered = false;
        return $this;
    }

    $successRate = $this->attempts > 0 
        ? $this->successful_attempts / $this->attempts 
        : 0;

    // ✅ NEW: More forgiving thresholds for Alphabet mastery
    // The system tracks WRONG attempts too, so we reward accuracy
    if ($successRate >= 0.30 && $this->successful_attempts >= 2) {
        $this->is_mastered = true;
        $this->mastered_at = $this->mastered_at ?? now();
        $this->mastery_level = 'mastered';
    } elseif ($successRate >= 0.15 && $this->successful_attempts >= 1) {
        $this->mastery_level = 'proficient';
        $this->is_mastered = false;
    } elseif ($successRate >= 0.05 && $this->attempts >= 1) {
        $this->mastery_level = 'developing';
        $this->is_mastered = false;
    } else {
        $this->mastery_level = 'needs_practice';
        $this->is_mastered = false;
    }

    return $this;
}
    /**
     * Get struggling letters for a student
     */
    public static function getStrugglingLetters($studentId, $moduleId = null)
    {
        $query = self::where('student_id', $studentId)
                    ->where('mastery_level', 'needs_practice')
                    ->orderBy('wrong_attempts', 'desc');
        
        if ($moduleId) {
            $query->where('module_id', $moduleId);
        }
        
        return $query->with('gesture')->get();
    }

    /**
     * Get mastered letters for a student
     */
    public static function getMasteredLetters($studentId, $moduleId = null)
    {
        $query = self::where('student_id', $studentId)
                    ->where('is_mastered', true);
        
        if ($moduleId) {
            $query->where('module_id', $moduleId);
        }
        
        return $query->with('gesture')->get();
    }

    /**
     * Get module progress summary for a student
     */
    public static function getModuleProgress($studentId, $moduleId)
    {
        $performances = self::where('student_id', $studentId)
                           ->where('module_id', $moduleId)
                           ->get();

        $totalGestures = $performances->count();
        $mastered = $performances->where('is_mastered', true)->count();
        $struggling = $performances->where('mastery_level', 'needs_practice')->count();

        return [
            'total_gestures' => $totalGestures,
            'mastered' => $mastered,
            'struggling' => $struggling,
            'mastery_percentage' => $totalGestures > 0 
                ? round(($mastered / $totalGestures) * 100) 
                : 0,
            'performances' => $performances,
        ];
    }
}