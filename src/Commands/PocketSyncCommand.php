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
    protected $signature = 'pocket:sync {--since=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync contents from Pocket';

    protected $since = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function syncUser($pocket) 
    {
        $this->info(' - ' .  $pocket->user->email);
        $user_id = $pocket->user->id;
        $consumer_key = env('POCKET_CONSUMER_KEY');
        $pockpack = new Pockpack($consumer_key, $pocket->access_token);

        if ($this->since) {
            $since = $this->since;
        }
        else {
            $since = Carbon::now()->subMinutes(10);
        }

        $as_array = true;
        $list = $pockpack->retrieve([
            'state' => 'all',
            'detailType' => 'complete',
            'since' => $since->getTimestamp(),
        ], /*as_array*/true);

        $bookmarks = Concept::where('parent_id', null)
            ->where('owner_id', $user_id)
            ->where('title', 'Bookmarks')
            ->first();

        $pocket->saveBookmarks($list['list'], $bookmarks);

        $last_sync_at = Carbon::now()->format('Y-m-d H:i:s');
        $cnt = count($list['list']);

        $pocket->update([
            'last_count' => $cnt,
            'last_sync_at' => $last_sync_at,
        ]);
        error_log("[{$last_sync_at}] Pocket {$pocket->user->email} n={$cnt}\n", 3, "/tmp/knowfox.log");
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info($this->description . '...');

        $since = $this->option('since');
        if ($since) {
            $this->since = Carbon::parse($since);
            $humane = $this->since->toDateTimeString();
            $this->info(" ... since {$humane}");
        }

        foreach (Pocket::with('user')->get() as $pocket) {
            $this->syncUser($pocket);
        }
    }
}
