
{{-- About Section --}}
<section
    id="about"
    aria-labelledby="about-heading"
    class="relative border-t theme-border theme-subtle py-28"
>

    <div class="mx-auto max-w-7xl px-6 lg:px-12">

        <div class="grid grid-cols-1 gap-16 lg:grid-cols-12">

            {{-- Section Introduction --}}
            <div class="lg:col-span-4">

                <p class="theme-accent font-mono text-xs uppercase tracking-[0.3em]">
                    {{ __('messages.about_eyebrow') }}
                </p>

                <h2
                    id="about-heading"
                    class="theme-text-primary mt-6 font-heading text-4xl font-bold leading-tight md:text-5xl"
                >
                    {{ __('messages.about_heading_line1') }}<br>
                    {{ __('messages.about_heading_line2') }}<span class="theme-accent">.</span>
                </h2>

                {{-- Professional Journey --}}
                <div class="mt-12 border-l-2 border-[var(--accent-primary)] pl-5">

                    <p class="theme-text-primary font-heading text-4xl font-extrabold">
                        20<span class="theme-accent">+</span>
                    </p>

                    <p class="theme-text-secondary mt-2 font-mono text-xs uppercase tracking-widest">
                        {{ __('messages.about_experience_label') }}
                    </p>

                    <p class="theme-text-secondary mt-3 max-w-xs text-sm leading-7">
                        {{ __('messages.about_experience_description') }}
                    </p>

                </div>

            </div>

            {{-- About Content --}}
            <div class="lg:col-span-8">

                <p class="theme-text-primary text-xl font-medium leading-relaxed md:text-2xl">
                    {{ __('messages.about_intro') }}
                </p>

                <p class="theme-text-secondary mt-8 text-base leading-8">
                    {{ __('messages.about_paragraph_1') }}
                </p>

                <p class="theme-text-secondary mt-6 text-base leading-8">
                    {{ __('messages.about_paragraph_2') }}
                </p>

                <p class="theme-text-secondary mt-6 text-base leading-8">
                    {{ __('messages.about_paragraph_3') }}
                </p>

                {{-- Areas of Focus --}}
                <div class="mt-10 flex flex-wrap gap-3">

                    <span class="theme-border theme-text-secondary rounded-full border px-4 py-2 font-mono text-xs">
                        {{ __('messages.about_focus_infrastructure') }}
                    </span>

                    <span class="theme-border theme-text-secondary rounded-full border px-4 py-2 font-mono text-xs">
                        {{ __('messages.about_focus_network') }}
                    </span>

                    <span class="theme-border theme-text-secondary rounded-full border px-4 py-2 font-mono text-xs">
                        {{ __('messages.about_focus_cctv') }}
                    </span>

                    <span class="theme-border theme-text-secondary rounded-full border px-4 py-2 font-mono text-xs">
                        {{ __('messages.about_focus_business') }}
                    </span>

                </div>

            </div>

        </div>

    </div>

</section>
