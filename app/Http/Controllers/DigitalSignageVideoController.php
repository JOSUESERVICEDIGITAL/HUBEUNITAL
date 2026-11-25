<?php

namespace App\Http\Controllers;

use App\Models\DigitalSignageVideo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;

class DigitalSignageVideoController extends Controller
{
    public function index(): View
    {
        $videos = DigitalSignageVideo::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('back.digital-signage.index', compact('videos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'video' => [
                'required',
                'file',
                'mimetypes:video/mp4,video/webm',
                'extensions:mp4,webm',
                'max:102400',
            ],
            'sort_order' => ['nullable', 'integer', 'min:1', 'max:65535'],
        ]);

        $path = $request->file('video')->store('digital-signage/videos', 'public');

        if ($path === false) {
            throw new RuntimeException('La vidéo n’a pas pu être enregistrée.');
        }

        DigitalSignageVideo::create([
            'title' => $validated['title'],
            'file_path' => $path,
            'sort_order' => $validated['sort_order'] ?? ((int) DigitalSignageVideo::max('sort_order') + 1),
            'is_active' => true,
        ]);

        return back()->with('success', 'La vidéo a été ajoutée à la chaîne de diffusion.');
    }

    public function update(Request $request, DigitalSignageVideo $digitalSignageVideo): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'sort_order' => ['required', 'integer', 'min:1', 'max:65535'],
            'is_active' => ['required', 'boolean'],
        ]);

        $digitalSignageVideo->update($validated);

        return back()->with('success', 'Les paramètres de la vidéo ont été mis à jour.');
    }

    public function destroy(DigitalSignageVideo $digitalSignageVideo): RedirectResponse
    {
        $disk = Storage::disk('public');

        if ($disk->exists($digitalSignageVideo->file_path) && ! $disk->delete($digitalSignageVideo->file_path)) {
            throw new RuntimeException('Le fichier vidéo n’a pas pu être supprimé.');
        }

        $digitalSignageVideo->delete();

        return back()->with('success', 'La vidéo a été supprimée.');
    }
}
