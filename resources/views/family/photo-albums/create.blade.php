
@extends('layouts.family')

@section('title', 'Buat Album Kenangan')

@section('content')
<div class="family-app-layout flex h-screen overflow-hidden">
    {{-- SIDEBAR --}}
    @include('family.partials.sidebar')

    <div class="flex min-h-0 min-w-0 flex-1 flex-col">
        {{-- TOPBAR --}}
        @include('family.partials.topbar')

        <main id="family-main"
            class="family-main-content min-h-0 min-w-0 flex-1 overflow-y-auto px-5 py-7 lg:px-10">

            <div class="mx-auto max-w-6xl space-y-8">

                {{-- BREADCRUMB --}}
                <div class="flex flex-wrap items-center gap-2 text-sm text-stone-500 dark:text-amber-200/60">
                    <a href="{{ route('family.photo-albums.index') }}"
                        class="transition hover:text-amber-700 dark:hover:text-amber-300">
                        Family Memories
                    </a>
                    <span>/</span>
                    <span class="font-semibold text-stone-800 dark:text-amber-100">
                        Buat Album
                    </span>
                </div>

                {{-- HERO --}}
                <section class="relative overflow-hidden rounded-3xl border border-amber-900/20 bg-gradient-to-br from-[#302318] via-[#493522] to-[#21180f] p-7 text-amber-50 shadow-2xl lg:p-10">

                    <div class="pointer-events-none absolute -right-16 -top-20 h-72 w-72 rounded-full bg-amber-500/10 blur-3xl"></div>

                    <div class="relative flex items-center gap-5">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl border border-amber-200/20 bg-amber-100/10 text-3xl shadow-inner">
                            ✧
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.25em] text-amber-300">
                                Preserve Your Memories
                            </p>

                            <h1 class="mt-2 text-3xl font-bold tracking-tight lg:text-4xl">
                                Ciptakan Album Kenangan
                            </h1>

                            <p class="mt-3 max-w-2xl text-sm leading-7 text-amber-100/70">
                                Setiap keluarga memiliki cerita. Mulailah dengan
                                membuat ruang khusus untuk menyimpan momen
                                berharga bersama orang-orang tercinta.
                            </p>
                        </div>
                    </div>
                </section>

                {{-- VALIDATION ERRORS --}}
                @if($errors->any())
                    <div class="rounded-2xl border border-red-500/30 bg-red-500/10 p-5 text-sm text-red-700 dark:text-red-300">
                        <p class="mb-2 font-bold">Ada beberapa hal yang perlu diperbaiki:</p>

                        <ul class="list-inside list-disc space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- FORM + PREVIEW --}}
                <div class="grid grid-cols-1 gap-7 xl:grid-cols-[1.4fr_0.8fr]">

                    {{-- FORM --}}
                    <section class="rounded-3xl border border-amber-900/10 bg-white/80 p-6 shadow-xl backdrop-blur dark:border-amber-100/10 dark:bg-[#241a11]/90 lg:p-8">

                        <div class="mb-7">
                            <p class="text-xs font-bold uppercase tracking-[0.22em] text-amber-700 dark:text-amber-400">
                                Album Information
                            </p>

                            <h2 class="mt-2 text-2xl font-bold text-stone-800 dark:text-amber-50">
                                Informasi Album
                            </h2>

                            <p class="mt-2 text-sm text-stone-500 dark:text-amber-100/60">
                                Lengkapi informasi dasar album keluarga.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('family.photo-albums.store') }}"
                            class="space-y-6">
                            @csrf

                            {{-- GROUP --}}
                            <div>
                                <label for="family_group_id"
                                    class="mb-2 block text-sm font-semibold text-stone-700 dark:text-amber-100">
                                    Grup Keluarga
                                    <span class="text-red-500">*</span>
                                </label>

                                <select id="family_group_id" name="family_group_id" required
                                    class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3.5 text-sm text-stone-800 outline-none transition focus:border-amber-600 focus:ring-2 focus:ring-amber-600/20 dark:border-amber-900 dark:bg-[#302318] dark:text-amber-50">
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}"
                                            @selected((string) old('family_group_id', $selectedGroupId) === (string) $group->id)>
                                            {{ $group->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('family_group_id')
                                    <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                                @enderror

                                <p class="mt-2 text-xs text-stone-500 dark:text-amber-200/50">
                                    Album akan menjadi milik grup keluarga yang dipilih.
                                </p>
                            </div>

                            {{-- ALBUM NAME --}}
                            <div>
                                <label for="name"
                                    class="mb-2 block text-sm font-semibold text-stone-700 dark:text-amber-100">
                                    Nama Album
                                    <span class="text-red-500">*</span>
                                </label>

                                <input type="text" id="name" name="name"
                                    value="{{ old('name') }}"
                                    maxlength="150"
                                    required
                                    placeholder="Contoh: Liburan Keluarga 2026"
                                    class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3.5 text-sm text-stone-800 outline-none transition placeholder:text-stone-400 focus:border-amber-600 focus:ring-2 focus:ring-amber-600/20 dark:border-amber-900 dark:bg-[#302318] dark:text-amber-50 dark:placeholder:text-amber-100/30">

                                @error('name')
                                    <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- EVENT DATE --}}
                            <div>
                                <label for="event_date"
                                    class="mb-2 block text-sm font-semibold text-stone-700 dark:text-amber-100">
                                    Tanggal Kenangan
                                </label>

                                <input type="date" id="event_date" name="event_date"
                                    value="{{ old('event_date') }}"
                                    class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3.5 text-sm text-stone-800 outline-none transition focus:border-amber-600 focus:ring-2 focus:ring-amber-600/20 dark:border-amber-900 dark:bg-[#302318] dark:text-amber-50">

                                @error('event_date')
                                    <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                                @enderror

                                <p class="mt-2 text-xs text-stone-500 dark:text-amber-200/50">
                                    Bisa dikosongkan jika album tidak terkait tanggal tertentu.
                                </p>
                            </div>

                            {{-- DESCRIPTION --}}
                            <div>
                                <label for="description"
                                    class="mb-2 block text-sm font-semibold text-stone-700 dark:text-amber-100">
                                    Cerita di Balik Album
                                </label>

                                <textarea id="description" name="description"
                                    rows="5"
                                    maxlength="5000"
                                    placeholder="Tuliskan cerita atau kenangan di balik album ini..."
                                    class="w-full resize-y rounded-xl border border-stone-300 bg-white px-4 py-3.5 text-sm leading-7 text-stone-800 outline-none transition placeholder:text-stone-400 focus:border-amber-600 focus:ring-2 focus:ring-amber-600/20 dark:border-amber-900 dark:bg-[#302318] dark:text-amber-50 dark:placeholder:text-amber-100/30">{{ old('description') }}</textarea>

                                @error('description')
                                    <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- ACTIONS --}}
                            <div class="flex flex-wrap items-center justify-between gap-4 border-t border-stone-200 pt-6 dark:border-amber-100/10">

                                <a href="{{ route('family.photo-albums.index') }}"
                                    class="inline-flex items-center gap-2 rounded-xl border border-stone-300 px-5 py-3 text-sm font-semibold text-stone-700 transition hover:bg-stone-100 dark:border-amber-900 dark:text-amber-100 dark:hover:bg-amber-950">
                                    <span>←</span>
                                    Kembali
                                </a>

                                <button type="submit"
                                    class="family-leaf-button inline-flex min-h-12 items-center justify-center gap-2 px-7 py-3 font-semibold text-white shadow-lg">
                                    <span>✧</span>
                                    Simpan Album
                                </button>
                            </div>
                        </form>
                    </section>

                    {{-- LIVE PREVIEW --}}
                    <aside class="space-y-5">

                        <section class="overflow-hidden rounded-3xl border border-amber-900/10 bg-white/80 shadow-xl dark:border-amber-100/10 dark:bg-[#241a11]/90">

                            <div class="border-b border-stone-200 p-6 dark:border-amber-100/10">
                                <p class="text-xs font-bold uppercase tracking-[0.22em] text-amber-700 dark:text-amber-400">
                                    Preview
                                </p>

                                <h2 class="mt-2 text-xl font-bold text-stone-800 dark:text-amber-50">
                                    Tampilan Album
                                </h2>
                            </div>

                            <div class="p-5">
                                <div class="overflow-hidden rounded-2xl shadow-lg">
                                    <div class="flex aspect-[16/10] items-center justify-center bg-gradient-to-br from-[#765638] via-[#493522] to-[#21180f] text-center text-amber-100">
                                        <div>
                                            <div class="text-6xl">✧</div>
                                            <p class="mt-3 text-xs font-semibold uppercase tracking-[0.25em]">
                                                Family Memories
                                            </p>
                                        </div>
                                    </div>

                                    <div class="bg-stone-100 p-5 dark:bg-[#302318]">
                                        <h3 id="preview-name"
                                            class="break-words text-lg font-bold text-stone-800 dark:text-amber-50">
                                            {{ old('name') ?: 'Nama Album Kenangan' }}
                                        </h3>

                                        <p id="preview-date"
                                            class="mt-2 text-xs text-stone-500 dark:text-amber-200/60">
                                            {{ old('event_date') ?: 'Tanggal belum ditentukan' }}
                                        </p>

                                        <p id="preview-description"
                                            class="mt-3 break-words text-sm leading-6 text-stone-600 dark:text-amber-100/70">
                                            {{ old('description') ?: 'Cerita dan kenangan keluarga akan tampil di sini.' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-amber-900/10 bg-amber-50/80 p-5 dark:border-amber-100/10 dark:bg-[#302318]/70">
                            <div class="flex gap-3">
                                <span class="text-2xl">💡</span>

                                <div>
                                    <h3 class="font-bold text-stone-800 dark:text-amber-100">
                                        Tips Membuat Album
                                    </h3>

                                    <p class="mt-2 text-sm leading-6 text-stone-600 dark:text-amber-100/60">
                                        Gunakan nama yang mudah dikenali,
                                        misalnya Liburan Keluarga, Ulang Tahun,
                                        Pernikahan, atau Masa Kecil.
                                    </p>
                                </div>
                            </div>
                        </section>

                    </aside>
                </div>

            </div>
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const nameInput = document.getElementById('name');
    const dateInput = document.getElementById('event_date');
    const descriptionInput = document.getElementById('description');

    const previewName = document.getElementById('preview-name');
    const previewDate = document.getElementById('preview-date');
    const previewDescription = document.getElementById('preview-description');

    if (!nameInput || !dateInput || !descriptionInput) {
        return;
    }

    nameInput.addEventListener('input', function () {
        previewName.textContent = this.value.trim() || 'Nama Album Kenangan';
    });

    dateInput.addEventListener('input', function () {
        if (!this.value) {
            previewDate.textContent = 'Tanggal belum ditentukan';
            return;
        }

        const parts = this.value.split('-');
        previewDate.textContent = `${parts[2]}-${parts[1]}-${parts[0]}`;
    });

    descriptionInput.addEventListener('input', function () {
        previewDescription.textContent =
            this.value.trim() || 'Cerita dan kenangan keluarga akan tampil di sini.';
    });
});
</script>
@endpush
