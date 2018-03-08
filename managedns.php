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
 * @package    Infinys - DNS Management Systems for Reseller
 * @copyright  Copyright (c) PT Infinys System Indonesia 2015
 **/

define("CLIENTAREA", true);

require_once "init.php";
require_once "includes/domainfunctions.php";
require_once "includes/whoisfunctions.php";
require_once "includes/registrarfunctions.php";
require_once "dcconfig.php";

use Illuminate\Database\Capsule\Manager as Capsule;

$capatacha = clientAreaInitCaptcha();
$pagetitle = "DNS Management";
$breadcrumbnav = "<a href=\"index.php\">Portal Home</a> > <a href=\"clientarea.php\">Client Area</a> > <a href=\"clientarea.php?action=domains\">My Domains</a> > <a href=\"clientarea.php?action=domaindetails&id=".$_REQUEST['domainid']."\">Domain</a> > <a href=\"\">DNS Management</a>";

$templatefile = "domaindns";
$pageicon = "images/domains_big.gif";
initialiseClientArea($pagetitle, $pageicon, $breadcrumbnav);
$availabilityresults = array();
$search_tlds = array();
$tldslist = array();
$userid = (isset($_SESSION['uid']) ? $_SESSION['uid'] : "");

# Basic params
$action = (isset( $_REQUEST['action'] ) ? mysql_real_escape_string($_REQUEST['action']) : "");
$domainid = (isset( $_REQUEST['domainid'] ) ? mysql_real_escape_string($_REQUEST['domainid']) : "");
$successful = (isset( $_REQUEST['success'] ) ? mysql_real_escape_string($_REQUEST['success']) : "");
$do = (isset( $_REQUEST['do'] ) ? mysql_real_escape_string($_REQUEST['do']) : "addrecord");

# DNS params
$dnsrecid = (isset( $_POST['dnsrecid'] ) ? $_POST['dnsrecid'] : "");
$dnsrecordtype = (isset( $_POST['dnsrecordtype'] ) ? $_POST['dnsrecordtype'] : "");
$dnsrecordhost = (isset( $_POST['dnsrecordhost'] ) ? $_POST['dnsrecordhost'] : "");
$dnsrecordaddress = (isset( $_POST['dnsrecordaddress'] ) ? $_POST['dnsrecordaddress'] : "");
$dnsrecordttl = (isset( $_POST['dnsrecordttl'] ) ? $_POST['dnsrecordttl'] : "");
$dnsrecordpriority = (isset( $_POST['dnsrecordpriority'] ) ? $_POST['dnsrecordpriority'] : "");
$delid = (isset( $_REQUEST['id'] ) ? mysql_real_escape_string($_REQUEST['id']) : "");

if (empty($domainid)) {
	header("Location: clientarea.php");
} else {
	$config = getregistrarconfigoptions('domainku');

	# Check if module parameters are sane
	if (empty($config["AuthEmail"]) && empty($config["Token"]) && empty($config["Endpoint"])) {
		throw new Exception('System configuration error(1), please contact your provider for further details.');
	}
	
	$result = Capsule::table('tbldomains')
		->where([
			['id', '=', $domainid],
			['userid', '=', $userid]
		])
		->get()[0];
	
	$domain = $result->domain;

	$domainparts = explode(".", $domain, 2);
    $sld = $domainparts[0];
    $tld = $domainparts[1];

	# Domain validation: domain status is active, this user has rights to manage this domain, .ID domain.
	if ($result->status == 'Active' && substr($tld,-2) == 'id') {

		$data = array(
			"token"             => $config["Token"],
	    	"authemail"         => $config["AuthEmail"],
			"dnsaction"			=> $action,
		    "domain"            => $domain,
		    "dnsrecid"			=> $dnsrecid,
		    "dnsrecordhost"		=> $dnsrecordhost,
		    "dnsrecordtype"		=> $dnsrecordtype,
		    "dnsrecordaddress"	=> $dnsrecordaddress,
		    "dnsrecordttl"		=> $dnsrecordttl,
		    "dnsrecordpriority"	=> $dnsrecordpriority,
		    "delid"				=> $delid,
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $dns_api_endpoint);
		curl_setopt($ch, CURLOPT_TIMEOUT, 0);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		$output = curl_exec($ch);

		if ($output == false) {
		    $res = array("error"=>curl_error($ch));
		} else {
		    $res = json_decode($output, true);
		}
		$errors = array_filter($res);

		$dnsrecords = array();
		if (!empty($errors)) {
			foreach ($res as $row) {
				if ($row['type'] == 'A' || $row['type'] == 'CNAME' || $row['type'] == 'MX' || $row['type'] == 'TXT') {
					if ($row['hostname'] == $domain) {
						$row['hostname'] = str_replace("$domain","@",$row['hostname']);
					} else {
						$row['hostname'] = str_replace(".$domain","",$row['hostname']);
					}
					$dnsrecords[] = $row;
				}
			}
		}

		if ($action == 'addrecord') {
			if ((filter_var($dnsrecordaddress, FILTER_VALIDATE_IP) !== false && $dnsrecordtype == 'A') || $dnsrecordtype == 'CNAME' || $dnsrecordtype == 'MX' || $dnsrecordtype == 'NS' || $dnsrecordtype == 'TXT') {
				if (!$res['error']) redir("domainid=$domainid&success=true");
				else $error = $res['error'];
			} else $error = "Please insert a valid IP address (e.g: 127.0.0.1).";
		} elseif ($action == 'saverecords') {
			if (!$res['error']) redir("domainid=$domainid&success=true");
			else $error = $res['error'];
		} elseif ($action == 'deleterecord') {
			if (!$res['error']) redir("domainid=$domainid&success=true");
			else $error = $res['error'];
		}

		$smartyvalues['domainid'] = $domainid;
		$smartyvalues['domain'] = $domain;
		$smartyvalues['dnsrecords'] = $dnsrecords;
		$smartyvalues['error'] = $error;
		$smartyvalues['successful'] = $successful;
		$smartyvalues['do'] = $do;

		outputClientArea($templatefile);
	} else {
		$smartyvalues['domain'] = $domain;
		$smartyvalues['do'] = $do;
		$smartyvalues['external'] = true;
		$smartyvalues['error'] = "Domain not found or registrar function is not supported.";
		outputClientArea($templatefile);
	}
}