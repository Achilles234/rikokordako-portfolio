
{{-- Footer --}}
<footer class="border-t border-white/10 bg-brand-950">

    <div class="mx-auto max-w-7xl px-6 py-10 lg:px-12">

        <div class="flex flex-col gap-8 md:flex-row md:items-center md:justify-between">

            {{-- Brand --}}
            <div>

                <a
                    href="#home"
                    aria-label="Riko Kordako - Back to top"
                    class="font-heading text-sm font-bold tracking-tight text-white transition-colors hover:text-accent-400 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    RIKO KORDAKO<span class="text-accent-500">.</span>
                </a>

                <p class="mt-2 font-mono text-xs text-gray-500">
                    Technology. Systems. Business.
                </p>

            </div>

            {{-- Footer Navigation --}}
            <nav
                aria-label="Footer navigation"
                class="flex flex-wrap gap-x-6 gap-y-3"
            >

                <a
                    href="#home"
                    class="text-xs text-gray-400 transition-colors hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    Home
                </a>

                <a
                    href="#about"
                    class="text-xs text-gray-400 transition-colors hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    About
                </a>

                <a
                    href="#experience"
                    class="text-xs text-gray-400 transition-colors hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    Experience
                </a>

                <a
                    href="#projects"
                    class="text-xs text-gray-400 transition-colors hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    Projects
                </a>

                <a
                    href="#technology-lab"
                    class="text-xs text-gray-400 transition-colors hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    Technology
                </a>

                <a
                    href="#journal"
                    class="text-xs text-gray-400 transition-colors hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    Journal
                </a>

                <a
                    href="#contact"
                    class="text-xs text-gray-400 transition-colors hover:text-accent-400 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                >
                    Contact
                </a>

            </nav>

        </div>

        {{-- Copyright --}}
        <div class="mt-8 border-t border-white/10 pt-6">

            <p class="font-mono text-xs leading-6 text-gray-600">
                © {{ date('Y') }} Riko Kordako. All rights reserved.
            </p>

        </div>

    </div>

</footer>
