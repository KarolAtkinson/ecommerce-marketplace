@php
    $announcements = collect(config('announcements.messages', []))
        ->sortBy(fn ($message) => mb_strtolower(\Illuminate\Support\Str::ascii($message['text'])))
        ->values();
    $announcementInterval = max(5, (int) config('announcements.interval_seconds', 20)) * 1000;
@endphp
@if ($announcements->isNotEmpty())
    <div class="announcement" data-announcement-slider data-interval="{{ $announcementInterval }}" role="region" aria-roledescription="carousel" aria-label="Komunikaty">
        <div class="announcement-track">
            @foreach ($announcements as $message)
                <div class="announcement-slide{{ $loop->first ? ' is-active' : '' }}" @unless ($loop->first) inert @endunless>
                    <p>{{ $message['text'] }}</p>
                    @if (! empty($message['url']) && ! empty($message['label']))
                        <a href="{{ $message['url'] }}">{{ $message['label'] }} <span aria-hidden="true">↗</span></a>
                    @endif
                </div>
            @endforeach
        </div>
        @if ($announcements->count() > 1)
            <button class="announcement-toggle" type="button" data-announcement-toggle aria-label="Wstrzymaj komunikaty">
                <span aria-hidden="true">❚❚</span>
            </button>
        @endif
    </div>
@endif
