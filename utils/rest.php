<?php

/** Rest services */
$apis = [
	'aimp'
];

if (empty($_GET['api']) || !in_array($_GET['api'], $apis))
	exit(404);

require "rest/".$_GET['api'].'.php';


exit;