<?php


namespace App\Helpers;


use DateTime;
use Exception;
use Psr\Log\LoggerInterface;

class DateTimeHelper
{
    const DATE_FORMAT = 'Y-m-d H:i:s';

    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function convertToString(?DateTime $dateTime): string
    {
        if ($dateTime) {
            return $dateTime->format(self::DATE_FORMAT);
        }

        return '';
    }

    public function createFromString(?string $date): ?DateTime
    {
        $dateTime = null;

        if ($date) {
            try {
                $dateTime =  new DateTime($date);
            } catch (Exception $exception) {
                $this->logger->warning($exception->getMessage(), [
                    'trace' => $exception->getTraceAsString()
                ]);
            }
        }

        return $dateTime;
    }
}
