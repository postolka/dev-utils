<?php $pgeName = 'Checksum generator';
$src = isset($_POST['src']) ? $_POST['src'] : NULL;
$gen = !empty($_POST['gen']);
?>
<form action="#" method="post">
	<div class="input-group">
		<input id="input" class="form-control" type="text" name="src" value="<?= $src ?>"/>
		<span class="input-group-btn">
			<button class="btn btn-primary" type="submit" name="gen" value="gen">Checksums</button>
		</span>
	</div>
</form><hr/>
<?php
if ($gen){?>
<table>
	<tr><th>MD5</th><td><code class="nosyntax"><?= md5($src) ?></code></td></tr>
	<tr><th>SHA1</th><td><code class="nosyntax"><?= sha1($src) ?></code></td></tr>
</table>
<?php }