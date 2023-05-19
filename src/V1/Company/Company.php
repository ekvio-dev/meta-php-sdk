<?php

declare(strict_types=1);


namespace Ekvio\Meta\Sdk\V1\Company;


interface Company
{
    public function search(CompanySearchCriteria $criteria): array;
}