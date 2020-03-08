<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    static public function createForRoomAndUser($room_id, $user_id, $user_name, $user_picture, $user_phone)
    {
        // complete previous uncompleted sessions on this room
        \App\Session::where('room_id', $room_id)
            ->whereNull('end_at')
            ->update(['end_at' => date('Y-m-d H:i:s')]);

        $session = new \App\Session();
        $session->user_id = $user_id;
        $session->user_name = $user_name;
        $session->user_picture = $user_picture;
        $session->user_phone = $user_phone;
        $session->room_id = $room_id;
        $session->start_at = date('Y-m-d H:i:s');
        $session->save();
        return $session;
    }
}
