<?php
/**
 * Copyright (c) 2018, Infinys System Indonesia
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
use Illuminate\Database\Capsule\Manager as Capsule;

class WHMCS_DomainCloudFunctions {
    private $rows;

    public function __construct($domainid = "") {
        if (!empty($domainid)) {
            $query = Capsule::table('tbldomains')
                    ->select(Capsule::raw('tbldomains.*, mod_domaincloudregistrar.domain AS coza_domain, mod_domaincloudregistrar.id_doc_storage_name, mod_domaincloudregistrar.le_doc_storage_name, mod_domaincloudregistrar.su_doc_storage_name, mod_domaincloudregistrar.domain_approval_date, mod_domaincloudregistrar.domain_status'))
                    ->whereRaw("tbldomains.id = " . $domainid)
                    ->leftJoin('mod_domaincloudregistrar', 'tbldomains.id', '=', 'mod_domaincloudregistrar.domainid')->pluck($this->rows);          
        }
    }

    public function displayFormFilter() {
        $value = (isset($_REQUEST['filter'])) ? $_REQUEST['filter'] : '' ;

        return "<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
                    <tbody>
                        <tr>
                            <td width=\"15%\" class=\"fieldlabel\">Domain Name</td>
                            <td class=\"fieldarea\"><input type=\"text\" name=\"domainname\" id=\"filter\" class=\"form-control input-250\" value=\"".$value."\"></td>
                        </tr>
                    </tbody>
                </table>
                <p align=\"center\"><input type=\"button\" id=\"search-clients\" value=\"Search\" class=\"button btn btn-default\" onclick=\"window.location='$_SERVER[PHP_SELF]?module=domaincloud&amp;filter=' + document.getElementById('filter').value + '&amp;page=1&amp;per_page=$this->items_per_page$this->querystring';return false\"></p>

                <script type=\"text/javascript\">
                    document.getElementById(\"filter\").addEventListener(\"keyup\", function(event) {
                        event.preventDefault();
                        if (event.keyCode == 13) {
                            document.getElementById(\"search-clients\").click();
                        }
                    });
                </script>";
    }

    public function outputUploadSection($domain, $action) {
        $cmd = substr($action, -1);
        $module = (isset($_GET["module"])) ? $_GET["module"] : '' ;

        $content = "
        <div class=\"row\">
            <div class=\"col-md-12\">
                <div style=\"border: 1px solid #ccc; background-color: #f0f0f0; border-radius: 5px; padding: 15px;\">
                    <form method=\"post\" enctype=\"multipart/form-data\">
                        <table width=\"100%\">
                            <tbody>
                                <tr>
                                    <td width=\"10%\"><b>Domain</b>:</td>
                                    <td>" . $domain . "</td>
                                </tr>
                                <tr>
                                    <td width=\"10%\"><b>Jenis Dokumen</b>:</td>
                                    <td>";

        switch ($cmd) {
            case '1':
                $content .= "
                <select name=\"doc_type\">
                    <option value=\"KTP\">KTP</option>
                    <option value=\"SIM\">SIM</option>
                    <option value=\"PASSPORT\">PASSPORT</option>
                </select>";
                break;
            case '2':
                $content .= "
                <select name=\"doc_type\">
                    <option value=\"NPWP\">NPWP</option>
                    <option value=\"SIUP\">SIUP</option>
                    <option value=\"BKPM\">BKPM</option>
                </select>";
                break;
            case '3':
                $content .= "
                <select name=\"doc_type\">
                    <option value=\"Surat Pernyataan\">Surat Pernyataan</option>
                    <option value=\"Lainnya\">Lainnya</option>
                </select>";
                break;
        }

        $content .= "
                                    </td>
                                </tr>
                                <tr>
                                    <td width=\"10%\"><b>Dokumen</b>:</td>
                                    <td><input type=\"file\" name=\"file\" id=\"file\" size=\"30\"><br /></td>
                                </tr>
                                <tr>
                                    <td colspan=\"2\">
                                        <input class=\"btn btn-success\" type=\"submit\" name=\"ul\" value=\"Upload\" onclick=\"check_file()\">
                                        <a href=\"addonmodules.php?module=". $module ."\">
                                            <input class=\"btn btn-default\" type=\"button\" name=\"cancel\" value=\"Cancel\">
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
        </div>";

        # Add literal javascript functions
        $content .= "
        <script type=\"text/javascript\"> 
        function check_file() {
            var file = document.getElementById(\"file\").files[0];
            var file_name = file.name;
            var file_ext = file_name.split('.')[file_name.split('.').length - 1]);
            var fe = file_ext.toLowerCase();

            if (fe != \"pdf\" || fe != \"jpeg\" || fe != \"jpg\" || fe != \"png\") {
                alert(\"File type is not allowed!\");
                event.returnValue = false;
            }
            else {
                event.returnValue = true;
            }
        }
        </script>";
        return $content;
    }

    public function outputDownloadSection($domain, $domainid, $userid, $document_name, $action, $domain_status) {
        $module = (isset($_GET["module"])) ? $_GET["module"] : '' ;

        switch (substr($action, -1)) {
            case 1:
                $doctype = "Identity Document";
                break;
            case 2:
                $doctype = "Legality Document";
                break;
            case 3:
                $doctype = "Other Document";
                break;
            default:
                $doctype = "Identity Document";
                break;
        }
        $content = "

        <div class=\"row\">
            <div class=\"col-md-12\">
                <div style=\"border: 1px solid #ccc; background-color: #f0f0f0; border-radius: 5px; padding: 15px;\">
                    <form method=\"post\">
                        <table width=\"100%\">
                            <tbody>
                                <tr>
                                    <td width=\"5%\"><b>Domain</b>:</td>
                                    <td>" . $domain . "</td>
                                </tr>
                                <tr>
                                    <td width=\"5%\"><b>Document</b>:</td>
                                    <td>" . $doctype . " &rarr; <a href=\"addonmodules.php?module=". $module ."&amp;userid=". $userid ."&amp;a=dl&amp;filter_id=" . $domainid . "&amp;doc_name=" . $document_name . "\">Download</a></td>
                                </tr>
                                <tr>
                                    <td width=\"5%\"><b>Domain Status</b>:</td>
                                    <td>
                                        <select name=\"domain_status\">
                                            <option value=\"2\"". ($this->rows['domain_status'] == 2 ? "selected" : "") .">Review</option>
                                            <option value=\"3\"". ($this->rows['domain_status'] == 3 ? "selected" : "") .">Approve</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td>
                                        <input class=\"btn btn-success\" type=\"submit\" name=\"save_domain_status\" value=\"Update Status\">
                                    </td>
                                    <td>
                                        <a href=\"addonmodules.php?module=". $module ."\">
                                            <input class=\"btn btn-default\" type=\"button\" value=\"Cancel\" />
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
        </div>";
        return $content;
    }
}