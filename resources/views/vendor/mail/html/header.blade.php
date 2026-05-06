@use('App\Settings\GeneralSettings')

@props(['url'])

@php
    $generalSettings = app(GeneralSettings::class);

    $logoSrc = ! empty($generalSettings->branding_logo_black)
        ? Storage::url($generalSettings->branding_logo_black)
        : asset('img/logo/logo-black.svg');

    $appName = ! empty($generalSettings->app_name) ? $generalSettings->app_name : config('app.name');
@endphp
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ $logoSrc }}" class="logo" alt="{{ $appName }}">
</a>
</td>
</tr>
