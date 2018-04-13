<link rel="stylesheet" type="text/css" href="templates/style-infinys.css" />

<p>
<form method="post" action="clientarea.php?action=domaindetails">
    <input type="hidden" name="id" value="{$domainid}" />
    <input type="submit" value="{$LANG.clientareabacklink}" class="btn btn-default" />
</form>
</p>

<div class="space-20"></div>
<h4>{$LANG.domainname}: {$domain}</h4>
<p>{$LANG.domaindnsmanagementdesc}</p>
<hr>
<div class="space-20"></div>


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
    {if $dnsrecords|@count gt 0}*}
        <form class="form-horizontal" method="post" action="{$smarty.server.PHP_SELF}?domainid={$domainid}&amp;action=saverecords">
          <table class="table table-framed table-inf">
            <thead>
              <tr>
                <th>{$LANG.domaindnshostname}</th>
                <th>{$LANG.domaindnsrecordtype}</th>
                <th>{$LANG.domaindnsaddress}</th>
                <th>TTL</th>
                <th>{$LANG.domaindnspriority} *</th>
                <th>Action</th>
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
                  <select name="dnsrecordtype[]" class="form-control">
                    <option value="A"{if $dnsrecord.type eq "A"} selected="selected"{/if}>A (Address)</option>
                    <option value="CNAME"{if $dnsrecord.type eq "CNAME"} selected="selected"{/if}>CNAME (Alias)</option>
                    <option value="MX"{if $dnsrecord.type eq "MX"} selected="selected"{/if}>MX (Mail)</option>
                    <option value="TXT"{if $dnsrecord.type eq "TXT"} selected="selected"{/if}>SPF (txt)</option>
                  </select>
                </td>
                <td>
                  <input type="text" name="dnsrecordaddress[]" value="{$dnsrecord.address}" class="form-control" required />
                </td>
                <td>
                  <input type="text" name="dnsrecordttl[]" value="{$dnsrecord.ttl}" class="form-control" required />
                </td>
                <td>
                  {if $dnsrecord.type eq "MX"}
                    <input type="text" name="dnsrecordpriority[]" value="{$dnsrecord.priority}" class="form-control" />
                  {else}
                    <input type="hidden" value="N/A" />{$LANG.domainregnotavailable}
                  {/if}
                </td>
                <td>
                  <a href="managedns.php?domainid={$domainid}&amp;action=deleterecord&amp;id={$dnsrecord.recid}" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this item?');"><span aria-hidden="true" class="glyphicon glyphicon-trash"></span></a>
                </td>
              </tr>
              {/foreach}
            </tbody>
          </table>
          <input type="submit" value="{$LANG.clientareasavechanges}" class="btn btn-primary" />
          <div class="space-20"></div>
        <small>* Priority Record for MX Only</small><br>
        <small><sup>1</sup> Put @ as hostname value to point your domain to root.</small><br>
        <small><sup>2</sup> TTL for A record must be greater than or equal to 3600</small><br>
        <small>Please set your ns records to <b>ns1.domaincloud.id</b> &amp; <b>ns2.domaincloud.id</b>.</small>
        </form>
    {else}
        No records found.
    {/if}
{/if}
<br>
<br>

<div class="space-20"></div>

<h4>Add Record</h4>

<form class="form-horizontal" method="post" action="{$smarty.server.PHP_SELF}?domainid={$domainid}&amp;action=addrecord">
  <input type="hidden" name="domainid" value="{$domainid}" />
  <div class="row">
    <div class="col-lg-12">
      <table class="table table-striped table-framed table-inf">
        <thead>
          <tr>
            <th>{$LANG.domaindnshostname} <sup>1</sup></th>
            <th>{$LANG.domaindnsrecordtype}</th>
            <th>{$LANG.domaindnsaddress}</th>
            <th>TTL <sup>2</sup></th>
            <th>{$LANG.domaindnspriority} *</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><input type="text" name="dnsrecordhost" class="form-control" required /></td>
            <td>
              <select name="dnsrecordtype" class="form-control">
                <option value="A">A (Address)</option>
                <option value="MX">MX (Mail)</option>
                <option value="CNAME">CNAME (Alias)</option>
                <option value="TXT">SPF (txt)</option>
              </select>
            </td>
            <td><input type="text" name="dnsrecordaddress" class="form-control" required /></td>
            <td><input type="text" name="dnsrecordttl" class="form-control" required /></td>
            <td><input type="text" name="dnsrecordpriority" class="form-control" /></td>
          </tr>
        </tbody>
      </table>
      <input type="submit" value="Add Record" class="btn btn-primary" />
      <div class="space-20"></div>
      <small>* Priority Record for MX Only</small><br>
      <small><sup>1</sup> Put @ as hostname value to point your domain to root.</small><br>
      <small><sup>2</sup> TTL for A record must be greater than or equal to 3600</small><br>
      <small>Please set your ns records to <b>ns1.domaincloud.id</b> &amp; <b>ns2.domaincloud.id</b>.</small>

    </div>
  </div>
</form>
</div>