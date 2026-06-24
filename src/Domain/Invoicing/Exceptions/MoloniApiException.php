<?php

namespace Domain\Invoicing\Exceptions;

use Exception;

class MoloniApiException extends Exception
{
    public function __construct(
        string $message,
        int $code = 0,
        public ?string $endpoint = null,
        public ?array $response = null,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }

    public function getResponse(): ?array
    {
        return $this->response;
    }

    public function context(): array
    {
        return [
            'endpoint' => $this->endpoint,
            'response' => $this->response,
            'code' => $this->getCode(),
        ];
    }
}
