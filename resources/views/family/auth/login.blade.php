
@extends('layouts.family')

@section('title', session('family_locale', 'id') === 'en' ? 'Family Portal — Welcome Home' : 'Family Portal — Selamat Datang')

@php
    $locale = session('family_locale', 'id');
    $isEnglish = $locale === 'en';
@endphp

@section('content')
<style>
    .heritage-login {
        --heritage-gold: #c6a56b;
        --heritage-gold-light: #e5cc96;
        --heritage-ink: #241b15;
        --heritage-cream: #f4eee2;
        --heritage-muted: #a99a83;
        min-height: 100vh;
        background: #211811;
        font-family: Georgia, 'Times New Roman', serif;
    }

    .heritage-scene {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        background:
            linear-gradient(110deg, rgba(22, 15, 11, .94), rgba(38, 27, 19, .88)),
            repeating-linear-gradient(92deg, #38271c 0px, #38271c 3px, #2a1d15 5px, #493323 9px, #2a1d15 13px);
    }

    .heritage-scene::before,
    .heritage-scene::after {
        content: "";
        position: absolute;
        pointer-events: none;
        z-index: -1;
    }

    .heritage-scene::before {
        inset: 0;
        opacity: .2;
        background:
            repeating-linear-gradient(3deg, transparent 0 13px, rgba(224, 185, 119, .12) 14px, transparent 16px),
            radial-gradient(ellipse at 50% 40%, rgba(191, 143, 74, .2), transparent 65%);
    }

    .heritage-scene::after {
        inset: 18px;
        border: 1px solid rgba(198, 165, 107, .28);
    }

    .heritage-frame {
        border: 1px solid rgba(198, 165, 107, .5);
        box-shadow: inset 0 0 0 5px rgba(198, 165, 107, .06),
                    0 20px 60px rgba(0, 0, 0, .3);
    }

    .heritage-tree {
        width: min(100%, 430px);
        filter: drop-shadow(0 8px 18px rgba(0, 0, 0, .3));
        animation: tree-arrive 1.4s ease both;
    }

    .heritage-tree-line {
        stroke-dasharray: 500;
        stroke-dashoffset: 500;
        animation: draw-tree 2.2s ease forwards .3s;
    }

    .heritage-tree-node {
        transform-box: fill-box;
        transform-origin: center;
        animation: node-appear .7s ease both;
    }

    .heritage-tree-node:nth-of-type(2) { animation-delay: .4s; }
    .heritage-tree-node:nth-of-type(3) { animation-delay: .6s; }
    .heritage-tree-node:nth-of-type(4) { animation-delay: .8s; }

    .heritage-form-panel {
        background:
            radial-gradient(ellipse at 90% 0%, rgba(198, 165, 107, .13), transparent 42%),
            linear-gradient(145deg, #f8f3e9, #eee4d3);
        color: var(--heritage-ink);
    }

    .heritage-input {
        width: 100%;
        border: 1px solid #d6c8b2;
        border-radius: 5px;
        background: rgba(255, 255, 255, .58);
        padding: 13px 15px;
        color: #30251c;
        font-family: Arial, sans-serif;
        font-size: 14px;
        transition: border-color .25s, box-shadow .25s, background .25s;
    }

    .heritage-input:focus {
        outline: none;
        border-color: #a7834c;
        background: #fffdf8;
        box-shadow: 0 0 0 3px rgba(167, 131, 76, .13);
    }

    .heritage-submit {
        position: relative;
        overflow: hidden;
        border: 1px solid #9d7844;
        background: linear-gradient(135deg, #6b4b2d, #987447 55%, #6d4c2d);
        color: #fff8e9;
        box-shadow: 0 5px 15px rgba(83, 55, 29, .2);
        transition: transform .25s, box-shadow .25s, filter .25s;
    }

    .heritage-submit:hover {
        transform: translateY(-2px);
        filter: brightness(1.1);
        box-shadow: 0 9px 22px rgba(83, 55, 29, .27);
    }

    .heritage-submit:active {
        transform: translateY(0);
    }

    .heritage-divider {
        display: flex;
        align-items: center;
        gap: 13px;
        color: #a38b68;
        font-size: 10px;
        letter-spacing: .22em;
        text-transform: uppercase;
    }

    .heritage-divider::before,
    .heritage-divider::after {
        content: "";
        height: 1px;
        flex: 1;
        background: linear-gradient(90deg, transparent, #c9b99e);
    }

    .heritage-divider::after {
        background: linear-gradient(90deg, #c9b99e, transparent);
    }

    .heritage-ornament {
        color: var(--heritage-gold);
        letter-spacing: .35em;
    }

    .heritage-enter {
        animation: heritage-enter .9s cubic-bezier(.2,.7,.2,1) both;
    }

    @keyframes heritage-enter {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes tree-arrive {
        from { opacity: 0; transform: scale(.96); }
        to { opacity: 1; transform: scale(1); }
    }

    @keyframes draw-tree {
        to { stroke-dashoffset: 0; }
    }

    @keyframes node-appear {
        from { opacity: 0; transform: scale(.7); }
        to { opacity: 1; transform: scale(1); }
    }

    @media (prefers-reduced-motion: reduce) {
        .heritage-login *,
        .heritage-login *::before,
        .heritage-login *::after {
            animation-duration: .01ms !important;
            transition-duration: .01ms !important;
        }
    }

    @media (max-width: 1023px) {
        .heritage-scene::after { inset: 10px; }
    }
</style>

<main class="heritage-login min-h-screen flex items-center justify-center p-3 sm:p-6">
    <div class="w-full max-w-6xl min-h-[680px] grid lg:grid-cols-[1.08fr_.92fr] rounded-lg overflow-hidden shadow-2xl heritage-enter">

        {{-- FAMILY HERITAGE SIDE --}}
        <section class="heritage-scene text-[#f5ead7] flex flex-col justify-between p-8 sm:p-12 lg:p-14 min-h-[390px] lg:min-h-[680px]">
            <div class="relative z-10 flex items-center gap-3">
                <div class="w-12 h-12 rounded-full border border-[#c6a56b] flex items-center justify-center text-[#e5cc96]">
                    <svg viewBox="0 0 48 48" class="w-8 h-8" fill="none" aria-hidden="true">
                        <path d="M24 39V19M24 27C14 27 10 20 11 11C20 11 26 16 24 27Z" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M24 23C24 13 31 8 39 9C39 18 33 25 24 23Z" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M17 39H31" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                </div>
                <div>
                    <div class="text-[11px] tracking-[.32em] uppercase text-[#d7bd8a]">Family Heritage</div>
                    <div class="text-lg tracking-wide">Family Portal</div>
                </div>
            </div>

            <div class="relative z-10 flex flex-col items-center text-center py-8">
                <div class="heritage-divider w-full max-w-xs mb-5">Our generations</div>

                <h1 class="text-3xl sm:text-4xl lg:text-[42px] leading-tight text-[#f5ead7] mb-3">
                    {{ $isEnglish ? 'One Family,' : 'Satu Keluarga,' }}<br>
                    <span class="italic text-[#d6b77e]">{{ $isEnglish ? 'Many Generations.' : 'Banyak Generasi.' }}</span>
                </h1>

                <p class="max-w-sm text-sm leading-7 text-[#c8b79e] font-sans">
                    {{ $isEnglish
                        ? 'Every generation is a chapter. Every connection is part of our story.'
                        : 'Setiap generasi adalah sebuah cerita. Setiap hubungan adalah bagian dari warisan keluarga kita.' }}
                </p>

                {{-- Decorative family tree --}}
                <div class="heritage-frame mt-8 p-4 sm:p-6 bg-black/15">
                    <svg class="heritage-tree" viewBox="0 0 430 300" fill="none" role="img" aria-label="{{ $isEnglish ? 'Decorative family tree illustration' : 'Ilustrasi pohon keluarga' }}">
                        <defs>
                            <linearGradient id="goldBranch" x1="0" y1="0" x2="1" y2="1">
                                <stop stop-color="#e8d19d"/>
                                <stop offset="1" stop-color="#9d7845"/>
                            </linearGradient>
                            <radialGradient id="nodeGold">
                                <stop stop-color="#e8d19d"/>
                                <stop offset="1" stop-color="#a77d43"/>
                            </radialGradient>
                        </defs>

                        {{-- Tree trunk and branches --}}
                        <g stroke="url(#goldBranch)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path class="heritage-tree-line" d="M215 260V205M215 205L215 155M215 205L115 145M215 205L315 145M215 155L155 100M215 155L275 100"/>
                            <path class="heritage-tree-line" d="M115 145L75 105M115 145L155 100M315 145L275 100M315 145L355 105"/>
                            <path d="M180 260H250" opacity=".6"/>
                        </g>

                        {{-- Roots --}}
                        <g stroke="#9d7845" stroke-width="1.5" opacity=".65">
                            <path d="M215 260L180 282M215 260L250 282M215 260L215 287"/>
                        </g>

                        {{-- Family nodes --}}
                        <g fill="#2d2118" stroke="#d8bc82" stroke-width="1.7">
                            <circle class="heritage-tree-node" cx="215" cy="63" r="23"/>
                            <circle class="heritage-tree-node" cx="155" cy="100" r="19"/>
                            <circle class="heritage-tree-node" cx="275" cy="100" r="19"/>
                            <circle class="heritage-tree-node" cx="75" cy="105" r="16"/>
                            <circle class="heritage-tree-node" cx="355" cy="105" r="16"/>
                            <circle class="heritage-tree-node" cx="115" cy="145" r="17"/>
                            <circle class="heritage-tree-node" cx="315" cy="145" r="17"/>
                            <circle class="heritage-tree-node" cx="215" cy="155" r="20"/>
                        </g>

                        {{-- Inner emblems --}}
                        <g fill="url(#nodeGold)">
                            <circle cx="215" cy="63" r="7"/>
                            <circle cx="155" cy="100" r="6"/>
                            <circle cx="275" cy="100" r="6"/>
                            <circle cx="75" cy="105" r="5"/>
                            <circle cx="355" cy="105" r="5"/>
                            <circle cx="115" cy="145" r="5"/>
                            <circle cx="315" cy="145" r="5"/>
                            <circle cx="215" cy="155" r="6"/>
                        </g>

                        <g fill="#d8bc82" opacity=".8">
                            <path d="M215 34L219 42L228 43L221 49L223 58L215 53L207 58L209 49L202 43L211 42Z"/>
                            <circle cx="180" cy="72" r="2"/>
                            <circle cx="250" cy="72" r="2"/>
                            <circle cx="45" cy="145" r="2"/>
                            <circle cx="385" cy="145" r="2"/>
                        </g>
                    </svg>
                    <div class="heritage-ornament text-[9px] mt-2">✦ &nbsp; ROOTS · BONDS · LEGACY &nbsp; ✦</div>
                </div>

                <p class="mt-5 text-xs italic text-[#c6b18e]">
                    {{ $isEnglish ? 'A place where our stories stay connected.' : 'Tempat cerita dan hubungan keluarga tetap terhubung.' }}
                </p>
            </div>

            <div class="relative z-10 flex justify-between items-end gap-4 text-[10px] tracking-wider text-[#aa9676]">
                <span>EST. FAMILY · FOREVER</span>
                <span class="text-right">GENERATIONS UNITED</span>
            </div>
        </section>

        {{-- LOGIN SIDE --}}
        <section class="heritage-form-panel flex flex-col justify-between p-7 sm:p-12 lg:p-14">
          
<div class="flex justify-between items-center gap-4">
    <a href="{{ url('/') }}"
       class="inline-flex items-center gap-2 text-xs tracking-wider text-[#80694b] hover:text-[#49351f] transition font-sans"
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
            <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.6">
                <path d="M12 3v2m0 14v2M3 12h2m14 0h2M5.64 5.64l1.42 1.42m9.88 9.88 1.42 1.42m0-12.72-1.42 1.42m-9.88 9.88-1.42 1.42"/>
                <circle cx="12" cy="12" r="4"/>
            </svg>
        </button>
    </div>
</div>


            <div class="w-full max-w-md mx-auto py-10">
                <div class="mb-8">
                    <div class="text-[#a18458] text-sm mb-3 tracking-[.18em]">✦ &nbsp; FAMILY PORTAL</div>
                    <h2 class="text-3xl sm:text-4xl text-[#38291d] leading-tight mb-3">
                        {{ $isEnglish ? 'Welcome Back' : 'Selamat Datang' }}
                    </h2>
                    <p class="text-sm leading-6 text-[#8b7b66] font-sans">
                        {{ $isEnglish
                            ? 'Sign in to reconnect with your family and continue your story.'
                            : 'Masuk kembali untuk terhubung dengan keluarga dan melanjutkan cerita bersama.' }}
                    </p>
                </div>

                @if ($errors->any())
                    <div class="mb-5 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 font-sans">
                        <ul class="list-disc pl-4 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('status'))
                    <div class="mb-5 rounded border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 font-sans">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ url('/family/login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-xs tracking-wider text-[#6e5a40] mb-2 font-sans uppercase">
                            {{ $isEnglish ? 'Email Address' : 'Alamat Email' }}
                        </label>
                        <input id="email"
                               name="email"
                               type="email"
                               value="{{ old('email') }}"
                               required
                               autofocus
                               autocomplete="username"
                               class="heritage-input"
                               placeholder="{{ $isEnglish ? 'Your email address' : 'Masukkan alamat email' }}">
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label for="password" class="block text-xs tracking-wider text-[#6e5a40] font-sans uppercase">
                                {{ $isEnglish ? 'Password' : 'Kata Sandi' }}
                            </label>
                        </div>

                        <div class="relative">
                            <input id="password"
                                   name="password"
                                   type="password"
                                   required
                                   autocomplete="current-password"
                                   class="heritage-input pr-14"
                                   placeholder="{{ $isEnglish ? 'Enter your password' : 'Masukkan kata sandi' }}">

                            <button type="button"
                                    id="togglePassword"
                                    class="absolute inset-y-0 right-0 px-4 text-[#92764f] hover:text-[#49351f] transition font-sans text-xs"
                                    aria-label="{{ $isEnglish ? 'Show password' : 'Tampilkan kata sandi' }}">
                                <span id="passwordToggleText">{{ $isEnglish ? 'Show' : 'Lihat' }}</span>
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
                            class="heritage-submit w-full rounded py-3.5 text-sm tracking-[.12em] uppercase font-sans font-semibold">
                        {{ $isEnglish ? 'Enter Family Portal' : 'Masuk ke Portal Keluarga' }}
                        <span class="ml-2">→</span>
                    </button>
                </form>

                <div class="heritage-divider mt-8 mb-5">A family connected</div>

                <p class="text-center text-xs leading-6 text-[#9a896f] font-sans">
                    {{ $isEnglish
                        ? 'This is a private space for our family. Access is available to invited members.'
                        : 'Ini adalah ruang pribadi keluarga. Akses hanya tersedia bagi anggota yang diundang.' }}
                </p>
            </div>

            <div class="text-center text-[10px] tracking-wider text-[#a18e70] font-sans">
                <span class="text-[#987447]">✦</span>
                {{ $isEnglish ? 'CONNECTED BY FAMILY, UNITED BY STORIES' : 'TERHUBUNG OLEH KELUARGA, DISATUKAN OLEH CERITA' }}
                <span class="text-[#987447]">✦</span>
            </div>
        </section>
    </div>
</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
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
});
</script>
@endpush
