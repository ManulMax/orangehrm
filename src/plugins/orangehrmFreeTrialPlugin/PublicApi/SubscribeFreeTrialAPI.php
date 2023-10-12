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

namespace OrangeHRM\FreeTrial\PublicApi;

use OrangeHRM\Admin\Service\CountryService;
use OrangeHRM\Core\Api\V2\CollectionEndpoint;
use OrangeHRM\Core\Api\V2\Endpoint;
use OrangeHRM\Core\Api\V2\EndpointResourceResult;
use OrangeHRM\Core\Api\V2\EndpointResult;
use OrangeHRM\Core\Api\V2\Exception\BadRequestException;
use OrangeHRM\Core\Api\V2\Exception\InvalidParamException;
use OrangeHRM\Core\Api\V2\Model\ArrayModel;
use OrangeHRM\Core\Api\V2\RequestParams;
use OrangeHRM\Core\Api\V2\Validator\ParamRule;
use OrangeHRM\Core\Api\V2\Validator\ParamRuleCollection;
use OrangeHRM\Core\Api\V2\Validator\Rule;
use OrangeHRM\Core\Api\V2\Validator\Rules;
use OrangeHRM\Core\Traits\ServiceContainerTrait;
use OrangeHRM\Framework\Services;
use OrangeHRM\FreeTrial\Service\FreeTrialService;

class SubscribeFreeTrialAPI extends Endpoint implements CollectionEndpoint
{
    use ServiceContainerTrait;

    public const PARAMETER_COMPANY_NAME = 'companyName';
    public const PARAMETER_NO_OF_EMPLOYEES = 'noOfEmployee';
    public const PARAMETER_COUNTRY = 'country';
    public const PARAMETER_CONTACT_PERSON = 'contactPersonName';
    public const PARAMETER_CONTACT_NUMBER = 'contactNumber';
    public const PARAMETER_EMAIL = 'email';

    /**
     * @var FreeTrialService|null
     */
    protected ?FreeTrialService $freeTrialService = null;

    /**
     * @return FreeTrialService
     */
    public function getFreeTrialService(): FreeTrialService
    {
        if (!$this->freeTrialService instanceof FreeTrialService) {
            $this->freeTrialService = new FreeTrialService();
        }
        return $this->freeTrialService;
    }

    /**
     * @inheritDoc
     */
    public function getAll(): EndpointResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @return CountryService
     */
    public function getCountryService(): CountryService
    {
        return $this->getContainer()->get(Services::COUNTRY_SERVICE);
    }

    /**
     * @inheritDoc
     */
    public function create(): EndpointResult
    {
        if ($this->getFreeTrialService()->isSubscribed()) {
            throw new BadRequestException('This Instance have already subscribed.');
        }

        $response = $this->freeTrialService->saveSubscription($this->getParametersArray());
        if ($response != null) {
            $this->freeTrialService->setSubscribed();
        }

        $data = $response[0];
        $country = $this->getCountryService()->getCountryByCountryName($data['country']);

        $formattedCountry = [
            'id' => $country->getCountryCode(),
            'label' => $country->getCountryName()
        ];

        $subscriberDetails = [
            'id' => $data['id'],
            'companyName' => $data['companyName'],
            'contactNumber' => $data['contactNumber'],
            'contactPersonName' => $data['contactPersonName'],
            'country' => $formattedCountry,
            'email' => $data['email'],
            'noOfEmployees' => $data['noOfEmployees'],
        ];

        return new EndpointResourceResult(
            ArrayModel::class,
            $subscriberDetails
        );
    }


    private function getParametersArray(): array
    {
        $companyName = $this->getRequestParams()->getString(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_COMPANY_NAME,
        );

        $contactNumber = $this->getRequestParams()->getString(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_CONTACT_NUMBER,
        );

        $contactPerson = $this->getRequestParams()->getString(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_CONTACT_PERSON,
        );

        $noOfEmployees = $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_NO_OF_EMPLOYEES,
        );

        $email = $this->getRequestParams()->getString(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_EMAIL,
        );

        $country = $this->getRequestParams()->getString(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_COUNTRY,
        );

        return [
            'country' => $country,
            'companyName' => $companyName,
            'contactNumber' => $contactNumber,
            'email' => $email,
            'noOfEmployees' => $noOfEmployees,
            'contactPersonName' => $contactPerson,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                self::PARAMETER_COMPANY_NAME,
                new Rule(Rules::STRING_TYPE),
                new Rule(Rules::NOT_BLANK),
            ),
            new ParamRule(
                self::PARAMETER_CONTACT_NUMBER,
                new Rule(Rules::STRING_TYPE),
                new Rule(Rules::NOT_BLANK),
            ),
            new ParamRule(
                self::PARAMETER_CONTACT_PERSON,
                new Rule(Rules::STRING_TYPE),
                new Rule(Rules::NOT_BLANK),
            ),
            new ParamRule(
                self::PARAMETER_NO_OF_EMPLOYEES,
                new Rule(Rules::INT_TYPE),
            ),
            new ParamRule(
                self::PARAMETER_EMAIL,
                new Rule(Rules::EMAIL),
                new Rule(Rules::NOT_BLANK),
            ),
            new ParamRule(
                self::PARAMETER_COUNTRY,
                new Rule(Rules::STRING_TYPE),
                new Rule(Rules::NOT_BLANK),
            ),
        );
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
