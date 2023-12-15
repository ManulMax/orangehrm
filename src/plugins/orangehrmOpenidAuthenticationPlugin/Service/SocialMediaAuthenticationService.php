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

use Jumbojett\OpenIDConnectClientException;
use OrangeHRM\Authentication\Dto\UserCredential;
use OrangeHRM\Entity\AuthProviderExtraDetails;
use OrangeHRM\OpenidAuthentication\Dao\AuthProviderDao;
use OrangeHRM\OpenidAuthentication\OpenID\OpenIDConnectClient;
use OrangeHRM\OpenidAuthentication\Traits\Service\SocialMediaAuthenticationServiceTrait;

class SocialMediaAuthenticationService
{
    use SocialMediaAuthenticationServiceTrait;
    private AuthProviderDao $authProviderDao;

    public const SCOPE = 'email';
    public const REDIRECT_URL = 'https://734d-2402-d000-a500-40f9-f1e8-1109-5f81-bcf4.ngrok-free.app/openidauth/openIdCredentials';

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

//    TODO - remove if not need
    /**
     * @param OpenIDConnectClient $oidcClient
     * @throws OpenIDConnectClientException
     */
    public function handleCallback(OpenIDConnectClient $oidcClient): string
    {
//        ob_start();
//
//        $oidcClient->authenticate();
//        $output = ob_get_contents();
//        dump($output);
//        dump('here1');
//        ob_end_flush();
//        try {
//            $isAuthenticated = $oidcClient->authenticate();
//            if ($isAuthenticated) {
//                $credentials = new UserCredential($oidcClient->requestUserInfo('email'));
//                $this->authenticateUser($credentials);
//            }
//        } catch (OpenIDConnectClientException $e) {
//            throw $e;
//        }
    }

    private function authenticateUser(UserCredential $userCredential): void
    {
//        $username = $userCredential->getUsername();
    }
}