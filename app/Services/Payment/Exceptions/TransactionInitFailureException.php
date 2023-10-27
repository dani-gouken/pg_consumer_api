<?php
namespace App\Services\Payment\Exceptions;

use Illuminate\Http\Client\Response;

class TransactionInitFailureException extends \Exception
{
    public function __construct(
        string $message,
        private ?Response $response = null,
    ) {
        parent::__construct($message);
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }
}