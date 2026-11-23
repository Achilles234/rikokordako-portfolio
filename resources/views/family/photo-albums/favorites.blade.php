
@extends('layouts.family')

@section('title', 'Foto Favorit Saya')

@section('content')
@php
    $familyUser = auth('family')->user();
@endphp

<div class="family-app-layout flex h-screen overflow-hidden">
    @include('family.partials.sidebar')

    <div class="flex min-h-0 min-w-0 flex-1 flex-col">
        @include('family.partials.topbar')

        <main id="family-main"
            class="family-main-content min-h-0 min-w-0 flex-1 overflow-y-auto px-5 py-7 lg:px-10">

            <div class="mx-auto max-w-7xl">

                {{-- Header --}}
                <div class="mb-8">
                    <a href="{{ route('family.photo-albums.index') }}"
                        class="mb-5 inline-flex items-center gap-2 text-sm transition hover:text-amber-400"
                        style="color: var(--family-text-secondary);">
                        <span>←</span>
                        Kembali ke Album Foto
                    </a>

                    <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
                        <div>
                            <div class="mb-4 inline-flex items-center gap-3 rounded-full border border-amber-300/20 bg-amber-100/10 px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-amber-200">
                                <span class="shrink-0 text-base">♥</span>
                                <span>My Favorite Memories</span>
                            </div>

                            <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl"
                                style="color: var(--family-text-primary);">
                                Foto Favorit Saya
                            </h1>

                            <p class="mt-3 max-w-xl text-sm leading-6"
                                style="color: var(--family-text-secondary);">
                                Kumpulan kenangan pilihan yang kamu simpan dari berbagai album keluarga.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-amber-300/20 bg-amber-100/5 px-6 py-4">
                            <p class="text-xs uppercase tracking-widest"
                                style="color: var(--family-text-secondary);">
                                Total Foto Favorit
                            </p>
                            <p class="mt-1 text-3xl font-semibold text-amber-300">
                                {{ $photos->total() }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Flash message --}}
                @if (session('success'))
                    <div class="mb-6 rounded-xl border border-green-500/20 bg-green-500/10 px-5 py-3 text-sm text-green-400">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Gallery --}}
                @if ($photos->isNotEmpty())
                    <div class="columns-2 gap-4 space-y-4 sm:columns-3 lg:columns-4 xl:columns-5">
                        @foreach ($photos as $photo)
                            <article
                                class="group relative mb-4 break-inside-avoid overflow-hidden rounded-2xl border border-amber-300/10"
                                data-favorite-card>

                                <div class="relative overflow-hidden">
                                    <a href="{{ route('family.photo-albums.show', $photo->family_photo_album_id) }}"
                                        class="block">
                                        <img
                                            src="{{ route('family.photo-albums.photos.show', [
                                                'albumId' => $photo->family_photo_album_id,
                                                'photoId' => $photo->id,
                                                'variant' => 'preview',
                                            ]) }}"
                                            alt="{{ $photo->caption ?: $photo->original_name }}"
                                            class="h-auto w-full object-cover transition duration-700 group-hover:scale-105"
                                            loading="lazy">
                                    </a>

                                    <button
                                        type="button"
                                        class="favorite-remove-button absolute right-3 top-3 z-20 flex h-10 w-10 items-center justify-center rounded-full border border-white/20 bg-black/60 text-xl text-white shadow-lg backdrop-blur transition hover:scale-110 hover:bg-black/80"
                                        data-favorite-url="{{ route('family.photo-albums.photos.favorite.toggle', [
                                            'albumId' => $photo->family_photo_album_id,
                                            'photoId' => $photo->id,
                                        ]) }}"
                                        aria-label="Hapus dari favorit"
                                        title="Hapus dari favorit">
                                        ❤️
                                    </button>
                                </div>

                                <div class="p-3"
                                    style="background: var(--family-surface, rgba(0,0,0,.2));">
                                    <p class="truncate text-sm font-semibold"
                                        style="color: var(--family-text-primary);">
                                        {{ $photo->caption ?: $photo->original_name }}
                                    </p>

                                    <p class="mt-1 truncate text-xs"
                                        style="color: var(--family-text-secondary);">
                                        {{ $photo->album_name }}
                                    </p>

                                    <p class="mt-1 truncate text-xs"
                                        style="color: var(--family-text-secondary);">
                                        {{ $photo->group_name }}
                                    </p>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="mt-8">
                        {{ $photos->links() }}
                    </div>
                @else
                    <div class="rounded-3xl border border-dashed border-amber-300/20 px-6 py-20 text-center">
                        <div class="mb-5 text-6xl text-amber-300/70">♡</div>

                        <h2 class="text-xl font-semibold"
                            style="color: var(--family-text-primary);">
                            Belum Ada Foto Favorit
                        </h2>

                        <p class="mx-auto mt-3 max-w-md text-sm leading-6"
                            style="color: var(--family-text-secondary);">
                            Simpan foto kenangan dengan menekan ikon hati pada foto di album keluarga.
                        </p>

                        <a href="{{ route('family.photo-albums.index') }}"
                            class="family-leaf-button mt-7 inline-flex items-center justify-center">
                            Jelajahi Album Foto
                        </a>
                    </div>
                @endif

            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.favorite-remove-button').forEach(function (button) {
        button.addEventListener('click', async function () {
            if (button.dataset.loading === '1') return;

            button.dataset.loading = '1';
            button.disabled = true;

            try {
                const response = await fetch(button.dataset.favoriteUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({}),
                });

                if (!response.ok) {
                    throw new Error('Gagal memperbarui favorit.');
                }

                const result = await response.json();

                if (result.success && !result.favorited) {
                    const card = button.closest('[data-favorite-card]');
                    card.style.transition = 'opacity 250ms ease, transform 250ms ease';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(.95)';

                    setTimeout(function () {
                        card.remove();

                        const remaining = document.querySelectorAll('[data-favorite-card]').length;

                        if (remaining === 0) {
                            window.location.reload();
                        }
                    }, 260);
                }
            } catch (error) {
                alert(error.message || 'Terjadi kesalahan.');
                button.disabled = false;
                button.dataset.loading = '0';
            }
        });
    });
});
</script>
@endsection
