<?php


namespace App\Console\Commands;


use App\Database\Indexes\BaseIndex;
use App\Database\Indexes\InvertedIndex;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Illuminate\Console\Command;
use JetBrains\PhpStorm\ArrayShape;
use Psr\Log\LoggerInterface;

class DatabaseCreate extends Command
{
    protected $signature = 'database:create';

    protected $description = 'Create the database';

    public function __construct(
        private DynamoDbClient $client,
        private LoggerInterface $logger,
        private BaseIndex $baseIndex,
        private InvertedIndex $invertedIndex,
        private string $tableName
    ){
        parent::__construct();
    }

    public function handle(): void
    {
        $params = $this->generateTable();
        $this->createTable($params);
        $this->enableBackup();

        $this->logger->info("The table was created successfully.");
    }

    #[ArrayShape(['TableName' => "string", 'AttributeDefinitions' => "array[]", 'KeySchema' => "array[]", 'GlobalSecondaryIndexes' => "array[]", 'ProvisionedThroughput' => "int[]"])]
    private function generateTable(): array
    {
        return [
            'TableName' => $this->tableName,
            'AttributeDefinitions' => [
                [
                    'AttributeName' => $this->baseIndex->getPartitionKey(),
                    'AttributeType' => 'S'
                ], [
                    'AttributeName' => $this->baseIndex->getSortKey(),
                    'AttributeType' => 'S'
                ]
            ],
            'KeySchema' => [
                [
                    'AttributeName' => $this->baseIndex->getPartitionKey(),
                    'KeyType' => 'HASH'
                ],[
                    'AttributeName' => $this->baseIndex->getSortKey(),
                    'KeyType' => 'RANGE'
                ]
            ],
            'GlobalSecondaryIndexes' => [
                [
                    'IndexName' => $this->invertedIndex->getName(),
                    'KeySchema' => [
                        [
                            'AttributeName' => $this->invertedIndex->getPartitionKey(),
                            'KeyType' => 'HASH'
                        ],[
                            'AttributeName' => $this->invertedIndex->getSortKey(),
                            'KeyType' => 'RANGE'
                        ]
                    ],
                    'Projection' => [
                        'ProjectionType' => 'ALL'
                    ],
                    'ProvisionedThroughput' => [
                        'ReadCapacityUnits' => 1,
                        'WriteCapacityUnits' => 1,
                    ]
                ]
            ],
            'ProvisionedThroughput' => [
                'ReadCapacityUnits' => 1,
                'WriteCapacityUnits' => 1,
            ]
        ];
    }

    private function createTable(array $params): void
    {
        try {
            $this->client->createTable($params);
        } catch (DynamoDbException $exception) {
            $this->logger->error("Can not create the table: {$exception->getMessage()}");
            die(1);
        }

        $this->logger->info("Waiting for table creation.");
        $databaseCreated = false;
        $lastStatus = '';
        do {
            try {
                $result = $this->client->describeTable(['TableName' => $this->tableName]);
                $status = $result->get('Table')['TableStatus'];

                if ($status !== $lastStatus) {
                    $this->logger->info("Current status: {$status}.");
                    $lastStatus = $status;
                }

                if ('ACTIVE' === $status) {
                    $databaseCreated = true;
                }

                sleep(1);

            } catch (DynamoDbException $exception) {
                $this->logger->error("Can not describe the table after its creation: {$exception->getMessage()}");
            }
        } while ($databaseCreated === false);
    }

    private function enableBackup(): void
    {
        $this->logger->info("Waiting for backup activation.");
        $backupEnable = false;
        $backupStatus = '';
        do {
            try {
                $result = $this->client->describeContinuousBackups(['TableName' => $this->tableName]);
                $status = $result->get('ContinuousBackupsDescription')['ContinuousBackupsStatus'];
                if ($backupStatus !== $status) {
                    $this->logger->info("Current backup status: {$status}");
                }

                if ($status === 'ENABLED') {
                    $backupEnable = true;
                }
            } catch (DynamoDbException $exception) {
                $this->logger->info("Can not describe the table backup: {$exception->getMessage()}");
            }
            sleep(1);
        } while ($backupEnable === false);

        $this->logger->info("Activate point in time recovery");
        try {
            $this->client->updateContinuousBackups([
                'TableName' => $this->tableName,
                'PointInTimeRecoverySpecification' => [
                    'PointInTimeRecoveryEnabled' => true
                ]
            ]);
        } catch (DynamoDbException $exception) {
            $this->logger->error("Can not update backups: {$exception->getMessage()}");
        }
    }
}
