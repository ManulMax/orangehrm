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
     * @return bool
     */
    private function getSubscribeStatus(): bool
    {
        $freeTrialService  = new FreeTrialService();
        return $freeTrialService->isSubscribed();
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
    public function preRender(Request $request): void
    {
        $component = new Component('subscribe-free-hosting');
        $currentURL = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $instanceUrl = rtrim($currentURL, '/trial/subscribeFreeHosting');

        $preloadedValues = $this->getFreeTrialService()->getPreloadedValues();
        $instanceDetails = $preloadedValues['response'];
        $countryName = $instanceDetails['country'];
        $formattedCountry = (object) [];
        if ($countryName !== null) {
            $country = $this->getCountryService()->getCountryByCountryName($countryName);
            $formattedCountry = [
                'id' => $country->getCountryCode(),
                'label' => $country->getCountryName()
            ];
        }

        /** @var CountryService $countryService */
        $countryService = $this->getContainer()->get(Services::COUNTRY_SERVICE);
        $component->addProp(new Prop('countries', Prop::TYPE_ARRAY, $countryService->getCountryArray()));
        $component->addProp(new Prop('url', Prop::TYPE_STRING, $instanceUrl));
        $component->addProp(new Prop('company-name', Prop::TYPE_STRING, $instanceDetails['companyName']));
        $component->addProp(new Prop('contact-number', Prop::TYPE_NUMBER, $instanceDetails['contactNumber']));
        $component->addProp(new Prop('email', Prop::TYPE_STRING, $instanceDetails['email']));
        $component->addProp(new Prop('contact-person-name', Prop::TYPE_STRING, $instanceDetails['contactPersonName']));
        $component->addProp(new Prop('country', Prop::TYPE_OBJECT, $formattedCountry));
        $component->addProp(new Prop('is-subscribed', Prop::TYPE_BOOLEAN, $this->getSubscribeStatus()));

        $this->setComponent($component);
    }
}
