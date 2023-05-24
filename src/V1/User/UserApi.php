<?php

declare(strict_types=1);


namespace Ekvio\Meta\Sdk\V1\User;


use Ekvio\Meta\Sdk\V1\MetaClient;

class UserApi implements User
{
    public const USERS_SEARCH_ENDPOINT = '/users/search';

    private MetaClient $metaClient;

    public function __construct(MetaClient $metaClient)
    {
        $this->metaClient = $metaClient;
    }
    public function search(UserSearchCriteria $criteria): array
    {
        return $this->metaClient->cursorRequest(
            $criteria->method(),
            self::USERS_SEARCH_ENDPOINT,
            $criteria->queryParams(),
            $criteria->body()
        );
    }
}