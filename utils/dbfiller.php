<?php
$pgeName = 'Database filler';
$locale = 'cs_CZ'; // @todo: make selecteble
$fillerURL = URL_ROOT.'dbfiller';
$msgSuccess =
$msgError = NULL;
$dbData = empty($_SESSION['dbfiller']) ? NULL : $_SESSION['dbfiller'];
if (!(empty($_POST['user']) || empty($_POST['pass']))){
	$dbData = (object) array(
		'host' => 'localhost',
		'user' => $_POST['user'],
		'pass' => $_POST['pass'],
		'database' => $_POST['user']
	);
	if (!empty($_POST['database']))
		$dbData->database = $_POST['database'];
	if (!empty($_POST['host']))
		$dbData->host = $_POST['host'];
}

if ($dbData) {
	try {
		dibi::connect(array(
			'driver' => 'mysqli',
			'host' => $dbData->host,
			'username' => $dbData->user,
			'password' => $dbData->pass,
			'database' => $dbData->database
		));
	}
	catch(DibiException $e){
		$dbData = NULL;
		$msgError = $e->getMessage();//'Unable to connect to database';
	}

	if ($dbData)
		$_SESSION['dbfiller'] = $dbData;
}
if ($dbData) {
	$dbTables = dibi::query('SHOW TABLES');
	$tblList = array();
	$table = getGet('table');
?>
	<form action="<?= $fillerURL ?>" method="get">
		<div class="input-group">
			<select class="form-control" name="table">
<?php foreach($dbTables as $t){
				$tName = reset($t->toArray());
				$tblList[] = $tName;
?>
				<option value="<?= $tName ?>"<?= ($tName == $table) ? ' selected' : NULL ?>><?= $tName ?></option>
<?php	} ?>
			</select>
			<div class="input-group-btn">
				<button class="btn btn-primary"><span class="fa fa-ok"></span></button>
			</div>
		</div>
	</form>
<?php

	if (in_array($table, $tblList)){
		/** @var DibiRow[] $dbColumns */
		$dbColumns = dibi::query("SHOW COLUMNS FROM [$table]")->fetchAssoc('Field');
		require 'generator.inc';
		if ($go = getPost('go')){
			if ($mod = ($go == 'mod'))
				$cuCols = $ccCols = array();
			$iData = $uCols = array();
			foreach ($_POST as $var => $val) if (!empty($types[$val]) && preg_match('/^col_(.*)$/', $var, $match)){
				$cName = $match[1];
				if (array_key_exists($cName, $dbColumns)) {
					$iData[$cName] = array();
					$uCols[$cName] = $val;
					if ($mod){
						$ccCols[$cName] = "[$cName] {$dbColumns[$cName]->Type}";
						$cuCols[$cName] = "t.$cName=s.$cName";
					}
				}
			}
			require 'ipsum.inc';
			$faker = Faker\Factory::create($locale);
			$faker->addProvider(new Ipsum\ExtendedProvider($faker, $locale));
			switch($go){
				case 'add':
					$data = array();
					dibi::insert($table, array());
					break;
				case 'mod':
					$idField = NULL;
					$cuCond = NULL;
					foreach($dbColumns as $col)
						if ($col->Key == 'PRI') {
							$idField = $col->Field;
							$ccCols[$idField] = "[$idField] {$dbColumns[$idField]->Type}";
							$ccCols[] = "PRIMARY KEY ([$idField])";
							$cuCond = "t.$idField=s.$idField";
							break;
						}
					$data = array();
					$qCols = array();

					foreach(dibi::query("SELECT [$idField] FROM [$table]") as $r){
//						if ($r->$idField > 1100) break;
						$iData[$idField][] = $r->$idField;
						$gender = (mt_rand() % 2) ? 'Male' : 'Female';
						foreach($uCols as $c => $type){
							switch($type){
								case 'firstName':
								case 'lastName':
									$type .= $gender;
									break;
							}
							$val = $faker->format($type);
							switch($type){
								case 'postcode':
									$val = (int) preg_replace('[^\d]', '', $val);
									break;
							}
							$iData[$c][] = $val;
						}
					}
					$ttName = 'tmp_'.substr(md5(mt_rand()), 0, 10);
					$rcnt = count($data);
					$inserted = 0;
					try{
						dibi::query(sprintf('CREATE TEMPORARY TABLE [%s] (%s);', $ttName, implode(',', $ccCols)));
						dibi::query("INSERT INTO [$ttName] %m", $iData);
						dibi::query(sprintf('UPDATE [%s] AS s, [%s] AS t SET %s WHERE (%s);', $ttName, $table, implode(',', $cuCols), $cuCond));
						dibi::query(sprintf('DROP TABLE [%s];', $ttName));
					}
					catch(DibiException $e){
						$msgError = $e->getMessage();
					}
					finally{
						$msgSuccess = sprintf('Updated [%d] rows', count($iData[$idField]));
					}
					break;
			}
		}

		$optList = '<option value="#" selected>- none -</option>';
		foreach($types as $tID => $tLabel)
			$optList .= sprintf('<option value="%s">%s</option>', $tID, $tLabel);
		?>
<form action="<?=$fillerURL?>?table=<?=$table?>" method="post">
<?php
		foreach ($dbColumns as $c){
			$cName = $c->Field;
?>
		<div class="input-group">
			<span class="input-group-addon">
				<label for="col_<?=$cName?>"><?=$cName?></label>
			</span>
			<select class="form-control" name="col_<?=$cName?>" id="col_<?= $cName ?>">
				<?=$optList?>
			</select>
		</div>
<?php	} ?>
	<button type="submit" name="go" value="mod" class="btn btn-danger">Modify</button>

	<div class="input-group">
		<input type="number" name="cnt" value="10" class="form-control"/>
		<div class="input-group-btn">
			<button type="submit" name="go" value="add" class="btn btn-primary">Add</button>
		</div>
	</div>
</form>
<?php
	}

}
else{
	/*$dbSaved = array();
	foreach ($config['databases'] as $db => $cfg)
		$dbSaved[] = (object) array(
			'database' => $db,
			'user'  => $cfg['user'],
			'pass'  => $cfg['pass']
		);*/
?>
<script type="text/javascript">
	var dbSaved = <?= json_encode($config['databases'])?>;
	function fillDBConfig(){
		var db = $('#dbCfg').val();
		var cfg = dbSaved[db];
		$('#database').val(db);
		$('#user').val(cfg.user);
		$('#pass').val(cfg.pass);
	}
</script>
<form action="<?= $fillerURL ?>" method="post">
	<select class="form-control" onchange="fillDBConfig()" id="dbCfg">
		<option value=""></option>
<?php foreach(array_keys($config['databases']) as $db){	?>
		<option value="<?=$db?>"><?=$db?></option>
<?php	} ?>
	</select>
	<label for="host">Host</label><input class="form-control" name="host" id="host" type="text" value="localhost"/>
	<label for="database">Database</label><input class="form-control" name="database" id="database" type="text"/>
	<label for="user">Username</label><input class="form-control" name="user" id="user" type="text"/>
	<label for="pass">Password</label><input class="form-control" name="pass" id="pass" type="password"/>
	<button type="submit" class="btn btn-primary">Connect</button>
</form>
<?php
}
if ($msgError){?>
	<div class="alert alert-danger"><?= $msgError ?></div>
<?php	}
if ($msgSuccess){?>
	<div class="alert alert-success"><?= $msgSuccess ?></div>
<?php	}