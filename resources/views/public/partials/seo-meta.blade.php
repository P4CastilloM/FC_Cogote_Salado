@php
    $seoTitle = $seoTitle ?? 'FC Cogote Salado';
    $seoDescription = $seoDescription ?? 'Más que amigos, familia. Noticias, plantel, fotos y avisos del equipo.';
    $seoUrl = $seoUrl ?? url()->current();
    $seoImage = $seoImage ?? asset('storage/logo/logo_fccs_s_f.png');
@endphp

<meta name="description" content="{{ $seoDescription }}">
<link rel="canonical" href="{{ $seoUrl }}">
<meta name="theme-color" content="#34205C">

<meta property="og:type" content="website">
<meta property="og:site_name" content="FC Cogote Salado">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ $seoUrl }}">
<meta property="og:image" content="{{ $seoImage }}">
<meta property="og:image:secure_url" content="{{ $seoImage }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
<meta name="twitter:image" content="{{ $seoImage }}">
