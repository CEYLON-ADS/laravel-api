<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin Panel' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f7fbf9;
            --bg-soft: #edf6f2;
            --panel: #ffffff;
            --panel-alt: #f6fffb;
            --line: #d8ece3;
            --text: #153b2f;
            --muted: #5f8275;
            --accent: #24bf87;
            --accent-strong: #16a170;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Manrope", sans-serif;
            color: var(--text);
            background: linear-gradient(150deg, #f8fcfa, #eef7f3 48%, #fdfefe);
            min-height: 100vh;
        }
        .admin-shell {
            display: grid;
            grid-template-columns: 240px 1fr;
            min-height: 100vh;
        }
        .sidebar {
            border-right: 1px solid var(--line);
            background: linear-gradient(180deg, #ffffff, #f4fbf8);
            padding: 1rem;
        }
        .brand {
            font-family: "Cormorant Garamond", serif;
            font-size: 1.6rem;
            margin: 0 0 1rem;
            letter-spacing: .03em;
        }
        .brand a { color: #124333; text-decoration: none; }
        .nav { display: grid; gap: .45rem; }
        .nav a {
            text-decoration: none;
            color: #165641;
            background: #f3fcf7;
            border: 1px solid #d7ece2;
            border-radius: 12px;
            padding: .58rem .7rem;
            font-weight: 600;
        }
        .nav a:hover { background: #e8f8f1; }
        .logout-form { margin-top: 1rem; }
        .content { padding: 1rem; }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .7rem;
            margin-bottom: 1rem;
        }
        .title {
            margin: 0;
            font-family: "Cormorant Garamond", serif;
            font-size: 2rem;
            line-height: 1.1;
        }
        .subtitle {
            margin: .1rem 0 0;
            color: var(--muted);
            font-size: .92rem;
        }
        .panel {
            background: linear-gradient(180deg, #ffffff, #fbfffd);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 1rem;
            box-shadow: 0 10px 18px rgba(20, 76, 61, 0.08);
        }
        .btn {
            background: linear-gradient(135deg, var(--accent), var(--accent-strong));
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: .55rem .85rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
        }
        .btn.warn {
            background: linear-gradient(135deg, #d76666, #b84747);
        }
        .btn.muted {
            background: linear-gradient(135deg, #4a8372, #3a695b);
        }
        .flash {
            margin-bottom: .8rem;
            border: 1px solid #b8e9d5;
            background: #ebf9f2;
            color: #15523e;
            border-radius: 12px;
            padding: .65rem .8rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: .92rem;
        }
        th, td {
            border-bottom: 1px solid #e1f0ea;
            text-align: left;
            vertical-align: top;
            padding: .55rem .45rem;
        }
        th { color: #2e6653; font-size: .8rem; text-transform: uppercase; letter-spacing: .03em; }
        .pill {
            display: inline-block;
            border-radius: 999px;
            padding: .18rem .52rem;
            font-size: .75rem;
            border: 1px solid #bce8d5;
            background: #e8f8f1;
        }
        input, select {
            width: 100%;
            border-radius: 10px;
            border: 1px solid #d1e8de;
            background: #ffffff;
            color: #153b2f;
            padding: .56rem .62rem;
            font: inherit;
        }
        @media (max-width: 980px) {
            .admin-shell { grid-template-columns: 1fr; }
            .sidebar { border-right: none; border-bottom: 1px solid var(--line); }
        }
    </style>
</head>
<body>
<div class="admin-shell">
    <aside class="sidebar">
        @php
            $adminRole = session('admin_role', '');
            $homeRoute = $adminRole === 'ads_agent' ? route('admin.ads.index') : route('admin.dashboard');
        @endphp
        <h2 class="brand"><a href="{{ $homeRoute }}">Ceylon Admin</a></h2>
        <nav class="nav">
            @if (in_array($adminRole, ['super_admin', 'admin'], true))
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            @endif
            <a href="{{ route('admin.ads.index') }}">Manage Ads</a>
            @if ($adminRole === 'ads_agent')
                <a href="{{ route('ads.create') }}">Create Ad</a>
            @endif
            @if (in_array($adminRole, ['super_admin', 'admin'], true))
                <a href="{{ route('admin.users.index') }}">Manage Users</a>
                <a href="{{ route('admin.categories.index') }}">Manage Categories</a>
                <a href="{{ route('admin.cities.index') }}">Manage Cities</a>
                <a href="{{ route('admin.districts.index') }}">Manage Districts</a>
            @endif
            @if ($adminRole === 'super_admin')
                <a href="{{ route('admin.admin-users.index') }}">Admin Users</a>
            @endif
        </nav>
        <form class="logout-form" method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button class="btn muted" type="submit">Log out</button>
        </form>
    </aside>

    <main class="content">
        @if (session('status'))
            <div class="flash">{{ session('status') }}</div>
        @endif

        @yield('content')
    </main>
</div>
</body>
</html>
