<?php
$pgeName = 'PHP code interpreter';
$varMax = 10;
$code = isset($_POST['phpcode']) ? $_POST['phpcode'] : NULL;
if (!$code)
	$code= 'echo $a;';
$varCnt = 0;
if (isset($_POST['cnt']))
	$varCnt = (int) $_POST['cnt'];
$varCnt = min(10, max(1, $varCnt));
?>
<form action="<?= URL_ROOT ?>interpreter" method="post">
	<div class="input-group">
		<label class="input-group-addon" for="cnt">#</label>
		<input class="form-control" type="number" id="cnt" name="cnt" value="<?= $varCnt ?>" min="1" max="<?= $varMax ?>"/>
	</div>
<?php
for ($n = 0; $n < $varCnt; $n++){
	$var = chr(ord('a') + $n);
	$val = empty($_POST[$var]) ? NULL : $_POST[$var];
	$$var = $val;
?>
	<div class="input-group">
		<label class="input-group-addon" for="var_<?= $var ?>"><?= $var ?></label>
		<input class="form-control" type="text" id="var_<?= $var ?>" name="<?= $var ?>" value="<?= htmlentities($val) ?>"/>
	</div>
<?php } ?>
<?= codeEditor($code, 'phpcode', 'php'); ?>
	<button class="btn btn-primary" type="submit">Interpret</button>
</form>
<hr/>
<?php
$ret = eval($code);
if ($ret === false)
	$ret = eval('?>'.$code);
if ($ret === false)
	$ret = eval($code.'<?php');
if ($ret === false)
	$ret = eval('?>'.$code.'<?php');
?>
<hr/><?= $ret ?>
