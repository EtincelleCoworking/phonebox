<?php

namespace App\Http\Controllers;

use App\Room;
use App\Session;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $rooms = Room::orderBy('name', 'ASC')->get();
        $resources = [];
        foreach ($rooms as $room) {
            $resources[] = [
                'id' => $room->id,
                'title' => $room->name
            ];
        }
        return view('index', [
            'rooms' => $rooms,
            'resources' => $resources]);
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
            $request->get('user_id'), $request->get('user_name'),
            $request->get('user_picture'), $request->get('user_phone'));
        return response('OK');

        return redirect(route('room', ['room_id' => $room_id]));
    }

    public function usage(Request $request)
    {
        $rooms = Room::orderBy('name', 'ASC')->get();
        $resources = [];
        foreach ($rooms as $room) {
            $resources[] = [
                'id' => $room->id,
                'title' => $room->name
            ];
        }
        return view('usage', [
            'rooms' => $rooms,
            'resources' => $resources]);
    }

    public function api_usage(Request $request)
    {
        $data = [];
        $sessions = Session::where('start_at', '>', $request->get('start'))
            ->where('end_at', '<', $request->get('end'))
            ->get();
        foreach ($sessions as $session) {
            $data[] = [
                'title' => $session->user_name,
                'start' => $session->start_at,
                'end' => $session->end_at,
                'resourceId' => $session->room_id
            ];
        }
        return response()->json($data);
    }
    //SELECT date_format(start_at, '%d/%m/%Y') as day, user_name, TIMESTAMPDIFF(MINUTE, start_at, end_at) FROM `sessions` order by start_at desc


    // revoir le système de notification pour ne pas avoir 1h depuis le début de la session,
    // mais sur le temps restant dans son quota

    // afficher les utilisateurs avec un quota dépassé pour pouvoir faire la police
}
