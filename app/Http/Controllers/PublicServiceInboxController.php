<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PublicServiceInboxController extends Controller
{
    private const STATUSES = [
        'new' => 'Nouveau',
        'contacted' => 'Contacté',
        'in_progress' => 'En traitement',
        'quoted' => 'Devis transmis',
        'closed' => 'Clôturé',
    ];

    public function index(Request $request)
    {
        $query = DB::table('public_service_requests');

        if ($request->filled('q')) {
            $term = mb_substr($request->string('q')->toString(), 0, 150);
            $query->where(function ($search) use ($term) {
                $search->where('name', 'like', '%'.$term.'%')
                    ->orWhere('email', 'like', '%'.$term.'%')
                    ->orWhere('phone', 'like', '%'.$term.'%')
                    ->orWhere('service_name', 'like', '%'.$term.'%')
                    ->orWhere('details', 'like', '%'.$term.'%');
            });
        }

        if ($request->filled('chamber_slug')) {
            $query->where('chamber_slug', $request->input('chamber_slug'));
        }

        if ($request->filled('status') && array_key_exists($request->input('status'), self::STATUSES)) {
            $query->where('status', $request->input('status'));
        }

        $requests = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('back.public-services.index', [
            'requests' => $requests,
            'statuses' => self::STATUSES,
            'chambers' => config('public_services.chambers'),
            'counts' => [
                'all' => DB::table('public_service_requests')->count(),
                'new' => DB::table('public_service_requests')->where('status', 'new')->count(),
                'contacted' => DB::table('public_service_requests')->where('status', 'contacted')->count(),
                'quoted' => DB::table('public_service_requests')->where('status', 'quoted')->count(),
            ],
        ]);
    }

    public function show(int $requestId)
    {
        $serviceRequest = DB::table('public_service_requests')->find($requestId);
        abort_if(!$serviceRequest, 404);

        return view('back.public-services.show', [
            'serviceRequest' => $serviceRequest,
            'statuses' => self::STATUSES,
            'chambers' => config('public_services.chambers'),
        ]);
    }

    public function updateStatus(Request $request, int $requestId)
    {
        $serviceRequest = DB::table('public_service_requests')->find($requestId);
        abort_if(!$serviceRequest, 404);

        $data = $request->validate([
            'status' => ['required', 'string', Rule::in(array_keys(self::STATUSES))],
        ]);

        DB::table('public_service_requests')->where('id', $requestId)->update([
            'status' => $data['status'],
            'updated_at' => now(),
        ]);

        return redirect()->route('public-services.show', $requestId)
            ->with('success', 'Le statut de la demande a été mis à jour.');
    }
}
