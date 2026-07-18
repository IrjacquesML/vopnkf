@extends('layouts.admin')

@section('title', 'Palmarès — VOP Admin')

@section('content')
    <div class="container admin-container">
        @include('partials.alerts')

        <div class="admin-header no-print">
            <div class="page-kicker">
                @include('partials.icon', ['name' => 'trophy', 'class' => 'vop-icon-gold'])
                Excellence
            </div>
            <h1>Palmarès des participants</h1>
            <p>Classement par leçons terminées et score moyen</p>
        </div>

        <div class="admin-toolbar no-print">
            <form method="GET" action="{{ route('admin.reports.palmares') }}" class="filter-form">
                <select name="categorie_id" class="filter-select">
                    <option value="">Toutes les catégories</option>
                    @foreach ($categories as $categorie)
                        <option value="{{ $categorie->id }}" @selected($categorieId == $categorie->id)>{{ $categorie->nom }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary">Filtrer</button>
                @if ($categorieId)
                    <a href="{{ route('admin.reports.palmares') }}" class="btn btn-secondary">✖ Effacer</a>
                @endif
            </form>
            <button type="button" onclick="window.print()" class="btn btn-primary">🖨️ Imprimer</button>
        </div>

        <div class="admin-section palmares-print">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom: 1.25rem; flex-wrap:wrap;">
                <div style="display:flex; align-items:center; gap:16px;">
                    <img src="{{ asset('img/logo-adventiste.jpg') }}" alt="Logo Adventiste" style="width:72px; height:72px; object-fit:contain; flex-shrink:0;">
                    <div>
                        <div style="font-size:.8rem; letter-spacing:.04em; color:#555; text-transform:uppercase;">
                            Église Adventiste du 7ème jour — Association du Nord-Kivu
                        </div>
                        <div style="font-weight:700; color:var(--vert-foret, #1b5e20);">
                            Département de la Voix de l'Espérance
                        </div>
                        <h2 style="margin:.35rem 0 0; font-size:1.45rem;">Palmarès des Participants</h2>
                        <div style="font-size:.9rem; color:#666;">
                            {{ $categorieId ? 'Catégorie filtrée' : 'Toutes catégories' }}
                            · {{ $palmares->count() }} participant(s)
                            · {{ $totalLessons }} leçon(s)
                        </div>
                    </div>
                </div>
            </div>

            <div class="charts-grid no-print" style="margin: 0 0 1.5rem;">
                <div class="chart-card">
                    <div class="chart-card-header">
                        <h2>Top 10 — Leçons terminées</h2>
                        <p>Classement des meilleurs participants</p>
                    </div>
                    <div class="chart-canvas-wrap">
                        <canvas id="chartPalmaresLecons" height="180"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <div class="chart-card-header">
                        <h2>Top 10 — Scores moyens</h2>
                        <p>Performance académique</p>
                    </div>
                    <div class="chart-canvas-wrap">
                        <canvas id="chartPalmaresScores" height="180"></canvas>
                    </div>
                </div>
            </div>

            @if ($palmares->isNotEmpty())
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Participant</th>
                                <th>Email</th>
                                <th>Ville</th>
                                <th>Leçons terminées</th>
                                <th>Taux</th>
                                <th>Score moyen</th>
                                <th>Dernière complétion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($palmares as $index => $entry)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $entry->prenom }} {{ $entry->nom }}</td>
                                    <td>{{ $entry->email }}</td>
                                    <td>{{ $entry->ville ?? '—' }}</td>
                                    <td>{{ $entry->nb_lecons_terminees }} / {{ $entry->total_lecons }}</td>
                                    <td>{{ $entry->taux_completion }}%</td>
                                    <td>{{ number_format($entry->score_moyen, 1) }}%</td>
                                    <td>
                                        @if ($entry->derniere_completion)
                                            {{ \Carbon\Carbon::parse($entry->derniere_completion)->format('d/m/Y') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">Aucun participant avec des leçons terminées.</p>
            @endif
        </div>
    </div>
@endsection

@push('head')
    <style>
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.25rem;
        }
        .chart-card {
            background: #fff;
            border: 1px solid rgba(201, 162, 39, 0.22);
            border-radius: 14px;
            padding: 1.15rem 1.25rem 1.35rem;
            box-shadow: 0 10px 28px rgba(27, 67, 50, 0.07);
        }
        .chart-card-header h2 {
            margin: 0;
            font-family: var(--font-display, Georgia, serif);
            font-size: 1.2rem;
            color: var(--vert-profond, #1b4332);
        }
        .chart-card-header p {
            margin: .25rem 0 0;
            color: #667;
            font-size: .88rem;
        }
        .chart-canvas-wrap { margin-top: 1rem; }
        @media (max-width: 900px) {
            .charts-grid { grid-template-columns: 1fr; }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
    <script>
        const chartsData = @json($charts);
        Chart.defaults.font.family = "'Source Sans 3', 'Lato', system-ui, sans-serif";
        Chart.defaults.color = '#52606d';

        new Chart(document.getElementById('chartPalmaresLecons'), {
            type: 'bar',
            data: {
                labels: chartsData.lecons.labels.length ? chartsData.lecons.labels : ['Aucun participant'],
                datasets: [{
                    label: 'Leçons terminées',
                    data: chartsData.lecons.data.length ? chartsData.lecons.data : [0],
                    backgroundColor: '#2d6a4f',
                    borderRadius: 8,
                    maxBarThickness: 34,
                }],
            },
            options: {
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                    y: { grid: { display: false } },
                },
            },
        });

        new Chart(document.getElementById('chartPalmaresScores'), {
            type: 'bar',
            data: {
                labels: chartsData.scores.labels.length ? chartsData.scores.labels : ['Aucun participant'],
                datasets: [{
                    label: 'Score moyen (%)',
                    data: chartsData.scores.data.length ? chartsData.scores.data : [0],
                    backgroundColor: '#00838f',
                    borderRadius: 8,
                    maxBarThickness: 34,
                }],
            },
            options: {
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, max: 100, grid: { color: 'rgba(0,0,0,0.05)' } },
                    y: { grid: { display: false } },
                },
            },
        });
    </script>
@endpush
