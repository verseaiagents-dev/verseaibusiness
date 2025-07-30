<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index()
    {
        // Auth kontrolü
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Admin kontrolü
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Bu sayfaya erişim yetkiniz yok.');
        }

        return view('dashboard.admin-panel');
    }

    public function aiSettings()
    {
        // Auth kontrolü
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Admin kontrolü
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Bu sayfaya erişim yetkiniz yok.');
        }

        return view('dashboard.ai-settings');
    }

    public function userManagement()
    {
        // Auth kontrolü
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Admin kontrolü
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Bu sayfaya erişim yetkiniz yok.');
        }

        return view('dashboard.user-management');
    }

    public function systemLogs()
    {
        // Auth kontrolü
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Admin kontrolü
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Bu sayfaya erişim yetkiniz yok.');
        }

        return view('dashboard.system-logs');
    }

    public function backup()
    {
        // Auth kontrolü
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Admin kontrolü
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Bu sayfaya erişim yetkiniz yok.');
        }

        return view('dashboard.backup');
    }

    public function analytics()
    {
        // Auth kontrolü
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Admin kontrolü
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Bu sayfaya erişim yetkiniz yok.');
        }

        return view('dashboard.analytics');
    }

    public function security()
    {
        // Auth kontrolü
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Admin kontrolü
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Bu sayfaya erişim yetkiniz yok.');
        }

        return view('dashboard.security');
    }

    public function adminSettings()
    {
        // Auth kontrolü
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Admin kontrolü
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Bu sayfaya erişim yetkiniz yok.');
        }

        return view('dashboard.admin-settings');
    }
} 