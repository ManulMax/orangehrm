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
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use OrangeHRM\Core\Traits\Service\DateTimeHelperTrait;

class ClientService
{
    use AuthUserTrait;
    use DateTimeHelperTrait;

    public const ON_DEMAND_ACCESS_TOKEN_CREATED_AT = 'ondemand_access_token_created_at';
    public const ON_DEMAND_ACCESS_TOKEN = 'ondemand_access_token';
    public const ENDPOINT_TOKEN = '/oauth/v2/token';
    public const TOKEN_VALID_TIME_PERIOD = 55; // in minutes
    public const GRANT_TYPE = 'client_credentials';
    public const DEFAULT_CONTENT_TYPE = "application/json";

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
     * @return Client
     */
    public function getApiClient(): Client
    {
        $url = $this->getFreeTrialService()->getInstanceUrl();
        if (!isset($this->apiClient)) {
            $this->apiClient = new Client(['base_uri' => $url, 'verify' => false]);
        }
        return $this->apiClient;
    }

    /**
     * @return bool
     */
    public function isValidToken(): bool
    {
        if ($this->getAuthUser()->getAttribute(ClientService::ON_DEMAND_ACCESS_TOKEN) == null) {
            return false;
        }
        $createdDateTime = $this->getAuthUser()->getAttribute(ClientService::ON_DEMAND_ACCESS_TOKEN);
        $createdDateTimeInMinutes = strtotime($createdDateTime) / 60;
        $timeDifference  = $createdDateTimeInMinutes - (strtotime($this->getDateTimeHelper()->getNow()->format('Y-m-d H:i:s'))/60);

        return $timeDifference <= self::TOKEN_VALID_TIME_PERIOD;
    }

    private function getNewAccessToken(): void
    {
        $client = new Client();
        $clientId = $this->getFreeTrialService()->getOnDemandClientId();
        $clientSecret = $this->getFreeTrialService()->getOnDemandClientSecret();
        $onDemandUrl = $this->getFreeTrialService()->getInstanceUrl();
        $url = $onDemandUrl . self::ENDPOINT_TOKEN;
        $response = $client->post(
            $url,
            [
                'json' => [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type' => self::GRANT_TYPE,
                ],
                'headers' => [
                    'Content-Type' => self::DEFAULT_CONTENT_TYPE,
                    'Accept' => self::DEFAULT_CONTENT_TYPE,
                ],
                'verify' => false
            ]
        );
        $body = json_decode($response->getBody(), true);
        $responseCode = $response->getStatusCode();

        if ($responseCode == 200) {
            $this->getAuthUser()->setAttribute(ClientService::ON_DEMAND_ACCESS_TOKEN, $body['access_token']);
            $this->getAuthUser()->setAttribute(
                ClientService::ON_DEMAND_ACCESS_TOKEN_CREATED_AT,
                $this->getDateTimeHelper()->getNow()->format('Y-m-d H:i:s')
            );
        }
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        if (!$this->isValidToken()) {
            $this->getNewAccessToken();
        }
        return $this->getAuthUser()->getAttribute(ClientService::ON_DEMAND_ACCESS_TOKEN);
    }
}
