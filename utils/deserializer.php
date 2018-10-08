<?php $pgeName = 'PHP unserializer';


$src = isset($_POST['src']) ? $_POST['src'] : '';
switch($_POST['type']){
	case 'json':
		$assoc = !empty($_POST['json_assoc']);
//		$flags = JSON_THROW_ON_ERROR;
		$flags = 0;
		if (!empty($_POST['json_flag_bigint_as_string']))
			$flags |= JSON_BIGINT_AS_STRING;
		if (!empty($_POST['json_flag_object_as_array']))
			$flags |= JSON_OBJECT_AS_ARRAY;
		$dData = json_decode($src, $assoc, 512, $flags); break;
	case 'php':
		$dData = unserialize($src); break;
	default:
		$dData = 'no-data/unknown';
}

$extra = NULL;

$eTicket = $dData && is_array($dData) && !empty($dData[5]) && preg_match('/[\d]-[\d]/', $dData[5]);
if ($eTicket){
	$ppPay = array(
		0 => 'Cash',
		1 => 'Credit Card',
		2 => 'Cheque',
		3 => 'Payment Order',
		4 => 'COD',
		5 => 'Post Remittance');
	$ppPick = array(
		1 => 'Pickup Prague',
		2 => 'Pickup Bus',
		3 => 'Mail delivery',
		4 => 'E-Mail',
		5 => 'Pickup Brno',
		6 => 'SMS');
	$tPay = $tPick = 0;
	sscanf($dData[5], '%d-%d', $tPay, $tPick);
	foreach (array(0, 1) as $d)
		if ($dData[$d]
			&& !empty($dData[$d][0]) && !empty($dData[$d][1])
			&& !empty($dData[$d][2]) && !empty($dData[$d][2]['CityName'])
			&& !empty($dData[$d][3]) && !empty($dData[$d][3]['CityName'])){
			$tDep = explode('T', $dData[$d][0]);
			$tArv = explode('T', $dData[$d][1]);
			$extra .= sprintf("[%s =&gt; %s] (%s %s)\n", $dData[$d][2]['CityName'], $dData[$d][3]['CityName'], $tDep[0], substr($tDep[1], 0, 5));
		}

	$extra .= sprintf('[%s / %s]',
		isset($ppPay[$tPay]) ? $ppPay[$tPay] : '!UNDEF!',
		isset($ppPick[$tPick]) ? $ppPick[$tPick] : '!UNDEF!');
}

if($extra) $extra .= '<hr/>';
?>
<form method="post" action="<?= URL_ROOT ?>deserializer">
<?= codeEditor($code, 'src', 'php') ?>
	<button class="btn btn-primary" type="submit" name="type" value="php">PHP-unserialize</button>
	<button class="btn btn-primary" type="submit" name="type" value="json">JSON-decode</button>
	<input type="checkbox" value="1" id="json_assoc" name="json_assoc"/>
	<label for="json_assoc">convert objects to associative arrays</label>
	<input type="checkbox" value="1" id="json_flag_bigint_as_string" name="json_flag_bigint_as_string"/>
	<label for="json_flag_bigint_as_string">JSON_BIGINT_AS_STRING</label>
	<input type="checkbox" value="1" id="json_flag_object_as_array" name="json_flag_object_as_array"/>
	<label for="json_flag_object_as_array">JSON_OBJECT_AS_ARRAY</label>
</form>
<hr/>
<pre>
<?= $extra.print_r($dData, true) ?>
</pre>
