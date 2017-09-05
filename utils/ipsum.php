<?php
$pgeName = 'Whatever-Ipsum generator';
require 'generator.inc';
require 'ipsum.inc';

$ipsumURL = URL_ROOT.'ipsum';
$fullURL = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$ipsumURL;


$locale = empty($_POST['locale']) ? current($locales) : $_POST['locale'];
$json = isset($_GET['json']);
$field = isset($_GET['field']);

$generatorURL = empty($_POST['generator']) ? $fullURL.'?json' : $_POST['generator'];

$faker = Faker\Factory::create($locale);
$faker->addProvider(new Ipsum\ExtendedProvider($faker, $locale));

function getField($fID, $type, $sel = NULL, $val= NULL){
	global $types;
	$sel = htmlentities($sel);
	$val = htmlentities($val);
	$label = empty($types[$type]) ? '???' : $types[$type];
	$readOnly = ($type == 'constant') ? NULL : ' readonly';
	return <<<HTML
<div class="input-group" id="$fID">
	<span class="input-group-addon">
		<label for="{$fID}_sel">$label</label>
	</span>
	<input type="hidden" class="form-control code" name="type[]" value="$type"/>
	<input type="text" class="form-control code" id="{$fID}_sel" name="selector[]" value="$sel" onchange="updateCode()" title="selector"/>
	<input type="text" class="form-control code value" value="$val" title="random generated value"$readOnly/>
	<div class="input-group-btn">
		<button onclick="delField('#$fID')" class="btn btn-danger"><span class="fa fa-trash"></span></button>
	</div>
</div>
HTML;
}

if ($field){
	$type = empty($_POST['type']) ? NULL : $_POST['type'];
	$fID = 'rf_'.$type.'_'.substr(md5(mt_rand()), 0, 10);
	$val = $faker->format($type);
	echo getField($fID, $type, NULL, $val);
	exit;
}
if ($json){
	header("Access-Control-Allow-Origin: *");
	foreach($_POST['fields'] as $fld){
		$sData = array();
		for ($i = $fld['cnt']; $i >0 ; $i--)
			$sData[] = $faker->format($fld['type']);
		$data[] = $sData;
	}
	echo json_encode($data);
	exit;
}

$updateScript = <<<'JS'
var selectors = $selectors$;
var fields = [];
$.each(selectors, function(k,selector){
	fields.push({
		type: selector.type,
		cnt: $(selector.selector).length
	});
});
$.post('$provider$', {locale: $locale$, fields: fields}, function(data){
	$.each(selectors, function(k,selector){
		var i = 0;
		$(selector.selector).each(function(){
			$(this).val(data[k][i++]);
		});
	});
	$constants$
});
JS;
?>
<script type="application/javascript">
function delField(id){
	$(id).remove();
	updateCode();
}
function addField(generator){
	var data = {
		type: generator,
		locale: $('#locale').val()
	};
	if (generator === 'constant')
		data['value'] = $('#constant').val();
	$.post('<?= $ipsumURL.'?field' ?>', data, function(genHTML){
		$('#fields').append(genHTML);
		updateCode();
	});
}
function updateCode(){
	var provider = $('#generator').val();
	var locale = $('#locale').val();
	var selectors = [];
	var constants = [];
	$('#fields').find('div.input-group').each(function(){
		var fType = $(this).find('input[name="type[]"]').val();
		var fSelector = $(this).find('input[name="selector[]"]').val();
		if (fType === 'constant'){
			constants.push('$('+JSON.stringify(fSelector)+').val('+JSON.stringify($(this).find('input.value').val())+')');
		}
		else
			selectors.push({
				type: fType,
				selector: fSelector});
	});
console.log(constants);
	//editor.getSession().setValue();
	editor.setValue(<?= strtr(json_encode($updateScript), array(
	'$selectors$' => '"+JSON.stringify(selectors)+"',
	'$provider$'  => '"+provider+"',
	'$locale$' => '\'"+locale+"\'',
	'$constants$' => '"+constants.join(\'\')+"'
)) ?>);
}
</script>
<form id="ipsum">
<fieldset>
	<legend>Settings</legend>
	<input class="form-control" type="url" id="generator" name="generator" value="<?= htmlentities($generatorURL) ?>" title="Generator service URL"/>
	<select class="form-control" name="locale" id="locale" title="Generator locale">
<?php foreach ($locales as $locale){ ?>
	<option value="<?=$locale?>"><?=$locale?></option>
<?php } ?>
	</select>
</fieldset>
<?= codeEditor('', 'code', 'javascript', array('readOnly' => true)); ?>
<fieldset id="fields">
</fieldset>
<div class="btn-group" role="group">
<?php foreach ($types as $generator => $label){ ?>
	<button type="button" class="btn btn-primary" onclick="addField('<?= $generator ?>')"><?= $label ?></button>
<?php } ?>
</div>

<div class="input-group" role="group">
	<input class="form-control" type="text" id="constant"/>
	<div class="input-group-btn">
		<button type="button" class="btn btn-primary" onclick="addField('constant')">Fixed</button>
	</div>
</div>

</form>
