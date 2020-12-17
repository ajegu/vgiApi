<?php


namespace App\Console\Commands;


use App\Database\ClientAdapter;
use Illuminate\Console\Command;

class DatabasePurge extends Command
{
    protected $signature = 'database:purge';

    protected $description = 'Remove all items from database';

    public function __construct(
        private ClientAdapter $clientAdapter,
    ){
        parent::__construct();
    }

    public function handle(): void
    {
        $items = $this->clientAdapter->scan();
        $count = count($items);
        $this->output->info("Purge {$count} items.");
        $this->output->progressStart($count);
        foreach ($items as $item) {
            $params['Key'] = [
                'pk' => $item['pk'],
                'sk' => $item['sk'],
            ];

            $this->clientAdapter->deleteItem($params);
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->output->info("Purge done!");
    }
}
