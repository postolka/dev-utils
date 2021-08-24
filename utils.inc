<?php

const APP_ROOT = __DIR__;
const URL_ROOT = '/';

require_once APP_ROOT.'/vendor/autoload.php';

Tracy\Debugger::enable(Tracy\Debugger::DEVELOPMENT);

function codeEditor($code, $name = 'code', $syntax = 'html', $config = []): string {
	static $c = 0;
	$code = htmlentities($code);
	$initCode = '';
	if (empty($config['lines']))
		$config['lines'] = 50;
	if (!in_array('noWarnings', $config)){
		unset($config['noWarnings']);
		$initCode = 'editor.getSession().setUseWorker(false);
';
	}
	$config = json_encode($config, JSON_FORCE_OBJECT);
	$html = <<<HTML
<textarea id="editor$c" cols="200" rows="20">$code</textarea>
<input type="hidden" name="$name" value=""/>
<script type="text/javascript">
	const editor = ace.edit("editor$c");
	editor.setOptions($config);
	editor.setTheme("ace/theme/monokai");
	editor.getSession().setUseWrapMode(true);
	editor.getSession().setMode("ace/mode/$syntax");
$initCode
$(function() {
	$('form').submit(function(){
		$('input[name="$name"]').val(editor.getValue());
	});
});
</script>
HTML;
	$c++;
	return $html;
}

function htmlHead($title, $description = NULL, $keywords = NULL, $bodyAttr=NULL): string {
	if (!$description)$description= $title;
	if (!$keywords)		$keywords		= $description;
	$root = URL_ROOT;
	$urlBower = $root.'bower_components/';
	return <<<HTML
<!DOCTYPE html>
<html lang="cs">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>$title</title>
	<meta name="author" content="Jan Kopidol"/>
	<meta name="keywords" content="$keywords"/>
	<meta name="description" content="$description"/>
	<link href="{$root}utils.css" type="text/css" rel="stylesheet"/>
	<script type="text/javascript" src="{$root}utils.js"></script>
	<script type="text/javascript" src="{$root}vendor.js"></script>
	<link type="image/x-icon" href="{$root}favicon.ico" rel="SHORTCUT ICON"/>
	<script type="text/javascript">
$(function() {
	$('code.syntax').each(function(i, block) {
		hljs.highlightBlock(block);
	});
});
	</script>
</head>
<body$bodyAttr>
HTML;
}

function htmlEnd(): string {
	return '</body></html>';
}


/**
 * Gets value of GET variable
 * @param string $varName - name of variable
 * @param mixed $default - default value
 * @param ?string $cType - type to which should be value converted
 * @return mixed - value of variable, $default if undefined
 */
function getGet(string $varName, $default = NULL, ?string $cType = NULL){
	return defType($_GET[$varName] ?? $default, $cType);
}

/**
 * Gets value of POST variable
 * @param string $varName - name of variable
 * @param mixed $default - default value
 * @param ?string $cType - type to which should be value converted
 * @return mixed - value of variable, $default if undefined
 */
function getPost(string $varName, $default = NULL, ?string $cType = NULL){
	return defType($_POST[$varName] ?? $default, $cType);
}

/**
* Convert value to type (for sure)
* @param mixed $val - input value
* @param ?string $type - type
* @return mixed - converted value, NULL if value can not be converted to that type
*/
function defType($val, ?string $type = NULL){
	if (!$type) return $val;
	return settype($val, $type) ? $val : NULL;
}

function unparse_url($parsed_url): string {
	$scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '//';
	$host     = $parsed_url['host'] ?? '';
	$port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
	$user     = $parsed_url['user'] ?? '';
	$pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
	$pass     = ($user || $pass) ? "$pass@" : '';
	$path     = $parsed_url['path'] ?? '';
	$fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
	return "$scheme$user$pass$host$port$path$fragment";
}
