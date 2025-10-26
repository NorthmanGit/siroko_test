<?php
namespace App\Infrastructure;

use Psr\Log\LoggerInterface;

final class LoggingQueryHandlerDecorator
{
    public function __construct(
        private object $inner,
        private LoggerInterface $logger
    ) {}

    public function __invoke(object $query)
    {
        $this->logger->info(sprintf('Handling %s', get_class($query)));
        try {
            $result = ($this->inner)($query);
            $this->logger->info(sprintf('%s handled successfully', get_class($query)));
            return $result;
        } catch (\Throwable $e) {
            $this->logger->error(sprintf(
                'Error in handling %s: %s',
                get_class($query),
                $e->getMessage()
            ));
            throw $e;
        }
    }
}
