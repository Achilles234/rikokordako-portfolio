
{{-- Hero Section --}}
<section id="home" class="relative flex min-h-screen items-center overflow-hidden">

    <div class="mx-auto grid w-full max-w-7xl grid-cols-1 items-center gap-16 px-6 py-24 lg:grid-cols-2 lg:px-12">

        {{-- Hero Content --}}
        <div class="relative z-10">

            <p class="mb-8 font-mono text-xs uppercase tracking-[0.3em] text-accent-400">
                Technology · Systems · Business
            </p>

            <h1 class="font-heading text-6xl font-extrabold leading-[1.05] tracking-tight md:text-7xl lg:text-8xl">
                RIKO<br>
                KORDAKO<span class="text-accent-500">.</span>
            </h1>

            <div class="mt-8 max-w-xl">
                <p class="text-xl font-medium leading-relaxed text-gray-200 md:text-2xl">
                    Technology. Systems. Business.
                </p>

                <p class="mt-5 max-w-lg text-base leading-8 text-gray-400 md:text-lg">
                    A journey shaped by technology, real-world experience, and continuous learning.
                </p>
            </div>

        </div>

        {{-- Seamless Cinematic Portrait --}}
        <div class="relative mx-auto w-full max-w-lg">

            <div class="relative aspect-[4/5] w-full">

               <img
                    src="{{ asset('images/rico.png') }}"
                    alt="Riko Kordako"
                    class="h-full w-full object-cover object-center"
                    fetchpriority="high"
                    decoding="async"
                    width="800"
                    height="1000"
                >

                {{-- Top Blend --}}
                <div class="pointer-events-none absolute inset-x-0 top-0 h-1/4 bg-gradient-to-b from-brand-950 to-transparent"></div>

                {{-- Left Blend --}}
                <div class="pointer-events-none absolute inset-y-0 left-0 w-1/3 bg-gradient-to-r from-brand-950 to-transparent"></div>

                {{-- Right Blend --}}
                <div class="pointer-events-none absolute inset-y-0 right-0 w-1/3 bg-gradient-to-l from-brand-950 to-transparent"></div>

                {{-- Bottom Blend --}}
                <div class="pointer-events-none absolute inset-x-0 bottom-0 h-1/3 bg-gradient-to-t from-brand-950 to-transparent"></div>

            </div>

        </div>

    </div>

</section>
