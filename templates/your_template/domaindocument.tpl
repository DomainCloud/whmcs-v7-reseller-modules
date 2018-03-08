<link rel="stylesheet" type="text/css" href="templates/style-infinys.css" />

{literal}
<script type="text/javascript">
$(document).ready(function(){
	$('#checkall0').click(function (event) {
		$(event.target).parents('.table-inf').find('input').attr('checked',this.checked);
	});
});
</script>
{/literal}

{if ($domain or $search_domain)}
	<a href="/domaindocument.php" class="button-inf button-inf-home-trans"><i class="fa fa-angle-double-left"></i> Kembali</a>
{/if}
<div class="space-20"></div>

<form method="post" class="form-inf">
  <input type="hidden" name="token" value="8bf08a74c74e07fa14f33be06dc425d675be7531">
  <input type="text" class="input-inf" name="search_domain" value="Enter Domain to Find" onfocus="if(this.value=='Enter Domain to Find')this.value=''">
  <button type="submit" class="button-inf-default button-inf button-inf-cari">Cari</button>   
</form>
<p class="desc-inf">{$domains|@count} Rekaman Ditemukan, Halaman 1 dari 1</p>

<table class="table-inf">
	<thead>
		<tr>
			<th width="20">
				#
			</th>
			<th>Domain</th>
			<th>Dok. Identitas</th>
			<th>Dok. Legalitas</th>
			<th>Dok. Penunjang</th>
			<th>Tanggal Registrasi</th>
			<th>Status Domain</th>
			<!--th>Status</th-->
		</tr>
	</thead>
	<tbody>
		{$no=1}
		{foreach name=outer item=data from=$domains}
			<tr>
				<td>
					{$no++}
				</td>
				<td >
					{$data.domain}
				</td>
				<td>
					{if $data.id_doc_storage_name}
						<a href="domaindocument.php?userid={$data.userid}&amp;a=download_1&amp;domain={$data.domain}" class="button-inf button-inf-home">
							Download <i class="fa fa-download"></i>
						</a> &nbsp; 
					{/if}
					<a href="domaindocument.php?userid={$data.userid}&amp;a=upload_1&amp;domain={$data.domain}" class="button-inf">
						Upload <i class="fa fa-upload"></i>
					</a> 
				</td>
				<td>
					{if $data.le_doc_storage_name}
						<a href="domaindocument.php?userid={$data.userid}&amp;a=download_2&amp;domain={$data.domain}" class="button-inf">
							Download <i class="fa fa-download"></i>
						</a> &nbsp; 
					{/if}
					<a href="domaindocument.php?userid={$data.userid}&amp;a=upload_2&amp;domain={$data.domain}" class="button-inf">
						Upload <i class="fa fa-upload"></i>
					</a>
				</td>
				<td>
					{if $data.su_doc_storage_name}
						<a href="domaindocument.php?userid={$data.userid}&amp;a=download_3&amp;domain={$data.domain}" class="button-inf">
							Download <i class="fa fa-download"></i>
						</a> &nbsp; 
					{/if}
					<a href="domaindocument.php?userid={$data.userid}&amp;a=upload_3&amp;domain={$data.domain}" class="button-inf">
						Upload <i class="fa fa-upload"></i>
					</a>
				</td>
				<td>
					{$data.registrationdate}
				</td>
				<td>
						{if $data.domain_status == "3"}
							<span class="label-inf active-inf">Document approved</span>
						{elseif $data.domain_status == "2"}
							<span class="label-inf pending-inf">Document review</span>
						{elseif $data.domain_status == "1"}
							<span class="label-inf terminated-inf">Document rejected</span>
						{elseif $data.domain_status == "0"}
							<span class="label-inf suspended-inf">Domain suspended</span>
						{/if}
				</td>
				<!--td>
					{$data.status}
				</td-->
			</tr>
		{/foreach}
	</tbody>
</table>

{if (strpos($action, 'upload') !== false)}
	<div class="space-10"></div>
	<div class="space-10"></div>
	<div class="box-inf">
		<form method="POST" action="domaindocument.php?a={$action}&amp;domain={$domain}" enctype="multipart/form-data" class="form-horizontal">
			<center>
				<h3 class="title-inf">Upload Dokumen</h3>
			</center>
  			<div class="space-10"></div>
			<div class="form-group">
			    <label class="col-sm-2 control-label">Domain</label>
			    <div class="col-sm-10">
			      <label class="control-label">{$domain}</label>
			    </div>
			</div>
			<div class="form-group">
			    <label class="col-sm-2 control-label">Jenis Dokumen</label>
			    <div class="col-sm-4">
			      <select name="doc_type" class="form-control">
					{if $action == "upload_1"}
						<option value="KTP">KTP</option>
						<option value="SIM">SIM</option>
						<option value="PASSPORT">PASSPORT</option>
					{elseif $action == "upload_2"}
						<option value="NPWP">NPWP</option>
						<option value="SIUP">SIUP</option>
						<option value="BKPM">BKPM</option>
					{else}
						<option value="Surat Kuasa">Surat Kuasa</option>
						<option value="Lainnya">Lainnya</option>
					{/if}
				  </select>
			    </div>
			</div>
			<div class="form-group">
			    <label class="col-sm-2 control-label">Dokumen</label>
			    <div class="col-sm-10">
			      <input type="file" name="file" id="file" size="30">
			    </div>
			 </div>
			
  			<div class="form-group">
			    <div class="col-sm-offset-2 col-sm-10">
			      	<input class="button-inf button-inf-home" type="submit" name="submit" value="Upload" onclick="check_file()">
					<a href="domaindocument.php" class="button-inf button-inf-home-trans">
						Cancel
					</a>
			    </div>
			</div>
		</form>
	</div>
{/if}

<br />
<p class="desc-inf">Notes :<br>
Max. Filesize : 256 KB<br>
Allowed filetype : JPG, JPEG, PNG & PDF</p>
<hr />