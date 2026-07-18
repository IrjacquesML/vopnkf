@extends('layouts.admin')

@section('title', 'Certificats — VOP Admin')

@section('content')
    <div class="container admin-container">
        @include('partials.alerts')

        @if ($mode === 'certificat')
            @php
                [$day, $month, $year] = array_pad(explode('/', $dateCertificat), 3, '');
                $nomComplet = trim($user->prenom.' '.mb_strtoupper($user->nom));
            @endphp

            <div class="admin-header no-print">
                <div class="page-kicker">
                    @include('partials.icon', ['name' => 'certificate', 'class' => 'vop-icon-gold'])
                    Distinction
                </div>
                <h1>Certificat de Participation</h1>
                <div class="admin-toolbar">
                    <button type="button" onclick="window.print()" class="btn btn-primary">Imprimer</button>
                    <a href="{{ route('admin.settings.certificate') }}" class="btn btn-secondary">Modifier les signataires</a>
                    <a href="{{ route('admin.reports.certificat') }}" class="btn btn-secondary">← Retour à la liste</a>
                </div>
            </div>

            <div id="cert-wrap" class="certificat-print">
                <div class="cert-side-bar"></div>
                <div class="cert-bottom-bar"></div>

                <svg class="cert-dots" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <circle cx="40" cy="160" r="38" fill="#e65100" opacity=".75"/>
                    <circle cx="80" cy="145" r="28" fill="#00838f" opacity=".7"/>
                    <circle cx="15" cy="120" r="22" fill="#2e7d32" opacity=".6"/>
                    <circle cx="105" cy="170" r="18" fill="#00838f" opacity=".5"/>
                    <circle cx="60" cy="185" r="14" fill="#e65100" opacity=".5"/>
                </svg>

                <div class="cert-content">
                    <div class="cert-header">
                        <div class="cert-org-block">
                            <div class="cert-org-line1">Église Adventiste du 7ème jour</div>
                            <div class="cert-org-line2">Association du Nord-Kivu</div>
                            <div class="cert-org-line3">Département de la Voix de l'Espérance</div>
                        </div>
                        <div class="cert-logo">
                            <img src="{{ asset('img/logo-adventiste.jpg') }}" alt="Logo Adventiste">
                        </div>
                    </div>

                    <div class="cert-divider"></div>

                    <div class="cert-title">Certificat de Participation</div>
                    <div class="cert-subtitle">Études Bibliques par Correspondance</div>

                    <div class="cert-body">
                        <p>Le présent document atteste que</p>
                        <p class="cert-name-wrap">
                            <span class="cert-name">{{ $nomComplet }}</span>
                        </p>
                        <p>
                            a suivi avec succès les cours par correspondance sur les doctrines bibliques,
                            en foi de quoi le présent certificat lui est délivré tout en lui souhaitant
                            une bonne application des vérités étudiées.
                        </p>
                    </div>

                    <div class="cert-date">
                        Fait à Butembo, le&nbsp;<u>{{ $day }}</u>
                        &nbsp;/&nbsp;<u>{{ $month }}</u>
                        &nbsp;/&nbsp;<u>{{ $year }}</u>
                    </div>

                    <div class="cert-signatures">
                        @foreach ($signatories as $signatory)
                            <div class="cert-sig">
                                <div class="sig-role">{{ $signatory['role'] }}</div>
                                <div class="sig-nom">{{ $signatory['nom'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="admin-header">
                <div class="page-kicker">
                    @include('partials.icon', ['name' => 'certificate', 'class' => 'vop-icon-gold'])
                    Distinction
                </div>
                <h1>Certificats de Participation</h1>
                <p>Sélectionnez un participant pour générer son certificat</p>
                <div class="admin-toolbar">
                    <a href="{{ route('admin.settings.certificate') }}" class="btn btn-secondary">
                        @include('partials.icon', ['name' => 'user']) Pré-enregistrer les signataires
                    </a>
                </div>
            </div>

            <div class="admin-section">
                <p><strong>{{ $totalLessons }}</strong> leçon(s) au total dans le programme</p>

                @if ($users->isNotEmpty())
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Participant</th>
                                    <th>Email</th>
                                    <th>Ville</th>
                                    <th>Leçons terminées</th>
                                    <th>Score moyen</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $entry)
                                    <tr>
                                        <td>{{ $entry->prenom }} {{ $entry->nom }}</td>
                                        <td>{{ $entry->email }}</td>
                                        <td>{{ $entry->ville ?? '—' }}</td>
                                        <td>{{ $entry->nb_lecons_terminees }} / {{ $totalLessons }}</td>
                                        <td>{{ $entry->score_moyen ? number_format($entry->score_moyen, 1) . '%' : '—' }}</td>
                                        <td>
                                            @if ($entry->eligible)
                                                <span class="badge badge-success">Complet</span>
                                            @else
                                                <span class="badge badge-warning">Partiel</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.reports.certificat', ['user_id' => $entry->id]) }}" class="btn btn-small btn-primary">Certificat</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">Aucun participant éligible pour le moment.</p>
                @endif
            </div>
        @endif
    </div>
@endsection

@push('head')
    <style>
        #cert-wrap {
            width: 100%;
            max-width: 297mm;
            min-height: 210mm;
            margin: 1.5rem auto;
            background: #f5f5f0;
            position: relative;
            overflow: hidden;
            border-radius: 4px;
            box-shadow: 0 8px 40px rgba(0,0,0,.18);
            font-family: 'Palatino Linotype', 'Book Antiqua', Palatino, Georgia, serif;
        }
        #cert-wrap::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 120% 80% at 30% 40%, rgba(200,195,180,.35) 0%, transparent 70%),
                radial-gradient(ellipse 80% 60% at 70% 60%, rgba(180,175,160,.25) 0%, transparent 70%);
            pointer-events: none;
        }
        .cert-side-bar {
            position: absolute;
            top: 0; right: 0; bottom: 0;
            width: 42mm;
            background: linear-gradient(180deg, #00838f 0%, #006064 100%);
            z-index: 1;
        }
        .cert-side-bar::before {
            content: '';
            position: absolute;
            inset: 0;
            background: repeating-linear-gradient(
                -45deg,
                transparent,
                transparent 6px,
                rgba(255,255,255,.06) 6px,
                rgba(255,255,255,.06) 12px
            );
        }
        .cert-bottom-bar {
            position: absolute;
            bottom: 0; right: 0;
            width: 42mm;
            height: 18mm;
            background: #2e7d32;
            z-index: 2;
        }
        .cert-dots {
            position: absolute;
            bottom: -10mm;
            left: -10mm;
            width: 80mm;
            height: 80mm;
            z-index: 1;
        }
        .cert-content {
            position: relative;
            z-index: 3;
            padding: 12mm 52mm 14mm 16mm;
            min-height: 210mm;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }
        .cert-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8mm;
            gap: 12px;
        }
        .cert-org-line1 {
            font-size: 10.5pt;
            font-weight: 700;
            color: #1a1a1a;
            text-transform: uppercase;
            letter-spacing: .04em;
            line-height: 1.3;
        }
        .cert-org-line2 {
            font-size: 9pt;
            color: #444;
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .cert-org-line3 {
            font-size: 8.5pt;
            color: #00838f;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 3px;
        }
        .cert-logo {
            width: 22mm;
            height: 22mm;
            flex-shrink: 0;
        }
        .cert-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .cert-divider {
            height: 3px;
            background: linear-gradient(90deg, #00838f, #2e7d32, #00838f);
            margin: 0 0 8mm;
            border-radius: 2px;
        }
        .cert-title {
            text-align: center;
            font-size: 26pt;
            font-weight: 700;
            color: #00838f;
            letter-spacing: .06em;
            text-transform: uppercase;
            margin-bottom: 3mm;
            line-height: 1.1;
        }
        .cert-subtitle {
            text-align: center;
            font-size: 9pt;
            color: #888;
            letter-spacing: .25em;
            text-transform: uppercase;
            margin-bottom: 8mm;
        }
        .cert-body {
            font-size: 12.5pt;
            color: #1a1a1a;
            line-height: 1.9;
            text-align: justify;
            flex: 1;
        }
        .cert-name-wrap {
            text-align: center;
            margin: 4mm 0;
        }
        .cert-name {
            font-size: 16pt;
            font-weight: 700;
            color: #1a237e;
            border-bottom: 2px solid #1a237e;
            padding: 0 6px;
            display: inline-block;
        }
        .cert-date {
            font-size: 10.5pt;
            color: #444;
            margin-top: 8mm;
        }
        .cert-signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 10mm;
            gap: 6mm;
        }
        .cert-sig {
            flex: 1;
            text-align: center;
            border-top: 1.5px solid #00838f;
            padding-top: 4mm;
        }
        .cert-sig .sig-role {
            font-size: 8pt;
            color: #555;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: 2mm;
        }
        .cert-sig .sig-nom {
            font-size: 9pt;
            font-weight: 700;
            color: #1a1a1a;
        }

        @media print {
            @page { size: A4 landscape; margin: 0; }
            body, html { margin: 0 !important; padding: 0 !important; background: #f5f5f0 !important; }
            .navbar, .admin-footer, .admin-header, .no-print, .footer { display: none !important; }
            body { padding-top: 0 !important; }
            .admin-container, .container {
                max-width: none !important;
                margin: 0 !important;
                padding: 0 !important;
                background: transparent !important;
                box-shadow: none !important;
                border: none !important;
            }
            #cert-wrap {
                width: 297mm !important;
                max-width: none !important;
                min-height: 210mm !important;
                margin: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
        }

        @media (max-width: 900px) {
            .cert-content { padding-right: 18mm; }
            .cert-side-bar, .cert-bottom-bar { width: 12mm; }
            .cert-title { font-size: 20pt; }
            .cert-signatures { flex-direction: column; }
        }
    </style>
@endpush
