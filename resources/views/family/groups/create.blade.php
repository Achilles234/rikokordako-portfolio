
@extends('layouts.family')

@section('title', session('family_locale', 'id') === 'en'
    ? 'Create Family Group — Family Portal'
    : 'Buat Family Group — Portal Keluarga'
)

@section('content')

@php
    $isEnglish = session('family_locale', 'id') === 'en';
@endphp

<div class="family-app-layout flex h-screen overflow-hidden"
    style="background: var(--family-background);">

    @include('family.partials.sidebar')

    <div class="flex min-h-0 min-w-0 flex-1 flex-col">

        @include('family.partials.topbar')

        <main id="family-main"
            class="family-main-content min-h-0 min-w-0 flex-1 overflow-y-auto px-4 py-8 sm:px-6 lg:px-10">

            <div class="mx-auto max-w-3xl">

       <a href="{{ route('family.dashboard') }}"
                class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-600 text-white">
                    <span class="text-xl">⌂</span>
                </div>
                <div>
                    <p class="font-bold"
                        style="color: var(--family-text-primary);">
                        Family Portal
                    </p>
                    <p class="text-xs"
                        style="color: var(--family-text-secondary);">
                        Private Family Space
                    </p>
                </div>
            </a>

            <div class="flex items-center gap-3">
                <button type="button"
                    data-family-theme-toggle
                    class="rounded-xl border px-3 py-2 text-sm transition hover:opacity-75"
                    style="border-color: var(--family-border); color: var(--family-text-secondary);"
                    aria-label="{{ $isEnglish ? 'Toggle theme' : 'Ganti tema' }}">
                    ◐
                    <span class="hidden sm:inline">
                        {{ $isEnglish ? 'Theme' : 'Tema' }}
                    </span>
                </button>

                <form method="POST" action="{{ route('family.logout') }}">
                    @csrf
                    <button type="submit"
                        class="rounded-xl bg-red-500/10 px-3 py-2 text-sm font-semibold text-red-500 transition hover:bg-red-500/20">
                        {{ $isEnglish ? 'Sign Out' : 'Keluar' }}
                    </button>
                </form>
            </div>
        </header>

        {{-- Breadcrumb --}}
        <nav class="mb-6 text-sm"
            aria-label="{{ $isEnglish ? 'Breadcrumb' : 'Navigasi halaman' }}">
            <a href="{{ route('family.dashboard') }}"
                class="font-medium text-blue-500 hover:underline">
                Dashboard
            </a>
            <span class="mx-2" style="color: var(--family-text-secondary);">/</span>
            <span style="color: var(--family-text-secondary);">
                {{ $isEnglish ? 'Create Family Group' : 'Buat Family Group' }}
            </span>
        </nav>

        {{-- Page heading --}}
        <div class="mb-6">
            <p class="mb-2 text-sm font-semibold uppercase tracking-wider text-blue-500">
                {{ $isEnglish ? 'Group Management' : 'Manajemen Grup' }}
            </p>

            <h1 class="text-2xl font-bold tracking-tight sm:text-3xl"
                style="color: var(--family-text-primary);">
                {{ $isEnglish ? 'Create Family Group' : 'Buat Family Group' }}
            </h1>

            <p class="mt-2 text-sm leading-6"
                style="color: var(--family-text-secondary);">
                {{ $isEnglish
                    ? 'Create a new private space for a family.'
                    : 'Buat ruang privat baru untuk sebuah keluarga.' }}
            </p>
        </div>

        {{-- Form card --}}
        <div class="rounded-2xl border p-5 sm:p-8"
            style="background: var(--family-surface); border-color: var(--family-border);">

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-500">
                    <p class="mb-2 font-semibold">
                        {{ $isEnglish
                            ? 'Please correct the following errors:'
                            : 'Mohon periksa kembali data berikut:' }}
                    </p>

                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('family.groups.store') }}"
                class="space-y-6">
                @csrf

                {{-- Group name --}}
                <div>
                    <label for="name"
                        class="mb-2 block text-sm font-semibold"
                        style="color: var(--family-text-primary);">
                        {{ $isEnglish ? 'Family Group Name' : 'Nama Family Group' }}
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        maxlength="255"
                        required
                        autofocus
                        placeholder="{{ $isEnglish
                            ? 'Enter family group name'
                            : 'Masukkan nama Family Group' }}"
                        class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10"
                        style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);"
                    >

                    @error('name')
                        <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Group type --}}
                <div>
                    <label for="group_type"
                        class="mb-2 block text-sm font-semibold"
                        style="color: var(--family-text-primary);">
                        {{ $isEnglish ? 'Group Type' : 'Tipe Grup' }}
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        id="group_type"
                        name="group_type"
                        value="{{ old('group_type', 'custom') }}"
                        maxlength="30"
                        required
                        placeholder="{{ $isEnglish ? 'Example: custom' : 'Contoh: custom' }}"
                        class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10"
                        style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);"
                    >

                    <p class="mt-2 text-xs leading-5"
                        style="color: var(--family-text-secondary);">
                        {{ $isEnglish
                            ? 'Enter a group type identifier. Use lowercase letters and underscores where needed.'
                            : 'Masukkan identitas tipe grup. Gunakan huruf kecil dan garis bawah jika diperlukan.' }}
                    </p>

                    @error('group_type')
                        <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label for="description"
                        class="mb-2 block text-sm font-semibold"
                        style="color: var(--family-text-primary);">
                        {{ $isEnglish ? 'Description' : 'Deskripsi' }}
                        <span class="font-normal"
                            style="color: var(--family-text-secondary);">
                            ({{ $isEnglish ? 'Optional' : 'Opsional' }})
                        </span>
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        placeholder="{{ $isEnglish
                            ? 'Describe this family group...'
                            : 'Ceritakan singkat tentang Family Group ini...' }}"
                        class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10"
                        style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);"
                    >{{ old('description') }}</textarea>

                    @error('description')
                        <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- System information --}}
                <div class="rounded-xl border border-blue-500/20 bg-blue-500/5 p-4">
                    <p class="mb-2 text-sm font-semibold text-blue-500">
                        {{ $isEnglish ? 'Automatic settings' : 'Pengaturan otomatis' }}
                    </p>

                    <ul class="space-y-2 text-sm"
                        style="color: var(--family-text-secondary);">
                        <li>
                            ✓ {{ $isEnglish
                                ? 'Group status will be Active.'
                                : 'Status grup otomatis Aktif.' }}
                        </li>
                        <li>
                            ✓ {{ $isEnglish
                                ? 'Group URL identifier (slug) will be generated automatically.'
                                : 'Identitas URL grup (slug) dibuat otomatis.' }}
                        </li>
                        <li>
                            ✓ {{ $isEnglish
                                ? 'Creation activity will be recorded in the audit log.'
                                : 'Aktivitas pembuatan dicatat dalam audit log.' }}
                        </li>
                    </ul>
                </div>

                {{-- Actions --}}
                <div class="flex flex-col-reverse gap-3 border-t pt-6 sm:flex-row sm:justify-end"
                    style="border-color: var(--family-border);">

                    <a href="{{ route('family.dashboard') }}"
                        class="inline-flex items-center justify-center rounded-xl border px-5 py-3 text-sm font-semibold transition hover:opacity-75"
                        style="border-color: var(--family-border); color: var(--family-text-secondary);">
                        {{ $isEnglish ? 'Cancel' : 'Batal' }}
                    </a>

                    <button type="submit" class="family-leaf-button inline-flex items-center justify-center gap-2">
                        <span aria-hidden="true">+</span>
                        {{ $isEnglish ? 'Create Family Group' : 'Buat Family Group' }}
                    </button>
                </div>
            </form>
        </div>

        <footer class="mt-8 text-center text-xs"
            style="color: var(--family-text-secondary);">
            © {{ date('Y') }} Family Portal
        </footer>

    </div>
</div>

            </div>
        </main>
    </div>
</div>

@endsection
