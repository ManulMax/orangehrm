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

namespace OrangeHRM\OpenidAuthentication\Service;

use OrangeHRM\Admin\Dao\UserDao;
use OrangeHRM\Admin\Dto\UserSearchFilterParams;
use OrangeHRM\Authentication\Dto\UserCredential;
use OrangeHRM\Authentication\Exception\AuthenticationException;
use OrangeHRM\Authentication\Service\AuthenticationService;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use OrangeHRM\Entity\AuthProviderExtraDetails;
use OrangeHRM\Entity\OpenIdProvider;
use OrangeHRM\Entity\OpenIdUserIdentity;
use OrangeHRM\Entity\User;
use OrangeHRM\Framework\Routing\UrlGenerator;
use OrangeHRM\Framework\Services;
use OrangeHRM\OpenidAuthentication\Dao\AuthProviderDao;
use OrangeHRM\OpenidAuthentication\OpenID\OpenIDConnectClient;
use OrangeHRM\OpenidAuthentication\Traits\Service\SocialMediaAuthenticationServiceTrait;

class SocialMediaAuthenticationService
{
    use SocialMediaAuthenticationServiceTrait;
    use AuthUserTrait;

    private AuthenticationService $authenticationService;
    private AuthProviderDao $authProviderDao;
    private UserDao $userDao;

    public const SCOPE = 'email';

    /**
     * @return AuthProviderDao
     */
    public function getAuthProviderDao(): AuthProviderDao
    {
        return $this->authProviderDao ??= new AuthProviderDao();
    }

    /**
     * @return UserDao
     */
    public function getUserDao(): UserDao
    {
        return $this->userDao ??= new UserDao();
    }

    /**
     * @return AuthenticationService
     */
    private function getAuthenticationService(): AuthenticationService
    {
        return $this->authenticationService ??= new AuthenticationService();
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
        //TODO
        $url = $urlGenerator->generate('auth_oidc_login_redirect', [], UrlGenerator::ABSOLUTE_URL);
        return str_replace('http', 'https', $url);
    }

    /**
     * @return string
     */
    public function getScope(): string
    {
        return self::SCOPE;
    }

    /**
     * @param UserCredential $userCredential
     * @return array
     */
    public function getOIDCUser(UserCredential $userCredential): array
    {
        $userSearchFilterParams = new UserSearchFilterParams();
        $userSearchFilterParams->setUsername($userCredential->getUsername());

        return $this->getUserDao()->searchSystemUsers($userSearchFilterParams);
    }

    /**
     * @param User $user
     * @param OpenIdProvider $provider
     *
     * @return void
     */
    public function setOIDCUserIdentity(User $user, OpenIdProvider $provider): void
    {
        $openIdUserIdentity = new OpenIdUserIdentity();
        $openIdUserIdentity->setUser($user);
        $openIdUserIdentity->setOpenIdProvider($provider);

        $this->getAuthProviderDao()->saveUserIdentity($openIdUserIdentity);
    }

    /**
     * @throws AuthenticationException
     */
    public function handleCallback(User $user): bool
    {
        return $this->getAuthenticationService()->setCredentialsForUser($user);
    }
}
