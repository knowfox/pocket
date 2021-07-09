<?php

namespace Knowfox\Pocket\Services;

use Pocket as PocketApi;
use Knowfox\Pocket\Models\Pocket;
use Knowfox\Models\Concept;
use Carbon\Carbon;

class PocketService
{
    public function saveBookmarks($user_id, $since)
    {
        $consumer_key = env('POCKET_CONSUMER_KEY');
        $pocket = Pocket::where('user_id', $user_id)->first();

        if ($pocket) {
            $api = new PocketApi([
                'consumerKey' => $consumer_key
            ]);
            $api->setAccessToken($pocket->access_token);

            $list = $api->retrieve([
                'state' => 'all',
                'since' => $since->getTimestamp(),
                'detailType' => 'complete',
            ]);
            if (!$list) {
                return null;
            }
        }

        $parent = Concept::where('parent_id', null)
            ->where('owner_id', $user_id)
            ->where('title', 'Bookmarks')
            ->first();

        $bookmarks = $pocket->saveBookmarks($list['list'], $parent);

        return [
            'pocket' => $pocket,
            'list' => $bookmarks,
        ];
    }
}