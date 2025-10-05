<?php

namespace App\Http\Controllers;

class CsrfController extends Controller
{
    public function token()
    {
        return response()->json([
            'token' => csrf_token(),
        ]);
    }
}
