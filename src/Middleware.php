<?php declare(strict_types=1);

namespace ApiClients\Middleware\Timer\Response;

use ApiClients\Foundation\Middleware\ErrorTrait;
use ApiClients\Foundation\Middleware\MiddlewareInterface;
use ApiClients\Foundation\Middleware\Priority;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use React\Promise\CancellablePromiseInterface;
use function React\Promise\resolve;

final class Middleware implements MiddlewareInterface
{
    use ErrorTrait;

    const HEADER = 'X-Middleware-Timer-Response';

    /**
     * @var float
     */
    private $time;

    /**
     * Return the processed $request via a fulfilled promise.
     * When implementing cache or other feature that returns a response, do it with a rejected promise.
     * If neither is possible, e.g. on some kind of failure, resolve the unaltered request.
     *
     * @param  RequestInterface            $request
     * @param  array                       $options
     * @return CancellablePromiseInterface
     */
    public function pre(RequestInterface $request, array $options = []): CancellablePromiseInterface
    {
        $this->time = microtime(true);

        return resolve($request);
    }

    /**
     * Return the processed $response via a promise.
     *
     * @param  ResponseInterface           $response
     * @param  array                       $options
     * @return CancellablePromiseInterface
     */
    public function post(ResponseInterface $response, array $options = []): CancellablePromiseInterface
    {
        $time = microtime(true) - $this->time;

        return resolve($response->withAddedHeader(self::HEADER, (string)$time));
    }

    /**
     * Priority ranging from 0 to 1000. Where 1000 will be executed first on `pre` and 0 last on `pre`.
     * For `post` the order is reversed.
     *
     * @return int
     */
    public function priority(): int
    {
        return Priority::FIRST;
    }
}
