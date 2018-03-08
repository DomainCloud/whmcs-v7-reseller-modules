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
 * This is document management module for DomainCloud Reseller. 
 *
 * @package    DomainCloud Document Management
 * @author     Infinys System Indonesia
 * @copyright  Copyright (c) Infinys System Indonesia. 2017
 * @license    http://www.isi.co.id/
 * @version    $Id$
 * @link       http://www.isi.co.id/
 */

date_default_timezone_set('Asia/Jakarta');
require_once ROOTDIR . "/includes/registrarfunctions.php";
require_once ROOTDIR . "/modules/addons/tableinfinys.class.php";
require_once "functions.php";
require_once ROOTDIR . "/dcconfig.php";

use Illuminate\Database\Capsule\Manager as Capsule;

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

function domaincloud_config() {
    $configarray = array(
        "name" => "DomainCloud Docma",
        "description" => "Document Management",
        "version" => "0.9.0",
        "author" => "Infinys System Indonesia",
        "language" => "english",
        "fields" => array());
    return $configarray;
}

function domaincloud_activate() {
    try {
        Capsule::schema()->create(
            'mod_domaincloudregistrar',
            function ($table) {
                $table->increments('id');
                $table->integer('userid');
                $table->integer('domainid');
                $table->string('domain');
                $table->string('id_doc_storage_name');
                $table->string('id_doc_type')->nullable();
                $table->string('le_doc_storage_name');
                $table->string('le_doc_type')->nullable();
                $table->string('su_doc_storage_name');
                $table->string('su_doc_type')->nullable();
                $table->date('domain_registration_date')->nullable();
                $table->date('domain_approval_date')->nullable();
                $table->string('reason')->nullable();
                $table->smallInteger('domain_status');
            }
        );
        return array('status'=>'success','description'=>'DomainCloud module has been added successfully');
    }
    catch (Exception $exception) {
        return array('status'=>'error','description'=>'Failed to activate DomainCloud module');
    }  
}

function domaincloud_deactivate() {
    try {
        Capsule::schema()->drop('mod_domaincloudregistrar');
        return array('status'=>'success','description'=>'DomainCloud module has been removed successfully');
    }
    catch(Exception $exception) {
        return array('status'=>'error','description'=>'Failed to remove DomainCloud module');
    }    
}

function domaincloud_output($vars) {
    require ROOTDIR . "/dcconfig.php";
    
    $uid = (isset($_REQUEST['userid']) ? $_REQUEST['userid'] : "");
    $action = (isset($_REQUEST['a']) ? $_REQUEST['a'] : "");
    $domainid = (isset($_REQUEST['filter_id']) ? $_REQUEST['filter_id'] : "");
    $filter = (isset($_REQUEST['filter']) ? $_REQUEST['filter'] : "");
    $document_download = (isset($_REQUEST['dl']) ? $_REQUEST['dl'] : "");
    $document_name = (isset($_REQUEST['doc_name']) ? $_REQUEST['doc_name'] : "");
    $domainname = (isset($_POST["domainname"]) ? $_POST["domainname"] : "");
    $domain_status = (isset($_POST["domain_status"]) ? $_POST["domain_status"] : "");
    $transfersecret = (isset($_POST["transfersecret"]) ? $_POST["transfersecret"] : "");
    $current_date = date('Y-m-d');
    $where = "tbldomains.domain like '%%'";

    if (isset($_REQUEST['filter'])) {
        $where = " tbldomains.domain like '%" . $_REQUEST['filter'] . "%' ";  
    }
    else if (isset($_REQUEST['filter_id'])) {
        $where = " tbldomains.id = '" . $_REQUEST['filter_id'] . "' ";
    }

    $result = Capsule::table('tbldomains')
        ->select(Capsule::raw('count(*) as domains_count'))
        ->whereRaw($where)
        ->get();
    
    $num_rows = $result[0]->domains_count;
    
    $pages = new INFINYS_table($num_rows, 9, array(15,25,50,100,250,'All'));
    $section = new WHMCS_DomainCloudFunctions($domainid);
    $module = (isset($_GET["module"])) ? $_GET["module"] : '' ;

    ob_start();
    if ($domainid || $filter) {
        echo "
        <a href=\"addonmodules.php?module=".$module."\" style=\"text-decoration: none\">
            <span style=\"background-color: #1A4D80; padding: 5px 10px; color: #fff;\">
                <i class=\"fa fa-angle-double-left\"></i> Back
            </span>
        </a><br /><br />";
    }

    echo $section->displayFormFilter();
    echo "<div style=\"float:right\">".$pages->displayJumpMenu().$pages->displayItemsPerPage()."</div>
        <div style=\"float:left\">".$pages->displayTableFooter()."</div>
        <div style=\"clear:both\"></div>
        <div style=\"margin-top:10px\"></div>";

    $result = Capsule::table('tbldomains')
            ->select(Capsule::raw('tbldomains.*, tblinvoices.subtotal, tblinvoices.tax, tblinvoices.status, tblorders.nameservers, tblorders.transfersecret,
                mod_domaincloudregistrar.domain AS coza_domain, mod_domaincloudregistrar.id_doc_storage_name, mod_domaincloudregistrar.id_doc_type, mod_domaincloudregistrar.le_doc_storage_name, 
                mod_domaincloudregistrar.le_doc_type, mod_domaincloudregistrar.su_doc_storage_name, mod_domaincloudregistrar.su_doc_type, mod_domaincloudregistrar.domain_approval_date, mod_domaincloudregistrar.domain_status'))
            ->leftJoin('mod_domaincloudregistrar', 'tbldomains.domain', '=', 'mod_domaincloudregistrar.domain')
            ->leftJoin('tblorders', 'tbldomains.orderid', '=', 'tblorders.id')
            ->leftJoin('tblinvoices', 'tblorders.invoiceid', '=', 'tblinvoices.id')
            ->orderBy('id', 'desc')
            ->skip($pages->limit_start)->take($pages->limit_end)
            ->whereRaw($where)
            ->get();

    $pages->setTableHeader(array("Domain", "Identity Document", "Legality Document", "Other Document", "Registration Date", "Special Action", "Domain Status", "Payment"));

    foreach($result as $dom) {
        $linkopen = "<a class=\"link\" href=\"clientsdomains.php?userid=" . $dom->userid . "&id=" . $dom->id . "\">";
        $linkclose = "</a>";
        
        $pages->addRow(array(
            $linkopen . $dom->domain . $linkclose,
            ($dom->id_doc_storage_name ? "<a class=\"button btn-xs btn-success\" href=\"addonmodules.php?module=".$module."&amp;userid=" . $dom->userid . "&amp;a=download_1&amp;filter_id=" . $dom->id . "&amp;doc_name=" . $dom->id_doc_storage_name . "\" style=\"text-decoration: none;\"><span class=\"label check\">Manage <i class=\"fa fa-comment-o\"></i></span></a> &#124; " : "") . "<a href=\"addonmodules.php?module=".$module."&amp;userid=" . $dom->userid . "&amp;a=upload_1&amp;filter_id=" . $dom->id . "\" style=\"text-decoration: none;\" class=\"button btn-xs btn-primary\"><span class=\"label upload\">Upload <i class=\"fa fa-upload\"></i></span></a>", 
            ($dom->le_doc_storage_name ? "<a class=\"button btn-xs btn-success\" href=\"addonmodules.php?module=".$module."&amp;userid=" . $dom->userid . "&amp;a=download_2&amp;filter_id=" . $dom->id . "&amp;doc_name=" . $dom->le_doc_storage_name . "\" style=\"text-decoration: none;\"><span class=\"label check\">Manage <i class=\"fa fa-comment-o\"></i></span></a> &#124; " : "") . "<a class=\"button btn-xs btn-primary\" href=\"addonmodules.php?module=".$module."&amp;userid=" . $dom->userid . "&amp;a=upload_2&amp;filter_id=" . $dom->id . "\" style=\"text-decoration: none;\"><span class=\"label upload\">Upload <i class=\"fa fa-upload\"></i></span></a>", 
            ($dom->su_doc_storage_name ? "<a class=\"button btn-xs btn-success\" href=\"addonmodules.php?module=".$module."&amp;userid=" . $dom->userid . "&amp;a=download_3&amp;filter_id=" . $dom->id . "&amp;doc_name=" . $dom->su_doc_storage_name . "\" style=\"text-decoration: none;\"><span class=\"label check\">Manage <i class=\"fa fa-comment-o\"></i></span></a> &#124; " : "") . "<a class=\"button btn-xs btn-primary\" href=\"addonmodules.php?module=".$module."&amp;userid=" . $dom->userid . "&amp;a=upload_3&amp;filter_id=" . $dom->id . "\" style=\"text-decoration: none;\"><span class=\"label upload\">Upload <i class=\"fa fa-upload\"></i></span></a>", 
            $dom->registrationdate,
            "<a class=\"button btn-xs btn-primary\" href=\"addonmodules.php?module=".$module."&amp;userid=" . $dom->userid . "&amp;a=transfer&amp;filter_id=" . $dom->id . "\" style=\"text-decoration: none;\"><span class=\"label check\">Renew via Transfer</span></a>",
            $dom->domain_status == 3 ? "<span class=\"label active\">Approved</span>" : ($dom->domain_status == 2 ? "<span class=\"label pending\">Review</span>" : ($dom->domain_status == 1 ? "<span class=\"label closed\">Rejected</span>" : "")),
            $dom->status == 'Paid' ? "<span class=\"label active\">".$dom->status."</span>" : "<span class=\"label cancelled\">".$dom->status."</span>"
        ));
    }

    echo $pages->displayTable();
    echo "<div style=\"text-align:center\">".$pages->displayPages()."</div>";
    echo"
    <br />
    <script type=\"text/javascript\">
        $( \"a[href^='#tab']\" ).click( function() {
            var tabID = $(this).attr('href').substr(4);
            var tabToHide = $(\"#tab\" + tabID);
            if(tabToHide.hasClass('active')) {
                tabToHide.removeClass('active');
            }  else {
                tabToHide.addClass('active')
            }
        });
    </script>";
    
    $output = ob_get_contents();
    ob_end_clean();
    echo $output;

    if ($uid && $action && $domainid) {
        
        $result = Capsule::table('tbldomains')
            ->select(Capsule::raw('tbldomains.*, mod_domaincloudregistrar.domain AS coza_domain, mod_domaincloudregistrar.id_doc_storage_name, mod_domaincloudregistrar.le_doc_storage_name, mod_domaincloudregistrar.su_doc_storage_name, mod_domaincloudregistrar.domain_approval_date, mod_domaincloudregistrar.domain_status'))
            ->leftJoin('mod_domaincloudregistrar', 'tbldomains.id', '=', 'mod_domaincloudregistrar.domainid')
            ->whereRaw('tbldomains.id = ' . $domainid)
            ->get();
        $domain = $result[0]->domain;

        if (isset($_FILES["file"]) && $_FILES["file"]["error"] > 0) {
            echo "Error: " . $_FILES["file"]["error"] . "<br>";
        } else {
            
            if (isset($_FILES["file"]) && $_FILES["file"]["name"] != null) {
                $ext = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
                $filename = md5($uid . $domain . $action) . "." . $ext;
                move_uploaded_file($_FILES["file"]["tmp_name"], $upload_path . $filename);
                
                $domainparts = explode(".", $domain, 2);
                
                $config = getregistrarconfigoptions('domainku');
                
                $data = array(
                    "action"            => 'UploadFile',
                    "token"             => $config['Token'],
                    "authemail"         => $config['AuthEmail'],
                    "sld"               => $domainparts[0],
                    "tld"               => $domainparts[1],
                    "file"              => new \CURLFile($upload_path . $filename),
                    "user_action"       => $action,
                    "doc_type"          => $_POST['doc_type']
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $api_endpoint);
                curl_setopt($ch, CURLOPT_TIMEOUT, 0);
                curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
                curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);

                $output = curl_exec($ch);

                if ($output == false) {
                    $res = array("error"=>curl_error($ch));
                } else {
                    $res = json_decode($output, true);
                }
                curl_close($ch);

                if (empty($res['error'])) {
                    $values = array("userid"=>$uid,"domain"=>$domain);
                    if ($action == "upload_1") { 
                        $values["id_doc_storage_name"] = $filename;
                        $values["id_doc_type"] = $_POST["doc_type"];
                    }
                    if ($action == "upload_2") { 
                        $values["le_doc_storage_name"] = $filename;
                        $values["le_doc_type"] = $_POST["doc_type"];
                    }
                    if ($action == "upload_3") { 
                        $values["su_doc_storage_name"] = $filename;
                        $values["su_doc_type"] = $_POST["doc_type"];
                    }

                    if ($result[0]->coza_domain == $domain && $filename) {
                        $query = Capsule::table('mod_domaincloudregistrar')
                            ->where("domainid", $domainid)
                            ->update($values);
                    }
                    else {
                        $values['domainid'] = $domainid;
                        $values['domain_registration_date'] = $result[0]->registrationdate;
                        $values['domain_status'] = "2";
                        Capsule::table('mod_domaincloudregistrar')->insert($values);
                    }

                    $query = Capsule::table('tbldomains')
                            ->where("id", $domainid)
                            ->update(["registrar"=>"domainku"]);                            
                    redir("module=".$module);
                }
            }
        }


        if (strpos($action, 'upload') !== false) {
            
            echo $section->outputUploadSection($domain, $action);
            //var_dump($action);die;
        }
        elseif (strpos($action, 'dl') !== false) {
            $file = $upload_path . $document_name;

            if (file_exists($file)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename='.basename($file));
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file));
                ob_clean();
                flush();
                readfile($file);
                exit;
            }
        }
        elseif (strpos($action, 'download') !== false) {
            echo $section->outputDownloadSection($domain, $domainid, $uid, $document_name, $action, $domain_status);

            $file = $upload_path . $document_name;
            $rows = $result[0];
            if ($rows->coza_domain == $domain && $domain_status != "") {
                $mvalues = array("domain_status"=>$domain_status);
                
                $params = array();
                $params['userid'] = $uid;
                $params['domainid'] = $domainid;
                $domainparts = explode(".", $domain, 2);
                $params['sld'] = $domainparts[0];
                $params['tld'] = $domainparts[1];
                $params['regperiod'] = $rows->registrationperiod;
                $params['registrar'] = $rows->registrar;
                $params['regtype'] = $rows->type;

                if ($domain_status == 3) {
                    if ($rows->type == 'Register') {
                        $result_epp = RegRegisterDomain($params);
                    }
                    elseif ($rows->type == 'Transfer') {
                        $params['transfersecret'] = $rows->transfersecret;
                        $result_epp = RegTransferDomain($params);
                    }

                    if (!$result_epp['error']) {
                        $mvalues['domain_approval_date'] = $current_date;
                        echo "
                        <div class=\"successbox\">
                            <strong><span class=\"title\">Registrar Status</span></strong><br />" . $result_epp['status'] . "
                        </div>
                        ";

                    } else {
                        $mvalues['domain_status'] = $rows->domain_status;
                        echo "
                        <div class=\"errorbox\">
                            <strong><span class=\"title\">Registrar Error</span></strong><br>".$result_epp['error']."
                        </div>
                        ";
                    }
                }
                $query = Capsule::table('mod_domaincloudregistrar')
                    ->where('domainid', $domainid)
                    ->update($mvalues);
            }
        }
        elseif (strpos($action, 'transfer') !== false) {
            echo "
            <form method=\"post\" class=\"form-inline\">
                
                 <label for=\"transfersecret\">EPP Code</label>
                <input type=\"textbox\" name=\"transfersecret\" id=\"transfersecret\" value=\"\" class=\"form-control input-250\">
                <input type=\"submit\" class=\"btn btn-primary\" value=\"Submit Domain Renewal via Transfer\">
            </form>";

            if (!empty($transfersecret)) {
                $params = array();
                $params['userid'] = $uid;
                $params['domainid'] = $rows->id;
                $domainparts = explode(".", $domain, 2);
                $params['sld'] = $domainparts[0];
                $params['tld'] = $domainparts[1];
                $params['regperiod'] = $rows->registrationperiod;
                $params['registrar'] = $rows->registrar;
                $params['regtype'] = 'transfer';
                $params['transfersecret'] = $transfersecret;
                $result_epp = RegTransferDomain($params);

                if (!$result_epp['error']) {
                    # Set domain approval to 'Approved'.
                    $query = Capsule::table('mod_domaincloudregistrar')
                            ->where("domain_approval_date", $current_date)
                            ->where("domain_status", 3)
                            ->update(["domainid"=>$rows->id]);
                    
                    # Check domain status, if 'Pending Transfer' set it to 'Active'.
                    $query = Capsule::table('tbldomains')
                            ->where("domainid", $rows->id)
                            ->where("status", "Pending Transfer")
                            ->update(["status"=>"Active"]);

                    echo "
                    <div class=\"successbox\">
                        <strong><span class=\"title\">Registrar Status</span></strong><br />Command completed successfully.
                    </div>
                    ";
                }
                else {
                    echo "
                    <div class=\"errorbox\">
                        <strong><span class=\"title\">Registrar Error</span></strong><br>".$result_epp['error']."
                    </div>
                    ";
                }
            }
        }
    }
}