<?php $pgeName = 'Speed dial';

define('SPEED_DIAL_CFG', APP_ROOT.'/data/speed_dial.json');
define('SPEED_DIAL_PLACEHOLDER', '~QUERY~');

$update = !empty($_POST['update']);
$usrc = @json_decode(@file_get_contents(SPEED_DIAL_CFG));
if ($update){
	$szx = (int) (isset($_POST['x']) ? $_POST['x'] : NULL);
	$szy = (int) (isset($_POST['y']) ? $_POST['y'] : NULL);
}
else{
	$szy = count($usrc);
	$szx = count(current($usrc));
}

function getDomain($url){
	return preg_match('/^http(\?:s)?:\/\/([-\.a-z0-9]+)/', $url, $match)
		? $match[1]
		: NULL;
}

$szx = max(1, $szx);
$szy = max(1, $szy);
$urls = array_fill(0, $szy, array_fill(0, $szx, NULL));
if ($update){
	for ($y = 0; $y < $szy; $y++)
		for ($x = 0; $x < $szx; $x++)
			if (!empty($_POST["url-$x-$y"]))
				$urls[$y][$x] = (object) array(
					'url' => $_POST["url-$x-$y"],
					'label' => empty($_POST["label-$x-$y"])
						? getDomain($_POST["url-$x-$y"])
						: $_POST["label-$x-$y"]
				);
	file_put_contents(SPEED_DIAL_CFG, json_encode($urls));
}
else{
	for ($y = 0; $y < $szy; $y++)
		for ($x = 0; $x < $szx; $x++)
			$urls[$y][$x] = empty($usrc[$y][$x])
				? (object)  array('url' => NULL, 'label' => NULL)
				: $usrc[$y][$x];
}

if (isset($_GET['dial'])){
	echo htmlHead('Speed dial', NULL, NULL, ' id="speed_dial"');
	?>
<table class="speed_dial">
	<?php
	for ($y = 0; $y < $szy; $y++){
		$row = '';
		for ($x = 0; $x < $szx; $x++){
			$label = htmlentities($urls[$y][$x]->label);
			$url = $urls[$y][$x]->url;
			if (strpos($url, SPEED_DIAL_PLACEHOLDER)){
				$urlParts = parse_url($url);
				$action = unparse_url($urlParts);
				$iList = '';
				$target= NULL;
				foreach (explode('&', $urlParts['query']) as $q){
					list($var, $val) = array_pad(explode('=', $q), 2, NULL);
					list($type, $val) = ($val == SPEED_DIAL_PLACEHOLDER)
						? array('text', NULL)
						: array('hidden', htmlentities($val));
					if (($type == 'text') && !$target){
						$target = sprintf('sd-%d-%d', $x, $y);
						$id = " id=\"$target\"";
					}
					$iList .= <<<HTML
<input type="$type" name="$var" value="$val"$id/>
HTML;
				}
				$cell = <<<HTML
<form method="get" action="$action">
	$iList
	<button type="submit">$label <span class="fa fa-chevron-right"></span></button>
</form>
HTML;

			}
			else{
				$url = htmlentities($url);
				$cell = "<a href=\"$url\">$label</a>";
			}
			$row .= "<td>$cell</td>";
		}
		echo "<tr>$row</tr>";
	}
	?>
</table>
	<?php
	echo htmlEnd();
	exit;
}


$unescape = !empty($_POST['unescape']);
if ($unescape)
	$code = stripcslashes($code);
?>
<form action="<?= URL_ROOT ?>speeddial" method="post">
	<label for="width">width:</label><input type="number" name="x" min="1" value="<?= $szx ?>" id="width"/>
	<label for="height">height:</label><input type="number" name="y" min="1" value="<?= $szy ?>" id="width"/>
<table class="speed_editor">
<?php
	for ($y = 0; $y < $szy; $y++){
		$row = '';
		for ($x = 0; $x < $szx; $x++){
			$label = htmlentities($urls[$y][$x]->label);
			$url = htmlentities($urls[$y][$x]->url);
			$row .= <<<HTML
<td>
<label for="label-$x-$y">label:</label><input class="form-control" type="text" id="label-$x-$y" name="label-$x-$y" value="$label"/>
<label for="url-$x-$y">URL:</label><input class="form-control" type="url" id="url-$x-$y" name="url-$x-$y" value="$url"/>
</td>
HTML;
		}
		echo "<tr>$row</tr>";
	}
?>
</table>
	<button class="btn btn-primary" type="submit" name="update" value="update">Update</button>
</form>
