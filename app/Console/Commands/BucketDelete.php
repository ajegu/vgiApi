<?php


namespace App\Console\Commands;


use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Illuminate\Console\Command;
use Psr\Log\LoggerInterface;

class BucketDelete extends Command
{
    protected $signature = 'bucket:delete';

    protected $description = 'Delete the bucket';

    public function __construct(
        private S3Client $client,
        private LoggerInterface $logger,
        private string $bucketName,
    ){
        parent::__construct();
    }

    public function handle(): void
    {
        if ($this->client->doesBucketExist($this->bucketName)) {

            $this->logger->info("Waiting for deleting all objects.");
            try {
                $result = $this->client->listObjectsV2([
                    'Bucket' => $this->bucketName
                ]);
                $objects = $result->get('Contents') ?? [];

            } catch (S3Exception $exception) {
                $this->logger->error("Can not list objects from the bucket {$this->bucketName}: {$exception->getMessage()}");
                die(1);
            }

            foreach ($objects as $object) {
                try {
                    $this->client->deleteObject([
                        'Bucket' => $this->bucketName,
                        'Key' => $object['Key']
                    ]);
                } catch (S3Exception $exception) {
                    $this->logger->error("Can not delete object {$object['Key']} from the bucket {$this->bucketName}: {$exception->getMessage()}");
                    die(1);
                }
            }

            try {
                $this->client->deleteBucket([
                    'Bucket' => $this->bucketName
                ]);
            } catch (S3Exception $exception) {
                $this->logger->error("Can not delete the bucket {$this->bucketName}: {$exception->getMessage()}");
                die(1);
            }

            $this->logger->info("Waiting for deleting the bucket.");
            do {
                $bucketExists = $this->client->doesBucketExist($this->bucketName);
                sleep(1);
            } while ($bucketExists === true);

            $this->logger->info("The bucket {$this->bucketName} was deleted.");
        } else {
            $this->logger->info("No bucket found for {$this->bucketName}.");
        }
    }
}
