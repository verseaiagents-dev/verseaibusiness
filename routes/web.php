<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BlogCommentController;
use App\Http\Controllers\BlogViewController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\KnowledgeBaseController;
use App\Http\Controllers\AdminController;

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

// Language Routes (for manual override if needed)
Route::get('/language/{locale}', [LanguageController::class, 'changeLanguage'])->name('language.change');
Route::get('/language/reset', [LanguageController::class, 'resetToBrowserDefault'])->name('language.reset');

Route::get('/', function () {
    return view('landingpage.landing');
})->name('home');

// User Authentication Routes
Route::get('/signup', [UserController::class, 'show'])->name('signup');
Route::post('/register', [UserController::class, 'register'])->name('register');

Route::get('/login', [UserController::class, 'showLogin'])->name('login');
Route::post('/login', [UserController::class, 'login'])->name('login.post');

Route::post('/logout', [UserController::class, 'logout'])->name('logout');

Route::get('/terms', [UserController::class, 'terms'])->name('terms');

// Password Reset Routes
Route::get('/forgot-password', [UserController::class, 'showPasswordRequest'])->name('password.request');
Route::post('/forgot-password', [UserController::class, 'passwordRequest'])->name('password.email');

// Blog Routes
Route::get('/blog', [BlogViewController::class, 'index'])->name('blog');
Route::get('/blog/{slug}', [BlogViewController::class, 'show'])->name('blog.detail');
Route::get('/category/{slug}', [BlogViewController::class, 'category'])->name('blog.category');
Route::get('/home', [DashboardController::class, 'showdashboard'])->name('dashboard')->middleware('auth');
Route::get('/dashboard/knowledge-base/{project}', [DashboardController::class, 'knowledgeBase'])->name('knowledge-base')->middleware('auth');

// Admin Routes
Route::get('/admin', [AdminController::class, 'index'])->name('admin.panel');
Route::get('/admin/ai-settings', [AdminController::class, 'aiSettings'])->name('ai.settings');
Route::get('/admin/user-management', [AdminController::class, 'userManagement'])->name('admin.user-management');
Route::get('/admin/system-logs', [AdminController::class, 'systemLogs'])->name('admin.system-logs');
Route::get('/admin/backup', [AdminController::class, 'backup'])->name('admin.backup');
Route::get('/admin/analytics', [AdminController::class, 'analytics'])->name('admin.analytics');
Route::get('/admin/security', [AdminController::class, 'security'])->name('admin.security');
Route::get('/admin/settings', [AdminController::class, 'adminSettings'])->name('admin.settings');


// API Routes for Blog
Route::prefix('api')->group(function () {
    // Blog Posts
    Route::get('/blog', [BlogController::class, 'index'])->name('api.blog.index');
    Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('api.blog.show');
    Route::get('/blog/category/{slug}', [BlogController::class, 'byCategory'])->name('api.blog.category');
    
    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('api.categories.index');
    Route::get('/categories/{slug}', [CategoryController::class, 'show'])->name('api.categories.show');
    Route::get('/categories/{slug}/posts', [CategoryController::class, 'posts'])->name('api.categories.posts');
    
    // Comments
    Route::get('/blog/{blogPost}/comments', [BlogCommentController::class, 'forPost'])->name('api.comments.for-post');
    Route::get('/comments/{blogComment}/replies', [BlogCommentController::class, 'replies'])->name('api.comments.replies');
    
    // Protected API Routes (require authentication)
    Route::middleware(['auth'])->group(function () {
        // Blog Posts (CRUD)
        Route::post('/blog', [BlogController::class, 'store'])->name('api.blog.store');
        Route::put('/blog/{blogPost}', [BlogController::class, 'update'])->name('api.blog.update');
        Route::delete('/blog/{blogPost}', [BlogController::class, 'destroy'])->name('api.blog.destroy');
        
        // Blog Post Actions
        Route::post('/blog/{blogPost}/like', [BlogController::class, 'like'])->name('api.blog.like');
        Route::delete('/blog/{blogPost}/like', [BlogController::class, 'unlike'])->name('api.blog.unlike');
        
        // Categories (CRUD)
        Route::post('/categories', [CategoryController::class, 'store'])->name('api.categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('api.categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('api.categories.destroy');
        
        // Comments (CRUD)
        Route::post('/comments', [BlogCommentController::class, 'store'])->name('api.comments.store');
        Route::put('/comments/{blogComment}', [BlogCommentController::class, 'update'])->name('api.comments.update');
        Route::delete('/comments/{blogComment}', [BlogCommentController::class, 'destroy'])->name('api.comments.destroy');
        
        // Comment Moderation
        Route::patch('/comments/{blogComment}/approve', [BlogCommentController::class, 'approve'])->name('api.comments.approve');
        Route::patch('/comments/{blogComment}/spam', [BlogCommentController::class, 'markAsSpam'])->name('api.comments.spam');
        
        // Projects (CRUD)
        Route::get('/projects', [ProjectController::class, 'index'])->name('api.projects.index');
        Route::post('/projects', [ProjectController::class, 'store'])->name('api.projects.store');
        Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('api.projects.show');
        Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('api.projects.update');
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('api.projects.destroy');
        Route::get('/projects/user/info', [ProjectController::class, 'getUserInfo'])->name('api.projects.user-info');
        
        // Knowledge Base Routes
        Route::get('/projects/{project}/knowledge-base', [KnowledgeBaseController::class, 'index'])->name('api.knowledge-base.index');
        Route::post('/projects/{project}/knowledge-base', [KnowledgeBaseController::class, 'store'])->name('api.knowledge-base.store');
        Route::get('/projects/{project}/knowledge-base/{knowledgeBase}', [KnowledgeBaseController::class, 'show'])->name('api.knowledge-base.show');
        Route::delete('/projects/{project}/knowledge-base/{knowledgeBase}', [KnowledgeBaseController::class, 'destroy'])->name('api.knowledge-base.destroy');
        Route::get('/projects/{project}/knowledge-base/{knowledgeBase}/download', [KnowledgeBaseController::class, 'download'])->name('api.knowledge-base.download');
    });
    
    // Public comment creation (for guest users)
    Route::post('/comments/guest', [BlogCommentController::class, 'store'])->name('api.comments.guest');
});

