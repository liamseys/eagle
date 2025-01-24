<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-lg bg-white shadow']) }}>
    <div class="px-4 py-5 sm:p-6">
        {{ $slot }}
    </div>
</div>
