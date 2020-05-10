<?php

namespace Knowfox\Pocket\Commands;

use Illuminate\Console\Command;
use Knowfox\Pocket\Models\Pocket;
use Duellsy\Pockpack\Pockpack;
use Knowfox\Core\Models\Concept;
use Carbon\Carbon;

class PocketSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pocket:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync contents from Pocket';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function syncUser($pocket) {
        $this->info(' - ' .  $pocket->user->email);
        $consumer_key = env('POCKET_CONSUMER_KEY');
        $pockpack = new Pockpack($consumer_key, $pocket->access_token);

        $since = Carbon::now()->subMinutes(10)->getTimestamp();

        $as_array = true;
        $list = $pockpack->retrieve([
            'state' => 'all',
            'detailType' => 'complete',
            'since' => $since,
        ], /*as_array*/true);

        $bookmarks = Concept::where('parent_id', null)
            ->where('owner_id', 1)
            ->where('title', 'Bookmarks')
            ->first();

        foreach ($list['list'] as $item) {
            $concept = Concept::firstOrCreate([
                'source_url' => $item['given_url'],
                'owner_id' => 1,
            ], [
                'parent_id' => $bookmarks->id,
                'title' => $item['given_title'],
                'summary' => $item['excerpt'],
            ]);
            if (!empty($item['tags'])) {
                $tags = array_map(function ($item) { return $item['tag']; }, $item['tags']);
                $concept->retag($tags);
            }
            $this->info('   . ' . $item['given_title'] . " -> " . $concept->id);
        }
        $pocket->update([
            'last_count' => count($list['list']),
            'last_sync_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info($this->description . '...');
        foreach (Pocket::with('user')->get() as $pocket) {
            $this->syncUser($pocket);
        }
    }
}
