
<!DOCTYPE html>
<html lang="{{ in_array(session('family_locale', 'id'), ['id', 'en'], true) ? session('family_locale', 'id') : 'id' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

  <meta name="theme-color" content="#17120F" data-family-theme-color>
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', session('family_locale', 'id') === 'en' ? 'Family Portal' : 'Portal Keluarga')</title>

    <script>
    (() => {
        const root = document.documentElement;

        try {
            const theme = localStorage.getItem('rk-family-theme');
            const selected = theme === 'light' ? 'light' : 'dark';
            root.classList.add(`family-theme-${selected}`);
            root.style.colorScheme = selected;
        } catch (error) {
            root.classList.add('family-theme-dark');
        }

        try {
            const pinned = localStorage.getItem('rk-family-sidebar-pinned') !== 'false';
            root.classList.add(pinned ? 'family-sidebar-pinned' : 'family-sidebar-unpinned');
        } catch (error) {
            root.classList.add('family-sidebar-pinned');
        }
    })();
</script>

<link rel="preload" as="image" href="/images/family/sidebar-natural-oak.webp">
<link rel="preload" as="image" href="/images/family/sidebar-blackwood.webp">

    @vite([
        'resources/css/family.css',
        'resources/js/family.js',
    ])

    @stack('styles')
</head>

<body class="family-portal">

    <a
        href="#family-main"
        class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-slate-900"
    >
        {{ session('family_locale', 'id') === 'en' ? 'Skip to content' : 'Lewati ke konten' }}
    </a>

    <div id="family-app">
        @yield('content')
    </div>

    @stack('scripts')
    <script>
    requestAnimationFrame(() =>
        requestAnimationFrame(() =>
            document.documentElement.classList.add('family-ready')
        )
    );
</script>

</body>
</html>
