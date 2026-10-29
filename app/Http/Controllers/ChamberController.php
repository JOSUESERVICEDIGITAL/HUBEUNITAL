<?php

namespace App\Http\Controllers;

use App\Models\Chamber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChamberController extends Controller
{
    public function index()
    {
        return view('back.chambers.index', ['chambers' => Chamber::orderBy('id')->get()]);
    }

    public function show(Chamber $chamber)
    {
        return view('back.chambers.show', compact('chamber'));
    }

    public function updateStatus(Request $request, Chamber $chamber)
    {
        abort_unless($request->user()->role === 'superadmin', 403);
        $data = $request->validate(['is_open' => 'required|boolean']);
        DB::transaction(function () use ($request, $chamber, $data) {
            $locked = Chamber::whereKey($chamber->id)->lockForUpdate()->firstOrFail();
            $before = $locked->only('is_open');
            $locked->update($data);
            DB::table('chamber_audits')->insert([
                'chamber_slug' => $chamber->slug,
                'user_id' => $request->user()->id, 'module' => 'chambers',
                'record_id' => $chamber->id, 'action' => $locked->is_open ? 'open' : 'close',
                'before' => json_encode($before), 'after' => json_encode($data), 'created_at' => now(),
            ]);
        });

        return back()->with('success', 'Le statut de la chambre a été mis à jour.');
    }
}
