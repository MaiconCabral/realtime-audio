<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Events\AudioSended;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = ['audio', 'sender', 'receiver'];

    public function sender()
    {
        return $this->hasOne(\App\Models\User::class, 'sender');
    }

    public function receiver()
    {
        return $this->hasOne(\App\Models\User::class, 'receiver');
    }

    public function getAllByMeAnd($id)
    {
        return $this->orWhere(function ($query) use ($id) {
                $query->where('sender', \Auth::user()->id)
                    ->where('receiver', $id);
            })
            ->orWhere(function ($query) use ($id) {
                $query->where('sender', $id)
                    ->where('receiver', \Auth::user()->id);
            })
            ->latest()
            ->get();
    }

    public function saveAudio($audio, $receiver)
    {
        $name = uniqid(date('h-i-s-')) . '.ogg';
        $save_path = public_path() . DIRECTORY_SEPARATOR . 'audios';
        $audio->move($save_path, $name);

        $chat = $this->create([
            'audio' => $name,
            'sender' => \Auth::user()->id,
            'receiver' => $receiver,
        ]);

        broadcast(new AudioSended($chat));
        // broadcast(new AudioSended($chat))->toOthers();
        // event(new AudioSended($chat));

        return $chat;
    }

}
