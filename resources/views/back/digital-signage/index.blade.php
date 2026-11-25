@extends('back.app')

@section('title', 'Affichage publicitaire numérique')

@section('dashboard-header')
    <h1 class="page-title">Affichage publicitaire numérique</h1>
@endsection

@section('dashboard-content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0">Ajouter une vidéo à la chaîne</h2>
        </div>
        <div class="card-body">
            <p class="text-muted">Formats acceptés : MP4 et WebM. Taille maximale : 100 Mo.</p>
            <form action="{{ route('signage-videos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row align-items-end">
                    <div class="col-md-5 mb-3">
                        <label for="video-title" class="form-label">Titre</label>
                        <input
                            id="video-title"
                            class="form-control"
                            type="text"
                            name="title"
                            maxlength="120"
                            value="{{ old('title') }}"
                            required
                        >
                    </div>
                    <div class="col-md-5 mb-3">
                        <label for="video-file" class="form-label">Fichier vidéo</label>
                        <input
                            id="video-file"
                            class="form-control"
                            type="file"
                            name="video"
                            accept=".mp4,.webm,video/mp4,video/webm"
                            required
                        >
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="video-sort-order" class="form-label">Ordre</label>
                        <input
                            id="video-sort-order"
                            class="form-control"
                            type="number"
                            name="sort_order"
                            min="1"
                            max="65535"
                            value="{{ old('sort_order') }}"
                            placeholder="Auto"
                        >
                    </div>
                </div>
                <button class="btn btn-primary" type="submit">Ajouter à la diffusion</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="h5 mb-0">Vidéos de la chaîne</h2>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Ordre</th>
                        <th scope="col">Titre</th>
                        <th scope="col">Aperçu</th>
                        <th scope="col">Diffusion</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($videos as $video)
                        <tr>
                            <td>
                                <form
                                    id="update-video-{{ $video->id }}"
                                    action="{{ route('signage-videos.update', $video) }}"
                                    method="POST"
                                >
                                    @csrf
                                    @method('PUT')
                                </form>
                                <input
                                    form="update-video-{{ $video->id }}"
                                    class="form-control"
                                    type="number"
                                    name="sort_order"
                                    min="1"
                                    max="65535"
                                    value="{{ $video->sort_order }}"
                                    aria-label="Ordre de {{ $video->title }}"
                                    required
                                >
                            </td>
                            <td>
                                <input
                                    form="update-video-{{ $video->id }}"
                                    class="form-control"
                                    type="text"
                                    name="title"
                                    maxlength="120"
                                    value="{{ $video->title }}"
                                    aria-label="Titre de la vidéo"
                                    required
                                >
                            </td>
                            <td>
                                <video
                                    class="digital-signage-preview"
                                    src="{{ asset('storage/' . $video->file_path) }}"
                                    controls
                                    preload="metadata"
                                    aria-label="Aperçu : {{ $video->title }}"
                                ></video>
                            </td>
                            <td>
                                <input
                                    form="update-video-{{ $video->id }}"
                                    type="hidden"
                                    name="is_active"
                                    value="0"
                                >
                                <div class="form-check">
                                    <input
                                        form="update-video-{{ $video->id }}"
                                        class="form-check-input"
                                        type="checkbox"
                                        name="is_active"
                                        value="1"
                                        id="video-active-{{ $video->id }}"
                                        @checked($video->is_active)
                                    >
                                    <label class="form-check-label" for="video-active-{{ $video->id }}">
                                        Active
                                    </label>
                                </div>
                            </td>
                            <td class="text-nowrap">
                                <button
                                    form="update-video-{{ $video->id }}"
                                    class="btn btn-sm btn-primary"
                                    type="submit"
                                >
                                    Enregistrer
                                </button>
                                <form
                                    action="{{ route('signage-videos.destroy', $video) }}"
                                    method="POST"
                                    class="d-inline"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">
                                        Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                Aucune vidéo. Ajoutez-en une pour démarrer la diffusion.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
