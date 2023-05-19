<?php

declare(strict_types=1);


namespace Ekvio\Meta\Sdk\V1\Company;


use Ekvio\Meta\Sdk\Common\Criteria;
use Ekvio\Meta\Sdk\Common\Method;
use RuntimeException;

class CompanySearchCriteria extends Criteria
{
    private const MAX_FILTER_COUNT = 500;
    public function __construct(){}

    public function filterById(array $companies): self
    {
        if (count($companies) > self::MAX_FILTER_COUNT) {
            throw new RuntimeException(sprintf('Maximum count items is %s', self::MAX_FILTER_COUNT));
        }

        $companies = array_filter($companies, function ($companyId) {
            return is_int($companyId) && $companyId > 0;
        });

        return $this->cloneWithFilter('id', $companies);
    }

    public function filterByName(array $name): self
    {
        if (count($name) > self::MAX_FILTER_COUNT) {
            throw new RuntimeException(sprintf('Maximum count items is %s', self::MAX_FILTER_COUNT));
        }

        $name = array_filter($name, function ($login) {
            return is_string($login) && mb_strlen($login) > 0;
        });

        return $this->cloneWithFilter('name', $name);
    }

    public function method(): string
    {
        return Method::POST;
    }
}