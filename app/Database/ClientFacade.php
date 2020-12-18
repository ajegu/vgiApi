<?php


namespace App\Database;


use App\Database\Indexes\BaseIndex;
use App\Database\Indexes\IndexInterface;
use App\Exceptions\ItemNotFound;
use Aws\DynamoDb\Marshaler;

class ClientFacade
{
    public function __construct(
        private ClientAdapter $clientAdapter,
        private Marshaler $marshaler,
        private BaseIndex $baseIndex,
    ) {}

    public function findByPk(string $value, IndexInterface $index = null): array
    {
        $params = [];
        $partitionKey = $this->baseIndex->getPartitionKey();

        if (null !== $index) {
            $params['IndexName'] = $index->getName();
            $partitionKey = $index->getPartitionKey();
        }

        $params['KeyConditionExpression'] = "{$partitionKey} = :value";
        $params['ExpressionAttributeValues'] = [':value' => $this->marshaler->marshalValue($value)];

        $result = $this->clientAdapter->query($params);

        $items = [];
        if (!empty($result)) {
            $items = array_map(function(array $item) {
                return $this->marshaler->unmarshalItem($item);
            }, $result);
        }

        return $items;
    }

    public function findByPkAndSk(string $pkValue, string $skValue, IndexInterface $index = null): array
    {
        $params = [];
        $partitionKey = $this->baseIndex->getPartitionKey();
        $sortKey = $this->baseIndex->getSortKey();

        if (null !== $index) {
            $params['IndexName'] = $index->getName();
            $partitionKey = $index->getPartitionKey();
            $sortKey = $index->getSortKey();
        }

        $params['KeyConditionExpression'] = "{$partitionKey} = :pkValue and begins_with({$sortKey}, :skValue)";
        $params['ExpressionAttributeValues'] = [
            ':pkValue' => $this->marshaler->marshalValue($pkValue),
            ':skValue' => $this->marshaler->marshalValue($skValue),
        ];

        $result = $this->clientAdapter->query($params);

        $items = [];
        if (!empty($result)) {
            $items = array_map(function(array $item) {
                return $this->marshaler->unmarshalItem($item);
            }, $result);
        }

        return $items;
    }

    /**
     * @param string $pkValue
     * @param string $skValue
     * @return array
     * @throws ItemNotFound
     */
    public function findOneByPkAndSk(string $pkValue, string $skValue): array
    {
        $partitionKey = $this->baseIndex->getPartitionKey();
        $sortKey = $this->baseIndex->getSortKey();

        $params = [];
        $params['KeyConditionExpression'] = "{$partitionKey} = :pkValue and {$sortKey} = :skValue";
        $params['ExpressionAttributeValues'] = [
            ':pkValue' => $this->marshaler->marshalValue($pkValue),
            ':skValue' => $this->marshaler->marshalValue($skValue),
        ];

        $result = $this->clientAdapter->query($params);

        if (empty($result)) {
            throw new ItemNotFound("Item not found with PK: '{$pkValue}' and SK: '{$skValue}'");
        }

        return $this->marshaler->unmarshalItem($result[0]);
    }

    public function save(array $item): void
    {
        $params = [];
        $params['Item'] = $this->marshaler->marshalItem($item);

        $this->clientAdapter->putItem($params);
    }

    public function delete(string $pkValue, string $skValue): void
    {
        $params = [];
        $params['Key'] = [
            $this->baseIndex->getPartitionKey() => $this->marshaler->marshalValue($pkValue),
            $this->baseIndex->getSortKey() => $this->marshaler->marshalValue($skValue),
        ];

        $this->clientAdapter->deleteItem($params);
    }
}
