<div>
    <x-alert>
        {{ __('Make sure to copy your personal access token now. You won’t be able to see it again!') }}
    </x-alert>

    <x-input type="text" name="test"
             id="test" class="w-full"
             required/>

    {{ $test }}
</div>
