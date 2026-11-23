
@extends('layouts.family')

@section('title', ($isEnglish = session('family_locale', 'id') === 'en')
    ? 'Edit Group — Family Portal'
    : 'Edit Grup — Family Portal')

@section('content')

@php
    $isEnglish = session('family_locale', 'id') === 'en';
@endphp

<div class="family-app-layout flex h-screen overflow-hidden"
    style="background: var(--family-background);">

    @include('family.partials.sidebar')

    <div class="flex min-h-0 min-w-0 flex-1 flex-col">

        @include('family.partials.topbar')

        <main id="family-main"
            class="family-main-content min-h-0 min-w-0 flex-1 overflow-y-auto px-4 py-8 sm:px-6 lg:px-10">

            <div class="mx-auto max-w-5xl">

    <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-10 lg:py-10">

        <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm">
            <a href="{{ route('family.dashboard') }}"
                class="font-medium text-blue-500 hover:underline">
                Dashboard
            </a>

            <span style="color: var(--family-text-secondary);">/</span>

            <a href="{{ route('family.groups.show', $group->id) }}"
                class="font-medium text-blue-500 hover:underline">
                {{ $group->name }}
            </a>

            <span style="color: var(--family-text-secondary);">/</span>

            <span style="color: var(--family-text-secondary);">
                {{ $isEnglish ? 'Edit' : 'Edit' }}
            </span>
        </nav>

        <div class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight sm:text-3xl"
                style="color: var(--family-text-primary);">
                {{ $isEnglish ? 'Edit Family Group' : 'Edit Family Group' }}
            </h1>

            <p class="mt-2 text-sm"
                style="color: var(--family-text-secondary);">
                {{ $isEnglish
                    ? 'Update the basic information of this family group.'
                    : 'Perbarui informasi dasar Family Group ini.' }}
            </p>
        </div>

        <section class="rounded-2xl border p-5 sm:p-7"
            style="background: var(--family-surface); border-color: var(--family-border);">

            <form method="POST"
                action="{{ route('family.groups.update', $group->id) }}"
                class="space-y-6">

                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="mb-2 block text-sm font-semibold"
                        style="color: var(--family-text-primary);">
                        {{ $isEnglish ? 'Group Name' : 'Nama Grup' }}
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $group->name) }}"
                        required
                        maxlength="255"
                        autocomplete="off"
                        class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                        style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);">

                    @error('name')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="mb-2 block text-sm font-semibold"
                        style="color: var(--family-text-primary);">
                        {{ $isEnglish ? 'Description' : 'Deskripsi' }}
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="5"
                        class="w-full resize-y rounded-xl border px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                        style="background: var(--family-background); border-color: var(--family-border); color: var(--family-text-primary);"
                        placeholder="{{ $isEnglish ? 'Describe this family group...' : 'Tuliskan deskripsi Family Group...' }}">{{ old('description', $group->description) }}</textarea>

                    @error('description')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-xl border p-4"
                    style="background: var(--family-background); border-color: var(--family-border);">

                    <p class="text-sm font-semibold"
                        style="color: var(--family-text-primary);">
                        {{ $isEnglish ? 'Information' : 'Informasi' }}
                    </p>

                    <p class="mt-1 text-sm leading-6"
                        style="color: var(--family-text-secondary);">
                        {{ $isEnglish
                            ? 'Only the group name and description can be changed here. Group type, status, and slug are managed separately.'
                            : 'Di sini hanya nama dan deskripsi grup yang dapat diubah. Tipe grup, status, dan slug dikelola secara terpisah.' }}
                    </p>
                </div>

                @if ($errors->has('group') || $errors->has('data'))
                    <div class="rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-500">
                        {{ $errors->first('group') ?: $errors->first('data') }}
                    </div>
                @endif

                <div class="flex flex-col-reverse gap-3 border-t pt-5 sm:flex-row sm:justify-end"
                    style="border-color: var(--family-border);">

                    <a href="{{ route('family.groups.show', $group->id) }}"
                        class="inline-flex items-center justify-center rounded-xl border px-5 py-3 text-sm font-semibold transition hover:bg-blue-500/5"
                        style="border-color: var(--family-border); color: var(--family-text-primary);">
                        {{ $isEnglish ? 'Cancel' : 'Batal' }}
                    </a>

                    <button type="submit"
                        class="family-leaf-button">
                        {{ $isEnglish ? 'Save Changes' : 'Simpan Perubahan' }}
                    </button>
                </div>

            </form>
        </section>

                </div>
        </main>
    </div>
</div>

@endsection
