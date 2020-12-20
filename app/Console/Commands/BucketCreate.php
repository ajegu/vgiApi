<?php


namespace App\Console\Commands;


use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Illuminate\Console\Command;
use Psr\Log\LoggerInterface;

class BucketCreate extends Command
{
    protected $signature = 'bucket:create';

    protected $description = 'Create the bucket';

    public function __construct(
        private S3Client $client,
        private LoggerInterface $logger,
        private string $bucketName,
    ){
        parent::__construct();
    }

    public function handle(): void
    {
        if (!$this->client->doesBucketExist($this->bucketName)) {
            try {
                $result = $this->client->createBucket([
                    'Bucket' => $this->bucketName
                ]);
            } catch (S3Exception $exception) {
                $this->logger->error("Can not create the bucket {$this->bucketName}: {$exception->getMessage()}");
                die(1);
            }

            $this->logger->info("Waiting for creating the bucket.");
            do {
                $bucketExists = $this->client->doesBucketExist($this->bucketName);
                sleep(1);
            } while ($bucketExists === false);

            $this->logger->info("The bucket {$this->bucketName} was created at location {$result->get('Location')}.");
        } else {
            $this->logger->info("The bucket {$this->bucketName} already exists.");
        }
    }
}
