<?php

namespace App\Http\Controllers;

use App\Room;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $rooms = Room::orderBy('name', 'ASC')->get();

        return view('index', ['rooms' => $rooms]);
    }

    public function room(Request $request, $room_id)
    {
        $room = Room::findOrFail($room_id);
        $disk = Storage::disk('public');
        $qrCodeFilename = sprintf('%d.png', $room_id);
        if (!$disk->exists($qrCodeFilename)) {
            $qrCode = new \Endroid\QrCode\QrCode(route('room_pick', ['room_id' => $room_id], true));
            $disk->put($qrCodeFilename, $qrCode->writeString());
        }

        return view('room', ['room' => $room]);
    }

    public function room_pick(Request $request, $room_id)
    {
        $url = route('room_pick', ['room_id' => $room_id], true);
        return redirect(env('API_URI') . '/phonebox/auth?redirect=' . urlencode($url));
    }

    public function room_picked(Request $request, $room_id)
    {
        if (env('API_KEY') != $request->get('api_key')) {
            abort(401);
        }
        \App\Session::createForRoomAndUser($room_id,
            $request->get('user_id'), $request->get('user_name') , $request->get('user_picture'));
        return response('OK');

        return redirect(route('room', ['room_id' => $room_id]));
    }
}
