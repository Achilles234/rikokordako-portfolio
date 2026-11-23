
{{-- Journal Section --}}
<section
    id="journal"
    aria-labelledby="journal-heading"
    class="theme-border border-t theme-subtle py-28"
>

    <div class="mx-auto max-w-7xl px-6 lg:px-12">

        {{-- Section Heading --}}
        <div class="mb-16 grid grid-cols-1 gap-8 lg:grid-cols-12">

            <div class="lg:col-span-5">

                <p class="theme-accent font-mono text-xs uppercase tracking-[0.3em]">
                    {{ __('messages.journal_eyebrow') }}
                </p>

                <h2
                    id="journal-heading"
                    class="theme-text-primary mt-6 font-heading text-4xl font-bold md:text-5xl"
                >
                    {{ __('messages.journal_heading_line1') }}<br>
                    {{ __('messages.journal_heading_line2') }}<span class="theme-accent">.</span>
                </h2>

            </div>

            <div class="lg:col-span-7">

                <p class="theme-text-secondary max-w-2xl text-lg leading-8">
                    {{ __('messages.journal_intro') }}
                </p>

            </div>

        </div>

        {{-- Journal Cards --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">

            {{-- Article 01 --}}
            <article class="theme-journal-card flex flex-col rounded-xl border p-8 transition-colors duration-300">

                <div class="flex items-center justify-between gap-3">

                    <p class="theme-accent font-mono text-xs uppercase tracking-wider">
                        {{ __('messages.journal_article_1_category') }}
                    </p>

                    <span class="theme-text-muted font-mono text-[10px]">
                        01
                    </span>

                </div>

                <h3 class="theme-text-primary mt-8 font-heading text-xl font-bold leading-relaxed">
                    {{ __('messages.journal_article_1_title') }}
                </h3>

                <p class="theme-text-secondary mt-5 flex-grow text-sm leading-7">
                    {{ __('messages.journal_article_1_description') }}
                </p>

                <div class="theme-border mt-8 border-t pt-5">

                    <p class="theme-text-secondary font-mono text-xs uppercase tracking-widest">
                        {{ __('messages.journal_coming_soon') }}
                    </p>

                </div>

            </article>

            {{-- Article 02 --}}
            <article class="theme-journal-card flex flex-col rounded-xl border p-8 transition-colors duration-300">

                <div class="flex items-center justify-between gap-3">

                    <p class="theme-accent font-mono text-xs uppercase tracking-wider">
                        {{ __('messages.journal_article_2_category') }}
                    </p>

                    <span class="theme-text-muted font-mono text-[10px]">
                        02
                    </span>

                </div>

                <h3 class="theme-text-primary mt-8 font-heading text-xl font-bold leading-relaxed">
                    {{ __('messages.journal_article_2_title') }}
                </h3>

                <p class="theme-text-secondary mt-5 flex-grow text-sm leading-7">
                    {{ __('messages.journal_article_2_description') }}
                </p>

                <div class="theme-border mt-8 border-t pt-5">

                    <p class="theme-text-secondary font-mono text-xs uppercase tracking-widest">
                        {{ __('messages.journal_coming_soon') }}
                    </p>

                </div>

            </article>

            {{-- Article 03 --}}
            <article class="theme-journal-card flex flex-col rounded-xl border p-8 transition-colors duration-300">

                <div class="flex items-center justify-between gap-3">

                    <p class="theme-accent font-mono text-xs uppercase tracking-wider">
                        {{ __('messages.journal_article_3_category') }}
                    </p>

                    <span class="theme-text-muted font-mono text-[10px]">
                        03
                    </span>

                </div>

                <h3 class="theme-text-primary mt-8 font-heading text-xl font-bold leading-relaxed">
                    {{ __('messages.journal_article_3_title') }}
                </h3>

                <p class="theme-text-secondary mt-5 flex-grow text-sm leading-7">
                    {{ __('messages.journal_article_3_description') }}
                </p>

                <div class="theme-border mt-8 border-t pt-5">

                    <p class="theme-text-secondary font-mono text-xs uppercase tracking-widest">
                        {{ __('messages.journal_coming_soon') }}
                    </p>

                </div>

            </article>

        </div>

    </div>

</section>
