<?php $pgeName = 'Base64 encoder/decoder';
$text = $base = NULL;

if (!empty($_POST['dir'])) switch($_POST['dir']){
	case '>':
		$text = isset($_POST['text']) ? $_POST['text'] : NULL;
		$base = base64_encode($text);
		break;
	case '<':
		$base = isset($_POST['base']) ? $_POST['base'] : NULL;
		$text = base64_decode($base);
		break;
}
?>
<form action="#" method="post">
<table>
<tr><th><label for="text">plain</label></th><th></th><th><label for="base">base64</label></th></tr>
<tr>
	<td><textarea name="text" id="text" cols="80" rows="10"><?=$text?></textarea></td>
	<td>
		<div class="btn-group-vertical" role="group">
			<button class="btn btn-default" name="dir" type="submit" value="&gt;"><span class="fa fa-chevron-right"></span></button>
			<button class="btn btn-default" name="dir" type="submit" value="&lt;"><span class="fa fa-chevron-left"></span></button>
		</div>
	</td>
	<td><textarea name="base" id="base" cols="80" rows="10"><?=$base?></textarea></td>
</tr></table>
</form>
