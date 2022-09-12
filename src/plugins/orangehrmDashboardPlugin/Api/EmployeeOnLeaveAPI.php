<?php
/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA  02110-1301, USA
 */

namespace OrangeHRM\Dashboard\Api;

use OrangeHRM\Core\Api\CommonParams;
use OrangeHRM\Core\Api\V2\CollectionEndpoint;
use OrangeHRM\Core\Api\V2\Endpoint;
use OrangeHRM\Core\Api\V2\EndpointCollectionResult;
use OrangeHRM\Core\Api\V2\EndpointResult;
use OrangeHRM\Core\Api\V2\ParameterBag;
use OrangeHRM\Core\Api\V2\RequestParams;
use OrangeHRM\Core\Api\V2\Validator\ParamRule;
use OrangeHRM\Core\Api\V2\Validator\ParamRuleCollection;
use OrangeHRM\Core\Api\V2\Validator\Rule;
use OrangeHRM\Core\Api\V2\Validator\Rules;
use OrangeHRM\Core\Traits\Service\DateTimeHelperTrait;
use OrangeHRM\Dashboard\Api\Model\EmployeeOnLeaveModel;
use OrangeHRM\Leave\Traits\Service\LeavePeriodServiceTrait;
use OrangeHRM\Dashboard\Dto\EmployeeOnLeaveSearchFilterParams;
use OrangeHRM\Dashboard\Traits\Service\EmployeeOnLeaveServiceTrait;

class EmployeeOnLeaveAPI extends Endpoint implements CollectionEndpoint
{
    use DateTimeHelperTrait;
    use LeavePeriodServiceTrait;
    use EmployeeOnLeaveServiceTrait;

    public const PARAMETER_DATE = 'date';
    public const PARAMETER_LEAVE_PERIOD = 'leavePeriod';

    /**
     * @inheritDoc
     */
    public function getAll(): EndpointResult
    {
        $employeeOnLeaveSearchFilterParams = new EmployeeOnLeaveSearchFilterParams();

        $this->setSortingAndPaginationParams($employeeOnLeaveSearchFilterParams);
        $date = $this->getRequestParams()->getDateTime(
            RequestParams::PARAM_TYPE_QUERY,
            self::PARAMETER_DATE,
            null,
            $this->getDateTimeHelper()->getNow()
        );

        $employeeOnLeaveSearchFilterParams->setDate($date);

        $empLeaveList = $this->getEmployeeOnLeaveService()->getEmployeeOnLeaveDao()
            ->getEmployeeOnLeaveList($employeeOnLeaveSearchFilterParams);
        $employeeCount = $this->getEmployeeOnLeaveService()->getEmployeeOnLeaveDao()
            ->getEmployeeOnLeaveCount($employeeOnLeaveSearchFilterParams);

        return new EndpointCollectionResult(
            EmployeeOnLeaveModel::class,
            $empLeaveList,
            new ParameterBag([
                CommonParams::PARAMETER_TOTAL => $employeeCount,
                self::PARAMETER_LEAVE_PERIOD => $this->getLeavePeriodService()->getCurrentLeavePeriod()
            ])
        );
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                self::PARAMETER_DATE,
                new Rule(Rules::API_DATE)
            ),
            ...$this->getSortingAndPaginationParamsRules(EmployeeOnLeaveSearchFilterParams::ALLOWED_SORT_FIELDS),
        );
    }

    /**
     * @inheritDoc
     */
    public function create(): EndpointResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function delete(): EndpointResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForDelete(): ParamRuleCollection
    {
        throw $this->getNotImplementedException();
    }
}