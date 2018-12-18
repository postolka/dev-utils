<?php $pgeName = 'Password Generator';
$passChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
$passCharsEx = '@#+-*/=!';
$crazyRatio = 0.3;

$len = (int) (isset($_POST['len']) ? $_POST['len'] : 12);
$cnt = (int) (isset($_POST['cnt']) ? $_POST['cnt'] : 12);
$crz = (int) (isset($_POST['crz']) ? $_POST['crz'] : 0);
$crazyRatio = $crz/100;
$pc = strlen($passChars)-1;
$px = strlen($passCharsEx)-1;
$xc = (int) ($crazyRatio * $len);

$pList = array();
for ($p = 0; $p < $cnt; $p++){
	$pass = '';
	$crazy = $cb = $len-1;

	$cp = array();
	if ($crz)
		for ($c = 0; $c < $xc; $c++)
			$cp[mt_rand(1, $len-1)] = true;
	for ($c = 0; $c < $len; $c++)
		$pass .= empty($cp[$c])
			? $passChars[mt_rand(0, $pc)]
			: $passCharsEx[mt_rand(0, $px)];
	$pList[] = $pass;
}

?>
<form action="#" method="post">
<table>
	<tr><td><label for="len">Length</label></td><td><input class="form-control" id="len" type="number" name="len" value="<?= $len ?>"/></td></tr>
	<tr><td><label for="cnt">Count</label></td><td><input class="form-control" id="cnt" type="number" name="cnt" value="<?= $cnt ?>"/></td></tr>
	<tr><td><label for="crz">Craziness</label></td><td><input class="form-control" id="crz" type="number" name="crz" value="<?= $crz ?>"/></td></tr>
</table>
<button class="btn btn-primary" type="submit">Generate</button>
</form><hr/>
<table>
	<tr><th>pass</th><th>MD5</th><th>PassHash</th></tr>
<?php
foreach ($pList as $pass)
	printf('<tr><td><pre>%s</pre></td><td><pre>%s</pre></td><td><pre>%s</pre></td></tr>
',
		$pass,
		md5($pass),
		password_hash($pass, PASSWORD_BCRYPT)
	); ?>
</table>