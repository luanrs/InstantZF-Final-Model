<hr />
<div>
	<div><?php print_r($msg)?></div>
	<form method="post" action="file-upload" enctype="multipart/form-data">
		File <br/>
		<input type="file" name="doc" />
		<br /><br />
		<input type="submit" value="Upload" />
	</form>
</div>