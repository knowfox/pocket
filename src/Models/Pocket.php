<?php

namespace Knowfox\Pocket\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;
use Knowfox\Core\Models\Concept;
use Illuminate\Database\QueryException;

class Pocket extends Model
{
    protected $fillable = ['access_token', 'last_count', 'last_sync_at', 'user_id'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function saveBookmarks($list, $parent, $command = null)
    {
        $user_id = $parent->owner_id;
        $affected = [];

        foreach ($list as $item) {
            $title = $item['resolved_title'];
            if (!$title) {
                $title = $item['given_url'];
            }

            $data = [
                'title' => $title,
                'summary' => $item['excerpt'],
                'source_url' => $item['given_url'],
            ];

            if (!empty($item['top_image_url'])) {
                $data['config'] = [
                    'image' => $item['top_image_url']
                ];
            }

            try {
                $concept = Concept::firstOrCreate([
                    'source_url' => $item['given_url'],
                    'owner_id' => $user_id,
                ], $data);
            }
            catch (QueryException $e) {
                if ($command) {
                    $command->error('   . ' . $item['given_title'] . " ERR: " . json_encode($e->errorInfo));
                }
                continue;
            }
        
            if (!empty($item['tags'])) {
                $tags = array_map(function ($item) { return $item['tag']; }, $item['tags']);
                $concept->retag($tags);
            }
            $concept->tag('pocket');

            if (!$concept->parent_id) {
                $concept->appendToNode($parent);
                try {
                    $concept->save();
                }
                catch (QueryException $e) {
                    if ($command) {
                        $command->error('   . ' . $item['given_title'] . " ERR: " . json_encode($e->errorInfo));
                    }
                    continue;
                }
            }
            if ($command) {
                $command->info('   . ' . $item['given_title'] . " -> " . $concept->id);
            }
            $affected[] = $concept;
        }
        return $affected;
    }
}
