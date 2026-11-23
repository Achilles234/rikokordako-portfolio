
{{-- Footer --}}
<footer class="theme-border border-t theme-subtle">

    <div class="mx-auto max-w-7xl px-6 py-10 lg:px-12">

        <div class="flex flex-col gap-8 md:flex-row md:items-center md:justify-between">

            {{-- Brand --}}
            <div>

                <a
                    href="#home"
                    aria-label="{{ __('messages.footer_brand_aria') }}"
                    class="theme-text-primary inline-block font-heading text-sm font-bold tracking-tight transition-colors hover:theme-accent focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    RIKO KORDAKO<span class="theme-accent">.</span>
                </a>

                <p class="theme-text-secondary mt-2 font-mono text-xs">
                    Technology. Systems. Business.
                </p>

            </div>

            {{-- Footer Navigation --}}
            <nav
                aria-label="{{ __('messages.footer_nav_aria') }}"
                class="flex flex-wrap gap-x-6 gap-y-3"
            >

                <a
                    href="#home"
                    class="theme-text-secondary text-xs transition-colors hover:theme-text-primary focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    {{ __('messages.footer_nav_home') }}
                </a>

                <a
                    href="#about"
                    class="theme-text-secondary text-xs transition-colors hover:theme-text-primary focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    {{ __('messages.footer_nav_about') }}
                </a>

                <a
                    href="#experience"
                    class="theme-text-secondary text-xs transition-colors hover:theme-text-primary focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    {{ __('messages.footer_nav_experience') }}
                </a>

                <a
                    href="#projects"
                    class="theme-text-secondary text-xs transition-colors hover:theme-text-primary focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    {{ __('messages.footer_nav_projects') }}
                </a>

                <a
                    href="#technology-lab"
                    class="theme-text-secondary text-xs transition-colors hover:theme-text-primary focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    {{ __('messages.footer_nav_technology') }}
                </a>

                <a
                    href="#journal"
                    class="theme-text-secondary text-xs transition-colors hover:theme-text-primary focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    {{ __('messages.footer_nav_journal') }}
                </a>

                <a
                    href="#contact"
                    class="theme-text-secondary text-xs transition-colors hover:theme-accent focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    {{ __('messages.footer_nav_contact') }}
                </a>

            </nav>

        </div>

        {{-- Copyright --}}
        <div class="theme-border mt-8 border-t pt-6">

            <p class="theme-text-secondary font-mono text-xs leading-6">
                © {{ date('Y') }} Riko Kordako. {{ __('messages.footer_rights') }}
            </p>

        </div>

    </div>

</footer>
