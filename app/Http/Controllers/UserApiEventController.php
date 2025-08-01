<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApiEvent;
use App\Models\Agent;
use App\Models\Intent;
use Illuminate\Support\Facades\Auth;

class UserApiEventController extends Controller
{
    public function index()
    {
        // Kullanıcının agent'larını al
        $userAgents = Agent::where('user_id', Auth::id())->with(['intents', 'apiEvents'])->get();
        
        // Sektör bazlı grupla
        $agentsBySector = $userAgents->groupBy('sector');
        
        return view('dashboard.user-api-events', compact('agentsBySector'));
    }

    public function show(Agent $agent)
    {
        // Kullanıcının kendi agent'ı mı kontrol et
        if ($agent->user_id !== Auth::id()) {
            abort(403, 'Bu agent\'a erişim yetkiniz yok.');
        }

        $intents = $agent->intents()->orderBy('name')->get();
        $apiEvents = $agent->apiEvents()->with('intent')->orderBy('name')->get();
        
        return view('dashboard.user-agent-api-events', compact('agent', 'intents', 'apiEvents'));
    }

    public function store(Request $request, Agent $agent)
    {
        // Kullanıcının kendi agent'ı mı kontrol et
        if ($agent->user_id !== Auth::id()) {
            abort(403, 'Bu agent\'a erişim yetkiniz yok.');
        }

        $validated = $request->validate([
            'intent_id' => 'nullable|exists:intents,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'http_method' => 'required|in:GET,POST,PUT,DELETE,PATCH',
            'endpoint_url' => 'required|url',
            'headers' => 'nullable|array',
            'body_template' => 'nullable|array',
            'response_mapping' => 'nullable|array',
            'trigger_conditions' => 'nullable|array'
        ]);

        $validated['user_id'] = Auth::id();
        $validated['agent_id'] = $agent->id;
        $validated['is_active'] = true;

        $apiEvent = ApiEvent::create($validated);

        return response()->json([
            'message' => 'API Event başarıyla oluşturuldu',
            'apiEvent' => $apiEvent
        ]);
    }

    public function update(Request $request, ApiEvent $apiEvent)
    {
        // Kullanıcının kendi event'i mi kontrol et
        if ($apiEvent->user_id !== Auth::id()) {
            abort(403, 'Bu event\'e erişim yetkiniz yok.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'http_method' => 'required|in:GET,POST,PUT,DELETE,PATCH',
            'endpoint_url' => 'required|url',
            'headers' => 'nullable|array',
            'body_template' => 'nullable|array',
            'response_mapping' => 'nullable|array',
            'trigger_conditions' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        $apiEvent->update($validated);

        return response()->json([
            'message' => 'API Event başarıyla güncellendi',
            'apiEvent' => $apiEvent
        ]);
    }

    public function destroy(ApiEvent $apiEvent)
    {
        // Kullanıcının kendi event'i mi kontrol et
        if ($apiEvent->user_id !== Auth::id()) {
            abort(403, 'Bu event\'e erişim yetkiniz yok.');
        }

        $apiEvent->delete();

        return response()->json([
            'message' => 'API Event başarıyla silindi'
        ]);
    }

    public function test(ApiEvent $apiEvent)
    {
        // Kullanıcının kendi event'i mi kontrol et
        if ($apiEvent->user_id !== Auth::id()) {
            abort(403, 'Bu event\'e erişim yetkiniz yok.');
        }

        try {
            // Test isteği gönder
            $response = $this->sendTestRequest($apiEvent);
            
            return response()->json([
                'message' => 'Test başarılı',
                'response' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Test başarısız: ' . $e->getMessage()
            ], 400);
        }
    }

    private function sendTestRequest(ApiEvent $apiEvent)
    {
        $client = new \GuzzleHttp\Client();
        
        $options = [
            'headers' => $apiEvent->headers ?? [],
            'timeout' => 10
        ];

        if ($apiEvent->http_method !== 'GET' && $apiEvent->body_template) {
            $options['json'] = $apiEvent->body_template;
        }

        $response = $client->request($apiEvent->http_method, $apiEvent->endpoint_url, $options);
        
        return [
            'status_code' => $response->getStatusCode(),
            'body' => $response->getBody()->getContents(),
            'headers' => $response->getHeaders()
        ];
    }
}
