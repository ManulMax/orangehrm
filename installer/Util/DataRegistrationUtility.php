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

namespace OrangeHRM\Installer\Util;

use DateTime;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use OrangeHRM\Config\Config;
use OrangeHRM\Installer\Util\Service\DataRegistrationService;
use OrangeHRM\Installer\Util\SystemConfig\SystemConfiguration;

class DataRegistrationUtility
{
    public const REGISTRATION_TYPE_UPGRADER_STARTED = 4;
    public const REGISTRATION_TYPE_UPGRADER_SUCCESS = 3;

    public const NOT_PUBLISHED = 0;
    public const PUBLISHED = 1;

    public const IS_INITIAL_REG_DATA_SENT = 'isInitialRegDataSent';

    private SystemConfiguration $systemConfiguration;
    private DataRegistrationService $dataRegistrationService;
    private SystemConfig $systemConfig;
    private array $initialRegistrationDataBody = [];

    public function __construct()
    {
        $this->systemConfiguration = new SystemConfiguration();
        $this->dataRegistrationService = new DataRegistrationService();
        $this->systemConfig = new SystemConfig();
    }

    /**
     * @return array
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception
     */
    private function getInitialRegistrationData(): array
    {
        $organizationName = $this->systemConfiguration->getOrganizationName();
        $country = $this->systemConfiguration->getCountry();
        $language = $this->systemConfiguration->getLanguage();
        $adminFirstName = $this->systemConfiguration->getAdminFirstName();
        $adminLastName = $this->systemConfiguration->getAdminLastName();
        $adminEmail = $this->systemConfiguration->getAdminEmail();
        $adminContactNumber = $this->systemConfiguration->getAdminContactNumber();
        $adminUserName = $this->systemConfiguration->getAdminUserName();
        $dateTime = new DateTime();
        $currentTimestamp = $dateTime->getTimestamp();

        $instanceIdentifier = $this->setInstanceIdentifier(
            $adminFirstName,
            $adminLastName,
            $organizationName,
            $adminEmail,
            $country,
            $currentTimestamp
        );
        $this->setInstanceIdentifierChecksum(
            $adminFirstName,
            $adminLastName,
            $organizationName,
            $adminEmail,
            $country,
            $currentTimestamp
        );
        return [
            $organizationName,
            $country,
            $language,
            $adminFirstName,
            $adminLastName,
            $adminEmail,
            $adminContactNumber,
            $adminUserName,
            $instanceIdentifier
        ];
    }

    /**
     * @param string $type
     * @throws \Doctrine\DBAL\Exception
     */
    public function setInitialRegistrationDataBody(string $type): void
    {
        list(
            $organizationName,
            $country,
            $language,
            $adminFirstName,
            $adminLastName,
            $adminEmail,
            $adminContactNumber,
            $adminUserName,
            $instanceIdentifier
            ) = $this->getInitialRegistrationData();

        $this->initialRegistrationDataBody = [
            'username' => $adminUserName,
            'email' => $adminEmail,
            'telephone' => $adminContactNumber,
            'admin_first_name' => $adminFirstName,
            'admin_last_name' => $adminLastName,
            'timezone' => SystemConfiguration::NOT_CAPTURED,
            'language' => $language,
            'country' => $country,
            'organization_name' => $organizationName,
            'type' => $type,
            'instance_identifier' => $instanceIdentifier,
            'system_details' => json_encode($this->systemConfig->getSystemDetails())
        ];
    }

    /**
     * @return array
     */
    public function getInitialRegistrationDataBody(): array
    {
        return $this->initialRegistrationDataBody;
    }

    /**
     * @param string $adminFirstName
     * @param string $adminLastName
     * @param string $organizationName
     * @param string $organizationEmail
     * @param string $country
     * @param int $currentTimestamp
     * @return string
     * @throws Exception
     */
    protected function setInstanceIdentifier(
        string $adminFirstName,
        string $adminLastName,
        string $organizationName,
        string $organizationEmail,
        string $country,
        int $currentTimestamp
    ): string {
        if (is_null($this->systemConfiguration->getInstanceIdentifier())) {
            $this->systemConfiguration->setInstanceIdentifier(
                $organizationName,
                $organizationEmail,
                $adminFirstName,
                $adminLastName,
                $_SERVER['HTTP_HOST'],
                $country,
                Config::PRODUCT_VERSION,
                $currentTimestamp
            );
        }
        return $this->systemConfiguration->getInstanceIdentifier();
    }

    /**
     * @param string $adminFirstName
     * @param string $adminLastName
     * @param string $organizationName
     * @param string $organizationEmail
     * @param string $country
     * @param int $currentTimestamp
     * @return string
     * @throws Exception
     */
    protected function setInstanceIdentifierChecksum(
        string $adminFirstName,
        string $adminLastName,
        string $organizationName,
        string $organizationEmail,
        string $country,
        int $currentTimestamp
    ): string {
        if (is_null($this->systemConfiguration->getInstanceIdentifierChecksum())) {
            $this->systemConfiguration->setInstanceIdentifierChecksum(
                $organizationName,
                $organizationEmail,
                $adminFirstName,
                $adminLastName,
                $_SERVER['HTTP_HOST'],
                $country,
                Config::PRODUCT_VERSION,
                $currentTimestamp
            );
        }
        return $this->systemConfiguration->getInstanceIdentifierChecksum();
    }

    /**
     * @param string $type
     * @throws \Doctrine\DBAL\Exception
     * @throws GuzzleException
     */
    public function sendRegistrationDataOnFailure(string $type)
    {
        $this->systemConfiguration->setInitialRegistrationEventQueue(
            $type,
            self::NOT_PUBLISHED
        );
        $this->setInitialRegistrationDataBody($type);
        $initialRegistrationDataBody = $this->getInitialRegistrationDataBody();
        $result = $this->dataRegistrationService->sendInitialRegistrationData($initialRegistrationDataBody);

        if ($result) {
            $this->systemConfiguration->updateInitialRegistrationEventQueue(
                $type,
                self::PUBLISHED,
                json_encode($initialRegistrationDataBody)
            );
            StateContainer::getInstance()->removeAttribute(
                DataRegistrationUtility::IS_INITIAL_REG_DATA_SENT
            );
        }
    }
}