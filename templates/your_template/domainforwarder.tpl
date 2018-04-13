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
    <form class="form-horizontal" method="post" action="{$smarty.server.PHP_SELF}?domainid={$domainid}&amp;action=saverecords">
      <table class="table table-framed table-inf">
        <thead>
          <tr>
            <th>Type</th>
            <th>From <sup>1</sup></th>
            <th>Redirect Option</th>
            <th>Redirect to <sup>2</sup></th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          {foreach from=$dfrecords item=dfrecord}
          <tr>
            <td>
              <input type="hidden" name="recid[]" value="{$dfrecord.id}" />
              <select name="type[]" class="form-control">
                <option value="301" {if $dfrecord.type eq "301"} selected="selected"{/if}>Permanent (301)</option>
                <option value="302" {if $dfrecord.type eq "302"} selected="selected"{/if}>Temporary (302)</option>
              </select>
            </td>
            <td><input type="text" name="origin_domain[]" class="form-control" value="{$dfrecord.origin_domain}" disabled /></td>
            <td>
              <select name="option" class="form-control" disabled>
                <option value="1" {if $dfrecord.option eq "1"} selected="selected"{/if}>Only redirect with www</option>
                <option value="2" {if $dfrecord.option eq "2"} selected="selected"{/if}>Redirect with or without www</option>
                <option value="3" {if $dfrecord.option eq "3"} selected="selected"{/if}>Do no redirect www</option>
              </select>
            </td>
            <td><input type="text" name="destination_domain[]" class="form-control" value="{$dfrecord.destination_domain}" required /></td>
            <td>
              <a href="managedf.php?domainid={$domainid}&amp;action=deleterecord&amp;id={$dfrecord.id}" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this item?');"><span aria-hidden="true" class="glyphicon glyphicon-trash"></span></a>
            </td>
          </tr>
          {/foreach}
        </tbody>
      </table>
      <input type="submit" value="{$LANG.clientareasavechanges}" class="btn btn-primary" />
      <div class="space-20"></div>
      <small><sup>1</sup> Put @ as value to point your domain to root.</small><br>
      <small><sup>2</sup> Please use this format for optimal use: <i>[protocol]://[sld][tld]. e.g: http://example.com</i>.</small><br>
      <small>Please set your ns records to <b>ns1.domaincloud.id</b> &amp; <b>ns2.domaincloud.id</b>.</small>
    </form>
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
            <th>Type</th>
            <th>From<sup>1</sup></th>
            <th>Redirect Option</th>
            <th>Redirect<sup>2</sup> to &rarr;</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <select name="type" class="form-control">
                <option value="301">Permanent (301)</option>
                <option value="302">Temporary (302)</option>
              </select>
            </td>
            <td><input type="text" name="origin_domain" class="form-control" placeholder="@" required /></td>
            <td>
              <select name="option" class="form-control">
                <option value="1">Only redirect with www</option>
                <option value="2">Redirect with or without www</option>
                <option value="3">Do no redirect www</option>
              </select>
            </td>
            <td><input type="text" name="destination_domain" class="form-control" placeholder="http://" required /></td>
          </tr>
        </tbody>
      </table>
      <input type="submit" value="Add Record" class="btn btn-primary" />
      <div class="space-20"></div>
      <small><sup>1</sup> Put @ as value to point your domain to root.</small><br>
      <small><sup>2</sup> Please use this format for optimal use: <i>[protocol]://[sld][tld]. e.g: http://example.com</i>.</small><br>
      <small>Please set your ns records to <b>ns1.domaincloud.id</b> &amp; <b>ns2.domaincloud.id</b> <a href=/clientarea.php?action=domaindetails&amp;domainid={$domains.0.id}/#tabNameservers>here</a>.</small>
    </div>
  </div>
</form>
</div>