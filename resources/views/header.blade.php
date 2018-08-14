<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no,viewport-fit=cover">

    <title>{{$title ?? "首页"}}</title>

    <script src="{{asset('js/jquery-2.1.1.js')}}" type="text/javascript" charset="utf-8"></script>
    <script src="{{asset('js/jweixin-1.2.0.js')}}" type="text/javascript" charset="utf-8"></script>
    <script src="{{asset('js/common.js')}}" type="text/javascript" charset="utf-8"></script>

    <style type="text/css">
        html {
            font-size: calc(100vw/7.5);
        }
        body {
            margin:0;
            width: 7.5rem;
            font-size: .3rem;
        }
        a {
            text-decoration: none;
        }

        p {
            margin:0;
            padding:0;
        }
    </style>