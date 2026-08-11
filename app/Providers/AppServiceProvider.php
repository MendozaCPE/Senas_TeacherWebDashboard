<?php

namespace App\Providers;

use App\Models\TeacherNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Share unread notification count with every view that uses the app layout.
        // This powers the bell badge in the header across all pages.
        View::composer('partials.header', function ($view) {
            $count = 0;
            if (Auth::check() && Auth::user()->teacher) {
                $count = TeacherNotification::where('teacher_id', Auth::user()->teacher->id)
                    ->where('is_read', false)
                    ->count();
            }
            $view->with('unreadNotifCount', $count);
        });
    }
}
