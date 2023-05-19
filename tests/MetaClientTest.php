<?php

declare(strict_types=1);

namespace Ekvio\Meta\Sdk\Tests;

use Ekvio\Meta\Sdk\ApiException;
use Ekvio\Meta\Sdk\Common\Async\HttpFileResult;
use Ekvio\Meta\Sdk\Tests\HttpDummyResult;
use Ekvio\Meta\Sdk\V1\MetaClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MetaClientTest extends TestCase
{
    public function testRaiseExceptionIfNoApiHost()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('API host required');
        $client = new Client();
        new MetaClient($client, new HttpFileResult(), '', '12345');
    }

    public function testRaiseExceptionIfNoApiToken()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('API token required');
        $client = new Client();
        new MetaClient($client, new HttpFileResult(), 'http://test.dev', '');
    }

    public function testRawEqueoClientRequest()
    {
        $container = [];
        $client = $this->getMockClient($container);
        $equeoClient = new MetaClient($client, new HttpDummyResult(), 'http://test.dev', '12345');
        $equeoClient->request('GET', '/test/me', ['param' => 1]);

        foreach ($container as $transaction) {
            /** @var Request $request */
            $request = $transaction['request'];
            $this->assertEquals('GET', $request->getMethod());
            $this->assertEquals('test.dev', $request->getUri()->getHost());
            $this->assertEquals('/test/me', $request->getUri()->getPath());
            $this->assertEquals('param=1', $request->getUri()->getQuery());
        }
    }

    public function testRaiseExceptionWhenDeferredRequestReturnBadIntegrationStructureResponse()
    {
        $this->expectException(ApiException::class);
        $container = [];
        $client = $this->getMockClient($container, [
            new Response(200, ['X-Foo' => 'Bar'], '{"data":{"integra":100}}')
        ]);

        $equeoClient = new MetaClient($client, new HttpDummyResult(), 'http://test.dev', '12345');
        $equeoClient->deferredRequest('POST', '/v2/users/sync', ['data' => []]);
    }

    public function testRaiseExceptionWhenDeferredRequestReturnNotNaturalIntegrationId()
    {
        $this->expectException(ApiException::class);
        $container = [];
        $client = $this->getMockClient($container, [
            new Response(200, ['X-Foo' => 'Bar'], '{"data":{"integration":-100}}')
        ]);
        $equeoClient = new MetaClient($client, new HttpDummyResult(), 'http://test.dev', '12345');
        $equeoClient->deferredRequest('POST', '/v2/users/sync', ['data' => []]);
    }

    public function testRaiseExceptionWhenDeferredRequestReturnErrorsInIntegrationResponse()
    {
        $this->expectException(ApiException::class);
        $container = [];
        $client = $this->getMockClient($container, [
            new Response(200, ['X-Foo' => 'Bar'], '{"data":{"integration":100}}'),
            new Response(200, [], '{"errors":[{"code": 100, "message": "Bad request"}]}')
        ]);
        $equeoClient = new MetaClient($client, new HttpDummyResult(), 'http://test.dev', '12345');
        $equeoClient->deferredRequest('POST', '/v2/users/sync', ['data' => []]);
    }

    public function testRaiseExceptionWhenDeferredRequestReturnInvalidStructureResponse()
    {
        $this->expectException(ApiException::class);
        $container = [];
        $client = $this->getMockClient($container, [
            new Response(200, ['X-Foo' => 'Bar'], '{"data":{"integration":100}}'),
            new Response(200, [], '{"data":{"invalid_status":"failed"}}')
        ]);
        $equeoClient = new MetaClient($client, new HttpDummyResult(), 'http://test.dev', '12345');
        $equeoClient->deferredRequest('POST', '/v2/users/sync', ['data' => []]);
    }

    public function testRaiseExceptionWhenDeferredRequestReturnResponseWithoutFileLink()
    {
        $this->expectException(ApiException::class);
        $container = [];
        $client = $this->getMockClient($container, [
            new Response(200, ['X-Foo' => 'Bar'], '{"data":{"integration":100}}'),
            new Response(200, [], '{"data":{"status":"progress"}}'),
            new Response(200, [], '{"data":{"status":"progress"}}'),
            new Response(200, [], '{"data":{"status":"completed"}}'),
        ]);
        $equeoClient = new MetaClient($client, new HttpDummyResult(), 'http://test.dev', '12345', [
            'request_interval' => false
        ]);
        $equeoClient->deferredRequest('POST', '/users/sync', ['data' => []]);
    }

    public function testRaiseExceptionWhenDeferredRequestFailedAfterExceedRetryCount()
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Bad request: integration 100 status progress was not change or data from link  is empty');

        $container = [];
        $client = $this->getMockClient($container, [
            new Response(200, ['X-Foo' => 'Bar'], '{"data":{"integration":100}}'),
            new Response(200, [], '{"data":{"status":"progress"}}'),
            new Response(200, [], '{"data":{"status":"progress"}}'),
            new Response(200, [], '{"data":{"status":"progress"}}'),
            new Response(200, [], '{"data":{"status":"progress"}}'),
            new Response(200, [], '{"data":{"status":"completed"}}'),
        ]);
        $equeoClient = new MetaClient($client, new HttpDummyResult(), 'http://test.dev', '12345', [
            'request_interval' => false,
            'retry_count' => 3
        ]);
        $equeoClient->deferredRequest('POST', '/users/sync', ['data' => []]);
    }

    public function testRaiseExceptionWhenDeferredRequestFailedAfterExceedRetryCountWithEmptyBody()
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Bad request: integration 100 status completed was not change or data from link http://dev.link is empty');

        $container = [];
        $client = $this->getMockClient($container, [
            new Response(200, ['X-Foo' => 'Bar'], '{"data":{"integration":100}}'),
            new Response(200, [], '{"data":{"status":"completed","file":"http://dev.link"}}'),
            new Response(200, [], '{"data":{"status":"completed","file":"http://dev.link"}}'),
            new Response(200, [], '{"data":{"status":"completed","file":"http://dev.link"}}'),
            new Response(200, [], '{"data":{"status":"completed","file":"http://dev.link"}}'),
            new Response(200, [], '{"data":{"status":"completed","file":"http://dev.link"}}'),
            new Response(200, [], '{"data":{"status":"completed","file":"http://dev.link"}}'),
        ]);
        $equeoClient = new MetaClient($client, new HttpDummyResult('null'), 'http://test.dev', '12345', [
            'request_interval' => false,
            'retry_count' => 3
        ]);
        $equeoClient->deferredRequest('POST', '/users/sync', ['data' => []]);
    }

    public function testSuccessDeferredRequest()
    {
        $responses = [
            new Response(200, ['X-Foo' => 'Bar'], '{"data":{"integration":100}}'),
            new Response(200, [], '{"data":{"status":"progress"}}'),
            new Response(200, [], '{"data":{"status":"progress"}}'),
            new Response(200, [], '{"data":{"status":"progress"}}'),
            new Response(200, [], '{"data":{"status":"progress"}}'),
            new Response(200, [], '{"data":{"status":"completed", "file": "http://result.link"}}'),
        ];

        $container = [];
        $client = $this->getMockClient($container, $responses);

        $equeoClient = new MetaClient($client, new HttpDummyResult('{"data": [{"field": "Hello"}]}'), 'http://test.dev', '12345', [
            'request_interval' => false
        ]);
        $response = $equeoClient->deferredRequest('POST', '/users/sync', ['data' => []]);

        $this->assertCount(count($responses), $container);
        $this->assertEquals(['data' => [['field' => 'Hello']]], $response);
    }

    public function testSuccessCursorRequest()
    {
        $responses = [
            new Response(200, [], '{"data":[{"user": 1}], "meta": {"cursor":{"total": 2,"count": 1, "links": {"next": "/v2/url?after=token1", "current": "/v2/url"}}}}'),
            new Response(200, [], '{"data":[{"user": 2}], "meta": {"cursor":{"total": 2,"count": 1, "links": {"next":null, "current":"/v2/url?after=token1"}}}}'),
        ];

        $container = [];
        $client = $this->getMockClient($container, $responses);

        $equeoClient = new MetaClient($client, new HttpDummyResult(), 'http://test.dev', '12345');
        $response = $equeoClient->cursorRequest('GET', 'users/search');

        $this->assertCount(count($responses), $container);
        $this->assertEquals([['user' => 1], ['user' => 2]], $response);
    }

    private function getMockClient(array &$container, array $responses = []): Client
    {
        if(!$responses) {
            $responses = [new Response(200, ['X-Foo' => 'Bar'], '{"data": []}')];
        }

        $mock = new MockHandler($responses);
        $history = Middleware::history($container);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        return new Client(['handler' => $handlerStack]);
    }
}