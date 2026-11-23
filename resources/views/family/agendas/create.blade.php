
@extends('layouts.family')

@section('title', 'Buat Agenda Keluarga')

@section('content')
<div class="family-app-layout flex h-screen overflow-hidden">
    @include('family.partials.sidebar')

    <div class="flex min-h-0 min-w-0 flex-1 flex-col">
        @include('family.partials.topbar', [
            'navigationGroup' => $groups->firstWhere('id', $selectedGroupId),
        ])

        <main id="family-main" class="family-main-content min-h-0 min-w-0 flex-1 overflow-y-auto px-6 py-8 lg:px-10">
<div class="family-agenda-page">

    <div class="agenda-heading">
        <div>
            <div class="agenda-eyebrow">FAMILY PLANNER</div>
            <h1>Buat Agenda</h1>
            <p>Rencanakan kegiatan bersama anggota keluarga.</p>
        </div>

        <a href="{{ route('family.agendas.index') }}"
           class="agenda-reset-button">
            ← Kembali ke Agenda
        </a>
    </div>

    @if ($errors->any())
        <div class="agenda-alert agenda-alert-danger">
            <strong>Periksa kembali data berikut:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="agenda-form-panel">

        <form method="POST" action="{{ route('family.agendas.store') }}">
            @csrf

            <section class="agenda-form-section">
                <div class="agenda-form-section-heading">
                    <span class="agenda-section-number">01</span>
                    <div>
                        <h2>Informasi Agenda</h2>
                        <p>Informasi dasar kegiatan keluarga.</p>
                    </div>
                </div>

                <div class="agenda-form-grid">

                    <div class="agenda-form-field full-width">
                        <label for="family_group_id">
                            Grup Keluarga <span class="required">*</span>
                        </label>

                        <select
                            name="family_group_id"
                            id="family_group_id"
                            required
                            data-create-url="{{ route('family.agendas.create') }}"
                        >
                            <option value="">Pilih grup keluarga</option>

                            @foreach ($groups as $group)
                                <option
                                    value="{{ $group->id }}"
                                    @selected((string) old('family_group_id', $selectedGroupId) === (string) $group->id)
                                >
                                    {{ $group->name }}
                                </option>
                            @endforeach
                        </select>

                        <small>
                            Peserta agenda akan diambil dari anggota aktif grup ini.
                        </small>
                    </div>

                    <div class="agenda-form-field full-width">
                        <label for="title">
                            Nama Agenda <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            name="title"
                            id="title"
                            maxlength="180"
                            required
                            placeholder="Contoh: Silaturahmi Keluarga Besar"
                            value="{{ old('title') }}"
                        >
                    </div>

                    <div class="agenda-form-field">
                        <label for="category">Kategori</label>

                        <input
                            type="text"
                            name="category"
                            id="category"
                            maxlength="80"
                            list="agenda-category-options"
                            placeholder="Contoh: Keluarga, Ulang Tahun"
                            value="{{ old('category') }}"
                        >

                        <datalist id="agenda-category-options">
                            <option value="Keluarga">
                            <option value="Ulang Tahun">
                            <option value="Pernikahan">
                            <option value="Pertemuan">
                            <option value="Liburan">
                            <option value="Keagamaan">
                            <option value="Lainnya">
                        </datalist>
                    </div>

                    <div class="agenda-form-field">
                        <label for="location">Lokasi</label>

                        <input
                            type="text"
                            name="location"
                            id="location"
                            maxlength="255"
                            placeholder="Lokasi kegiatan"
                            value="{{ old('location') }}"
                        >
                    </div>

                    <div class="agenda-form-field full-width">
                        <label for="description">Deskripsi</label>

                        <textarea
                            name="description"
                            id="description"
                            rows="4"
                            placeholder="Tambahkan informasi kegiatan..."
                        >{{ old('description') }}</textarea>
                    </div>

                </div>
            </section>

            <section class="agenda-form-section">
                <div class="agenda-form-section-heading">
                    <span class="agenda-section-number">02</span>
                    <div>
                        <h2>Waktu Kegiatan</h2>
                        <p>Tentukan jadwal pelaksanaan agenda.</p>
                    </div>
                </div>

                <div class="agenda-form-grid">

                    <div class="agenda-form-field">
                        <label for="starts_at">
                            Mulai <span class="required">*</span>
                        </label>

                        <input
                            type="datetime-local"
                            name="starts_at"
                            id="starts_at"
                            required
                            value="{{ old('starts_at') }}"
                        >
                    </div>

                    <div class="agenda-form-field">
                        <label for="ends_at">Selesai</label>

                        <input
                            type="datetime-local"
                            name="ends_at"
                            id="ends_at"
                            value="{{ old('ends_at') }}"
                        >
                    </div>

                    <div class="agenda-form-field full-width">
                        <label class="agenda-checkbox-option">
                            <input
                                type="checkbox"
                                name="is_all_day"
                                value="1"
                                @checked(old('is_all_day'))
                            >
                            <span>
                                <strong>Agenda sepanjang hari</strong>
                                <small>Agenda tidak memiliki jam khusus.</small>
                            </span>
                        </label>
                    </div>

                </div>
            </section>

            <section class="agenda-form-section">
                <div class="agenda-form-section-heading">
                    <span class="agenda-section-number">03</span>
                    <div>
                        <h2>Peserta Agenda</h2>
                        <p>Tentukan siapa saja yang diundang.</p>
                    </div>
                </div>

                <div class="agenda-audience-options">

                    <label class="agenda-radio-option">
                        <input
                            type="radio"
                            name="audience_type"
                            value="all_members"
                            @checked(old('audience_type', 'all_members') === 'all_members')
                        >
                        <span>
                            <strong>Seluruh anggota aktif</strong>
                            <small>Semua anggota aktif grup yang dipilih akan menjadi peserta.</small>
                        </span>
                    </label>

                    <label class="agenda-radio-option">
                        <input
                            type="radio"
                            name="audience_type"
                            value="selected"
                            @checked(old('audience_type') === 'selected')
                        >
                        <span>
                            <strong>Pilih anggota tertentu</strong>
                            <small>Undang hanya anggota yang dipilih.</small>
                        </span>
                    </label>

                </div>

                <div class="agenda-members-box" id="agenda-members-box">

                    @if (!$selectedGroupId)
                        <div class="agenda-members-empty">
                            Pilih grup keluarga terlebih dahulu untuk melihat daftar anggotanya.
                        </div>
                    @elseif ($members->isEmpty())
                        <div class="agenda-members-empty">
                            Belum ada anggota aktif dalam grup ini.
                        </div>
                    @else
                        <div class="agenda-members-toolbar">
                            <strong>Anggota Grup</strong>

                            <span>{{ $members->count() }} anggota aktif</span>
                        </div>

                        <div class="agenda-members-list">
                            @foreach ($members as $member)
                                <label class="agenda-member-option">
                                    <input
                                        type="checkbox"
                                        name="participants[]"
                                        value="{{ $member->id }}"
                                        @checked(in_array(
                                            (string) $member->id,
                                            array_map('strval', old('participants', []))
                                        ))
                                    >

                                    <span>
                                        <strong>
                                            {{ $member->nickname ?: $member->full_name }}
                                        </strong>

                                        @if ($member->nickname && $member->full_name)
                                            <small>{{ $member->full_name }}</small>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @endif

                    <small class="agenda-members-note">
                        Anggota yang bergabung setelah agenda dibuat tidak otomatis ditambahkan.
                    </small>

                </div>
            </section>

            <section class="agenda-form-section">
                <div class="agenda-form-section-heading">
                    <span class="agenda-section-number">04</span>
                    <div>
                        <h2>Pengingat</h2>
                        <p>Pilih waktu pengingat sebelum kegiatan.</p>
                    </div>
                </div>

                <div class="agenda-reminder-options">

                    <label class="agenda-reminder-option">
                        <input
                            type="checkbox"
                            name="reminders[]"
                            value="15"
                            @checked(in_array('15', array_map('strval', old('reminders', []))))
                        >
                        <span>
                            <strong>15 menit</strong>
                            <small>Sebelum agenda</small>
                        </span>
                    </label>

                    <label class="agenda-reminder-option">
                        <input
                            type="checkbox"
                            name="reminders[]"
                            value="60"
                            @checked(in_array('60', array_map('strval', old('reminders', []))))
                        >
                        <span>
                            <strong>1 jam</strong>
                            <small>Sebelum agenda</small>
                        </span>
                    </label>

                    <label class="agenda-reminder-option">
                        <input
                            type="checkbox"
                            name="reminders[]"
                            value="1440"
                            @checked(in_array('1440', array_map('strval', old('reminders', []))))
                        >
                        <span>
                            <strong>1 hari</strong>
                            <small>Sebelum agenda</small>
                        </span>
                    </label>

                </div>

                <div class="agenda-reminder-info">
                    Pengingat disimpan sebagai konfigurasi agenda. Pengiriman notifikasi otomatis akan diintegrasikan pada modul notifikasi.
                </div>
            </section>

            <div class="agenda-form-footer">
                <a href="{{ route('family.agendas.index') }}"
                   class="agenda-reset-button">
                    Batal
                </a>

                <button type="submit" class="family-leaf-button">
                    Simpan Agenda
                </button>
            </div>

        </form>
    </div>
        </main>
</div>
@endsection

@push('styles')
<style>
.family-agenda-page {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.agenda-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
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
    font-weight: 750;
    margin: 0 0 7px;
}

.agenda-heading p {
    margin: 0;
    font-size: 13px;
    opacity: .7;
}

.agenda-form-panel {
    border: 1px solid var(--family-border, rgba(150, 120, 80, .2));
    background: var(--family-card-bg, rgba(120, 90, 55, .07));
    border-radius: 16px;
    overflow: hidden;
}

.agenda-form-section {
    padding: 25px;
    border-bottom: 1px solid var(--family-border, rgba(150, 120, 80, .2));
}

.agenda-form-section-heading {
    display: flex;
    align-items: center;
    gap: 13px;
    margin-bottom: 23px;
}

.agenda-section-number {
    display: flex;
    width: 38px;
    height: 38px;
    flex: 0 0 38px;
    justify-content: center;
    align-items: center;
    border-radius: 10px;
    color: #bd955e;
    background: rgba(179, 138, 82, .14);
    font-weight: 800;
    font-size: 12px;
}

.agenda-form-section-heading h2 {
    font-size: 16px;
    margin: 0 0 5px;
    font-weight: 750;
}

.agenda-form-section-heading p {
    margin: 0;
    font-size: 12px;
    opacity: .65;
}

.agenda-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

.agenda-form-field {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 0;
}

.agenda-form-field.full-width {
    grid-column: 1 / -1;
}

.agenda-form-field > label {
    font-size: 12px;
    font-weight: 700;
}

.agenda-form-field input:not([type="checkbox"]),
.agenda-form-field select,
.agenda-form-field textarea {
    width: 100%;
    min-height: 43px;
    padding: 11px 13px;
    border-radius: 9px;
    border: 1px solid var(--family-border, rgba(150, 120, 80, .3));
    background: var(--family-input-bg, rgba(120, 90, 55, .06));
    color: inherit;
    font: inherit;
    font-size: 13px;
    outline: none;
}

.agenda-form-field textarea {
    resize: vertical;
    min-height: 100px;
}

.agenda-form-field input:focus,
.agenda-form-field select:focus,
.agenda-form-field textarea:focus {
    border-color: #b38a52;
    box-shadow: 0 0 0 3px rgba(179, 138, 82, .1);
}

.agenda-form-field option {
    background: #fff;
    color: #30271e;
}

.agenda-form-field small {
    font-size: 11px;
    opacity: .65;
}

.required {
    color: #d77d7d;
}

.agenda-checkbox-option,
.agenda-radio-option {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    padding: 13px;
    border: 1px solid var(--family-border, rgba(150, 120, 80, .2));
    border-radius: 10px;
    cursor: pointer;
}

.agenda-checkbox-option input,
.agenda-radio-option input,
.agenda-member-option input,
.agenda-reminder-option input {
    accent-color: #b38a52;
    width: 16px;
    height: 16px;
    flex: 0 0 16px;
    margin-top: 2px;
}

.agenda-checkbox-option span,
.agenda-radio-option span,
.agenda-member-option span,
.agenda-reminder-option span {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.agenda-checkbox-option strong,
.agenda-radio-option strong,
.agenda-member-option strong,
.agenda-reminder-option strong {
    font-size: 12px;
}

.agenda-checkbox-option small,
.agenda-radio-option small,
.agenda-member-option small,
.agenda-reminder-option small {
    font-size: 11px;
    opacity: .65;
}

.agenda-audience-options {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}


.agenda-members-box {
    transition: opacity 250ms ease, background 250ms ease, border-color 250ms ease;
}

.agenda-members-box.agenda-members-disabled {
    opacity: .55;
    background: rgba(120, 90, 55, .02);
}

.agenda-members-box.agenda-members-disabled .agenda-members-list {
    pointer-events: none;
}

.agenda-members-box.agenda-members-disabled .agenda-member-option {
    cursor: not-allowed;
}


.agenda-members-toolbar {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 14px;
    font-size: 12px;
}

.agenda-members-toolbar span {
    opacity: .6;
}

.agenda-members-list {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 9px;
}

.agenda-member-option {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 11px;
    border-radius: 9px;
    border: 1px solid var(--family-border, rgba(150, 120, 80, .15));
    cursor: pointer;
}

.agenda-members-note {
    display: block;
    margin-top: 14px;
    font-size: 11px;
    opacity: .65;
}

.agenda-members-empty {
    font-size: 12px;
    opacity: .7;
    padding: 10px 0;
}

.agenda-reminder-options {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
}

.agenda-reminder-option {
    display: flex;
    gap: 11px;
    padding: 15px;
    border: 1px solid var(--family-border, rgba(150, 120, 80, .2));
    border-radius: 10px;
    cursor: pointer;
}

.agenda-reminder-info {
    margin-top: 14px;
    padding: 12px 14px;
    border-left: 3px solid #b38a52;
    background: rgba(179, 138, 82, .08);
    border-radius: 5px;
    font-size: 11px;
    line-height: 1.6;
    opacity: .8;
}

.agenda-form-footer {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 10px;
    padding: 20px 25px;
}

.agenda-primary-button {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    padding: 11px 18px;
    border: 0;
    border-radius: 9px;
    background: linear-gradient(135deg, #b68a50, #8c6336);
    color: #fff !important;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none !important;
    cursor: pointer;
}

.agenda-reset-button {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    padding: 10px 15px;
    border: 1px solid var(--family-border, rgba(150, 120, 80, .3));
    border-radius: 9px;
    color: inherit !important;
    font-size: 12px;
    text-decoration: none !important;
}

.agenda-alert {
    padding: 15px 18px;
    border-radius: 10px;
    font-size: 12px;
}

.agenda-alert-danger {
    border: 1px solid rgba(200, 80, 80, .3);
    background: rgba(200, 80, 80, .08);
    color: inherit;
}

.agenda-alert ul {
    margin: 8px 0 0;
    padding-left: 20px;
}

@media (max-width: 700px) {
    .agenda-form-section {
        padding: 19px;
    }

    .agenda-form-grid,
    .agenda-audience-options,
    .agenda-members-list {
        grid-template-columns: 1fr;
    }

    .agenda-reminder-options {
        grid-template-columns: 1fr;
    }

    .agenda-form-footer {
        padding: 17px;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const groupSelect = document.getElementById('family_group_id');

    if (groupSelect) {
        groupSelect.addEventListener('change', function () {
            const baseUrl = groupSelect.dataset.createUrl;
            const url = new URL(baseUrl, window.location.origin);

            if (groupSelect.value) {
                url.searchParams.set('group', groupSelect.value);
            }

            window.location.href = url.toString();
        });
    }

    const audienceRadios = document.querySelectorAll(
        'input[name="audience_type"]'
    );

    const membersBox = document.getElementById('agenda-members-box');
    const memberCheckboxes = membersBox
        ? membersBox.querySelectorAll('input[name="participants[]"]')
        : [];


function updateAudience() {
    const selected = document.querySelector(
        'input[name="audience_type"]:checked'
    );

    const isSelected = selected && selected.value === 'selected';

    if (membersBox) {
        membersBox.classList.toggle('agenda-members-disabled', !isSelected);
        membersBox.setAttribute('aria-disabled', String(!isSelected));
    }

    memberCheckboxes.forEach(function (checkbox) {
        checkbox.disabled = !isSelected;
    });
}

    audienceRadios.forEach(function (radio) {
        radio.addEventListener('change', updateAudience);
    });

    updateAudience();


const allDay = document.querySelector('input[name="is_all_day"]');
const start = document.getElementById('starts_at');
const end = document.getElementById('ends_at');

if (allDay && start && end) {
    const originalValues = {
        start: start.value,
        end: end.value,
    };

    function updateAllDayMode() {
        const isAllDay = allDay.checked;

        if (isAllDay) {
            if (start.type !== 'date') {
                originalValues.start = start.value;
                originalValues.end = end.value;
            }

            start.type = 'date';
            end.type = 'date';

            start.value = originalValues.start.substring(0, 10);
            end.value = originalValues.end.substring(0, 10);
        } else {
            start.type = 'datetime-local';
            end.type = 'datetime-local';

            start.value = originalValues.start;
            end.value = originalValues.end;
        }
    }

    allDay.addEventListener('change', updateAllDayMode);

    updateAllDayMode();
}

});
</script>
@endpush
