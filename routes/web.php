<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BlogCommentController;
use App\Http\Controllers\BlogViewController;
use App\Http\Controllers\LanguageController;

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
    });
    
    // Public comment creation (for guest users)
    Route::post('/comments/guest', [BlogCommentController::class, 'store'])->name('api.comments.guest');
});

