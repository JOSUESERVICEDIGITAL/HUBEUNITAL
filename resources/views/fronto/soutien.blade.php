
@extends('fronto.app')

@section('title', ' Soutenir EUNITAL')

@section('main_section')
<div class="container py-5">
        <div class="text-center mb-5">
            <h1 class="display-4 text-primary">{{ $cei_support->titre ?? 'Soutenir le Comité d’Entraide Internationale' }}</h1>
            <p class="lead">{{ $cei_support->slogan ?? 'Ensemble, apportons aide, dignité et espoir à ceux qui en ont besoin.' }}</p>
        </div>

        <div class="row align-items-center mb-5">
                <div class="col-md-6 text-center">
                    <img src="{{ asset('img/logoeunital.png') }}" alt="Logo EUNITAL" style="max-height: 200px;">
                </div>
                <div class="col-md-6">
                    <h2 class="text-dark">{{ $cei_support->question ?? 'Pourquoi soutenir EUNITAL ?' }}</h2>
                    <p>{!! nl2br(e($cei_support->introduction ?? '')) !!}</p>
                    <ul>
                        @for ($i = 1; $i <= 4; $i++)
                            @php $raison = 'raison_'.$i; @endphp
                            @if (!empty($cei_support->$raison))
                                <li>{{ $cei_support->$raison }}</li>
                            @endif
                        @endfor
                    </ul>
                </div>
        </div>

            <div class="bg-light rounded p-4 mb-5">
            <h3 class="text-center text-success">{{ $cei_support->action_title ?? 'Comment nous aider ?' }}</h3>
                        <div class="row mt-4">
                            @for ($i = 1; $i <= 3; $i++)
                                @php
                                    $icon = 'action'.$i.'_icon';
                                    $title = 'action'.$i.'_title';
                                    $text = 'action'.$i.'_text';
                                    $url  = 'action'.$i.'_url';
                                @endphp
                                @if (!empty($cei_support->$title))
                                    <div class="col-md-4 mb-4">
                                        <a href="{{ $cei_support->$url ?? '#' }}" class="text-decoration-none text-dark">
                                            <div class="action-card text-center h-100">
                                                @if (!empty($cei_support->$icon))
                                                    <i class="{{ $cei_support->$icon }} fa-3x mb-3 text-primary"></i>
                                                @endif
                                                <h5>{{ $cei_support->$title }}</h5>
                                                <p>{{ $cei_support->$text }}</p>
                                            </div>
                                        </a>
                                    </div>

                                @endif
                            @endfor
                        </div>
            </div>


               <!-- @if (!empty($cei_support->social_url))
    <div class="text-center">
        <a href="{{ $cei_support->social_url }}"
           onclick="window.open(this.href, 'popupSocial', 'width=480,height=600,scrollbars=no'); return false;"
           class="btn btn-primary btn-lg">
            @if (!empty($cei_support->social_icon))
                <i class="{{ $cei_support->social_icon }} me-2"></i>
            @endif
            {{ $cei_support->social_name ?? 'Suivre EUNITAL' }}
        </a>
    </div>
@endif -->

</div>
@endsection
