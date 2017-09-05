<pre><?php 
$pgeName = 'HTML template';
echo htmlentities(htmlHead(
isset($_POST['title'])	? $_POST['title']	: '#Title',
isset($_POST['kwords'])	? $_POST['kwords']: '#Keywords',
isset($_POST['desc'])		? $_POST['desc']	: '#Description').'
<?php
?>
'.htmlEnd());
?></pre>
<script type="application/javascript">
	$(function() {
		$(document).ready(function() {
			$('#module').find('pre').each(function(i, block) {
				hljs.highlightBlock(block);
			});
		});
	});
</script>
