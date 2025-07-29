<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BlogPost;
use App\Models\Category;

class BlogViewController extends Controller
{
    /**
     * Display a listing of blog posts.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $posts = BlogPost::with('category')->latest()->paginate(9);
        return view('landingpage.blog', compact('posts'));
    }

    /**
     * Display the specified blog post.
     *
     * @param  string  $slug
     * @return \Illuminate\View\View
     */
    public function show($slug)
    {
        $post = BlogPost::where('slug', $slug)->with('category', 'comments')->firstOrFail();
        $relatedPosts = BlogPost::where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->latest()
            ->take(3)
            ->get();
            
        return view('landingpage.blog-detail', compact('post', 'relatedPosts'));
    }

    /**
     * Display posts by category.
     *
     * @param  string  $slug
     * @return \Illuminate\View\View
     */
    public function category($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $posts = BlogPost::where('category_id', $category->id)->latest()->paginate(9);
        
        return view('landingpage.blog', compact('posts', 'category'));
    }
}