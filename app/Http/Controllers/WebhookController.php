<?php

namespace App\Http\Controllers;

use App\Models\Webhook;
use App\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebhookController extends Controller
{
    public function index()
    {
        $webhooks = Auth::user()->webhooks()->latest()->get();
        return view('dashboard.webhooks', compact('webhooks'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $webhook = Auth::user()->webhooks()->create($data);
        return response()->json(['success' => true, 'data' => $webhook]);
    }

    public function update(Request $request, Webhook $webhook)
    {
        abort_if($webhook->user_id !== Auth::id(), 403);
        $data = $this->validated($request);
        $webhook->update($data);
        return response()->json(['success' => true, 'data' => $webhook]);
    }

    public function destroy(Webhook $webhook)
    {
        abort_if($webhook->user_id !== Auth::id(), 403);
        $webhook->delete();
        return response()->json(['success' => true]);
    }

    public function test(Request $request, Webhook $webhook, WebhookService $service)
    {
        abort_if($webhook->user_id !== Auth::id(), 403);

        // Send fake payload
        $fake = new \App\Models\Service([
            'id' => 0, 'name' => 'Test Service', 'ip' => '127.0.0.1',
            'url' => 'https://example.com', 'expires_at' => today()->addDays(7),
            'cost' => 100, 'currency' => 'RUB', 'provider_name' => 'Test',
        ]);
        $fake->id = 0;

        $success = $service->send($webhook, $fake);
        $log = $webhook->webhookLogs()->latest()->first();

        return response()->json([
            'success' => $success,
            'status_code' => $log?->status_code,
            'response_body' => $log?->response_body,
        ]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'url' => ['required', 'url'],
            'secret' => ['nullable', 'string', 'max:255'],
            'method' => ['nullable', 'in:POST,GET'],
            'headers' => ['nullable', 'array'],
            'payload_template' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
