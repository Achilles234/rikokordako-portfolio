
@extends('layouts.family')

@section('content')
@php
    $isEnglish = session('family_locale', 'id') === 'en';
    $displayName = auth('family')->user()?->name ?? 'Family Member';
    $isSuperAdmin = app(\App\Services\Family\FamilyAccessService::class)
        ->isSuperAdmin(auth('family')->user());
@endphp

<div class="h-screen overflow-hidden" style="background: var(--family-background); color: var(--family-text-primary);">
    <div class="flex h-full min-h-0">

        {{-- Desktop Sidebar --}}
        @include('family.partials.sidebar', [
            'navigationGroup' => $group,
            'displayName' => $displayName,
            'isSuperAdmin' => $isSuperAdmin,
            'activeMenu' => 'people',
        ])

        {{-- Main Content --}}
        <div class="flex min-w-0 min-h-0 flex-1 flex-col overflow-hidden">

            {{-- Topbar --}}
            @include('family.partials.topbar', [
                'navigationGroup' => $group,
            ])

         <main class="min-h-0 flex-1 overflow-y-auto overflow-x-hidden px-4 py-6 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-3xl space-y-6">

                    <div>
                        <a href="{{ route('family.groups.people.index', $group->id) }}"
                           class="text-sm hover:underline"
                           style="color: var(--family-text-secondary);">
                            ← {{ $isEnglish ? 'Back to people' : 'Kembali ke Data Individu' }}
                        </a>

                        <h1 class="mt-3 text-2xl font-bold">
                            {{ $isEnglish ? 'Add New Person' : 'Tambah Individu Baru' }}
                        </h1>

                        <p class="mt-1 text-sm" style="color: var(--family-text-secondary);">
                            {{ $group->name }}
                        </p>
                    </div>

                    <form method="POST"
                          action="{{ route('family.groups.people.store', $group->id) }}"
                          class="space-y-6 rounded-2xl border p-5 shadow-sm sm:p-7"
                          style="background: var(--family-surface); border-color: var(--family-border);">

                        @csrf

                        <div>
                            <h2 class="font-semibold">
                                {{ $isEnglish ? 'Basic Information' : 'Informasi Dasar' }}
                            </h2>
                            <p class="mt-1 text-sm" style="color: var(--family-text-secondary);">
                                {{ $isEnglish
                                    ? "Enter the person's identity information."
                                    : 'Masukkan informasi identitas individu.' }}
                            </p>
                        </div>

                        @if ($errors->any())
                            <div class="rounded-xl border border-red-300 bg-red-50 p-4 text-sm text-red-700">
                                <ul class="list-inside list-disc space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="space-y-2">
                            <label for="full_name" class="text-sm font-medium">
                                {{ $isEnglish ? 'Full Name' : 'Nama Lengkap' }} *
                            </label>
                            <input id="full_name" name="full_name" type="text"
                                   value="{{ old('full_name') }}" required maxlength="255"
                                   class="w-full rounded-xl border px-4 py-3 text-sm"
                                   style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">
                        </div>

                        <div class="space-y-2">
                            <label for="nickname" class="text-sm font-medium">
                                {{ $isEnglish ? 'Nickname' : 'Nama Panggilan' }}
                            </label>
                            <input id="nickname" name="nickname" type="text"
                                   value="{{ old('nickname') }}" maxlength="255"
                                   class="w-full rounded-xl border px-4 py-3 text-sm"
                                   style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="space-y-2">
                                <label for="sex" class="text-sm font-medium">
                                    {{ $isEnglish ? 'Gender' : 'Jenis Kelamin' }}
                                </label>
                                <select id="sex" name="sex"
                                        class="w-full rounded-xl border px-4 py-3 text-sm"
                                        style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">
                                    <option value="">{{ $isEnglish ? 'Not specified' : 'Tidak ditentukan' }}</option>
                                    <option value="male" @selected(old('sex') === 'male')>
                                        {{ $isEnglish ? 'Male' : 'Laki-laki' }}
                                    </option>
                                    <option value="female" @selected(old('sex') === 'female')>
                                        {{ $isEnglish ? 'Female' : 'Perempuan' }}
                                    </option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label for="life_status" class="text-sm font-medium">
                                    {{ $isEnglish ? 'Life Status' : 'Status Kehidupan' }}
                                </label>
                                <select id="life_status" name="life_status"
                                        class="w-full rounded-xl border px-4 py-3 text-sm"
                                        style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">
                                    <option value="unknown" @selected(old('life_status', 'unknown') === 'unknown')>
                                        {{ $isEnglish ? 'Unknown' : 'Belum diketahui' }}
                                    </option>
                                    <option value="alive" @selected(old('life_status') === 'alive')>
                                        {{ $isEnglish ? 'Alive' : 'Masih hidup' }}
                                    </option>
                                    <option value="deceased" @selected(old('life_status') === 'deceased')>
                                        {{ $isEnglish ? 'Deceased' : 'Sudah meninggal' }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="border-t pt-5" style="border-color: var(--family-border);">
                            <h2 class="font-semibold">
                                {{ $isEnglish ? 'Birth Information' : 'Informasi Kelahiran' }}
                            </h2>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="space-y-2">
                                <label for="birth_date" class="text-sm font-medium">
                                    {{ $isEnglish ? 'Date of Birth' : 'Tanggal Lahir' }}
                                </label>
                                <input id="birth_date" name="birth_date" type="date"
                                       value="{{ old('birth_date') }}"
                                       class="w-full rounded-xl border px-4 py-3 text-sm"
                                       style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">
                            </div>

                            <div class="space-y-2">
                                <label for="birth_place" class="text-sm font-medium">
                                    {{ $isEnglish ? 'Place of Birth' : 'Tempat Lahir' }}
                                </label>
                                <input id="birth_place" name="birth_place" type="text"
                                       value="{{ old('birth_place') }}" maxlength="255"
                                       class="w-full rounded-xl border px-4 py-3 text-sm"
                                       style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">
                            </div>
                        </div>

                        <div class="border-t pt-5" style="border-color: var(--family-border);">
                            <h2 class="font-semibold">
                                {{ $isEnglish ? 'Death Information' : 'Informasi Wafat' }}
                            </h2>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="space-y-2">
                                <label for="death_date" class="text-sm font-medium">
                                    {{ $isEnglish ? 'Date of Death' : 'Tanggal Wafat' }}
                                </label>
                                <input id="death_date" name="death_date" type="date"
                                       value="{{ old('death_date') }}"
                                       class="w-full rounded-xl border px-4 py-3 text-sm"
                                       style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">
                            </div>

                            <div class="space-y-2">
                                <label for="death_place" class="text-sm font-medium">
                                    {{ $isEnglish ? 'Place of Death' : 'Tempat Wafat' }}
                                </label>
                                <input id="death_place" name="death_place" type="text"
                                       value="{{ old('death_place') }}" maxlength="255"
                                       class="w-full rounded-xl border px-4 py-3 text-sm"
                                       style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">
                            </div>
                        </div>

                        <div class="border-t pt-5" style="border-color: var(--family-border);">
                            <h2 class="font-semibold">
                                {{ $isEnglish ? 'Biography' : 'Biografi' }}
                            </h2>
                            <textarea id="biography" name="biography" rows="4"
                                      class="mt-2 w-full rounded-xl border px-4 py-3 text-sm"
                                      style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">{{ old('biography') }}</textarea>
                        </div>

                        <div class="rounded-xl border p-4 text-sm"
                             style="background: var(--family-background); border-color: var(--family-border);">
                            {{ $isEnglish
                                ? 'The new person will be registered as a pending member of this group. No login account will be created.'
                                : 'Individu baru akan didaftarkan sebagai anggota berstatus Menunggu pada grup ini. Akun login tidak dibuat otomatis.' }}
                        </div>

                        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                            <a href="{{ route('family.groups.people.index', $group->id) }}"
                               class="rounded-xl border px-5 py-3 text-center text-sm font-semibold"
                               style="border-color: var(--family-border);">
                                {{ $isEnglish ? 'Cancel' : 'Batal' }}
                            </a>

                            <button type="submit"
                                    class="rounded-xl px-5 py-3 text-sm font-semibold"
                                    style="background: var(--family-accent, #4f46e5); color: white;">
                                {{ $isEnglish ? 'Save Person' : 'Simpan Individu' }}
                            </button>
                        </div>
                    </form>

                </div>
            </main>
        </div>
    </div>
</div>
@endsection
