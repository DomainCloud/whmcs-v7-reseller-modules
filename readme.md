## WHMCS modules for DomainCloud Reseller

- - - -

About | Description
------------ | -------------
Stable tag | 0.9.0
Tested on | WHMCS 7.4.x
License | GPLv2 or later
License URI | http://www.gnu.org/licenses/gpl-2.0.html

### Installation

If you have any troubles during installation please contact us at registrar[at]isi.co.id.

### Setting up the reseller modules

1. Put reseller files and folders to your WHMCS system path (e.g: `/var/www/html`).
2. Activate domainku registrar module and document management addon on your WHMCS system.
3. Configure these modules using credentials provided by us.
4. Create an administrator user named `resellerapi` on your WHMCS system.
5. Create an email template named 'Domain Registration Approved'. Please read `domain registration approved_-_email template.txt` for further information.
6. Configure upload path and API endpoint on `dcconfig.php`.


### Setting up clientarea template for document management

Add this line in `clientareadomains.tpl`:
```php
<a href="/domaindocument.php" class="btn btn-success pull-right">.ID Document Registration</a>
```
above this line:
```php
<span class="help-block"><small>{$numitems} {$LANG.recordsfound}, {$LANG.page} {$pagenumber} {$LANG.pageof} {$totalpages}</small></span>
```

### Setting up clientarea template for DNS Management and Domain/URL Forwarding

Add these lines in `clientareadomaindetails.tpl`:
```php
{if $domain|substr:-2 eq 'id'}
    <li><a href="managedns.php?domainid={$domainid}">{$LANG.clientareadomainmanagedns}</a></li>
{else}
    {if $dnsmanagement}
        <li><a href="clientarea.php?action=domaindns&domainid={$domainid}">{$LANG.clientareadomainmanagedns}</a></li>
    {/if}
{/if}

{if $domain|substr:-2 eq 'id'}
	<li><a href="managedf.php?domainid={$domainid}">Manage URL Forwarding</a></li>
{/if}
```
to change this line:
```php
{if $dnsmanagement}[code]{/if}
```