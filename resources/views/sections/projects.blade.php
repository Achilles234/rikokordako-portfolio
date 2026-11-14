
{{-- Projects Section --}}
<section
    id="projects"
    aria-labelledby="projects-heading"
    class="theme-border border-t theme-subtle py-28"
>

    <div class="mx-auto max-w-7xl px-6 lg:px-12">

        {{-- Section Heading --}}
        <div class="mb-16 grid grid-cols-1 gap-8 lg:grid-cols-12">

            <div class="lg:col-span-5">

                <p class="theme-accent font-mono text-xs uppercase tracking-[0.3em]">
                    {{ __('messages.projects_eyebrow') }}
                </p>

                <h2
                    id="projects-heading"
                    class="theme-text-primary mt-6 font-heading text-4xl font-bold md:text-5xl"
                >
                    {{ __('messages.projects_heading') }}<span class="theme-accent">.</span>
                </h2>

            </div>

            <div class="lg:col-span-7">

                <p class="theme-text-secondary max-w-2xl text-lg leading-8">
                    {{ __('messages.projects_intro') }}
                </p>

            </div>

        </div>

        {{-- Project 01: ATCS --}}
        <article
            aria-labelledby="project-atcs-heading"
            class="theme-border grid grid-cols-1 gap-10 border-t py-12 lg:grid-cols-2 lg:gap-16"
        >

            {{-- Project Visual --}}
            <figure class="theme-project-visual relative flex aspect-[16/10] items-center justify-center overflow-hidden rounded-xl border">

                <div class="theme-project-glow absolute inset-0"></div>

                <div class="relative p-8 text-center">

                    <p class="theme-accent font-mono text-xs uppercase tracking-[0.3em]">
                        {{ __('messages.project_atcs_category') }}
                    </p>

                    <p class="theme-text-primary mt-5 font-heading text-5xl font-extrabold tracking-tight md:text-6xl">
                        ATCS<span class="theme-accent">.</span>
                    </p>

                    <p class="theme-text-muted mt-3 text-sm">
                        {{ __('messages.project_atcs_visual_subtitle') }}
                    </p>

                    <div class="theme-accent-divider mx-auto mt-8 h-px w-20"></div>

                    <p class="theme-text-muted mt-5 font-mono text-xs tracking-widest">
                        SUBANG · 2022
                    </p>

                </div>

                <figcaption class="sr-only">
                    {{ __('messages.project_atcs_visual_caption') }}
                </figcaption>

            </figure>

            {{-- Project Details --}}
            <div class="flex flex-col justify-center">

                <p class="theme-accent font-mono text-xs uppercase tracking-widest">
                    {{ __('messages.project_atcs_location') }}
                </p>

                <h3
                    id="project-atcs-heading"
                    class="theme-text-primary mt-5 font-heading text-3xl font-bold"
                >
                    {{ __('messages.project_atcs_title') }}
                </h3>

                <p class="theme-text-secondary mt-6 text-sm leading-8">
                    {{ __('messages.project_atcs_description_1') }}
                </p>

                <p class="theme-text-secondary mt-4 text-sm leading-8">
                    {{ __('messages.project_atcs_description_2') }}
                </p>

                {{-- Technical Scope --}}
                <div class="mt-8 flex flex-wrap gap-2">

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

        </article>

        {{-- Project 02: Overclocking --}}
        <article
            aria-labelledby="project-overclocking-heading"
            class="theme-border grid grid-cols-1 gap-10 border-t py-12 lg:grid-cols-2 lg:gap-16"
        >

            {{-- Project Visual --}}
            <figure class="theme-project-visual relative flex aspect-[16/10] items-center justify-center overflow-hidden rounded-xl border">

                <div class="theme-project-glow absolute inset-0"></div>

                <div class="relative p-8 text-center">

                    <p class="theme-accent font-mono text-xs uppercase tracking-[0.3em]">
                        {{ __('messages.project_overclocking_category') }}
                    </p>

                    <p class="theme-text-primary mt-5 font-heading text-7xl font-extrabold tracking-tight md:text-8xl">
                        2<span class="theme-accent">ND</span>
                    </p>

                    <p class="theme-text-secondary mt-3 text-sm">
                        {{ __('messages.project_overclocking_class') }}
                    </p>

                    <div class="theme-accent-divider mx-auto mt-8 h-px w-20"></div>

                    <p class="theme-text-muted mt-5 font-mono text-xs tracking-widest">
                        YOGYAKARTA · CIRCA 2004
                    </p>

                </div>

                <figcaption class="sr-only">
                    {{ __('messages.project_overclocking_visual_caption') }}
                </figcaption>

            </figure>

            {{-- Project Details --}}
            <div class="flex flex-col justify-center">

                <p class="theme-accent font-mono text-xs uppercase tracking-widest">
                    {{ __('messages.project_overclocking_location') }}
                </p>

                <h3
                    id="project-overclocking-heading"
                    class="theme-text-primary mt-5 font-heading text-3xl font-bold"
                >
                    {{ __('messages.project_overclocking_title') }}
                </h3>

                <p class="theme-text-secondary mt-6 text-sm leading-8">
                    {{ __('messages.project_overclocking_description_1') }}
                </p>

                <p class="theme-text-secondary mt-4 text-sm leading-8">
                    {{ __('messages.project_overclocking_description_2') }}
                </p>

                {{-- Technical Scope --}}
                <div class="mt-8 flex flex-wrap gap-2">

                    <span class="theme-tag">
                        {{ __('messages.project_overclocking_scope_amd') }}
                    </span>

                    <span class="theme-tag">
                        {{ __('messages.project_overclocking_scope_overclocking') }}
                    </span>

                    <span class="theme-tag">
                        {{ __('messages.project_overclocking_scope_optimization') }}
                    </span>

                </div>

            </div>

        </article>

    </div>

</section>
