
@php
    $isEnglish = session('family_locale', 'id') === 'en';
    $navigationGroup = $navigationGroup ?? $selectedGroup ?? $group ?? null;

    $activeMenu = $activeMenu ?? '';

    $comingSoonItems = $isEnglish
        ? [
            ['Members', 'users'],
            ['Family Tree', 'git-branch'],
            ['Family Agenda', 'calendar-days'],
            ['Photo Albums', 'images'],
            ['Location Sharing', 'map-pin'],
        ]
        : [
            ['Anggota Keluarga', 'users'],
            ['Pohon Keluarga', 'git-branch'],
            ['Agenda Keluarga', 'calendar-days'],
            ['Album Foto', 'images'],
            ['Berbagi Lokasi', 'map-pin'],
        ];
@endphp

<header data-family-topbar
    class="sticky top-0 z-20 border-b px-4 sm:px-6 lg:px-10"
    style="border-color: var(--family-border);">

    <div class="flex h-16 items-center justify-between gap-4">
        <div>
            <p class="text-sm font-bold lg:hidden"
                style="color: var(--family-text-primary);">
                Family Portal
            </p>
            <p class="hidden text-sm lg:block"
                style="color: var(--family-text-secondary);">
                {{ $isEnglish ? 'Your family space' : 'Ruang keluarga Anda' }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <details class="relative lg:hidden">
                <summary
                    class="flex cursor-pointer list-none items-center gap-2 rounded-xl border px-3 py-2 text-sm font-medium"
                    style="border-color: var(--family-border); color: var(--family-text-primary);">
                    <span aria-hidden="true">☰</span>
                    <span>{{ $isEnglish ? 'Menu' : 'Menu' }}</span>
                </summary>

                <nav class="absolute right-0 top-full z-50 mt-3 w-64 rounded-2xl border p-3 shadow-xl"
                    style="background: var(--family-surface); border-color: var(--family-border);"
                    aria-label="{{ $isEnglish ? 'Mobile navigation' : 'Navigasi mobile' }}">

                    <a href="{{ route('family.dashboard') }}"
                        class="block rounded-xl px-4 py-3 text-sm font-semibold transition hover:bg-blue-500/10"
                        style="color: var(--family-text-primary);">
                        Dashboard
                    </a>

                    @if ($navigationGroup)
                        <a href="{{ route('family.groups.people.index', $navigationGroup->id) }}"
                            class="mt-1 flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition hover:bg-blue-500/10"
                            style="color: var(--family-text-primary);">
                            <span aria-hidden="true">👥</span>
                            <span>
                                {{ $isEnglish ? 'People Data' : 'Data Individu' }}
                                <span class="block text-xs font-normal"
                                    style="color: var(--family-text-secondary);">
                                    {{ $navigationGroup->name }}
                                </span>
                            </span>
                        </a>
                    @else
                        <div class="mt-1 rounded-xl px-4 py-3 text-sm opacity-50"
                            style="color: var(--family-text-secondary);">
                            👥 {{ $isEnglish ? 'People Data' : 'Data Individu' }}
                            <span class="block text-xs">
                                {{ $isEnglish ? 'Select a group first' : 'Pilih grup terlebih dahulu' }}
                            </span>
                        </div>
                    @endif

                    <div class="mt-3 border-t pt-3"
                        style="border-color: var(--family-border);">
                        <p class="px-4 py-2 text-xs font-semibold uppercase"
                            style="color: var(--family-text-secondary);">
                            {{ $isEnglish ? 'Family Features' : 'Fitur Keluarga' }}
                        </p>

                        @foreach ($comingSoonItems as $item)
                            @if ($item[1] === 'git-branch' && $navigationGroup)
                                <a href="{{ route('family.groups.tree', $navigationGroup->id) }}"
                                    @class([
                                        'block rounded-xl px-4 py-3 text-sm font-medium transition hover:bg-blue-500/10',
                                        'bg-blue-500/10 font-semibold text-blue-500' => $activeMenu === 'tree',
                                    ])
                                    @if ($activeMenu !== 'tree')
                                        style="color: var(--family-text-primary);"
                                    @endif>
                                    {{ $item[0] }}
                                    <span class="float-right text-xs text-blue-500">
                                        {{ $isEnglish ? 'Open' : 'Buka' }}
                                    </span>
                                </a>
                            @else
                                <div class="rounded-xl px-4 py-3 text-sm opacity-60"
                                    style="color: var(--family-text-secondary);"
                                    aria-disabled="true">
                                    {{ $item[0] }}
                                    <span class="float-right text-xs">
                                        {{ $isEnglish ? 'Soon' : 'Nanti' }}
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </nav>
            </details>

            <button type="button"
                data-family-theme-toggle
                class="rounded-xl border px-3 py-2 text-sm transition hover:opacity-75"
                style="border-color: var(--family-border); color: var(--family-text-secondary);"
                aria-label="{{ $isEnglish ? 'Toggle theme' : 'Ganti tema' }}">
                <span aria-hidden="true">◐</span>
                <span class="hidden family-theme-light:inline">☾</span>
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
    </div>
</header>
