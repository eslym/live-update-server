<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/>
    <link rel="icon" href="{{ asset('/favicon.svg') }}" type="image/x-icon"/>
    <title>{{ config('app.name') }}</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.ts')
    @inertiaHead
</head>
<body>
@inertia
<div id="page-spinner"
     class="fixed z-[99999] inset-0 flex bg-backgroundPrimary items-center justify-center duration-500 transition-opacity"
     ontransitionend="this.remove();"
>
    <div class="spinner-dot-pulse spinner-xl">
        <div class="spinner-pulse-dot"></div>
    </div>
</div>
</body>
</html>
