<?php

declare(strict_types=1);


namespace Ekvio\Meta\Sdk\Tests\V1\User;


use Ekvio\Meta\Sdk\Tests\HttpDummyResult;
use Ekvio\Meta\Sdk\Tests\MockClient;
use Ekvio\Meta\Sdk\V1\MetaClient;
use Ekvio\Meta\Sdk\V1\User\UserApi;
use Ekvio\Meta\Sdk\V1\User\UserSearchCriteria;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class UserApiTest extends TestCase
{
    public function testApiUsersSearchRequest()
    {
        $container = [];
        $client = MockClient::get($container, [
            new Response(200, [], '{"data":[]}'),
            new Response(200, [], '{"data":[]}'),
        ]);
        $equeoClient = new MetaClient($client, new HttpDummyResult(), 'http://test.dev', '12345');
        $userApi = new UserApi($equeoClient);
        $userApi->search(new UserSearchCriteria());
        $userApi->search((new UserSearchCriteria())
            ->filterByCompanyId([1,2,3])
            ->filterByLogin(["test1", "test2"])
            ->withNeedChangePassword()
        );

        /** @var Request $request */
        $request = $container[0]['request'];
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('test.dev', $request->getUri()->getHost());
        $this->assertEquals('/users/search', $request->getUri()->getPath());

        /** @var Request $request */
        $request = $container[1]['request'];
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('test.dev', $request->getUri()->getHost());
        $this->assertEquals('/users/search', $request->getUri()->getPath());
        $this->assertEquals(
            '{"filters":{"company":[1,2,3],"login":["test1","test2"],"is_need_change_password":true}}',
            (string) $request->getBody());
    }
}