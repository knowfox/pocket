<?php

namespace Knowfox\Pocket\Controllers;

use Illuminate\Http\Request;
use Duellsy\Pockpack\Pockpack;
use Duellsy\Pockpack\PockpackAuth;
use Knowfox\Pocket\Models\Pocket;
use Carbon\Carbon;

class PocketController
{
    public function index(Request $request)
    {
        $consumer_key = env('POCKET_CONSUMER_KEY');
        $pocket = Pocket::where('user_id', $request->user()->id)->first();

        $invalid_token = true;
        if ($pocket) {
            $today = Carbon::today()->getTimestamp();

            $pockpack = new Pockpack($consumer_key, $pocket->access_token);
            $list = $pockpack->retrieve([
                'state' => 'all',
                'since' => $today,
                'detailType' => 'simple',
            ], /*as_array*/true);
            if ($list) {
                $invalid_token = false;
            }
        }

        if ($invalid_token) {
            $pockpack = new PockpackAuth();
            $request_token = $pockpack->connect($consumer_key);
            session(['request_token' => $request_token]);
            $url = route('pocket.auth');
            return redirect()->away("https://getpocket.com/auth/authorize?request_token={$request_token}&redirect_uri={$url}");
        }

        return view('pocket::index', [
            'pocket' => $pocket,
            'list' => $list['list'],
        ]);
    }

    public function auth(Request $request)
    {
        $pockpack = new PockpackAuth();
        $consumer_key = env('POCKET_CONSUMER_KEY');
        $request_token = session('request_token');
        $access_token = $pockpack->receiveToken($consumer_key, $request_token);
        $pocket = Pocket::updateOrCreate([
            'user_id' => $request->user()->id,
        ], [
            'access_token' => $access_token,
        ]);

        return redirect()->route('pocket');
    }
}