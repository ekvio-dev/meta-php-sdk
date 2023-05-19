<?php

declare(strict_types=1);


namespace Ekvio\Meta\Sdk\Tests\V1\Company;


use Ekvio\Meta\Sdk\Tests\HttpDummyResult;
use Ekvio\Meta\Sdk\Tests\MockClient;
use Ekvio\Meta\Sdk\V1\Company\CompanyApi;
use Ekvio\Meta\Sdk\V1\Company\CompanySearchCriteria;
use Ekvio\Meta\Sdk\V1\MetaClient;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class CompanyApiTest extends TestCase
{
    public function testApiSearchCompany()
    {
        $container = [];
        $client = MockClient::get($container, [
            new Response(200, [], '{"data":[]}'),
            new Response(200, [], '{"data":[]}'),
        ]);
        $equeoClient = new MetaClient($client, new HttpDummyResult(), 'http://test.dev', '12345');
        $userApi = new CompanyApi($equeoClient);
        $userApi->search(new CompanySearchCriteria());
        $userApi->search((new CompanySearchCriteria())
            ->filterById([1,2,3])
            ->filterByName(["test1", "test2"])
        );

        /** @var Request $request */
        $request = $container[0]['request'];
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('test.dev', $request->getUri()->getHost());
        $this->assertEquals('/companies/search', $request->getUri()->getPath());

        /** @var Request $request */
        $request = $container[1]['request'];
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('test.dev', $request->getUri()->getHost());
        $this->assertEquals('/companies/search', $request->getUri()->getPath());
        $this->assertEquals(
            '{"filters":{"id":[1,2,3],"name":["test1","test2"]}}',
            (string) $request->getBody());
    }
}