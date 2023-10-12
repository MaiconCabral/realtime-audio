<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chat;

class ChatController extends Controller
{
    //
    public function show($id)
    {

        return (new Chat)->getAllByMeAnd($id);
    }

    public function store(Request $request)
    {
     
        if (!$request->file('audio')->getSize()) {
            return;
        }

        return (new Chat)->saveAudio($request->file('audio'), $request->input('receiver'));

    }
}
