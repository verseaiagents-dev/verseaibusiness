<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BlogComment;
use App\Models\BlogPost;

class BlogCommentController extends Controller
{
    /**
     * Display comments for a specific blog post.
     *
     * @param  \App\Models\BlogPost  $blogPost
     * @return \Illuminate\Http\JsonResponse
     */
    public function forPost(BlogPost $blogPost)
    {
        $comments = $blogPost->comments()->with('user')->latest()->get();
        return response()->json($comments);
    }

    /**
     * Display replies for a specific comment.
     *
     * @param  \App\Models\BlogComment  $blogComment
     * @return \Illuminate\Http\JsonResponse
     */
    public function replies(BlogComment $blogComment)
    {
        $replies = $blogComment->replies()->with('user')->latest()->get();
        return response()->json($replies);
    }

    /**
     * Store a newly created comment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'blog_post_id' => 'required|exists:blog_posts,id',
            'parent_id' => 'nullable|exists:blog_comments,id',
        ]);

        $validated['user_id'] = auth()->id();
        $comment = BlogComment::create($validated);
        
        return response()->json($comment, 201);
    }

    /**
     * Store a guest comment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeGuest(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'blog_post_id' => 'required|exists:blog_posts,id',
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email',
        ]);

        $comment = BlogComment::create([
            'content' => $validated['content'],
            'blog_post_id' => $validated['blog_post_id'],
            'guest_name' => $validated['guest_name'],
            'guest_email' => $validated['guest_email'],
        ]);
        
        return response()->json($comment, 201);
    }

    /**
     * Update the specified comment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BlogComment  $blogComment
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, BlogComment $blogComment)
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $blogComment->update($validated);
        return response()->json($blogComment);
    }

    /**
     * Remove the specified comment.
     *
     * @param  \App\Models\BlogComment  $blogComment
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(BlogComment $blogComment)
    {
        $blogComment->delete();
        return response()->json(null, 204);
    }

    /**
     * Approve a comment.
     *
     * @param  \App\Models\BlogComment  $blogComment
     * @return \Illuminate\Http\JsonResponse
     */
    public function approve(BlogComment $blogComment)
    {
        $blogComment->update(['is_approved' => true]);
        return response()->json(['message' => 'Comment approved successfully']);
    }

    /**
     * Mark a comment as spam.
     *
     * @param  \App\Models\BlogComment  $blogComment
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsSpam(BlogComment $blogComment)
    {
        $blogComment->update(['is_spam' => true]);
        return response()->json(['message' => 'Comment marked as spam']);
    }
}