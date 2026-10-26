<?php

use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\Teacher\AnnouncementController as TeacherAnnouncementController;
use App\Http\Controllers\Teacher\NotificationController as TeacherNotificationController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', 'login', 301)->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::resource('students', StudentController::class);
    Route::resource('parents', ParentController::class);

    Route::prefix('teacher')
        ->name('teacher.')
        ->middleware('role:Teacher')
        ->group(function () {
            Route::resource('announcements', TeacherAnnouncementController::class)->only(['index', 'create', 'store']);
            Route::get('notifications', [TeacherNotificationController::class, 'index'])->name('notifications.index');
            Route::get('notifications/{notification}', [TeacherNotificationController::class, 'show'])->name('notifications.show');
        });

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:Admin')
        ->group(function () {
            Route::resource('teachers', TeacherController::class)->except('show');
            Route::resource('announcements', AnnouncementController::class)->except('show');
        });
});

require __DIR__.'/settings.php';
