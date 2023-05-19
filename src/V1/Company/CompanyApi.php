<?php

declare(strict_types=1);


namespace Ekvio\Meta\Sdk\V1\Company;


use Ekvio\Meta\Sdk\V1\MetaClient;

class CompanyApi implements Company
{
    public const COMPANIES_SEARCH_ENDPOINT = '/companies/search';

    private MetaClient $metaClient;

    public function __construct(MetaClient $metaClient)
    {
        $this->metaClient = $metaClient;
    }
    public function search(CompanySearchCriteria $criteria): array
    {
        $response = $this->metaClient->request(
            $criteria->method(),
            self::COMPANIES_SEARCH_ENDPOINT,
            $criteria->queryParams(),
            $criteria->body()
        );

        return $response['data'];
    }
}