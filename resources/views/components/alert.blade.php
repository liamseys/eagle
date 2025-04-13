@use('App\Settings\GeneralSettings')

@php
    $generalSettings = app(GeneralSettings::class);

    $gradientViaColor = $generalSettings->branding_gradient_via_color;
@endphp

<div {{ $attributes->merge(['class' => 'p-4 rounded-md']) }}
     style="background-color: {{ $gradientViaColor }};">
    <div class="flex items-center gap-1">
        <x-heroicon-s-bell-alert class="h-5 w-5 text-white"/>
        <p class="text-sm text-white">{{ $slot }}</p>
    </div>
</div>
