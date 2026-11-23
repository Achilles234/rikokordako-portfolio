
@php
    $isEnglish = session('family_locale', 'id') === 'en';
    $navigationGroup = $navigationGroup ?? $selectedGroup ?? $group ?? null;
    $displayName = $displayName ?? auth('family')->user()?->name ?? 'Family Member';
    $isSuperAdmin = $isSuperAdmin ?? false;
    $activeMenu = $activeMenu ?? '';

    $comingSoonItems = $isEnglish
        ? [
            ['Members', 'users'],
            ['Family Tree', 'git-branch'],
            ['Location Sharing', 'map-pin'],
        ]
        : [
            ['Anggota Keluarga', 'users'],
            ['Pohon Keluarga', 'git-branch'],
            ['Berbagi Lokasi', 'map-pin'],
        ];
@endphp

<aside
    data-family-sidebar
    class="hidden h-screen w-64 shrink-0 flex-col border-r lg:flex sticky top-0"
    style="background: var(--family-surface); border-color: var(--family-border);"
>
   <div class="flex h-20 items-center justify-between gap-3 border-b px-4"
    style="border-color: var(--family-border);">

    <div class="flex min-w-0 items-center gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white">
            <svg viewBox="0 0 24 24" fill="none"
                class="h-6 w-6" stroke="currentColor" stroke-width="1.8">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>

        <div class="min-w-0">
            <p class="truncate font-bold tracking-tight"
                style="color: var(--family-text-primary);">
                Family Portal
            </p>
            <p class="truncate text-xs"
                style="color: var(--family-text-secondary);">
                Private Family Space
            </p>
        </div>
    </div>

    <button
        type="button"
        data-family-sidebar-pin
        aria-label="{{ $isEnglish ? 'Pin sidebar' : 'Pin sidebar' }}"
        aria-pressed="true"
        title="{{ $isEnglish ? 'Pin sidebar' : 'Pin sidebar' }}"
        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg transition hover:bg-blue-500/10"
        style="color: var(--family-text-secondary);"
    >
        <svg viewBox="0 0 24 24"
            class="h-5 w-5"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
            stroke-linejoin="round">
            <path d="m16 3 5 5-4 1-4 4-1 4-2-2-5 5"/>
            <path d="m8 8 8 8"/>
        </svg>
    </button>
</div>

    <nav class="min-h-0 flex-1 overflow-y-auto space-y-1 px-4 py-6"
        aria-label="{{ $isEnglish ? 'Main navigation' : 'Navigasi utama' }}">

        <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider"
            style="color: var(--family-text-secondary);">
            {{ $isEnglish ? 'Workspace' : 'Ruang Keluarga' }}
        </p>

        <a href="{{ route('family.dashboard') }}"
            @class([
                'flex items-center gap-3 rounded-xl px-3 py-3 text-sm transition',
                'bg-blue-500/10 font-semibold text-blue-500' => $activeMenu === 'dashboard',
                'font-medium hover:bg-blue-500/10' => $activeMenu !== 'dashboard',
            ])
            @if ($activeMenu !== 'dashboard')
                style="color: var(--family-text-primary);"
            @endif>
            <span aria-hidden="true">▦</span>
            Dashboard
        </a>

        @if ($navigationGroup)
            <a href="{{ route('family.groups.people.index', $navigationGroup->id) }}"
                @class([
                    'flex items-center gap-3 rounded-xl px-3 py-3 text-sm transition',
                    'bg-blue-500/10 font-semibold text-blue-500' => $activeMenu === 'people',
                    'font-medium hover:bg-blue-500/10' => $activeMenu !== 'people',
                ])
                @if ($activeMenu !== 'people')
                    style="color: var(--family-text-primary);"
                @endif>
                <span aria-hidden="true">👥</span>
                <span class="min-w-0 flex-1">
                    {{ $isEnglish ? 'People Data' : 'Data Individu' }}
                    <span class="block truncate text-xs font-normal"
                        style="color: var(--family-text-secondary);">
                        {{ $navigationGroup->name }}
                    </span>
                </span>
            </a>
        @else
            <div class="flex cursor-not-allowed items-center gap-3 rounded-xl px-3 py-3 text-sm opacity-50"
                style="color: var(--family-text-secondary);">
                <span aria-hidden="true">👥</span>
                {{ $isEnglish ? 'People Data' : 'Data Individu' }}
            </div>
        @endif

                <p class="mb-3 mt-8 px-3 text-xs font-semibold uppercase tracking-wider"
            style="color: var(--family-text-secondary);">
            {{ $isEnglish ? 'Family Features' : 'Fitur Keluarga' }}
        </p>

        <!-- MENU AGENDA KELUARGA DI SINI -->
        <a href="{{ route('family.agendas.index') }}"
            @class([
                'flex items-center gap-3 rounded-xl px-3 py-3 text-sm transition',
                'bg-blue-500/10 font-semibold text-blue-500' => request()->routeIs('family.agendas.*', 'family.groups.agendas.*'),
                'font-medium hover:bg-blue-500/10' => !request()->routeIs('family.agendas.*', 'family.groups.agendas.*'),
            ])
            @if (!request()->routeIs('family.agendas.*', 'family.groups.agendas.*'))
                style="color: var(--family-text-primary);"
            @endif>
            <span aria-hidden="true">▦</span>
            {{ $isEnglish ? 'Family Agenda' : 'Agenda Keluarga' }}
        </a>

        {{-- MENU ALBUM FOTO --}}
        <a href="{{ route('family.photo-albums.index') }}"
            @class([
                'flex items-center gap-3 rounded-xl px-3 py-3 text-sm transition',
                'bg-blue-500/10 font-semibold text-blue-500' => request()->routeIs('family.photo-albums.*'),
                'font-medium hover:bg-blue-500/10' => !request()->routeIs('family.photo-albums.*'),
            ])
            @if (!request()->routeIs('family.photo-albums.*'))
                style="color: var(--family-text-primary);"
            @endif>
            <span aria-hidden="true">▧</span>
            {{ $isEnglish ? 'Photo Albums' : 'Album Foto' }}
        </a>

        {{-- MENU FOTO FAVORIT --}}
        <a href="{{ route('family.photo-albums.favorites') }}"
            @class([
                'flex items-center gap-3 rounded-xl px-3 py-3 text-sm transition',
                'bg-amber-500/10 font-semibold text-amber-400' => request()->routeIs('family.photo-albums.favorites'),
                'font-medium hover:bg-amber-500/10' => !request()->routeIs('family.photo-albums.favorites'),
            ])
            @if (!request()->routeIs('family.photo-albums.favorites'))
                style="color: var(--family-text-primary);"
            @endif>
            <span aria-hidden="true">♥</span>
            {{ $isEnglish ? 'My Favorite Photos' : 'Foto Favorit' }}
        </a>


      @foreach ($comingSoonItems as $item)
    @if ($item[1] === 'calendar-days')
        <a href="{{ route('family.agendas.index') }}"
            @class([
                'flex items-center gap-3 rounded-xl px-3 py-3 text-sm transition',
                'bg-blue-500/10 font-semibold text-blue-500' => request()->routeIs('family.agendas.*', 'family.groups.agendas.*'),
                'font-medium hover:bg-blue-500/10' => !request()->routeIs('family.agendas.*', 'family.groups.agendas.*'),
            ])
            @if (!request()->routeIs('family.agendas.*', 'family.groups.agendas.*'))
                style="color: var(--family-text-primary);"
            @endif>
            <span aria-hidden="true">▦</span>
            {{ $item[0] }}
        </a>
    @elseif ($item[1] === 'git-branch' && $navigationGroup)
        <a href="{{ route('family.groups.tree', $navigationGroup->id) }}"
            @class([
                'flex items-center gap-3 rounded-xl px-3 py-3 text-sm transition',
                'bg-blue-500/10 font-semibold text-blue-500' => $activeMenu === 'tree',
                'font-medium hover:bg-blue-500/10' => $activeMenu !== 'tree',
            ])
            @if ($activeMenu !== 'tree')
                style="color: var(--family-text-primary);"
            @endif>
            <span aria-hidden="true">⌘</span>
            {{ $item[0] }}
        </a>
    @else
        <div class="flex cursor-not-allowed items-center justify-between rounded-xl px-3 py-3 text-sm opacity-55"
            style="color: var(--family-text-secondary);"
            aria-disabled="true">
            <span class="flex items-center gap-3">
                <span aria-hidden="true">
                    @switch($item[1])
                        @case('users') ♧ @break
                        @case('git-branch') ⌘ @break
                        @case('images') ▧ @break
                        @case('map-pin') ⌖ @break
                    @endswitch
                </span>
                {{ $item[0] }}
            </span>
            <span class="text-[10px]">
                {{ $isEnglish ? 'Soon' : 'Nanti' }}
            </span>
        </div>
    @endif
@endforeach

        <p class="mb-3 mt-8 px-3 text-xs font-semibold uppercase tracking-wider"
            style="color: var(--family-text-secondary);">
            {{ $isEnglish ? 'Account' : 'Akun' }}
        </p>

        <div class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm opacity-55"
            style="color: var(--family-text-secondary);">
            <span>⚙</span>
            {{ $isEnglish ? 'Settings' : 'Pengaturan' }}
            <span class="ml-auto text-[10px]">
                {{ $isEnglish ? 'Soon' : 'Nanti' }}
            </span>
        </div>
    </nav>

    <div class="border-t p-4" style="border-color: var(--family-border);">
        <div class="flex items-center gap-3 rounded-xl p-3"
            style="background: var(--family-surface-elevated);">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-500/15 font-bold text-blue-500">
                {{ mb_strtoupper(mb_substr($displayName, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold"
                    style="color: var(--family-text-primary);">
                    {{ $displayName }}
                </p>
                <p class="text-xs" style="color: var(--family-text-secondary);">
                    {{ $isSuperAdmin
                        ? 'Super Admin'
                        : ($isEnglish ? 'Family Portal Member' : 'Anggota Family Portal') }}
                </p>
            </div>
        </div>
    </div>
</aside>
