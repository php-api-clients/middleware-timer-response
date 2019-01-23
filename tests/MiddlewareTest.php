<?php declare(strict_types=1);

namespace ApiClients\Tests\Middleware\Timer\Response;

use ApiClients\Middleware\Timer\Response\Middleware;
use ApiClients\Tools\TestUtilities\TestCase;
use function Clue\React\Block\await;
use React\EventLoop\Factory;
use RingCentral\Psr7\Request;
use RingCentral\Psr7\Response;

/**
 * @internal
 */
final class MiddlewareTest extends TestCase
{
    public function testPost(): void
    {
        $idA = 'Abc';
        $idB = 'aBc';
        $response = new Response(200, []);
        $middleware = new Middleware();
        $middleware->pre(new Request('GET', 'https://example.com/'), $idA, []);
        $middleware->pre(new Request('GET', 'https://example.com/'), $idB, []);
        $responseObject = await($middleware->post($response, $idA, []), Factory::create());
        self::assertTrue((float)$responseObject->getHeaderLine(Middleware::HEADER) < 1);
        \sleep(1);
        $responseObject = await($middleware->post($response, $idB, []), Factory::create());
        self::assertTrue((float)$responseObject->getHeaderLine(Middleware::HEADER) > 1);
    }
}
