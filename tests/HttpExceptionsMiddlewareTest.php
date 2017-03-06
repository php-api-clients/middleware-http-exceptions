<?php declare(strict_types=1);

namespace ApiClients\Tests\Middleware\HttpExceptions;

use ApiClients\Foundation\Middleware\Priority;
use ApiClients\Middleware\HttpExceptions\HttpExceptionsMiddleware;
use ApiClients\Tools\Psr7\HttpStatusExceptions\ExceptionFactory;
use ApiClients\Tools\TestUtilities\TestCase;
use Clue\React\Buzz\Message\ResponseException;
use Exception;
use React\EventLoop\Factory;
use RingCentral\Psr7\Response;
use Throwable;
use function Clue\React\Block\await;
use function React\Promise\reject;

final class HttpExceptionsMiddlewareTest extends TestCase
{
    /**
     * Ensure we run second last so the last can be logging or so that stores information.
     */
    public function testPriority()
    {
        self::assertSame(
            Priority::SECOND_LAST,
            (new HttpExceptionsMiddleware())->priority()
        );
    }

    public function provideThrowables()
    {
        yield [
            new Exception('foo.bar'),
            new Exception('foo.bar'),
        ];

        foreach (ExceptionFactory::STATUS_CODE_EXCEPTION_MAP as $code => $exception) {
            $response = new Response($code);
            $responseException = new ResponseException(
                new Response($code)
            );
            yield [
                $responseException,
                $exception::create($response, $responseException)
            ];
        }
    }

    /**
     * @dataProvider provideThrowables
     */
    public function testException(Throwable $input, Throwable $output)
    {
        $result = null;

        try {
            await(
                (new HttpExceptionsMiddleware())->error($input, []),
                Factory::create()
            );
        } catch (Throwable $result) {
        }

        self::assertNotNull($result);
        self::assertEquals($output, $result);
    }
}
