@use('App\Settings\GeneralSettings')

@props([
    'title',
    'description' => null,
])

<section class="py-12 bg-gradient-to-r from-[{{ app(GeneralSettings::class)->branding_from_color }}] via-[{{ app(GeneralSettings::class)->branding_via_color }}] to-[{{ app(GeneralSettings::class)->branding_to_color }}]">
    <x-container class="max-w-7xl">
        <div class="flex flex-col gap-2">
            <h1 class="text-3xl font-bold text-white">{{ $title }}</h1>

            @if($description)
                <p class="w-full lg:w-1/2 text-sm text-white">{{ $description }}</p>
            @endif
        </div>
    </x-container>
</section>
