<?php

namespace App\Http\Controllers;

use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $profile = $user->profile;

        if (!$profile) {
            // Create default profile if doesn't exist
            $profile = $user->profile()->create([
                'business_name' => $user->name,
                'profile_slug' => UserProfile::generateProfileSlug($user->name),
                'is_public' => true,
            ]);
        }

        return view('dashboard.profile.index', compact('profile'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard.profile.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => ['nullable', 'string', 'max:255', 'unique:user_profiles'],
            'business_name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'industry' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'social_links' => ['nullable', 'array'],
            'social_links.instagram' => ['nullable', 'url'],
            'social_links.linkedin' => ['nullable', 'url'],
            'social_links.facebook' => ['nullable', 'url'],
            'social_links.twitter' => ['nullable', 'url'],
            'is_public' => ['boolean'],
        ]);

        $user = Auth::user();

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar_url'] = Storage::url($avatarPath);
        }

        // Generate profile slug if not provided
        if (empty($validated['profile_slug'])) {
            $validated['profile_slug'] = UserProfile::generateProfileSlug($validated['business_name']);
        }

        // Create or update profile
        $profile = $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        return redirect()->route('profile.index')
            ->with('success', 'Profil başarıyla güncellendi!');
    }

    /**
     * Display the specified resource.
     */
    public function show(UserProfile $userProfile)
    {
        // Check if profile is public or belongs to authenticated user
        if (!$userProfile->is_public && $userProfile->user_id !== Auth::id()) {
            abort(404);
        }

        // Update last active timestamp
        $userProfile->updateLastActive();

        return view('dashboard.profile.show', compact('userProfile'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserProfile $userProfile)
    {
        // Check if user owns this profile
        if ($userProfile->user_id !== Auth::id()) {
            abort(403);
        }

        return view('dashboard.profile.edit', compact('userProfile'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserProfile $userProfile)
    {
        // Check if user owns this profile
        if ($userProfile->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'username' => ['nullable', 'string', 'max:255', 'unique:user_profiles,username,' . $userProfile->id],
            'business_name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'industry' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'social_links' => ['nullable', 'array'],
            'social_links.instagram' => ['nullable', 'url'],
            'social_links.linkedin' => ['nullable', 'url'],
            'social_links.facebook' => ['nullable', 'url'],
            'social_links.twitter' => ['nullable', 'url'],
            'is_public' => ['boolean'],
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($userProfile->avatar_url) {
                $oldPath = str_replace('/storage/', '', $userProfile->avatar_url);
                Storage::disk('public')->delete($oldPath);
            }

            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar_url'] = Storage::url($avatarPath);
        }

        // Update profile slug if business name changed
        if ($validated['business_name'] !== $userProfile->business_name) {
            $validated['profile_slug'] = UserProfile::generateProfileSlug($validated['business_name']);
        }

        $userProfile->update($validated);

        return redirect()->route('profile.index')
            ->with('success', 'Profil başarıyla güncellendi!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserProfile $userProfile)
    {
        // Check if user owns this profile
        if ($userProfile->user_id !== Auth::id()) {
            abort(403);
        }

        // Delete avatar if exists
        if ($userProfile->avatar_url) {
            $avatarPath = str_replace('/storage/', '', $userProfile->avatar_url);
            Storage::disk('public')->delete($avatarPath);
        }

        $userProfile->delete();

        return redirect()->route('profile.index')
            ->with('success', 'Profil başarıyla silindi!');
    }

    /**
     * Show public profile
     */
    public function showPublic($slug)
    {
        $userProfile = UserProfile::where('profile_slug', $slug)
            ->where('is_public', true)
            ->firstOrFail();

        // Update last active timestamp
        $userProfile->updateLastActive();

        return view('profile.public', compact('userProfile'));
    }

    /**
     * Generate QR code for profile
     */
    public function generateQrCode(UserProfile $userProfile)
    {
        // Check if user owns this profile
        if ($userProfile->user_id !== Auth::id()) {
            abort(403);
        }

        $qrCodeUrl = $userProfile->generateQrCodeUrl();
        $userProfile->update(['share_qr_code_url' => $qrCodeUrl]);

        return response()->json([
            'success' => true,
            'qr_code_url' => $qrCodeUrl
        ]);
    }

    /**
     * Update profile statistics
     */
    public function updateStatistics(UserProfile $userProfile, Request $request)
    {
        $validated = $request->validate([
            'total_sessions' => ['nullable', 'integer'],
            'total_events_tracked' => ['nullable', 'integer'],
            'conversion_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'popular_topics' => ['nullable', 'array'],
            'response_quality_score' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $userProfile->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'İstatistikler güncellendi'
        ]);
    }

    /**
     * Check username availability
     */
    public function checkUsername(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'current_user_id' => 'nullable|integer'
        ]);

        $username = $request->input('username');
        $currentUserId = $request->input('current_user_id');

        // Check if username exists in user_profiles table
        $query = UserProfile::where('username', $username);
        
        // Exclude current user's profile if editing
        if ($currentUserId) {
            $query->where('user_id', '!=', $currentUserId);
        }

        $exists = $query->exists();

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'Bu kullanıcı adı zaten alınmış' : 'Bu kullanıcı adı kullanmaya müsait'
        ]);
    }
}
