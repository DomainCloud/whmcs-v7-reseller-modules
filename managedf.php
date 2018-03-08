<?php
/**
 * Copyright (c) 2015, Infinys System Indonesia
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
 * @package    Infinys - Domain/URL Forwarding Systems for Reseller
 * @copyright  Copyright (c) PT Infinys System Indonesia 2015
 **/

define("CLIENTAREA", true);
require_once "init.php";
require_once "includes/domainfunctions.php";
require_once "includes/registrarfunctions.php";
require_once "dcconfig.php";

$capatacha = clientAreaInitCaptcha();
$pagetitle = $_LANG['domaintitle'];
$breadcrumbnav = "<a href=\"index.php\">" . $_LANG['globalsystemname'] . "</a> > <a href=\"domainchecker.php\">" . $_LANG['domaintitle'] . "</a>";
$templatefile = "domainforwarder";
$pageicon = "images/domains_big.gif";
initialiseClientArea($pagetitle, $pageicon, $breadcrumbnav);
$availabilityresults = array();
$search_tlds = array();
$tldslist = array();

$userid = (isset($_SESSION['uid']) ? $_SESSION['uid'] : "");
$action = (isset( $_REQUEST['action'] ) ? mysql_real_escape_string($_REQUEST['action']) : "");
$domainid = (isset( $_REQUEST['domainid'] ) ? mysql_real_escape_string($_REQUEST['domainid']) : "");
$successful = (isset( $_REQUEST['success'] ) ? mysql_real_escape_string($_REQUEST['success']) : "");
$do = (isset( $_REQUEST['do'] ) ? mysql_real_escape_string($_REQUEST['do']) : "addrecord");

# DNS records.
$recid = (isset( $_POST['recid'] ) ? preg_replace('/\s+/', '', $_POST['recid']) : "");
$origin_domain = (isset( $_POST['origin_domain'] ) ? preg_replace('/\s+/', '', $_POST['origin_domain']) : "");
$destination_domain = (isset( $_POST['destination_domain'] ) ? preg_replace('/\s+/', '', $_POST['destination_domain']) : "");
$type = (isset( $_POST['type'] ) ? preg_replace('/\s+/', '', $_POST['type']) : "");
$option = (isset( $_POST['option'] ) ? preg_replace('/\s+/', '', $_POST['option']) : "2");
$delid = (isset( $_REQUEST['id'] ) ? mysql_real_escape_string($_REQUEST['id']) : "");

if (empty($domainid)) {
	header("Location: clientarea.php");
} else {
	$config = getregistrarconfigoptions('domainku');

	$query = select_query( "tbldomains", "", array('id'=>$domainid,"userid"=>$userid) );
	$result = mysql_fetch_array($query);
	$domain = $result['domain'];

	# Domain validation.
	if ($result['status'] == 'Active') {
		$data = array(
			"token"=>$config["Token"],
	    	"authemail"=>$config["AuthEmail"],
			"action"=>$action,
		    "domain"=>$domain,
		    "recid"=>$recid,
		    "origin_domain"=>$origin_domain,
		    "destination_domain"=>$destination_domain,
		    "type"=>$type,
		    "option"=>$option,
		    "delid"=>$delid,
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $df_api_endpoint);
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

		if (!empty($errors)) {
			foreach ($res as $row) {
				if ($row['origin_domain'] == $domain) {
					$row['origin_domain'] = str_replace("$domain","@",$row['origin_domain']);
				} else {
					$row['origin_domain'] = str_replace(".$domain","",$row['origin_domain']);
				}
				$dfrecords[] = $row;
			}
		}

		if ($action == 'addrecord' || $action == 'saverecords' || $action == 'deleterecord') {
			if (!$res['error']) redir("domainid=$domainid&success=true");
			else $error = $res['error'];
		}

		$smartyvalues['domainid'] = $domainid;
		$smartyvalues['domain'] = $domain;
		$smartyvalues['dfrecords'] = $dfrecords;
		$smartyvalues['error'] = $error;
		$smartyvalues['successful'] = $successful;
		$smartyvalues['do'] = $do;
		outputClientArea($templatefile);
	} else {
		$smartyvalues['domain'] = $domain;
		$smartyvalues['do'] = $do;
		$smartyvalues['external'] = true;
		$smartyvalues['error'] = "You don't have access to manage this domain.";
		outputClientArea($templatefile);
	}
}