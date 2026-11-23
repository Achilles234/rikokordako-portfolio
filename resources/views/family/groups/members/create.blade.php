
@extends('layouts.family')

@section('title', ($isEnglish = session('family_locale', 'id') === 'en')
    ? 'Add Member — Family Portal'
    : 'Tambah Anggota — Family Portal')

@section('content')

@php
    $isEnglish = session('family_locale', 'id') === 'en';
@endphp

<div class="h-screen overflow-y-auto overflow-x-hidden" style="background: var(--family-background);">

    <header class="sticky top-0 z-20 border-b px-4 sm:px-6 lg:px-10"
        style="background: var(--family-surface); border-color: var(--family-border);">

        <div class="mx-auto flex h-16 max-w-5xl items-center justify-between gap-4">
            <div>
                <p class="font-bold" style="color: var(--family-text-primary);">
                    Family Portal
                </p>
                <p class="text-xs" style="color: var(--family-text-secondary);">
                    {{ $isEnglish ? 'Add Family Member' : 'Tambah Anggota Keluarga' }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                <button type="button"
                    data-family-theme-toggle
                    class="rounded-xl border px-3 py-2 text-sm"
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
                        class="rounded-xl bg-red-500/10 px-3 py-2 text-sm font-semibold text-red-500">
                        {{ $isEnglish ? 'Sign Out' : 'Keluar' }}
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-10 lg:py-10">

        {{-- Breadcrumb --}}
        <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm">
            <a href="{{ route('family.dashboard') }}"
                class="font-medium text-blue-500 hover:underline">
                Dashboard
            </a>

            <span style="color: var(--family-text-secondary);">/</span>

            <a href="{{ route('family.groups.show', $group->id) }}"
                class="font-medium text-blue-500 hover:underline">
                {{ $group->name }}
            </a>

            <span style="color: var(--family-text-secondary);">/</span>

            <span style="color: var(--family-text-secondary);">
                {{ $isEnglish ? 'Add Member' : 'Tambah Anggota' }}
            </span>
        </nav>

        {{-- Heading --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight sm:text-3xl"
                style="color: var(--family-text-primary);">
                {{ $isEnglish ? 'Add Family Member' : 'Tambah Anggota Keluarga' }}
            </h1>

            <p class="mt-2 text-sm"
                style="color: var(--family-text-secondary);">
                {{ $isEnglish
                    ? 'Add an existing individual to this family group.'
                    : 'Tambahkan individu yang sudah terdaftar ke Family Group ini.' }}
            </p>

            <p class="mt-2 text-sm font-semibold text-blue-500">
                {{ $group->name }}
            </p>
        </div>

        {{-- Form --}}
        <section class="rounded-2xl border p-5 sm:p-7"
            style="background: var(--family-surface); border-color: var(--family-border);">

            <form method="POST"
                action="{{ route('family.groups.members.store', $group->id) }}"
                class="space-y-6">

                @csrf

                {{-- Individual --}}
                <div>
                    <label for="person_id"
                        class="mb-2 block text-sm font-semibold"
                        style="color: var(--family-text-primary);">
                        {{ $isEnglish ? 'Individual' : 'Individu' }}
                        <span class="text-red-500">*</span>
                    </label>

                    <select
                        id="person_id"
                        name="person_id"
                        required
                        class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                        style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">

                        <option value="">
                            {{ $isEnglish ? '-- Select an individual --' : '-- Pilih individu --' }}
                        </option>

                        @foreach ($people as $person)
                            <option value="{{ $person->id }}"
                                @selected((string) old('person_id') === (string) $person->id)>
                                {{ $person->full_name }}
                                @if ($person->nickname)
                                    ({{ $person->nickname }})
                                @endif
                            </option>
                        @endforeach
                    </select>

                    @error('person_id')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror

                    @if ($people->isEmpty())
                        <p class="mt-2 text-sm"
                            style="color: var(--family-text-secondary);">
                            {{ $isEnglish
                                ? 'No eligible individuals are available. Add an individual to the family database first, or check existing memberships.'
                                : 'Belum ada individu yang dapat ditambahkan. Daftarkan individu terlebih dahulu atau periksa keanggotaan yang sudah ada.' }}
                        </p>
                    @else
                        <p class="mt-2 text-xs"
                            style="color: var(--family-text-secondary);">
                            {{ $isEnglish
                                ? 'Only individuals who do not already have a membership record in this group are shown.'
                                : 'Hanya individu yang belum memiliki catatan keanggotaan di grup ini yang ditampilkan.' }}
                        </p>
                    @endif
                </div>

                {{-- Role --}}
                <div>
                    <label for="role_id"
                        class="mb-2 block text-sm font-semibold"
                        style="color: var(--family-text-primary);">
                        {{ $isEnglish ? 'Group Role' : 'Peran dalam Grup' }}
                        <span class="text-red-500">*</span>
                    </label>

                    <select
                        id="role_id"
                        name="role_id"
                        required
                        class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                        style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">

                        <option value="">
                            {{ $isEnglish ? '-- Select a role --' : '-- Pilih peran --' }}
                        </option>

                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}"
                                @selected((string) old('role_id') === (string) $role->id)>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('role_id')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror

                    <p class="mt-2 text-xs"
                        style="color: var(--family-text-secondary);">
                        {{ $isEnglish
                            ? 'Available roles depend on your permissions in this group.'
                            : 'Pilihan peran mengikuti hak akses Anda di grup ini.' }}
                    </p>
                </div>

                {{-- Reason --}}
                <div>
                    <label for="reason"
                        class="mb-2 block text-sm font-semibold"
                        style="color: var(--family-text-primary);">
                        {{ $isEnglish ? 'Reason' : 'Alasan Penambahan' }}
                        <span class="text-xs font-normal"
                            style="color: var(--family-text-secondary);">
                            ({{ $isEnglish ? 'Optional' : 'Opsional' }})
                        </span>
                    </label>

                    <textarea
                        id="reason"
                        name="reason"
                        rows="4"
                        maxlength="500"
                        class="w-full resize-y rounded-xl border px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                        style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);"
                        placeholder="{{ $isEnglish
                            ? 'Write a reason for adding this member...'
                            : 'Tuliskan alasan penambahan anggota...' }}">{{ old('reason') }}</textarea>

                    @error('reason')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Information --}}
                <div class="rounded-xl border p-4"
                    style="background: var(--family-background); border-color: var(--family-border);">

                    <p class="text-sm font-semibold"
                        style="color: var(--family-text-primary);">
                        {{ $isEnglish ? 'Important Information' : 'Informasi Penting' }}
                    </p>

                    <p class="mt-2 text-sm leading-6"
                        style="color: var(--family-text-secondary);">
                        {{ $isEnglish
                            ? 'New memberships are initially saved with Pending status. The member will not receive active group access until the membership is approved and activated.'
                            : 'Keanggotaan baru akan disimpan dengan status Menunggu (Pending). Anggota belum mendapatkan akses aktif ke grup sampai keanggotaannya disetujui dan diaktifkan.' }}
                    </p>
                </div>

                {{-- General errors --}}
                @if ($errors->has('group') || $errors->has('data'))
                    <div class="rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-500">
                        {{ $errors->first('group') ?: $errors->first('data') }}
                    </div>
                @endif

                {{-- Actions --}}
                <div class="flex flex-col-reverse gap-3 border-t pt-5 sm:flex-row sm:justify-end"
                    style="border-color: var(--family-border);">

                    <a href="{{ route('family.groups.show', $group->id) }}"
                        class="inline-flex items-center justify-center rounded-xl border px-5 py-3 text-sm font-semibold transition hover:bg-blue-500/5"
                        style="border-color: var(--family-border); color: var(--family-text-primary);">
                        {{ $isEnglish ? 'Cancel' : 'Batal' }}
                    </a>

                    <button type="submit"
                        @disabled($people->isEmpty() || $roles->isEmpty())
                        class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50">
                        {{ $isEnglish ? 'Add Member' : 'Tambah Anggota' }}
                    </button>
                </div>

            </form>
        </section>
    </main>
</div>

@endsection
