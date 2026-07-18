<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'VOP — Études Bibliques par Correspondance')</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/spiritual-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vop-theme.css') }}">
    @stack('head')
</head>
<body>
    @yield('content')

    @include('partials.footer')

    @stack('scripts')
</body>
</html>
