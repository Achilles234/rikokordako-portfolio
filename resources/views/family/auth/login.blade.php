
@extends('layouts.family')

@section('title', session('family_locale', 'id') === 'en' ? 'Family Portal — Welcome Home' : 'Family Portal — Selamat Datang')

@php
    $locale = session('family_locale', 'id');
    $isEnglish = $locale === 'en';
@endphp

@section('content')

<link rel="stylesheet" href="{{ asset('css/login.css') }}">

<main class="family-login-page">
    <div class="family-login-layout">
     

    <section class="family-login-card family-login-enter">

        {{-- Top navigation --}}
        <div class="flex justify-between items-center gap-4 mb-8">

            <a href="{{ url('/') }}"
                class="inline-flex items-center gap-2 text-xs tracking-wide text-[#80694b] hover:text-[#49351f] transition font-sans"
                title="{{ $isEnglish ? 'Back to Home' : 'Kembali ke Beranda' }}">
                <span class="text-lg">←</span>
                <span>{{ $isEnglish ? 'Home' : 'Beranda' }}</span>
            </a>

            <div class="flex items-center gap-3 text-xs font-sans">

                <a href="{{ url('/family/login?lang=id') }}"
                    class="{{ !$isEnglish ? 'text-[#75542e] font-bold' : 'text-[#a69a86] hover:text-[#75542e]' }} transition">
                    ID
                </a>

                <span class="text-[#c6b99f]">/</span>

                <a href="{{ url('/family/login?lang=en') }}"
                    class="{{ $isEnglish ? 'text-[#75542e] font-bold' : 'text-[#a69a86] hover:text-[#75542e]' }} transition">
                    EN
                </a>

                <span class="w-px h-4 bg-[#d4c7b1] mx-1"></span>

                <button type="button"
                    data-family-theme-toggle
                    class="w-9 h-9 rounded-full border border-[#d5c6ad] flex items-center justify-center text-[#80694b] hover:bg-[#e8decc] transition"
                    aria-label="{{ $isEnglish ? 'Change theme' : 'Ganti tema' }}">

                    <svg viewBox="0 0 24 24"
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.6">
                        <path d="M12 3v2m0 14v2M3 12h2m14 0h2M5.64 5.64l1.42 1.42m9.88 9.88 1.42 1.42m0-12.72-1.42 1.42m-9.88 9.88-1.42 1.42"/>
                        <circle cx="12" cy="12" r="4"/>
                    </svg>
                </button>

            </div>
        </div>

        {{-- Logo and identity --}}
        <div class="text-center mb-8">

            <div class="family-login-logo mb-4">
                <svg viewBox="0 0 48 48"
                    class="w-9 h-9"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                    aria-hidden="true">

                    <path d="M24 39V19M24 27C14 27 10 20 11 11C20 11 26 16 24 27Z"/>
                    <path d="M24 23C24 13 31 8 39 9C39 18 33 25 24 23Z"/>
                    <path d="M17 39H31"/>
                </svg>
            </div>

            <p class="text-xs tracking-[.28em] uppercase text-[#a18458] mb-2">
                FAMILY HERITAGE
            </p>

            <h1 class="text-2xl sm:text-3xl font-semibold tracking-wide text-[#38291d]">
                Family Portal
            </h1>

            <p class="mt-2 text-xs tracking-wide text-[#9a896f]">
                {{ $isEnglish ? 'Together, Closer Than Ever' : 'Bersama, Lebih Dekat' }}
            </p>

            <div class="family-login-divider mt-6">
                {{ $isEnglish ? 'Welcome Home' : 'Selamat Datang' }}
            </div>

        </div>

        {{-- Login heading --}}
        <div class="mb-6 text-center">

            <h2 class="text-2xl font-semibold text-[#38291d] mb-2">
                {{ $isEnglish ? 'Welcome Back' : 'Selamat Datang Kembali' }}
            </h2>

            <p class="text-sm leading-6 text-[#8b7b66] font-sans">
                {{ $isEnglish
                    ? 'Sign in to reconnect with your family and continue your story.'
                    : 'Masuk kembali untuk terhubung dengan keluarga dan melanjutkan cerita bersama.' }}
            </p>

        </div>

        {{-- Validation errors --}}
        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 font-sans">
                <ul class="list-disc pl-4 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Session status --}}
        @if (session('status'))
            <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 font-sans">
                {{ session('status') }}
            </div>
        @endif

        {{-- Login form --}}
        <form method="POST"
            action="{{ url('/family/login') }}"
            class="space-y-5">

            @csrf

            <div>
                <label for="email"
                    class="block text-xs tracking-wider text-[#6e5a40] mb-2 font-sans uppercase">
                    {{ $isEnglish ? 'Email Address' : 'Alamat Email' }}
                </label>

                <input id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    class="family-login-input"
                    placeholder="{{ $isEnglish ? 'Your email address' : 'Masukkan alamat email' }}">
            </div>

            <div>

                <label for="password"
                    class="block text-xs tracking-wider text-[#6e5a40] mb-2 font-sans uppercase">
                    {{ $isEnglish ? 'Password' : 'Kata Sandi' }}
                </label>

                <div class="relative">

                    <input id="password"
                        name="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        class="family-login-input pr-16"
                        placeholder="{{ $isEnglish ? 'Enter your password' : 'Masukkan kata sandi' }}">

                    <button type="button"
                        id="togglePassword"
                        class="absolute inset-y-0 right-0 px-4 text-[#92764f] hover:text-[#49351f] transition font-sans text-xs"
                        aria-label="{{ $isEnglish ? 'Show password' : 'Tampilkan kata sandi' }}">

                        <span id="passwordToggleText">
                            {{ $isEnglish ? 'Show' : 'Lihat' }}
                        </span>

                    </button>

                </div>
            </div>

            <div class="flex items-center justify-between gap-3 pt-1">

                <label class="flex items-center gap-2 text-xs text-[#79694f] font-sans cursor-pointer">

                    <input type="checkbox"
                        name="remember"
                        value="1"
                        class="rounded border-[#b8a486] text-[#80603a] focus:ring-[#a7834c]">

                    {{ $isEnglish ? 'Remember me' : 'Ingat saya' }}

                </label>

            </div>

            <button type="submit"
                class="family-login-submit text-sm tracking-[.12em] uppercase font-sans font-semibold">

                {{ $isEnglish ? 'Enter Family Portal' : 'Masuk ke Portal Keluarga' }}

                <span class="ml-2">→</span>

            </button>

        </form>

        {{-- Footer --}}
        <div class="family-login-divider mt-8 mb-5">
            {{ $isEnglish ? 'A Family Connected' : 'Keluarga yang Terhubung' }}
        </div>

        <p class="text-center text-xs leading-6 text-[#9a896f] font-sans">
            {{ $isEnglish
                ? 'This is a private space for our family. Access is available to invited members.'
                : 'Ini adalah ruang pribadi keluarga. Akses hanya tersedia bagi anggota yang diundang.' }}
        </p>

        <div class="mt-7 text-center text-[9px] tracking-[.16em] text-[#a18e70] font-sans">
            <span class="text-[#987447]">✦</span>
            {{ $isEnglish ? 'CONNECTED BY FAMILY, UNITED BY STORIES' : 'TERHUBUNG OLEH KELUARGA, DISATUKAN OLEH CERITA' }}
            <span class="text-[#987447]">✦</span>
        </div>

           </section>

        {{-- CINEMATIC FAMILY PROLOGUE --}}
        <section class="family-prologue">

            <div class="family-prologue-heading">
                <span class="family-prologue-eyebrow">
                    {{ $isEnglish ? 'OUR FAMILY · OUR STORY' : 'KELUARGA KITA · CERITA KITA' }}
                </span>

                <div class="family-prologue-line"></div>
            </div>

            <div class="family-prologue-content">

                <article class="family-prologue-slide is-active">
                    <span class="family-prologue-number">01 / 05</span>
                    <h2>
                        {{ $isEnglish ? 'Every Family Has a Story...' : 'Setiap Keluarga Punya Cerita...' }}
                    </h2>
                    <p>
                        {{ $isEnglish
                            ? 'A story of love, memories, journeys, and generations connected by one precious bond.'
                            : 'Sebuah kisah tentang cinta, kenangan, perjalanan, dan generasi yang terhubung oleh satu ikatan berharga.' }}
                    </p>
                </article>

                <article class="family-prologue-slide">
                    <span class="family-prologue-number">02 / 05</span>
                    <h2>
                        {{ $isEnglish ? 'Know Your Roots' : 'Kenali Akar Keluargamu' }}
                    </h2>
                    <p>
                        {{ $isEnglish
                            ? 'Discover your family tree, relationships, and the generations that shaped your family.'
                            : 'Telusuri silsilah, hubungan keluarga, dan generasi yang membentuk perjalanan keluargamu.' }}
                    </p>
                </article>

                <article class="family-prologue-slide">
                    <span class="family-prologue-number">03 / 05</span>
                    <h2>
                        {{ $isEnglish ? 'Preserve Every Memory' : 'Abadikan Setiap Kenangan' }}
                    </h2>
                    <p>
                        {{ $isEnglish
                            ? 'Keep precious photographs, meaningful moments, and family memories alive.'
                            : 'Simpan foto berharga, momen istimewa, dan kenangan keluarga agar selalu hidup.' }}
                    </p>
                </article>

                <article class="family-prologue-slide">
                    <span class="family-prologue-number">04 / 05</span>
                    <h2>
                        {{ $isEnglish ? 'Stay Connected' : 'Tetap Terhubung' }}
                    </h2>
                    <p>
                        {{ $isEnglish
                            ? 'Share family activities, agendas, group conversations, and important moments together.'
                            : 'Berbagi kegiatan, agenda keluarga, percakapan grup, dan berbagai momen penting bersama.' }}
                    </p>
                </article>

                <article class="family-prologue-slide">
                    <span class="family-prologue-number">05 / 05</span>
                    <h2>
                        {{ $isEnglish ? 'One Family. One Legacy.' : 'Satu Keluarga. Satu Warisan.' }}
                    </h2>
                    <p>
                        {{ $isEnglish
                            ? 'Family is not only where our story begins, but also the legacy we leave for generations to come.'
                            : 'Keluarga bukan hanya tempat cerita kita dimulai, tetapi juga warisan yang kita tinggalkan untuk generasi mendatang.' }}
                    </p>
                </article>

            </div>

            <div class="family-prologue-footer">

                <div class="family-prologue-indicators">
                    <span class="active"></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

                <span class="family-prologue-signature">
                    ONE FAMILY · MANY STORIES · ONE LEGACY
                </span>

            </div>

        </section>

    </div>

</main>

@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // PASSWORD VISIBILITY
    const passwordInput = document.getElementById('password');
    const toggleButton = document.getElementById('togglePassword');
    const toggleText = document.getElementById('passwordToggleText');

    if (passwordInput && toggleButton && toggleText) {
        toggleButton.addEventListener('click', function () {

            const isPassword = passwordInput.type === 'password';

            passwordInput.type = isPassword ? 'text' : 'password';

            toggleText.textContent = isPassword
                ? @json($isEnglish ? 'Hide' : 'Sembunyikan')
                : @json($isEnglish ? 'Show' : 'Lihat');

            toggleButton.setAttribute(
                'aria-label',
                isPassword
                    ? @json($isEnglish ? 'Hide password' : 'Sembunyikan kata sandi')
                    : @json($isEnglish ? 'Show password' : 'Tampilkan kata sandi')
            );
        });
    }

    // CINEMATIC FAMILY PROLOGUE
    const prologue = document.querySelector('.family-prologue');

    if (prologue) {
        const slides = prologue.querySelectorAll('.family-prologue-slide');
        const indicators = prologue.querySelectorAll(
            '.family-prologue-indicators span'
        );

        if (
            slides.length > 1 &&
            slides.length === indicators.length &&
            !window.matchMedia('(prefers-reduced-motion: reduce)').matches
        ) {
            let currentSlide = 0;

            setInterval(function () {
                slides[currentSlide].classList.remove('is-active');
                indicators[currentSlide].classList.remove('active');

                currentSlide = (currentSlide + 1) % slides.length;

                slides[currentSlide].classList.add('is-active');
                indicators[currentSlide].classList.add('active');
            }, 7000);
        }
    }

});
</script>
@endpush
