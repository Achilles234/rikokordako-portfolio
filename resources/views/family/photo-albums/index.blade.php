@extends('layouts.family')

@section('title', 'Family Memories')

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

                    <div class="relative flex flex-col justify-between gap-7 lg:flex-row lg:items-center">
                        <div class="max-w-2xl">
                            <div class="mb-4 inline-flex items-center gap-3 rounded-full border border-amber-300/20 bg-amber-100/10 px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-amber-200">
                                <span class="shrink-0 text-base">✦</span>
                                <span>Family Memory Collection</span>
                            </div>

                            <h1 class="text-3xl font-bold tracking-tight lg:text-5xl">
                                Every Picture
                                <span class="block text-amber-300">Tells Our Story.</span>
                            </h1>

                            <p class="mt-4 max-w-xl text-sm leading-7 text-amber-100/70 lg:text-base">
                                Simpan, rayakan, dan kenang setiap momen berharga
                                bersama keluarga dalam satu galeri kenangan.
                            </p>

                            <div class="mt-6 flex flex-wrap gap-3 text-sm text-amber-100/80">
                                <span class="rounded-xl border border-white/10 bg-white/5 px-4 py-2">
                                    {{ $albums->total() }} Album
                                </span>
                                <span class="rounded-xl border border-white/10 bg-white/5 px-4 py-2">
                                    {{ $groups->count() }} Grup Keluarga
                                </span>
                            </div>
                        </div>

                        @if($groups->isNotEmpty())
                            <a href="{{ route('family.photo-albums.create') }}"
                                class="family-leaf-button inline-flex min-h-14 items-center justify-center gap-3 px-7 py-4 font-semibold text-white shadow-xl">
                                <span class="text-xl">＋</span>
                                Buat Album Baru
                            </a>
                        @endif
                    </div>
                </section>

                {{-- FLASH MESSAGE --}}
                @if(session('success'))
                    <div class="rounded-2xl border border-green-500/30 bg-green-500/10 px-5 py-4 text-sm text-green-700 dark:text-green-300">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- FILTER --}}
                <section class="rounded-2xl border border-amber-900/10 bg-white/70 p-5 shadow-lg backdrop-blur dark:border-amber-100/10 dark:bg-[#21180f]/80">
                    <form method="GET" action="{{ route('family.photo-albums.index') }}"
                        class="grid grid-cols-1 gap-4 md:grid-cols-[1fr_1.5fr_auto]">

                        <div>
                            <label for="group_id" class="mb-2 block text-sm font-semibold text-stone-700 dark:text-amber-100">
                                Grup Keluarga
                            </label>

                            <select id="group_id" name="group_id"
                                class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 text-sm text-stone-800 outline-none focus:border-amber-600 dark:border-amber-900 dark:bg-[#302318] dark:text-amber-50">
                                <option value="">Semua Grup</option>

                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}"
                                        @selected((string) request('group_id') === (string) $group->id)>
                                        {{ $group->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="q" class="mb-2 block text-sm font-semibold text-stone-700 dark:text-amber-100">
                                Cari Album
                            </label>

                            <input id="q" type="search" name="q"
                                value="{{ request('q') }}"
                                placeholder="Cari nama album..."
                                class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 text-sm text-stone-800 outline-none focus:border-amber-600 dark:border-amber-900 dark:bg-[#302318] dark:text-amber-50">
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="submit"
                                class="rounded-xl bg-amber-800 px-6 py-3 text-sm font-semibold text-white transition hover:bg-amber-700">
                                Cari
                            </button>

                            <a href="{{ route('family.photo-albums.index') }}"
                                class="rounded-xl border border-stone-300 px-5 py-3 text-sm font-semibold text-stone-700 transition hover:bg-stone-100 dark:border-amber-900 dark:text-amber-100 dark:hover:bg-amber-950">
                                Reset
                            </a>
                        </div>
                    </form>
                </section>

                {{-- ALBUM COLLECTION --}}
                <section>
                    <div class="mb-6 flex items-end justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.25em] text-amber-700 dark:text-amber-400">
                                Your Memories
                            </p>

                            <h2 class="mt-2 text-2xl font-bold text-stone-800 dark:text-amber-50">
                                Koleksi Album
                            </h2>
                        </div>

                        <span class="text-sm text-stone-500 dark:text-amber-200/60">
                            {{ $albums->total() }} koleksi
                        </span>
                    </div>

                    @if($albums->isNotEmpty())
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">

                            @foreach($albums as $album)
                               <article class="group relative overflow-hidden rounded-3xl border border-amber-900/10 bg-white/80 shadow-lg transition duration-300 hover:-translate-y-1 hover:shadow-2xl dark:border-amber-100/10 dark:bg-[#241a11]/90">

                                <a href="{{ route('family.photo-albums.show', $album->id) }}"
                                    class="absolute inset-0 z-10 rounded-3xl focus:outline-none focus:ring-2 focus:ring-amber-500"
                                    aria-label="Buka album {{ $album->name }}">
                                </a>

                                    {{-- COVER PLACEHOLDER --}}
                                    <div class="relative flex aspect-[16/10] items-center justify-center overflow-hidden bg-gradient-to-br from-[#6d5034] via-[#382719] to-[#1e160f]">

                                       @if($album->coverPhoto)
                                                <img
                                                    src="{{ route('family.photo-albums.photos.show', [
                                                        'albumId' => $album->id,
                                                        'photoId' => $album->coverPhoto->id,
                                                        'variant' => 'preview',
                                                    ]) }}"
                                                    alt="Cover {{ $album->name }}"
                                                    class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-105"
                                                    loading="lazy"
                                                >
                                            @else
                                                <div class="text-center text-amber-100/80 transition duration-500 group-hover:scale-110">
                                                    <div class="text-6xl">✧</div>
                                                    <p class="mt-3 text-xs font-medium uppercase tracking-[0.3em]">
                                                        Family Memories
                                                    </p>
                                                </div>
                                            @endif

                                        <div class="absolute left-4 top-4 rounded-full border border-white/20 bg-black/30 px-3 py-1.5 text-xs font-medium text-white backdrop-blur">
                                            {{ $album->familyGroup->name }}
                                        </div>

                                        <div class="absolute bottom-4 right-4 rounded-full bg-black/40 px-3 py-1.5 text-xs text-white backdrop-blur">
                                            {{ $album->photos_count }} Foto
                                        </div>
                                    </div>

                                    {{-- ALBUM INFO --}}
                                    <div class="p-5">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <h3 class="truncate text-lg font-bold text-stone-800 dark:text-amber-50">
                                                    {{ $album->name }}
                                                </h3>

                                                <p class="mt-1 text-xs text-stone-500 dark:text-amber-200/60">
                                                    {{ $album->event_date?->translatedFormat('d F Y') ?? 'Tanggal belum ditentukan' }}
                                                </p>
                                            </div>

                                            <span class="text-xl text-amber-700 dark:text-amber-400">✦</span>
                                        </div>

                                        @if($album->description)
                                            <p class="mt-3 line-clamp-2 text-sm leading-6 text-stone-600 dark:text-amber-100/70">
                                                {{ $album->description }}
                                            </p>
                                        @endif

                                        {{-- DELETE ALBUM --}}
                                        @if (auth('family')->check() &&
                                            app(\App\Services\Family\FamilyAccessService::class)->hasPermission(
                                                auth('family')->user(),
                                                'photo_albums.delete',
                                                $album->family_group_id
                                            ))
                                            <form
                                                action="{{ route('family.photo-albums.destroy', $album->id) }}"
                                                method="POST"
                                                onsubmit="return confirm('Yakin ingin menghapus album ini?')"
                                                class="mt-4"
                                            >
                                                @csrf
                                                @method('DELETE')

                                               <button type="submit"
                                                    class="family-leaf-button family-leaf-button--danger relative z-20">
                                                    Hapus Album
                                                </button>
                                            </form>
                                        @endif

                                        <div class="mt-5 border-t border-stone-200 pt-4 dark:border-amber-100/10">
                                            <span class="text-xs font-medium text-stone-500 dark:text-amber-200/60">
                                                Album keluarga
                                            </span>
                                        </div>
                                    </div>
                                </article>
                            @endforeach

                        </div>

                        <div class="mt-8">
                            {{ $albums->links() }}
                        </div>

                    @else
                        <div class="rounded-3xl border border-dashed border-amber-800/30 bg-white/50 px-6 py-16 text-center dark:bg-[#21180f]/60">
                            <div class="text-6xl text-amber-700 dark:text-amber-400">✧</div>

                            <h3 class="mt-5 text-xl font-bold text-stone-800 dark:text-amber-50">
                                Belum Ada Album Kenangan
                            </h3>

                            <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-stone-500 dark:text-amber-100/60">
                                Mulai kumpulkan foto dan momen berharga keluarga
                                dengan membuat album pertama.
                            </p>

                            @if($groups->isNotEmpty())
                                <a href="{{ route('family.photo-albums.create') }}"
                                    class="family-leaf-button mt-6 inline-flex items-center justify-center px-7 py-3 font-semibold text-white">
                                    Buat Album Pertama
                                </a>
                            @endif
                        </div>
                    @endif
                </section>

            </div>
        </main>
    </div>
</div>
@endsection