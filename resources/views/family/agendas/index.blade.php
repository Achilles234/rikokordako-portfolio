
@extends('layouts.family')

@section('title', 'Agenda Keluarga')

@section('content')
@php
    $today = now()->startOfDay();

    $upcomingCount = $agendas->getCollection()
        ->filter(fn ($agenda) =>
            $agenda->starts_at && $agenda->starts_at->gte($today)
            && $agenda->status === 'scheduled'
        )->count();

    $cancelledCount = $agendas->getCollection()
        ->where('status', 'cancelled')
        ->count();

    $statusLabels = [
        'scheduled' => 'Terjadwal',
        'cancelled' => 'Dibatalkan',
        'completed' => 'Selesai',
    ];
@endphp

<div class="family-app-layout flex h-screen overflow-hidden">
    @include('family.partials.sidebar')

    <div class="flex min-h-0 min-w-0 flex-1 flex-col">
        @include('family.partials.topbar')

        <main id="family-main"
            class="family-main-content min-h-0 min-w-0 flex-1 overflow-y-auto px-6 py-8 lg:px-10">

            <div class="family-agenda-page">

    <div class="agenda-heading">
        <div>
            <div class="agenda-eyebrow">FAMILY PLANNER</div>
            <h1>Agenda Keluarga</h1>
            <p>
                Satu tempat untuk melihat dan mengatur kegiatan seluruh keluarga.
            </p>
        </div>

       <a href="{{ route('family.agendas.create') }}"
        class="family-leaf-button inline-flex items-center justify-center gap-2">
            <span>＋</span>
            Buat Agenda
        </a>
    </div>

    <div class="agenda-summary">
        <div class="agenda-summary-card">
            <span class="summary-label">Total Agenda</span>
            <strong>{{ $agendas->total() }}</strong>
            <small>Agenda sesuai filter</small>
        </div>

        <div class="agenda-summary-card">
            <span class="summary-label">Agenda Ditampilkan</span>
            <strong>{{ $agendas->count() }}</strong>
            <small>Halaman ini</small>
        </div>

        <div class="agenda-summary-card">
            <span class="summary-label">Akan Datang</span>
            <strong>{{ $upcomingCount }}</strong>
            <small>Di halaman ini</small>
        </div>

        <div class="agenda-summary-card">
            <span class="summary-label">Dibatalkan</span>
            <strong>{{ $cancelledCount }}</strong>
            <small>Di halaman ini</small>
        </div>
    </div>

    <div class="agenda-filter-panel">
        <div class="agenda-section-heading">
            <div>
                <h2>Filter Agenda</h2>
                <p>Temukan kegiatan berdasarkan grup dan waktu.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('family.agendas.index') }}"
              class="agenda-filter-form">

            <div class="agenda-filter-field">
                <label for="agenda-group">Grup Keluarga</label>
                <select name="group" id="agenda-group">
                    <option value="">Semua Grup</option>
                    @foreach ($groups as $group)
                        <option value="{{ $group->id }}"
                            @selected((string) ($filters['group'] ?? '') === (string) $group->id)>
                            {{ $group->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="agenda-filter-field">
                <label for="agenda-category">Kategori</label>
                <select name="category" id="agenda-category">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}"
                            @selected(($filters['category'] ?? '') === $category)>
                            {{ $category }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="agenda-filter-field">
                <label for="agenda-date-from">Dari Tanggal</label>
                <input type="date" name="date_from" id="agenda-date-from"
                       value="{{ $filters['date_from'] ?? '' }}">
            </div>

            <div class="agenda-filter-field">
                <label for="agenda-date-to">Sampai Tanggal</label>
                <input type="date" name="date_to" id="agenda-date-to"
                       value="{{ $filters['date_to'] ?? '' }}">
            </div>

            <div class="agenda-filter-field">
                <label for="agenda-status">Status</label>
                <select name="status" id="agenda-status">
                    <option value="">Semua Status</option>
                    @foreach ($statusLabels as $value => $label)
                        <option value="{{ $value }}"
                            @selected(($filters['status'] ?? '') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

        <div class="agenda-filter-actions">
                <button type="submit" class="family-leaf-button">
                    Terapkan Filter
                </button>

                <a href="{{ route('family.agendas.index') }}"
                class="agenda-reset-button">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="agenda-list-panel">
        <div class="agenda-section-heading">
            <div>
                <h2>Semua Agenda</h2>
                <p>
                    Menampilkan agenda dari grup keluarga yang dapat kamu akses.
                </p>
            </div>

            <span class="agenda-result-count">
                {{ $agendas->total() }} agenda
            </span>
        </div>

        @forelse ($agendas as $agenda)
            <article class="agenda-item">

                <div class="agenda-date">
                    <strong>{{ $agenda->starts_at->format('d') }}</strong>
                    <span>{{ $agenda->starts_at->translatedFormat('M Y') }}</span>
                    @if ($agenda->is_all_day)
                        <small>Seharian</small>
                    @else
                        <small>{{ $agenda->starts_at->format('H:i') }}</small>
                    @endif
                </div>

                <div class="agenda-item-content">
                    <div class="agenda-item-topline">
                        <span class="agenda-category">
                            {{ $agenda->category ?: 'Umum' }}
                        </span>

                        <span class="agenda-status status-{{ $agenda->status }}">
                            {{ $statusLabels[$agenda->status] ?? ucfirst($agenda->status) }}
                        </span>
                    </div>

                    <h3>{{ $agenda->title }}</h3>

                    <div class="agenda-meta">
                        <span>⌂ {{ $agenda->familyGroup->name }}</span>

                        @if ($agenda->location)
                            <span>⌖ {{ $agenda->location }}</span>
                        @endif

                        @if ($agenda->ends_at && ! $agenda->is_all_day)
                            <span>
                                Sampai {{ $agenda->ends_at->format('H:i') }}
                            </span>
                        @endif

                        <span>
                            {{ $agenda->participants_count }} peserta
                        </span>
                    </div>

                    @if ($agenda->description)
                        <p class="agenda-description">
                            {{ \Illuminate\Support\Str::limit($agenda->description, 180) }}
                        </p>
                    @endif

                    @if ($agenda->status === 'cancelled' && $agenda->cancellation_reason)
                        <p class="agenda-cancellation">
                            Alasan pembatalan: {{ $agenda->cancellation_reason }}
                        </p>
                    @endif
                </div>

                <div class="agenda-item-actions">

                    <a href="{{ route('family.agendas.show', $agenda->id) }}"
                    class="agenda-action-link">
                        Lihat Detail →
                    </a>

                    @if ($agenda->status !== 'cancelled')
                        @if (auth('family')->check() &&
                            app(\App\Services\Family\FamilyAccessService::class)
                                ->hasPermission(auth('family')->user(), 'agendas.update', $agenda->family_group_id))
                            <a href="{{ route('family.agendas.edit', $agenda->id) }}"
                            class="agenda-action-link">
                                Edit
                            </a>
                        @endif
                    @endif

                </div>

            </article>
        @empty
            <div class="agenda-empty">
                <div class="agenda-empty-icon">◷</div>
                <h3>Belum ada agenda</h3>
                <p>
                    Belum ada agenda yang cocok dengan filter saat ini.
                    Coba ubah filter atau buat agenda keluarga baru.
                </p>

                <a href="{{ route('family.agendas.create') }}"
                    class="family-leaf-button inline-flex items-center justify-center">
                        Buat Agenda Pertama
                    </a>
            </div>
        @endforelse

        @if ($agendas->hasPages())
            <div class="agenda-pagination">
                {{ $agendas->links() }}
            </div>
        @endif
    </div>

            </div>
        </main>
    </div>
</div>
@endsection

@push('styles')
<style>
/* ========== Variabel tema (default = DARK, tidak berubah) ========== */
/* ========== Variabel tema (default = DARK) ========== */
.family-agenda-page {
    --ag-card-bg: rgba(120, 90, 55, .07);
    --ag-border: var(--family-border);
    --ag-border-strong: rgba(211, 174, 128, .30);
    --ag-accent: #bd955e;
    --ag-accent-bg: rgba(179, 138, 82, .12);
    --ag-accent-border: rgba(179, 138, 82, .25);
    --ag-eyebrow: #b38a52;
    --ag-input-bg: rgba(120, 90, 55, .06);
    --ag-muted: .7;
    --ag-muted-sm: .6;
    --ag-shadow: none;
    --ag-scheme: dark;
    --ag-option-bg: #211b16;
    --ag-option-text: #f5e9d5;

    --ag-ok: #78ad7a;
    --ag-ok-bg: rgba(100, 150, 100, .12);
    --ag-cancel: #d77d7d;
    --ag-cancel-bg: rgba(190, 75, 75, .12);
    --ag-done: #8caed5;
    --ag-done-bg: rgba(100, 130, 170, .14);

    display: flex;
    flex-direction: column;
    gap: 24px;
    color: var(--family-text-primary);
}

/* ========== Override MODE TERANG (Natural Oak) ========== */
html.family-theme-light .family-agenda-page {
    --ag-card-bg: var(--family-surface);          /* #FBF6ED, solid */
    --ag-border: rgba(100, 66, 36, .22);
    --ag-border-strong: rgba(100, 66, 36, .38);
    --ag-accent: #8a5a1f;
    --ag-accent-bg: rgba(138, 90, 31, .10);
    --ag-accent-border: rgba(138, 90, 31, .30);
    --ag-eyebrow: #7a4e18;
    --ag-input-bg: #ffffff;
    --ag-muted: .85;
    --ag-muted-sm: .78;
    --ag-shadow: var(--family-panel-shadow);
    --ag-scheme: light;
    --ag-option-bg: #ffffff;
    --ag-option-text: #382719;

    --ag-ok: #2f6b3a;
    --ag-ok-bg: rgba(47, 107, 58, .12);
    --ag-cancel: #b03a3a;
    --ag-cancel-bg: rgba(176, 58, 58, .10);
    --ag-done: #2f5f94;
    --ag-done-bg: rgba(47, 95, 148, .10);
}

/* ========== Heading ========== */
.agenda-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.agenda-eyebrow {
    color: var(--ag-eyebrow);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 2px;
    margin-bottom: 8px;
}

.agenda-heading h1 {
    font-size: clamp(25px, 3vw, 32px);
    font-weight: 750;
    margin: 0 0 7px;
}

.agenda-heading p,
.agenda-section-heading p {
    margin: 0;
    opacity: var(--ag-muted);
    font-size: 13px;
}

/* ========== Panel & kartu ========== */
.agenda-summary {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
}

.agenda-summary-card,
.agenda-filter-panel,
.agenda-list-panel {
    background: var(--ag-card-bg);
    border: 1px solid var(--ag-border);
    border-radius: 15px;
    box-shadow: var(--ag-shadow);
}

.agenda-summary-card {
    padding: 19px;
    display: flex;
    flex-direction: column;
    gap: 7px;
}

.summary-label {
    font-size: 12px;
    font-weight: 600;
    opacity: var(--ag-muted);
}

.agenda-summary-card strong {
    font-size: 27px;
    font-weight: 750;
    color: var(--ag-accent);
}

.agenda-summary-card small {
    font-size: 11px;
    opacity: var(--ag-muted-sm);
}

.agenda-filter-panel,
.agenda-list-panel {
    padding: 22px;
}

.agenda-section-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 19px;
}

.agenda-section-heading h2 {
    font-size: 17px;
    font-weight: 750;
    margin: 0 0 6px;
}

/* ========== Filter ========== */
.agenda-filter-form {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 14px;
    align-items: end;
}

.agenda-filter-field {
    display: flex;
    flex-direction: column;
    gap: 7px;
    min-width: 0;
}

.agenda-filter-field label {
    font-size: 12px;
    font-weight: 650;
    opacity: .9;
}

.agenda-filter-field input,
.agenda-filter-field select {
    width: 100%;
    min-height: 42px;
    border: 1px solid var(--ag-border-strong);
    border-radius: 9px;
    background: var(--ag-input-bg);
    color: inherit;
    padding: 9px 11px;
    font: inherit;
    font-size: 12px;
    color-scheme: var(--ag-scheme);
}

.agenda-filter-field input:focus,
.agenda-filter-field select:focus {
    outline: 2px solid var(--ag-accent-border);
    outline-offset: 1px;
    border-color: var(--ag-accent);
}

.agenda-filter-field option {
    background: var(--ag-option-bg);
    color: var(--ag-option-text);
}

.agenda-filter-actions {
    grid-column: 1 / -1;
    display: flex;
    gap: 10px;
    align-items: center;
}

.agenda-reset-button {
    padding: 10px 15px;
    border-radius: 9px;
    border: 1px solid var(--ag-border-strong);
    background: transparent;
    text-decoration: none !important;
    color: inherit !important;
    font-size: 12px;
    font-weight: 600;
    transition: background .15s;
}

.agenda-reset-button:hover {
    background: var(--ag-accent-bg);
}

.agenda-result-count {
    font-size: 12px;
    font-weight: 700;
    padding: 7px 11px;
    border-radius: 20px;
    background: var(--ag-accent-bg);
    border: 1px solid var(--ag-accent-border);
    color: var(--ag-accent);
    white-space: nowrap;
}

/* ========== Item agenda ========== */
.agenda-item {
    display: grid;
    grid-template-columns: 76px minmax(0, 1fr) auto;
    gap: 18px;
    padding: 19px 0;
    border-top: 1px solid var(--ag-border);
}

.agenda-date {
    width: 68px;
    height: 78px;
    border-radius: 12px;
    background: var(--ag-accent-bg);
    border: 1px solid var(--ag-accent-border);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.agenda-date strong {
    color: var(--ag-accent);
    font-size: 26px;
    line-height: 1.1;
}

.agenda-date span {
    font-size: 11px;
    font-weight: 700;
    margin-top: 4px;
}

.agenda-date small {
    opacity: var(--ag-muted);
    font-size: 10px;
    margin-top: 3px;
}

.agenda-item-content {
    min-width: 0;
}

.agenda-item-topline {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 7px;
}

.agenda-category,
.agenda-status {
    display: inline-flex;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 700;
}

.agenda-category {
    background: var(--ag-accent-bg);
    color: var(--ag-accent);
}

.agenda-status {
    background: var(--ag-ok-bg);
    color: var(--ag-ok);
}

.status-cancelled {
    background: var(--ag-cancel-bg);
    color: var(--ag-cancel);
}

.status-completed {
    background: var(--ag-done-bg);
    color: var(--ag-done);
}

.agenda-item-content h3 {
    font-size: 16px;
    font-weight: 750;
    margin: 0 0 9px;
}

.agenda-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 8px 16px;
    font-size: 11px;
    opacity: var(--ag-muted);
}

.agenda-description {
    margin: 10px 0 0;
    font-size: 12px;
    line-height: 1.6;
    opacity: var(--ag-muted);
}

.agenda-cancellation {
    margin: 10px 0 0;
    font-size: 12px;
    color: var(--ag-cancel);
}

.agenda-item-actions {
    display: flex;
    align-items: center;
    gap: 14px;
}

.agenda-action-link {
    color: var(--ag-accent) !important;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none !important;
}

.agenda-action-link:hover {
    text-decoration: underline !important;
}

/* ========== Empty & pagination ========== */
.agenda-empty {
    text-align: center;
    padding: 55px 20px;
    border-top: 1px solid var(--ag-border);
}

.agenda-empty-icon {
    color: var(--ag-accent);
    font-size: 42px;
    margin-bottom: 10px;
}

.agenda-empty h3 {
    font-size: 17px;
    margin: 0 0 8px;
}

.agenda-empty p {
    max-width: 420px;
    margin: 0 auto 20px;
    font-size: 12px;
    line-height: 1.7;
    opacity: var(--ag-muted);
}

.agenda-pagination {
    padding-top: 20px;
    border-top: 1px solid var(--ag-border);
}

/* ========== Responsive ========== */
@media (max-width: 1000px) {
    .agenda-filter-form {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .agenda-summary {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 600px) {
    .agenda-heading {
        align-items: flex-start;
    }

    .agenda-heading .family-leaf-button {
        width: 100%;
        min-width: 0;
    }

    .agenda-filter-form {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .agenda-filter-actions {
        flex-wrap: wrap;
    }

    .agenda-filter-actions .family-leaf-button {
        flex: 1;
        min-width: 0;
    }

    .agenda-item {
        grid-template-columns: 58px minmax(0, 1fr);
        gap: 12px;
    }

    .agenda-date {
        width: 56px;
        height: 70px;
    }

    .agenda-date strong {
        font-size: 22px;
    }

    .agenda-item-actions {
        grid-column: 2;
    }

    .agenda-filter-panel,
    .agenda-list-panel {
        padding: 16px;
    }
}

@media (max-width: 380px) {
    .agenda-filter-form {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush