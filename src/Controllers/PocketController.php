<?php

namespace Knowfox\Pocket\Controllers;

use Illuminate\Http\Request;
use Pocket as PocketApi;
use Knowfox\Pocket\Services\PocketService;
use Carbon\Carbon;

class PocketController
{
    public function index(Request $request)
    {
        $service = new PocketService();
        $user_id = $request->user()->id;
        $info = $service->saveBookmarks($user_id, Carbon::today());

        if (!$info) {
            $api = new PocketApi([
                'consumerKey' => $consumer_key
            ]);
            $url = route('pocket.auth');
            $result = $api->requestToken($url);

            $request_token = $result['request_token'];
            session(['request_token' => $request_token]);
            return redirect()->away($result['redirect_uri']);
        }

        return view('pocket::index', $info);
    }

    public function auth(Request $request)
    {
        $consumer_key = env('POCKET_CONSUMER_KEY');
        $api = new PocketApi([
            'consumerKey' => $consumer_key
        ]);
        $request_token = session('request_token');
        //$user = $api->convertToken($request->authorized);
        $user = $api->convertToken($request_token);
        $pocket = Pocket::updateOrCreate([
            'user_id' => $request->user()->id,
        ], [
            'access_token' => $user['access_token'],
        ]);

        return redirect()->route('pocket');
    }
}