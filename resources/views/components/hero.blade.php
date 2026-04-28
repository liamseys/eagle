@use('App\Settings\GeneralSettings')

@props([
    'title',
    'description' => null,
])

@php
    $generalSettings = app(GeneralSettings::class);

    $gradientFromColor = $generalSettings->branding_gradient_from_color;
    $gradientViaColor = $generalSettings->branding_gradient_via_color;
    $gradientToColor = $generalSettings->branding_gradient_to_color;
@endphp

<section
    class="relative isolate overflow-hidden bg-linear-to-br from-(--from) via-(--via) to-(--to) py-16 sm:py-20"
    style="--from: {{ $gradientFromColor }}; --via: {{ $gradientViaColor }}; --to: {{ $gradientToColor }};"
>
    <div
        aria-hidden="true"
        class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_top_left,rgba(255,255,255,0.18),transparent_60%)]"
    ></div>
    <div
        aria-hidden="true"
        class="pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-px bg-white/10"
    ></div>

    <x-container class="max-w-7xl">
        <div class="flex flex-col gap-3">
            <h1 class="max-w-[28ch] text-balance text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                {{ $title }}
            </h1>

            @if($description)
                <p class="max-w-2xl text-pretty text-sm text-white/85 sm:text-base">
                    {{ $description }}
                </p>
            @endif
        </div>
    </x-container>
</section>
