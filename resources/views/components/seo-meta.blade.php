@props([
    'title' => 'SubTracker — трекер подписок и серверов',
    'description' => 'Бесплатный сервис для учёта серверов, доменов, VPS и подписок с напоминаниями о сроках оплаты',
    'image' => '/og-image.png',
    'type' => 'website',
])
@php
    $canonicalUrl = url()->current();
    $imageUrl = str_starts_with($image, 'http') ? $image : url($image);
@endphp
<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<link rel="canonical" href="{{ $canonicalUrl }}">

<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:image" content="{{ $imageUrl }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:type" content="{{ $type }}">
<meta property="og:site_name" content="SubTracker">
<meta property="og:locale" content="ru_RU">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $imageUrl }}">

@if(config('app.google_site_verification'))
<meta name="google-site-verification" content="{{ config('app.google_site_verification') }}">
@endif
@if(config('app.yandex_site_verification'))
<meta name="yandex-verification" content="{{ config('app.yandex_site_verification') }}">
@endif
