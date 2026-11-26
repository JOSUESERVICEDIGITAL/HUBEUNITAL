<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PublicServiceRequestController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'chamber_slug' => ['required', 'string', Rule::in(array_keys(config('public_services.chambers')))],
            'service_key' => ['required', 'string'],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:40'],
            'organization' => ['nullable', 'string', 'max:180'],
            'details' => ['required', 'string', 'max:5000'],
        ]);

        $chamber = config('public_services.chambers.'.$data['chamber_slug']);
        $service = $chamber['services'][$data['service_key']] ?? null;
        if (!$service) {
            throw ValidationException::withMessages([
                'service_key' => 'Cette prestation ne fait pas partie des services de la chambre sélectionnée.',
            ]);
        }

        $requestId = DB::table('public_service_requests')->insertGetId([
            ...$data,
            'service_name' => $service['label'],
            'status' => 'new',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $message = 'Votre demande a bien été envoyée. Un agent EUNITAL vous contactera prochainement.';
        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'reference' => 'SR-'.str_pad((string) $requestId, 6, '0', STR_PAD_LEFT)], 201);
        }

        return back()->with('service_request_success', $message);
    }
}
