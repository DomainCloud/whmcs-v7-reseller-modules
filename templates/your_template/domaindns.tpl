{include file="$template/pageheader.tpl" title=$LANG.domaindnsmanagement}
<p>
<form method="post" action="clientarea.php?action=domaindetails">
    <input type="hidden" name="id" value="{$domainid}" />
    <input type="submit" value="{$LANG.clientareabacklink}" class="btn btn-default" />
</form>
</p>
<blockquote>{$LANG.domainname}: {$domain}</blockquote>
{if $successful}
<div class="alert alert-success">
    <p>{$LANG.changessavedsuccessfully}</p>
</div>
{/if}
{if $error}
    <div class="alert alert-danger">
        {$error}
    </div>
{/if}
{if $external}
    <p>{$code}</p>
{else}
    <div>
        <ul class="nav nav-tabs">
            <li {if $do eq 'addrecord'}class="active"{/if}><a href="/managedns.php?domainid={$domainid}&amp;do=addrecord">Add New Record</a></li>
            <li {if $do eq 'saverecords'}class="active"{/if}><a href="/managedns.php?domainid={$domainid}&amp;do=saverecords">Change Existing Records</a></li>
        </ul>
    </div>
    <p>{$LANG.domaindnsmanagementdesc}</p>
    {if $do eq 'addrecord'}
        <form class="form-horizontal" method="post" action="{$smarty.server.PHP_SELF}?domainid={$domainid}&amp;action=addrecord">
            <input type="hidden" name="domainid" value="{$domainid}" />
            <div class="row">
                <div class="col-lg-12">
                    <table class="table table-condensed">
                        <thead>
                            <tr>
                                <th class="textcenter">{$LANG.domaindnshostname}</th>
                                <th class="textcenter">{$LANG.domaindnsrecordtype}</th>
                                <th class="textcenter">{$LANG.domaindnsaddress}</th>
                                <th class="textcenter">TTL <sup>1</sup></th>
                                <th class="textcenter">{$LANG.domaindnspriority} *</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="text" name="dnsrecordhost" class="form-control input-sm" required /></td>
                                <td>
                                    <select name="dnsrecordtype" class="form-control input-sm">
                                        <option value="A"{if $dnsrecord.type eq "A"} selected="selected"{/if}>A (Address)</option>
                                        <option value="CNAME"{if $dnsrecord.type eq "CNAME"} selected="selected"{/if}>CNAME (Alias)</option>
                                        <option value="MX"{if $dnsrecord.type eq "MX"} selected="selected"{/if}>MX (Mail)</option>
                                        <!--option value="NS"{if $dnsrecord.type eq "NS"} selected="selected"{/if}>NS (Name server)</option-->
                                        <option value="TXT"{if $dnsrecord.type eq "TXT"} selected="selected"{/if}>SPF (txt)</option>
                                    </select>
                                </td>
                                <td><input type="text" name="dnsrecordaddress" class="form-control input-sm" required /></td>
                                <td><input type="text" name="dnsrecordttl" class="form-control input-sm" required /></td>
                                <td><input type="text" name="dnsrecordpriority" class="form-control input-sm" /></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <p>* {$LANG.domaindnsmxonly}</p>
            <p><sup>1</sup> Put @ as hostname value to point your domain to root.</p>
            <p><sup>2</sup> TTL for A record must be greater than or equal to 3600</p>
            <p>Please set your ns records to <b>ns1.domaincloud.id</b> &amp; <b>ns2.domaincloud.id</b></p>
            <input type="submit" value="Add Record" class="btn btn-primary" />
        </form>
    {elseif $do eq 'saverecords'}
        {if $dnsrecords | @count gt 0}
        <form class="form-horizontal" method="post" action="{$smarty.server.PHP_SELF}?domainid={$domainid}&amp;action=saverecords">
            <table class="table table-condensed">
                <thead>
                    <tr>
                        <th class="textcenter">{$LANG.domaindnshostname}</th>
                        <th class="textcenter">{$LANG.domaindnsrecordtype}</th>
                        <th class="textcenter">{$LANG.domaindnsaddress}</th>
                        <th class="textcenter">TTL</th>
                        <th class="textcenter">{$LANG.domaindnspriority} *</th>
                        <th class="textcenter">Action</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$dnsrecords item=dnsrecord}
                    <tr>
                        <td>
                            <input type="hidden" name="dnsrecid[]" value="{$dnsrecord.recid}" />
                            <input type="text" name="dnsrecordhost[]" value="{$dnsrecord.hostname}" class="form-control input-sm" required />
                        </td>
                        <td>
                            <select name="dnsrecordtype[]" class="form-control input-sm">
                                <option value="A"{if $dnsrecord.type eq "A"} selected="selected"{/if}>A (Address)</option>
                                <option value="CNAME"{if $dnsrecord.type eq "CNAME"} selected="selected"{/if}>CNAME (Alias)</option>
                                <option value="MX"{if $dnsrecord.type eq "MX"} selected="selected"{/if}>MX (Mail)</option>
                                <!--option value="NS"{if $dnsrecord.type eq "NS"} selected="selected"{/if}>NS (Name server)</option-->
                                <option value="TXT"{if $dnsrecord.type eq "TXT"} selected="selected"{/if}>SPF (txt)</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="dnsrecordaddress[]" value="{$dnsrecord.address}" class="form-control input-sm" required />
                        </td>
                        <td>
                            <input type="text" name="dnsrecordttl[]" value="{$dnsrecord.ttl}" class="form-control input-sm" required />
                        </td>
                        <td>
                            {if $dnsrecord.type eq "MX"}
                                <input type="text" name="dnsrecordpriority[]" value="{$dnsrecord.priority}" class="form-control input-sm" />
                            {else}
                                <input type="hidden" value="N/A" />{$LANG.domainregnotavailable}
                            {/if}
                        </td>
                        <td>
                            <a href="managedns.php?domainid={$domainid}&amp;action=deleterecord&amp;id={$dnsrecord.recid}" class="btn btn-warning btn-sm">Delete</a>
                        </td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
            <p>* {$LANG.domaindnsmxonly}</p>
            <p><sup>1</sup> Put @ as hostname value to point your domain to root.</p>
            <p><sup>2</sup> TTL for A record must be greater than or equal to 3600</p>
            <p>Please set your ns records to <b>ns1.domaincloud.id</b> &amp; <b>ns2.domaincloud.id</b></p>
            <input type="submit" value="{$LANG.clientareasavechanges}" class="btn btn-primary" />
        </form>
        {else}
        &nbsp;<p>No records found.</p>
        {/if}
    {/if}
{/if}