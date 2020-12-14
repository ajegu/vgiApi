<?php


namespace App\Database;


use App\Database\Indexes\IndexInterface;
use App\Exceptions\ItemNotFound;
use Aws\DynamoDb\Marshaler;

class ClientFacade
{
    public function __construct(
        private ClientAdapter $clientAdapter,
        private Marshaler $marshaler
    ) {}

    public function findByPk(string $value, IndexInterface $index): array
    {
        $params = [];
        $partitionKey = 'pk';

        if (null !== $index) {
            $params['IndexName'] = $index->getName();
            $partitionKey = $index->getPartitionKey();
        }

        $params['KeyConditionExpression'] = $partitionKey . ' = :value';
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

    /**
     * @param string $pkValue
     * @param string $skValue
     * @return array
     * @throws ItemNotFound
     */
    public function findOneByPkAndSk(string $pkValue, string $skValue): array
    {
        $params = [];
        $params['KeyConditionExpression'] = 'pk = :pkValue and sk = :skValue';
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
            'pk' => $this->marshaler->marshalValue($pkValue),
            'sk' => $this->marshaler->marshalValue($skValue),
        ];

        $this->clientAdapter->deleteItem($params);
    }
}
