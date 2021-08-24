<?php
/** @var array $config Defined in index.php */

$pgeName = 'Personal notes';

if (getPost('lock'))
	unset($_SESSION['note_key']);

$key = empty($_SESSION['note_key']) ? NULL : $_SESSION['note_key'];
$path = $config['notes']['path'];
$eAlg = $config['notes']['encryption'];

$dataEnc = trim(file_get_contents($path));
$dataDec = NULL;

if (!$key && ($pass = getPost('pass'))){
	$key = md5($pass);
}

$eKey = substr($key, 0, 16);
if ($dataEnc && $key){
	$dataDec = openssl_decrypt($dataEnc, $eAlg, $eKey);
	if (!$dataDec) while ($err = openssl_error_string()) echo $err;
	if (!$dataDec)  // invalid key
		$key = NULL;
}

if (!$key){?>
<form action="<?= URL_ROOT ?>notes" method="post" id="note-unlock">
	<h2><span class="fas fa-lock"></span> Locked!</h2>
	<div class="input-group">
		<input name="pass" class="form-control" type="password" title="Password"/>
		<span class="input-group-btn">
			<button class="btn btn-primary" type="submit" name="unlock" value="unlock"><span class="fas fa-retweet"></span> Unlock</button>
		</span>
	</div>
</form>
<?php
}
else{
$_SESSION['note_key'] = $key;

$edit = false;

if (getPost('save') == 'save'){
	$dataDec = getPost('data');
	$dataEnc = openssl_encrypt($dataDec, $eAlg, $eKey);
	file_put_contents($path, $dataEnc);
if (!$dataEnc) while ($err = openssl_error_string()) echo $err;
}
else
	$edit = getPost('edit');
if ($edit){
?>
<form action="<?= URL_ROOT ?>notes" method="post">
	<?= codeEditor($dataDec, 'data') ?>
	<button name="save" value="save" class="btn btn-success" title="Confirm"><span class="fas fa-ok"></span></button>
	<button name="save" value="cancel" class="btn btn-danger" title="Cancel"><span class="fas fa-times"></span></button>
</form>
<?php
}
else{ ?>
<form id="note-edit" action="<?= URL_ROOT ?>notes" method="post">
	<button name="edit" value="edit" class="btn btn-default" title="Edit"><span class="fas fa-edit"></span></button>
	<button name="lock" value="lock" class="btn btn-warning" title="Lock"><span class="fas fa-lock"></span></button>
</form>
<pre id="notes"><?= $dataDec ?></pre>
<?php
}
}
