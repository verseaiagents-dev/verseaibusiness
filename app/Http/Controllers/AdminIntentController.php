<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Intent;
use App\Models\Agent;
use Illuminate\Support\Facades\Auth;

class AdminIntentController extends Controller
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

        // Tüm kullanıcıların intent'lerini çek
        $intents = Intent::with(['agent.user'])->get();
        $agents = Agent::with(['user'])->get();
        
        return view('dashboard.admin-intents-management', compact('intents', 'agents'));
    }

    public function show(Intent $intent)
    {
        // Auth kontrolü
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Admin kontrolü
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Bu sayfaya erişim yetkiniz yok.');
        }

        $intent->load(['agent.user', 'apiEvents']);
        
        return view('dashboard.admin-intent-detail', compact('intent'));
    }

    public function update(Request $request, Intent $intent)
    {
        // Auth kontrolü
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Admin kontrolü
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Bu sayfaya erişim yetkiniz yok.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'config' => 'nullable|array'
        ]);

        $intent->update($validated);

        return response()->json([
            'message' => 'Intent başarıyla güncellendi',
            'intent' => $intent
        ]);
    }

    public function destroy(Intent $intent)
    {
        // Auth kontrolü
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Admin kontrolü
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Bu sayfaya erişim yetkiniz yok.');
        }

        $intent->delete();

        return response()->json([
            'message' => 'Intent başarıyla silindi'
        ]);
    }

    public function toggleStatus(Intent $intent)
    {
        // Auth kontrolü
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Admin kontrolü
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Bu sayfaya erişim yetkiniz yok.');
        }

        $intent->update(['is_active' => !$intent->is_active]);

        return response()->json([
            'message' => 'Intent durumu güncellendi',
            'is_active' => $intent->is_active
        ]);
    }

    public function storeTemplate(Request $request)
    {
        // Auth kontrolü
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Admin kontrolü
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Bu sayfaya erişim yetkiniz yok.');
        }

        $validated = $request->validate([
            'template_name' => 'required|string|max:255',
            'sector' => 'required|in:ecommerce,real_estate,hotel',
            'description' => 'nullable|string',
            'training_data' => 'nullable|string'
        ]);

        // Training data'yı array'e çevir
        $trainingData = [];
        if (!empty($validated['training_data'])) {
            $trainingData = array_filter(
                array_map('trim', explode("\n", $validated['training_data'])),
                function($line) { return !empty($line); }
            );
        }

        // Template'i veritabanına kaydet
        $template = \App\Models\IntentTemplate::create([
            'name' => $validated['template_name'],
            'sector' => $validated['sector'],
            'description' => $validated['description'],
            'training_data' => $trainingData,
            'is_active' => true
        ]);

        return response()->json([
            'message' => 'Şablon başarıyla eklendi',
            'template' => $template
        ]);
    }

    public function getTemplates()
    {
        // Auth kontrolü
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Admin kontrolü
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Bu sayfaya erişim yetkiniz yok.');
        }

        $templates = \App\Models\IntentTemplate::where('is_active', true)->get();
        
        return response()->json([
            'templates' => $templates
        ]);
    }

    public function applyTemplate(Request $request, $templateId)
    {
        // Auth kontrolü
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Admin kontrolü
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Bu sayfaya erişim yetkiniz yok.');
        }

        $validated = $request->validate([
            'agent_id' => 'required|exists:agents,id'
        ]);

        $template = \App\Models\IntentTemplate::findOrFail($templateId);
        
        // Template'i agent'a uygula
        $intent = Intent::create([
            'agent_id' => $validated['agent_id'],
            'name' => $template->name,
            'description' => $template->description,
            'sector' => $template->sector,
            'training_data' => $template->training_data,
            'is_active' => true,
            'config' => [
                'response_type' => 'general_info',
                'template_applied' => true
            ]
        ]);

        return response()->json([
            'message' => 'Şablon başarıyla uygulandı',
            'intent' => $intent
        ]);
    }
} 