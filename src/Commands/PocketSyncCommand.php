<?php

namespace Knowfox\Pocket\Commands;

use Illuminate\Console\Command;
use Knowfox\Pocket\Models\Pocket;
use Duellsy\Pockpack\Pockpack;

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

        $as_array = true;
        $list = $pockpack->retrieve([
            'state' => 'all',
            'detailType' => 'complete',
            'count' => 3,
        ], /*as_array*/true);
        var_dump($list);
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
