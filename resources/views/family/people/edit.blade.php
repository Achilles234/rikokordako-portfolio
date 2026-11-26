
@extends('layouts.family')

@section('content')
@php
    $isEnglish = session('family_locale', 'id') === 'en';
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

                <div class="mx-auto max-w-3xl space-y-6">

        <div>
            <a href="{{ route('family.groups.people.index', $group->id) }}"
               class="text-sm hover:underline"
               style="color: var(--family-text-secondary);">
                ← {{ $isEnglish ? 'Back to people' : 'Kembali ke Data Individu' }}
            </a>

            <h1 class="mt-3 text-2xl font-bold">
                {{ $isEnglish ? 'Edit Person' : 'Edit Data Individu' }}
            </h1>

            <p class="mt-1 text-sm" style="color: var(--family-text-secondary);">
                {{ $group->name }}
            </p>
        </div>

        <div class="rounded-xl border p-4 text-sm"
             style="background: var(--family-background); border-color: var(--family-border);">
            <strong>{{ $isEnglish ? 'Important information' : 'Informasi penting' }}</strong>
            <p class="mt-1" style="color: var(--family-text-secondary);">
                {{ $isEnglish
                    ? 'This is shared identity data. Changes will also appear in other family groups where this person is registered.'
                    : 'Data identitas ini digunakan bersama. Perubahan akan berlaku juga pada grup keluarga lain tempat individu ini terdaftar.' }}
            </p>
        </div>

       <form method="POST"
      enctype="multipart/form-data"
      action="{{ route('family.groups.people.update', [
          'groupId' => $group->id,
          'personId' => $person->id,
      ]) }}"
      class="space-y-6 rounded-2xl border p-5 shadow-sm sm:p-7"
      style="background: var(--family-surface); border-color: var(--family-border);">

            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="rounded-xl border border-red-300 bg-red-50 p-4 text-sm text-red-700">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <h2 class="font-semibold">
                    {{ $isEnglish ? 'Basic Information' : 'Informasi Dasar' }}
                </h2>
            </div>

                {{-- Profile Photo --}}
                <div class="border-t pt-5" style="border-color: var(--family-border);">
                    <h2 class="font-semibold">
                        {{ $isEnglish ? 'Profile Photo' : 'Foto Profil' }}
                    </h2>

                    <div class="mt-4 flex flex-col gap-5 sm:flex-row sm:items-center">

                        <div class="flex h-28 w-28 shrink-0 items-center justify-center overflow-hidden rounded-full border"
                            style="background: var(--family-background); border-color: var(--family-border);">

                            @if ($person->photo_path)
                                <img
                                    id="profile-photo-preview"
                                    src="{{ route('family.groups.people.photo', [
                                        'groupId' => $group->id,
                                        'personId' => $person->id,
                                    ]) }}"
                                    alt="{{ $person->full_name }}"
                                    class="h-full w-full object-cover"
                                >
                            @else
                                <div id="profile-photo-fallback"
                                    class="flex h-full w-full items-center justify-center text-3xl font-bold"
                                    style="color: var(--family-text-secondary);">
                                    {{ mb_strtoupper(mb_substr($person->full_name, 0, 1)) }}
                                </div>

                                <img
                                    id="profile-photo-preview"
                                    alt="{{ $person->full_name }}"
                                    class="hidden h-full w-full object-cover"
                                >
                            @endif
                        </div>

                        <div class="min-w-0 flex-1 space-y-3">
                            <div>
                                <label for="photo" class="text-sm font-medium">
                                    {{ $isEnglish ? 'Choose a new photo' : 'Pilih foto baru' }}
                                </label>

                                <input
                                    id="photo"
                                    name="photo"
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="mt-2 block w-full text-sm"
                                >

                                <p class="mt-1 text-xs" style="color: var(--family-text-secondary);">
                                    {{ $isEnglish
                                        ? 'JPG, PNG or WebP. Maximum 2 MB.'
                                        : 'Format JPG, PNG atau WebP. Maksimal 2 MB.' }}
                                </p>
                            </div>

                            @if ($person->photo_path)
                                <label class="flex items-center gap-2 text-sm">
                                    <input
                                        type="checkbox"
                                        name="remove_photo"
                                        value="1"
                                        class="rounded"
                                    >
                                    {{ $isEnglish ? 'Remove current photo' : 'Hapus foto saat ini' }}
                                </label>
                            @endif
                        </div>
                    </div>
                </div>
            <div class="space-y-2">
                <label for="full_name" class="text-sm font-medium">
                    {{ $isEnglish ? 'Full Name' : 'Nama Lengkap' }} *
                </label>
                <input id="full_name" name="full_name" type="text"
                       value="{{ old('full_name', $person->full_name) }}"
                       required maxlength="255"
                       class="w-full rounded-xl border px-4 py-3 text-sm"
                       style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">
            </div>

            <div class="space-y-2">
                <label for="nickname" class="text-sm font-medium">
                    {{ $isEnglish ? 'Nickname' : 'Nama Panggilan' }}
                </label>
                <input id="nickname" name="nickname" type="text"
                       value="{{ old('nickname', $person->nickname) }}"
                       maxlength="255"
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
                        <option value="male" @selected(old('sex', $person->sex) === 'male')>
                            {{ $isEnglish ? 'Male' : 'Laki-laki' }}
                        </option>
                        <option value="female" @selected(old('sex', $person->sex) === 'female')>
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
                        <option value="unknown" @selected(old('life_status', $person->life_status) === 'unknown')>
                            {{ $isEnglish ? 'Unknown' : 'Belum diketahui' }}
                        </option>
                        <option value="alive" @selected(old('life_status', $person->life_status) === 'alive')>
                            {{ $isEnglish ? 'Alive' : 'Masih hidup' }}
                        </option>
                        <option value="deceased" @selected(old('life_status', $person->life_status) === 'deceased')>
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
                           value="{{ old('birth_date', $person->birth_date?->format('Y-m-d')) }}"
                           class="w-full rounded-xl border px-4 py-3 text-sm"
                           style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">
                </div>

                <div class="space-y-2">
                    <label for="birth_place" class="text-sm font-medium">
                        {{ $isEnglish ? 'Place of Birth' : 'Tempat Lahir' }}
                    </label>
                    <input id="birth_place" name="birth_place" type="text"
                           value="{{ old('birth_place', $person->birth_place) }}"
                           maxlength="255"
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
                           value="{{ old('death_date', $person->death_date?->format('Y-m-d')) }}"
                           class="w-full rounded-xl border px-4 py-3 text-sm"
                           style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">
                </div>

                <div class="space-y-2">
                    <label for="death_place" class="text-sm font-medium">
                        {{ $isEnglish ? 'Place of Death' : 'Tempat Wafat' }}
                    </label>
                    <input id="death_place" name="death_place" type="text"
                           value="{{ old('death_place', $person->death_place) }}"
                           maxlength="255"
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
                          style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">{{ old('biography', $person->biography) }}</textarea>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('family.groups.people.index', $group->id) }}"
                   class="rounded-xl border px-5 py-3 text-center text-sm font-semibold"
                   style="border-color: var(--family-border);">
                    {{ $isEnglish ? 'Cancel' : 'Batal' }}
                </a>

                <button type="submit"
                        class="rounded-xl px-5 py-3 text-sm font-semibold text-white"
                        style="background: var(--family-accent, #4f46e5);">
                    {{ $isEnglish ? 'Save Changes' : 'Simpan Perubahan' }}
                </button>
            </div>
        </form>
                    </div>
            </main>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('photo');
    const preview = document.getElementById('profile-photo-preview');
    const fallback = document.getElementById('profile-photo-fallback');

    if (!input || !preview) return;

    input.addEventListener('change', function () {
        const file = this.files[0];

        if (!file) return;

        if (!file.type.startsWith('image/')) return;

        const reader = new FileReader();

        reader.onload = function (event) {
            preview.src = event.target.result;
            preview.classList.remove('hidden');

            if (fallback) {
                fallback.classList.add('hidden');
            }
        };

        reader.readAsDataURL(file);
    });
});
</script>
@endpush
@endsection