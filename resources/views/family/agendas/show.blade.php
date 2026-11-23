
@extends('layouts.family')

@section('title', 'Detail Agenda')

@section('content')
<div class="family-app-layout flex h-screen overflow-hidden">
    @include('family.partials.sidebar')

    <div class="flex min-h-0 min-w-0 flex-1 flex-col">
        @include('family.partials.topbar', [
            'navigationGroup' => $agenda->familyGroup,
        ])

        <main id="family-main"
            class="family-main-content min-h-0 min-w-0 flex-1 overflow-y-auto px-6 py-8 lg:px-10">

            <div class="family-agenda-page mx-auto max-w-5xl space-y-6">

                {{-- Header --}}
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-sm opacity-70">
                            {{ $agenda->familyGroup->name ?? 'Grup Keluarga' }}
                        </p>

                        <h1 class="mt-1 text-2xl font-bold">
                            Detail Agenda
                        </h1>
                    </div>

                    <a href="{{ route('family.agendas.index', ['group' => $agenda->family_group_id]) }}"
                        class="family-leaf-button">
                        Kembali ke Agenda
                    </a>
                </div>

                {{-- Status --}}
                <section class="rounded-2xl border p-6"
                    style="background:var(--family-surface);border-color:var(--family-border);color:var(--family-text-primary);">

                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-sm opacity-70">
                                {{ $agenda->category ?: 'Agenda Keluarga' }}
                            </p>

                            <h2 class="mt-2 text-2xl font-bold">
                                {{ $agenda->title }}
                            </h2>

                            <p class="mt-2 text-sm opacity-70">
                                Dibuat oleh:
                                {{ $agenda->creator->name ?? 'Tidak diketahui' }}
                            </p>
                        </div>

                       @if($agenda->status === 'cancelled')
                            <span class="rounded-full border border-red-400/40 bg-red-900/20 px-4 py-2 text-sm font-semibold text-red-400">
                                Dibatalkan
                            </span>
                        @else
                            <span class="rounded-full border border-green-400/40 bg-green-900/20 px-4 py-2 text-sm font-semibold text-green-400">
                                Terjadwal
                            </span>
                        @endif
                    </div>
                </section>

                {{-- Informasi waktu dan lokasi --}}
                        <section class="rounded-2xl border p-6"
                            style="background:var(--family-surface);border-color:var(--family-border);color:var(--family-text-primary);">
                    <h3 class="mb-5 text-lg font-bold">Informasi Agenda</h3>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <p class="text-sm opacity-60">Tanggal</p>
                            <p class="mt-1 font-semibold">
                                {{ $agenda->starts_at->translatedFormat('l, d F Y') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm opacity-60">Waktu</p>
                            <p class="mt-1 font-semibold">
                                @if($agenda->is_all_day)
                                    Sepanjang hari
                                @else
                                    {{ $agenda->starts_at->format('H:i') }}
                                    @if($agenda->ends_at)
                                        – {{ $agenda->ends_at->format('H:i') }}
                                    @endif
                                @endif
                            </p>
                        </div>

                        <div>
                            <p class="text-sm opacity-60">Lokasi</p>
                            <p class="mt-1 font-semibold">
                                {{ $agenda->location ?: 'Tidak ditentukan' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm opacity-60">Peserta</p>
                            <p class="mt-1 font-semibold">
                                {{ $agenda->participants_count }} orang
                            </p>
                        </div>
                    </div>

                    @if($agenda->description)
                        <div class="mt-6 border-t pt-5"
                            style="border-color:var(--family-border,rgba(150,120,75,.3));">
                            <p class="mb-2 text-sm opacity-60">Deskripsi</p>
                            <div class="whitespace-pre-line leading-relaxed">{{ $agenda->description }}</div>
                        </div>
                    @endif
                </section>

               {{-- Pembatalan --}}
                @if($agenda->status === 'cancelled')
                    <section class="rounded-2xl border p-6"
                        style="
                            background:var(--family-surface);
                            border-color:rgba(220,38,38,.5);
                            color:var(--family-text-primary);
                        ">
                        <h3 class="font-bold" style="color:#dc6262;">
                            Agenda Dibatalkan
                        </h3>

                        <p class="mt-2 text-sm whitespace-pre-line opacity-90">
                            {{ $agenda->cancellation_reason ?: 'Tidak ada alasan pembatalan yang dicatat.' }}
                        </p>
                    </section>
                @endif

           {{-- Daftar peserta --}}
<section class="rounded-2xl border p-6"
    style="background:var(--family-surface);border-color:var(--family-border);color:var(--family-text-primary);">

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <h3 class="text-lg font-bold">Daftar Peserta</h3>

        <span class="text-sm opacity-70">
            {{ $agenda->participants->count() }} peserta
        </span>
    </div>

    @if($agenda->participants->isEmpty())
        <p class="py-6 text-center text-sm opacity-60">
            Belum ada peserta dalam agenda ini.
        </p>
    @else
        <div class="space-y-3">
            @foreach($agenda->participants as $participant)
                @php
                    $attendanceLabels = [
                        'pending'   => 'Belum merespons',
                        'attending' => 'Hadir',
                        'declined'  => 'Tidak hadir',
                        'maybe'     => 'Mungkin hadir',
                    ];

                    $attendanceColors = [
                        'pending'   => 'border border-amber-500/50 text-amber-500',
                        'attending' => 'border border-green-500/50 text-green-500',
                        'declined'  => 'border border-red-500/50 text-red-500',
                        'maybe'     => 'border border-blue-500/50 text-blue-500',
                    ];

                    $attendanceStatus = $participant->attendance_status;
                @endphp

                <div class="rounded-xl border p-4"
                    style="border-color:var(--family-border,rgba(150,120,75,.25));">

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="font-semibold">
                                {{ $participant->familyMember->person->full_name
                                    ?? $participant->familyMember->person->nickname
                                    ?? 'Anggota keluarga' }}
                            </p>

                            @if($participant->response_at)
                                <p class="mt-1 text-xs opacity-60">
                                    Dikonfirmasi:
                                    {{ $participant->response_at->format('d/m/Y H:i') }}
                                </p>
                            @endif
                        </div>

                        <span class="rounded-full px-3 py-1 text-xs font-semibold
                            {{ $attendanceColors[$attendanceStatus] ?? 'border border-gray-500/50 text-gray-500' }}">
                            {{ $attendanceLabels[$attendanceStatus] ?? ucfirst($attendanceStatus) }}
                        </span>
                    </div>

                    @if($participant->notes)
                        <p class="mt-3 border-t pt-3 text-sm opacity-75"
                            style="border-color:var(--family-border,rgba(150,120,75,.2));">
                            {{ $participant->notes }}
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</section>

                {{-- Pengingat --}}
<section class="rounded-2xl border p-6"
    style="background:var(--family-surface);border-color:var(--family-border);color:var(--family-text-primary);">

                    <h3 class="mb-4 text-lg font-bold">Pengingat Agenda</h3>

                    @if($agenda->reminders->where('is_active', true)->isEmpty())
                        <p class="text-sm opacity-60">
                            Tidak ada pengingat aktif.
                        </p>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @foreach($agenda->reminders->where('is_active', true) as $reminder)
                                <span class="rounded-full border px-4 py-2 text-sm"
                                    style="border-color:var(--family-border,rgba(150,120,75,.3));">
                                    @if($reminder->minutes_before == 1440)
                                        1 hari sebelumnya
                                    @elseif($reminder->minutes_before == 60)
                                        1 jam sebelumnya
                                    @else
                                        {{ $reminder->minutes_before }} menit sebelumnya
                                    @endif
                                </span>
                            @endforeach
                        </div>
                    @endif
                </section>

                {{-- Aksi --}}
                @if($agenda->status !== 'cancelled' && ($canUpdate || $canCancel))
                    <section class="flex flex-wrap items-center gap-3">
                        @if($canUpdate)
                            <a href="{{ route('family.agendas.edit', $agenda->id) }}"
                                class="family-leaf-button">
                                Edit Agenda
                            </a>
                        @endif

                        @if($canCancel)
                            <form method="POST"
                                action="{{ route('family.agendas.cancel', $agenda->id) }}"
                                onsubmit="return confirm('Yakin ingin membatalkan agenda ini?')">
                                @csrf

                                <button type="submit"
                                    class="family-leaf-button family-leaf-button--danger">
                                    Batalkan Agenda
                                </button>
                            </form>
                        @endif
                    </section>
                @endif

            </div>
        </main>
    </div>
</div>
@endsection
