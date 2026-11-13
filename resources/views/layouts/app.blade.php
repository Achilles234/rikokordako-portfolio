
<!DOCTYPE html>
<html lang="en">

<head>

    {{-- Document Configuration --}}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="theme-color" content="#0B1120">
    <meta name="author" content="Riko Kordako">

    {{-- SEO Metadata --}}
    <meta
        name="description"
        content="@yield('meta_description', 'Riko Kordako — Technology, Systems, Business. Professional experience in IT infrastructure, network systems, and practical digital solutions.')"
    >

    <link rel="canonical" href="https://rikokordako.my.id">

    <title>@yield('title', 'Riko Kordako') | Technology. Systems. Business.</title>

    {{-- Open Graph Metadata --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Riko Kordako">
    <meta property="og:title" content="@yield('title', 'Riko Kordako') | Technology. Systems. Business.">
    <meta
        property="og:description"
        content="@yield('meta_description', 'Riko Kordako — Technology, Systems, Business.')"
    >
    <meta property="og:url" content="https://rikokordako.my.id">
    <meta property="og:image" content="https://rikokordako.my.id/images/rico.png">
    <meta property="og:image:alt" content="Riko Kordako — Technology, Systems, Business.">
    <meta property="og:locale" content="en_US">

    {{-- Twitter Card Metadata --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Riko Kordako') | Technology. Systems. Business.">
    <meta
        name="twitter:description"
        content="@yield('meta_description', 'Riko Kordako — Technology, Systems, Business.')"
    >
    <meta name="twitter:image" content="https://rikokordako.my.id/images/rico.png">
    <meta name="twitter:image:alt" content="Riko Kordako — Technology, Systems, Business.">

    {{-- Application Assets --}}
    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    @stack('styles')

</head>

<body class="min-h-screen bg-brand-950 font-sans text-white antialiased">

    {{-- Skip Navigation --}}
   
        <a
            href="#main-content"
            class="sr-only z-[100] rounded-md bg-accent-600 px-5 py-3 font-medium text-white focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:outline-2 focus:outline-offset-4 focus:outline-white"
        >
            Skip to main content
        </a>


    <div id="app" class="min-h-screen">

        @include('sections.navbar')

        @yield('content')

    </div>

    {{-- Application Scripts --}}
    @stack('scripts')

</body>

</html>
