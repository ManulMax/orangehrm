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

namespace OrangeHRM\Authentication\lib\utility;

use ZxcvbnPhp\Zxcvbn;

class PasswordStrengthValidation
{
    private $zxcvbn;

    public const VERY_WEAK = 0;
    public const WEAK = 1;
    public const BETTER = 2;
    public const STRONGEST = 3;

    public function __construct()
    {
        $this->zxcvbn = new Zxcvbn();
    }

    /**
     * @param string $password
     * @return int
     */
    public function checkPasswordStrength(string $password): int
    {
        $strength =  $this->zxcvbn->passwordStrength($password);
        if ($strength['score'] <= 1)return self::VERY_WEAK;
        if ($strength['score'] == 2)return self::WEAK;
        if ($strength['score'] == 3)return self::BETTER;
        if ($strength['score'] >= 4)return self::STRONGEST;
        return self::VERY_WEAK;
    }
}
