
@extends('layouts.family')

@section('title', session('family_locale', 'id') === 'en'
    ? 'Dashboard — Family Portal'
    : 'Dashboard — Portal Keluarga'
)

@section('content')

@php
    $isEnglish = session('family_locale', 'id') === 'en';

    $statusLabels = [
        'active' => $isEnglish ? 'Active' : 'Aktif',
        'inactive' => $isEnglish ? 'Inactive' : 'Tidak aktif',
        'archived' => $isEnglish ? 'Archived' : 'Diarsipkan',
    ];
@endphp

<div class="h-screen overflow-hidden" style="background: var(--family-background);">

    <div class="flex h-full min-h-0">

       {{-- Shared desktop sidebar --}}
        @include('family.partials.sidebar', [
            'navigationGroup' => $selectedGroup,
            'displayName' => $displayName,
            'isSuperAdmin' => $isSuperAdmin,
            'activeMenu' => 'dashboard',
        ])

        {{-- Main area --}}
       <div class="flex min-h-0 min-w-0 flex-1 flex-col">

           {{-- Shared topbar --}}
            @include('family.partials.topbar', [
                'navigationGroup' => $selectedGroup,
            ])

        <main id="family-main" class="min-h-0 flex-1 overflow-y-auto">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-10 lg:py-10">

                {{-- Welcome --}}
                <section class="mb-8">
                    <p class="mb-2 text-sm font-semibold uppercase tracking-wider text-blue-500">
                        {{ $isEnglish ? 'Family Overview' : 'Ringkasan Keluarga' }}
                    </p>

                    <h1 class="text-2xl font-bold tracking-tight sm:text-3xl"
                        style="color: var(--family-text-primary);">
                        {{ $isEnglish ? 'Welcome back' : 'Selamat datang kembali' }},
                        {{ $displayName }}
                    </h1>

                    <p class="mt-2 text-sm leading-6"
                        style="color: var(--family-text-secondary);">
                        {{ $isEnglish
                            ? 'Stay connected with the people who matter most.'
                            : 'Tetap terhubung dengan orang-orang yang paling berarti bagi Anda.' }}
                    </p>
                </section>

                {{-- Group filter --}}
                <section class="mb-6 rounded-2xl border p-4 sm:p-5"
                    style="background: var(--family-surface); border-color: var(--family-border);">

                    <form method="GET" action="{{ route('family.dashboard') }}"
                        class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">

                        <div class="min-w-0 flex-1">
                            <label for="family-group-filter"
                                class="mb-2 block text-xs font-semibold uppercase tracking-wider"
                                style="color: var(--family-text-secondary);">
                                {{ $isEnglish ? 'Currently viewing' : 'Tampilan grup' }}
                            </label>

                            <select
                                id="family-group-filter"
                                name="group"
                                class="w-full rounded-xl border px-4 py-3 text-sm outline-none focus:border-blue-500"
                                style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);"
                            >
                                <option value="">
                                    {{ $isEnglish ? 'All my family groups' : 'Semua Family Group' }}
                                </option>

                                @foreach ($groups as $group)
                                    <option value="{{ $group->id }}"
                                        @selected($selectedGroup?->id === $group->id)>
                                        {{ $group->name }}
                                        @if ($canViewAllGroups && $group->status !== 'active')
                                            — {{ $statusLabels[$group->status] ?? $group->status }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                       <button type="submit" class="family-leaf-button">
                            {{ $isEnglish ? 'Apply Filter' : 'Terapkan Filter' }}
                        </button>
                    </form>
                </section>

                {{-- Summary cards --}}
                <section class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-3">

                    <div class="rounded-2xl border p-5"
                        style="background: var(--family-surface); border-color: var(--family-border);">
                        <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/10 text-blue-500">
                            <svg viewBox="0 0 24 24" fill="none"
                                class="h-5 w-5" stroke="currentColor" stroke-width="1.8">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
                        <p class="text-3xl font-bold tabular-nums"
                            style="color: var(--family-text-primary);">
                            {{ number_format($activeMemberCount) }}
                        </p>
                        <p class="mt-1 text-sm"
                            style="color: var(--family-text-secondary);">
                            {{ $isEnglish ? 'Active family members' : 'Anggota keluarga aktif' }}
                        </p>
                    </div>

                    <div class="rounded-2xl border p-5"
                        style="background: var(--family-surface); border-color: var(--family-border);">
                        <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-violet-500/10 text-violet-500">
                            <svg viewBox="0 0 24 24" fill="none"
                                class="h-5 w-5" stroke="currentColor" stroke-width="1.8">
                                <path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-5h6v5"/>
                                <path d="M9 9h.01M15 9h.01"/>
                            </svg>
                        </div>
                        <p class="text-3xl font-bold tabular-nums"
                            style="color: var(--family-text-primary);">
                            {{ number_format($groupCount) }}
                        </p>
                        <p class="mt-1 text-sm"
                            style="color: var(--family-text-secondary);">
                            {{ $isEnglish ? 'Family groups' : 'Family Group' }}
                        </p>
                    </div>

                    <div class="rounded-2xl border p-5"
                        style="background: var(--family-surface); border-color: var(--family-border);">
                        <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-500">
                            <svg viewBox="0 0 24 24" fill="none"
                                class="h-5 w-5" stroke="currentColor" stroke-width="1.8">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="m8 12 2.5 2.5L16 9"/>
                            </svg>
                        </div>
                        <p class="text-3xl font-bold tabular-nums"
                            style="color: var(--family-text-primary);">
                            {{ number_format($activeGroupCount) }}
                        </p>
                        <p class="mt-1 text-sm"
                            style="color: var(--family-text-secondary);">
                            {{ $isEnglish ? 'Active groups' : 'Grup aktif' }}
                        </p>
                    </div>
                </section>

                        {{-- Family groups --}}
             <section>
                <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-bold"
                            style="color: var(--family-text-primary);">
                            {{ $isEnglish ? 'Your Family Groups' : 'Family Group Anda' }}
                        </h2>
                        <p class="mt-1 text-sm"
                            style="color: var(--family-text-secondary);">
                            {{ $isEnglish
                                ? 'An overview of the groups available to you.'
                                : 'Ringkasan grup keluarga yang dapat Anda akses.' }}
                        </p>
                    </div>

                    @if ($canCreateGroup)
                        <a href="{{ route('family.groups.create') }}"
                            class="family-leaf-button inline-flex w-fit shrink-0 items-center justify-center gap-2">
                            <span class="text-lg leading-none" aria-hidden="true">+</span>
                            {{ $isEnglish ? 'Create Family Group' : 'Buat Family Group' }}
                        </a>
                    @endif
                </div>

                    @if ($visibleGroups->isNotEmpty())
                        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">

                            @foreach ($visibleGroups as $group)
                                <a
                                  href="{{ route('family.groups.show', $group->id) }}"
                                    class="group rounded-2xl border p-5 transition hover:-translate-y-0.5 hover:border-blue-500/50"
                                    style="background: var(--family-surface); border-color: var(--family-border);"
                                >
                                    <div class="flex items-start justify-between gap-4">

                                        <div class="flex min-w-0 items-center gap-3">
                                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-500/10 text-blue-500">
                                                <svg viewBox="0 0 24 24" fill="none"
                                                    class="h-6 w-6" stroke="currentColor" stroke-width="1.7">
                                                    <path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-5h6v5"/>
                                                </svg>
                                            </div>

                                            <div class="min-w-0">
                                                <h3 class="truncate font-bold"
                                                    style="color: var(--family-text-primary);">
                                                    {{ $group->name }}
                                                </h3>
                                                <p class="mt-1 text-xs capitalize"
                                                    style="color: var(--family-text-secondary);">
                                                    {{ str_replace('_', ' ', $group->group_type) }}
                                                </p>
                                                @php
                                                    $roleSlug = $groupRoles[$group->id] ?? 'family_member';

                                                    $roleLabel = match ($roleSlug) {
                                                        'super_admin' => $isEnglish ? 'Super Admin' : 'Super Admin',
                                                        'family_admin' => $isEnglish ? 'Family Admin' : 'Admin Keluarga',
                                                        default => $isEnglish ? 'Family Member' : 'Anggota Keluarga',
                                                    };
                                                @endphp

                                                <span class="mt-2 inline-flex rounded-full bg-blue-500/10 px-2.5 py-1 text-xs font-semibold text-blue-500">
                                                    {{ $roleLabel }}
                                                </span>

                                            </div>
                                        </div>

                                        <span class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold
                                            {{ $group->status === 'active'
                                                ? 'bg-emerald-500/10 text-emerald-500'
                                                : ($group->status === 'inactive'
                                                    ? 'bg-amber-500/10 text-amber-500'
                                                    : 'bg-slate-500/10 text-slate-400') }}">
                                            {{ $statusLabels[$group->status] ?? $group->status }}
                                        </span>
                                    </div>

                                    <p class="mt-4 line-clamp-2 min-h-10 text-sm leading-5"
                                        style="color: var(--family-text-secondary);">
                                        {{ $group->description
                                            ?: ($isEnglish
                                                ? 'A private space for your family.'
                                                : 'Ruang privat untuk keluarga Anda.') }}
                                    </p>

                                    <div class="mt-5 flex items-center justify-between border-t pt-4"
                                        style="border-color: var(--family-border);">

                                        <span class="text-sm"
                                            style="color: var(--family-text-secondary);">
                                            {{ number_format($group->active_members_count) }}
                                            {{ $isEnglish ? 'active members' : 'anggota aktif' }}
                                        </span>

                                        <span class="text-sm font-semibold text-blue-500 transition group-hover:translate-x-1">
                                            {{ $isEnglish ? 'View group' : 'Lihat grup' }} →
                                        </span>
                                    </div>
                                </a>
                            @endforeach

                        </div>
                    @else
                        <div class="rounded-2xl border px-6 py-12 text-center"
                            style="background: var(--family-surface); border-color: var(--family-border);">

                            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-500/10 text-blue-500">
                                <svg viewBox="0 0 24 24" fill="none"
                                    class="h-7 w-7" stroke="currentColor" stroke-width="1.7">
                                    <path d="M3 21h18M5 21V7l7-4 7 4v14"/>
                                    <path d="M9 12h6M12 9v6"/>
                                </svg>
                            </div>

                            <h3 class="font-bold"
                                style="color: var(--family-text-primary);">
                                {{ $isEnglish ? 'No family groups to display' : 'Belum ada Family Group untuk ditampilkan' }}
                            </h3>

                            <p class="mx-auto mt-2 max-w-md text-sm leading-6"
                                style="color: var(--family-text-secondary);">
                                {{ $isEnglish
                                    ? 'Family groups you are allowed to access will appear here.'
                                    : 'Family Group yang dapat Anda akses akan ditampilkan di sini.' }}
                            </p>
                        </div>
                    @endif
                </section>

                                <footer class="mt-12 border-t py-6 text-center text-xs"
                    style="border-color: var(--family-border); color: var(--family-text-secondary);">
                    © {{ date('Y') }} Family Portal ·
                    {{ $isEnglish ? 'A private space for family connection.' : 'Ruang privat untuk menjaga hubungan keluarga.' }}
                </footer>

            </div>
        </main>
        </div>
    </div>
</div>

@endsection