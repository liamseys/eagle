<div class="flex flex-col gap-y-4">
    <x-alert>
        {{ __('Make sure to copy your personal access token now. You won’t be able to see it again!') }}
    </x-alert>

    <x-filament::input.wrapper>
        <x-filament::input
            type="text"
            value="{{ $plainTextToken }}"
            onclick="this.select()"
        />
    </x-filament::input.wrapper>
</div>
