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
 * Boston, MA 02110-1301, USA
 */

namespace OrangeHRM\FreeTrial\Controller;

use OrangeHRM\Core\Api\V2\Validator\ParamRule;
use OrangeHRM\Core\Api\V2\Validator\ParamRuleCollection;
use OrangeHRM\Core\Api\V2\Validator\Rule;
use OrangeHRM\Core\Api\V2\Validator\Rules;
use OrangeHRM\Core\Controller\AbstractController;
use OrangeHRM\Core\Controller\PublicControllerInterface;
use OrangeHRM\Core\Traits\ValidatorTrait;
use OrangeHRM\Framework\Http\RedirectResponse;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Framework\Http\Response;
use OrangeHRM\Framework\Routing\UrlGenerator;
use OrangeHRM\Framework\Services;

class SubscribeFreeHostingController extends AbstractController implements PublicControllerInterface
{
    use ValidatorTrait;

    public const PARAMETER_URL = 'url';
    public const PARAMETER_COMPANY_NAME = 'companyName';
    public const PARAMETER_NO_OF_EMPLOYEES = 'noOfEmployees';
    public const PARAMETER_COUNTRY = 'country';
    public const PARAMETER_CONTACT_PERSON = 'contactPerson';
    public const PARAMETER_CONTACT_NUMBER = 'contactNumber';
    public const PARAMETER_EMAIL = 'email';

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function handle(Request $request)
    {
        if (!$this->validateParameters($request)) {
            return $this->handleBadRequest();
        }

        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $this->getContainer()->get(Services::URL_GENERATOR);
        $redirectUrl = $urlGenerator->generate(
            'free_trial_instance',
            [],
            UrlGenerator::ABSOLUTE_URL
        );
        return new RedirectResponse($redirectUrl);
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function validateParameters(Request $request): bool
    {
        $variables = $request->request->all();
        $paramRules = $this->getParamRuleCollection();
        return $this->validate($variables, $paramRules);
    }

    /**
     * @return ParamRuleCollection|null
     */
    private function getParamRuleCollection(): ?ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                self::PARAMETER_URL,
                new Rule(Rules::STRING_TYPE),
                new Rule(Rules::NOT_BLANK),
            ),
            new ParamRule(
                self::PARAMETER_COMPANY_NAME,
                new Rule(Rules::STRING_TYPE),
                new Rule(Rules::NOT_BLANK),
            ),
            new ParamRule(
                self::PARAMETER_CONTACT_NUMBER,
                new Rule(Rules::INT_TYPE),
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
                new Rule(Rules::COUNTRY_CODE),
                new Rule(Rules::NOT_BLANK),
            ),
        );
    }
}
