<?php

namespace JordanPartridge\LaraBikes\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use JordanPartridge\LaraBikes\Http\Requests\TokenExchange;
use JordanPartridge\LaraBikes\Http\Strava;
use JordanPartridge\LaraBikes\Models\StravaToken;

final readonly class CallbackController
{
    public function __construct(private Strava $strava)
    {
    }


    public
    function __invoke(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
        ]);

        $response = $this->strava->send(new TokenExchange($validated['code']));

        if (!$response->ok()) {
            return response(['error' => 'Failed to exchange token'], 500);
        }

        StravaToken::create([
            'access_token'  => $response->json()['access_token'],
            'expires_at'    => now()->addSeconds($response->json()['expires_in']),
            'refresh_token' => $response->json()['refresh_token'],
            'athlete_id'    => $response->json()['athlete']['id'],
            'user_id'       =>  $request->user()->id,
        ]);

        return redirect('/admin');
    }
}
