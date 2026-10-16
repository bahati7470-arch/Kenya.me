<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kenya.me') · Kenya.me</title>
    <style>
        :root {
            color-scheme: light;
            font-family: "Segoe UI", Tahoma, sans-serif;
            color: #172522;
            background: #f2f5f1;
            font-synthesis: none;
            --ink: #172522;
            --muted: #64716c;
            --line: #d8e0da;
            --paper: #fff;
            --green: #087f5b;
            --green-dark: #066348;
            --lime: #d5e8a5;
            --red: #a12e2e;
        }
        * { box-sizing: border-box; }
        body { min-width: 320px; min-height: 100vh; margin: 0; }
        a { color: inherit; }
        .topbar { display: flex; align-items: center; justify-content: space-between; min-height: 68px; padding: 0 5vw; background: var(--ink); color: #fff; }
        .brand { display: flex; align-items: center; gap: 11px; font-size: 18px; font-weight: 700; }
        .brand-mark { display: grid; width: 34px; height: 34px; place-items: center; border-radius: 7px; background: var(--lime); color: var(--ink); font-size: 17px; }
        .topbar-note { color: #c5d1cb; font-size: 13px; }
        .topbar-nav { display: flex; align-items: center; gap: 18px; }
        .topbar-nav a { color: #e2e9e5; font-size: 13px; text-decoration: none; }
        .topbar-nav a:hover { color: var(--lime); }
        .logout { padding: 7px 10px; border: 1px solid #61716a; border-radius: 4px; background: transparent; color: #fff; font: inherit; font-size: 12px; cursor: pointer; }
        main { width: min(100% - 40px, 1080px); margin: 44px auto 72px; }
        .eyebrow { margin: 0 0 9px; color: var(--green); font-size: 12px; font-weight: 700; text-transform: uppercase; }
        h1 { margin: 0; font-size: clamp(28px, 4vw, 38px); line-height: 1.12; }
        .intro { max-width: 620px; margin: 12px 0 30px; color: var(--muted); line-height: 1.6; }
        .workspace { display: grid; grid-template-columns: minmax(0, 1fr) 300px; gap: 22px; align-items: start; }
        .panel { padding: 26px; border: 1px solid var(--line); border-radius: 7px; background: var(--paper); }
        .panel h2 { margin: 0 0 22px; font-size: 18px; }
        .field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .field { display: grid; gap: 7px; }
        .field-wide { grid-column: 1 / -1; }
        label { font-size: 13px; font-weight: 650; }
        input, textarea { width: 100%; border: 1px solid #bdc9c1; border-radius: 5px; padding: 11px 12px; background: #fff; color: var(--ink); font: inherit; font-size: 14px; }
        textarea { min-height: 94px; resize: vertical; }
        input:focus, textarea:focus { outline: 3px solid #d2ebdf; border-color: var(--green); }
        .hint, .error { margin: 0; font-size: 12px; }
        .hint { color: var(--muted); }
        .error { color: var(--red); }
        .submit { display: inline-flex; min-height: 44px; align-items: center; justify-content: center; margin-top: 22px; padding: 0 18px; border: 0; border-radius: 5px; background: var(--green); color: #fff; font: inherit; font-size: 14px; font-weight: 700; cursor: pointer; }
        .submit:hover { background: var(--green-dark); }
        .side-note { padding: 22px; border-radius: 7px; background: #e5eddf; }
        .side-note h2 { margin: 0 0 12px; font-size: 16px; }
        .side-note p { margin: 0; color: #46574d; font-size: 13px; line-height: 1.55; }
        .reference { display: inline-block; margin-top: 12px; padding: 8px 10px; border: 1px solid #b6cbb8; border-radius: 4px; background: #f8fbf5; font-family: Consolas, monospace; font-size: 13px; }
        .summary { display: grid; gap: 0; margin-top: 20px; border-top: 1px solid var(--line); }
        .summary-row { display: flex; justify-content: space-between; gap: 12px; padding: 13px 0; border-bottom: 1px solid var(--line); font-size: 14px; }
        .summary-row span:first-child { color: var(--muted); }
        .amount { font-size: 26px; font-weight: 750; }
        @media (max-width: 760px) {
            .topbar { align-items: flex-start; flex-direction: column; gap: 13px; padding: 14px 20px; }
            .topbar-nav { width: 100%; flex-wrap: wrap; gap: 12px; }
            main { margin-top: 30px; }
            .workspace { grid-template-columns: 1fr; }
            .field-grid { grid-template-columns: 1fr; }
            .field-wide { grid-column: auto; }
            .panel { padding: 20px; }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ auth()->check() ? route('dashboard') : route('login') }}" style="text-decoration:none">
            <span class="brand-mark">K</span><span>Kenya.me</span>
        </a>
        @if (auth()->check())
            <nav class="topbar-nav" aria-label="Main navigation">
                <a href="{{ route('dashboard') }}">Overview</a>
                <a href="{{ route('quotes.index') }}">Quotes</a>
                <a href="{{ route('invoices.index') }}">Invoices</a>
                <a href="{{ route('payments.index') }}">Payments</a>
                @if (auth()->user()->role === 'owner')<a href="{{ route('team.index') }}">Team</a>@endif
                @if (auth()->user()->role === 'owner')<a href="{{ route('settings.daraja') }}">Daraja</a>@endif
                <form method="post" action="{{ route('logout') }}">@csrf<button class="logout" type="submit">Sign out</button></form>
            </nav>
        @else
            <span class="topbar-note">Invoices · M-Pesa reconciliation</span>
        @endif
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>