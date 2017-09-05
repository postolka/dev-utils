<?php
$pgeName = 'Render time calculator';

$cTypes = array(
	'animation' => 'Animation',
	'batch' => 'Batch',
	'frames' => 'Frames'
);

$cType = empty($_POST['cType']) ? 'animation' : $_POST['cType'];
if (!array_key_exists($cType, $cTypes))
	$cType = key($cTypes);

$iFps = empty($_POST['iFps']) ? 25 : $_POST['iFps'];
$iTime = empty($_POST['iTime']) ? 0 : $_POST['iTime'];

$iModels = empty($_POST['iModels']) ? 1 : $_POST['iModels'];
$iMaterials = empty($_POST['iMaterials']) ? 1 : $_POST['iMaterials'];

$iFrames = empty($_POST['iFrames']) ? 1 : $_POST['iFrames'];

$iFrame = empty($_POST['iFrame']) ? 0 : $_POST['iFrame'];
$iPCs = empty($_POST['iPCs']) ? 1 : $_POST['iPCs'];

function tabAttr($tabID, $cType){
	$attr = sprintf('id="%s"', $tabID);
	if ($cType != $tabID)
		$attr .= ' style="display: none"';
	return $attr;
}

?>
<style type="text/css">
#rtmForm label,
#rtmForm>input{
	float: left;
	clear: both;
}
#rtmForm label span,
#rtmForm label input{
	float: left;
}
#rtmForm label span{
	width: 70px;
}
#rtmForm fieldset{
	border: none;
	padding: 5px 0;
}
table th{
	text-align: left;
}
</style>
	<link href="/css/jquery.timepicker.css" type="text/css" rel="stylesheet"/>
<script type="text/javascript" src="/js/jquery.timepicker.min.js"></script>
<form action="#" method="post" id="rtmForm">
	<ul class="nav nav-tabs">
<?php foreach ($cTypes as $t => $label){
	$cls = $t == $cType ? ' class="active"' : NULL; ?>
		<li role="presentation"<?= $cls ?>><a href="#" onclick="switchTab(this, '<?=$t?>')"><?=$label?></a></li>
<?php } ?>
	</ul>
	<fieldset class="tab" <?= tabAttr('animation', $cType) ?>>
		<label for="iFps"><span>FPS:</span><input class="form-control" type="number" name="iFps" id="iFps" value="<?= $iFps ?>"/></label>
		<label for="iTime"><span>Time:</span><input class="form-control" type="text" name="iTime" id="iTime" value="<?= $iTime ?>"/></label>
	</fieldset>
	<fieldset class="tab" <?= tabAttr('batch', $cType) ?>>
		<label for="iModels"><span>Models:</span><input class="form-control" type="number" name="iModels" id="iModels" value="<?= $iModels ?>"/></label>
		<label for="iMaterials"><span>Materials:</span><input class="form-control" type="number" name="iMaterials" id="iMaterials" value="<?= $iMaterials ?>"/></label>
	</fieldset>
	<fieldset class="tab" <?= tabAttr('frames', $cType) ?>>
		<label for="iFrames"><span>frames:</span><input class="form-control" type="number" name="iFrames" id="iFrames" value="<?= $iFrames ?>"/></label>
	</fieldset>
	<input type="hidden" name="cType" id="cType" value="<?= $cType ?>"/>
	<label for="iFrame"><span>1 Frame:</span><input class="form-control" type="text" name="iFrame" id="iFrame" value="<?= $iFrame ?>"/></label>
	<label for="iPCs"><span>PCs:</span><input class="form-control" type="number" name="iPCs" id="iPCs" value="<?= $iPCs ?>"/></label>
	<div class="clearfix"></div>
	<button class="btn btn-primary" type="submit" name="calc" value="calc">Compute rendering time</button>
</form>
<script type="text/javascript">
function switchTab(tab, cType){
	var form = $('#rtmForm');
	form.find('ul.nav-tabs').find('li').removeClass('active');
	$(tab).closest('li').addClass('active');
	form.find('.tab').hide();
	form.find('#'+cType).show();
	form.find('#cType').val(cType);
}
	var tTime = new Date();
	var tFrame= new Date();
	var qTime = $('#iTime');
	var qFrame= $('#iFrame');
	var iTime = qTime.val().split(':').reverse();
	var iFrame =qFrame.val().split(':').reverse();

	tTime.setSeconds(iTime[0]);
	tTime.setMinutes(iTime[1]);
	tTime.setHours(0);
	tFrame.setSeconds(iFrame[0]);
	tFrame.setMinutes(iFrame[1]);
	tFrame.setHours(0);

	$('#iTime,#iFrame').timepicker({
		timeFormat: 'H:i:s',
		step: 1,
		maxTime: '0:20'
	});

	qTime.timepicker('setTime', tTime);
	qFrame.timepicker('setTime', tFrame);
</script>
<hr/>
<?php

function sec2time($sec, $mode = 2){
	$sec = round($sec);
	$tSec = $sec % 60;
	$min = round(($sec - $tSec) / 60);
	$tMin = $min % 60;
	$hrs = round(($min - $tMin) / 60);
	$tRes = NULL;
	if ($mode & 1)
		$tRes = sprintf('%d:%02d:%02d', $hrs, $tMin, $tSec);
	if ($mode & 2){
		$tHrs = $hrs % 24;
		$tDay = round(($hrs - $tHrs) / 24);
		$tResH = sprintf('%d:%02d:%02d', $tHrs, $tMin, $tSec);
		if ($tDay)
			$tResH = "{$tDay}d ".$tResH;
		if (!($mode & 1))
			$tRes = $tResH;
		elseif ($tDay)
			$tRes = $tResH." ($tRes)";
	}
	return $tRes;
}

function time2sec($time){
	list($tHrs, $tMin, $tSec) = array_reverse(array_pad(array_reverse(explode(':', $time)), 3, 0));
	return (($tHrs * 60) + $tMin) * 60 + $tSec;
}


$iFrCnt = NULL;

if (!empty($_POST['calc']) && ($iFrame && $iPCs)) {
	$sFrame = time2sec($iFrame);
	switch ($cType){
		case 'animation':
			$sTime = time2sec($iTime);
			$iFrCnt = $iFps * $sTime;
			break;
		case 'batch':
			$iFrCnt = $iModels * $iMaterials;
			break;
		case 'frames':
			$iFrCnt = $iFrames;
			break;
	}
}

if ($iFrCnt !== NULL) {?>
<table>
	<tr><th>Frames:</th><td><?= $iFrCnt ?></td></tr>
	<tr><th>Time total:</th><td><?= sec2time($iFrCnt * $sFrame, 3) ?></td></tr>
	<tr><th>Time compute:</th><td><?= sec2time($iFrCnt / $iPCs * $sFrame, 3) ?></td></tr>
</table>
<?php }
