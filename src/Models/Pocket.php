<?php

namespace Knowfox\Pocket\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Pocket extends Model
{
    protected $fillable = ['access_token', 'last_count', 'last_sync_at', 'user_id'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function saveBookmarks($list, $parent)
    {
        $user_id = $parent->owner_id;

        foreach ($list as $item) {
            $data = [
                'title' => $item['resolved_title'],
                'summary' => $item['excerpt'],
                'source_url' => $item['given_url'],
            ];

            if (!empty($item['top_image_url'])) {
                $data['config'] = [
                    'image' => $item['top_image_url']
                ];
            }

            $concept = Concept::firstOrCreate([
                'source_url' => $item['given_url'],
                'owner_id' => $user_id,
            ], $data);
            
            if (!empty($item['tags'])) {
                $tags = array_map(function ($item) { return $item['tag']; }, $item['tags']);
                $concept->retag($tags);
            }
            $concept->tag('pocket');

            $concept->appendToNode($parent);
        }
    }
}
