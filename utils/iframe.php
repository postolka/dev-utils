<?php
$pgeName = 'Page viewer';
$url = empty($_GET['url']) ? NULL : $_GET['url'];
?>
<form action="#" method="get">
<div class="input-group">
	<input class="form-control" type="text" value="<?= $url ?>" name="url"/>
	<span class="input-group-btn">
		<button class="btn btn-primary" type="submit">Load</button>
	</span>
</div>
</form>
<iframe src="<?= $url ? $url : 'about:blank' ?>" id="testFrame"></iframe>
