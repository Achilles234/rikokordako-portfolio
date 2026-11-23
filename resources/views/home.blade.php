
@extends('layouts.app')

@section('title', 'Home')

@section('meta_description', 'Riko Kordako — Technology, Systems, Business. A journey shaped by technology, real-world experience, and continuous learning.')

@section('content')


<main
    id="main-content"
    tabindex="-1"
    class="min-h-screen"
>

    @include('sections.hero')
    @include('sections.about')
    @include('sections.experience')
    @include('sections.projects')
    @include('sections.technology-lab')
    @include('sections.journal')
    @include('sections.contact')

</main>


@include('sections.footer')

@endsection
