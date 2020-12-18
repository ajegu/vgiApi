<?php


namespace App\Console\Commands;


use App\Database\ClientAdapter;
use App\Database\Indexes\BaseIndex;
use Illuminate\Console\Command;

class DatabasePurge extends Command
{
    protected $signature = 'database:purge';

    protected $description = 'Remove all items from database';

    public function __construct(
        private ClientAdapter $clientAdapter,
        private BaseIndex $baseIndex,
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
                $this->baseIndex->getPartitionKey() => $item[$this->baseIndex->getPartitionKey()],
                $this->baseIndex->getSortKey() => $item[$this->baseIndex->getSortKey()],
            ];

            $this->clientAdapter->deleteItem($params);
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->output->info("Purge done!");
    }
}
