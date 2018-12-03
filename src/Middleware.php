<?php declare(strict_types=1);

namespace ApiClients\Middleware\Timer\Response;

use ApiClients\Foundation\Middleware\Annotation\First;
use ApiClients\Foundation\Middleware\Annotation\Last;
use ApiClients\Foundation\Middleware\MiddlewareInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use React\Promise\CancellablePromiseInterface;
use Throwable;
use function React\Promise\resolve;

final class Middleware implements MiddlewareInterface
{
    const HEADER = 'X-Middleware-Timer-Response';

    /**
     * @var float[]
     */
    private $times;

    /**
     * Return the processed $request via a fulfilled promise.
     * When implementing cache or other feature that returns a response, do it with a rejected promise.
     * If neither is possible, e.g. on some kind of failure, resolve the unaltered request.
     *
     * @param  RequestInterface            $request
     * @param  string                      $transactionId
     * @param  array                       $options
     * @return CancellablePromiseInterface
     *
     * @Last()
     */
    public function pre(
        RequestInterface $request,
        string $transactionId,
        array $options = []
    ): CancellablePromiseInterface {
        $this->times[$transactionId] = \microtime(true);

        return resolve($request);
    }

    /**
     * Return the processed $response via a promise.
     *
     * @param  ResponseInterface           $response
     * @param  string                      $transactionId
     * @param  array                       $options
     * @return CancellablePromiseInterface
     *
     * @First()
     */
    public function post(
        ResponseInterface $response,
        string $transactionId,
        array $options = []
    ): CancellablePromiseInterface {
        $time = \microtime(true) - $this->times[$transactionId];
        unset($this->times[$transactionId]);

        return resolve($response->withAddedHeader(self::HEADER, (string)$time));
    }

    /**
     * @param  Throwable                   $throwable
     * @param  string                      $transactionId
     * @param  array                       $options
     * @return CancellablePromiseInterface
     */
    public function error(
        Throwable $throwable,
        string $transactionId,
        array $options = []
    ): CancellablePromiseInterface {
        unset($this->times[$transactionId]);
    }
}
