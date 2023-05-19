<?php
declare(strict_types=1);

namespace Ekvio\Meta\Sdk\V1\User;

use Ekvio\Meta\Sdk\Common\Criteria;
use Ekvio\Meta\Sdk\Common\Method;
use RuntimeException;

class UserSearchCriteria extends Criteria
{
    private const MAX_FILTER_COUNT = 500;
    public function __construct(){}

    public function filterByCompanyId(array $companies): self
    {
        if (count($companies) > self::MAX_FILTER_COUNT) {
            throw new RuntimeException(sprintf('Maximum count items is %s', self::MAX_FILTER_COUNT));
        }

        $companies = array_filter($companies, function ($companyId) {
            return is_int($companyId) && $companyId > 0;
        });

        return $this->cloneWithFilter('company', $companies);
    }

    public function filterByLogin(array $logins): self
    {
        if (count($logins) > self::MAX_FILTER_COUNT) {
            throw new RuntimeException(sprintf('Maximum count items is %s', self::MAX_FILTER_COUNT));
        }

        $logins = array_filter($logins, function ($login) {
            return is_string($login) && mb_strlen($login) > 0;
        });

        return $this->cloneWithFilter('login', $logins);
    }

    public function withNeedChangePassword(): self
    {
        return $this->cloneWithFilter('is_need_change_password', true);
    }

    public function withoutNeedChangePassword(): self
    {
        return $this->cloneWithFilter('is_need_change_password', false);
    }

    public function method(): string
    {
        return Method::POST;
    }
}