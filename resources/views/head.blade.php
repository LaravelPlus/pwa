<link rel="manifest" href="{{ route('pwa.manifest') }}">
<meta name="theme-color" content="{{ config('pwa.manifest.theme_color', '#ffffff') }}">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="{{ config('pwa.manifest.short_name', config('app.name')) }}">
@foreach(config('pwa.manifest.icons', []) as $icon)
@if(str_contains($icon['sizes'] ?? '', '180x180') || str_contains($icon['sizes'] ?? '', '152x152'))
<link rel="apple-touch-icon" sizes="{{ $icon['sizes'] }}" href="{{ $icon['src'] }}">
@endif
@endforeach
