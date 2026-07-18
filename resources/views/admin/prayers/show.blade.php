@extends('layouts.admin')

@section('title', 'Détails Prière — VOP Admin')

@section('content')
    <div class="container admin-container">
        @include('partials.alerts')

        <div class="admin-header">
            <h1>🙏 Détails de la Demande de Prière</h1>
            <a href="{{ route('admin.prayers.index') }}" class="btn btn-secondary">← Retour à la liste</a>
        </div>

        <div class="prayer-detail-section">
            <div class="prayer-info-card admin-section">
                <div class="prayer-header-info">
                    <h2>{{ $prayer->sujet }}</h2>
                    @switch($prayer->statut)
                        @case('en_attente')
                            <span class="badge badge-warning badge-large">⏳ En attente</span>
                            @break
                        @case('en_priere')
                            <span class="badge badge-info badge-large">🙏 En prière</span>
                            @break
                        @case('exaucee')
                            <span class="badge badge-success badge-large">✅ Exaucée</span>
                            @break
                    @endswitch
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <strong>Date:</strong> {{ $prayer->created_at?->format('d/m/Y à H:i') }}
                    </div>
                    <div class="info-item">
                        <strong>Anonyme:</strong> {{ $prayer->est_anonyme ? 'Oui' : 'Non' }}
                    </div>
                    @if (! $prayer->est_anonyme && $prayer->user)
                        <div class="info-item">
                            <strong>Utilisateur:</strong> {{ $prayer->user->prenom }} {{ $prayer->user->nom }}
                        </div>
                        <div class="info-item">
                            <strong>Email:</strong> {{ $prayer->user->email }}
                        </div>
                    @endif
                </div>

                <div class="prayer-message" style="margin-top: 20px;">
                    <h3>Message</h3>
                    <p>{!! nl2br(e($prayer->message)) !!}</p>
                </div>
            </div>

            <div class="admin-section">
                <h2>Modifier le statut</h2>
                <form method="POST" action="{{ route('admin.prayers.updateStatus', $prayer) }}" class="admin-form">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="statut">Statut</label>
                        <select name="statut" id="statut" class="form-control" required>
                            <option value="en_attente" @selected($prayer->statut === 'en_attente')>En attente</option>
                            <option value="en_priere" @selected($prayer->statut === 'en_priere')>En prière</option>
                            <option value="exaucee" @selected($prayer->statut === 'exaucee')>Exaucée</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Mettre à jour le statut</button>
                </form>
            </div>

            <div class="admin-section">
                <form action="{{ route('admin.prayers.destroy', $prayer) }}" method="POST" onsubmit="return confirm('Supprimer cette demande de prière ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">🗑 Supprimer la demande</button>
                </form>
            </div>
        </div>
    </div>
@endsection
