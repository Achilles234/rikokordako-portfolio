
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>

    {{-- Document Configuration --}}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

       <meta name="theme-color" content="#0B1120">
    <meta name="author" content="Riko Kordako">

    {{-- Theme Initialization --}}
    <script>
        (() => {
            const storageKey = 'rk-theme';
            const root = document.documentElement;

            let theme = 'dark';

            try {
                const savedTheme = localStorage.getItem(storageKey);

                if (savedTheme === 'light' || savedTheme === 'dark') {
                    theme = savedTheme;
                }
            } catch (error) {
                // Use Dark Mode when browser storage is unavailable.
            }

            root.classList.toggle('light', theme === 'light');
            root.style.colorScheme = theme;

            const themeColor = document.querySelector(
                'meta[name="theme-color"]'
            );

            if (themeColor) {
                themeColor.setAttribute(
                    'content',
                    theme === 'light' ? '#F8FAFC' : '#0B1120'
                );
            }
        })();
    </script>

    {{-- SEO Metadata --}}
    <meta
        name="description"
        content="@yield('meta_description', __('messages.seo_description'))"
    >

    <link
        rel="canonical"
        href="{{ route('home', ['locale' => app()->getLocale()]) }}"
    >

    {{-- Multilingual SEO --}}
    <link
        rel="alternate"
        hreflang="en"
        href="{{ route('home', ['locale' => 'en']) }}"
    >

    <link
        rel="alternate"
        hreflang="id"
        href="{{ route('home', ['locale' => 'id']) }}"
    >

    <link
        rel="alternate"
        hreflang="x-default"
        href="{{ route('home', ['locale' => 'en']) }}"
    >

    <title>@yield('title', __('messages.seo_title')) | {{ __('messages.seo_brand_tagline') }}</title>

    {{-- Open Graph Metadata --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Riko Kordako">

    <meta
        property="og:title"
        content="@yield('title', __('messages.seo_title')) | {{ __('messages.seo_brand_tagline') }}"
    >

    <meta
        property="og:description"
        content="@yield('meta_description', __('messages.seo_description'))"
    >

    <meta
        property="og:url"
        content="{{ route('home', ['locale' => app()->getLocale()]) }}"
    >

    <meta property="og:image" content="https://rikokordako.my.id/images/rico.png">

    <meta
        property="og:image:alt"
        content="Riko Kordako — {{ __('messages.seo_brand_tagline') }}"
    >

    <meta
        property="og:locale"
        content="{{ app()->getLocale() === 'id' ? 'id_ID' : 'en_US' }}"
    >

    <meta
        property="og:locale:alternate"
        content="{{ app()->getLocale() === 'id' ? 'en_US' : 'id_ID' }}"
    >

    {{-- Twitter Card Metadata --}}
    <meta name="twitter:card" content="summary_large_image">

    <meta
        name="twitter:title"
        content="@yield('title', __('messages.seo_title')) | {{ __('messages.seo_brand_tagline') }}"
    >

    <meta
        name="twitter:description"
        content="@yield('meta_description', __('messages.seo_description'))"
    >

    <meta name="twitter:image" content="https://rikokordako.my.id/images/rico.png">

    <meta
        name="twitter:image:alt"
        content="Riko Kordako — {{ __('messages.seo_brand_tagline') }}"
    >

    {{-- Application Assets --}}
    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    @stack('styles')

</head>

<body class="min-h-screen font-sans antialiased">

    {{-- Skip Navigation --}}
    <a
        href="#main-content"
        class="sr-only z-[100] rounded-md bg-accent-600 px-5 py-3 font-medium text-white focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:outline-2 focus:outline-offset-4 focus:outline-white"
    >
        {{ __('messages.skip_to_content') }}
    </a>

    <div id="app" class="min-h-screen">

        @include('sections.navbar')

        @yield('content')

    </div>

    {{-- Application Scripts --}}
    @stack('scripts')

</body>

</html>
