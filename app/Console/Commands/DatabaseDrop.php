<?php


namespace App\Console\Commands;


use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Illuminate\Console\Command;
use Psr\Log\LoggerInterface;

class DatabaseDrop extends Command
{
    protected $signature = 'database:drop';

    protected $description = 'Remove the database';

    public function __construct(
        private DynamoDbClient $client,
        private LoggerInterface $logger,
        private string $tableName
    )
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $params = [
            'TableName' => $this->tableName
        ];

        $tableExists = true;
        try {
            $this->client->describeTable($params);
        } catch (DynamoDbException) {
            $tableExists = false;
            $this->logger->info("No table found for {$this->tableName}.");
        }

        if ($tableExists) {
            try {
                $this->client->deleteTable($params);
            } catch (DynamoDbException $exception) {
                $this->logger->error("Can not delete the table: {$exception->getMessage()}");
            }

            $this->logger->info("Waiting for table deletion.");
            $databaseDeleted = false;
            do {
                try {
                    $this->client->describeTable(['TableName' => $this->tableName]);
                } catch (DynamoDbException) {
                    $databaseDeleted = true;
                }
            } while ($databaseDeleted === false);

            $this->logger->info("The table was deleted successfully.");
        }

    }
}
