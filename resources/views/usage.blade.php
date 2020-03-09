<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{mix('css/app.css')}}"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Etincelle Room</title>
</head>
<body>
<div id="app">
    <usage-overview :rooms="rooms"/>
</div>
<script type="application/javascript" src="{{mix('js/app.js')}}"></script>
<script type="application/javascript">
    const app = new Vue({
        el: '#app',
        data() {
            return {
                rooms: {!! json_encode($resources, JSON_HEX_QUOT) !!}
            }
        },
        onIdle() {
            //console.log('onIdle');
            //window.location.reload(true);
        },
        onActive() {
            //console.log('onActive');
        }
    });
</script>
</body>
</html>
