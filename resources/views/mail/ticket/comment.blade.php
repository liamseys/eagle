<x-mail::message>
# {{ __('Hello!') }}

{!! $ticketComment->body !!}

{{ __('Regards') }},<br>
{{ config('app.name') }}
</x-mail::message>
