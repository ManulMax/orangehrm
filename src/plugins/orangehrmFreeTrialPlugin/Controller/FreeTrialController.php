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

use OrangeHRM\Admin\Service\CountryService;
use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Traits\ServiceContainerTrait;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Framework\Services;
use OrangeHRM\FreeTrial\Service\FreeTrialService;

class FreeTrialController extends AbstractVueController
{
    use ServiceContainerTrait;

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
    public function preRender(Request $request): void
    {
        $component = new Component('subscribe-free-hosting');
        $preloadedValues = $this->getFreeTrialService()->getPreloadedValues();
        $instanceDetails = $preloadedValues['response'];

        /** @var CountryService $countryService */
        $countryService = $this->getContainer()->get(Services::COUNTRY_SERVICE);
        $component->addProp(new Prop('countries', Prop::TYPE_ARRAY, $countryService->getCountryArray()));
        $component->addProp(new Prop('url', Prop::TYPE_STRING, $this->getFreeTrialService()->getInstanceUrl()));
        $component->addProp(new Prop('companyName', Prop::TYPE_STRING, $instanceDetails['companyName']));
        $component->addProp(new Prop('contactNumber', Prop::TYPE_NUMBER, $instanceDetails['contactNumber']));
        $component->addProp(new Prop('email', Prop::TYPE_STRING, $instanceDetails['email']));
        $component->addProp(new Prop('contactPersonName', Prop::TYPE_STRING, $instanceDetails['contactPersonName']));
        $component->addProp(new Prop('country', Prop::TYPE_STRING, $instanceDetails['country']));
        $component->addProp(new  Prop('noOfEmployees', Prop::TYPE_NUMBER, $instanceDetails['noOfEmployees']));

        $this->setComponent($component);
    }
}
