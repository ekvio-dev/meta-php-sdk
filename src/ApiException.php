<?php
declare(strict_types=1);

namespace Ekvio\Meta\Sdk;

use Exception;

class ApiException extends Exception
{
    public static function apiFailed(string $message): self
    {
        throw new self($message);
    }

    public static function apiErrors(array $errors): self
    {
        $messages = [];
        foreach ($errors as $error) {
            $messages[] = sprintf('%s:%s', $error['code'], $error['message']);
        }

        throw new self(implode(',', $messages));
    }

    public static function failedRequest(string $message): self
    {
        throw new self(sprintf('Bad request: %s', $message));
    }

    public static function apiBadFormatResponse(string $message): self
    {
        throw new self(sprintf('Bad response format: %s', $message));
    }

    public static function apiErrorResponse(string $code, string $message, array $logs = []): self
    {
        $log = '';
        if($logs !== []) {
            $log = implode(', ', $logs);
        }
        throw new self(sprintf('Error response: code = %s, message = %s, log = %s', $code, $message, $log));
    }
}