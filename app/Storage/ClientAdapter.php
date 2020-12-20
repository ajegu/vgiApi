<?php


namespace App\Storage;


use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\UploadedFile;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class ClientAdapter
{
    public function __construct(
        private S3Client $client,
        private LoggerInterface $logger,
        private string $bucketName
    ) {}

    /**
     * @param UploadedFile $file
     * @return string
     * @throws FileNotFoundException
     */
    public function putObject(UploadedFile $file): string
    {
        $filename = Uuid::uuid4();
        $key = "{$filename}.{$file->getClientOriginalExtension()}";
        try {
            $params = [
                'Bucket' => $this->bucketName,
                'Key' => $key,
                'Body' => $file->get(),
                'ACL' => "public-read",
                'ContentType' => $file->getClientMimeType()
            ];
        } catch (FileNotFoundException $exception) {
            $this->logger->error("Call to method Get on UploadedFile throw exception: {$exception->getMessage()}");
            throw $exception;
        }

        $this->logger->debug("Call S3 putObject", [
            'Bucket' => $this->bucketName,
            'Key' => $key,
            'ContentType' => $file->getClientMimeType()
        ]);

        try {
            $this->client->putObject($params);
        } catch (S3Exception $exception) {
            $this->logger->error($exception->getMessage());
            throw $exception;
        }

        return $key;
    }

    public function deleteObject(string $key): void
    {
        $params = [
            'Bucket' => $this->bucketName,
            'Key' => $key,
        ];

        $this->logger->debug("Call S3 deleteObject", $params);

        try {
            $this->client->deleteObject($params);
        } catch (S3Exception $exception) {
            $this->logger->error($exception->getMessage());
            throw $exception;
        }
    }
}
