<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BlogPost;
use App\Models\Category;

class BlogController extends Controller
{
    /**
     * Display a listing of blog posts.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $posts = BlogPost::with('category')->latest()->paginate(9);
        return response()->json($posts);
    }

    /**
     * Display the specified blog post.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($slug)
    {
        $post = BlogPost::where('slug', $slug)->with('category', 'comments')->firstOrFail();
        return response()->json($post);
    }

    /**
     * Display posts by category.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function byCategory($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $posts = BlogPost::where('category_id', $category->id)->latest()->paginate(9);
        
        return response()->json($posts);
    }

    /**
     * Store a newly created blog post.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'slug' => 'required|string|unique:blog_posts,slug',
        ]);

        $post = BlogPost::create($validated);
        return response()->json($post, 201);
    }

    /**
     * Update the specified blog post.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BlogPost  $blogPost
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, BlogPost $blogPost)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        $blogPost->update($validated);
        return response()->json($blogPost);
    }

    /**
     * Remove the specified blog post.
     *
     * @param  \App\Models\BlogPost  $blogPost
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(BlogPost $blogPost)
    {
        $blogPost->delete();
        return response()->json(null, 204);
    }

    /**
     * Like a blog post.
     *
     * @param  \App\Models\BlogPost  $blogPost
     * @return \Illuminate\Http\JsonResponse
     */
    public function like(BlogPost $blogPost)
    {
        $blogPost->increment('likes');
        return response()->json(['message' => 'Post liked successfully']);
    }

    /**
     * Unlike a blog post.
     *
     * @param  \App\Models\BlogPost  $blogPost
     * @return \Illuminate\Http\JsonResponse
     */
    public function unlike(BlogPost $blogPost)
    {
        $blogPost->decrement('likes');
        return response()->json(['message' => 'Post unliked successfully']);
    }
}