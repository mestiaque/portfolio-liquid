@php
    $methods  = collect($contact['methods'] ?? [])->filter(fn ($m) => filled($m['value'] ?? null));
    $formMode = ($contactForm['mode'] ?? 'demo') === 'production' && filled($contactForm['endpoint'] ?? null) ? 'production' : 'demo';
@endphp
<section id="contact" class="section section--last" tabindex="-1" aria-labelledby="contact-title">
    <div class="container">
        <header class="section-head reveal">
            <p class="eyebrow"><i class="bi bi-envelope" aria-hidden="true"></i> Contact</p>
            <h2 id="contact-title" class="section-title">Let's build something</h2>
            @if (filled($contact['intro'] ?? null))
                <p class="section-sub">{{ $contact['intro'] }}</p>
            @endif
        </header>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="glass-card h-100 reveal">
                    <h3 class="card-title-sm">Reach me directly</h3>
                    <ul class="contact-list">
                        @foreach ($methods as $m)
                            @php
                                $href = $m['href'] ?? null;
                                $external = filled($href) && str_starts_with($href, 'http');
                            @endphp
                            <li class="contact-item">
                                <span class="contact-item__icon" aria-hidden="true"><i class="bi bi-{{ $m['icon'] ?? 'link' }}"></i></span>
                                <span class="contact-item__body">
                                    <span class="contact-item__label">{{ $m['label'] }}</span>
                                    @if (filled($href))
                                        <a class="contact-item__value" href="{{ $href }}" @if ($external) target="_blank" rel="noopener" @endif>
                                            {{ $m['value'] }}@if ($external)<span class="visually-hidden"> (opens in new tab)</span>@endif
                                        </a>
                                    @else
                                        <span class="contact-item__value">{{ $m['value'] }}</span>
                                    @endif
                                </span>
                                @if (filled($m['copy'] ?? null))
                                    <button type="button" class="copy-btn" data-copy="{{ $m['copy'] }}" data-copy-label="{{ $m['label'] }}"
                                            aria-label="Copy {{ $m['label'] }}">
                                        <i class="bi bi-copy" aria-hidden="true"></i>
                                    </button>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="glass-card form-card h-100 reveal">
                    <form id="contactForm" class="contact-form" novalidate
                          data-mode="{{ $formMode }}"
                          data-endpoint="{{ $formMode === 'production' ? url($contactForm['endpoint']) : '' }}"
                          data-success-ms="{{ (int) ($contactForm['success_ms'] ?? 3000) }}">
                        <h3 class="card-title-sm">Send a message</h3>

                        @if ($formMode === 'demo')
                            <p class="form-note">
                                <i class="bi bi-info-circle" aria-hidden="true"></i>
                                Demo mode: the form is validated but no message is actually sent.
                            </p>
                        @endif

                        {{-- Honeypot for bots; hidden from people and assistive tech. --}}
                        <div class="hp" aria-hidden="true">
                            <label for="cf-website">Website</label>
                            <input type="text" id="cf-website" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label for="cf-name" class="form-label">Name</label>
                                <input type="text" id="cf-name" name="name" class="form-control glass-input" autocomplete="name"
                                       required maxlength="255" aria-describedby="cf-name-error">
                                <p class="field-error" id="cf-name-error"></p>
                            </div>
                            <div class="col-sm-6">
                                <label for="cf-email" class="form-label">Email</label>
                                <input type="email" id="cf-email" name="email" class="form-control glass-input" autocomplete="email"
                                       required maxlength="255" aria-describedby="cf-email-error">
                                <p class="field-error" id="cf-email-error"></p>
                            </div>
                            <div class="col-12">
                                <label for="cf-subject" class="form-label">Subject</label>
                                <input type="text" id="cf-subject" name="subject" class="form-control glass-input"
                                       required maxlength="255" aria-describedby="cf-subject-error">
                                <p class="field-error" id="cf-subject-error"></p>
                            </div>
                            <div class="col-12">
                                <label for="cf-message" class="form-label">Message</label>
                                <textarea id="cf-message" name="message" class="form-control glass-input" rows="5"
                                          required maxlength="5000" aria-describedby="cf-message-error"></textarea>
                                <p class="field-error" id="cf-message-error"></p>
                            </div>
                        </div>

                        <div class="form-foot">
                            <p class="form-alert" role="alert" data-form-alert hidden></p>
                            <button type="submit" class="btn btn-glass-primary btn-submit">
                                <span class="btn-submit__idle"><i class="bi bi-send" aria-hidden="true"></i> Send message</span>
                                <span class="btn-submit__busy"><span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Sending…</span>
                            </button>
                        </div>
                    </form>

                    <div class="form-success" data-form-success hidden>
                        <div class="form-success__inner" role="status" tabindex="-1">
                            <span class="form-success__badge" aria-hidden="true">
                                <svg viewBox="0 0 52 52"><circle cx="26" cy="26" r="24"/><path d="M15 27l7 7 15-16"/></svg>
                            </span>
                            <p class="form-success__title">OK, message {{ $formMode === 'demo' ? 'validated' : 'sent' }}!</p>
                            <p class="form-success__text">
                                @if ($formMode === 'demo')
                                    Demo mode: nothing was actually sent.
                                @else
                                    Thanks for reaching out. I'll reply soon.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
