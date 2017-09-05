<?php $pgeName = 'PHP unserializer';

$src = isset($_POST['src']) ? $_POST['src'] : '';
switch($_POST['type']){
	case 'json':
		$dData = json_decode($src); break;
	case 'php':
		$dData = unserialize($src); break;
	default:
		$dData = 'no-data/unknown';
}

$extra = NULL;

?>
<form method="post" action="<?= URL_ROOT ?>deserializer">
<?= codeEditor($code, 'src', 'php') ?>
	<button class="btn btn-primary" type="submit" name="type" value="php">PHP-unserialize</button>
	<button class="btn btn-primary" type="submit" name="type" value="json">JSON-decode</button>
</form>
<hr/>
<pre>
<?= $extra.print_r($dData, true) ?>
</pre>
