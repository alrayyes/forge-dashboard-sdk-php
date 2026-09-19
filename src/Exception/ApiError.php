<?php

declare(strict_types=1);

namespace ForgeDashboard\Exception;

use ForgeDashboard\Generated\ApiException;
use Throwable;

/**
 * Thrown for any forge-dashboard response carrying an error body --
 * every 4xx/5xx in the spec shares the same {error} shape
 * (rules/sdk-generation.md's "Client shape": map errors to typed
 * exceptions carrying the HTTP status and the parsed error body, never a
 * bare string or the raw response).
 */
final class ApiError extends \RuntimeException
{
    public function __construct(
        public readonly int $statusCode,
        string $apiMessage,
        ?Throwable $previous = null,
    ) {
        $message = \sprintf('forge-dashboard: %d: %s', $statusCode, $apiMessage);

        parent::__construct($message, $statusCode, $previous);
    }

    /**
     * Builds an ApiError from the ApiException the generated Api classes
     * throw on any non-2xx response. Prefers the deserialized Error model
     * openapi-generator attaches for status codes the spec documents
     * explicitly; falls back to decoding the raw JSON body itself for a
     * status code the spec doesn't list (a 429 or 500, say), since the
     * generated code never deserializes those.
     */
    public static function fromGeneratedException(ApiException $exception): self
    {
        return new self(
            statusCode: $exception->getCode(),
            apiMessage: self::decodeMessage($exception),
            previous: $exception,
        );
    }

    private static function decodeMessage(ApiException $exception): string
    {
        $responseObject = $exception->getResponseObject();
        if (\is_object($responseObject) && method_exists($responseObject, 'getError')) {
            $error = $responseObject->getError();
            if (\is_string($error)) {
                return $error;
            }
        }

        $body = $exception->getResponseBody();
        $raw = \is_string($body) ? $body : (string) json_encode($body);
        $decoded = json_decode($raw, true);
        if (\is_array($decoded) && isset($decoded['error']) && \is_string($decoded['error'])) {
            return $decoded['error'];
        }

        return $exception->getMessage();
    }
}
