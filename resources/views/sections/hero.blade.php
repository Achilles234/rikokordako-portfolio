
{{-- Hero Section --}}
<section id="home" class="relative flex min-h-screen items-center overflow-hidden">

    <div class="mx-auto grid w-full max-w-7xl grid-cols-1 items-center gap-16 px-6 py-24 lg:grid-cols-2 lg:px-12">

        {{-- Hero Content --}}
        <div class="relative z-10">

            <p class="theme-accent mb-8 font-mono text-xs uppercase tracking-[0.3em]">
                {{ __('messages.hero_eyebrow') }}
            </p>

            <h1 class="theme-text-primary font-heading text-6xl font-extrabold leading-[1.05] tracking-tight md:text-7xl lg:text-8xl">
                RIKO<br>
                KORDAKO<span class="theme-accent">.</span>
            </h1>

            <div class="mt-8 max-w-xl">

                <p class="theme-text-primary text-xl font-medium leading-relaxed md:text-2xl">
                    {{ __('messages.hero_tagline') }}
                </p>

                <p class="theme-text-secondary mt-5 max-w-lg text-base leading-8 md:text-lg">
                    {{ __('messages.hero_description') }}
                </p>

            </div>

        </div>

        {{-- Seamless Cinematic Portrait --}}
        <div class="relative mx-auto w-full max-w-lg">

            <div class="relative aspect-[4/5] w-full">

                <img
                    src="{{ asset('images/rico.png') }}"
                    alt="Riko Kordako"
                    class="hero-portrait-image h-full w-full object-cover object-center"
                    fetchpriority="high"
                    decoding="async"
                    width="800"
                    height="1000"
                >

            </div>

        </div>

    </div>

</section>
