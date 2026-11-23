
{{-- Technology Lab Section --}}
<section
    id="technology-lab"
    aria-labelledby="technology-lab-heading"
    class="theme-border border-t theme-subtle py-28"
>

    <div class="mx-auto max-w-7xl px-6 lg:px-12">

        {{-- Section Heading --}}
        <div class="mb-16 grid grid-cols-1 gap-8 lg:grid-cols-12">

            <div class="lg:col-span-5">

                <p class="theme-accent font-mono text-xs uppercase tracking-[0.3em]">
                    {{ __('messages.technology_eyebrow') }}
                </p>

                <h2
                    id="technology-lab-heading"
                    class="theme-text-primary mt-6 font-heading text-4xl font-bold md:text-5xl"
                >
                    {{ __('messages.technology_heading_line1') }}<br>
                    {{ __('messages.technology_heading_line2') }}<span class="theme-accent">.</span>
                </h2>

            </div>

            <div class="lg:col-span-7">

                <p class="theme-text-secondary max-w-2xl text-lg leading-8">
                    {{ __('messages.technology_intro') }}
                </p>

            </div>

        </div>

        {{-- Capability Grid --}}
        <div class="theme-border grid grid-cols-1 gap-px overflow-hidden rounded-2xl border md:grid-cols-2 lg:grid-cols-3">

            {{-- 01 Infrastructure --}}
            <article class="theme-card p-8 transition-colors duration-300">

                <p class="theme-accent font-mono text-xs">
                    {{ __('messages.technology_infrastructure_category') }}
                </p>

                <h3 class="theme-text-primary mt-6 font-heading text-xl font-bold">
                    {{ __('messages.technology_infrastructure_title') }}
                </h3>

                <p class="theme-text-secondary mt-4 text-sm leading-7">
                    {{ __('messages.technology_infrastructure_description') }}
                </p>

                <div class="mt-6 flex flex-wrap gap-x-3 gap-y-2">
                    <span class="theme-text-secondary text-xs">{{ __('messages.technology_infrastructure_tag_hardware') }}</span>
                    <span class="theme-text-muted text-xs">/</span>
                    <span class="theme-text-secondary text-xs">Windows</span>
                    <span class="theme-text-muted text-xs">/</span>
                    <span class="theme-text-secondary text-xs">{{ __('messages.technology_infrastructure_tag_servers') }}</span>
                </div>

            </article>

            {{-- 02 Networking --}}
            <article class="theme-card p-8 transition-colors duration-300">

                <p class="theme-accent font-mono text-xs">
                    {{ __('messages.technology_networking_category') }}
                </p>

                <h3 class="theme-text-primary mt-6 font-heading text-xl font-bold">
                    {{ __('messages.technology_networking_title') }}
                </h3>

                <p class="theme-text-secondary mt-4 text-sm leading-7">
                    {{ __('messages.technology_networking_description') }}
                </p>

                <div class="mt-6 flex flex-wrap gap-x-3 gap-y-2">
                    <span class="theme-text-secondary text-xs">MikroTik</span>
                    <span class="theme-text-muted text-xs">/</span>
                    <span class="theme-text-secondary text-xs">TCP/IP</span>
                    <span class="theme-text-muted text-xs">/</span>
                    <span class="theme-text-secondary text-xs">LAN</span>
                </div>

            </article>

            {{-- 03 Surveillance --}}
            <article class="theme-card p-8 transition-colors duration-300">

                <p class="theme-accent font-mono text-xs">
                    {{ __('messages.technology_surveillance_category') }}
                </p>

                <h3 class="theme-text-primary mt-6 font-heading text-xl font-bold">
                    {{ __('messages.technology_surveillance_title') }}
                </h3>

                <p class="theme-text-secondary mt-4 text-sm leading-7">
                    {{ __('messages.technology_surveillance_description') }}
                </p>

                <div class="mt-6 flex flex-wrap gap-x-3 gap-y-2">
                    <span class="theme-text-secondary text-xs">Hikvision</span>
                    <span class="theme-text-muted text-xs">/</span>
                    <span class="theme-text-secondary text-xs">NVR</span>
                    <span class="theme-text-muted text-xs">/</span>
                    <span class="theme-text-secondary text-xs">{{ __('messages.technology_surveillance_tag_video_wall') }}</span>
                </div>

            </article>

            {{-- 04 Development --}}
            <article class="theme-card p-8 transition-colors duration-300">

                <p class="theme-accent font-mono text-xs">
                    {{ __('messages.technology_development_category') }}
                </p>

                <h3 class="theme-text-primary mt-6 font-heading text-xl font-bold">
                    {{ __('messages.technology_development_title') }}
                </h3>

                <p class="theme-text-secondary mt-4 text-sm leading-7">
                    {{ __('messages.technology_development_description') }}
                </p>

                <div class="mt-6 flex flex-wrap gap-x-3 gap-y-2">
                    <span class="theme-text-secondary text-xs">Laravel</span>
                    <span class="theme-text-muted text-xs">/</span>
                    <span class="theme-text-secondary text-xs">PHP</span>
                    <span class="theme-text-muted text-xs">/</span>
                    <span class="theme-text-secondary text-xs">MySQL</span>
                </div>

            </article>

            {{-- 05 Automation --}}
            <article class="theme-card p-8 transition-colors duration-300">

                <p class="theme-accent font-mono text-xs">
                    {{ __('messages.technology_automation_category') }}
                </p>

                <h3 class="theme-text-primary mt-6 font-heading text-xl font-bold">
                    {{ __('messages.technology_automation_title') }}
                </h3>

                <p class="theme-text-secondary mt-4 text-sm leading-7">
                    {{ __('messages.technology_automation_description') }}
                </p>

                <div class="mt-6 flex flex-wrap gap-x-3 gap-y-2">
                    <span class="theme-text-secondary text-xs">AT89C51</span>
                    <span class="theme-text-muted text-xs">/</span>
                    <span class="theme-text-secondary text-xs">{{ __('messages.technology_automation_tag_microcontroller') }}</span>
                </div>

            </article>

            {{-- 06 Digital Operations --}}
            <article class="theme-card p-8 transition-colors duration-300">

                <p class="theme-accent font-mono text-xs">
                    {{ __('messages.technology_digital_category') }}
                </p>

                <h3 class="theme-text-primary mt-6 font-heading text-xl font-bold">
                    {{ __('messages.technology_digital_title') }}
                </h3>

                <p class="theme-text-secondary mt-4 text-sm leading-7">
                    {{ __('messages.technology_digital_description') }}
                </p>

                <div class="mt-6 flex flex-wrap gap-x-3 gap-y-2">
                    <span class="theme-text-secondary text-xs">Microsoft Excel</span>
                    <span class="theme-text-muted text-xs">/</span>
                    <span class="theme-text-secondary text-xs">Git</span>
                </div>

            </article>

            {{-- 07 Hardware Engineering --}}
            <article class="theme-card p-8 transition-colors duration-300">

                <p class="theme-accent font-mono text-xs">
                    {{ __('messages.technology_hardware_category') }}
                </p>

                <h3 class="theme-text-primary mt-6 font-heading text-xl font-bold">
                    {{ __('messages.technology_hardware_title') }}
                </h3>

                <p class="theme-text-secondary mt-4 text-sm leading-7">
                    {{ __('messages.technology_hardware_description') }}
                </p>

                <div class="mt-6 flex flex-wrap gap-x-3 gap-y-2">
                    <span class="theme-text-secondary text-xs">{{ __('messages.technology_hardware_tag_assembly') }}</span>
                    <span class="theme-text-muted text-xs">/</span>
                    <span class="theme-text-secondary text-xs">{{ __('messages.technology_hardware_tag_tuning') }}</span>
                    <span class="theme-text-muted text-xs">/</span>
                    <span class="theme-text-secondary text-xs">{{ __('messages.technology_hardware_tag_overclocking') }}</span>
                </div>

            </article>

        </div>

    </div>

</section>
