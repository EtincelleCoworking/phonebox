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

    curl_setopt($ch, CURLOPT_URL, env('API_URI').'/phonebox/auth');
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
        return response()->json(['status' => 'error']);
    }

    $session = \App\Session::createForRoomAndUser($room_id, $json->user->id);

    $result = [
        'status' => 'success',
        'session' => [
            'id' => $session->id,
            'started_at' => $session->start_at,
            'user' => [
                'id' => $json->user->id,
                'name' => $json->user->name,
                'picture_url' => $json->user->profile_url
            ]
        ]
    ];
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
