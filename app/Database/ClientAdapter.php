<?php


namespace App\Database;


use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Psr\Log\LoggerInterface;

class ClientAdapter
{
    public function __construct(
        private DynamoDbClient $client,
        private LoggerInterface $logger,
        private string $tableName
    ) {}

    public function query($params): ?array
    {
        $params['ReturnConsumedCapacity'] = 'TOTAL';
        $params['TableName'] = $this->tableName;

        $this->logger->debug('Query to DynamoDB:', $params);

        try {
            $result = $this->client->query($params);
        } catch (DynamoDbException $exception) {
            $this->logger->error($exception->getMessage(), [
                'request' => $exception->getRequest(),
                'response' => $exception->getResponse(),
                'trace' => $exception->getTraceAsString()
            ]);

            throw $exception;
        }

        $this->logger->debug('Result from DynamoDB:', [
            'Count' => $result->get('Count'),
            'ScannedCount' => $result->get('ScannedCount'),
            'ConsumedCapacity' => $result->get('ConsumedCapacity'),
            'Items' => $result->get('Items'),
            '@metadata' => $result->get('@metadata'),
        ]);

        return $result->get('Items');
    }

    public function putItem(array $params): void
    {
        $params['ReturnConsumedCapacity'] = 'TOTAL';
        $params['TableName'] = $this->tableName;

        $this->logger->debug('PutItem to DynamoDB:', $params);

        try {
            $result = $this->client->putItem($params);
        } catch (DynamoDbException $exception) {
            $this->logger->error($exception->getMessage(), [
                'request' => $exception->getRequest(),
                'response' => $exception->getResponse(),
                'trace' => $exception->getTraceAsString()
            ]);

            throw $exception;
        }

        $this->logger->debug('Result from DynamoDB:', [
            'Count' => $result->get('Count'),
            'ScannedCount' => $result->get('ScannedCount'),
            'ConsumedCapacity' => $result->get('ConsumedCapacity'),
            '@metadata' => $result->get('@metadata'),
        ]);
    }

    public function deleteItem($params): void
    {
        $params['ReturnConsumedCapacity'] = 'TOTAL';
        $params['TableName'] = $this->tableName;

        $this->logger->debug('Delete to DynamoDB:', $params);

        try {
            $result = $this->client->deleteItem($params);
        } catch (DynamoDbException $exception) {
            $this->logger->error($exception->getMessage(), [
                'request' => $exception->getRequest(),
                'response' => $exception->getResponse(),
                'trace' => $exception->getTraceAsString()
            ]);

            throw $exception;
        }

        $this->logger->debug('Result from DynamoDB:', [
            'Count' => $result->get('Count'),
            'ScannedCount' => $result->get('ScannedCount'),
            'ConsumedCapacity' => $result->get('ConsumedCapacity'),
            'Items' => $result->get('Items'),
            '@metadata' => $result->get('@metadata'),
        ]);
    }
}
