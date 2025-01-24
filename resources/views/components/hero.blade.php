@props([
    'title',
    'description' => null,
])

<section class="py-12 bg-gradient-to-r from-[#F8CB09] via-[#EB2622] to-[#7506BF]">
    <x-container>
        <div class="flex flex-col gap-2">
            <h1 class="text-3xl font-bold text-white">{{ $title }}</h1>

            @if($description)
                <p class="w-full lg:w-1/2 text-sm text-white">{{ $description }}</p>
            @endif
        </div>
    </x-container>
</section>
