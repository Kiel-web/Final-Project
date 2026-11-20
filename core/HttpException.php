<?php
namespace Core;

class HttpException extends \RuntimeException
{
    private int $statusCode;

    public function __construct(int $statusCode, string $message = '')
    {
        parent::__construct($message, $statusCode);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
