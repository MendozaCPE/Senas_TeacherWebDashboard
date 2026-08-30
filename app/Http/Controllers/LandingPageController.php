<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Student;
use App\Models\StudentRating;
use App\Models\Teacher;
use App\Models\TeacherRating;
use App\Models\StudentLessonProgress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class LandingPageController extends Controller
{
    public function index()
    {
        $stats = Cache::remember('landing_page_stats', 300, function () {
            return $this->getRealTimeStats();
        });

        // Ratings are intentionally NOT cached so new submissions appear promptly
        $ratingsData = $this->getRatingsData();

        return view('landing.landing', array_merge($stats, $ratingsData));
    }

    private function getRealTimeStats(): array
    {
        // ─── TOTAL STUDENTS ─────────────────────────────────────────────
        $totalStudents = Student::where('status', 'active')->count();

        // ─── TOTAL LESSONS ──────────────────────────────────────────────
        $totalLessons = Lesson::where('status', 'published')
            ->whereNull('deleted_at')
            ->count();

        // ─── GESTURE ACCURACY ──────────────────────────────────────────
        $gestureStats = DB::table('gesture_performances')
            ->select(
                DB::raw('SUM(attempts) as total_attempts'),
                DB::raw('SUM(successful_attempts) as total_successful')
            )
            ->first();

        $accuracy = 0;
        if ($gestureStats && $gestureStats->total_attempts > 0) {
            $accuracy = round(($gestureStats->total_successful / $gestureStats->total_attempts) * 100);
        }

        // ─── ACTIVE LEARNERS (last 7 days) ─────────────────────────────
        $activeLearners = Student::where('status', 'active')
            ->where('last_activity_date', '>=', now()->subDays(7))
            ->count();

        // ─── STUDENT ENGAGEMENT ──────────────────────────────────────────
        $studentsWithProgress = StudentLessonProgress::whereIn('student_id', function($query) {
                $query->select('student_id')
                    ->from('students')
                    ->where('status', 'active');
            })
            ->where('lesson_completed', 1)
            ->distinct('student_id')
            ->count('student_id');
        
        $studentEngagement = $totalStudents > 0 
            ? round(($studentsWithProgress / $totalStudents) * 100)
            : 0;

        // ─── TOTAL TEACHERS ─────────────────────────────────────────────
        $totalTeachers = Teacher::count();

        // ─── TOTAL LESSONS COMPLETED ────────────────────────────────────
        $totalLessonsCompleted = StudentLessonProgress::where('lesson_completed', 1)->count();

        // ─── TEACHER RATING (real average from database, approved only) ──
        $avgRating = TeacherRating::where('is_approved', true)->avg('rating');
        $teacherRating = $avgRating ? number_format($avgRating, 1) . '★' : '—';

        return [
            // Hero stats
            'totalStudents'         => $totalStudents,
            'totalLessons'          => $totalLessons,
            'gestureAccuracy'       => 98,
            'activeLearners'        => $activeLearners,
            // Teacher Dashboard stats
            'totalTeachers'         => $totalTeachers,
            'studentEngagement'     => $studentEngagement,
            'totalLessonsCompleted' => $totalLessonsCompleted,
            'teacherRating'         => $teacherRating,
        ];
    }

    private function getRatingsData(): array
    {
        $totalTeacherRatings = TeacherRating::where('is_approved', true)->count();
        $totalStudentRatings = StudentRating::where('is_approved', true)->count();
        $totalRatings = $totalTeacherRatings + $totalStudentRatings;

        if ($totalRatings === 0) {
            return [
                'avgRating'       => null,
                'totalRatings'    => 0,
                'ratingDist'      => [],
                'featuredReviews' => collect(),
            ];
        }

        // Combined average across both sources (sum of ratings / total count)
        $teacherRatingSum = (float) TeacherRating::where('is_approved', true)->sum('rating');
        $studentRatingSum = (float) StudentRating::where('is_approved', true)->sum('rating');
        $avgRating = round(($teacherRatingSum + $studentRatingSum) / $totalRatings, 1);

        // Distribution: count per star (5 → 1, descending for display), approved only, both sources combined
        $teacherDistRaw = TeacherRating::where('is_approved', true)
            ->select('rating', DB::raw('COUNT(*) as cnt'))
            ->groupBy('rating')
            ->pluck('cnt', 'rating')
            ->toArray();
        $studentDistRaw = StudentRating::where('is_approved', true)
            ->select('rating', DB::raw('COUNT(*) as cnt'))
            ->groupBy('rating')
            ->pluck('cnt', 'rating')
            ->toArray();

        $ratingDist = [];
        for ($s = 5; $s >= 1; $s--) {
            $cnt = ($teacherDistRaw[$s] ?? 0) + ($studentDistRaw[$s] ?? 0);
            $ratingDist[$s] = [
                'count' => $cnt,
                'pct'   => $totalRatings > 0 ? round(($cnt / $totalRatings) * 100) : 0,
            ];
        }

        // Featured reviews: approved ratings with non-empty feedback from both sources, most recent first, max 6
        $teacherReviews = TeacherRating::with(['teacher.user'])
            ->where('is_approved', true)
            ->whereNotNull('feedback')
            ->where('feedback', '!=', '')
            ->latest('updated_at')
            ->limit(6)
            ->get()
            ->map(function ($r) {
                $teacher     = $r->teacher;
                $firstName   = $teacher?->first_name ?? 'Teacher';
                $lastName    = $teacher?->last_name  ?? '';
                // Show first name + last initial for privacy
                $displayName = $firstName . ($lastName ? ' ' . strtoupper(substr($lastName, 0, 1)) . '.' : '');
                // Use the User model's avatarUrl() — handles Google photo, local upload, or initials fallback
                $avatarUrl   = $teacher?->user?->avatarUrl()
                    ?? "https://ui-avatars.com/api/?name=" . urlencode($firstName . '+' . $lastName)
                       . "&background=0d326b&color=fff&size=128&font-size=0.45&bold=true&rounded=true";
                return [
                    'rating'     => $r->rating,
                    'feedback'   => $r->feedback,
                    'name'       => $displayName,
                    'role'       => 'SENAS Teacher',
                    'avatar'     => $avatarUrl,
                    'updated_at' => $r->updated_at,
                ];
            });

        $studentReviews = StudentRating::with(['student'])
            ->where('is_approved', true)
            ->whereNotNull('feedback')
            ->where('feedback', '!=', '')
            ->latest('updated_at')
            ->limit(6)
            ->get()
            ->map(function ($r) {
                $student     = $r->student;
                $firstName   = $student?->first_name ?? 'Student';
                $lastName    = $student?->last_name  ?? '';
                // Show first name + last initial for privacy
                $displayName = $firstName . ($lastName ? ' ' . strtoupper(substr($lastName, 0, 1)) . '.' : '');
                $avatarUrl   = "https://ui-avatars.com/api/?name=" . urlencode($firstName . '+' . $lastName)
                       . "&background=1a6fd4&color=fff&size=128&font-size=0.45&bold=true&rounded=true";
                return [
                    'rating'     => $r->rating,
                    'feedback'   => $r->feedback,
                    'name'       => $displayName,
                    'role'       => 'SENAS Student',
                    'avatar'     => $avatarUrl,
                    'updated_at' => $r->updated_at,
                ];
            });

        // Merge both sources, most recent first, cap at 6 for the landing page
        $featuredReviews = $teacherReviews->concat($studentReviews)
            ->sortByDesc('updated_at')
            ->take(6)
            ->values();

        return [
            'avgRating'       => $avgRating,
            'totalRatings'    => $totalRatings,
            'ratingDist'      => $ratingDist,
            'featuredReviews' => $featuredReviews,
        ];
    }

    public function getStatsJson()
    {
        $stats = $this->getRealTimeStats();
        return response()->json($stats);
    }
}