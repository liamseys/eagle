<x-mail::message>
# Hello!

{!! $ticketComment->body !!}

Regards,<br>
{{ config('app.name') }}
</x-mail::message>
