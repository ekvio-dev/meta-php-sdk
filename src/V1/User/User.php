<?php

declare(strict_types=1);


namespace Ekvio\Meta\Sdk\V1\User;


interface User
{
    public function search(UserSearchCriteria $criteria): array;
}