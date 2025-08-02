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
use App\Http\Controllers\Admin\AiProviderController;
use App\Http\Controllers\UserProfileController;

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

// Public Profile Routes (must come before authenticated routes)
Route::get('/profile/{slug}', [UserProfileController::class, 'showPublic'])->name('profile.public');

// User Profile Routes
Route::prefix('profile')->middleware(['auth'])->group(function () {
    Route::get('/', [UserProfileController::class, 'index'])->name('profile.index');
    Route::get('/create', [UserProfileController::class, 'create'])->name('profile.create');
    Route::post('/', [UserProfileController::class, 'store'])->name('profile.store');
    Route::get('/{userProfile}', [UserProfileController::class, 'show'])->name('profile.show');
    Route::get('/{userProfile}/edit', [UserProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/{userProfile}', [UserProfileController::class, 'update'])->name('profile.update');
    Route::delete('/{userProfile}', [UserProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/{userProfile}/qr-code', [UserProfileController::class, 'generateQrCode'])->name('profile.qr-code');
    Route::post('/{userProfile}/statistics', [UserProfileController::class, 'updateStatistics'])->name('profile.statistics');
    Route::post('/check-username', [UserProfileController::class, 'checkUsername'])->name('profile.check-username');
});

// Admin Routes
Route::get('/admin', [AdminController::class, 'index'])->name('admin.panel');
Route::get('/admin/user-management', [AdminController::class, 'userManagement'])->name('admin.user-management');

// AI Provider Management Routes
Route::prefix('admin/ai-providers')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AiProviderController::class, 'index'])->name('admin.ai-providers.index');
    Route::get('/create', [AiProviderController::class, 'create'])->name('admin.ai-providers.create');
    Route::post('/', [AiProviderController::class, 'store'])->name('admin.ai-providers.store');
    Route::get('/{provider}/edit', [AiProviderController::class, 'edit'])->name('admin.ai-providers.edit');
    Route::put('/{provider}', [AiProviderController::class, 'update'])->name('admin.ai-providers.update');
    Route::delete('/{provider}', [AiProviderController::class, 'destroy'])->name('admin.ai-providers.destroy');
    Route::post('/{provider}/test-connection', [AiProviderController::class, 'testConnection'])->name('admin.ai-providers.test-connection');
    Route::post('/{provider}/toggle-status', [AiProviderController::class, 'toggleStatus'])->name('admin.ai-providers.toggle-status');
    Route::post('/{provider}/sync-models', [AiProviderController::class, 'syncModels'])->name('admin.ai-providers.sync-models');
    Route::get('/{provider}/stats', [AiProviderController::class, 'stats'])->name('admin.ai-providers.stats');
    Route::get('/status', [AiProviderController::class, 'status'])->name('admin.ai-providers.status');
});

// Agent Management Routes
Route::prefix('api/agents')->middleware(['auth'])->group(function () {
    Route::post('/', [AdminController::class, 'storeAgent'])->name('api.agents.store');
    Route::put('/{agent}', [AdminController::class, 'updateAgent'])->name('api.agents.update');
});

// User Intent Management Routes (Deprecated - moved to Knowledge Base)
// Route::prefix('user/intents')->middleware(['auth'])->group(function () {
//     Route::get('/', [UserIntentController::class, 'index'])->name('user.intents.index');
//     Route::get('/{agent}', [UserIntentController::class, 'show'])->name('user.intents.show');
//     Route::put('/{intent}', [UserIntentController::class, 'updateIntent'])->name('user.intents.update');
//     Route::delete('/{intent}', [UserIntentController::class, 'deleteIntent'])->name('user.intents.delete');
//     Route::post('/{agent}/intents', [UserIntentController::class, 'createCustomIntent'])->name('user.intents.create');
//     Route::get('/{agent}/stats', [UserIntentController::class, 'getIntentStats'])->name('user.intents.stats');
//     Route::get('/{agent}/templates', [UserIntentController::class, 'getSectorTemplates'])->name('user.intents.templates');
//     Route::get('/{agent}/templates/all', [UserIntentController::class, 'getAllTemplates'])->name('user.intents.templates.all');
//     Route::get('/{agent}/templates/sector/{sector}', [UserIntentController::class, 'getSectorTemplatesBySector'])->name('user.intents.templates.sector');
//     Route::post('/{agent}/templates', [UserIntentController::class, 'createIntentFromTemplate'])->name('user.intents.create-from-template');
//     Route::get('/{agent}/test-templates', [UserIntentController::class, 'testTemplates'])->name('user.intents.test'); // Test endpoint
// });

// User API Event Management Routes (Deprecated - moved to Knowledge Base)
// Route::prefix('user/api-events')->middleware(['auth'])->group(function () {
//     Route::get('/', [UserApiEventController::class, 'index'])->name('user.api-events.index');
//     Route::get('/{agent}', [UserApiEventController::class, 'show'])->name('user.api-events.show');
//     Route::post('/{agent}', [UserApiEventController::class, 'store'])->name('user.api-events.store');
//     Route::put('/{apiEvent}', [UserApiEventController::class, 'update'])->name('user.api-events.update');
//     Route::delete('/{apiEvent}', [UserApiEventController::class, 'destroy'])->name('user.api-events.delete');
//     Route::post('/{apiEvent}/test', [UserApiEventController::class, 'test'])->name('user.api-events.test');
// });

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

// Admin panel için niyet yönetimi (intents) sayfası - Artık AdminIntentController kullanıyor
Route::get('/admin/intents', [App\Http\Controllers\AdminIntentController::class, 'index'])->name('admin.intents.index');

// Admin panel için ayrı AI ayarları sayfası
Route::get('/admin/ai-settings', [AdminController::class, 'adminAiSettings'])->name('admin.ai-settings');

// Admin panel için intent detay sayfası
Route::get('/admin/intents/{intent}', [App\Http\Controllers\AdminIntentController::class, 'show'])->name('admin.intents.show');

// Admin panel için intent işlemleri
Route::put('/admin/intents/{intent}', [App\Http\Controllers\AdminIntentController::class, 'update'])->name('admin.intents.update');
Route::delete('/admin/intents/{intent}', [App\Http\Controllers\AdminIntentController::class, 'destroy'])->name('admin.intents.destroy');
Route::post('/admin/intents/{intent}/toggle-status', [App\Http\Controllers\AdminIntentController::class, 'toggleStatus'])->name('admin.intents.toggle-status');

// Admin panel için intent template işlemleri
Route::post('/admin/intent-templates', [App\Http\Controllers\AdminIntentController::class, 'storeTemplate'])->name('admin.intent-templates.store');
Route::get('/admin/intent-templates', [App\Http\Controllers\AdminIntentController::class, 'getTemplates'])->name('admin.intent-templates.index');
Route::post('/admin/intent-templates/{template}/apply', [App\Http\Controllers\AdminIntentController::class, 'applyTemplate'])->name('admin.intent-templates.apply');

// Admin panel için agent yönetimi
Route::get('/admin/agents', [App\Http\Controllers\AdminAgentController::class, 'index'])->name('admin.agents.index');
Route::get('/admin/agents/{agent}', [App\Http\Controllers\AdminAgentController::class, 'show'])->name('admin.agents.show');
Route::get('/admin/agents/{agent}/edit-data', [App\Http\Controllers\AdminAgentController::class, 'getEditData'])->name('admin.agents.edit-data');
Route::post('/admin/agents', [App\Http\Controllers\AdminAgentController::class, 'store'])->name('admin.agents.store');
Route::put('/admin/agents/{agent}', [App\Http\Controllers\AdminAgentController::class, 'update'])->name('admin.agents.update');
Route::delete('/admin/agents/{agent}', [App\Http\Controllers\AdminAgentController::class, 'destroy'])->name('admin.agents.destroy');
Route::post('/admin/agents/{agent}/toggle-status', [App\Http\Controllers\AdminAgentController::class, 'toggleStatus'])->name('admin.agents.toggle-status');

// Gerçek zamanlı maliyet istatistikleri
Route::get('/admin/cost-statistics', [App\Http\Controllers\AdminAgentController::class, 'getCostStatistics'])->name('admin.cost-statistics');

// Admin panel için agent detay yönetimi
Route::get('/admin/agents/{agent}/knowledge-base', [App\Http\Controllers\AdminAgentController::class, 'knowledgeBase'])->name('admin.agents.knowledge-base');
Route::get('/admin/agents/{agent}/intents', [App\Http\Controllers\AdminAgentController::class, 'intents'])->name('admin.agents.intents');
Route::get('/admin/agents/{agent}/api-events', [App\Http\Controllers\AdminAgentController::class, 'apiEvents'])->name('admin.agents.api-events');

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
        
        // Agent Management Routes
        Route::get('/projects/{project}/agent-data', [KnowledgeBaseController::class, 'getAgentData'])->name('api.agent.data');
        Route::get('/projects/{project}/sector-templates', [KnowledgeBaseController::class, 'getSectorTemplates'])->name('api.agent.sector-templates');
        Route::post('/projects/{project}/intents', [KnowledgeBaseController::class, 'createIntent'])->name('api.agent.intents.create');
        Route::get('/projects/{project}/intents/{intentId}', [KnowledgeBaseController::class, 'getIntent'])->name('api.agent.intents.show');
        Route::put('/projects/{project}/intents/{intentId}', [KnowledgeBaseController::class, 'updateIntent'])->name('api.agent.intents.update');
        Route::post('/projects/{project}/api-events', [KnowledgeBaseController::class, 'createApiEvent'])->name('api.agent.api-events.create');
        
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

