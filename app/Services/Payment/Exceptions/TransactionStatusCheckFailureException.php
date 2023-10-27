<?php
namespace App\Services\Payment\Exceptions;

use Illuminate\Http\Client\Response;

class TransactionStatusCheckFailureException extends TransactionException
{
    public function __construct(
        string $message,
        private ?Response $response = null,
        private ?int $retryDelayInSeconds = null,
    ) {
        parent::__construct($message);
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function shouldRetry(): bool
    {
        return !is_null($this->retryDelayInSeconds);
    }

    public function getRetryDelay(): int
    {
        return $this->retryDelayInSeconds ?? 0;
    }
}