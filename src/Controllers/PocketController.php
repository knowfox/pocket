<?php

namespace Knowfox\Pocket\Controllers;

use Illuminate\Http\Request;
use Pocket\Pocket as PocketApi;
use Knowfox\Pocket\Models\Pocket;
use Knowfox\Core\Models\Concept;
use Carbon\Carbon;

class PocketController
{
    public function index(Request $request)
    {
        $consumer_key = env('POCKET_CONSUMER_KEY');
        $user_id = $request->user()->id;
        $pocket = Pocket::where('user_id', $user_id)->first();

        $invalid_token = true;
        if ($pocket) {
            $today = Carbon::today()->getTimestamp();

            $api = new PocketApi([
                'consumerKey' => $consumer_key
            ]);
            $api->setAccessToken($pocket->access_token);

            $list = $api->retrieve([
                'state' => 'all',
                'since' => $today,
                'detailType' => 'simple',
            ]);
            if ($list) {
                $invalid_token = false;
            }
        }

        if ($invalid_token) {
            $api = new PocketApi([
                'consumerKey' => $consumer_key
            ]);
            $url = route('pocket.auth');
            $result = $api->requestToken($url);

            $request_token = $result['request_token'];
            session(['request_token' => $request_token]);
            return redirect()->away($result['redirect_uri']);
        }

        $parent = Concept::where('parent_id', null)
            ->where('owner_id', $user_id)
            ->where('title', 'Bookmarks')
            ->first();

        $bookmarks = $pocket->saveBookmarks($list['list'], $parent);

        return view('pocket::index', [
            'pocket' => $pocket,
            'list' => $bookmarks,
        ]);
    }

    public function auth(Request $request)
    {
        $consumer_key = env('POCKET_CONSUMER_KEY');
        $api = new PocketApi([
            'consumerKey' => $consumer_key
        ]);
        $request_token = session('request_token');
        $user = $api->convertToken($request->authorized);
        $pocket = Pocket::updateOrCreate([
            'user_id' => $request->user()->id,
        ], [
            'access_token' => $user['access_token'],
        ]);

        return redirect()->route('pocket');
    }
}