@extends('layouts.admin')

@section('title', 'Statistiques — VOP Admin')

@section('content')
    <div class="container admin-container">
        @include('partials.alerts')

        <div class="admin-header">
            <div class="page-kicker">
                @include('partials.icon', ['name' => 'chart', 'class' => 'vop-icon-gold'])
                Analytique
            </div>
            <h1>Statistiques et Rapports</h1>
            <p>Vue d'ensemble visuelle de la plateforme VOP</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-icon icon-badge">@include('partials.icon', ['name' => 'users'])</div>
                <div class="stat-info">
                    <h3>{{ $totals['users'] }}</h3>
                    <p>Participants</p>
                </div>
            </div>
            <div class="stat-card success">
                <div class="stat-icon icon-badge">@include('partials.icon', ['name' => 'bible'])</div>
                <div class="stat-info">
                    <h3>{{ $totals['lessons'] }}</h3>
                    <p>Leçons</p>
                </div>
            </div>
            <div class="stat-card info">
                <div class="stat-icon icon-badge">@include('partials.icon', ['name' => 'check'])</div>
                <div class="stat-info">
                    <h3>{{ $totals['completed'] }}</h3>
                    <p>Leçons complétées</p>
                </div>
            </div>
            <div class="stat-card warning">
                <div class="stat-icon icon-badge">@include('partials.icon', ['name' => 'chart'])</div>
                <div class="stat-info">
                    <h3>{{ number_format($totals['score_moyen'], 1) }}%</h3>
                    <p>Score moyen</p>
                </div>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-card chart-wide">
                <div class="chart-card-header">
                    <h2>Activité sur 12 mois</h2>
                    <p>Inscriptions et leçons terminées</p>
                </div>
                <div class="chart-canvas-wrap">
                    <canvas id="chartActivity" height="110"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-card-header">
                    <h2>Progressions</h2>
                    <p>Répartition par statut</p>
                </div>
                <div class="chart-canvas-wrap chart-canvas-sm">
                    <canvas id="chartProgressions"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-card-header">
                    <h2>Utilisateurs</h2>
                    <p>Répartition par rôle</p>
                </div>
                <div class="chart-canvas-wrap chart-canvas-sm">
                    <canvas id="chartRoles"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-card-header">
                    <h2>Scores par catégorie</h2>
                    <p>Moyenne des participants</p>
                </div>
                <div class="chart-canvas-wrap">
                    <canvas id="chartScores" height="160"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-card-header">
                    <h2>Demandes de prière</h2>
                    <p>Répartition par statut</p>
                </div>
                <div class="chart-canvas-wrap chart-canvas-sm">
                    <canvas id="chartPrayers"></canvas>
                </div>
            </div>

            <div class="chart-card chart-wide">
                <div class="chart-card-header">
                    <h2>Leçons les plus suivies</h2>
                    <p>Nombre de complétions</p>
                </div>
                <div class="chart-canvas-wrap">
                    <canvas id="chartTopLessons" height="120"></canvas>
                </div>
            </div>
        </div>

        <div class="admin-sections">
            <div class="admin-section">
                <h2>Utilisateurs par rôle</h2>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Rôle</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($countsByRole as $role => $total)
                                <tr>
                                    <td>{{ $role === 'utilisateur' ? 'Participants' : ($role === 'admin' ? 'Administrateurs' : $role) }}</td>
                                    <td>{{ $total }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-muted">Aucune donnée</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="admin-section">
                <h2>Progressions par statut</h2>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Statut</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($progressionsByStatut as $statut => $total)
                                <tr>
                                    <td>{{ $statut }}</td>
                                    <td>{{ $total }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-muted">Aucune donnée</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="admin-section">
            <h2>Scores moyens</h2>
            <p><strong>Score moyen global :</strong> {{ number_format($avgScores['global'] ?? 0, 1) }}%</p>

            @if ($avgScores['by_categorie']->isNotEmpty())
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Catégorie</th>
                                <th>Score moyen</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($avgScores['by_categorie'] as $row)
                                <tr>
                                    <td>{{ $row->categorie }}</td>
                                    <td>{{ number_format($row->score_moyen, 1) }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">Aucun score enregistré par catégorie.</p>
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
            margin: 1.5rem 0 2rem;
        }
        .chart-card {
            background: #fff;
            border: 1px solid rgba(201, 162, 39, 0.22);
            border-radius: 14px;
            padding: 1.15rem 1.25rem 1.35rem;
            box-shadow: 0 10px 28px rgba(27, 67, 50, 0.07);
        }
        .chart-wide { grid-column: 1 / -1; }
        .chart-card-header h2 {
            margin: 0;
            font-family: var(--font-display, Georgia, serif);
            font-size: 1.25rem;
            color: var(--vert-profond, #1b4332);
        }
        .chart-card-header p {
            margin: .25rem 0 0;
            color: #667;
            font-size: .88rem;
        }
        .chart-canvas-wrap { position: relative; margin-top: 1rem; }
        .chart-canvas-sm { max-width: 320px; margin-left: auto; margin-right: auto; }
        @media (max-width: 900px) {
            .charts-grid { grid-template-columns: 1fr; }
            .chart-wide { grid-column: auto; }
        }
        @media print {
            .charts-grid { break-inside: avoid; }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
    <script>
        const chartsData = @json($charts);
        const palette = {
            green: '#2d6a4f',
            greenSoft: 'rgba(45, 106, 79, 0.18)',
            teal: '#00838f',
            gold: '#c9a227',
            navy: '#1a365d',
            orange: '#e65100',
            sage: '#40916c',
            muted: '#90a4ae',
        };

        const doughnutColors = [palette.green, palette.teal, palette.gold, palette.navy, palette.orange, palette.sage];

        Chart.defaults.font.family = "'Source Sans 3', 'Lato', system-ui, sans-serif";
        Chart.defaults.color = '#52606d';
        Chart.defaults.plugins.legend.labels.usePointStyle = true;
        Chart.defaults.plugins.legend.labels.boxWidth = 8;

        function emptyFallback(chart, labels, data) {
            if (labels.length && data.some(v => Number(v) > 0)) return { labels, data };
            return { labels: ['Aucune donnée'], data: [1] };
        }

        const activityCtx = document.getElementById('chartActivity');
        if (activityCtx) {
            new Chart(activityCtx, {
                type: 'line',
                data: {
                    labels: chartsData.completions.labels,
                    datasets: [
                        {
                            label: 'Leçons terminées',
                            data: chartsData.completions.data,
                            borderColor: palette.green,
                            backgroundColor: palette.greenSoft,
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2.5,
                            pointRadius: 3,
                            pointBackgroundColor: palette.green,
                        },
                        {
                            label: 'Inscriptions',
                            data: chartsData.inscriptions.data,
                            borderColor: palette.gold,
                            backgroundColor: 'rgba(201, 162, 39, 0.12)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2.5,
                            pointRadius: 3,
                            pointBackgroundColor: palette.gold,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: { callbacks: { label: (ctx) => ` ${ctx.dataset.label}: ${ctx.parsed.y}` } },
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                        x: { grid: { display: false } },
                    },
                },
            });
        }

        const prog = emptyFallback(null, chartsData.progressions.labels, chartsData.progressions.data);
        new Chart(document.getElementById('chartProgressions'), {
            type: 'doughnut',
            data: {
                labels: prog.labels,
                datasets: [{
                    data: prog.data,
                    backgroundColor: doughnutColors,
                    borderWidth: 0,
                    hoverOffset: 6,
                }],
            },
            options: {
                cutout: '62%',
                plugins: {
                    legend: { position: 'bottom' },
                },
            },
        });

        const roles = emptyFallback(null, chartsData.roles.labels, chartsData.roles.data);
        new Chart(document.getElementById('chartRoles'), {
            type: 'doughnut',
            data: {
                labels: roles.labels,
                datasets: [{
                    data: roles.data,
                    backgroundColor: [palette.green, palette.navy, palette.teal],
                    borderWidth: 0,
                    hoverOffset: 6,
                }],
            },
            options: {
                cutout: '62%',
                plugins: { legend: { position: 'bottom' } },
            },
        });

        new Chart(document.getElementById('chartScores'), {
            type: 'bar',
            data: {
                labels: chartsData.scores.labels.length ? chartsData.scores.labels : ['Aucune catégorie'],
                datasets: [{
                    label: 'Score moyen (%)',
                    data: chartsData.scores.data.length ? chartsData.scores.data : [0],
                    backgroundColor: palette.teal,
                    borderRadius: 8,
                    maxBarThickness: 42,
                }],
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, max: 100, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } },
                },
            },
        });

        const prayers = emptyFallback(null, chartsData.prayers.labels, chartsData.prayers.data);
        new Chart(document.getElementById('chartPrayers'), {
            type: 'pie',
            data: {
                labels: prayers.labels,
                datasets: [{
                    data: prayers.data,
                    backgroundColor: [palette.gold, palette.teal, palette.green, palette.muted],
                    borderWidth: 0,
                }],
            },
            options: {
                plugins: { legend: { position: 'bottom' } },
            },
        });

        new Chart(document.getElementById('chartTopLessons'), {
            type: 'bar',
            data: {
                labels: chartsData.topLessons.labels.length ? chartsData.topLessons.labels : ['Aucune leçon'],
                datasets: [{
                    label: 'Complétions',
                    data: chartsData.topLessons.data.length ? chartsData.topLessons.data : [0],
                    backgroundColor: palette.green,
                    borderRadius: 8,
                    maxBarThickness: 36,
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
    </script>
@endpush
