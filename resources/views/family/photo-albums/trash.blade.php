
@extends('layouts.family')

@section('title', 'Sampah Family Memories')

@section('content')
<div class="family-app-layout flex h-screen overflow-hidden">
    @include('family.partials.sidebar')

    <div class="flex min-h-0 min-w-0 flex-1 flex-col">
        @include('family.partials.topbar')

        <main id="family-main"
            class="family-main-content min-h-0 min-w-0 flex-1 overflow-y-auto px-5 py-7 lg:px-10">

            <div class="mx-auto max-w-7xl space-y-8">

                {{-- HERO --}}
                <section class="relative overflow-hidden rounded-3xl border border-amber-900/20 bg-gradient-to-br from-[#302318] via-[#493522] to-[#21180f] p-7 text-amber-50 shadow-2xl lg:p-10">
                    <div class="pointer-events-none absolute -right-16 -top-20 h-72 w-72 rounded-full bg-amber-500/10 blur-3xl"></div>

                    <div class="relative flex flex-col justify-between gap-6 lg:flex-row lg:items-center">
                        <div class="max-w-2xl">
                            <div class="mb-4 inline-flex items-center gap-3 rounded-full border border-amber-300/20 bg-amber-100/10 px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-amber-200">
                                <span class="text-base">✦</span>
                                <span>Recently Deleted</span>
                            </div>

                            <h1 class="text-3xl font-bold tracking-tight lg:text-5xl">
                                Memory
                                <span class="block text-amber-300">Restoration.</span>
                            </h1>

                            <p class="mt-4 max-w-xl text-sm leading-7 text-amber-100/70 lg:text-base">
                                Kenangan yang dihapus masih tersimpan di sini.
                                Pulihkan kembali atau hapus secara permanen.
                            </p>

                            <div class="mt-6 flex flex-wrap gap-3 text-sm text-amber-100/80">
                                <span class="rounded-xl border border-white/10 bg-white/5 px-4 py-2">
                                    {{ $trashedAlbums->count() }} Album
                                </span>
                                <span class="rounded-xl border border-white/10 bg-white/5 px-4 py-2">
                                    {{ $trashedPhotos->count() }} Foto
                                </span>
                            </div>
                        </div>

                        <a href="{{ route('family.photo-albums.index') }}"
                            class="inline-flex min-h-12 items-center justify-center rounded-xl border border-amber-200/20 bg-white/10 px-6 py-3 text-sm font-semibold text-amber-50 transition hover:bg-white/20">
                            ← Kembali ke Galeri
                        </a>
                    </div>
                </section>

                {{-- FLASH MESSAGE --}}
                @if(session('success'))
                    <div class="rounded-2xl border border-green-500/30 bg-green-500/10 px-5 py-4 text-sm text-green-700 dark:text-green-300">
                        {{ session('success') }}
                    </div>
                @endif

                @if($trashedAlbums->isNotEmpty() || $trashedPhotos->isNotEmpty())
                    {{-- EMPTY TRASH --}}
                    <section class="flex flex-col justify-between gap-4 rounded-2xl border border-red-500/20 bg-red-500/5 p-5 sm:flex-row sm:items-center">
                        <div>
                            <h2 class="font-bold text-stone-800 dark:text-amber-50">
                                Kosongkan Sampah
                            </h2>
                            <p class="mt-1 text-sm text-stone-500 dark:text-amber-100/60">
                                Seluruh album dan foto di Sampah akan dihapus permanen.
                            </p>
                        </div>

                        <form action="{{ route('family.photo-albums.trash.empty') }}"
                            method="POST"
                            onsubmit="return confirm('PERINGATAN! Seluruh isi Sampah akan dihapus permanen dan tidak dapat dipulihkan. Lanjutkan?')">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                class="family-leaf-button family-leaf-button--danger px-6 py-3 font-semibold">
                                Kosongkan Sampah
                            </button>
                        </form>
                    </section>
                @endif

                {{-- DELETED ALBUMS --}}
                <section>
                    <div class="mb-6">
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-amber-700 dark:text-amber-400">
                            Deleted Collections
                        </p>
                        <h2 class="mt-2 text-2xl font-bold text-stone-800 dark:text-amber-50">
                            Album Terhapus
                        </h2>
                    </div>

                    @if($trashedAlbums->isNotEmpty())
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach($trashedAlbums as $album)
                                <article class="overflow-hidden rounded-3xl border border-amber-900/10 bg-white/80 shadow-lg dark:border-amber-100/10 dark:bg-[#241a11]/90">

                                    <div class="relative flex aspect-[16/10] items-center justify-center overflow-hidden bg-gradient-to-br from-[#6d5034] via-[#382719] to-[#1e160f]">
                                        @if($album->coverPhoto)
                                            <img
                                                src="{{ route('family.photo-albums.photos.show', [
                                                    'albumId' => $album->id,
                                                    'photoId' => $album->coverPhoto->id,
                                                    'variant' => 'preview',
                                                ]) }}"
                                                alt="Cover {{ $album->name }}"
                                                class="absolute inset-0 h-full w-full object-cover opacity-70"
                                                loading="lazy"
                                            >
                                        @else
                                            <div class="text-6xl text-amber-100/70">✧</div>
                                        @endif

                                        <div class="absolute inset-0 bg-black/30"></div>

                                        <span class="absolute left-4 top-4 rounded-full border border-white/20 bg-black/40 px-3 py-1.5 text-xs text-white backdrop-blur">
                                            {{ $album->familyGroup->name }}
                                        </span>

                                        <span class="absolute bottom-4 right-4 rounded-full bg-black/40 px-3 py-1.5 text-xs text-white backdrop-blur">
                                            {{ $album->photos->count() }} Foto
                                        </span>
                                    </div>

                                    <div class="space-y-4 p-5">
                                        <div>
                                            <h3 class="truncate text-lg font-bold text-stone-800 dark:text-amber-50">
                                                {{ $album->name }}
                                            </h3>
                                            <p class="mt-1 text-xs text-stone-500 dark:text-amber-200/60">
                                                Dihapus {{ $album->deleted_at?->translatedFormat('d F Y, H:i') }}
                                            </p>
                                        </div>

                                        @if($album->description)
                                            <p class="line-clamp-2 text-sm text-stone-600 dark:text-amber-100/70">
                                                {{ $album->description }}
                                            </p>
                                        @endif

                                        <div class="grid grid-cols-2 gap-2">
                                            <form action="{{ route('family.photo-albums.restore', $album->id) }}"
                                                method="POST"
                                                onsubmit="return confirm('Pulihkan album beserta foto yang ikut terhapus?')">
                                                @csrf

                                                <button type="submit"
                                                    class="family-leaf-button w-full px-3 py-3 text-sm font-semibold text-white">
                                                    ↻ Pulihkan
                                                </button>
                                            </form>

                                            <form action="{{ route('family.photo-albums.force-delete', $album->id) }}"
                                                method="POST"
                                                onsubmit="return confirm('Album dan seluruh fotonya akan dihapus permanen. Lanjutkan?')">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                    class="family-leaf-button family-leaf-button--danger w-full px-3 py-3 text-sm font-semibold">
                                                    Hapus Permanen
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-3xl border border-dashed border-amber-800/30 bg-white/50 px-6 py-10 text-center dark:bg-[#21180f]/60">
                            <div class="text-4xl text-amber-700 dark:text-amber-400">✧</div>
                            <p class="mt-3 text-sm text-stone-500 dark:text-amber-100/60">
                                Tidak ada album terhapus.
                            </p>
                        </div>
                    @endif
                </section>

                {{-- DELETED PHOTOS --}}
                <section>
                    <div class="mb-6">
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-amber-700 dark:text-amber-400">
                            Deleted Memories
                        </p>
                        <h2 class="mt-2 text-2xl font-bold text-stone-800 dark:text-amber-50">
                            Foto Terhapus
                        </h2>
                    </div>

                    @if($trashedPhotos->isNotEmpty())
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                            @foreach($trashedPhotos as $photo)
                                <article class="overflow-hidden rounded-2xl border border-amber-900/10 bg-white/80 shadow-lg dark:border-amber-100/10 dark:bg-[#241a11]/90">
                                    <div class="relative aspect-square overflow-hidden bg-gradient-to-br from-[#6d5034] to-[#21180f]">
                                        <img
                                            src="{{ route('family.photo-albums.photos.show', [
                                                'albumId' => $photo->family_photo_album_id,
                                                'photoId' => $photo->id,
                                                'variant' => 'thumbnail',
                                            ]) }}"
                                            alt="{{ $photo->caption ?: $photo->original_name }}"
                                            class="h-full w-full object-cover opacity-80"
                                            loading="lazy"
                                        >

                                        <span class="absolute bottom-2 left-2 rounded-lg bg-black/50 px-2 py-1 text-xs text-white backdrop-blur">
                                            {{ $photo->album->name }}
                                        </span>
                                    </div>

                                    <div class="space-y-3 p-3 sm:p-4">
                                        <div>
                                            <p class="truncate text-sm font-semibold text-stone-800 dark:text-amber-50">
                                                {{ $photo->caption ?: $photo->original_name }}
                                            </p>
                                            <p class="mt-1 text-xs text-stone-500 dark:text-amber-200/60">
                                                {{ $photo->deleted_at?->translatedFormat('d M Y, H:i') }}
                                            </p>
                                        </div>

                                        <div class="space-y-2">
                                            <form action="{{ route('family.photo-albums.photos.restore', [
                                                'albumId' => $photo->family_photo_album_id,
                                                'photoId' => $photo->id,
                                            ]) }}" method="POST">
                                                @csrf

                                                <button type="submit"
                                                    class="family-leaf-button w-full px-3 py-2.5 text-sm font-semibold text-white">
                                                    ↻ Pulihkan Foto
                                                </button>
                                            </form>

                                            <form action="{{ route('family.photo-albums.photos.force-delete', [
                                                'albumId' => $photo->family_photo_album_id,
                                                'photoId' => $photo->id,
                                            ]) }}"
                                                method="POST"
                                                onsubmit="return confirm('Foto ini akan dihapus permanen. Lanjutkan?')">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                    class="family-leaf-button family-leaf-button--danger w-full px-3 py-2.5 text-sm font-semibold">
                                                    Hapus Permanen
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-3xl border border-dashed border-amber-800/30 bg-white/50 px-6 py-10 text-center dark:bg-[#21180f]/60">
                            <div class="text-4xl text-amber-700 dark:text-amber-400">✧</div>
                            <p class="mt-3 text-sm text-stone-500 dark:text-amber-100/60">
                                Tidak ada foto terhapus secara individual.
                            </p>
                        </div>
                    @endif
                </section>

            </div>
        </main>
    </div>
</div>
@endsection
