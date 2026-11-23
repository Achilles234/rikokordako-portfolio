@extends('layouts.family')

@section('title', $album->name . ' - Family Memories')

@section('content')

@php
    $familyUser = auth('family')->user();

    $canUpload = $familyUser
        ? app(\App\Services\Family\FamilyAccessService::class)->hasPermission(
            $familyUser,
            'photo_albums.upload',
            $album->family_group_id
        )
        : false;

    $canDelete = $familyUser
        ? app(\App\Services\Family\FamilyAccessService::class)->hasPermission(
            $familyUser,
            'photo_albums.photos.delete',
            $album->family_group_id
        )
        : false;

    $canSetCover = $familyUser
        ? app(\App\Services\Family\FamilyAccessService::class)->hasPermission(
            $familyUser,
            'photo_albums.update',
            $album->family_group_id
        )
        : false;
    $canEditPhoto = $canSetCover;
    $familyMemberId = $familyUser
    ? \Illuminate\Support\Facades\DB::table('family_members')
        ->join('family_persons', 'family_persons.id', '=', 'family_members.family_person_id')
        ->where('family_members.family_group_id', $album->family_group_id)
        ->where('family_persons.user_id', $familyUser->id)
        ->where('family_members.status', 'active')
        ->whereNull('family_members.left_at')
        ->value('family_members.id')
    : null;


$isSuperAdmin = $familyUser
    ? app(\App\Services\Family\FamilyAccessService::class)->isSuperAdmin($familyUser)
    : false;

$canFavorite = $familyUser
    && ($familyMemberId || $isSuperAdmin)
    && app(\App\Services\Family\FamilyAccessService::class)->hasPermission(
        $familyUser,
        'photo_albums.favorite',
        $album->family_group_id
    );

$favoritePhotoIds = [];

if ($canFavorite) {
    $favoriteQuery = \App\Models\Family\FamilyPhotoFavorite::query()
        ->whereIn('family_photo_id', $photos->pluck('id'));

    if ($familyMemberId) {
        $favoriteQuery->where('family_member_id', $familyMemberId);
    } else {
        $favoriteQuery->where('user_id', $familyUser->id);
    }

    $favoritePhotoIds = $favoriteQuery
        ->pluck('family_photo_id')
        ->all();
}

    $isCurrentCover = fn ($photo) => $album->cover_photo_id == $photo->id;

@endphp

<div class="family-app-layout flex h-screen overflow-hidden">
    @include('family.partials.sidebar')

    <div class="flex min-h-0 min-w-0 flex-1 flex-col">
        @include('family.partials.topbar', [
            'navigationGroup' => $album->familyGroup,
        ])

        <main id="family-main"
            class="family-main-content min-h-0 min-w-0 flex-1 overflow-y-auto px-5 py-7 lg:px-10">

            <div class="mx-auto max-w-7xl space-y-8">

                {{-- Breadcrumb --}}
                <div class="flex flex-wrap items-center gap-2 text-sm"
                    style="color: var(--family-text-muted);">
                    <a href="{{ route('family.photo-albums.index') }}"
                        class="transition hover:text-amber-500">
                        Album Foto
                    </a>
                    <span>/</span>
                    <span style="color: var(--family-text-primary);">
                        {{ $album->name }}
                    </span>
                </div>

                {{-- Flash Message --}}
                @if (session('success'))
                    <div class="rounded-2xl border border-green-500/30 bg-green-500/10 px-5 py-4 text-sm text-green-700 dark:text-green-300">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="rounded-2xl border border-red-500/30 bg-red-500/10 px-5 py-4 text-sm text-red-700 dark:text-red-300">
                        <p class="mb-2 font-semibold">Ada masalah yang perlu diperbaiki:</p>
                        <ul class="list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Album Hero --}}
                <section class="relative isolate min-h-[320px] overflow-hidden rounded-[2rem] border border-amber-500/20 shadow-2xl lg:min-h-[390px]"
                    style="background: var(--family-card-bg, #211b16);">

                    @if ($coverPhoto)
                        <img
                            src="{{ route('family.photo-albums.photos.show', [
                                'albumId' => $album->id,
                                'photoId' => $coverPhoto->id,
                                'variant' => 'preview',
                            ]) }}"
                            alt="{{ $album->name }}"
                            decoding="async"
                            class="absolute inset-0 -z-20 h-full w-full object-cover"
                        >
                    @else
                        <div class="absolute inset-0 -z-20 bg-gradient-to-br from-amber-950 via-stone-900 to-black"></div>
                    @endif

                    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-black/90 via-black/65 to-black/15"></div>
                    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-black/70 via-transparent to-black/10"></div>

                    <div class="flex min-h-[320px] flex-col justify-end p-7 sm:p-10 lg:min-h-[390px] lg:p-14">

                        <div class="mb-5 inline-flex w-fit items-center gap-3 rounded-full border border-amber-200/30 bg-black/30 px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-amber-200 backdrop-blur-md">
                            <span class="text-base">✦</span>
                            <span>Family Memory Collection</span>
                        </div>

                        <p class="mb-2 text-sm font-medium tracking-wide text-amber-100/80">
                            {{ $album->familyGroup->name ?? 'Family Group' }}
                        </p>

                        <h1 class="max-w-3xl text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl">
                            {{ $album->name }}
                        </h1>

                        @if ($album->description)
                            <p class="mt-4 max-w-2xl text-sm leading-7 text-white/80 sm:text-base">
                                {{ $album->description }}
                            </p>
                        @endif

                        <div class="mt-7 flex flex-wrap items-center gap-5 text-sm text-white/85">
                            <span class="inline-flex items-center gap-2">
                                <span>▧</span>
                                {{ number_format($album->photos_count) }} Foto
                            </span>

                            @if ($album->event_date)
                                <span class="inline-flex items-center gap-2">
                                    <span>◷</span>
                                    {{ $album->event_date->translatedFormat('d F Y') }}
                                </span>
                            @endif

                            <span class="inline-flex items-center gap-2">
                                <span>⌂</span>
                                {{ $album->familyGroup->name ?? 'Family' }}
                            </span>
                        </div>
                    </div>
                </section>

                {{-- Gallery Heading --}}
                <section class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-[0.22em] text-amber-600">
                            Precious Moments
                        </p>
                        <h2 class="text-2xl font-bold tracking-tight sm:text-3xl"
                            style="color: var(--family-text-primary);">
                            Kenangan dalam Foto
                        </h2>
                        <p class="mt-2 text-sm"
                            style="color: var(--family-text-muted);">
                            Setiap foto menyimpan cerita yang berharga.
                        </p>
                    </div>

                    <a href="{{ route('family.photo-albums.index') }}"
                        class="rounded-xl border border-amber-500/30 px-5 py-3 text-sm font-semibold transition hover:bg-amber-500/10"
                        style="color: var(--family-text-primary);">
                        ← Kembali ke Album
                    </a>
                </section>

                {{-- Photo Filters --}}
<form
    method="GET"
    action="{{ route('family.photo-albums.show', $album->id) }}"
    class="mb-6 rounded-2xl border p-4 sm:p-5"
    style="background: var(--family-card); border-color: var(--family-border);"
>
    <div class="mb-4 flex items-center justify-between gap-3">
        <div>
            <h3 class="font-semibold" style="color: var(--family-text-primary);">
                Filter Foto
            </h3>
            <p class="mt-1 text-xs" style="color: var(--family-text-muted);">
                Cari foto berdasarkan tahun, tanggal, atau caption.
            </p>
        </div>

        <a
            href="{{ route('family.photo-albums.show', $album->id) }}"
            class="text-sm font-medium text-amber-500 hover:text-amber-400"
        >
            Reset
        </a>
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div>
            <label class="mb-1.5 block text-xs font-semibold"
                style="color: var(--family-text-secondary);">
                Tahun
            </label>
         <select
    name="year"
    class="w-full rounded-xl border px-3 py-2.5 text-sm"
    style="
        background-color: #211b16;
        color: #f5e9d5;
        border-color: rgba(180, 130, 60, 0.35);
        color-scheme: dark;
    "
>
    <option value="">Semua Tahun</option>
    @foreach ($years as $year)
        <option value="{{ $year }}"
            @selected(request('year') == $year)>
            {{ $year }}
        </option>
    @endforeach
</select>
        </div>

        <div>
            <label class="mb-1.5 block text-xs font-semibold"
                style="color: var(--family-text-secondary);">
                Dari Tanggal
            </label>
            <input
                type="date"
                name="date_from"
                value="{{ request('date_from') }}"
                class="w-full rounded-xl border px-3 py-2.5 text-sm"
                style="background: var(--family-input); color: var(--family-text-primary); border-color: var(--family-border);"
            >
        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-semibold"
                                style="color: var(--family-text-secondary);">
                                Sampai Tanggal
                            </label>
                            <input
                                type="date"
                                name="date_to"
                                value="{{ request('date_to') }}"
                                class="w-full rounded-xl border px-3 py-2.5 text-sm"
                                style="background: var(--family-input); color: var(--family-text-primary); border-color: var(--family-border);"
                            >
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-semibold"
                                style="color: var(--family-text-secondary);">
                                Cari Caption
                            </label>
                            <input
                                type="search"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Masukkan kata kunci..."
                                class="w-full rounded-xl border px-3 py-2.5 text-sm"
                                style="background: var(--family-input); color: var(--family-text-primary); border-color: var(--family-border);"
                            >
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button
                            type="submit"
                            class="family-leaf-button"
                        >
                            Terapkan Filter
                        </button>
                    </div>
                </form>

                {{-- Upload Panel --}}
                @if ($canUpload)
                    <section class="rounded-3xl border border-amber-500/20 p-5 shadow-lg sm:p-7"
                        style="background: var(--family-card-bg, rgba(40,30,20,.8));">

                        <div class="mb-5 flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-green-700/15 text-2xl">
                                🌿
                            </div>
                            <div>
                                <h3 class="font-bold" style="color: var(--family-text-primary);">
                                    Tambahkan Kenangan
                                </h3>
                                <p class="text-xs" style="color: var(--family-text-muted);">
                                    Upload hingga 20 foto sekaligus, maksimal 15 MB per foto.
                                </p>
                            </div>
                        </div>

                        <form
                            action="{{ route('family.photo-albums.photos.upload', $album->id) }}"
                            method="POST"
                            enctype="multipart/form-data"
                            id="family-photo-upload-form"
                        >
                            @csrf

                            <label for="family-photo-input"
                                class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-amber-500/35 px-5 py-10 text-center transition hover:border-amber-500 hover:bg-amber-500/5">

                                <span class="mb-3 text-4xl">📷</span>

                                <span class="font-semibold"
                                    style="color: var(--family-text-primary);">
                                    Pilih foto untuk diunggah
                                </span>

                                <span class="mt-2 text-xs"
                                    style="color: var(--family-text-muted);">
                                    JPG, JPEG, PNG, atau WebP
                                </span>

                                <input
                                    type="file"
                                    id="family-photo-input"
                                    name="photos[]"
                                    accept="image/jpeg,image/png,image/webp"
                                    multiple
                                    required
                                    class="sr-only"
                                >
                            </label>

                            <div id="family-photo-preview-list"
                                class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                            </div>

                            <div class="mt-5 flex flex-wrap items-center justify-between gap-4">
                                <p id="family-photo-selection-info"
                                    class="text-xs"
                                    style="color: var(--family-text-muted);">
                                    Belum ada foto dipilih.
                                </p>

                                <button type="submit"
                                    class="family-leaf-button px-8 py-3 font-semibold">
                                    ↑ Unggah Foto
                                </button>
                            </div>
                        </form>
                    </section>
                @endif
                @if ($canDelete)
    <section id="family-photo-selection-toolbar"
        class="hidden rounded-2xl border border-amber-500/30 p-5 shadow-lg"
        style="background: var(--family-card-bg, rgba(40,30,20,.8));">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="font-semibold" style="color: var(--family-text-primary);">
                    Mode Pilih Foto
                </p>
                <p id="family-photo-selected-count"
                    class="mt-1 text-xs"
                    style="color: var(--family-text-muted);">
                    0 foto dipilih
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <button type="button" id="family-photo-select-all"
                    class="rounded-xl border border-amber-500/30 px-4 py-2 text-sm font-semibold"
                    style="color: var(--family-text-primary);">
                    Pilih Semua
                </button>

                <button type="button" id="family-photo-clear-selection"
                    class="rounded-xl border border-gray-500/30 px-4 py-2 text-sm"
                    style="color: var(--family-text-primary);">
                    Batal Pilih
                </button>

                <button type="button" id="family-photo-delete-selected"
                    class="rounded-xl bg-red-700 px-5 py-2 text-sm font-semibold text-white transition hover:bg-red-800">
                    🗑 Hapus Terpilih
                </button>
            </div>
        </div>
    </section>

            <form id="family-photo-bulk-delete-form"
                action="{{ route('family.photo-albums.photos.bulk-destroy', $album->id) }}"
                method="POST"
                class="hidden">
                @csrf
                @method('DELETE')
                <div id="family-photo-delete-inputs"></div>
            </form>
        @endif

{{-- Masonry Gallery --}}
<section>
    @if ($photos->isNotEmpty())

        <div class="family-photo-masonry">
            @foreach ($photos as $photo)
                <article class="family-photo-card group relative"
                    data-photo-card="{{ $photo->id }}">

                    {{-- Checkbox Hapus Foto --}}
                    @if ($canDelete)
                        <label class="absolute right-3 top-3 z-30 flex h-9 w-9 cursor-pointer items-center justify-center rounded-xl border border-white/40 bg-black/65 shadow-lg backdrop-blur-sm transition hover:bg-amber-600">
                            <input
                                type="checkbox"
                                value="{{ $photo->id }}"
                                data-photo-select
                                aria-label="Pilih foto {{ $photo->original_name }}"
                                class="h-5 w-5 cursor-pointer accent-amber-500"
                            >
                        </label>
                    @endif

                    {{-- Tombol Cover Album --}}
                    @if ($canSetCover)
                        <div class="absolute left-3 top-3 z-30">
                            @if ($isCurrentCover($photo))
                                <span class="rounded-xl border border-amber-300/50 bg-amber-500 px-3 py-2 text-xs font-bold text-black shadow-lg">
                                    ✦ COVER UTAMA
                                </span>
                            @else
                                <form
                                    action="{{ route('family.photo-albums.photos.cover', [
                                        'albumId' => $album->id,
                                        'photoId' => $photo->id,
                                    ]) }}"
                                    method="POST"
                                    onsubmit="return confirm('Jadikan foto ini sebagai cover album?')"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit"
                                        class="rounded-xl border border-amber-200/40 bg-black/75 px-3 py-2 text-xs font-semibold text-amber-100 shadow-lg backdrop-blur-sm transition hover:bg-amber-600 hover:text-white">
                                        ✦ Jadikan Cover
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif


                    {{-- Area Tombol Favorit dan Buka Foto --}}
                    <div class="relative">

                        {{-- Tombol Favorit Foto --}}
                        @if ($canFavorite)
                            <button
                                type="button"
                                class="family-photo-favorite absolute bottom-3 right-3 z-30 flex h-11 w-11 items-center justify-center rounded-full border border-white/30 bg-black/70 text-xl text-white shadow-lg backdrop-blur-sm transition hover:scale-110"
                                data-favorite-url="{{ route('family.photo-albums.photos.favorite.toggle', [
                                    'albumId' => $album->id,
                                    'photoId' => $photo->id,
                                ]) }}"
                                data-favorited="{{ in_array($photo->id, $favoritePhotoIds) ? '1' : '0' }}"
                                aria-label="Favorit foto"
                                aria-pressed="{{ in_array($photo->id, $favoritePhotoIds) ? 'true' : 'false' }}"
                            >
                                {{ in_array($photo->id, $favoritePhotoIds) ? '❤️' : '🤍' }}
                            </button>
                        @endif

                        {{-- Tombol Buka Foto / Lightbox --}}
                        <button
                            type="button"
                            class="family-photo-open relative block w-full overflow-hidden rounded-2xl text-left shadow-lg"
                            data-photo-src="{{ route('family.photo-albums.photos.show', [
                                'albumId' => $album->id,
                                'photoId' => $photo->id,
                                'variant' => 'preview',
                            ]) }}"
                            data-photo-caption="{{ $photo->caption ?: $photo->original_name }}"
                            data-photo-original-caption="{{ $photo->caption ?? '' }}"
                            data-photo-taken-at="{{ $photo->taken_at ? $photo->taken_at->format('Y-m-d\TH:i') : '' }}"
                            data-photo-update-url="{{ route('family.photo-albums.photos.update', [
                                'albumId' => $album->id,
                                'photoId' => $photo->id,
                            ]) }}"
                            aria-label="Lihat {{ $photo->original_name }}"
                        >
                            <img
                                src="{{ route('family.photo-albums.photos.show', [
                                    'albumId' => $album->id,
                                    'photoId' => $photo->id,
                                    'variant' => 'thumbnail',
                                ]) }}"
                                alt="{{ $photo->caption ?: $photo->original_name }}"
                                loading="lazy"
                                decoding="async"
                                class="h-auto w-full transition duration-700 group-hover:scale-105"
                            >

                            <div class="absolute inset-0 flex items-end bg-gradient-to-t from-black/70 via-transparent to-transparent p-4 opacity-0 transition duration-300 group-hover:opacity-100">
                                <span class="text-sm font-medium text-white">
                                    {{ $photo->caption ?: $photo->original_name }}
                                </span>
                            </div>
                        </button>
                    </div>

                    {{-- Tanggal Foto --}}
                    @if ($photo->taken_at)
                        <p class="mt-2 px-1 text-xs"
                            style="color: var(--family-text-muted);">
                            {{ $photo->taken_at->translatedFormat('d F Y') }}
                        </p>
                    @endif
                </article>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $photos->links() }}
        </div>

    @else
        <div class="rounded-3xl border border-dashed border-amber-500/30 px-6 py-20 text-center"
            style="background: var(--family-card-bg, rgba(40,30,20,.5));">

            <div class="mb-5 text-6xl">🌄</div>

            <h3 class="text-xl font-bold"
                style="color: var(--family-text-primary);">
                Album ini masih kosong
            </h3>

            <p class="mx-auto mt-3 max-w-md text-sm leading-7"
                style="color: var(--family-text-muted);">
                Belum ada foto yang tersimpan di album ini. Mulailah mengabadikan momen keluarga yang berharga.
            </p>
        </div>
    @endif
</section>

</div> {{-- Tutup mx-auto max-w-7xl --}}

{{-- Lightbox --}}
<div id="family-photo-lightbox"
    class="fixed inset-0 z-[100] hidden items-center justify-center overflow-y-auto bg-black/95 p-4"
    aria-modal="true"
    role="dialog"
    aria-label="Penampil foto">

    {{-- Tombol Tutup --}}
    <button type="button"
        id="family-lightbox-close"
        class="absolute right-5 top-5 z-50 flex h-12 w-12 items-center justify-center rounded-full bg-white/10 text-3xl text-white hover:bg-white/20"
        aria-label="Tutup">×</button>

    {{-- Foto Sebelumnya --}}
    <button type="button"
        id="family-lightbox-prev"
        class="absolute left-3 top-1/2 z-40 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-3xl text-white sm:left-7"
        aria-label="Foto sebelumnya">‹</button>

    <div class="flex w-full max-w-7xl flex-col items-center gap-6 py-12 lg:flex-row lg:items-start">

        {{-- Tampilan Foto --}}
        <div class="flex min-w-0 flex-1 flex-col items-center">
            <img id="family-lightbox-image"
                src=""
                alt=""
                decoding="async"
                class="max-h-[70vh] max-w-full rounded-xl object-contain shadow-2xl">

            <p id="family-lightbox-caption"
                class="mt-5 max-w-3xl text-center text-sm text-white/80"></p>
        </div>

        {{-- Editor Informasi Foto --}}
        @if ($canEditPhoto)
            <aside class="w-full shrink-0 rounded-2xl border border-amber-500/25 p-6 shadow-2xl lg:mt-8 lg:w-80"
                style="background: var(--family-card-bg, #211b16);">

                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-500">
                    Photo Details
                </p>

                <h3 class="mt-2 text-xl font-bold"
                    style="color: var(--family-text-primary);">
                    Informasi Foto
                </h3>

                <form id="family-photo-edit-form"
                    action="#"
                    method="POST"
                    class="mt-6 space-y-5">

                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="family-photo-edit-caption"
                            class="mb-2 block text-sm font-semibold"
                            style="color: var(--family-text-primary);">
                            Caption Foto
                        </label>

                        <textarea
                            id="family-photo-edit-caption"
                            name="caption"
                            rows="4"
                            maxlength="2000"
                            class="w-full rounded-xl border border-amber-500/20 bg-black/10 p-3 text-sm"
                            style="color: var(--family-text-primary);"
                            placeholder="Ceritakan kenangan di balik foto ini..."
                        ></textarea>
                    </div>

                    <div>
                        <label for="family-photo-edit-taken-at"
                            class="mb-2 block text-sm font-semibold"
                            style="color: var(--family-text-primary);">
                            Tanggal dan Waktu
                        </label>

                        <input
                            type="datetime-local"
                            id="family-photo-edit-taken-at"
                            name="taken_at"
                            class="w-full rounded-xl border border-amber-500/20 bg-black/10 p-3 text-sm"
                            style="color: var(--family-text-primary);"
                        >
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button type="submit"
                            class="family-leaf-button flex-1 px-5 py-3 text-sm font-semibold">
                            Simpan Perubahan
                        </button>

                        <button type="button"
                            id="family-photo-edit-reset"
                            class="rounded-xl border border-gray-500/30 px-4 py-3 text-sm"
                            style="color: var(--family-text-primary);">
                            Reset
                        </button>
                    </div>
                </form>
            </aside>
        @endif
    </div>

    {{-- Foto Berikutnya --}}
    <button type="button"
        id="family-lightbox-next"
        class="absolute right-3 top-1/2 z-40 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-3xl text-white sm:right-7"
        aria-label="Foto berikutnya">›</button>
</div>
</main>
</div>
</div>
@endsection

@push('styles')
<style>
    .family-photo-masonry {
        columns: 1;
        column-gap: 1.25rem;
    }

    @media (min-width: 640px) {
        .family-photo-masonry {
            columns: 2;
        }
    }

    @media (min-width: 1024px) {
        .family-photo-masonry {
            columns: 3;
        }
    }

    @media (min-width: 1440px) {
        .family-photo-masonry {
            columns: 4;
        }
    }

    .family-photo-card {
        break-inside: avoid;
        margin-bottom: 1.25rem;
    }

    .family-photo-card img {
        display: block;
        width: 100%;
        height: auto;
    }

    #family-photo-lightbox img {
        user-select: none;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ==========================================
    // UPLOAD PREVIEW
    // ==========================================

    const input = document.getElementById('family-photo-input');
    const previewList = document.getElementById('family-photo-preview-list');
    const selectionInfo = document.getElementById('family-photo-selection-info');
    const uploadForm = document.getElementById('family-photo-upload-form');

    if (input && previewList && selectionInfo) {
        input.addEventListener('change', function () {
            previewList.innerHTML = '';

            const files = Array.from(input.files || []);

            selectionInfo.textContent = files.length
                ? files.length + ' foto dipilih.'
                : 'Belum ada foto dipilih.';

            if (files.length > 20) {
                selectionInfo.textContent = 'Maksimal 20 foto dalam satu upload.';
                return;
            }

            files.forEach(function (file) {
                if (!file.type.startsWith('image/')) return;

                const card = document.createElement('div');
                card.style.cssText = `
                    overflow:hidden;
                    border:1px solid rgba(180,130,60,.3);
                    border-radius:12px;
                    min-width:0;
                `;

                const image = document.createElement('img');
                image.alt = file.name;
                image.style.cssText = `
                    display:block;
                    width:100%;
                    height:150px;
                    object-fit:cover;
                    background:#302318;
                `;

                const name = document.createElement('p');
                name.textContent = file.name;
                name.style.cssText = `
                    overflow:hidden;
                    text-overflow:ellipsis;
                    white-space:nowrap;
                    padding:10px;
                    margin:0;
                    font-size:12px;
                    color:var(--family-text-primary);
                `;

                const reader = new FileReader();

                reader.onload = function (event) {
                    image.src = event.target.result;
                };

                reader.readAsDataURL(file);

                card.appendChild(image);
                card.appendChild(name);
                previewList.appendChild(card);
            });
        });
    }

    if (uploadForm && input) {
        uploadForm.addEventListener('submit', function (event) {
            if (!input.files.length || input.files.length > 20) {
                event.preventDefault();
                alert('Pilih antara 1 sampai 20 foto terlebih dahulu.');
            }
        });
    }

    // ==========================================
    // PHOTO SELECTION & BULK DELETE
    // ==========================================

    const photoCheckboxes = Array.from(
        document.querySelectorAll('[data-photo-select]')
    );

    const toolbar = document.getElementById('family-photo-selection-toolbar');
    const selectedCount = document.getElementById('family-photo-selected-count');
    const selectAllButton = document.getElementById('family-photo-select-all');
    const clearButton = document.getElementById('family-photo-clear-selection');
    const deleteButton = document.getElementById('family-photo-delete-selected');
    const deleteForm = document.getElementById('family-photo-bulk-delete-form');
    const deleteInputs = document.getElementById('family-photo-delete-inputs');

    function getSelectedPhotos() {
        return photoCheckboxes.filter(function (checkbox) {
            return checkbox.checked;
        });
    }

    function updatePhotoSelection() {
        const selected = getSelectedPhotos();
        const count = selected.length;

        if (toolbar) {
            toolbar.classList.toggle('hidden', count === 0);
        }

        if (selectedCount) {
            selectedCount.textContent = count + ' foto dipilih';
        }

        photoCheckboxes.forEach(function (checkbox) {
            const card = checkbox.closest('[data-photo-card]');

            if (card) {
                card.classList.toggle('ring-2', checkbox.checked);
                card.classList.toggle('ring-amber-500', checkbox.checked);
                card.classList.toggle('rounded-2xl', checkbox.checked);
            }
        });
    }

    photoCheckboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', updatePhotoSelection);
    });

    if (selectAllButton) {
        selectAllButton.addEventListener('click', function () {
            photoCheckboxes.forEach(function (checkbox) {
                checkbox.checked = true;
            });

            updatePhotoSelection();
        });
    }

    if (clearButton) {
        clearButton.addEventListener('click', function () {
            photoCheckboxes.forEach(function (checkbox) {
                checkbox.checked = false;
            });

            updatePhotoSelection();
        });
    }

    if (deleteButton && deleteForm) {
        deleteButton.addEventListener('click', function () {
            const selected = getSelectedPhotos();

            if (!selected.length) return;

            const confirmed = confirm(
                'Yakin ingin menghapus ' + selected.length +
                ' foto terpilih? Album tetap tersimpan.'
            );

            if (!confirmed) return;

            deleteInputs.innerHTML = '';

            selected.forEach(function (checkbox) {
                const hidden = document.createElement('input');

                hidden.type = 'hidden';
                hidden.name = 'photo_ids[]';
                hidden.value = checkbox.value;

                deleteInputs.appendChild(hidden);
            });

            deleteForm.submit();
        });
    }

    // ==========================================
    // PHOTO FAVORITES
    // ==========================================

    document.querySelectorAll('.family-photo-favorite').forEach(function (button) {
        button.addEventListener('click', async function (event) {
            event.stopPropagation();

            if (button.dataset.loading === '1') return;

            button.dataset.loading = '1';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]');

                if (!csrfToken) {
                    throw new Error('Token keamanan tidak ditemukan.');
                }

                const response = await fetch(button.dataset.favoriteUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken.content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error('Gagal memperbarui favorit.');
                }

                const result = await response.json();

                button.dataset.favorited = result.favorited ? '1' : '0';
                button.setAttribute('aria-pressed', result.favorited ? 'true' : 'false');
                button.textContent = result.favorited ? '❤️' : '🤍';

            } catch (error) {
                alert(error.message || 'Terjadi kesalahan saat menyimpan favorit.');
            } finally {
                button.dataset.loading = '0';
            }
        });
    });


    // ==========================================
    // LIGHTBOX
    // ==========================================

    const lightbox = document.getElementById('family-photo-lightbox');
    const lightboxImage = document.getElementById('family-lightbox-image');
    const lightboxCaption = document.getElementById('family-lightbox-caption');
    const closeButton = document.getElementById('family-lightbox-close');
    const previousButton = document.getElementById('family-lightbox-prev');
    const nextButton = document.getElementById('family-lightbox-next');
    const photoButtons = Array.from(document.querySelectorAll('.family-photo-open'));
    const photoEditForm = document.getElementById('family-photo-edit-form');
    const photoEditCaption = document.getElementById('family-photo-edit-caption');
    const photoEditTakenAt = document.getElementById('family-photo-edit-taken-at');
    const photoEditReset = document.getElementById('family-photo-edit-reset');

    let currentPhotoIndex = 0;
    let currentOriginalCaption = '';
    let currentOriginalTakenAt = '';

    function showPhoto(index) {
    if (!photoButtons.length || !lightbox) return;

    currentPhotoIndex =
        (index + photoButtons.length) % photoButtons.length;

    const photo = photoButtons[currentPhotoIndex];

    lightboxImage.src = photo.dataset.photoSrc;
    lightboxImage.alt = photo.dataset.photoCaption || 'Foto keluarga';
    lightboxCaption.textContent = photo.dataset.photoCaption || '';

    currentOriginalCaption = photo.dataset.photoOriginalCaption || '';
    currentOriginalTakenAt = photo.dataset.photoTakenAt || '';

    if (photoEditForm) {
        photoEditForm.action = photo.dataset.photoUpdateUrl;
        photoEditCaption.value = currentOriginalCaption;
        photoEditTakenAt.value = currentOriginalTakenAt;
    }

    lightbox.classList.remove('hidden');
    lightbox.classList.add('flex');

    document.body.style.overflow = 'hidden';
    }

        // Reset informasi foto ke data awal
    if (photoEditReset && photoEditCaption && photoEditTakenAt) {
        photoEditReset.addEventListener('click', function () {
            photoEditCaption.value = currentOriginalCaption;
            photoEditTakenAt.value = currentOriginalTakenAt;
        });
    }

     function closeLightbox() {
        if (!lightbox) return;

        lightbox.classList.add('hidden');
        lightbox.classList.remove('flex');

        lightboxImage.src = '';
        document.body.style.overflow = '';
    }

    photoButtons.forEach(function (button, index) {
        button.addEventListener('click', function () {
            showPhoto(index);
        });
    });

    if (closeButton) {
        closeButton.addEventListener('click', closeLightbox);
    }

    if (previousButton) {
        previousButton.addEventListener('click', function () {
            showPhoto(currentPhotoIndex - 1);
        });
    }

    if (nextButton) {
        nextButton.addEventListener('click', function () {
            showPhoto(currentPhotoIndex + 1);
        });
    }

    if (lightbox) {
        lightbox.addEventListener('click', function (event) {
            if (event.target === lightbox) {
                closeLightbox();
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (!lightbox || lightbox.classList.contains('hidden')) return;

        if (event.key === 'Escape') closeLightbox();

        if (event.key === 'ArrowLeft') {
            showPhoto(currentPhotoIndex - 1);
        }

        if (event.key === 'ArrowRight') {
            showPhoto(currentPhotoIndex + 1);
        }
    });

});
</script>
@endpush