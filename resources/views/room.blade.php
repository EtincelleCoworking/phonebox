<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{mix('css/app.css')}}"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style type="text/css">
        div .col-2 {
            height: 100px;
            padding-top: 40px;
        }

        .btn-primary, .btn-primary:visited {
            background-color: #461A5E;
            border-color: #461A5E;
            color: #ffffff !important;
        }

        .btn-primary:hover {
            background-color: #000000;
            border-color: #000000;
            color: #ffffff !important;
        }

        .btn-default {
            background-color: #000000;
            border-color: #000000;
            color: #ffffff !important;
        }

        .img-circle {
            border-radius: 50%;
        }

        img.circle-border {
            border: 6px solid #FFFFFF;
            border-radius: 50%;
        }
    </style>
    <title>{{$room->name}}</title>
</head>
<body>
<div id="app">
    <phone-box id="{{$room->id}}"></phone-box>
</div>
<script src="{{mix('js/app.js')}}"></script>
<script type="application/javascript">
    const app = new Vue({
        el: '#app',
        data() {
            return {}
        },
        onIdle() {
            console.log('onIdle');
            window.location.reload(true);
        },
        onActive() {
            console.log('onActive');
        }
    });
</script>
</body>
</html>
