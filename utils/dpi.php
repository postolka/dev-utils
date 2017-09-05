<?php
$pgeName = 'DPI calculator';
define('CM_INCH', 2.54);
$paperSizes = array(
	0	=> array(84.1, 118.9),
	1	=> array(59.4, 84.1),
	2	=> array(42.0, 59.4),
	3	=> array(29.7, 42.0),
	4	=> array(21.0, 29.7),
	5	=> array(14.8, 21.0),
	6	=> array(10.5, 14.8));

$dpi = empty($_POST['dpi']) ? NULL : (int)$_POST['dpi'];
$szx = empty($_POST['szx']) ? NULL : (float)str_replace(',', '.', $_POST['szx']);
$szy = empty($_POST['szy']) ? NULL : (float)str_replace(',', '.', $_POST['szy']);
$rsx = empty($_POST['rsx']) ? NULL : (int)$_POST['rsx'];
$rsy = empty($_POST['rsy']) ? NULL : (int)$_POST['rsy'];
$ssz = empty($_POST['ssz']) ? NULL : (int)$_POST['ssz'];
$calc = !empty($_POST['calc']);
if (!($dpi || $calc)) $dpi = 300;
if (!$ssz) $ssz = 4;
if (!($szx && $szy && $rsx && $rsy))
	list($szx, $szy) = $paperSizes[$ssz];

$paperSizesJS = array();
foreach($paperSizes as $a => $sz)
	$paperSizesJS[] = sprintf('%d:[%.1F,%.1F]', $a, $sz[0], $sz[1])
?>
<style type="text/css">
#dpiForm input{
	width: 50px; }
#dpiForm input[type='submit']{
	width: 100%; }
#dpiForm td{
	text-align: center; }
</style>
<script type="text/javascript">
var paperSizes = {<?= implode(',', $paperSizesJS) ?>};
function setSize(select){
	var fSrc = $('#dpiForm');
	var sSize= paperSizes[$(select).val()];
	fSrc.find('input[name="szx"]').val(sSize[0]);
	fSrc.find('input[name="szy"]').val(sSize[1]);
}
</script>
<form action="#" method="post" id="dpiForm">
<table>
	<tr><th><label for="szx">cm</label></th><th><label for="dpi">dpi</label></th><th><label for="rsx">px</label></th><th><label for="ssz">template</label></th></tr>
	<tr>
		<td><input class="form-control" type="number" step="any" name="szx" id="szx" value="<?= $szx ?>"/> &times; <input class="form-control" name="szy" id="szy" type="number" step="any" value="<?= $szy ?>"/></td>
		<td><input class="form-control" type="number" name="dpi" id="dpi" value="<?= $dpi ?>"/></td>
		<td><input class="form-control" type="number" name="rsx" id="rsx" value="<?= $rsx ?>"/> &times; <input class="form-control" name="rsy" id="rsy" type="number" value="<?= $rsy ?>"/></td>
		<td><select class="form-control" onchange="setSize(this)" name="ssz" id="ssz"><?php
foreach($paperSizes as $a => $sz)
	printf('<option value="%d"%s>A%d (%.1f &times; %.1f)</option>', $a, ($ssz == $a) ? ' selected="selected"' : NULL,$a, $sz[0], $sz[1]); ?>
	</select></td></tr>
<?php

$szIN = ($szx && $szy) ? array($szx/CM_INCH, $szy/CM_INCH) : NULL;
$szPX = ($rsx && $rsy) ? array($rsx, $rsy) : NULL;
$ic = 0;
if ($szIN) $ic++;
if ($szPX) $ic++;
if ($dpi) $ic++;
$tsz = $tdp = $trs = $scl = NULL;
if ($calc && ($ic >= 2)){
	if ($dpi){
		if ($szIN){
			$rsx = round($szIN[0] * $dpi);
			$rsy = round($szIN[1] * $dpi);
			if ($szPX)
				$scl = sprintf('[%.1f%% &times; %.1f%%]', $rsx * 100 / $szPX[0], $rsy * 100 / $szPX[1]);
		}
		else{
			$szIN = array(round($szPX[0] / $dpi), round($szPX[1] / $dpi));
			$szx = round($szIN[0] / CM_INCH);
			$szy = round($szIN[1] / CM_INCH);
		}
	}
	else{
		$dpX = round($szPX[0]/$szIN[0]);
		$dpY = round($szPX[1]/$szIN[1]);
		$dpi = ($dpX == $dpY)
			? sprintf('[%d]', $dpX)
			: sprintf('[%d] &times; [%d]', $dpX, $dpY);
	}
	$tsz = sprintf('%.1f &times; %.1f', $szx, $szy);
	$tszI = sprintf('%.1f &times; %.1f', $szIN[0], $szIN[1]);
	$tdp = $dpi;
	$trs = sprintf('%d &times; %d', $rsx, $rsy);
}
?>
	<tr>
		<td><?= $tsz ?></td>
		<td><?= $tdp ?></td>
		<td><?= $trs ?></td>
		<td><button class="btn btn-primary" type="submit" name="calc" value="calc">Calculate</button></td>
	</tr>
	<tr>
		<td><?= $tszI ?></td>
	</tr>
	<tr>
		<td colspan="2"></td>
		<td><?= $scl ?></td></tr>
</table>
</form>
