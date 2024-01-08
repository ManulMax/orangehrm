<?php
/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software: you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with OrangeHRM.
 * If not, see <https://www.gnu.org/licenses/>.
 */

namespace OrangeHRM\OpenidAuthentication\Service;

use OrangeHRM\Entity\AuthProviderExtraDetails;
use OrangeHRM\Framework\Routing\UrlGenerator;
use OrangeHRM\Framework\Services;
use OrangeHRM\OpenidAuthentication\Dao\AuthProviderDao;
use OrangeHRM\OpenidAuthentication\OpenID\OpenIDConnectClient;
use OrangeHRM\OpenidAuthentication\Traits\Service\SocialMediaAuthenticationServiceTrait;

class SocialMediaAuthenticationService
{
    use SocialMediaAuthenticationServiceTrait;
    private AuthProviderDao $authProviderDao;

    public const SCOPE = 'email';

    /**
     * @return AuthProviderDao
     */
    public function getAuthProviderDao(): AuthProviderDao
    {
        return $this->authProviderDao ??= new AuthProviderDao();
    }

    /**
     * @param AuthProviderExtraDetails $provider
     * @param string $scope
     * @param string $redirectUrl
     *
     * @return OpenIDConnectClient
     */
    public function initiateAuthentication(AuthProviderExtraDetails $provider, string $scope, string $redirectUrl): OpenIDConnectClient
    {
        $oidcClient = new OpenIDConnectClient(
            $provider->getOpenIdProvider()->getProviderUrl(),
            $provider->getClientId(),
            $provider->getClientSecret()
        );

        $oidcClient->addScope([$scope]);
        $oidcClient->setRedirectURL($redirectUrl);

        return $oidcClient;
    }

    /**
     * @return string
     */
    public function getRedirectURL(): string
    {
        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $this->getContainer()->get(Services::URL_GENERATOR);
        return  $urlGenerator->generate('auth_oidc_login_redirect', [], UrlGenerator::ABSOLUTE_URL);
    }

    /**
     * @return string
     */
    public function getScope(): string
    {
        return self::SCOPE;
    }
}
