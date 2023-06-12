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

namespace OrangeHRM\FreeTrial\Service;

use GuzzleHttp\Client;

class ClientService
{
    public const ON_DEMAND_ACCESS_TOKEN_CREATED_AT = 'ondemand_access_token_created_at';
    public const ON_DEMAND_ACCESS_TOKEN = 'ondemand_access_token';

    protected ?FreeTrialService $freeTrialService = null;

    public function getFreeTrialService(): FreeTrialService
    {
        if (!$this->freeTrialService instanceof FreeTrialService){
            $this->freeTrialService = new FreeTrialService();
        }
        return $this->freeTrialService;
    }

//    public function getNewAccessToken(): array
//    {
//        $client = new Client();
//        $clientId = $this->getFreeTrialService()->getOnDemandClientId();
//        $clientSecret = $this->getFreeTrialService()->getOnDemandClientSecret();
//        $onDemandUrl = $this->getFreeTrialService()->getInstanceUrl();
//
//    }
}
