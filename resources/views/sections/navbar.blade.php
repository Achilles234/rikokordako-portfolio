
{{-- Navigation Bar --}}
<header class="sticky top-0 z-50 border-b border-white/10 bg-brand-950/90 backdrop-blur-xl">

    <nav
        class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5 lg:px-12"
        aria-label="Main navigation"
    >

        {{-- Brand --}}
        <a
            href="#home"
            aria-label="Riko Kordako - Home"
            class="font-heading text-xl font-extrabold tracking-tight text-white transition-colors hover:text-accent-400 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
        >
            RK<span class="text-accent-500">.</span>
        </a>

        {{-- Desktop Navigation --}}
        <div class="hidden items-center gap-8 lg:flex">

            <a
                href="#home"
                class="font-mono text-[11px] uppercase tracking-widest text-accent-400 transition-colors hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
            >
                Home
            </a>

            <a
                href="#about"
                class="font-mono text-[11px] uppercase tracking-widest text-gray-400 transition-colors hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
            >
                About
            </a>

            <a
                href="#experience"
                class="font-mono text-[11px] uppercase tracking-widest text-gray-400 transition-colors hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
            >
                Experience
            </a>

            <a
                href="#projects"
                class="font-mono text-[11px] uppercase tracking-widest text-gray-400 transition-colors hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
            >
                Projects
            </a>

            <a
                href="#technology-lab"
                class="font-mono text-[11px] uppercase tracking-widest text-gray-400 transition-colors hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
            >
                Technology Lab
            </a>

            <a
                href="#journal"
                class="font-mono text-[11px] uppercase tracking-widest text-gray-400 transition-colors hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
            >
                Journal
            </a>

            <a
                href="#contact"
                class="rounded-full border border-accent-500/60 px-5 py-2.5 font-mono text-[11px] uppercase tracking-widest text-white transition-all duration-300 hover:border-accent-500 hover:bg-accent-600 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
            >
                Contact
            </a>

        </div>

        {{-- Mobile Navigation --}}
        <details id="mobile-navigation" class="group relative lg:hidden">

            <summary
                aria-label="Toggle navigation menu"
                aria-controls="mobile-navigation-menu"
                class="cursor-pointer list-none rounded-lg border border-white/15 px-4 py-2 font-mono text-xs uppercase tracking-widest text-white transition-colors hover:border-accent-500 hover:text-accent-400 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
            >
                Menu
            </summary>

            <div
                id="mobile-navigation-menu"
                class="absolute right-0 top-full mt-3 flex w-64 flex-col gap-5 rounded-xl border border-white/10 bg-brand-900 p-6 shadow-2xl"
            >

                <a
                    href="#home"
                    class="font-mono text-xs uppercase tracking-widest text-accent-400 transition-colors hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    Home
                </a>

                <a
                    href="#about"
                    class="font-mono text-xs uppercase tracking-widest text-gray-300 transition-colors hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    About
                </a>

                <a
                    href="#experience"
                    class="font-mono text-xs uppercase tracking-widest text-gray-300 transition-colors hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    Experience
                </a>

                <a
                    href="#projects"
                    class="font-mono text-xs uppercase tracking-widest text-gray-300 transition-colors hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    Projects
                </a>

                <a
                    href="#technology-lab"
                    class="font-mono text-xs uppercase tracking-widest text-gray-300 transition-colors hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    Technology Lab
                </a>

                <a
                    href="#journal"
                    class="font-mono text-xs uppercase tracking-widest text-gray-300 transition-colors hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    Journal
                </a>

                <a
                    href="#contact"
                    class="border-t border-white/10 pt-5 font-mono text-xs uppercase tracking-widest text-accent-400 transition-colors hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    Contact
                </a>

            </div>

        </details>

    </nav>

</header>

{{-- Close Mobile Menu After Navigation --}}
@push('scripts')
<script>
    document.addEventListener('click', function (event) {

        const link = event.target.closest(
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
