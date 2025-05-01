<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Defcomm</title>

    <link rel="shortcut icon" href="{{asset('img/logo.png')}}" />
    <!--begin::Fonts(mandatory for all pages)-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <!--end::Fonts-->
    <!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
    <link href="{{ asset('demo3/plugins/global/plugins.bundle.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('demo3/css/style.bundle.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('global.css')}}" rel="stylesheet" type="text/css" />
</head>

<body id="kt_body" class="auth-bg bgi-size-cover bgi-attachment-fixed bgi-position-center bgi-no-repeat">
    @yield("content")

    <script src="{{ asset('demo3/plugins/global/plugins.bundle.js')}}"></script>
    <script src="{{ asset('demo3/js/scripts.bundle.js')}}"></script>
    <!-- <script src="{{ asset('demo3/js/custom/authentication/sign-in/general.js')}}"></script> -->
</body>

</html>