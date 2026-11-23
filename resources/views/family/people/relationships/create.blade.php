
@extends('layouts.family')

@section('title', 'Tambah Hubungan Keluarga')

@section('content')
<div class="min-h-screen" style="background: var(--family-background);">
    <div class="flex min-h-screen">
        @include('family.partials.sidebar', [
            'navigationGroup' => $group,
            'activeMenu' => 'people',
        ])

        <div class="min-w-0 flex-1">
            @include('family.partials.topbar', [
                'navigationGroup' => $group,
            ])

            <main id="family-main" class="px-4 py-6 sm:px-6 lg:px-8"
                  style="color: var(--family-text-primary);">
                <div class="mx-auto max-w-3xl space-y-6">

                    <div>
                        <a href="{{ route('family.groups.people.show', [
                            'groupId' => $group->id,
                            'personId' => $person->id,
                        ]) }}"
                           class="text-sm"
                           style="color: var(--family-text-secondary);">
                            ← Kembali ke detail individu
                        </a>

                        <h1 class="mt-3 text-2xl font-bold">
                            Tambah Hubungan Keluarga
                        </h1>

                        <p class="mt-1 text-sm"
                           style="color: var(--family-text-secondary);">
                            Tambahkan hubungan keluarga untuk
                            <strong>{{ $person->full_name }}</strong>.
                        </p>
                    </div>

                    <form method="POST"
                          action="{{ route('family.groups.people.relationships.store', [
                              'groupId' => $group->id,
                              'personId' => $person->id,
                          ]) }}"
                          class="space-y-6 rounded-2xl border p-5 sm:p-7"
                          style="background: var(--family-surface); border-color: var(--family-border);">

                        @csrf

                        <div>
                            <label for="relationship_type" class="mb-2 block text-sm font-medium">
                                Jenis Hubungan
                            </label>

                            <select id="relationship_type"
                                    name="relationship_type"
                                    required
                                    class="w-full rounded-xl border px-4 py-3"
                                    style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">
                                <option value="">Pilih hubungan</option>
                                <option value="parent" @selected(old('relationship_type') === 'parent')>Orang Tua</option>
                                <option value="child" @selected(old('relationship_type') === 'child')>Anak</option>
                                <option value="spouse" @selected(old('relationship_type') === 'spouse')>Pasangan</option>
                                <option value="guardian" @selected(old('relationship_type') === 'guardian')>Wali</option>
                            </select>
                            @error('relationship_type')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="related_person_id" class="mb-2 block text-sm font-medium">
                                Pilih Individu
                            </label>

                            <select id="related_person_id"
                                    name="related_person_id"
                                    required
                                    class="w-full rounded-xl border px-4 py-3"
                                    style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">
                                <option value="">Pilih individu</option>
                                @foreach($availablePeople as $availablePerson)
                                    <option value="{{ $availablePerson->id }}"
                                        @selected((string) old('related_person_id') === (string) $availablePerson->id)>
                                        {{ $availablePerson->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('related_person_id')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="parent-subtype-wrapper" class="hidden">
                            <label for="parent_subtype" class="mb-2 block text-sm font-medium">
                                Jenis Orang Tua
                            </label>

                            <select id="parent_subtype"
                                    name="relationship_subtype"
                                    class="w-full rounded-xl border px-4 py-3"
                                    style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">
                                <option value="">Pilih jenis</option>
                                <option value="biological" @selected(old('relationship_subtype') === 'biological')>Biologis</option>
                                <option value="adoptive" @selected(old('relationship_subtype') === 'adoptive')>Angkat</option>
                                <option value="step" @selected(old('relationship_subtype') === 'step')>Tiri</option>
                            </select>
                        </div>

                        <div id="parent-role-wrapper" class="hidden">
                            <label for="parent_role" class="mb-2 block text-sm font-medium">
                                Peran Orang Tua
                            </label>

                            <select id="parent_role"
                                    name="parent_role"
                                    class="w-full rounded-xl border px-4 py-3"
                                    style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">
                                <option value="unspecified" @selected(old('parent_role', 'unspecified') === 'unspecified')>Tidak ditentukan</option>
                                <option value="father" @selected(old('parent_role') === 'father')>Ayah</option>
                                <option value="mother" @selected(old('parent_role') === 'mother')>Ibu</option>
                            </select>
                        </div>

                        <div id="guardian-subtype-wrapper" class="hidden">
                            <label for="guardian_subtype" class="mb-2 block text-sm font-medium">
                                Jenis Wali
                            </label>

                            <select id="guardian_subtype"
                                    name="relationship_subtype"
                                    class="w-full rounded-xl border px-4 py-3"
                                    style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">
                                <option value="">Pilih jenis</option>
                                <option value="legal" @selected(old('relationship_subtype') === 'legal')>Wali Legal</option>
                                <option value="informal" @selected(old('relationship_subtype') === 'informal')>Wali Informal</option>
                            </select>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="started_at" class="mb-2 block text-sm font-medium">
                                    Mulai Berlaku
                                </label>
                                <input type="date"
                                       id="started_at"
                                       name="started_at"
                                       value="{{ old('started_at') }}"
                                       class="w-full rounded-xl border px-4 py-3"
                                       style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">
                            </div>

                            <div>
                                <label for="ended_at" class="mb-2 block text-sm font-medium">
                                    Berakhir
                                </label>
                                <input type="date"
                                       id="ended_at"
                                       name="ended_at"
                                       value="{{ old('ended_at') }}"
                                       class="w-full rounded-xl border px-4 py-3"
                                       style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">
                            </div>
                        </div>

                        <div>
                            <label for="notes" class="mb-2 block text-sm font-medium">
                                Catatan (Opsional)
                            </label>
                            <textarea id="notes"
                                      name="notes"
                                      rows="3"
                                      class="w-full rounded-xl border px-4 py-3"
                                      style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">{{ old('notes') }}</textarea>
                        </div>

                        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                            <a href="{{ route('family.groups.people.show', [
                                'groupId' => $group->id,
                                'personId' => $person->id,
                            ]) }}"
                               class="rounded-xl border px-5 py-3 text-center text-sm font-medium"
                               style="border-color: var(--family-border);">
                                Batal
                            </a>

                            <button type="submit"
                                    class="rounded-xl px-5 py-3 text-sm font-semibold text-white"
                                    style="background: var(--family-primary);">
                                Simpan Hubungan
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</div>

<script>

document.addEventListener('DOMContentLoaded', function () {
    const type = document.getElementById('relationship_type');

    const parentSubtype = document.getElementById('parent-subtype-wrapper');
    const parentRole = document.getElementById('parent-role-wrapper');
    const guardianSubtype = document.getElementById('guardian-subtype-wrapper');

    const parentSelect = document.getElementById('parent_subtype');
    const parentRoleSelect = document.getElementById('parent_role');
    const guardianSelect = document.getElementById('guardian_subtype');

    function updateRelationshipFields() {
        const value = type.value;
        const isParent = ['parent', 'child'].includes(value);
        const isGuardian = value === 'guardian';

        parentSubtype.classList.toggle('hidden', !isParent);
        parentRole.classList.toggle('hidden', !isParent);
        guardianSubtype.classList.toggle('hidden', !isGuardian);

        parentSelect.disabled = !isParent;
        parentRoleSelect.disabled = !isParent;
        guardianSelect.disabled = !isGuardian;
    }

    type.addEventListener('change', updateRelationshipFields);
    updateRelationshipFields();
});

</script>
@endsection
