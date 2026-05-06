@use('App\Settings\GeneralSettings')

@php
    $generalSettings = app(GeneralSettings::class);

    $brandFromColor = $generalSettings->branding_gradient_from_color ?: '#f8cb09';
    $brandViaColor = $generalSettings->branding_gradient_via_color ?: '#eb2622';
    $brandToColor = $generalSettings->branding_gradient_to_color ?: '#7506bf';
@endphp
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<title>{{ config('app.name') }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<style>
@media only screen and (max-width: 600px) {
.inner-body {
width: 100% !important;
border-radius: 0 !important;
}

.brand-strip {
border-radius: 0 !important;
}

.content-cell {
padding: 28px 24px !important;
}

.footer {
width: 100% !important;
}
}

@media only screen and (max-width: 500px) {
.button {
width: 100% !important;
}
}
</style>
{!! $head ?? '' !!}
</head>
<body>

<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
{!! $header ?? '' !!}

<!-- Email Body -->
<tr>
<td class="body" width="100%" cellpadding="0" cellspacing="0" style="border: hidden !important;">
<table class="inner-body" align="center" width="600" cellpadding="0" cellspacing="0" role="presentation">
<!-- Brand strip -->
<tr>
<td class="brand-strip" height="6" style="height: 6px; line-height: 6px; font-size: 0; mso-line-height-rule: exactly; padding: 0; background-color: {{ $brandViaColor }}; background-image: linear-gradient(to right, {{ $brandFromColor }} 0%, {{ $brandViaColor }} 50%, {{ $brandToColor }} 100%); border-top-left-radius: 12px; border-top-right-radius: 12px;">&nbsp;</td>
</tr>
<!-- Body content -->
<tr>
<td class="content-cell">
{!! Illuminate\Mail\Markdown::parse($slot) !!}

{!! $subcopy ?? '' !!}
</td>
</tr>
</table>
</td>
</tr>

{!! $footer ?? '' !!}
</table>
</td>
</tr>
</table>
</body>
</html>
