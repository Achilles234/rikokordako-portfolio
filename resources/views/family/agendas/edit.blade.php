
@extends('layouts.family')

@section('title', 'Edit Agenda')

@section('content')
@php
    $access = app(\App\Services\Family\FamilyAccessService::class);
    $currentUser = auth('family')->user();

    $canCancel = $access->hasPermission(
        $currentUser,
        'agendas.cancel',
        $agenda->family_group_id
    );

    $canUpdate = $access->hasPermission(
        $currentUser,
        'agendas.update',
        $agenda->family_group_id
    );
@endphp


<div class="family-app-layout flex h-screen overflow-hidden">
    @include('family.partials.sidebar')

    <div class="flex min-h-0 min-w-0 flex-1 flex-col">
        @include('family.partials.topbar', [
            'navigationGroup' => $agenda->familyGroup,
        ])

        <main id="family-main" class="family-main-content min-h-0 min-w-0 flex-1 overflow-y-auto px-6 py-8 lg:px-10">
            <div class="family-agenda-page">

    <div class="agenda-heading">
        <div>
            <div class="agenda-eyebrow">FAMILY PLANNER</div>
            <h1>Edit Agenda</h1>
            <p>Perbarui informasi kegiatan keluarga.</p>
        </div>

        <a href="{{ route('family.agendas.index', ['group' => $agenda->family_group_id]) }}"
           class="agenda-reset-button">
            ← Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="agenda-alert">
            <strong>Periksa kembali data berikut:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="agenda-edit-panel">

        <div class="agenda-edit-information">
            <div>
                <span>Grup Keluarga</span>
                <strong>{{ $agenda->familyGroup->name }}</strong>
            </div>

            <div>
                <span>Status Agenda</span>
                <strong>{{ $agenda->status === 'cancelled' ? 'Dibatalkan' : ($agenda->status === 'completed' ? 'Selesai' : 'Terjadwal') }}</strong>
            </div>

            <div>
                <span>Jumlah Peserta</span>
                <strong>{{ $agenda->participants->count() }} anggota</strong>
            </div>
        </div>

        @if ($agenda->status === 'cancelled')
            <div class="agenda-alert">
                Agenda ini sudah dibatalkan dan hanya dapat dilihat sebagai riwayat.
                @if ($agenda->cancellation_reason)
                    <p><strong>Alasan:</strong> {{ $agenda->cancellation_reason }}</p>
                @endif
            </div>
        @elseif ($agenda->status === 'completed')
            <div class="agenda-alert">
                Agenda ini sudah selesai.
            </div>
        @elseif ($canUpdate)
            <form method="POST"
                  action="{{ route('family.agendas.update', $agenda->id) }}">
                @csrf
                @method('PUT')

                <div class="agenda-edit-form">

                    <div class="agenda-edit-field full-width">
                        <label for="title">Nama Agenda *</label>
                        <input
                            type="text"
                            name="title"
                            id="title"
                            maxlength="180"
                            required
                            value="{{ old('title', $agenda->title) }}"
                        >
                    </div>

                    <div class="agenda-edit-field">
                        <label for="category">Kategori</label>
                        <input
                            type="text"
                            name="category"
                            id="category"
                            maxlength="80"
                            value="{{ old('category', $agenda->category) }}"
                        >
                    </div>

                    <div class="agenda-edit-field">
                        <label for="location">Lokasi</label>
                        <input
                            type="text"
                            name="location"
                            id="location"
                            maxlength="255"
                            value="{{ old('location', $agenda->location) }}"
                        >
                    </div>

                    <div class="agenda-edit-field">
                        <label for="starts_at">Mulai *</label>
                        <input
                            type="datetime-local"
                            name="starts_at"
                            id="starts_at"
                            required
                            value="{{ old('starts_at', $agenda->starts_at?->format('Y-m-d\TH:i')) }}"
                        >
                    </div>

                    <div class="agenda-edit-field">
                        <label for="ends_at">Selesai</label>
                        <input
                            type="datetime-local"
                            name="ends_at"
                            id="ends_at"
                            value="{{ old('ends_at', $agenda->ends_at?->format('Y-m-d\TH:i')) }}"
                        >
                    </div>

                    <div class="agenda-edit-field full-width">
                        <label for="description">Deskripsi</label>
                        <textarea
                            name="description"
                            id="description"
                            rows="5"
                        >{{ old('description', $agenda->description) }}</textarea>
                    </div>

                    <div class="agenda-edit-field full-width">
                        <label class="agenda-checkbox-option">
                            <input
                                type="checkbox"
                                name="is_all_day"
                                value="1"
                                @checked(old('is_all_day', $agenda->is_all_day))
                            >
                            <span>
                                <strong>Agenda sepanjang hari</strong>
                                <small>Kegiatan tidak memiliki jam khusus.</small>
                            </span>
                        </label>
                    </div>

                </div>

                <div class="agenda-edit-footer">
                    <button type="submit" class="family-leaf-button">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        @endif

        <div class="agenda-participant-section">
            <h2>Peserta Agenda</h2>
            <p>Daftar peserta yang ditetapkan ketika agenda dibuat.</p>

            <div class="agenda-participant-list">
                @forelse ($agenda->participants as $participant)
                    @php
                        $person = $participant->familyMember?->person;
                        $participantName = $person?->nickname
                            ?: $person?->full_name
                            ?: 'Anggota keluarga';
                    @endphp

                    <div class="agenda-participant-item">
                        <div>
                            <strong>{{ $participantName }}</strong>
                            <small>
                                {{ $participant->attendance_status === 'attending'
                                    ? 'Hadir'
                                    : ($participant->attendance_status === 'declined'
                                        ? 'Tidak hadir'
                                        : ($participant->attendance_status === 'maybe'
                                            ? 'Belum pasti'
                                            : 'Belum merespons')) }}
                            </small>
                        </div>
                    </div>
                @empty
                    <div class="agenda-participant-empty">
                        Belum ada peserta yang tercatat.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="agenda-reminder-section">
            <h2>Pengingat</h2>
            <div class="agenda-reminder-list">
                @forelse ($agenda->reminders as $reminder)
                    <span class="agenda-reminder-tag">
                        @if ($reminder->minutes_before === 15)
                            15 menit sebelumnya
                        @elseif ($reminder->minutes_before === 60)
                            1 jam sebelumnya
                        @elseif ($reminder->minutes_before === 1440)
                            1 hari sebelumnya
                        @else
                            {{ $reminder->minutes_before }} menit sebelumnya
                        @endif

                        {{ $reminder->is_active ? '' : '(Nonaktif)' }}
                    </span>
                @empty
                    <span class="agenda-participant-empty">
                        Tidak ada pengingat yang dikonfigurasi.
                    </span>
                @endforelse
            </div>
        </div>

        @if ($canCancel && $agenda->status === 'scheduled')
            <div class="agenda-cancel-section">
                <div>
                    <h2>Batalkan Agenda</h2>
                    <p>
                        Agenda tidak akan dihapus. Riwayat dan data pesertanya tetap tersimpan.
                    </p>
                </div>

                <form
                    method="POST"
                    action="{{ route('family.agendas.cancel', $agenda->id) }}"
                    onsubmit="return confirm('Yakin ingin membatalkan agenda ini?')"
                >
                    @csrf

                    <label for="cancellation_reason">
                        Alasan Pembatalan
                    </label>

                    <textarea
                        name="cancellation_reason"
                        id="cancellation_reason"
                        rows="3"
                        maxlength="5000"
                        placeholder="Tuliskan alasan pembatalan..."
                    ></textarea>

                   <button type="submit" class="family-leaf-button family-leaf-button--danger">
                        Batalkan Agenda
                    </button>
                </form>
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
.family-agenda-page {
    display: flex;
    flex-direction: column;
    gap: 22px;
}

.agenda-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.agenda-eyebrow {
    color: #b38a52;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 2px;
    margin-bottom: 8px;
}

.agenda-heading h1 {
    font-size: clamp(25px, 3vw, 32px);
    margin: 0 0 7px;
    font-weight: 750;
}

.agenda-heading p {
    margin: 0;
    font-size: 13px;
    opacity: .7;
}

.agenda-edit-panel {
    border: 1px solid var(--family-border, rgba(150, 120, 80, .2));
    background: var(--family-card-bg, rgba(120, 90, 55, .07));
    border-radius: 15px;
    overflow: hidden;
}

.agenda-edit-information {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 15px;
    padding: 22px;
    border-bottom: 1px solid var(--family-border, rgba(150, 120, 80, .2));
}

.agenda-edit-information div {
    display: flex;
    flex-direction: column;
    gap: 7px;
}

.agenda-edit-information span {
    font-size: 11px;
    opacity: .65;
}

.agenda-edit-information strong {
    font-size: 13px;
}

.agenda-edit-form {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 17px;
    padding: 24px;
}

.agenda-edit-field {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.agenda-edit-field.full-width {
    grid-column: 1 / -1;
}

.agenda-edit-field label {
    font-size: 12px;
    font-weight: 700;
}

.agenda-edit-field input:not([type="checkbox"]),
.agenda-edit-field textarea {
    width: 100%;
    padding: 11px 13px;
    border: 1px solid var(--family-border, rgba(150, 120, 80, .3));
    border-radius: 9px;
    background: var(--family-input-bg, rgba(120, 90, 55, .06));
    color: inherit;
    font: inherit;
    font-size: 13px;
}

.agenda-edit-field textarea {
    resize: vertical;
}

.agenda-checkbox-option {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    padding: 13px;
    border: 1px solid var(--family-border, rgba(150, 120, 80, .2));
    border-radius: 9px;
    cursor: pointer;
}

.agenda-checkbox-option input {
    accent-color: #b38a52;
}

.agenda-checkbox-option span {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.agenda-checkbox-option strong {
    font-size: 12px;
}

.agenda-checkbox-option small {
    font-size: 11px;
    opacity: .65;
}

.agenda-edit-footer {
    display: flex;
    justify-content: flex-end;
    padding: 0 24px 24px;
}

.agenda-primary-button,
.agenda-danger-button {
    padding: 11px 18px;
    border: 0;
    border-radius: 9px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
}

.agenda-primary-button {
    background: linear-gradient(135deg, #b68a50, #8c6336);
    color: #fff;
}

.agenda-danger-button {
    background: #9d4545;
    color: #fff;
}

.agenda-reset-button {
    display: inline-flex;
    padding: 10px 15px;
    border: 1px solid var(--family-border, rgba(150, 120, 80, .3));
    border-radius: 9px;
    color: inherit !important;
    text-decoration: none !important;
    font-size: 12px;
}

.agenda-participant-section,
.agenda-reminder-section,
.agenda-cancel-section {
    padding: 22px 24px;
    border-top: 1px solid var(--family-border, rgba(150, 120, 80, .2));
}

.agenda-participant-section h2,
.agenda-reminder-section h2,
.agenda-cancel-section h2 {
    font-size: 15px;
    margin: 0 0 6px;
}

.agenda-participant-section > p,
.agenda-cancel-section p {
    font-size: 12px;
    opacity: .65;
    margin: 0 0 15px;
}

.agenda-participant-list {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 9px;
    margin-top: 15px;
}

.agenda-participant-item {
    padding: 12px;
    border: 1px solid var(--family-border, rgba(150, 120, 80, .2));
    border-radius: 9px;
}

.agenda-participant-item div {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.agenda-participant-item strong {
    font-size: 12px;
}

.agenda-participant-item small,
.agenda-participant-empty {
    font-size: 11px;
    opacity: .65;
}

.agenda-reminder-list {
    display: flex;
    flex-wrap: wrap;
    gap: 9px;
    margin-top: 12px;
}

.agenda-reminder-tag {
    padding: 8px 11px;
    border-radius: 8px;
    background: rgba(179, 138, 82, .12);
    color: #bd955e;
    font-size: 11px;
    font-weight: 650;
}

.agenda-cancel-section {
    background: rgba(160, 65, 65, .035);
}

.agenda-cancel-section form {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
}

.agenda-cancel-section label {
    font-size: 12px;
    font-weight: 650;
}

.agenda-cancel-section textarea {
    width: 100%;
    padding: 11px;
    border-radius: 9px;
    border: 1px solid var(--family-border, rgba(150, 120, 80, .3));
    background: var(--family-input-bg, rgba(120, 90, 55, .06));
    color: inherit;
    font: inherit;
    font-size: 12px;
}

.agenda-alert {
    padding: 15px;
    border: 1px solid rgba(179, 138, 82, .3);
    background: rgba(179, 138, 82, .08);
    border-radius: 9px;
    font-size: 12px;
}

.agenda-alert ul {
    margin: 8px 0 0;
    padding-left: 20px;
}

@media (max-width: 650px) {
    .agenda-edit-information {
        grid-template-columns: 1fr;
    }

    .agenda-edit-form,
    .agenda-participant-list {
        grid-template-columns: 1fr;
    }

    .agenda-edit-form {
        padding: 18px;
    }

    .agenda-edit-footer {
        padding: 0 18px 18px;
    }
}

</style>
@endpush
