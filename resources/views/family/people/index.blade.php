
@extends('layouts.family')

@section('content')
@php
    $isEnglish = session('family_locale', 'id') === 'en';

    $statusLabels = [
        'pending' => $isEnglish ? 'Pending' : 'Menunggu',
        'active' => $isEnglish ? 'Active' : 'Aktif',
        'inactive' => $isEnglish ? 'Inactive' : 'Tidak Aktif',
        'left' => $isEnglish ? 'Left' : 'Keluar',
        'rejected' => $isEnglish ? 'Rejected' : 'Ditolak',
    ];

    $lifeStatusLabels = [
        'unknown' => $isEnglish ? 'Unknown' : 'Belum diketahui',
        'alive' => $isEnglish ? 'Alive' : 'Hidup',
        'deceased' => $isEnglish ? 'Deceased' : 'Meninggal',
    ];
@endphp

<div class="h-screen overflow-hidden"
     style="background: var(--family-background);">

    <div class="flex h-full min-h-0">

        {{-- Shared desktop sidebar --}}
        @include('family.partials.sidebar', [
            'navigationGroup' => $group,
            'activeMenu' => 'people',
        ])

        <div class="flex min-w-0 min-h-0 flex-1 flex-col overflow-hidden">

            {{-- Shared topbar --}}
            @include('family.partials.topbar', [
                'navigationGroup' => $group,
            ])

          <main id="family-main"
      class="min-h-0 flex-1 overflow-y-auto overflow-x-hidden px-4 py-6 sm:px-6 lg:px-8"
      style="color: var(--family-text-primary);">

                <div class="mx-auto max-w-7xl space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <a href="{{ route('family.groups.show', $group->id) }}"
                   class="text-sm hover:underline"
                   style="color: var(--family-text-secondary);">
                    ← {{ $isEnglish ? 'Back to family group' : 'Kembali ke grup keluarga' }}
                </a>

                <h1 class="mt-3 text-2xl font-bold sm:text-3xl">
                    {{ $isEnglish ? 'Family People' : 'Data Individu' }}
                </h1>

                <p class="mt-1 text-sm"
                   style="color: var(--family-text-secondary);">
                    {{ $group->name }}
                </p>
            </div>

            @if ($canCreate)
                <a href="{{ route('family.groups.people.create', $group->id) }}"
                   class="inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:opacity-90"
                   style="background: var(--family-accent, #4f46e5);">
                    + {{ $isEnglish ? 'Add Person' : 'Tambah Individu' }}
                </a>
            @endif
        </div>

        {{-- Flash message --}}
        @if (session('success'))
            <div class="rounded-xl border px-4 py-3 text-sm"
                 style="background: var(--family-surface); border-color: var(--family-border);">
                {{ session('success') }}
            </div>
        @endif

        {{-- People table --}}
        <div class="overflow-hidden rounded-2xl border shadow-sm"
             style="background: var(--family-surface); border-color: var(--family-border);">

            <div class="border-b px-5 py-4"
                 style="border-color: var(--family-border);">
                <h2 class="font-semibold">
                    {{ $isEnglish ? 'Registered People' : 'Individu Terdaftar' }}
                </h2>
                <p class="mt-1 text-sm"
                   style="color: var(--family-text-secondary);">
                    {{ $isEnglish
                        ? 'People registered in this family group.'
                        : 'Daftar individu yang terdaftar dalam grup keluarga ini.' }}
                </p>
            </div>
                @if ($people->isEmpty())
                <div class="px-5 py-12 text-center">
                    <div class="text-4xl">👥</div>
                    <h3 class="mt-3 font-semibold">
                        {{ $isEnglish ? 'No people yet' : 'Belum ada individu' }}
                    </h3>
                    <p class="mt-1 text-sm"
                       style="color: var(--family-text-secondary);">
                        {{ $isEnglish
                            ? 'Start by adding a person to this family group.'
                            : 'Mulai dengan menambahkan individu ke grup keluarga ini.' }}
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[650px] text-left text-sm">
                        <thead style="background: var(--family-background);">
                            <tr>
                                <th class="px-5 py-3 font-semibold">
                                    {{ $isEnglish ? 'Name' : 'Nama Individu' }}
                                </th>
                                <th class="px-5 py-3 font-semibold">
                                    {{ $isEnglish ? 'Gender' : 'Jenis Kelamin' }}
                                </th>
                                <th class="px-5 py-3 font-semibold">
                                    {{ $isEnglish ? 'Life Status' : 'Status Kehidupan' }}
                                </th>
                                <th class="px-5 py-3 font-semibold">
                                    {{ $isEnglish ? 'Membership' : 'Keanggotaan' }}
                                </th>
                                <th class="px-5 py-3 text-right font-semibold">
                                    {{ $isEnglish ? 'Action' : 'Aksi' }}
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($people as $person)
                                @php
                                    $membership = $person->memberships->first();
                                @endphp

                                <tr class="border-t"
                                    style="border-color: var(--family-border);">

                                    
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">

                                    {{-- Profile Avatar --}}
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full border"
                                        style="background: var(--family-background); border-color: var(--family-border);">

                                        @if ($person->photo_path)
                                            <img
                                                src="{{ route('family.groups.people.photo', [
                                                    'groupId' => $group->id,
                                                    'personId' => $person->id,
                                                ]) }}"
                                                alt="{{ $person->full_name }}"
                                                loading="lazy"
                                                class="h-full w-full object-cover"
                                            >
                                        @else
                                            <span class="text-sm font-bold"
                                                style="color: var(--family-text-secondary);">
                                                {{ mb_strtoupper(mb_substr($person->full_name, 0, 1)) }}
                                            </span>
                                        @endif

                                    </div>

                                    {{-- Person Name --}}
                                    <div class="min-w-0">
                                        <div class="font-semibold">
                                            {{ $person->full_name }}
                                        </div>

                                        @if ($person->nickname)
                                            <div class="mt-1 text-xs"
                                                style="color: var(--family-text-secondary);">
                                                {{ $person->nickname }}
                                            </div>
                                        @endif
                                    </div>

                                </div>
                            </td>
                                    <td class="px-5 py-4">
                                        @if ($person->sex === 'male')
                                            {{ $isEnglish ? 'Male' : 'Laki-laki' }}
                                        @elseif ($person->sex === 'female')
                                            {{ $isEnglish ? 'Female' : 'Perempuan' }}
                                        @else
                                            —
                                        @endif
                                    </td>

                                    <td class="px-5 py-4">
                                      {{ $lifeStatusLabels[$person->life_status ?? 'unknown']
                                         ?? ucfirst($person->life_status ?? 'unknown') }}
                                    </td>

                                    <td class="px-5 py-4">
                                        @if ($membership)
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium"
                                                  style="background: var(--family-background); color: var(--family-text-primary);">
                                                {{ $statusLabels[$membership->status] ?? $membership->status }}
                                            </span>
                                        @else
                                            —
                                        @endif
                                    </td>

                                    <td class="px-5 py-4 text-right">
                                        @if ($membership)
                                            <div class="inline-flex items-center justify-end gap-3">
                                                <a href="{{ route('family.groups.people.show', [
                                                    'groupId' => $group->id,
                                                    'personId' => $person->id,
                                                ]) }}"
                                                class="font-semibold hover:underline"
                                                style="color: var(--family-accent, #4f46e5);">
                                                    {{ $isEnglish ? 'Details' : 'Detail' }}
                                                </a>

                                                @if ($canUpdate)
                                                    <a href="{{ route('family.groups.people.edit', [
                                                        'groupId' => $group->id,
                                                        'personId' => $person->id,
                                                    ]) }}"
                                                    class="font-semibold hover:underline"
                                                    style="color: var(--family-text-primary);">
                                                        Edit
                                                    </a>
                                                @endif
                                            </div>
                                        @else
                                            <span style="color: var(--family-text-secondary);">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t px-5 py-4"
                     style="border-color: var(--family-border);">
                    {{ $people->links() }}
                </div>
            @endif
        </div>

        <p class="text-xs"
           style="color: var(--family-text-secondary);">
            {{ $isEnglish
                ? 'Person identity data may be shared across multiple family groups.'
                : 'Data identitas individu dapat digunakan bersama oleh beberapa grup keluarga.' }}
        </p>

    </div>
                                </div>
            </main>
        </div>
    </div>
</div>
@endsection