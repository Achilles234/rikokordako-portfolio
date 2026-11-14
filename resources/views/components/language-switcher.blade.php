
@php
    $currentLocale = app()->getLocale();
@endphp

<nav
    aria-label="Language selection"
    class="theme-language-switcher flex items-center gap-1 rounded-full border p-1"
>
    @foreach (['en' => 'EN', 'id' => 'ID'] as $locale => $label)
        <a
            href="{{ route('home', ['locale' => $locale]) }}"
            data-language-switch
            hreflang="{{ $locale }}"
            lang="{{ $locale }}"
            aria-label="{{ $locale === 'en' ? 'Switch to English' : 'Ganti ke Bahasa Indonesia' }}"
            @if ($currentLocale === $locale) aria-current="page" @endif
            class="rounded-full px-3 py-1.5 font-mono text-[10px] font-semibold tracking-wider transition-all duration-300 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-400
                {{ $currentLocale === $locale
                    ? 'theme-language-active'
                    : 'theme-language-inactive' }}"
        >
            {{ $label }}
        </a>
    @endforeach
</nav>
