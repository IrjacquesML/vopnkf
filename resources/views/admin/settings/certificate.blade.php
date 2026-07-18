@extends('layouts.admin')

@section('title', 'Signataires du certificat — VOP Admin')

@section('content')
    <div class="container admin-container">
        @include('partials.alerts')

        <div class="admin-header">
            <div class="page-kicker">
                @include('partials.icon', ['name' => 'certificate', 'class' => 'vop-icon-gold'])
                Paramètres
            </div>
            <h1>Signataires du certificat</h1>
            <p>Pré-enregistrez les noms et titres qui apparaîtront sur chaque certificat imprimé.</p>
        </div>

        <div class="admin-section">
            <form method="POST" action="{{ route('admin.settings.certificate.update') }}" class="settings-form">
                @csrf
                @method('PUT')

                @foreach ($signatories as $index => $signatory)
                    <fieldset class="signatory-card">
                        <legend>Signataire {{ $index + 1 }}</legend>

                        <div class="form-group">
                            <label for="role_{{ $index }}">Titre / fonction</label>
                            <input
                                type="text"
                                id="role_{{ $index }}"
                                name="signatories[{{ $index }}][role]"
                                value="{{ old("signatories.$index.role", $signatory['role']) }}"
                                required
                                maxlength="150"
                            >
                            @error("signatories.$index.role")
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="nom_{{ $index }}">Nom complet</label>
                            <input
                                type="text"
                                id="nom_{{ $index }}"
                                name="signatories[{{ $index }}][nom]"
                                value="{{ old("signatories.$index.nom", $signatory['nom']) }}"
                                required
                                maxlength="120"
                            >
                            @error("signatories.$index.nom")
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </fieldset>
                @endforeach

                <div class="admin-toolbar" style="margin-top:1.25rem;">
                    <button type="submit" class="btn btn-primary">
                        @include('partials.icon', ['name' => 'check']) Enregistrer les signataires
                    </button>
                    <a href="{{ route('admin.reports.certificat') }}" class="btn btn-secondary">Voir les certificats</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('head')
    <style>
        .signatory-card {
            border: 1px solid rgba(201, 162, 39, 0.28);
            border-radius: 12px;
            padding: 1.25rem 1.35rem 0.5rem;
            margin-bottom: 1.1rem;
            background: linear-gradient(180deg, #fff, #f7f4ef);
        }
        .signatory-card legend {
            font-family: var(--font-display, Georgia, serif);
            font-weight: 700;
            color: var(--vert-profond, #1b4332);
            padding: 0 .5rem;
            font-size: 1.1rem;
        }
        .text-danger { color: #c62828; display:block; margin-top:.35rem; }
    </style>
@endpush
