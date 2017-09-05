<?php $pgeName = 'HTML processor';
$code = isset($_POST['htmlcode']) ? $_POST['htmlcode'] : NULL;
$unescape = !empty($_POST['unescape']);
if ($unescape)
	$code = stripcslashes($code);
?>
<form action="<?= URL_ROOT ?>html" method="post">
<?= codeEditor($code, 'htmlcode', 'html'); ?>
	<label for="unescape"><input type="checkbox" name="unescape" id="unescape" value="1"/>Unescape</label>
	<button class="btn btn-primary" type="submit">View result</button>
</form>
<hr/>
<?= $code ?>
<hr/>
<?= htmlentities($code) ?>