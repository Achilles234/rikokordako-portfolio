
{{-- Contact Section --}}
<section
    id="contact"
    aria-labelledby="contact-heading"
    class="relative overflow-hidden theme-border border-t theme-subtle py-28"
>
    <div class="mx-auto max-w-7xl px-6 lg:px-12">

        <div class="grid grid-cols-1 gap-12 lg:grid-cols-12 lg:items-end">

            {{-- Contact Heading --}}
            <div class="lg:col-span-7">

                <p class="theme-accent font-mono text-xs uppercase tracking-[0.3em]">
                    {{ __('messages.contact_eyebrow') }}
                </p>

                <h2
                    id="contact-heading"
                    class="theme-text-primary mt-8 font-heading text-5xl font-extrabold leading-tight md:text-7xl"
                >
                    {{ __('messages.contact_heading_line1') }}
                    <br>
                    <span class="theme-accent">
                        {{ __('messages.contact_heading_line2') }}
                    </span>
                </h2>

                <p class="theme-text-secondary mt-8 max-w-xl text-base leading-8 md:text-lg">
                    {{ __('messages.contact_intro') }}
                </p>

                {{-- Availability Status --}}
                <div class="theme-border mt-8 inline-flex items-center gap-3 rounded-full border px-4 py-2">

                    <span
                        class="bg-accent-400 h-2 w-2 rounded-full"
                        aria-hidden="true"
                    ></span>

                    <span class="theme-text-secondary font-mono text-xs">
                        {{ __('messages.contact_availability') }}
                    </span>

                </div>

            </div>

            {{-- Contact Information --}}
            <div class="lg:col-span-5">

                <div class="theme-border theme-card rounded-2xl border p-8">

                    <p class="theme-accent font-mono text-xs uppercase tracking-widest">
                        {{ __('messages.contact_card_eyebrow') }}
                    </p>

                    <h3 class="theme-text-primary mt-5 font-heading text-2xl font-bold">
                        {{ __('messages.contact_card_heading') }}
                    </h3>

                    <p class="theme-text-secondary mt-4 text-sm leading-7">
                        {{ __('messages.contact_card_description') }}
                    </p>

                    {{-- Primary Contact: WhatsApp --}}
                    <a
                        href="https://wa.me/6285846502569?text={{ rawurlencode(__('messages.contact_whatsapp_message')) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="{{ __('messages.contact_whatsapp_aria') }}"
                        class="mt-8 flex items-center justify-between rounded-lg bg-accent-600 px-6 py-4 text-sm font-semibold text-white transition-colors duration-300 hover:bg-accent-500 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                    >

                        <span>{{ __('messages.contact_whatsapp_label') }}</span>

                        <span aria-hidden="true">↗</span>

                    </a>

                    {{-- Secondary Contact: Email --}}
                    <div class="theme-border mt-8 border-t pt-6">

                        <p class="theme-text-secondary font-mono text-xs uppercase tracking-widest">
                            {{ __('messages.contact_email_label') }}
                        </p>

                        <a
                            href="mailto:rikokordako23@gmail.com?subject={{ rawurlencode(__('messages.contact_email_subject')) }}"
                            class="theme-text-primary mt-3 inline-block break-all text-sm transition-colors duration-300 hover:theme-accent focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400"
                        >
                            rikokordako23@gmail.com
                        </a>

                    </div>

                    {{-- Availability Note --}}
                    <p class="theme-text-secondary mt-8 text-xs leading-6">
                        {{ __('messages.contact_availability_note') }}
                    </p>

                </div>

            </div>

        </div>

    </div>
</section>
