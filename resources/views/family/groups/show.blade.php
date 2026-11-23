
@extends('layouts.family')

@section('title', $group->name . ' — Family Portal')

@section('content')

@php
    $isEnglish = session('family_locale', 'id') === 'en';

    $statusLabels = [
        'active' => $isEnglish ? 'Active' : 'Aktif',
        'inactive' => $isEnglish ? 'Inactive' : 'Tidak aktif',
        'archived' => $isEnglish ? 'Archived' : 'Diarsipkan',
    ];

    $typeLabel = str_replace('_', ' ', $group->group_type);

    $displayName = auth('family')->user()->name ?? 'Member';


@endphp

<div class="min-h-screen" style="background: var(--family-background);">

    <div class="flex min-h-screen">

       @include('family.partials.sidebar')

        {{-- Main area --}}
        <div class="min-w-0 flex-1">

           {{-- Shared topbar --}}
                @include('family.partials.topbar', [
                    'navigationGroup' => $group,
                ])

            {{-- Page content --}}
            <main id="family-main"
                class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-10 lg:py-10">
                @if (session('success'))
                    <div class="mb-6 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-4 text-sm font-medium text-emerald-600">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Breadcrumb --}}
                <nav class="mb-6 flex items-center gap-2 text-sm"
                    aria-label="{{ $isEnglish ? 'Breadcrumb' : 'Navigasi halaman' }}">

                    <a href="{{ route('family.dashboard') }}"
                        class="font-medium text-blue-500 hover:underline">
                        Dashboard
                    </a>

                    <span style="color: var(--family-text-secondary);">/</span>

                    <span style="color: var(--family-text-secondary);">
                        {{ $group->name }}
                    </span>
                </nav>

                {{-- Group heading --}}
                <section class="mb-8">

                    <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">

                        <div class="flex min-w-0 items-start gap-4">

                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-blue-500/10 text-blue-500">
                                <svg viewBox="0 0 24 24" fill="none"
                                    class="h-7 w-7" stroke="currentColor" stroke-width="1.7">
                                    <path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-5h6v5"/>
                                </svg>
                            </div>

                            <div class="min-w-0">
                                <p class="mb-2 text-sm font-semibold uppercase tracking-wider text-blue-500">
                                    {{ $isEnglish ? 'Family Group' : 'Family Group' }}
                                </p>

                                <h1 class="break-words text-2xl font-bold tracking-tight sm:text-3xl"
                                    style="color: var(--family-text-primary);">
                                    {{ $group->name }}
                                </h1>

                                <p class="mt-2 text-sm capitalize"
                                    style="color: var(--family-text-secondary);">
                                    {{ $typeLabel }}
                                </p>
                            </div>
                        </div>

                     <div class="flex flex-wrap items-center gap-3">
                            @if ($canManageDetails && $group->status === 'active')
                                <a href="{{ route('family.groups.edit', $group->id) }}"
                                   class="family-leaf-button inline-flex w-fit items-center justify-center gap-2"
                                    <span aria-hidden="true">✎</span>
                                    {{ $isEnglish ? 'Edit Group' : 'Edit Grup' }}
                                </a>
                            @endif

                            <a href="{{ route('family.groups.people.index', $group->id) }}"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border px-4 py-3 text-sm font-semibold transition hover:opacity-80"
                                style="border-color: var(--family-border); color: var(--family-text-primary);">
                                <span aria-hidden="true">👥</span>
                                {{ $isEnglish ? 'People Data' : 'Data Individu' }}
                            </a>
                        </div>
                    </div>
                </section>

                {{-- Group information --}}
                <section class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-3">

                    <div class="rounded-2xl border p-5"
                        style="background: var(--family-surface); border-color: var(--family-border);">

                        <p class="text-sm"
                            style="color: var(--family-text-secondary);">
                            {{ $isEnglish ? 'Group status' : 'Status grup' }}
                        </p>

                        <span class="mt-3 inline-flex rounded-full px-3 py-1 text-xs font-semibold
                            {{ $group->status === 'active'
                                ? 'bg-emerald-500/10 text-emerald-500'
                                : ($group->status === 'inactive'
                                    ? 'bg-amber-500/10 text-amber-500'
                                    : 'bg-slate-500/10 text-slate-400') }}">
                            {{ $statusLabels[$group->status] ?? $group->status }}
                        </span>
                    </div>

                    <div class="rounded-2xl border p-5"
                        style="background: var(--family-surface); border-color: var(--family-border);">

                        <p class="text-sm"
                            style="color: var(--family-text-secondary);">
                            {{ $isEnglish ? 'Active members' : 'Anggota aktif' }}
                        </p>

                        <p class="mt-2 text-3xl font-bold tabular-nums"
                            style="color: var(--family-text-primary);">
                            {{ number_format($activeMembersCount) }}
                        </p>
                    </div>

                    <div class="rounded-2xl border p-5"
                        style="background: var(--family-surface); border-color: var(--family-border);">

                        <p class="text-sm"
                            style="color: var(--family-text-secondary);">
                            {{ $isEnglish ? 'Group type' : 'Tipe grup' }}
                        </p>

                        <p class="mt-2 text-xl font-bold capitalize"
                            style="color: var(--family-text-primary);">
                            {{ $typeLabel }}
                        </p>
                    </div>
                </section>

                {{-- Description --}}
                <section class="mb-8 rounded-2xl border p-5 sm:p-6"
                    style="background: var(--family-surface); border-color: var(--family-border);">

                    <h2 class="text-base font-bold"
                        style="color: var(--family-text-primary);">
                        {{ $isEnglish ? 'About this group' : 'Tentang grup ini' }}
                    </h2>

                    <p class="mt-3 text-sm leading-7"
                        style="color: var(--family-text-secondary);">
                        {{ $group->description ?: ($isEnglish
                            ? 'No group description has been added yet.'
                            : 'Belum ada deskripsi untuk grup ini.') }}
                    </p>
                </section>


{{-- Super Admin: Group Settings --}}
@if ($canManageGroupSettings)
    <section class="mb-8 rounded-2xl border p-5 sm:p-6"
        style="background: var(--family-surface); border-color: var(--family-border);">

        <div class="mb-6">
            <h2 class="text-lg font-bold"
                style="color: var(--family-text-primary);">
                {{ $isEnglish ? 'Family Group Settings' : 'Pengaturan Family Group' }}
            </h2>

            <p class="mt-1 text-sm"
                style="color: var(--family-text-secondary);">
                {{ $isEnglish
                    ? 'Manage group type and operational status.'
                    : 'Kelola tipe dan status operasional grup.' }}
            </p>
        </div>

        {{-- Change group type --}}
        @if ($group->status !== 'archived')
            <form method="POST"
                action="{{ route('family.groups.type', $group->id) }}"
                class="mb-6 border-b pb-6"
                style="border-color: var(--family-border);">
                @csrf
                @method('PUT')

                <label class="mb-2 block text-sm font-semibold"
                    style="color: var(--family-text-primary);">
                    {{ $isEnglish ? 'Group Type' : 'Tipe Grup' }}
                </label>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <input type="text"
                        name="group_type"
                        value="{{ old('group_type', $group->group_type) }}"
                        required
                        maxlength="30"
                        class="w-full rounded-xl border px-4 py-3 text-sm"
                        style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">

                    <button type="submit"
                     class="family-leaf-button">
                     {{ $isEnglish ? 'Update Type' : 'Ubah Tipe' }}
                    </button>
                </div>

                <label class="mt-3 block text-sm"
                    style="color: var(--family-text-secondary);">
                    {{ $isEnglish ? 'Reason (optional)' : 'Alasan perubahan (opsional)' }}
                </label>

                <input type="text"
                    name="reason"
                    maxlength="500"
                    class="mt-2 w-full rounded-xl border px-4 py-3 text-sm"
                    style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">
            </form>
        @endif

        {{-- Change status --}}
        @if ($group->status !== 'archived')
            <form method="POST"
                action="{{ route('family.groups.status', $group->id) }}"
                class="mb-6 border-b pb-6"
                style="border-color: var(--family-border);"
                onsubmit="return confirm('{{ $isEnglish ? 'Change this group status?' : 'Ubah status grup ini?' }}')">
                @csrf
                @method('PUT')

                <input type="hidden" name="status"
                    value="{{ $group->status === 'active' ? 'inactive' : 'active' }}">

                <label class="mb-3 block text-sm font-semibold"
                    style="color: var(--family-text-primary);">
                    {{ $isEnglish ? 'Operational Status' : 'Status Operasional' }}
                </label>

                <p class="mb-3 text-sm"
                    style="color: var(--family-text-secondary);">
                    {{ $group->status === 'active'
                        ? ($isEnglish ? 'This group is currently active.' : 'Grup ini sedang aktif.')
                        : ($isEnglish ? 'This group is currently inactive.' : 'Grup ini sedang tidak aktif.') }}
                </p>

                <input type="text"
                    name="reason"
                    maxlength="500"
                    placeholder="{{ $isEnglish ? 'Reason (optional)' : 'Alasan perubahan (opsional)' }}"
                    class="mb-3 w-full rounded-xl border px-4 py-3 text-sm"
                    style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">

                <button type="submit"
                    class="family-leaf-button">
                    {{ $group->status === 'active'
                        ? ($isEnglish ? 'Deactivate Group' : 'Nonaktifkan Grup')
                        : ($isEnglish ? 'Activate Group' : 'Aktifkan Grup') }}
                </button>
            </form>

            {{-- Archive --}}
            <form method="POST"
                action="{{ route('family.groups.archive', $group->id) }}"
                onsubmit="return confirm('{{ $isEnglish ? 'Archive this group? This will preserve its data and history.' : 'Arsipkan grup ini? Seluruh data dan riwayat tetap disimpan.' }}')">
                @csrf

                <label class="mb-2 block text-sm font-semibold"
                    style="color: var(--family-text-primary);">
                    {{ $isEnglish ? 'Archive Group' : 'Arsipkan Grup' }}
                </label>

                <input type="text"
                    name="reason"
                    maxlength="500"
                    placeholder="{{ $isEnglish ? 'Reason (optional)' : 'Alasan pengarsipan (opsional)' }}"
                    class="mb-3 w-full rounded-xl border px-4 py-3 text-sm"
                    style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">

                <button type="submit"
                    class="inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold"
                    style="background-color: #dc2626 !important; color: #ffffff !important;">
                    {{ $isEnglish ? 'Archive Family Group' : 'Arsipkan Family Group' }}
                </button>
            </form>
        @else

            {{-- Restore --}}
            <form method="POST"
                action="{{ route('family.groups.restore', $group->id) }}"
                onsubmit="return confirm('{{ $isEnglish ? 'Restore this archived group?' : 'Pulihkan grup yang diarsipkan ini?' }}')">
                @csrf

                <label class="mb-2 block text-sm font-semibold"
                    style="color: var(--family-text-primary);">
                    {{ $isEnglish ? 'Restore Group' : 'Pulihkan Grup' }}
                </label>

                <input type="text"
                    name="reason"
                    maxlength="500"
                    placeholder="{{ $isEnglish ? 'Reason (optional)' : 'Alasan pemulihan (opsional)' }}"
                    class="mb-3 w-full rounded-xl border px-4 py-3 text-sm"
                    style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">

                <button type="submit"
                    class="family-leaf-button">
                    {{ $isEnglish ? 'Restore Family Group' : 'Pulihkan Family Group' }}
                </button>
            </form>
        @endif
    </section>
@endif

           {{-- Members --}}
            <section id="anggota-keluarga"
                class="scroll-mt-6 overflow-hidden rounded-2xl border"
                style="background: var(--family-surface); border-color: var(--family-border);">

                    <div class="flex flex-col gap-2 border-b px-5 py-5 sm:flex-row sm:items-center sm:justify-between"
                        style="border-color: var(--family-border);">

                        <div>
                            <h2 class="text-lg font-bold"
                                style="color: var(--family-text-primary);">
                                {{ $isEnglish ? 'Family Members' : 'Anggota Keluarga' }}
                            </h2>

                            <p class="mt-1 text-sm"
                                style="color: var(--family-text-secondary);">
                                {{ $isEnglish
                                    ? 'People who are currently active in this group.'
                                    : 'Anggota yang saat ini aktif dalam grup ini.' }}
                            </p>
                        </div>
                        @if ($canManageMembers && $group->status === 'active')
                            <button
                                type="button"
                                onclick="openAddMemberModal()"
                                class="family-leaf-button inline-flex w-fit items-center justify-center gap-2">
                                <span aria-hidden="true">+</span>
                                {{ $isEnglish ? 'Add Member' : 'Tambah Anggota' }}
                            </button>
                        @endif
                    </div>

                    @if ($members->isNotEmpty())

                        <div class="divide-y"
                            style="border-color: var(--family-border);">

                            @foreach ($members as $member)
                                <div class="flex items-center gap-4 px-5 py-4">

                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-blue-500/10 font-bold text-blue-500">

                                        @if ($member->person?->photo_path)
                                            <img
                                                src="{{ asset('storage/' . $member->person->photo_path) }}"
                                                alt=""
                                                class="h-full w-full object-cover">
                                        @else
                                            {{ mb_strtoupper(mb_substr(
                                                $member->person?->nickname
                                                    ?: $member->person?->full_name
                                                    ?: '?',
                                                0,
                                                1
                                            )) }}
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">

                                        <p class="truncate text-sm font-semibold"
                                            style="color: var(--family-text-primary);">
                                            {{ $member->person?->nickname
                                                ?: $member->person?->full_name
                                                ?: ($isEnglish ? 'Unknown member' : 'Anggota tidak dikenal') }}
                                        </p>

                                        <p class="mt-1 text-xs"
                                            style="color: var(--family-text-secondary);">
                                            {{ $member->role?->name
                                                ?: ($isEnglish ? 'Family Member' : 'Anggota Keluarga') }}
                                        </p>
                                    </div>

                                    <div class="shrink-0 text-right">

                                    @php
                                        $statusLabels = [
                                            'pending' => $isEnglish ? 'Pending' : 'Menunggu',
                                            'active' => $isEnglish ? 'Active' : 'Aktif',
                                            'inactive' => $isEnglish ? 'Inactive' : 'Tidak Aktif',
                                            'left' => $isEnglish ? 'Left' : 'Keluar',
                                            'rejected' => $isEnglish ? 'Rejected' : 'Ditolak',
                                        ];

                                        $statusColors = [
                                            'pending' => 'bg-amber-500/10 text-amber-500',
                                            'active' => 'bg-emerald-500/10 text-emerald-500',
                                            'inactive' => 'bg-slate-500/10 text-slate-500',
                                            'left' => 'bg-orange-500/10 text-orange-500',
                                            'rejected' => 'bg-red-500/10 text-red-500',
                                        ];
                                    @endphp

                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusColors[$member->status] ?? 'bg-slate-500/10 text-slate-500' }}">
                                        {{ $statusLabels[$member->status] ?? $member->status }}
                                    </span>

                                    @if ($member->status === 'left' && $member->left_at)
                                        <p class="mt-2 text-xs" style="color: var(--family-text-secondary);">
                                            {{ $isEnglish ? 'Left' : 'Keluar' }}
                                            {{ $member->left_at->format('d M Y') }}
                                        </p>
                                    @elseif ($member->joined_at)
                                        <p class="mt-2 text-xs" style="color: var(--family-text-secondary);">
                                            {{ $isEnglish ? 'Joined' : 'Bergabung' }}
                                            {{ $member->joined_at->format('d M Y') }}
                                        </p>
                                    @endif

                                  @if (
    $canManageMembers
    && $group->status === 'active'
    && (int) $member->person?->user_id !== (int) $currentUserId
)
    <div class="mt-3 flex flex-wrap justify-end gap-2">

        {{-- Pending: Approve --}}
        @if ($member->status === 'pending')
            <form method="POST"
                action="{{ route('family.groups.members.status', [$group->id, $member->id]) }}"
                class="flex flex-wrap items-center justify-end gap-2">
                @csrf
                @method('PATCH')

                <input type="hidden" name="status" value="active">

                <select name="role_id"
                    required
                    class="max-w-36 rounded-lg border px-2 py-2 text-xs"
                    style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">
                    <option value="">
                        {{ $isEnglish ? 'Select role' : 'Pilih peran' }}
                    </option>

                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}"
                            @selected((int) $role->id === (int) $member->role_id)>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit"
                    class="family-leaf-button">
                    {{ $isEnglish ? 'Approve' : 'Setujui' }}
                </button>
            </form>

            {{-- Pending: Reject --}}
            <form method="POST"
                action="{{ route('family.groups.members.status', [$group->id, $member->id]) }}"
                onsubmit="return confirm('{{ $isEnglish ? 'Reject this membership?' : 'Tolak keanggotaan ini?' }}')">
                @csrf
                @method('PATCH')

                <input type="hidden" name="status" value="rejected">

                <button type="submit"
                    class="rounded-lg bg-red-500/10 px-3 py-2 text-xs font-semibold text-red-500 hover:bg-red-500/20">
                    {{ $isEnglish ? 'Reject' : 'Tolak' }}
                </button>
            </form>
        @endif

        {{-- Active: Deactivate or mark as left --}}
        @if ($member->status === 'active')
            <form method="POST"
                action="{{ route('family.groups.members.status', [$group->id, $member->id]) }}">
                @csrf
                @method('PATCH')

                <input type="hidden" name="status" value="inactive">

                <button type="submit"
                    class="rounded-lg bg-amber-500/10 px-3 py-2 text-xs font-semibold text-amber-600 hover:bg-amber-500/20">
                    {{ $isEnglish ? 'Deactivate' : 'Nonaktifkan' }}
                </button>
            </form>

            <form method="POST"
                action="{{ route('family.groups.members.status', [$group->id, $member->id]) }}"
                onsubmit="return confirm('{{ $isEnglish ? 'Mark this member as left?' : 'Tandai anggota ini keluar?' }}')">
                @csrf
                @method('PATCH')

                <input type="hidden" name="status" value="left">

                <button type="submit"
                    class="rounded-lg bg-orange-500/10 px-3 py-2 text-xs font-semibold text-orange-600 hover:bg-orange-500/20">
                    {{ $isEnglish ? 'Mark as Left' : 'Tandai Keluar' }}
                </button>
            </form>
        @endif

        {{-- Inactive: Reactivate or mark as left --}}
        @if ($member->status === 'inactive')
            <form method="POST"
                action="{{ route('family.groups.members.status', [$group->id, $member->id]) }}"
                class="flex flex-wrap items-center justify-end gap-2">
                @csrf
                @method('PATCH')

                <input type="hidden" name="status" value="active">

                <select name="role_id"
                    required
                    class="max-w-36 rounded-lg border px-2 py-2 text-xs"
                    style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">
                    <option value="">
                        {{ $isEnglish ? 'Select role' : 'Pilih peran' }}
                    </option>

                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}"
                            @selected((int) $role->id === (int) $member->role_id)>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit"
                    class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
                    {{ $isEnglish ? 'Reactivate' : 'Aktifkan Kembali' }}
                </button>
            </form>

            <form method="POST"
                action="{{ route('family.groups.members.status', [$group->id, $member->id]) }}"
                onsubmit="return confirm('{{ $isEnglish ? 'Mark this member as left?' : 'Tandai anggota ini keluar?' }}')">
                @csrf
                @method('PATCH')

                <input type="hidden" name="status" value="left">

                <button type="submit"
                    class="rounded-lg bg-orange-500/10 px-3 py-2 text-xs font-semibold text-orange-600 hover:bg-orange-500/20">
                    {{ $isEnglish ? 'Mark as Left' : 'Tandai Keluar' }}
                </button>
            </form>
        @endif

        {{-- Left: Reactivate --}}
        @if ($member->status === 'left')
            <form method="POST"
                action="{{ route('family.groups.members.status', [$group->id, $member->id]) }}"
                class="flex flex-wrap items-center justify-end gap-2">
                @csrf
                @method('PATCH')

                <input type="hidden" name="status" value="active">

                <select name="role_id"
                    required
                    class="max-w-36 rounded-lg border px-2 py-2 text-xs"
                    style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">
                    <option value="">
                        {{ $isEnglish ? 'Select role' : 'Pilih peran' }}
                    </option>

                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}"
                            @selected((int) $role->id === (int) $member->role_id)>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit"
                    class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
                    {{ $isEnglish ? 'Reactivate' : 'Terima Kembali' }}
                </button>
            </form>
        @endif

    </div>
@endif

                                </div>
                                </div>
                            @endforeach
                        </div>

                    @else

                        <div class="px-6 py-12 text-center">

                            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-500/10 text-blue-500">
                                <svg viewBox="0 0 24 24" fill="none"
                                    class="h-7 w-7" stroke="currentColor" stroke-width="1.7">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                </svg>
                            </div>

                            <h3 class="font-semibold"
                                style="color: var(--family-text-primary);">
                                {{ $isEnglish ? 'No active members' : 'Belum ada anggota aktif' }}
                            </h3>

                            <p class="mt-2 text-sm"
                                style="color: var(--family-text-secondary);">
                                {{ $isEnglish
                                    ? 'There are currently no active members in this group.'
                                    : 'Saat ini belum ada anggota yang aktif dalam grup ini.' }}
                            </p>
                        </div>

                    @endif
                </section>

                {{-- Back --}}
                <div class="mt-8">
                    <a href="{{ route('family.dashboard') }}"
                        class="inline-flex items-center gap-2 rounded-xl border px-4 py-3 text-sm font-semibold transition hover:bg-blue-500/5"
                        style="border-color: var(--family-border); color: var(--family-text-primary);">
                        <span aria-hidden="true">←</span>
                        {{ $isEnglish ? 'Back to Dashboard' : 'Kembali ke Dashboard' }}
                    </a>
                </div>

            </main>
        </div>
    </div>
</div>


{{-- Add Member Modal --}}
@if ($canManageMembers && $group->status === 'active')
    <dialog
        id="addMemberModal"
        class="rounded-2xl border p-0 shadow-2xl backdrop:bg-slate-950/60"
        style="
            position: fixed;
            inset: 0;
            margin: auto;
            width: calc(100% - 2rem);
            max-width: 36rem;
            max-height: 90vh;
            overflow-y: auto;
            background: var(--family-surface);
            border-color: var(--family-border);
            color: var(--family-text-primary);
        "
    >

        <div class="flex items-center justify-between border-b px-6 py-5"
            style="border-color: var(--family-border);">

            <div>
                <h2 class="text-lg font-bold">
                    {{ $isEnglish ? 'Add Family Member' : 'Tambah Anggota Keluarga' }}
                </h2>

                <p class="mt-1 text-sm"
                    style="color: var(--family-text-secondary);">
                    {{ $group->name }}
                </p>
            </div>

            <button type="button"
                onclick="closeAddMemberModal()"
                class="rounded-lg px-3 py-2 text-xl"
                aria-label="{{ $isEnglish ? 'Close' : 'Tutup' }}">
                &times;
            </button>
        </div>

        <form method="POST"
            action="{{ route('family.groups.members.store', $group->id) }}"
            class="space-y-5 p-6">

            @csrf

            <div>
                <label for="modal_person_id" class="mb-2 block text-sm font-semibold">
                    {{ $isEnglish ? 'Individual' : 'Individu' }}
                </label>

                <select id="modal_person_id"
                    name="person_id"
                    required
                    class="w-full rounded-xl border px-4 py-3 text-sm"
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
                    <p class="mt-2 text-sm text-amber-500">
                        {{ $isEnglish ? 'No eligible individuals available.' : 'Belum ada individu yang dapat ditambahkan.' }}
                    </p>
                @endif
            </div>

            <div>
                <label for="modal_role_id" class="mb-2 block text-sm font-semibold">
                    {{ $isEnglish ? 'Group Role' : 'Peran dalam Grup' }}
                </label>

                <select id="modal_role_id"
                    name="role_id"
                    required
                    class="w-full rounded-xl border px-4 py-3 text-sm"
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
            </div>

            <div>
                <label for="modal_reason" class="mb-2 block text-sm font-semibold">
                    {{ $isEnglish ? 'Reason (Optional)' : 'Alasan (Opsional)' }}
                </label>

                <textarea id="modal_reason"
                    name="reason"
                    rows="3"
                    maxlength="500"
                    class="w-full rounded-xl border px-4 py-3 text-sm"
                    style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">{{ old('reason') }}</textarea>

                @error('reason')
                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="rounded-xl border p-4 text-sm leading-6"
                style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-secondary);">
                {{ $isEnglish
                    ? 'New memberships will initially have Pending status. Group access is granted only after approval and activation.'
                    : 'Keanggotaan baru akan berstatus Menunggu (Pending). Akses grup diberikan setelah keanggotaan disetujui dan diaktifkan.' }}
            </div>

            <div class="flex flex-col-reverse gap-3 border-t pt-5 sm:flex-row sm:justify-end"
                style="border-color: var(--family-border);">

                <button type="button"
                    onclick="closeAddMemberModal()"
                    class="rounded-xl border px-5 py-3 text-sm font-semibold"
                    style="border-color: var(--family-border);">
                    {{ $isEnglish ? 'Cancel' : 'Batal' }}
                </button>

                <button type="submit"
                    @disabled($people->isEmpty() || $roles->isEmpty())
                    class="family-leaf-button">
                    {{ $isEnglish ? 'Add Member' : 'Tambah Anggota' }}
                </button>
            </div>
        </form>
    </dialog>

    <script>
        function openAddMemberModal() {
            document.getElementById('addMemberModal')?.showModal();
        }

        function closeAddMemberModal() {
            document.getElementById('addMemberModal')?.close();
        }

        document.getElementById('addMemberModal')?.addEventListener('click', function (event) {
            if (event.target === this) {
                this.close();
            }
        });

        @if ($errors->has('person_id') || $errors->has('role_id') || $errors->has('reason'))
            openAddMemberModal();
        @endif
    </script>
@endif


@endsection
