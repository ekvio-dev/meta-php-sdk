<?php
declare(strict_types=1);


namespace Ekvio\Meta\Sdk\Common\Async;


interface Result
{
    public function get(string $url): string;
}