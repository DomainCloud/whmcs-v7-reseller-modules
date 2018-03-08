<?php
/**
 * Copyright (c) 2017, Infinys System Indonesia
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Formatting Phone Number
 *
 * @package    Infinys
 * @copyright  Copyright (c) PT Infinys System Indonesia 2017
 */

use WHMCS\Database\Capsule;

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

function formatphonefordomainku($vars) {
    $domain = $vars['domain'];

    $data = Capsule::table('tbldomains')
        ->select('registrar')
        ->where('domain', '=', $domain)
        ->first();

    $registrar = $data->registrar;

    if ($registrar == "domainku") {
        global $params;

        $phonenumber = $params['phonenumber'];
        $phonecc = $params['phonecc'];
        $params['phonenumber'] = '+'.$phonecc.'.'.ltrim($phonenumber, '0');

        $adminphonenumber = $params['adminphonenumber'];
        if ($adminphonenumber) {
            $params['adminphonenumber'] = '+'.$phonecc.'.'.ltrim($adminphonenumber, '0');
        }
    }
}

add_hook("PreDomainRegister",10,"formatphonefordomainku");
