<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        $user = session('sso_user');

        return view('dashboard', [
            'user' => $user,
        ]);
    }
}
