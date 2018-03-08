{include file="$template/pageheader.tpl" title="URL Redirect"}
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
            <li {if $do eq 'addrecord'}class="active"{/if}><a href="/managedf.php?domainid={$domainid}&amp;do=addrecord">Add New Record</a></li>
            <li {if $do eq 'saverecords'}class="active"{/if}><a href="/managedf.php?domainid={$domainid}&amp;do=saverecords">Change Existing Records</a></li>
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
                                <th class="textcenter">Type</th>
                                <th class="textcenter">From<sup>1</sup></th>
                                <th class="textcenter">Redirect Option</th>
                                <th class="textcenter">Redirect<sup>2</sup> to &rarr;</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select name="type" class="form-control input-sm">
                                        <option value="301">Permanent (301)</option>
                                        <option value="302">Temporary (302)</option>
                                    </select>
                                </td>
                                <td><input type="text" name="origin_domain" class="form-control input-sm" placeholder="@" required /></td>
                                <td>
                                    <select name="option" class="form-control input-sm">
                                        <option value="1">Only redirect with www</option>
                                        <option value="2">Redirect with or without www</option>
                                        <option value="3">Do no redirect www</option>
                                    </select>
                                </td>
                                <td><input type="text" name="destination_domain" class="form-control input-sm" placeholder="http://" required /></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <p>* {$LANG.domaindnsmxonly}</p>
            <p><sup>1</sup> Put @ as value to point your domain to root.</p>
            <p><sup>2</sup> Please use this format for optimal use: <i>[protocol]://[sld][tld]. e.g: http://example.com</i>.</p>
            <p><sup>*</sup> Please set your ns records to <b>ns1.domaincloud.id</b> and <b>ns2.domaincloud.id</b>.</p>
            <input type="submit" value="Add Record" class="btn btn-primary" />
        </form>
    {elseif $do eq 'saverecords'}
        {if $dfrecords | @count gt 0}
        <form class="form-horizontal" method="post" action="{$smarty.server.PHP_SELF}?domainid={$domainid}&amp;action=saverecords">
            <table class="table table-condensed">
                <thead>
                    <tr>
                        <th class="textcenter">Type</th>
                        <th class="textcenter">From</th>
                        <th class="textcenter">Redirect Option</th>
                        <th class="textcenter">Redirect<sup>1</sup> to &rarr;</th>
                        <th class="textcenter">Action</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$dfrecords item=dfrecord}
                    <tr>
                        <td>
                            <input type="hidden" name="recid[]" value="{$dfrecord.id}" />
                            <select name="type[]" class="form-control input-sm">
                                <option value="301" {if $dfrecord.type eq "301"} selected="selected"{/if}>Permanent (301)</option>
                                <option value="302" {if $dfrecord.type eq "302"} selected="selected"{/if}>Temporary (302)</option>
                            </select>
                        </td>
                        <td><input type="text" name="origin_domain[]" class="form-control input-sm" value="{$dfrecord.origin_domain}" disabled /></td>
                        <td>
                            <select name="option" class="form-control input-sm" disabled>
                                <option value="1" {if $dfrecord.option eq "1"} selected="selected"{/if}>Only redirect with www</option>
                                <option value="2" {if $dfrecord.option eq "2"} selected="selected"{/if}>Redirect with or without www</option>
                                <option value="3" {if $dfrecord.option eq "3"} selected="selected"{/if}>Do no redirect www</option>
                            </select>
                        </td>
                        <td><input type="text" name="destination_domain[]" class="form-control input-sm" value="{$dfrecord.destination_domain}" required /></td>
                        <td>
                            <a href="managedf.php?domainid={$domainid}&amp;action=deleterecord&amp;id={$dfrecord.id}" class="btn btn-warning btn-sm">Delete</a>
                        </td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
            <p><sup>*</sup> Please set your ns records to <b>ns1.domaincloud.id</b> and <b>ns2.domaincloud.id</b>.</p>
            <input type="submit" value="{$LANG.clientareasavechanges}" class="btn btn-primary" />
        </form>
        {else}
        &nbsp;
        No records found.
        {/if}
    {/if}
{/if}