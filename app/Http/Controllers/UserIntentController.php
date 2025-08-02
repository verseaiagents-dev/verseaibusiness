<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserIntentController extends Controller
{
    public function index()
    {
        return view('dashboard.user-agent-intents');
    }
}
