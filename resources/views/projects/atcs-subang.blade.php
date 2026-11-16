
@extends('layouts.app')

@section('title', app()->getLocale() === 'id'
    ? 'ATCS Subang — Studi Kasus'
    : 'ATCS Subang — Case Study'
)

@section('meta_description', app()->getLocale() === 'id'
    ? 'Dokumentasi proyek Area Traffic Control System (ATCS) Kabupaten Subang tahun 2022.'
    : 'Project documentation of the Area Traffic Control System (ATCS) in Subang Regency, 2022.'
)

@section('content')

@php
    $isId = app()->getLocale() === 'id';
@endphp

<main id="main-content" class="min-h-screen">

    {{-- Case Study Header --}}
    <section class="theme-border border-b theme-subtle">

        <div class="mx-auto max-w-7xl px-6 pb-16 pt-32 lg:px-12 lg:pb-20">

            <a
                href="{{ url('/' . app()->getLocale() . '#projects') }}"
                class="theme-text-secondary inline-flex items-center gap-3 font-mono text-xs uppercase tracking-widest transition-colors hover:theme-accent focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="h-4 w-4"
                    aria-hidden="true"
                >
                    <path d="m19 12-14 0"></path>
                    <path d="m12 19-7-7 7-7"></path>
                </svg>

                {{ $isId ? 'Kembali ke Proyek' : 'Back to Projects' }}
            </a>

            <div class="mt-16 grid grid-cols-1 gap-10 lg:grid-cols-12 lg:items-end">

                <div class="lg:col-span-8">

                    <p class="theme-accent font-mono text-xs uppercase tracking-[0.3em]">
                        {{ $isId ? 'Studi Kasus Proyek' : 'Project Case Study' }}
                    </p>

                    <h1 class="theme-text-primary mt-6 font-heading text-4xl font-extrabold tracking-tight md:text-6xl">
                        ATCS Subang<span class="theme-accent">.</span>
                    </h1>

                    <p class="theme-text-secondary mt-6 max-w-2xl text-lg leading-8">
                       
                  {{ $isId
                        ? 'Dokumentasi keterlibatan teknis dalam proyek Area Traffic Control System (ATCS) Kabupaten Subang, mencakup perancangan dashboard web berbasis Laravel, instalasi dan konfigurasi infrastruktur server serta ruang monitoring, konfigurasi video wall, dan supervisi instalasi di lapangan.'
                        : 'Documentation of technical involvement in the Area Traffic Control System (ATCS) project in Subang Regency, covering Laravel-based web dashboard design, server and monitoring-room infrastructure installation and configuration, video wall configuration, and field installation supervision.'
                    }}

                    </p>

                </div>

                <div class="lg:col-span-4 lg:text-right">

                    <p class="theme-text-muted font-mono text-xs uppercase tracking-widest">
                        {{ $isId ? 'Lokasi Proyek' : 'Project Location' }}
                    </p>

                    <p class="theme-text-primary mt-3 font-heading text-xl font-semibold">
                        Subang, Indonesia
                    </p>

                    <p class="theme-text-secondary mt-2 font-mono text-xs tracking-widest">
                        2022
                    </p>

                </div>

            </div>

        </div>

    </section>

    {{-- Main Documentation --}}
    <section
        aria-labelledby="atcs-documentation-heading"
        class="py-20 lg:py-28"
    >

        <div class="mx-auto max-w-7xl px-6 lg:px-12">

            <div class="grid grid-cols-1 gap-12 lg:grid-cols-12 lg:gap-16">

                {{-- Main Image --}}
                <div class="lg:col-span-8">

                    <figure>

                        <div class="overflow-hidden rounded-xl border theme-border">

                            <img
                                src="{{ asset('images/projects/atcs-subang/monitoring-center.webp') }}"
                                alt="{{ $isId
                                    ? 'Ruang monitoring ATCS Subang dengan video wall CCTV dan workstation operator'
                                    : 'ATCS Subang monitoring room featuring the CCTV video wall and operator workstations'
                                }}"
                                class="h-auto w-full object-cover"
                                fetchpriority="high"
                                decoding="async"
                                width="1200"
                                height="750"
                            >

                        </div>

                        <figcaption class="theme-text-muted mt-4 text-xs leading-6">
                            {{ $isId
                                ? 'Dokumentasi ruang monitoring ATCS Subang, menampilkan video wall dan workstation pemantauan.'
                                : 'ATCS Subang monitoring center documentation, featuring the video wall and monitoring workstations.'
                            }}
                        </figcaption>

                    </figure>

                </div>

                {{-- Project Information --}}
                <aside class="lg:col-span-4">

                    <p class="theme-accent font-mono text-xs uppercase tracking-[0.3em]">
                        {{ $isId ? 'Gambaran Proyek' : 'Project Overview' }}
                    </p>

                    <h2
                        id="atcs-documentation-heading"
                        class="theme-text-primary mt-5 font-heading text-2xl font-bold"
                    >
                        {{ __('messages.project_atcs_title') }}
                    </h2>

                    <p class="theme-text-secondary mt-6 text-sm leading-8">
                        {{ __('messages.project_atcs_description_1') }}
                    </p>

                    <p class="theme-text-secondary mt-4 text-sm leading-8">
                        {{ __('messages.project_atcs_description_2') }}
                    </p>

                    <div class="theme-border mt-8 border-t pt-7">

                        <p class="theme-text-muted font-mono text-xs uppercase tracking-widest">
                            {{ $isId ? 'Lingkup Teknis' : 'Technical Scope' }}
                        </p>

                        <div class="mt-5 flex flex-wrap gap-2">

                            <span class="theme-tag">
                                {{ __('messages.project_atcs_scope_dashboard') }}
                            </span>

                            <span class="theme-tag">
                                {{ __('messages.project_atcs_scope_cctv') }}
                            </span>

                            <span class="theme-tag">
                                {{ __('messages.project_atcs_scope_network') }}
                            </span>

                            <span class="theme-tag">
                                {{ __('messages.project_atcs_scope_video_wall') }}
                            </span>

                        </div>

                    </div>

                </aside>

            </div>

        </div>

    </section>

   
    {{-- Inauguration Documentation --}}
    <section
        aria-labelledby="atcs-inauguration-heading"
        class="theme-border border-t theme-subtle py-20 lg:py-28"
    >

        <div class="mx-auto max-w-7xl px-6 lg:px-12">

            <div class="max-w-3xl">

                <p class="theme-accent font-mono text-xs uppercase tracking-[0.3em]">
                    {{ $isId ? 'Dokumentasi Lapangan' : 'Project Documentation' }}
                </p>

                <h2
                    id="atcs-inauguration-heading"
                    class="theme-text-primary mt-5 font-heading text-3xl font-bold md:text-4xl"
                >
                    {{ $isId ? 'Peresmian dan Ruang Monitoring' : 'Inauguration and Monitoring Center' }}<span class="theme-accent">.</span>
                </h2>

                <p class="theme-text-secondary mt-6 text-sm leading-8">
                    {{ $isId
                        ? 'Dokumentasi kegiatan peresmian dan fasilitas ruang monitoring ATCS Kabupaten Subang, termasuk video wall, workstation operator, serta suasana kegiatan di lokasi proyek.'
                        : 'Documentation of the inauguration and ATCS monitoring facilities in Subang Regency, including the video wall, operator workstations, and moments from the project site.'
                    }}
                </p>

            </div>

            {{-- Main Inauguration Photo --}}
            <figure class="theme-border mt-12 overflow-hidden rounded-xl border">

                <img
                    src="{{ asset('images/projects/atcs-subang/inauguration-1.webp') }}"
                    alt="{{ $isId
                        ? 'Dokumentasi utama kegiatan peresmian fasilitas ATCS Kabupaten Subang'
                        : 'Main documentation of the ATCS facility inauguration in Subang Regency'
                    }}"
                    class="h-auto w-full object-cover"
                    loading="lazy"
                    decoding="async"
                    width="1200"
                    height="800"
                >

                <figcaption class="theme-text-muted p-5 text-xs leading-6">
                    {{ $isId
                        ? 'Dokumentasi kegiatan peresmian fasilitas ATCS Kabupaten Subang.'
                        : 'Documentation of the ATCS facility inauguration in Subang Regency.'
                    }}
                </figcaption>

            </figure>

            {{-- Supporting Photo Gallery --}}
            <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">

                <figure class="theme-border overflow-hidden rounded-xl border">

                    <img
                        src="{{ asset('images/projects/atcs-subang/inauguration-2.webp') }}"
                        alt="{{ $isId
                            ? 'Dokumentasi kegiatan peresmian ATCS Subang, foto kedua'
                            : 'ATCS Subang inauguration documentation, photo two'
                        }}"
                        class="h-auto w-full object-cover"
                        loading="lazy"
                        decoding="async"
                        width="1200"
                        height="800"
                    >

                </figure>

                <figure class="theme-border overflow-hidden rounded-xl border">

                    <img
                        src="{{ asset('images/projects/atcs-subang/inauguration-3.webp') }}"
                        alt="{{ $isId
                            ? 'Dokumentasi kegiatan peresmian ATCS Subang, foto ketiga'
                            : 'ATCS Subang inauguration documentation, photo three'
                        }}"
                        class="h-auto w-full object-cover"
                        loading="lazy"
                        decoding="async"
                        width="1200"
                        height="800"
                    >

                </figure>

                <figure class="theme-border overflow-hidden rounded-xl border">

                    <img
                        src="{{ asset('images/projects/atcs-subang/inauguration-4.webp') }}"
                        alt="{{ $isId
                            ? 'Dokumentasi fasilitas dan kegiatan ATCS Subang, foto keempat'
                            : 'ATCS Subang facilities and project documentation, photo four'
                        }}"
                        class="h-auto w-full object-cover"
                        loading="lazy"
                        decoding="async"
                        width="1200"
                        height="800"
                    >

                </figure>

                <figure class="theme-border overflow-hidden rounded-xl border">

                    <img
                        src="{{ asset('images/projects/atcs-subang/inauguration-5.webp') }}"
                        alt="{{ $isId
                            ? 'Dokumentasi fasilitas dan kegiatan ATCS Subang, foto kelima'
                            : 'ATCS Subang facilities and project documentation, photo five'
                        }}"
                        class="h-auto w-full object-cover"
                        loading="lazy"
                        decoding="async"
                        width="1200"
                        height="800"
                    >

                </figure>


            <figure class="theme-border overflow-hidden rounded-xl border">

                <img
                    src="{{ asset('images/projects/atcs-subang/inauguration-6.webp') }}"
                    alt="{{ $isId
                        ? 'Dokumentasi tambahan kegiatan peresmian ATCS Subang, foto keenam'
                        : 'Additional ATCS Subang inauguration documentation, photo six'
                    }}"
                    class="h-auto w-full object-cover"
                    loading="lazy"
                    decoding="async"
                    width="1200"
                    height="800"
                >

            </figure>

            <figure class="theme-border overflow-hidden rounded-xl border">

                <img
                    src="{{ asset('images/projects/atcs-subang/inauguration-7.webp') }}"
                    alt="{{ $isId
                        ? 'Dokumentasi tambahan kegiatan peresmian ATCS Subang, foto ketujuh'
                        : 'Additional ATCS Subang inauguration documentation, photo seven'
                    }}"
                    class="h-auto w-full object-cover"
                    loading="lazy"
                    decoding="async"
                    width="1200"
                    height="800"
                >

            </figure>


            </div>

        </div>

    </section>


    {{-- Back to Projects --}}
    <section class="py-16">

        <div class="mx-auto max-w-7xl px-6 text-center lg:px-12">

            <a
                href="{{ url('/' . app()->getLocale() . '#projects') }}"
                class="inline-flex items-center gap-3 font-mono text-xs font-semibold uppercase tracking-widest theme-accent transition-all duration-300 hover:gap-5 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
            >
                {{ $isId ? 'Kembali ke Semua Proyek' : 'Explore All Projects' }}

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="h-4 w-4"
                    aria-hidden="true"
                >
                    <path d="M5 12h14"></path>
                    <path d="m12 5 7 7-7 7"></path>
                </svg>
            </a>

        </div>

    </section>

</main>

@endsection
