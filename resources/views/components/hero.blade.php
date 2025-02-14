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

<section class="py-12 bg-gradient-to-r from-[var(--from)] via-[var(--via)] to-[var(--to)]"
         style="--from: {{ $gradientFromColor }}; --via: {{ $gradientViaColor }}; --to: {{ $gradientToColor }};">
    <x-container class="max-w-7xl">
        <div class="flex flex-col gap-2">
            <h1 class="text-3xl font-bold text-white">{{ $title }}</h1>

            @if($description)
                <p class="w-full lg:w-1/2 text-sm text-white">{{ $description }}</p>
            @endif
        </div>
    </x-container>
</section>
