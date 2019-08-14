<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/

Route::post('/room/{room_id}/auth', function (Request $request, $room_id) {
    $api_key = env('API_KEY');
    if (empty($api_key)) {
        return response()->json(['status' => 'error']);
    }

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, env('API_URI') . '/phonebox/auth');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,
        http_build_query([
            'api_key' => env('API_KEY'),
            'code' => $request->get('code')
        ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response_content = curl_exec($ch);
    //dump($response_content);
    $json = json_decode($response_content);
    curl_close($ch);


    if (!$json || !isset($json->user)) {
        abort(403);
    }

    $session = \App\Session::createForRoomAndUser($room_id, $json->user->id, $json->user->name, $json->user->profile_url);

    $result = [
        'status' => 'success',
        'session' => [
            'id' => $session->id,
            'started_at' => $session->start_at,
            'user' => [
                'id' => $session->user_id,
                'name' => $session->user_name,
                'picture_url' => $session->user_picture
            ]
        ]
    ];
    return response()->json($result);
});


Route::get('/room/{room_id}', function (Request $request, $room_id) {
    $session = \App\Session::where('room_id', $room_id)->whereNull('end_at')->first();
    if ($session) {
        $result = [
            'status' => 'success',
            'session' => [
                'id' => $session->id,
                'started_at' => $session->start_at,
                'user' => [
                    'id' => $session->user_id,
                    'name' => $session->user_name,
                    'picture_url' => $session->user_picture
                ]
            ]
        ];
    } else {
        $result = [
            'status' => 'success',
            'session' => [
                'started_at' => null,
                'user' => null
            ]
        ];
    }

    return response()->json($result);
});


Route::post('/session/{id}', function (Request $request, $id) {
    $session = \App\Session::findOrFail($id);
    if ($session->end_at) {
        abort(500, 'La session est déjà terminée');
    }
    $session->end_at = date('Y-m-d H:i:s');
    $session->save();
    return response()->json(['status' => 'success']);
});


Route::get('/status', function (Request $request) {
    $result = [];

    $sql = 'select rooms.id as room_id, sessions.id as session_id, sessions.start_at, sessions.user_id, sessions.user_name, sessions.user_picture FROM rooms LEFT OUTER JOIN sessions on rooms.id = sessions.room_id AND sessions.end_at IS NULL';
    foreach (DB::select($sql) as $room) {
        $result[$room->room_id] = [
            'name' => sprintf('Box %d', $room->room_id),
            'action' => route('room_pick', ['room_id' => $room->room_id], true),
            'session' => [
                'start_at' => $room->start_at,
                'user' => $room->user_id ? [
                    'id' => $room->user_id,
                    'name' => $room->user_name,
                    'picture_url' => $room->user_picture,
                ] : null
            ]
        ];
    }

    return response()
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET')
        ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, X-Auth-Token')
        ->json($result);
});
