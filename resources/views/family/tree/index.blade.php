@extends('layouts.family')

@section('title', 'Pohon Keluarga')

@push('styles')
<style>
    .tree-page {
        --tree-male: #3b82f6;
        --tree-female: #c026d3;
        --tree-neutral: #94a3b8;
        --tree-line: color-mix(in srgb, var(--family-text-secondary) 55%, transparent);
        --tree-up: #b45309;      /* jalur leluhur */
        --tree-down: #0f766e;    /* jalur keturunan */
        --tree-peer: #64748b;    /* saudara & pasangan */
        --tree-glass: color-mix(in srgb, var(--family-surface) 86%, transparent);
        --tree-wood-face:
            repeating-linear-gradient(91deg, transparent 0 11px, rgba(110, 65, 25, .10) 11px 12px, rgba(255, 240, 205, .12) 12px 17px, transparent 17px 29px),
            repeating-linear-gradient(88deg, transparent 0 41px, rgba(110, 65, 25, .08) 41px 43px),
            linear-gradient(135deg, #e6c694 0%, #d3a96f 52%, #deba84 100%);
        --tree-wood:
            repeating-linear-gradient(91deg, transparent 0 9px, rgba(70, 35, 10, .16) 9px 10px, rgba(255, 225, 180, .08) 10px 14px, transparent 14px 23px),
            repeating-linear-gradient(88deg, transparent 0 31px, rgba(50, 25, 8, .18) 31px 33px),
            linear-gradient(135deg, #c18d55 0%, #94622f 48%, #b07a45 100%);
    }

    .tree-page [data-sex="male"] { --sex: var(--tree-male); }
    .tree-page [data-sex="female"] { --sex: var(--tree-female); }
    .tree-page [data-sex="unknown"] { --sex: var(--tree-neutral); }

    /* ---------- Header stats ---------- */
    .tree-stats { display: flex; gap: 10px; }

    .tree-stat {
        display: flex;
        align-items: baseline;
        gap: 7px;
        padding: 10px 16px;
        border: 1px solid var(--family-border);
        border-radius: 14px;
        background: var(--family-surface);
    }

    .tree-stat strong {
        color: var(--family-text-primary);
        font-size: 22px;
        font-weight: 800;
        line-height: 1;
    }

    .tree-stat span {
        color: var(--family-text-secondary);
        font-size: 12px;
    }

    /* ---------- Legend ---------- */
    .tree-legend {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px 18px;
        margin-bottom: 12px;
        color: var(--family-text-secondary);
        font-size: 12px;
    }

    .tree-legend-item { display: inline-flex; align-items: center; gap: 7px; }
    .tree-legend-swatch { width: 4px; height: 14px; border-radius: 2px; }
    .tree-legend-line { width: 22px; height: 0; border-top: 3px solid; border-radius: 2px; }

    /* ---------- Canvas ---------- */
    .tree-viewport {
        position: relative;
        width: 100%;
        height: min(76vh, 820px);
        min-height: 480px;
        overflow: hidden;
        border: 0;
        border-radius: 0;
        background-color: transparent;
        background-image: radial-gradient(
            color-mix(in srgb, var(--family-text-secondary) 26%, transparent) 1px,
            transparent 1px
        );
        background-size: 24px 24px;
        cursor: grab;
        touch-action: none;
        user-select: none;
    }

    .tree-viewport.no-grid { background-image: none; }

    .tree-btn[aria-pressed="true"] {
        background: color-mix(in srgb, var(--family-accent) 14%, transparent);
        color: var(--family-accent);
    }

    .tree-viewport.is-dragging { cursor: grabbing; }

    .tree-viewport.is-animating {
        transition: background-position .38s cubic-bezier(.22, .8, .3, 1),
                    background-size .38s cubic-bezier(.22, .8, .3, 1);
    }

    .tree-stage {
        position: absolute;
        top: 0;
        left: 0;
        width: max-content;
        min-width: 100%;
        padding: 70px 75px 80px;
        transform-origin: 0 0;
        will-change: transform;
    }

    .tree-stage.is-animating {
        transition: transform .38s cubic-bezier(.22, .8, .3, 1);
    }

    .tree-connections {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        overflow: visible;
        pointer-events: none;
        z-index: 1;
    }

    .tree-connection {
        fill: none;
        stroke: var(--tree-line);
        stroke-width: 1.75;
        stroke-linecap: round;
        stroke-linejoin: round;
        opacity: .7;
        transition: opacity .2s, stroke .2s;
    }

    .tree-connections.has-focus .tree-connection { opacity: .09; }

    .tree-connections.has-focus .tree-connection.is-up {
        stroke: var(--tree-up);
        stroke-width: 2.75;
        opacity: 1;
    }

    .tree-connections.has-focus .tree-connection.is-down {
        stroke: var(--tree-down);
        stroke-width: 2.75;
        opacity: 1;
    }

    /* ---------- Generation lanes ---------- */
    .tree-generation {
        position: relative;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        gap: 28px;
        width: max-content;
        min-width: 100%;
        margin-bottom: 112px;
    }

    .tree-generation:last-child { margin-bottom: 0; }

    .tree-generation-label {
        position: absolute;
        z-index: 2;
        top: -23px;
        left: 0;
        display: flex;
        align-items: baseline;
        gap: 8px;
        color: var(--family-text-primary);
        font-size: 12px;
        font-weight: 750;
        white-space: nowrap;
    }

    .tree-generation-label small {
        color: var(--family-text-secondary);
        font-size: 11px;
        font-weight: 500;
    }

    /* ---------- Person card ---------- */
    .tree-person {
        --sex: var(--tree-neutral);
        --ring: transparent;
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 12px;
        width: 220px;
        min-width: 220px;
        min-height: 82px;
        padding: 10px 14px 10px 18px;
        /* bingkai kayu = border transparan + background border-box */
        border: 6px solid transparent;
        border-radius: 18px;
        background-image: var(--tree-wood-face), var(--tree-wood);
        background-origin: border-box;
        background-clip: padding-box, padding-box, padding-box, border-box, border-box, border-box;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, .35),
            inset 0 0 0 1px rgba(40, 20, 5, .28),
            0 0 0 2px var(--ring),
            0 2px 3px rgba(40, 20, 5, .25),
            0 18px 22px -12px rgba(40, 20, 5, .45),
            0 30px 34px -20px rgba(15, 23, 42, .35);
        cursor: pointer;
        outline: none;
        transition: transform .25s cubic-bezier(.22, .8, .3, 1), box-shadow .25s, opacity .25s, filter .25s;
    }

    .tree-person::before {
        content: "";
        position: absolute;
        left: 0;
        top: 12px;
        bottom: 12px;
        width: 4px;
        border-radius: 0 4px 4px 0;
        background: var(--sex);
    }

    /* mengambang saat disentuh */
    .tree-person:hover {
        --ring: color-mix(in srgb, var(--family-accent) 70%, transparent);
        transform: translateY(-5px);
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, .35),
            inset 0 0 0 1px rgba(40, 20, 5, .28),
            0 0 0 2px var(--ring),
            0 3px 4px rgba(40, 20, 5, .25),
            0 26px 28px -12px rgba(40, 20, 5, .5),
            0 42px 44px -22px rgba(15, 23, 42, .4);
    }

    .tree-person:focus-visible { --ring: var(--family-accent); }

    .tree-person.is-match { --ring: var(--family-accent); }
    .tree-person.is-up { --ring: var(--tree-up); }
    .tree-person.is-down { --ring: var(--tree-down); }

    .tree-person.is-peer {
        outline: 2px dashed var(--tree-peer);
        outline-offset: 4px;
    }

    .tree-person.is-dimmed {
        opacity: .3;
        filter: saturate(.4);
    }

    .tree-person.is-selected {
        --ring: var(--family-accent);
        transform: translateY(-6px);
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, .35),
            inset 0 0 0 1px rgba(40, 20, 5, .28),
            0 0 0 3px var(--ring),
            0 4px 5px rgba(40, 20, 5, .25),
            0 30px 32px -12px rgba(40, 20, 5, .55),
            0 48px 50px -22px rgba(15, 23, 42, .45);
    }

    /* teks "diukir" di atas kayu, warnanya tetap sama di tema terang/gelap */
    .tree-person .tree-person-name {
        color: #2e1a0b;
        text-shadow: 0 1px 0 rgba(255, 240, 215, .55);
    }

    .tree-person .tree-person-meta { color: #5c3d22; }

    .tree-person .tree-avatar {
        background: rgba(255, 250, 240, .9);
        color: color-mix(in srgb, var(--sex) 72%, #2e1a0b);
        box-shadow: inset 0 0 0 1px rgba(60, 30, 10, .2), 0 1px 2px rgba(60, 30, 10, .25);
    }

    .tree-avatar {
        --sex: var(--tree-neutral);
        display: flex;
        width: 44px;
        height: 44px;
        min-width: 44px;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: color-mix(in srgb, var(--sex) 15%, transparent);
        color: color-mix(in srgb, var(--sex) 78%, var(--family-text-primary));
        font-size: 17px;
        font-weight: 800;
    }

    .tree-person-info { min-width: 0; flex: 1; }

    .tree-person-name {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        color: var(--family-text-primary);
        font-size: 14px;
        font-weight: 750;
        line-height: 1.3;
    }

    .tree-person-meta {
        margin-top: 4px;
        color: var(--family-text-secondary);
        font-size: 11.5px;
    }

    .tree-role {
        position: absolute;
        top: -17px;
        right: 6px;
        padding: 2px 9px;
        border-radius: 999px;
        background: var(--tree-peer);
        box-shadow: 0 2px 6px rgba(15, 23, 42, .2);
        color: #fff;
        font-size: 10.5px;
        font-weight: 700;
        line-height: 1.5;
        white-space: nowrap;
    }

    .tree-role[hidden] { display: none; }
    .tree-person.is-up .tree-role { background: var(--tree-up); }
    .tree-person.is-down .tree-role { background: var(--tree-down); }
    .tree-person.is-selected .tree-role { background: var(--family-accent); }

    /* ---------- Floating controls ---------- */
    .tree-ui { position: absolute; z-index: 5; cursor: default; }

    .tree-searchbox {
        left: 14px;
        bottom: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        width: min(320px, 100%);
        height: 42px;
        padding: 0 12px;
        border: 1px solid var(--family-border);
        border-radius: 14px;
        background: var(--tree-glass);
        -webkit-backdrop-filter: blur(12px);
        backdrop-filter: blur(12px);
        box-shadow: 0 4px 16px rgba(15, 23, 42, .07);
        color: var(--family-text-secondary);
        transition: border-color .2s, box-shadow .2s;
    }

    .tree-searchbox:focus-within {
        border-color: var(--family-accent);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--family-accent) 16%, transparent);
    }

    .tree-search {
        flex: 1;
        min-width: 0;
        border: 0;
        outline: 0;
        background: transparent;
        color: var(--family-text-primary);
        font-size: 13px;
    }

    .tree-search-count {
        color: var(--family-text-secondary);
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .tree-search-count[hidden] { display: none; }

    .tree-zoom {
        right: 14px;
        bottom: 14px;
        display: flex;
        align-items: center;
        gap: 2px;
        padding: 4px;
        border: 1px solid var(--family-border);
        border-radius: 14px;
        background: var(--tree-glass);
        -webkit-backdrop-filter: blur(12px);
        backdrop-filter: blur(12px);
        box-shadow: 0 4px 16px rgba(15, 23, 42, .07);
    }

    .tree-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-width: 34px;
        height: 34px;
        padding: 0 9px;
        border: 0;
        border-radius: 10px;
        background: transparent;
        color: var(--family-text-primary);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s, color .15s;
    }

    .tree-btn:hover {
        background: color-mix(in srgb, var(--family-accent) 12%, transparent);
        color: var(--family-accent);
    }

    .tree-btn:focus-visible {
        outline: 2px solid var(--family-accent);
        outline-offset: 1px;
    }

    .tree-btn svg { flex: none; }

    .tree-zoom-level {
        min-width: 46px;
        text-align: center;
        color: var(--family-text-secondary);
        font-size: 12px;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }

    .tree-zoom-grip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 34px;
        color: var(--family-text-secondary);
        opacity: .7;
        cursor: grab;
        touch-action: none;
    }

    .tree-zoom-grip:hover { opacity: 1; }
    .tree-zoom-grip:active { cursor: grabbing; }

    .tree-zoom-sep {
        width: 1px;
        height: 18px;
        margin: 0 4px;
        background: var(--family-border);
    }

    /* ---------- Detail panel ---------- */
    .tree-panel {
        top: 14px;
        right: 14px;
        display: flex;
        flex-direction: column;
        width: 300px;
        max-height: calc(100% - 92px);
        overflow: hidden;
        border: 1px solid var(--family-border);
        border-radius: 20px;
        background: var(--family-surface);
        box-shadow: 0 20px 50px rgba(15, 23, 42, .18);
        animation: treePanelIn .22s ease-out;
    }

    .tree-panel[hidden] { display: none; }

    @keyframes treePanelIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: none; }
    }

    .tree-panel-close { position: absolute; top: 10px; right: 10px; }

    .tree-panel-head {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 48px 16px 18px;
    }

    .tree-panel-name {
        color: var(--family-text-primary);
        font-size: 16px;
        font-weight: 800;
        line-height: 1.3;
        overflow-wrap: anywhere;
    }

    .tree-panel-meta {
        margin-top: 3px;
        color: var(--family-text-secondary);
        font-size: 12px;
    }

    .tree-panel-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        border-block: 1px solid var(--family-border);
    }

    .tree-panel-stats > div {
        display: flex;
        flex-direction: column;
        gap: 2px;
        padding: 12px 18px;
    }

    .tree-panel-stats > div + div { border-left: 1px solid var(--family-border); }

    .tree-panel-stats strong { font-size: 20px; font-weight: 800; line-height: 1.1; }
    .tree-panel-stats span { color: var(--family-text-secondary); font-size: 11.5px; }
    .tree-panel-stats .is-up strong { color: var(--tree-up); }
    .tree-panel-stats .is-down strong { color: var(--tree-down); }

    .tree-panel-body {
        display: flex;
        flex-direction: column;
        gap: 16px;
        padding: 16px 18px 18px;
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    .tree-panel-group h3 {
        margin-bottom: 8px;
        color: var(--family-text-secondary);
        font-size: 12px;
        font-weight: 700;
    }

    .tree-panel-chips { display: flex; flex-wrap: wrap; gap: 6px; }

    .tree-panel-empty {
        color: var(--family-text-secondary);
        font-size: 12.5px;
        line-height: 1.5;
    }

    .tree-chip {
        --sex: var(--tree-neutral);
        display: inline-flex;
        align-items: center;
        gap: 7px;
        max-width: 100%;
        padding: 6px 12px 6px 10px;
        border: 1px solid var(--family-border);
        border-radius: 999px;
        background: var(--family-surface-elevated, var(--family-surface));
        color: var(--family-text-primary);
        font-size: 12.5px;
        font-weight: 600;
        text-align: left;
        cursor: pointer;
        transition: border-color .15s, color .15s;
    }

    .tree-chip::before {
        content: "";
        flex: none;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--sex);
    }

    .tree-chip:hover { border-color: var(--family-accent); color: var(--family-accent); }

    .tree-chip:focus-visible {
        outline: 2px solid var(--family-accent);
        outline-offset: 1px;
    }

    .tree-empty { padding: 65px 20px; text-align: center; }

    @media (max-width: 767px) {
        .tree-viewport { height: 68vh; min-height: 440px; }
        .tree-stage { padding: 60px 40px 70px; }
        .tree-searchbox { top: 10px; left: 10px; right: 10px; bottom: auto; width: auto; }
        .tree-btn-label { display: none; }
        .tree-zoom { top: 62px; right: 10px; bottom: auto; }

        .tree-panel {
            top: auto;
            right: 10px;
            bottom: 10px;
            left: 10px;
            width: auto;
            max-height: 46%;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .tree-panel { animation: none; }
        .tree-person, .tree-connection { transition: none; }
    }
</style>
@endpush

@section('content')
<div class="tree-page min-h-screen" style="background: var(--family-background);">
    <div class="flex min-h-screen">

        @include('family.partials.sidebar', [
            'navigationGroup' => $group,
            'activeMenu' => 'tree',
        ])

        <div class="min-w-0 flex-1">

            @include('family.partials.topbar', [
                'navigationGroup' => $group,
                'activeMenu' => 'tree',
            ])

            <main id="family-main" class="mx-auto max-w-[1600px] px-4 py-7 sm:px-6 lg:px-9 lg:py-9">

                {{-- Header --}}
                <div class="mb-6">
                    <a href="{{ route('family.groups.show', $group->id) }}"
                       class="inline-flex items-center gap-2 text-sm font-medium hover:underline"
                       style="color: var(--family-accent);">
                        <span>←</span>
                        Kembali ke Family Group
                    </a>

                    <div class="mt-4 flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <h1 class="text-3xl font-extrabold tracking-tight"
                                style="color: var(--family-text-primary);">
                                Pohon Keluarga
                            </h1>

                            <p class="mt-2 text-sm" style="color: var(--family-text-secondary);">
                                Jelajahi hubungan dan garis keturunan keluarga {{ $group->name }}.
                            </p>
                        </div>

                        <div class="tree-stats">
                            <div class="tree-stat">
                                <strong>{{ $people->count() }}</strong>
                                <span>anggota</span>
                            </div>
                            <div class="tree-stat">
                                <strong>{{ $generationGroups->count() }}</strong>
                                <span>generasi</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($people->isEmpty())

                    <div class="rounded-3xl border"
                         style="background: var(--family-surface); border-color: var(--family-border);">
                        <div class="tree-empty">
                            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl text-4xl"
                                 style="background: var(--family-surface-elevated);">
                                🌳
                            </div>
                            <h2 class="mt-5 text-lg font-bold"
                                style="color: var(--family-text-primary);">
                                Pohon keluarga belum tersedia
                            </h2>
                            <p class="mx-auto mt-2 max-w-md text-sm"
                               style="color: var(--family-text-secondary);">
                                Tambahkan individu dan hubungan keluarga untuk mulai membangun pohon keluarga.
                            </p>
                        </div>
                    </div>

                @else

                    {{-- Legend --}}
                    <div class="tree-legend">
                        <span class="tree-legend-item">
                            <i class="tree-legend-swatch" style="background: var(--tree-male);"></i>
                            Laki-laki
                        </span>
                        <span class="tree-legend-item">
                            <i class="tree-legend-swatch" style="background: var(--tree-female);"></i>
                            Perempuan
                        </span>
                        <span class="tree-legend-item">
                            <i class="tree-legend-line" style="border-color: var(--tree-up);"></i>
                            Jalur leluhur
                        </span>
                        <span class="tree-legend-item">
                            <i class="tree-legend-line" style="border-color: var(--tree-down);"></i>
                            Jalur keturunan
                        </span>
                        <span>Klik anggota untuk menyorot jalur keluarganya. Garis menunjukkan hubungan orang tua biologis.</span>
                    </div>

                    {{-- Tree canvas --}}
                    <div id="tree-viewport" class="tree-viewport no-grid">

                        {{-- Kotak cari (default kiri bawah, bisa digeser) --}}
                        <div class="tree-searchbox tree-ui">
                            <span id="tree-search-grip" class="tree-zoom-grip" title="Seret untuk memindahkan, klik dua kali untuk mengembalikan">
                                <svg width="10" height="16" viewBox="0 0 10 16" fill="currentColor" aria-hidden="true">
                                    <circle cx="2" cy="3" r="1.4"></circle><circle cx="8" cy="3" r="1.4"></circle>
                                    <circle cx="2" cy="8" r="1.4"></circle><circle cx="8" cy="8" r="1.4"></circle>
                                    <circle cx="2" cy="13" r="1.4"></circle><circle cx="8" cy="13" r="1.4"></circle>
                                </svg>
                            </span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="11" cy="11" r="7"></circle>
                                <path d="m20 20-3.5-3.5"></path>
                            </svg>
                            <input
                                id="tree-search"
                                type="search"
                                class="tree-search"
                                placeholder="Cari nama anggota keluarga..."
                                autocomplete="off"
                                aria-label="Cari anggota keluarga"
                            >
                            <span id="tree-search-count" class="tree-search-count" hidden></span>
                        </div>

                        {{-- Zoom toolbar (default kanan bawah, bisa digeser) --}}
                        <div class="tree-zoom tree-ui">
                            <span id="tree-zoom-grip" class="tree-zoom-grip" title="Seret untuk memindahkan, klik dua kali untuk mengembalikan">
                                <svg width="10" height="16" viewBox="0 0 10 16" fill="currentColor" aria-hidden="true">
                                    <circle cx="2" cy="3" r="1.4"></circle><circle cx="8" cy="3" r="1.4"></circle>
                                    <circle cx="2" cy="8" r="1.4"></circle><circle cx="8" cy="8" r="1.4"></circle>
                                    <circle cx="2" cy="13" r="1.4"></circle><circle cx="8" cy="13" r="1.4"></circle>
                                </svg>
                            </span>
                            <button type="button" class="tree-btn" id="tree-zoom-out" aria-label="Perkecil" title="Perkecil">−</button>
                            <span id="tree-zoom-level" class="tree-zoom-level">100%</span>
                            <button type="button" class="tree-btn" id="tree-zoom-in" aria-label="Perbesar" title="Perbesar">+</button>
                            <span class="tree-zoom-sep"></span>
                            <button type="button" class="tree-btn" id="tree-fit" title="Sesuaikan ke layar">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M8 3H5a2 2 0 0 0-2 2v3M16 3h3a2 2 0 0 1 2 2v3M8 21H5a2 2 0 0 1-2-2v-3M16 21h3a2 2 0 0 0 2-2v-3"></path>
                                </svg>
                                <span class="tree-btn-label">Sesuaikan</span>
                            </button>
                            <button type="button" class="tree-btn" id="tree-reset" title="Reset tampilan dan pilihan">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M3 12a9 9 0 1 0 3-6.7L3 8"></path>
                                    <path d="M3 3v5h5"></path>
                                </svg>
                                <span class="tree-btn-label">Reset</span>
                            </button>
                            <button type="button" class="tree-btn" id="tree-grid-toggle" aria-pressed="false" title="Tampilkan/sembunyikan grid">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                                    <path d="M3 9h18M3 15h18M9 3v18M15 3v18"></path>
                                </svg>
                                <span class="tree-btn-label">Grid</span>
                            </button>
                        </div>

                        {{-- Detail panel --}}
                        <aside id="tree-panel" class="tree-ui tree-panel" hidden aria-label="Detail anggota keluarga">
                            <button type="button" id="tree-panel-close" class="tree-btn tree-panel-close"
                                    aria-label="Tutup detail">✕</button>

                            <div class="tree-panel-head">
                                <div id="tree-panel-avatar" class="tree-avatar"></div>
                                <div class="min-w-0">
                                    <h2 id="tree-panel-name" class="tree-panel-name"></h2>
                                    <p id="tree-panel-meta" class="tree-panel-meta"></p>
                                </div>
                            </div>

                            <div class="tree-panel-stats">
                                <div class="is-up">
                                    <strong id="tree-panel-up">0</strong>
                                    <span>Leluhur</span>
                                </div>
                                <div class="is-down">
                                    <strong id="tree-panel-down">0</strong>
                                    <span>Keturunan</span>
                                </div>
                            </div>

                            <div id="tree-panel-body" class="tree-panel-body"></div>
                        </aside>

                        <div id="tree-stage" class="tree-stage">

                            <svg id="tree-connections" class="tree-connections"
                                 xmlns="http://www.w3.org/2000/svg"
                                 aria-hidden="true"></svg>

                            @foreach ($generationGroups as $generation => $generationPeople)
                                <section class="tree-generation"
                                         data-generation="{{ $generation }}">

                                    <span class="tree-generation-label">
                                        Generasi {{ $generation + 1 }}
                                        <small>{{ count($generationPeople) }} orang</small>
                                    </span>

                                    @foreach ($generationPeople as $person)
                                        @php
                                            $parentIds = array_map(
                                                'intval',
                                                $parentsByChild[$person->id] ?? []
                                            );

                                            $isSelected = $centerPerson?->id === $person->id;

                                            $sexClass = match ($person->sex) {
                                                'male' => 'male',
                                                'female' => 'female',
                                                default => 'unknown',
                                            };

                                            $sexLabel = match ($person->sex) {
                                                'male' => 'Laki-laki',
                                                'female' => 'Perempuan',
                                                default => 'Individu keluarga',
                                            };
                                        @endphp

                                        <article
                                            class="tree-person {{ $isSelected ? 'is-selected' : '' }}"
                                            data-person-id="{{ $person->id }}"
                                            data-parent-ids="{{ json_encode($parentIds) }}"
                                            data-person-name="{{ mb_strtolower($person->full_name) }}"
                                            data-display-name="{{ $person->full_name }}"
                                            data-sex="{{ $sexClass }}"
                                            tabindex="0"
                                            role="button"
                                            aria-pressed="{{ $isSelected ? 'true' : 'false' }}"
                                            aria-label="Pilih {{ $person->full_name }}"
                                        >
                                            <div class="tree-avatar" data-sex="{{ $sexClass }}">
                                                {{ strtoupper(mb_substr($person->full_name, 0, 1)) }}
                                            </div>

                                            <div class="tree-person-info">
                                                <h3 class="tree-person-name" title="{{ $person->full_name }}">
                                                    {{ $person->full_name }}
                                                </h3>
                                                <p class="tree-person-meta">{{ $sexLabel }}</p>
                                            </div>
                                        </article>
                                    @endforeach
                                </section>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-xs"
                         style="color: var(--family-text-secondary);">
                        <p>
                            Tips: scroll atau cubit untuk zoom, seret area kosong untuk berpindah, tekan Esc untuk membatalkan pilihan.
                        </p>
                        <p>
                            {{ $people->count() }} individu dalam {{ $generationGroups->count() }} generasi
                        </p>
                    </div>

                @endif
            </main>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if ($people->isNotEmpty())
<script>
document.addEventListener('DOMContentLoaded', function () {
    const viewport = document.getElementById('tree-viewport');
    const stage = document.getElementById('tree-stage');
    const svg = document.getElementById('tree-connections');

    if (!viewport || !stage || !svg) return;

    const search = document.getElementById('tree-search');
    const searchCount = document.getElementById('tree-search-count');
    const zoomLabel = document.getElementById('tree-zoom-level');
    const panel = document.getElementById('tree-panel');
    const panelAvatar = document.getElementById('tree-panel-avatar');
    const panelName = document.getElementById('tree-panel-name');
    const panelMeta = document.getElementById('tree-panel-meta');
    const panelUp = document.getElementById('tree-panel-up');
    const panelDown = document.getElementById('tree-panel-down');
    const panelBody = document.getElementById('tree-panel-body');

    const MIN_SCALE = 0.3;
    const MAX_SCALE = 2;
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    let scale = 1;
    let translateX = 0;
    let translateY = 0;

    const state = { selected: null, query: '', lineage: null };

    /* ---------------------------------------------------------------
     * Data: bangun relasi orang tua - anak dari atribut kartu
     * ------------------------------------------------------------- */
    const cards = Array.from(stage.querySelectorAll('.tree-person'));
    const people = new Map();

    cards.forEach(card => {
        let rawParents = [];
        try {
            rawParents = JSON.parse(card.dataset.parentIds || '[]').map(String);
        } catch (error) {
            rawParents = [];
        }

        people.set(card.dataset.personId, {
            id: card.dataset.personId,
            el: card,
            name: card.dataset.displayName || '',
            nameLower: card.dataset.personName || '',
            sex: card.dataset.sex || 'unknown',
            rawParents,
            parents: [],
            children: [],
            roleEl: null,
        });
    });

    people.forEach(person => {
        person.rawParents.forEach(parentId => {
            const parent = people.get(parentId);
            if (!parent || parentId === person.id || person.parents.includes(parentId)) return;

            person.parents.push(parentId);
            parent.children.push(person.id);
        });
    });

    people.forEach(person => {
        const role = document.createElement('span');
        role.className = 'tree-role';
        role.hidden = true;
        person.el.appendChild(role);
        person.roleEl = role;

        const meta = person.el.querySelector('.tree-person-meta');
        if (meta && person.children.length) {
            meta.textContent = `${meta.textContent.trim()}, ${person.children.length} anak`;
        }
    });

    function sexLabel(sex) {
        if (sex === 'male') return 'Laki-laki';
        if (sex === 'female') return 'Perempuan';
        return 'Individu keluarga';
    }

    /* ---------------------------------------------------------------
     * Lineage: leluhur, keturunan, saudara, pasangan
     * ------------------------------------------------------------- */
    function walk(startId, key) {
        const found = new Map();
        let frontier = [startId];
        let depth = 0;

        while (frontier.length) {
            depth++;
            const next = [];

            frontier.forEach(id => {
                people.get(id)[key].forEach(relatedId => {
                    if (relatedId === startId || found.has(relatedId)) return;
                    found.set(relatedId, depth);
                    next.push(relatedId);
                });
            });

            frontier = next;
        }

        return found;
    }

    function computeLineage(id) {
        const person = people.get(id);
        const up = walk(id, 'parents');
        const down = walk(id, 'children');
        const siblings = new Set();
        const partners = new Set();

        person.parents.forEach(parentId => {
            people.get(parentId).children.forEach(childId => {
                if (childId !== id) siblings.add(childId);
            });
        });

        person.children.forEach(childId => {
            people.get(childId).parents.forEach(parentId => {
                if (parentId !== id) partners.add(parentId);
            });
        });

        const all = new Set([id, ...up.keys(), ...down.keys(), ...siblings, ...partners]);

        return { up, down, siblings, partners, all };
    }

    function upLabel(depth, sex) {
        if (depth === 1) return sex === 'male' ? 'Ayah' : sex === 'female' ? 'Ibu' : 'Orang tua';
        if (depth === 2) return sex === 'male' ? 'Kakek' : sex === 'female' ? 'Nenek' : 'Kakek/nenek';
        return 'Leluhur';
    }

    function downLabel(depth) {
        if (depth === 1) return 'Anak';
        if (depth === 2) return 'Cucu';
        return 'Keturunan';
    }

    /* ---------------------------------------------------------------
     * Render status kartu + garis
     * ------------------------------------------------------------- */
    let edges = [];

    function render() {
        const query = state.query;
        const lineage = !query && state.selected ? state.lineage : null;

        people.forEach(person => {
            const classes = ['tree-person'];
            let role = '';

            if (query) {
                classes.push(person.nameLower.includes(query) ? 'is-match' : 'is-dimmed');
            } else if (lineage) {
                if (person.id === state.selected) {
                    classes.push('is-selected');
                    role = 'Dipilih';
                } else if (lineage.up.has(person.id)) {
                    classes.push('is-up');
                    role = upLabel(lineage.up.get(person.id), person.sex);
                } else if (lineage.down.has(person.id)) {
                    classes.push('is-down');
                    role = downLabel(lineage.down.get(person.id));
                } else if (lineage.siblings.has(person.id)) {
                    classes.push('is-peer');
                    role = 'Saudara';
                } else if (lineage.partners.has(person.id)) {
                    classes.push('is-peer');
                    role = 'Pasangan';
                } else {
                    classes.push('is-dimmed');
                }
            }

            person.el.className = classes.join(' ');
            person.el.setAttribute('aria-pressed', person.id === state.selected ? 'true' : 'false');
            person.roleEl.textContent = role;
            person.roleEl.hidden = !role;
        });

        applyEdges(lineage);
    }

    function applyEdges(lineage) {
        svg.classList.toggle('has-focus', !!lineage);

        edges.forEach(edge => {
            let cls = 'tree-connection';

            if (lineage && lineage.all.has(edge.parent) && lineage.all.has(edge.child)) {
                cls += lineage.down.has(edge.child) ? ' is-down' : ' is-up';
                svg.appendChild(edge.path); // taruh di atas garis lain
            }

            edge.path.setAttribute('class', cls);
        });
    }

    /* ---------------------------------------------------------------
     * Garis siku dengan sudut membulat
     * ------------------------------------------------------------- */
    function position(el) {
        let x = 0;
        let y = 0;
        let node = el;

        while (node && node !== stage) {
            x += node.offsetLeft;
            y += node.offsetTop;
            node = node.offsetParent;
        }

        return { x, y, w: el.offsetWidth, h: el.offsetHeight };
    }

    function elbow(px, py, cx, cy) {
        if (cy <= py) return `M ${px} ${py} L ${cx} ${cy}`;
        if (Math.abs(cx - px) < 2) return `M ${px} ${py} V ${cy}`;

        let busY = cy - 38;
        if (busY - py < 20) busY = py + (cy - py) / 2;

        const dir = cx > px ? 1 : -1;
        const r = Math.min(14, Math.abs(cx - px) / 2, busY - py, cy - busY);

        return [
            `M ${px} ${py}`,
            `V ${busY - r}`,
            `Q ${px} ${busY} ${px + dir * r} ${busY}`,
            `H ${cx - dir * r}`,
            `Q ${cx} ${busY} ${cx} ${busY + r}`,
            `V ${cy}`,
        ].join(' ');
    }

    function drawConnections() {
        const width = stage.offsetWidth;
        const height = stage.offsetHeight;

        svg.setAttribute('width', width);
        svg.setAttribute('height', height);
        svg.setAttribute('viewBox', `0 0 ${width} ${height}`);
        svg.innerHTML = '';
        edges = [];

        people.forEach(child => {
            const c = position(child.el);

            child.parents.forEach(parentId => {
                const parent = people.get(parentId);
                const p = position(parent.el);

                const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                path.setAttribute('d', elbow(p.x + p.w / 2, p.y + p.h, c.x + c.w / 2, c.y));
                path.setAttribute('class', 'tree-connection');
                svg.appendChild(path);

                edges.push({ parent: parentId, child: child.id, path });
            });
        });

        const lineage = !state.query && state.selected ? state.lineage : null;
        applyEdges(lineage);
    }

    /* ---------------------------------------------------------------
     * Kamera: pan, zoom, fit, fokus
     * ------------------------------------------------------------- */
    function clampScale(value) {
        return Math.max(MIN_SCALE, Math.min(MAX_SCALE, value));
    }

    function applyTransform(animate = false) {
        const animated = animate && !reduceMotion;

        stage.classList.toggle('is-animating', animated);
        viewport.classList.toggle('is-animating', animated);

        stage.style.transform = `translate(${translateX}px, ${translateY}px) scale(${scale})`;
        zoomLabel.textContent = `${Math.round(scale * 100)}%`;

        const dot = Math.max(12, 24 * scale);
        viewport.style.backgroundSize = `${dot}px ${dot}px`;
        viewport.style.backgroundPosition = `${translateX}px ${translateY}px`;
    }

    function zoomAt(nextScale, centerX, centerY, animate = false) {
        nextScale = clampScale(nextScale);

        const pointX = centerX ?? viewport.clientWidth / 2;
        const pointY = centerY ?? viewport.clientHeight / 2;

        const worldX = (pointX - translateX) / scale;
        const worldY = (pointY - translateY) / scale;

        scale = nextScale;
        translateX = pointX - worldX * scale;
        translateY = pointY - worldY * scale;

        applyTransform(animate);
    }

    function fitTree(animate = false) {
        const vw = viewport.clientWidth;
        const vh = viewport.clientHeight;
        const sw = stage.offsetWidth;
        const sh = stage.offsetHeight;

        if (!sw || !sh) return;

        const padding = 30;

        scale = clampScale(Math.min((vw - padding * 2) / sw, (vh - padding * 2) / sh, 1));
        translateX = (vw - sw * scale) / 2;
        translateY = (vh - sh * scale) / 2;

        applyTransform(animate);
    }

    function focusArea() {
        const w = viewport.clientWidth;
        const h = viewport.clientHeight;

        if (!panel.hidden) {
            if (window.innerWidth >= 768) {
                return { x: (w - panel.offsetWidth - 14) / 2, y: h / 2 + 20 };
            }

            return { x: w / 2, y: (h - panel.offsetHeight - 10) / 2 + 24 };
        }

        return { x: w / 2, y: h / 2 + 20 };
    }

    function focusCard(card, animate = true) {
        if (!card) return;

        const p = position(card);
        const area = focusArea();

        translateX = area.x - (p.x + p.w / 2) * scale;
        translateY = area.y - (p.y + p.h / 2) * scale;

        applyTransform(animate);
    }

    /* ---------------------------------------------------------------
     * Panel detail
     * ------------------------------------------------------------- */
    function closePanel() {
        panel.hidden = true;
    }

    function addGroup(title, ids) {
        if (!ids.length) return;

        const group = document.createElement('div');
        group.className = 'tree-panel-group';

        const heading = document.createElement('h3');
        heading.textContent = `${title} (${ids.length})`;
        group.appendChild(heading);

        const chips = document.createElement('div');
        chips.className = 'tree-panel-chips';

        ids.forEach(id => {
            const related = people.get(id);
            const chip = document.createElement('button');

            chip.type = 'button';
            chip.className = 'tree-chip';
            chip.dataset.sex = related.sex;
            chip.textContent = related.name;
            chip.addEventListener('click', () => select(id));

            chips.appendChild(chip);
        });

        group.appendChild(chips);
        panelBody.appendChild(group);
    }

    function showPanel(person) {
        const lineage = state.lineage;

        panelAvatar.dataset.sex = person.sex;
        panelAvatar.textContent = person.name.charAt(0).toUpperCase();
        panelName.textContent = person.name;
        panelMeta.textContent = sexLabel(person.sex);
        panelUp.textContent = lineage.up.size;
        panelDown.textContent = lineage.down.size;

        panelBody.replaceChildren();

        addGroup('Orang tua', person.parents);
        addGroup('Pasangan', Array.from(lineage.partners));
        addGroup('Saudara', Array.from(lineage.siblings));
        addGroup('Anak', person.children);

        if (!panelBody.children.length) {
            const empty = document.createElement('p');
            empty.className = 'tree-panel-empty';
            empty.textContent = 'Belum ada hubungan keluarga yang tercatat untuk individu ini.';
            panelBody.appendChild(empty);
        }

        panel.hidden = false;
    }

    /* ---------------------------------------------------------------
     * Pilih / batal pilih
     * ------------------------------------------------------------- */
    function select(id, options = {}) {
        const { openPanel = true, focus = true } = options;
        const person = people.get(String(id));
        if (!person) return;

        state.selected = person.id;
        state.query = '';
        state.lineage = computeLineage(person.id);

        search.value = '';
        searchCount.hidden = true;

        render();

        if (openPanel) {
            showPanel(person);
        } else {
            closePanel();
        }

        if (focus) focusCard(person.el);
    }

    function clearSelection() {
        state.selected = null;
        state.lineage = null;
        closePanel();
        render();
    }

    function resetAll() {
        state.query = '';
        search.value = '';
        searchCount.hidden = true;
        clearSelection();
    }

    /* ---------------------------------------------------------------
     * Kontrol
     * ------------------------------------------------------------- */
    document.getElementById('tree-zoom-in')?.addEventListener('click', () => zoomAt(scale * 1.25, undefined, undefined, true));
    document.getElementById('tree-zoom-out')?.addEventListener('click', () => zoomAt(scale / 1.25, undefined, undefined, true));
    document.getElementById('tree-fit')?.addEventListener('click', () => fitTree(true));

    document.getElementById('tree-reset')?.addEventListener('click', () => {
        resetAll();
        fitTree(true);
    });

    document.getElementById('tree-grid-toggle')?.addEventListener('click', function () {
        const hidden = viewport.classList.toggle('no-grid');
        this.setAttribute('aria-pressed', hidden ? 'false' : 'true');
    });

    /* Kotak cari & toolbar zoom bisa digeser lewat pegangan ⋮⋮ di kirinya */
    const draggables = [];

    function makeDraggable(box, grip) {
        if (!box || !grip) return;

        const offset = { x: 0, y: 0 };
        let session = null;

        const place = () => {
            box.style.transform = `translate(${offset.x}px, ${offset.y}px)`;
        };

        const clamp = () => {
            place();

            const v = viewport.getBoundingClientRect();
            const r = box.getBoundingClientRect();
            const m = 4;

            if (r.left < v.left + m) offset.x += v.left + m - r.left;
            if (r.right > v.right - m) offset.x -= r.right - (v.right - m);
            if (r.top < v.top + m) offset.y += v.top + m - r.top;
            if (r.bottom > v.bottom - m) offset.y -= r.bottom - (v.bottom - m);

            place();
        };

        grip.addEventListener('pointerdown', event => {
            event.preventDefault();
            session = { sx: event.clientX, sy: event.clientY, bx: offset.x, by: offset.y };
            grip.setPointerCapture(event.pointerId);
        });

        grip.addEventListener('pointermove', event => {
            if (!session) return;
            offset.x = session.bx + event.clientX - session.sx;
            offset.y = session.by + event.clientY - session.sy;
            clamp();
        });

        const stop = () => { session = null; };
        grip.addEventListener('pointerup', stop);
        grip.addEventListener('pointercancel', stop);

        grip.addEventListener('dblclick', () => {
            offset.x = 0;
            offset.y = 0;
            place();
        });

        draggables.push(clamp);
    }

    makeDraggable(document.querySelector('.tree-zoom'), document.getElementById('tree-zoom-grip'));
    makeDraggable(document.querySelector('.tree-searchbox'), document.getElementById('tree-search-grip'));

    window.addEventListener('resize', () => draggables.forEach(fn => fn()));

    document.getElementById('tree-panel-close')?.addEventListener('click', clearSelection);

    panel.addEventListener('wheel', event => event.stopPropagation());

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && (state.selected || state.query)) {
            resetAll();
        }
    });

    cards.forEach(card => {
        card.addEventListener('click', () => {
            const id = card.dataset.personId;

            if (state.selected === id && !panel.hidden) {
                clearSelection();
                return;
            }

            select(id);
        });

        card.addEventListener('keydown', event => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                card.click();
            }
        });
    });

    search.addEventListener('input', function () {
        const query = this.value.trim().toLowerCase();

        state.query = query;
        state.selected = null;
        state.lineage = null;
        closePanel();
        render();

        if (!query) {
            searchCount.hidden = true;
            return;
        }

        const matches = Array.from(people.values()).filter(p => p.nameLower.includes(query));

        searchCount.hidden = false;
        searchCount.textContent = matches.length ? `${matches.length} cocok` : 'Tidak ada';

        if (matches.length) focusCard(matches[0].el);
    });

    search.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter' || !state.query) return;

        const first = Array.from(people.values()).find(p => p.nameLower.includes(state.query));

        if (first) {
            event.preventDefault();
            select(first.id);
        }
    });

    /* ---------------------------------------------------------------
     * Gesture: scroll zoom, drag, pinch
     * ------------------------------------------------------------- */
    viewport.addEventListener('wheel', function (event) {
        event.preventDefault();

        const delta = event.deltaMode === 1 ? event.deltaY * 33 : event.deltaY;
        const factor = Math.exp(-delta * (event.ctrlKey ? 0.01 : 0.0016));
        const rect = viewport.getBoundingClientRect();

        zoomAt(scale * factor, event.clientX - rect.left, event.clientY - rect.top);
    }, { passive: false });

    const pointers = new Map();
    let drag = null;
    let pinch = null;
    let suppressClick = false;

    viewport.addEventListener('pointerdown', function (event) {
        if (event.target.closest('.tree-ui')) return;
        if (event.pointerType === 'mouse' && event.button !== 0) return;

        pointers.set(event.pointerId, { x: event.clientX, y: event.clientY });

        if (pointers.size === 1) {
            drag = {
                startX: event.clientX,
                startY: event.clientY,
                initialX: translateX,
                initialY: translateY,
                moved: false,
            };
        } else if (pointers.size === 2) {
            const [a, b] = Array.from(pointers.values());
            pinch = { dist: Math.hypot(a.x - b.x, a.y - b.y) || 1, scale };
            drag = null;
            suppressClick = true;
        }
    });

    viewport.addEventListener('pointermove', function (event) {
        if (!pointers.has(event.pointerId)) return;

        pointers.set(event.pointerId, { x: event.clientX, y: event.clientY });

        if (pinch && pointers.size === 2) {
            const [a, b] = Array.from(pointers.values());
            const rect = viewport.getBoundingClientRect();
            const dist = Math.hypot(a.x - b.x, a.y - b.y);

            zoomAt(
                pinch.scale * (dist / pinch.dist),
                (a.x + b.x) / 2 - rect.left,
                (a.y + b.y) / 2 - rect.top
            );
            return;
        }

        if (!drag) return;

        const dx = event.clientX - drag.startX;
        const dy = event.clientY - drag.startY;

        if (!drag.moved) {
            if (Math.hypot(dx, dy) < 5) return;

            drag.moved = true;
            suppressClick = true;
            viewport.classList.add('is-dragging');

            try {
                viewport.setPointerCapture(event.pointerId);
            } catch (error) { /* abaikan */ }
        }

        translateX = drag.initialX + dx;
        translateY = drag.initialY + dy;
        applyTransform();
    });

    function endPointer(event) {
        pointers.delete(event.pointerId);

        if (pointers.size < 2) pinch = null;

        if (pointers.size === 0) {
            drag = null;
            viewport.classList.remove('is-dragging');
            setTimeout(() => { suppressClick = false; }, 0);
        } else {
            drag = null;
        }
    }

    viewport.addEventListener('pointerup', endPointer);
    viewport.addEventListener('pointercancel', endPointer);

    // Cegah "klik" setelah menggeser atau mencubit.
    viewport.addEventListener('click', function (event) {
        if (suppressClick) {
            event.stopPropagation();
            event.preventDefault();
            suppressClick = false;
        }
    }, true);

    // Klik area kosong membatalkan pilihan.
    viewport.addEventListener('click', function (event) {
        if (event.target.closest('.tree-person, .tree-ui')) return;
        if (state.selected) clearSelection();
    });

    /* ---------------------------------------------------------------
     * Inisialisasi
     * ------------------------------------------------------------- */
    /* Tinggi canvas mengikuti sisa layar, supaya kotak cari & toolbar
       (yang menempel di bagian bawah canvas) selalu terlihat tanpa scroll. */
    function sizeViewport() {
        const top = viewport.getBoundingClientRect().top + window.scrollY;
        const available = window.innerHeight - top - 24;
        const minHeight = window.innerWidth < 768 ? 440 : 480;

        viewport.style.height = `${Math.max(minHeight, Math.min(available, 900))}px`;
    }

    sizeViewport();
    window.addEventListener('resize', sizeViewport);

    requestAnimationFrame(() => {
        sizeViewport();
        drawConnections();
        fitTree(false);

        const initial = cards.find(card => card.classList.contains('is-selected'));

        if (initial) {
            select(initial.dataset.personId, { openPanel: false, focus: false });
        } else {
            render();
        }

        // Pohon besar: mulai dari ukuran yang masih terbaca.
        if (scale < 0.55) {
            scale = 0.8;
            focusCard((initial || cards[0]), false);
        }
    });

    window.addEventListener('resize', drawConnections);

    if ('ResizeObserver' in window) {
        new ResizeObserver(drawConnections).observe(stage);
    }
});
</script>
@endif
@endpush