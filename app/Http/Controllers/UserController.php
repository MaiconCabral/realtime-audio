<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    public function index (){
        // return (new User())->getAllWithoutMe();

        // $user = User::all();


        return response()->json((new User())->getAllWithoutMe(), 200);
    }
}
