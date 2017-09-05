<?php

define('APP_ROOT', __DIR__);
define('URL_ROOT', '/');

require_once APP_ROOT.'/vendor/autoload.php';

Tracy\Debugger::enable(Tracy\Debugger::DEVELOPMENT);

function codeEditor($code, $name = 'code', $syntax = 'html', $config = array()){
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
	var editor = ace.edit("editor$c");
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

function htmlHead($title, $description = NULL, $keywords = NULL, $bodyAttr=NULL){
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
	<link href="{$root}css/styles.css" type="text/css" rel="stylesheet"/>
	<link href="{$urlBower}jt.timepicker/jquery.timepicker.css" type="text/css" rel="stylesheet"/>
	<!--<link href="{$root}css/bootstrap.vertical-tabs.min.css"  type="text/css" rel="stylesheet"/>-->
	<link href="{$root}css/code-styles/railscasts.css"  type="text/css" rel="stylesheet"/>
	<link type="image/x-icon" href="{$root}favicon.ico" rel="SHORTCUT ICON"/>
	<script type="text/javascript" src="{$urlBower}jquery/dist/jquery.min.js"></script>
	<script type="text/javascript" src="{$urlBower}jt.timepicker/jquery.timepicker.min.js"></script>
	<script type="text/javascript" src="{$urlBower}tether/dist/js/tether.min.js"></script>
	<script type="text/javascript" src="{$urlBower}bootstrap/dist/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="{$root}js/highlight.pack.js"></script>
	<script type="text/javascript" src="{$root}js/src-min-noconflict/ace.js"></script>
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

function htmlEnd(){
	return '</body></html>';
}


/**
 * Gets value of GET variable
 * @param string $varName - name of variable
 * @param mixed $default - default value
 * @param string $cType - type to which should be value converted
 * @return mixed - value of variable, $default if undefined
 */
function getGet($varName, $default = NULL, $cType = NULL){
	return defType(isset($_GET[$varName]) ? $_GET[$varName] : $default, $cType);
}

/**
 * Gets value of POST variable
 * @param string $varName - name of variable
 * @param mixed $default - default value
 * @param string $cType - type to which should be value converted
 * @return mixed - value of variable, $default if undefined
 */
function getPost($varName, $default = NULL, $cType = NULL){
	$val = isset($_POST[$varName]) ? $_POST[$varName] : $default;
	if (get_magic_quotes_gpc() && !is_array($val))
	$val = stripslashes($val);
	return defType($val, $cType);
}

/**
* Convert value to type (for sure)
* @param mixed $val - input value
* @param string $type - type
* @return mixed - converted value, NULL if value can not be converted to that type
*/
function defType($val, $type){
	if (!$type) return $val;
	if (!settype($val, $type)){
		$val = NULL;
		return settype($val, $type) ? $val : NULL;
	}
	return $val;
}

function unparse_url($parsed_url) {
	$scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '//';
	$host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
	$port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
	$user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
	$pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
	$pass     = ($user || $pass) ? "$pass@" : '';
	$path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
	$fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
	return "$scheme$user$pass$host$port$path$fragment";
}
