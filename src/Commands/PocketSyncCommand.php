<?php

namespace Knowfox\Pocket\Commands;

use Illuminate\Console\Command;
use Knowfox\Pocket\Models\Pocket;
use Knowfox\Pocket\Services\PocketService;
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
        $this->info('    . ' .  $pocket->user->email);
        $service = new PocketService();
        $user_id = $pocket->user->id;

        if ($this->since) {
            $since = $this->since;
        }
        else {
            $since = Carbon::now()->subMinutes(10);
        }
        $info = $service->saveBookmarks($user_id, $since);
        if (!$info) {
            $this->error('Invalid token. Please renew');
            return;
        }

        $last_sync_at = Carbon::now()->format('Y-m-d H:i:s');

        $cnt = count($info['list']);
        $this->info('    . saved ' . $cnt . ' bookmarks');

        $info['pocket']->update([
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

        $this->info(' - ' . Pocket::count() . ' users have link to pocket');

        foreach (Pocket::with('user')->get() as $pocket) {
            $this->syncUser($pocket);
        }
    }
}
