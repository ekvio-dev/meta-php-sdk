<?php
declare(strict_types=1);

namespace Ekvio\Meta\Sdk\Tests;

use Ekvio\Meta\Sdk\Common\Async\Result;

class HttpDummyResult implements Result
{
    private $response;
    public function __construct(string $response = '{"data":[]}')
    {
        $this->response = $response;
    }

    public function get(string $url): string
    {
        return $this->response;
    }
}