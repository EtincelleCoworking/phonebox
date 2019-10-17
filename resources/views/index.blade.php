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
<ul>
    @foreach($rooms as $room)
        <li><a href="{{route('room', ['id' => $room->id])}}">{{$room->name}}</a></li>
    @endforeach

</ul>
<div id="app">
    <b-tabs content-class="m-3">
        <b-tab title="Toulouse" active>
            <booking city="toulouse"/>
        </b-tab>
        <b-tab title="Albi">
            <booking city="albi"/>
        </b-tab>
    </b-tabs>
</div>
<script type="application/javascript" src="{{mix('js/app.js')}}"></script>
</body>
</html>
