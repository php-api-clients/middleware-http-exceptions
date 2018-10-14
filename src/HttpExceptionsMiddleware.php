<?php declare(strict_types=1);

namespace ApiClients\Middleware\HttpExceptions;

use ApiClients\Foundation\Middleware\Annotation\ThirdLast;
use ApiClients\Foundation\Middleware\MiddlewareInterface;
use ApiClients\Foundation\Middleware\PostTrait;
use ApiClients\Foundation\Middleware\PreTrait;
use ApiClients\Tools\Psr7\HttpStatusExceptions\ExceptionFactory;
use Clue\React\Buzz\Message\ResponseException;
use React\Promise\CancellablePromiseInterface;
use Throwable;
use function React\Promise\reject;

final class HttpExceptionsMiddleware implements MiddlewareInterface
{
    use PreTrait;
    use PostTrait;

    /**
     * When $throwable is a ResponseException this method will turn it into a
     * HTTP status code specific exception.
     *
     * @param  Throwable                   $throwable
     * @param  array                       $options
     * @return CancellablePromiseInterface
     *
     * @ThirdLast()
     */
    public function error(
        Throwable $throwable,
        string $transactionId,
        array $options = []
    ): CancellablePromiseInterface {
        if (!($throwable instanceof ResponseException)) {
            return reject($throwable);
        }

        return reject(ExceptionFactory::create($throwable->getResponse(), $throwable));
    }
}
