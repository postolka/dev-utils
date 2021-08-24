<?php

use Nette\Neon\Neon;

require '../utils.inc';

$config = Neon::decode(file_get_contents(APP_ROOT.'/config.neon'));

session_name('utils');
session_start();

$pgeName = '';
$util = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', getGet('pge')));

ob_start();
/** @noinspection PhpIncludeInspection Just dynamically determined */
@include APP_ROOT."/utils/$util.php";
$pgeBody = ob_get_clean();

$menuGroups = [
	'Generators'	=> [
		'checksums'=>'Checksum generator',
		'base64'	=> 'Base64 generator',
		'ipsum'   => 'Ipsum generator',
		'genpass'	=> 'Password generator'],
	'Viewers'	=> [
		'deserializer'=> 'PHP (de)serializer',
		'interpreter'	=> 'PHP interpreter',
    'xmlformat'		=> 'XML formater',
		'html'				=> 'HTML viewer',
    'har'				  => 'HAR viewer',
		'iframe'		  => 'IFrame'],
	'Tools'	=> [
		'template'		=> 'HTML template',
		'comparator'	=> 'phpini() comparator',
		'dbfiller'    => 'DB Filler'],
	'Utils'	=> [
		'info'		=> 'phpinfo()',
		'locales'	=> 'List of locales',
		'notes'		=> 'Notes',
		'speeddial'		=> 'Speed dial',
		'mail'		=> 'Send email via SMTP'],
	'Graphics'	=> [
		'dpi'			=> 'DPI calculator',
		'rendertime'			=> 'Render time calculator']
];

?>
<?= htmlHead('Dev utils'.($pgeName ? " - $pgeName" : NULL)) ?>
<h1>
	<span class="main">My Tools</span>
	<span class="sub"><?= $pgeName ?: '&nbsp;' ?></span></h1>
<div id="module">
	<ul id="menu">
		<?php
		foreach($menuGroups as $grpName => $grpItems){
			?>
			<li><span class="head"><?=$grpName?></span>
			<ul class="nav nav-tabs flex-column"><?php
				foreach ($grpItems as $iUrl => $iName)
					printf('<li class="nav-item"><a href="%s" class="nav-link%s">%s</a></li>',
						URL_ROOT.$iUrl,
						$iUrl == $util ? ' active' : NULL,
						$iName);
				?></ul></li><?php
		}
		?>
	</ul>
<?= $pgeBody ?>
</div>
<?= htmlEnd() ?>
