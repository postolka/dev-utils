<?php
$pgeName = 'XML formater';
$code = isset($_POST['xmlcode']) ? $_POST['xmlcode'] : NULL;

function formatXmlString($xml, $iSize = 1) {
	if (!$xml) return NULL;
	if (class_exists('DOMDocument')){
/**	@var DOMDocument $doc */
		$doc = @DOMDocument::loadXML($xml);
		$doc->preserveWhiteSpace = false;
		$doc->formatOutput = true;
		return $doc->saveXML();
	}

	// add marker linefeeds to aid the pretty-tokeniser (adds a linefeed between all tag-end boundaries)
	$xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $xml);

	// now indent the tags
	$token      = strtok($xml, "\n");
	$result     = ''; // holds formatted version as it is built
	$pad        = 0; // initial indent
	$matches    = array(); // returns from preg_matches()

	// scan each line and adjust indent based on opening/closing tags
	while ($token !== false) {
		$indent=0;
		// 1. open and closing tags on same line - no change
		if (preg_match('/.+<\/\w[^>]*>$/', $token, $matches))
			$indent=0;
		// 2. closing tag - outdent now
		elseif (preg_match('/^<\/\w/', $token, $matches))
			$pad -= $iSize;
		// 3. opening tag - don't pad this one, only subsequent tags
		elseif (preg_match('/^<\w[^>]*[^\/]>.*$/', $token, $matches))
			$indent = $iSize;
		// 4. no indentation needed
		else
			$indent = 0;

		// pad the line with the required number of leading spaces
		$line    = str_pad($token, strlen($token)+$pad, "\t", STR_PAD_LEFT);
		$result .= $line . "\n"; // add to the cumulative result, with linefeed
		$token   = strtok("\n"); // get the next token
		$pad    += $indent; // update the pad size for subsequent lines
	}
	return $result;
}

?>
<form action="<?= URL_ROOT ?>xmlformat" method="post">
<?= codeEditor($code, 'xmlcode', 'xml'); ?>
	<button class="btn btn-primary" type="submit" value="view">View result</button>
</form>
<code class="syntax"><?= htmlentities(formatXmlString($code), ENT_NOQUOTES, 'utf-8') ?>
</code>
