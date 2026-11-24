
@extends('layouts.family')

@section('title', 'Edit Hubungan Keluarga')

@section('content')
<div class="h-screen overflow-hidden"
     style="background: var(--family-background); color: var(--family-text-primary);">

    <div class="flex h-full min-h-0">

        @include('family.partials.sidebar', [
            'navigationGroup' => $group,
            'activeMenu' => 'people',
        ])

        <div class="flex min-w-0 min-h-0 flex-1 flex-col overflow-hidden">

            @include('family.partials.topbar', [
                'navigationGroup' => $group,
            ])

            <main id="family-main"
                  class="min-h-0 flex-1 overflow-y-auto overflow-x-hidden px-4 py-6 sm:px-6 lg:px-8">

                <div class="mx-auto max-w-3xl">

                    {{-- Header --}}
                    <div class="mb-6">
                        <a href="{{ route('family.groups.people.show', [
                            'groupId' => $group->id,
                            'personId' => $person->id,
                        ]) }}"
                           class="mb-4 inline-flex items-center gap-2 text-sm font-medium"
                           style="color: var(--family-text-muted);">
                            <span aria-hidden="true">←</span>
                            Kembali ke Profil
                        </a>

                        <h1 class="text-2xl font-bold sm:text-3xl">
                            Edit Hubungan Keluarga
                        </h1>

                        <p class="mt-2 text-sm"
                           style="color: var(--family-text-muted);">
                            Perbarui informasi hubungan keluarga antara
                            <strong>{{ $person->full_name }}</strong>
                            dan individu terkait.
                        </p>
                    </div>

                    {{-- Form --}}
                    <div class="rounded-2xl border p-5 shadow-sm sm:p-7"
                         style="background: var(--family-surface);
                                border-color: var(--family-border);">

                        <form method="POST"
                              action="{{ route('family.groups.people.relationships.update', [
                                  'groupId' => $group->id,
                                  'personId' => $person->id,
                                  'relationshipId' => $relationship->id,
                              ]) }}"
                              class="space-y-6">

                            @csrf
                            @method('PUT')

                            {{-- Relationship type --}}
                            <div>
                                <label for="relationship_type"
                                       class="mb-2 block text-sm font-semibold">
                                    Jenis Hubungan
                                </label>

                                <select id="relationship_type"
                                        name="relationship_type"
                                        required
                                        class="w-full rounded-xl border px-4 py-3 text-sm"
                                        style="background: var(--family-background);
                                               color: var(--family-text-primary);
                                               border-color: var(--family-border);">

                                    <option value="parent"
                                        @selected(old('relationship_type', $formType) === 'parent')>
                                        Orang Tua
                                    </option>

                                    <option value="child"
                                        @selected(old('relationship_type', $formType) === 'child')>
                                        Anak
                                    </option>

                                    <option value="spouse"
                                        @selected(old('relationship_type', $formType) === 'spouse')>
                                        Pasangan
                                    </option>

                                    <option value="guardian"
                                        @selected(old('relationship_type', $formType) === 'guardian')>
                                        Wali / Perwalian
                                    </option>
                                </select>
                            </div>

                            {{-- Related person --}}
                            <div>
                                <label for="related_person_id"
                                       id="related_person_label"
                                       class="mb-2 block text-sm font-semibold">
                                    Individu Terkait
                                </label>

                                <select id="related_person_id"
                                        name="related_person_id"
                                        required
                                        class="w-full rounded-xl border px-4 py-3 text-sm"
                                        style="background: var(--family-background);
                                               color: var(--family-text-primary);
                                               border-color: var(--family-border);">

                                    <option value="">Pilih individu</option>

                                    @foreach($availablePeople as $availablePerson)
                                        <option value="{{ $availablePerson->id }}"
                                            @selected((string) old('related_person_id', $relatedPersonId) === (string) $availablePerson->id)>
                                            {{ $availablePerson->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Parent subtype --}}
                            <div id="parent_subtype_wrapper">
                                <label for="parent_subtype"
                                       class="mb-2 block text-sm font-semibold">
                                    Jenis Hubungan Orang Tua
                                </label>

                                <select id="parent_subtype"
                                        name="relationship_subtype"
                                        class="w-full rounded-xl border px-4 py-3 text-sm"
                                        style="background: var(--family-background);
                                               color: var(--family-text-primary);
                                               border-color: var(--family-border);">

                                    <option value="biological"
                                        @selected(old('relationship_subtype', $relationship->relationship_subtype) === 'biological')>
                                        Biologis
                                    </option>

                                    <option value="adoptive"
                                        @selected(old('relationship_subtype', $relationship->relationship_subtype) === 'adoptive')>
                                        Angkat
                                    </option>

                                    <option value="step"
                                        @selected(old('relationship_subtype', $relationship->relationship_subtype) === 'step')>
                                        Tiri
                                    </option>
                                </select>
                            </div>

                            {{-- Parent role --}}
                            <div id="parent_role_wrapper">
                                <label for="parent_role"
                                       class="mb-2 block text-sm font-semibold">
                                    Peran Orang Tua
                                </label>

                                <select id="parent_role"
                                        name="parent_role"
                                        class="w-full rounded-xl border px-4 py-3 text-sm"
                                        style="background: var(--family-background);
                                               color: var(--family-text-primary);
                                               border-color: var(--family-border);">

                                    <option value="unspecified"
                                        @selected(old('parent_role', $relationship->parent_role ?? 'unspecified') === 'unspecified')>
                                        Tidak ditentukan
                                    </option>

                                    <option value="father"
                                        @selected(old('parent_role', $relationship->parent_role) === 'father')>
                                        Ayah
                                    </option>

                                    <option value="mother"
                                        @selected(old('parent_role', $relationship->parent_role) === 'mother')>
                                        Ibu
                                    </option>
                                </select>
                            </div>

                            {{-- Guardian subtype --}}
                            <div id="guardian_subtype_wrapper">
                                <label for="guardian_subtype"
                                       class="mb-2 block text-sm font-semibold">
                                    Jenis Perwalian
                                </label>

                                <select id="guardian_subtype"
                                        name="relationship_subtype"
                                        class="w-full rounded-xl border px-4 py-3 text-sm"
                                        style="background: var(--family-background);
                                               color: var(--family-text-primary);
                                               border-color: var(--family-border);">

                                    <option value="legal"
                                        @selected(old('relationship_subtype', $relationship->relationship_subtype) === 'legal')>
                                        Wali Hukum
                                    </option>

                                    <option value="informal"
                                        @selected(old('relationship_subtype', $relationship->relationship_subtype) === 'informal')>
                                        Wali Informal
                                    </option>
                                </select>
                            </div>

                            {{-- Dates --}}
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                                <div>
                                    <label for="started_at"
                                           class="mb-2 block text-sm font-semibold">
                                        Tanggal Mulai
                                    </label>

                                    <input type="date"
                                           id="started_at"
                                           name="started_at"
                                           value="{{ old('started_at', $relationship->started_at?->format('Y-m-d')) }}"
                                           class="w-full rounded-xl border px-4 py-3 text-sm"
                                           style="background: var(--family-background);
                                                  color: var(--family-text-primary);
                                                  border-color: var(--family-border);">
                                </div>

                                <div>
                                    <label for="ended_at"
                                           class="mb-2 block text-sm font-semibold">
                                        Tanggal Berakhir
                                    </label>

                                    <input type="date"
                                           id="ended_at"
                                           name="ended_at"
                                           value="{{ old('ended_at', $relationship->ended_at?->format('Y-m-d')) }}"
                                           class="w-full rounded-xl border px-4 py-3 text-sm"
                                           style="background: var(--family-background);
                                                  color: var(--family-text-primary);
                                                  border-color: var(--family-border);">
                                </div>

                            </div>

                            {{-- Notes --}}
                            <div>
                                <label for="notes"
                                       class="mb-2 block text-sm font-semibold">
                                    Catatan
                                </label>

                                <textarea id="notes"
                                          name="notes"
                                          rows="4"
                                          maxlength="2000"
                                          class="w-full rounded-xl border px-4 py-3 text-sm"
                                          style="background: var(--family-background);
                                                 color: var(--family-text-primary);
                                                 border-color: var(--family-border);"
                                          placeholder="Catatan tambahan mengenai hubungan ini...">{{ old('notes', $relationship->notes) }}</textarea>
                            </div>

                            {{-- Actions --}}
                            <div class="flex flex-col-reverse gap-3 border-t pt-5 sm:flex-row sm:justify-end"
                                 style="border-color: var(--family-border);">

                                <a href="{{ route('family.groups.people.show', [
                                    'groupId' => $group->id,
                                    'personId' => $person->id,
                                ]) }}"
                                   class="inline-flex items-center justify-center rounded-xl border px-5 py-3 text-sm font-semibold transition"
                                   style="border-color: var(--family-border);
                                          color: var(--family-text-primary);">
                                    Batal
                                </a>

                                <button type="submit"
                                        class="inline-flex items-center justify-center rounded-xl px-6 py-3 text-sm font-semibold shadow-sm transition"
                                        style="background: var(--family-accent);
                                               color: #fff;">
                                    Simpan Perubahan
                                </button>

                            </div>

                        </form>
                    </div>

                </div>
            </main>
        </div>
    </div>
</div>

{{-- Dynamic form fields --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('relationship_type');

    const relatedLabel = document.getElementById('related_person_label');

    const parentSubtypeWrapper = document.getElementById('parent_subtype_wrapper');
    const parentRoleWrapper = document.getElementById('parent_role_wrapper');
    const guardianSubtypeWrapper = document.getElementById('guardian_subtype_wrapper');

    const parentSubtype = document.getElementById('parent_subtype');
    const parentRole = document.getElementById('parent_role');
    const guardianSubtype = document.getElementById('guardian_subtype');

    function updateFields() {
        const type = typeSelect.value;

        const isParent = type === 'parent' || type === 'child';
        const isGuardian = type === 'guardian';

        parentSubtypeWrapper.classList.toggle('hidden', !isParent);
        parentRoleWrapper.classList.toggle('hidden', !isParent);
        guardianSubtypeWrapper.classList.toggle('hidden', !isGuardian);

        // Hindari dua select dengan nama yang sama terkirim bersamaan.
        parentSubtype.disabled = !isParent;
        parentRole.disabled = !isParent;
        guardianSubtype.disabled = !isGuardian;

        if (type === 'parent') {
            relatedLabel.textContent = 'Pilih Orang Tua';
        } else if (type === 'child') {
            relatedLabel.textContent = 'Pilih Anak';
        } else if (type === 'spouse') {
            relatedLabel.textContent = 'Pilih Pasangan';
        } else if (type === 'guardian') {
            relatedLabel.textContent = @json(
                $guardianIsOutgoing
                    ? 'Pilih Individu dalam Perwalian'
                    : 'Pilih Wali'
            );
        }
    }

    typeSelect.addEventListener('change', updateFields);

    updateFields();
});
</script>
@endsection
