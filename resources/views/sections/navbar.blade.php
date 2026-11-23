
{{-- Navigation Bar --}}
<header class="theme-header sticky top-0 z-50 border-b backdrop-blur-xl">

    <nav
        class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5 lg:px-12"
        aria-label="{{ __('messages.nav_main_aria') }}"
    >

        {{-- Brand --}}
        <a
            href="#home"
            aria-label="{{ __('messages.nav_brand_aria') }}"
            class="theme-text-primary font-heading text-xl font-extrabold tracking-tight transition-colors hover:text-accent-400 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
        >
            RK<span class="text-accent-500">.</span>
        </a>

        {{-- Desktop Navigation --}}
        <div class="hidden items-center gap-8 lg:flex">

            <a
                href="#home"
                class="font-mono text-[11px] uppercase tracking-widest text-accent-400 transition-colors hover:text-accent-500 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
            >
                {{ __('messages.nav_home') }}
            </a>

            <a
                href="#about"
                class="theme-text-secondary font-mono text-[11px] uppercase tracking-widest transition-colors hover:text-accent-400 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
            >
                {{ __('messages.nav_about') }}
            </a>

            <a
                href="#experience"
                class="theme-text-secondary font-mono text-[11px] uppercase tracking-widest transition-colors hover:text-accent-400 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
            >
                {{ __('messages.nav_experience') }}
            </a>

            <a
                href="#projects"
                class="theme-text-secondary font-mono text-[11px] uppercase tracking-widest transition-colors hover:text-accent-400 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
            >
                {{ __('messages.nav_projects') }}
            </a>

            <a
                href="#technology-lab"
                class="theme-text-secondary font-mono text-[11px] uppercase tracking-widest transition-colors hover:text-accent-400 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
            >
                {{ __('messages.nav_technology_lab') }}
            </a>

            <a
                href="#journal"
                class="theme-text-secondary font-mono text-[11px] uppercase tracking-widest transition-colors hover:text-accent-400 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
            >
                {{ __('messages.nav_journal') }}
            </a>

            <a
                href="#contact"
                class="theme-text-primary rounded-full border border-accent-500/60 px-5 py-2.5 font-mono text-[11px] uppercase tracking-widest transition-all duration-300 hover:border-accent-500 hover:bg-accent-600 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
            >
                {{ __('messages.nav_contact') }}
            </a>
                        {{-- Family Portal Access --}}
            <a
                href="{{ route('family.login') }}"
                class="inline-flex items-center gap-2 rounded-full border border-amber-500/50 bg-amber-500/10 px-5 py-2.5 font-mono text-[11px] font-semibold uppercase tracking-widest text-amber-600 transition-all duration-300 hover:border-amber-500 hover:bg-amber-500 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-amber-500 dark:text-amber-400"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="15"
                    height="15"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="10" cy="7" r="4"/>
                    <path d="M20 8v6"/>
                    <path d="M23 11h-6"/>
                </svg>

                {{ app()->getLocale() === 'id' ? 'Portal Keluarga' : 'Family Portal' }}
            </a>
            {{-- Desktop Theme Toggle --}}
            <button
                type="button"
                data-theme-toggle
                data-theme-label-light="{{ __('messages.theme_switch_to_light') }}"
                data-theme-label-dark="{{ __('messages.theme_switch_to_dark') }}"
                aria-label="{{ __('messages.theme_switch_to_light') }}"
                aria-pressed="false"
                title="{{ __('messages.theme_switch_to_light') }}"
                class="theme-toggle inline-flex h-9 w-9 items-center justify-center rounded-full border transition-colors focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
            >
                <svg
                    data-theme-icon="sun"
                    xmlns="http://www.w3.org/2000/svg"
                    width="18"
                    height="18"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <circle cx="12" cy="12" r="4"/>
                    <path d="M12 2v2"/>
                    <path d="M12 20v2"/>
                    <path d="m4.93 4.93 1.42 1.42"/>
                    <path d="m17.65 17.65 1.42 1.42"/>
                    <path d="M2 12h2"/>
                    <path d="M20 12h2"/>
                    <path d="m6.35 17.65-1.42 1.42"/>
                    <path d="m19.07 4.93-1.42 1.42"/>
                </svg>

                <svg
                    data-theme-icon="moon"
                    xmlns="http://www.w3.org/2000/svg"
                    width="18"
                    height="18"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M20.985 12.486a9 9 0 1 1-9.473-9.472c.405-.022.617.463.402.807a6.5 6.5 0 0 0 8.268 8.268c.344-.215.829-.003.803.397z"/>
                </svg>
            </button>

            <x-language-switcher />

        </div>

        {{-- Mobile Navigation --}}
        <details id="mobile-navigation" class="group relative lg:hidden">

            <summary
                aria-label="{{ __('messages.nav_menu_aria') }}"
                aria-controls="mobile-navigation-menu"
                class="theme-border theme-text-primary cursor-pointer list-none rounded-lg border px-4 py-2 font-mono text-xs uppercase tracking-widest transition-colors hover:border-accent-500 hover:text-accent-400 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
            >
                {{ __('messages.nav_menu') }}
            </summary>

            <div
                id="mobile-navigation-menu"
                class="theme-dropdown absolute right-0 top-full mt-3 flex w-64 flex-col gap-5 rounded-xl border p-6 shadow-2xl"
            >

                <a
                    href="#home"
                    class="font-mono text-xs uppercase tracking-widest text-accent-400 transition-colors hover:text-accent-500 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    {{ __('messages.nav_home') }}
                </a>

                <a
                    href="#about"
                    class="theme-text-secondary font-mono text-xs uppercase tracking-widest transition-colors hover:text-accent-400 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    {{ __('messages.nav_about') }}
                </a>

                <a
                    href="#experience"
                    class="theme-text-secondary font-mono text-xs uppercase tracking-widest transition-colors hover:text-accent-400 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    {{ __('messages.nav_experience') }}
                </a>

                <a
                    href="#projects"
                    class="theme-text-secondary font-mono text-xs uppercase tracking-widest transition-colors hover:text-accent-400 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    {{ __('messages.nav_projects') }}
                </a>

                <a
                    href="#technology-lab"
                    class="theme-text-secondary font-mono text-xs uppercase tracking-widest transition-colors hover:text-accent-400 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    {{ __('messages.nav_technology_lab') }}
                </a>

                <a
                    href="#journal"
                    class="theme-text-secondary font-mono text-xs uppercase tracking-widest transition-colors hover:text-accent-400 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    {{ __('messages.nav_journal') }}
                </a>

                <a
                    href="#contact"
                    class="theme-border border-t pt-5 font-mono text-xs uppercase tracking-widest text-accent-400 transition-colors hover:text-accent-500 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    {{ __('messages.nav_contact') }}
                </a>
                                {{-- Family Portal Access --}}
                <div class="theme-border border-t pt-5">
                    <a
                        href="{{ route('family.login') }}"
                        class="flex w-full items-center justify-center gap-3 rounded-xl border border-amber-500/50 bg-amber-500/10 px-5 py-3.5 font-mono text-xs font-semibold uppercase tracking-widest text-amber-600 transition-all duration-300 hover:border-amber-500 hover:bg-amber-500 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-amber-500 dark:text-amber-400"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="17"
                            height="17"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="10" cy="7" r="4"/>
                            <path d="M20 8v6"/>
                            <path d="M23 11h-6"/>
                        </svg>

                        {{ app()->getLocale() === 'id' ? 'Portal Keluarga' : 'Family Portal' }}
                    </a>
                </div>
                {{-- Mobile Theme Toggle --}}
                <div class="theme-border border-t pt-5">

                    <button
                        type="button"
                        data-theme-toggle
                        data-theme-label-light="{{ __('messages.theme_switch_to_light') }}"
                        data-theme-label-dark="{{ __('messages.theme_switch_to_dark') }}"
                        aria-label="{{ __('messages.theme_switch_to_light') }}"
                        aria-pressed="false"
                        title="{{ __('messages.theme_switch_to_light') }}"
                        class="theme-toggle flex w-full items-center gap-3 rounded-lg border px-4 py-3 transition-colors focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                    >
                        <svg
                            data-theme-icon="sun"
                            xmlns="http://www.w3.org/2000/svg"
                            width="18"
                            height="18"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <circle cx="12" cy="12" r="4"/>
                            <path d="M12 2v2"/>
                            <path d="M12 20v2"/>
                            <path d="m4.93 4.93 1.42 1.42"/>
                            <path d="m17.65 17.65 1.42 1.42"/>
                            <path d="M2 12h2"/>
                            <path d="M20 12h2"/>
                            <path d="m6.35 17.65-1.42 1.42"/>
                            <path d="m19.07 4.93-1.42 1.42"/>
                        </svg>

                        <svg
                            data-theme-icon="moon"
                            xmlns="http://www.w3.org/2000/svg"
                            width="18"
                            height="18"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M20.985 12.486a9 9 0 1 1-9.473-9.472c.405-.022.617.463.402.807a6.5 6.5 0 0 0 8.268 8.268c.344-.215.829-.003.803.397z"/>
                        </svg>

                        <span>{{ __('messages.theme_toggle_label') }}</span>
                    </button>

                </div>

                {{-- Mobile Language Switcher --}}
                <div class="theme-border border-t pt-5">
                    <x-language-switcher />
                </div>

            </div>

        </details>

    </nav>

</header>

{{-- Navigation Interaction --}}
@push('scripts')
<script>
    document.addEventListener('click', function (event) {

        const target = event.target;

        if (!(target instanceof Element)) {
            return;
        }

        // Handle language switch and preserve current section
        const languageLink = target.closest('[data-language-switch]');

        if (languageLink) {
            event.preventDefault();

            const targetUrl = new URL(languageLink.href);

            targetUrl.search = window.location.search;
            targetUrl.hash = window.location.hash;

            window.location.assign(targetUrl.toString());

            return;
        }

        // Handle mobile navigation
        const link = target.closest(
            '#mobile-navigation-menu a[href^="#"]'
        );

        if (!link) {
            return;
        }

        const menu = document.getElementById('mobile-navigation');

        if (menu) {
            menu.removeAttribute('open');
        }

    });
</script>
@endpush
