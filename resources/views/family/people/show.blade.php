
@extends('layouts.family')

@section('content')
@php
    $isEnglish = session('family_locale', 'id') === 'en';

    $lifeStatusLabels = [
        'unknown' => $isEnglish ? 'Unknown' : 'Belum diketahui',
        'alive' => $isEnglish ? 'Alive' : 'Hidup',
        'deceased' => $isEnglish ? 'Deceased' : 'Meninggal',
    ];

    $relationshipGroups = [
        'parent' => [
            'title' => $isEnglish ? 'Parents' : 'Orang Tua',
            'items' => [],
        ],
        'spouse' => [
            'title' => $isEnglish ? 'Spouse & History' : 'Pasangan & Riwayat',
            'items' => [],
        ],
        'child' => [
            'title' => $isEnglish ? 'Children' : 'Anak',
            'items' => [],
        ],
        'guardian' => [
            'title' => $isEnglish ? 'Guardianship' : 'Hubungan Wali',
            'items' => [],
        ],
    ];


foreach ($relationships as $relationship) {
    $isOutgoing = (int) $relationship->person_id === (int) $person->id;

    $relatedPerson = $isOutgoing
        ? $relationship->relatedPerson
        : $relationship->person;

    if (!$relatedPerson) {
        continue;
    }

    $type = $relationship->relationship_type;


    if ($type === 'parent') {
        // person_id = orang tua, related_person_id = anak.
        $section = $isOutgoing ? 'child' : 'parent';

        $direction = $isOutgoing
            ? ($isEnglish ? 'Child of this person' : 'Anak dari individu ini')
            : ($isEnglish ? 'Parent of this person' : 'Orang tua dari individu ini');

    } elseif ($type === 'spouse') {
        $section = 'spouse';
        $direction = $isEnglish ? 'Spouse' : 'Pasangan';

    } elseif ($type === 'guardian') {
        $section = 'guardian';

        $direction = $isOutgoing
            ? ($isEnglish ? 'Ward of this person' : 'Dalam perwalian individu ini')
            : ($isEnglish ? 'Guardian of this person' : 'Wali dari individu ini');

    } else {
        continue;
    }


    $subtypeLabels = [
        'biological' => $isEnglish ? 'Biological' : 'Biologis',
        'adoptive' => $isEnglish ? 'Adoptive' : 'Angkat',
        'step' => $isEnglish ? 'Step' : 'Tiri',
        'legal' => 'Legal',
        'informal' => 'Informal',
    ];

    $parentRoleLabels = [
        'father' => $isEnglish ? 'Father' : 'Ayah',
        'mother' => $isEnglish ? 'Mother' : 'Ibu',
        'unspecified' => $isEnglish ? 'Parent' : 'Orang tua',
    ];

    $relationshipGroups[$section]['items'][] = [
        'relationship' => $relationship,
        'person' => $relatedPerson,
        'direction' => $direction,
        'subtype' => $subtypeLabels[$relationship->relationship_subtype] ?? null,
        'parent_role' => $parentRoleLabels[$relationship->parent_role] ?? null,
        'started_at' => $relationship->started_at,
        'ended_at' => $relationship->ended_at,
        'notes' => $relationship->notes,
    ];
}
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

                <div class="mx-auto max-w-5xl space-y-6">

        {{-- Header --}}
        <div>
            <a href="{{ route('family.groups.people.index', $group->id) }}"
               class="text-sm hover:underline"
               style="color: var(--family-text-secondary);">
                ← {{ $isEnglish ? 'Back to people' : 'Kembali ke Data Individu' }}
            </a>

            <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold sm:text-3xl">
                        {{ $person->full_name }}
                    </h1>

                    @if ($person->nickname)
                        <p class="mt-1 text-sm"
                           style="color: var(--family-text-secondary);">
                            {{ $person->nickname }}
                        </p>
                    @endif

                    <p class="mt-2 text-sm"
                       style="color: var(--family-text-secondary);">
                        {{ $group->name }}
                    </p>
                </div>


                <div class="flex flex-wrap gap-2">

                   
                    @if ($canUpdate)
                        <a href="{{ route('family.groups.people.edit', [
                            'groupId' => $group->id,
                            'personId' => $person->id,
                        ]) }}"
                           class="inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold text-white"
                           style="background: var(--family-accent, #4f46e5);">
                            {{ $isEnglish ? 'Edit Profile' : 'Edit Profil' }}
                        </a>
                    @endif
                </div>

            </div>
        </div>

        {{-- Profile --}}
        <section class="rounded-2xl border p-5 shadow-sm sm:p-6"
                 style="background: var(--family-surface); border-color: var(--family-border);">

            <h2 class="text-lg font-semibold">
                {{ $isEnglish ? 'Personal Information' : 'Informasi Individu' }}
            </h2>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">

                <div>
                    <p class="text-xs" style="color: var(--family-text-secondary);">
                        {{ $isEnglish ? 'Full Name' : 'Nama Lengkap' }}
                    </p>
                    <p class="mt-1 font-medium">{{ $person->full_name }}</p>
                </div>

                <div>
                    <p class="text-xs" style="color: var(--family-text-secondary);">
                        {{ $isEnglish ? 'Nickname' : 'Nama Panggilan' }}
                    </p>
                    <p class="mt-1 font-medium">{{ $person->nickname ?: '—' }}</p>
                </div>

                <div>
                    <p class="text-xs" style="color: var(--family-text-secondary);">
                        {{ $isEnglish ? 'Gender' : 'Jenis Kelamin' }}
                    </p>
                    <p class="mt-1 font-medium">
                        @if ($person->sex === 'male')
                            {{ $isEnglish ? 'Male' : 'Laki-laki' }}
                        @elseif ($person->sex === 'female')
                            {{ $isEnglish ? 'Female' : 'Perempuan' }}
                        @else
                            —
                        @endif
                    </p>
                </div>

                <div>
                    <p class="text-xs" style="color: var(--family-text-secondary);">
                        {{ $isEnglish ? 'Life Status' : 'Status Kehidupan' }}
                    </p>
                    <p class="mt-1 font-medium">
                        {{ $lifeStatusLabels[$person->life_status ?? 'unknown'] ?? '—' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs" style="color: var(--family-text-secondary);">
                        {{ $isEnglish ? 'Date of Birth' : 'Tanggal Lahir' }}
                    </p>
                    <p class="mt-1 font-medium">
                        {{ $person->birth_date?->format('d M Y') ?? '—' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs" style="color: var(--family-text-secondary);">
                        {{ $isEnglish ? 'Place of Birth' : 'Tempat Lahir' }}
                    </p>
                    <p class="mt-1 font-medium">{{ $person->birth_place ?: '—' }}</p>
                </div>

                <div>
                    <p class="text-xs" style="color: var(--family-text-secondary);">
                        {{ $isEnglish ? 'Date of Death' : 'Tanggal Wafat' }}
                    </p>
                    <p class="mt-1 font-medium">
                        {{ $person->death_date?->format('d M Y') ?? '—' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs" style="color: var(--family-text-secondary);">
                        {{ $isEnglish ? 'Place of Death' : 'Tempat Wafat' }}
                    </p>
                    <p class="mt-1 font-medium">{{ $person->death_place ?: '—' }}</p>
                </div>
            </div>

            @if ($person->biography)
                <div class="mt-6 border-t pt-5"
                     style="border-color: var(--family-border);">
                    <h3 class="font-semibold">
                        {{ $isEnglish ? 'Biography' : 'Biografi' }}
                    </h3>
                    <p class="mt-2 whitespace-pre-line text-sm"
                       style="color: var(--family-text-secondary);">{{ $person->biography }}</p>
                </div>
            @endif
        </section>

        
        {{-- Family Relationships --}}
        <section class="space-y-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold">
                        {{ $isEnglish ? 'Family Relationships' : 'Hubungan Keluarga' }}
                    </h2>
                    <p class="mt-1 text-sm"
                       style="color: var(--family-text-secondary);">
                        {{ $isEnglish
                            ? 'Family relationships recorded for this person.'
                            : 'Daftar hubungan kekerabatan yang tercatat untuk individu ini.' }}
                    </p>
                </div>

                @if ($canManageRelationships)
                    <a href="{{ route('family.groups.people.relationships.create', [
                        'groupId' => $group->id,
                        'personId' => $person->id,
                    ]) }}"
                       class="inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:opacity-90"
                       style="background: var(--family-accent, #4f46e5);">
                        + {{ $isEnglish ? 'Add Relationship' : 'Tambah Hubungan' }}
                    </a>
                @endif
            </div>


            @foreach ($relationshipGroups as $section)
                <div class="rounded-2xl border p-5 shadow-sm"
                     style="background: var(--family-surface); border-color: var(--family-border);">

                    <h3 class="font-semibold">{{ $section['title'] }}</h3>

                    @if (count($section['items']))
                        <div class="mt-4 space-y-3">
                            @foreach ($section['items'] as $item)
                                <div class="rounded-xl border p-4"
                                     style="background: var(--family-background); border-color: var(--family-border);">

                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="font-semibold">
                                        {{ $item['person']->full_name }}
                                    </p>

                                    <p class="mt-1 text-xs"
                                    style="color: var(--family-text-secondary);">
                                        {{ $item['direction'] }}
                                    </p>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($item['subtype'])
                                        <span class="rounded-full px-3 py-1 text-xs"
                                            style="background: var(--family-surface);">
                                            {{ $item['subtype'] }}
                                        </span>
                                    @endif

                                    @if ($item['parent_role'])
                                        <span class="rounded-full px-3 py-1 text-xs"
                                            style="background: var(--family-surface);">
                                            {{ $item['parent_role'] }}
                                        </span>
                                    @endif

                                    @if ($canManageRelationships)
                                        <a href="{{ route('family.groups.people.relationships.edit', [
                                            'groupId' => $group->id,
                                            'personId' => $person->id,
                                            'relationshipId' => $item['relationship']->id,
                                        ]) }}"
                                        class="inline-flex items-center justify-center rounded-xl border px-4 py-2 text-xs font-semibold transition hover:opacity-80"
                                        style="border-color: var(--family-border);
                                                color: var(--family-text-primary);">
                                            {{ $isEnglish ? 'Edit' : 'Edit Hubungan' }}
                                        </a>
                                    @endif
                                </div>
                            </div>

                                   @if ($item['started_at'] || $item['ended_at'])
                                        <p class="mt-3 text-xs"
                                        style="color: var(--family-text-secondary);">
                                            {{ $item['started_at']?->format('d M Y') ?? '—' }}
                                            –
                                            {{ $item['ended_at']?->format('d M Y') ?? ($isEnglish ? 'Present' : 'Sekarang') }}
                                        </p>
                                    @endif

                                    @if ($item['notes'])
                                        <p class="mt-2 text-sm"
                                           style="color: var(--family-text-secondary);">
                                            {{ $item['notes'] }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-3 text-sm"
                           style="color: var(--family-text-secondary);">
                            {{ $isEnglish ? 'No relationship recorded.' : 'Belum ada hubungan yang tercatat.' }}
                        </p>
                    @endif
                </div>
            @endforeach
        </section>

            {{-- Actions --}}
@if ($canManageRelationships)
    <section class="rounded-2xl border p-5 shadow-sm"
             style="
                background-color: var(--family-surface);
                border-color: var(--family-border);
                color: var(--family-text-primary);
             ">

        <h2 class="text-lg font-semibold">
            {{ $isEnglish ? 'Relationship Management' : 'Pengelolaan Hubungan' }}
        </h2>

        <p class="mt-2 text-sm"
           style="color: var(--family-text-secondary);">
            {{ $isEnglish
                ? 'You have permission to manage this person’s family relationships.'
                : 'Anda memiliki izin untuk mengelola hubungan keluarga individu ini.' }}
        </p>

        <div class="mt-4 rounded-xl border p-4"
             style="
                background-color: var(--family-background);
                border-color: var(--family-border);
             ">
            <p class="block text-sm font-semibold"
               style="color: var(--family-text-primary) !important;">
                {{ $isEnglish
                    ? 'Relationship editing and ending features will be available in a future update.'
                    : 'Fitur edit dan akhiri hubungan akan tersedia pada tahap berikutnya.' }}
            </p>
        </div>
    </section>
@endif

        <a href="{{ route('family.groups.people.index', $group->id) }}"
           class="inline-flex rounded-xl border px-5 py-3 text-sm font-semibold"
           style="border-color: var(--family-border);">
            ← {{ $isEnglish ? 'Back to People' : 'Kembali ke Data Individu' }}
        </a>

                  </div>
            </main>
        </div>
    </div>
</div>
@endsection