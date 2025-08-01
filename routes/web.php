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
use App\Http\Controllers\UserIntentController;
use App\Http\Controllers\UserApiEventController;

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

// Agent Management Routes
Route::prefix('api/agents')->middleware(['auth'])->group(function () {
    Route::post('/', [AdminController::class, 'storeAgent'])->name('api.agents.store');
    Route::put('/{agent}', [AdminController::class, 'updateAgent'])->name('api.agents.update');
});

// User Intent Management Routes
Route::prefix('user/intents')->middleware(['auth'])->group(function () {
    Route::get('/', [UserIntentController::class, 'index'])->name('user.intents.index');
    Route::get('/{agent}', [UserIntentController::class, 'show'])->name('user.intents.show');
    Route::put('/{intent}', [UserIntentController::class, 'updateIntent'])->name('user.intents.update');
    Route::delete('/{intent}', [UserIntentController::class, 'deleteIntent'])->name('user.intents.delete');
    Route::post('/{agent}/intents', [UserIntentController::class, 'createCustomIntent'])->name('user.intents.create');
    Route::get('/{agent}/stats', [UserIntentController::class, 'getIntentStats'])->name('user.intents.stats');
    Route::get('/{agent}/templates', [UserIntentController::class, 'getSectorTemplates'])->name('user.intents.templates');
    Route::get('/{agent}/templates/all', [UserIntentController::class, 'getAllTemplates'])->name('user.intents.templates.all');
    Route::get('/{agent}/templates/sector/{sector}', [UserIntentController::class, 'getSectorTemplatesBySector'])->name('user.intents.templates.sector');
    Route::post('/{agent}/templates', [UserIntentController::class, 'createIntentFromTemplate'])->name('user.intents.create-from-template');
    Route::get('/{agent}/test-templates', [UserIntentController::class, 'testTemplates'])->name('user.intents.test'); // Test endpoint
});

// User API Event Management Routes
Route::prefix('user/api-events')->middleware(['auth'])->group(function () {
    Route::get('/', [UserApiEventController::class, 'index'])->name('user.api-events.index');
    Route::get('/{agent}', [UserApiEventController::class, 'show'])->name('user.api-events.show');
    Route::post('/{agent}', [UserApiEventController::class, 'store'])->name('user.api-events.store');
    Route::put('/{apiEvent}', [UserApiEventController::class, 'update'])->name('user.api-events.update');
    Route::delete('/{apiEvent}', [UserApiEventController::class, 'destroy'])->name('user.api-events.delete');
    Route::post('/{apiEvent}/test', [UserApiEventController::class, 'test'])->name('user.api-events.test');
});

// API Routes for Intent Management
Route::prefix('api/intents')->middleware(['auth'])->group(function () {
    Route::put('/{intent}', [UserIntentController::class, 'updateIntent'])->name('api.intents.update');
    Route::delete('/{intent}', [UserIntentController::class, 'deleteIntent'])->name('api.intents.delete');
});

// User Management Additional Routes
Route::prefix('admin/users')->middleware(['auth'])->group(function () {
    Route::get('/{user}/edit', [AdminController::class, 'editUser'])->name('admin.users.edit');
    Route::put('/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/{user}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');
});
Route::get('/admin/analytics', [AdminController::class, 'analytics'])->name('admin.analytics');
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
        Route::post('/projects/{project}/knowledge-base/import', [KnowledgeBaseController::class, 'import'])->name('api.knowledge-base.import');
        Route::get('/projects/{project}/knowledge-base/{knowledgeBase}', [KnowledgeBaseController::class, 'show'])->name('api.knowledge-base.show');
        Route::delete('/projects/{project}/knowledge-base/{knowledgeBase}', [KnowledgeBaseController::class, 'destroy'])->name('api.knowledge-base.destroy');
        Route::get('/projects/{project}/knowledge-base/{knowledgeBase}/download', [KnowledgeBaseController::class, 'download'])->name('api.knowledge-base.download');
        Route::post('/projects/{project}/knowledge-base/{knowledgeBase}/process-ai', [KnowledgeBaseController::class, 'processWithAi'])->name('api.knowledge-base.process-ai');
        
        // Test route for import
        Route::get('/test-import/{project}', function($project) {
            return response()->json([
                'success' => true,
                'message' => 'Test endpoint working',
                'project_id' => $project
            ]);
        })->name('api.test-import');
    });
    
    // Public comment creation (for guest users)
    Route::post('/comments/guest', [BlogCommentController::class, 'store'])->name('api.comments.guest');
});

