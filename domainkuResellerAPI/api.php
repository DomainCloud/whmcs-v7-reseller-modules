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
 **/

date_default_timezone_set('Asia/Jakarta');

require_once "../init.php";
require_once "../includes/registrarfunctions.php";
require_once "../modules/registrars/domainku/domainku.php";

use Illuminate\Database\Capsule\Manager as Capsule;

if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
    $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
    $config = getregistrarconfigoptions('domainku');
    $adminuser = 'resellerapi';

    if ($_POST['token'] == $config['Token'] && $_POST['authemail'] == $config['AuthEmail'])
    {
        $action = isset($_POST['action']) ? $_POST['action'] : "";

        if ($action == 'UpdateDomainStatus') {
            $domain = isset($_POST['domain']) ? $_POST['domain'] : "";
            $expirydate = isset($_POST['expirydate']) ? $_POST['expirydate'] : "";
            $nextduedate = isset($_POST['nextduedate']) ? $_POST['nextduedate'] : "";
            $status = isset($_POST['status']) ? $_POST['status'] : "Pending";

            $query = Capsule::table('tbldomains')
                ->select('*')
                ->whereRaw("domain = '".$domain."' AND (status = 'Active' OR status = 'Pending')")
                ->get();
            $rows = json_decode(json_encode($query), true);
            $rows = $rows[0];

            $data_update = array(
                'expirydate' => $expirydate,
                'nextduedate' => $nextduedate,
                'nextinvoicedate' => $nextduedate,
            );

            $query = Capsule::table('tbldomains')
                ->where('id', $rows['id'])
                ->update($data_update);
            
            # Send successful domain registration notification to customer.
            if ($query && $status == 'Active') {
                $command = "sendemail";
                $values["messagename"] = "Domain Registration Approved";
                $values["id"] = $rows['id'];

                $results = localAPI($command, $values, $adminuser);
            }
        }
    }
}