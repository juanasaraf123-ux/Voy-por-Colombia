<!doctype html>
<html lang="es-CO" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0b3d2e">
    <meta name="vpc-base" content="{{ rtrim((string) parse_url(url('/'), PHP_URL_PATH), '/') }}">
    <link rel="icon" href="{{ asset('assets/img/favicon.svg') }}" type="image/svg+xml">
    <link rel="preload" href="{{ asset('assets/fonts/kaushan-script-latin-400-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
    @php($admin = str_starts_with($page['component'], 'Admin/'))
    @php($v = fn ($f) => asset($f) . '?v=' . substr((string) @filemtime(public_path($f)), -6))
    @if ($admin)
        <link rel="stylesheet" href="{{ $v('assets/css/admin.css') }}">
    @else
        <link rel="stylesheet" href="{{ $v('assets/css/app.css') }}">
        <link rel="stylesheet" href="{{ $v('assets/css/design.css') }}">
        <link rel="stylesheet" href="{{ $v('assets/css/explore.css') }}">
    @endif
    @viteReactRefresh
    @vite(['resources/js/app.jsx', "resources/js/Pages/{$page['component']}.jsx"])
    @inertiaHead
</head>
<body class="{{ in_array($page['component'], ['Admin/Login', 'Admin/Setup'], true) ? 'auth' : ($admin ? 'admin' : '') }}">
    @inertia
</body>
</html>
