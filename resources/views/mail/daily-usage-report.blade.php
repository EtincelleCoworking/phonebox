<table border="1">
    @foreach($items as $item)
        <tr valign="top">
            <td>
                {{$item['name']}}
            </td>
            <td>{{$item['email']}}</td>
            <td>{{$item['duration']}}</td>
            <td>
                @foreach($item['sessions'] as $session)
                    @if($session['duration'] > 60)
                        <strong>
                            {{substr($session['start_at'], 11)}} - {{substr($session['end_at'], 11)}}
                            ({{$session['duration']}}min)
                        </strong>
                    @else
                        {{substr($session['start_at'], 11)}} - {{substr($session['end_at'], 11)}}
                        ({{$session['duration']}}min)
                    @endif
                    <br/>
                @endforeach
            </td>
        </tr>
    @endforeach
</table>
