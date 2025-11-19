<?php

use App\Http\Controllers\AIController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });

    // Authentication routes with rate limiting
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])
        ->middleware('throttle:5,1'); // 5 attempts per minute

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])
        ->middleware('throttle:3,10'); // 3 attempts per 10 minutes
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Projects
    Route::resource('projects', ProjectController::class);

    // Tasks
    Route::resource('tasks', TaskController::class);
    Route::patch('/tasks/{task}/complete', [TaskController::class, 'markAsComplete'])->name('tasks.complete');
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');

    // AI Features
    Route::prefix('ai')->name('ai.')->middleware('throttle:20,1')->group(function () {
        // AI status
        Route::get('/status', [AIController::class, 'status'])->name('status');

        // Task AI features (more restrictive rate limit for expensive operations)
        Route::post('/task/breakdown', [AIController::class, 'breakdownTask'])
            ->middleware('throttle:5,1')->name('task.breakdown');
        Route::post('/task/estimate-hours', [AIController::class, 'estimateHours'])
            ->middleware('throttle:10,1')->name('task.estimate-hours');
        Route::post('/task/suggest-priority', [AIController::class, 'suggestPriority'])
            ->middleware('throttle:10,1')->name('task.suggest-priority');
        Route::post('/task/enhance-description', [AIController::class, 'enhanceDescription'])
            ->middleware('throttle:10,1')->name('task.enhance-description');

        // Project AI features
        Route::get('/project/{project}/summary', [AIController::class, 'projectSummary'])
            ->middleware('throttle:3,1')->name('project.summary');
        Route::get('/project/{project}/risks', [AIController::class, 'projectRisks'])
            ->middleware('throttle:5,1')->name('project.risks');
        Route::get('/project/{project}/completion', [AIController::class, 'projectCompletion'])
            ->middleware('throttle:5,1')->name('project.completion');
        Route::get('/project/{project}/suggest-tasks', [AIController::class, 'suggestTasks'])
            ->middleware('throttle:5,1')->name('project.suggest-tasks');
    });
});
