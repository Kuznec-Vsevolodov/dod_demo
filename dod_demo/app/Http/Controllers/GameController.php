<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\QueueSing;
use App\QueueTeam;
use App\ChatSing;
use App\ChatTeam;
use Auth;
use App\Events\QueueEvent;
use App\Events\QueueTeamEvent;

class GameController extends Controller
{
    public function idDefender(Request $request){
        $user = Auth::user();
        $user->current_chat = $request->input('chat_id');
        $user->game_in = 'sing';
        $user->save();
        return 'It`s working';
    }
}
