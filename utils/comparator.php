<?php $pgeName = 'phpinfo() comparator';
$fa = isset($_POST['urla']) ? $_POST['urla'] : NULL;
$fb = isset($_POST['urlb']) ? $_POST['urlb'] : NULL;
$verbose = false;
?>
<h1>Compare Two phpinfo() Files</h1>
<form action="#" method="post" enctype="multipart/form-data">
<table>
<tr>
	<td><input type="radio" name="sa" value="1" id="sa1"/><label for="sa1">File</label></td><td><input type="file" name="filea"/></td>
	<td><input type="radio" name="sb" value="1" id="sb1"/><label for="sb1">File</label></td><td><input type="file" name="fileb"/></td>
</tr>
<tr>
	<td><input type="radio" checked="checked" name="sa" value="0" id="sa0"/><label for="sa0">URL</label></td><td><input type="text" name="urla" value="<?=$fa?>"/></td>
	<td><input type="radio" checked="checked" name="sb" value="0" id="sb0"/><label for="sb0">URL</label></td><td><input type="text" name="urlb" value="<?=$fb?>"/></td>
</tr>
</table>
	<button class="btn btn-primary" type="submit">Compare</button>
</form><hr/>
<?php

/**
 * START CODE FROM:
 * http://www.holomind.de/phpnet/diff.src.php
 */
/**
	Diff implemented in pure php, written from scratch.
	Copyright (C) 2003  Daniel Unterberger <diff.phpnet@holomind.de>

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

	http://www.gnu.org/licenses/gpl.html

	About:
	I searched a function to compare arrays and the array_diff()
	was not specific enough. It ignores the order of the array-values.
	So I reimplemented the diff-function which is found on unix-systems
	but this you can use directly in your code and adopt for your needs.
	Simply adopt the formatline-function. with the third-parameter of arr_diff()
	you can hide matching lines. Hope someone has use for this.

	Contact: d.u.diff@holomind.de <daniel unterberger>
**/

function arr_diff( $f1 , $f2 , $show_equal = 0 ){
	$c1		 = 0 ;				   # current line of left
	$c2		 = 0 ;				   # current line of right
	$max1	   = count( $f1 ) ;		# maximal lines of left
	$max2	   = count( $f2 ) ;		# maximal lines of right
	$outcount   = 0;					# output counter
	$hit1	   = "" ;				  # hit in left
	$hit2	   = "" ;				  # hit in right
  $stop = 0;
  $out = '';
	while (
			$c1 < $max1				 # have next line in left
			and
			$c2 < $max2				 # have next line in right
			and
			($stop++) < 1000			# don-t have more then 1000 ( loop-stopper )
			and
			$outcount < 20			  # output count is less then 20
		  )
	{
		/**
		*   is the trimmed line of the current left and current right line
		*   the same ? then this is a hit (no difference)
		*/
		if ( trim( $f1[$c1] ) == trim ( $f2[$c2])  ){
			/**
			*   add to output-string, if "show_equal" is enabled
			*/
			$out	.= ($show_equal==1)
					 ?  formatline ( ($c1) , ($c2), "=", $f1[ $c1 ] )
					 : "" ;
			/**
			*   increase the out-putcounter, if "show_equal" is enabled
			*   this ist more for demonstration purpose
			*/
			if ( $show_equal == 1 )
			{
				$outcount++ ;
			}

			/**
			*   move the current-pointer in the left and right side
			*/
			$c1 ++;
			$c2 ++;
		}

		/**
		*   the current lines are different so we search in parallel
		*   on each side for the next matching pair, we walk on both
		*   sided at the same time comparing with the current-lines
		*   this should be most probable to find the next matching pair
		*   we only search in a distance of 10 lines, because then it
		*   is not the same function most of the time. other algos
		*   would be very complicated, to detect 'real' block movements.
		*/
		else {

			$b	  = "" ;
			$s1	 = 0  ;	  # search on left
			$s2	 = 0  ;	  # search on right
			$found  = 0  ;	  # flag, found a matching pair
			$b1	 = "" ;
			$b2	 = "" ;
			$fstop  = 0  ;	  # distance of maximum search

			#fast search in on both sides for next match.
			while (
					$found == 0			 # search until we find a pair
				&&( $c1 + $s1 <= $max1 )  # and we are inside of the left lines
				&&( $c2 + $s2 <= $max2 )  # and we are inside of the right lines
				&& $fstop++  < 10		  # and the distance is lower than 10 lines
				){

				/**
				*   test the left side for a hit
				*
				*   comparing current line with the searching line on the left
				*   b1 is a buffer, which collects the line which not match, to
				*   show the differences later, if one line hits, this buffer will
				*   be used, else it will be discarded later
				*/
				#hit
				if ( @trim( $f1[$c1+$s1] ) == @trim( $f2[$c2] )  ){
					$found  = 1   ;	 # set flag to stop further search
					$s2	 = 0   ;	 # reset right side search-pointer
					$c2--		 ;	 # move back the current right, so next loop hits
					$b	  = $b1 ;	 # set b=output (b)uffer
				}
				#no hit: move on
				else{
					/**
					*   prevent finding a line again, which would show wrong results
					*   add the current line to leftbuffer, if this will be the hit
					*/
					$off = ($c1 + $s1) . "_" . ($c2);
					if (!(isset($hit1[$off]) && ($hit1[$off] == 1 ))){
						/**
						*   add current search-line to diffence-buffer
						*/
						$b1  .= formatline( ($c1 + $s1) , ($c2), "-", $f1[ $c1+$s1 ] );

						/**
						*   mark this line as 'searched' to prevent doubles.
						*/
						$hit1[$off] = 1 ;
					}
				}

				/**
				*   test the right side for a hit
				*   comparing current line with the searching line on the right
				*/
				if ( @trim ( $f1[$c1] ) == @trim ( $f2[$c2+$s2])  )	{
					$found  = 1   ;	 # flag to stop search
					$s1	 = 0   ;	 # reset pointer for search
					$c1--		 ;	 # move current line back, so we hit next loop
					$b	  = $b2 ;	 # get the buffered difference
				}
				else{
					/**
					*   prevent to find line again
					*/
					$off = ($c1) . "_" . ( $c2 + $s2);
					if (!(isset($hit2[$off]) && ($hit2[$off] == 1 ))){
						/**
						*   add current searchline to buffer
						*/
						$b2 .= formatline ( ($c1) , ($c2 + $s2), "+", $f2[ $c2+$s2 ] );
						/**
						*   mark current line to prevent double-hits
						*/
						$hit2[ $off ] = 1;
					}

				 }

				/**
				*   search in bigger distance
				*
				*   increase the search-pointers (satelites) and try again
				*/
				$s1++ ;	 # increase left  search-pointer
				$s2++ ;	 # increase right search-pointer
			}

			/**
			*   add line as different on both arrays (no match found)
			*/
			if ( $found == 0 )
			{
				$b  .= formatline ( ($c1) , ($c2), "-", $f1[ $c1 ] );
				$b  .= formatline ( ($c1) , ($c2), "+", $f2[ $c2 ] );
			}

			/**
			*   add current buffer to outputstring
			*/
			$out		.= $b;
			$outcount++ ;	   #increase outcounter

			$c1++  ;	#move currentline forward
			$c2++  ;	#move currentline forward

			/**
			*   comment the lines are tested quite fast, because
			*   the current line always moves forward
			*/

		} /*endif*/

	}/*endwhile*/

	return $out;

}/*end func*/

/**
*   callback function to format the diffence-lines with your 'style'
*/
function formatline($nr1, $nr2, $stat, &$value)  #change to $value if problems
{
	if (trim($value) == '')
		return '';

	switch ($stat)
	{
		case '=':
			#return $nr1. " : $nr2 : = ".htmlentities( $value )  ."<br>";
			return '= <span style="color: green;">'.$value.'</span>'."\n";
		break;

		case '+':
			#return $nr1. " : $nr2 : + <font color='blue' >".htmlentities( $value )  ."</font><br>";
			return '&gt; <span style="color: blue;">'.$value.'</span>'."\n";
		break;

		case '-':
			#return $nr1. " : $nr2 : - <font color='red' >".htmlentities( $value )  ."</font><br>";
			return '&lt; <span style="color: red;">'.$value.'</span>'."\n";
		break;
	}
}
/**
 * END CODE FROM:
 * http://www.holomind.de/phpnet/diff.src.php
 */

// pull the content via curl, allow for basic authorization
function curl_website($url, $username = NULL, $password = NULL)
{
	if (preg_match('/^http:\/\//i',$url))
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);

		if(!is_null($username) && !is_null($password))
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic '.base64_encode($username.':'.$password)));
		}

		$data = curl_exec($ch);
		curl_close($ch);

		return $data;
	}
	else
	{
		return FALSE;
	}
}

/**
 * START CODE FROM:
 * http://ca3.php.net/manual/en/function.phpinfo.php#77692
 */
// parse phpinfo (in HTML format) into an array
function phpinfo_to_array($data)
{
	$a = $mtc = array();
	if (preg_match_all('/<tr><td class="e">(.*?)<\/td><td class="v">(.*?)<\/td>(:?<td class="v">(.*?)<\/td>)?<\/tr>/',$data,$mtc,PREG_SET_ORDER))
	{
		foreach($mtc as $v)
		{
			if ($v[2] == '<i>no value</i>')
				continue;
			$a[$v[1]] = $v[2];
		}
	}
	return $a;
}
/**
 * END CODE FROM:
 * http://ca3.php.net/manual/en/function.phpinfo.php#77692
 */

$sa = isset($_POST['sa']) ? $_POST['sa'] : 0;
$sb = isset($_POST['sb']) ? $_POST['sb'] : 0;
$da = $sa
	? ((isset($_FILES['filea']) && isset($_FILES['filea']['tmp_name'])) ? $_FILES['filea']['tmp_name'] : NULL)
	: (isset($_POST['urla']) ? $_POST['urla'] : NULL);
$db = $sb
	? ((isset($_FILES['fileb']) && isset($_FILES['fileb']['tmp_name'])) ? $_FILES['fileb']['tmp_name'] : NULL)
	: (isset($_POST['urlb']) ? $_POST['urlb'] : NULL);

echo "<hr/>$da<hr/>$db<hr/>";

if ($da xor $db)
	echo 'Only one source';
else if ($da && $db){
/*
	// compare two different phpinfos
	$site1 = 'http://example.com/phpinfo.php';
	$site2 = 'http://example.com/phpinfo-2.php';

	// if required, use curl_website($site1,username,password);
	$data1 = curl_website($site1);
	$data2 = curl_website($site2);
*/
	$data1 = $sa
		? file_get_contents($da)
		: curl_website($da);
	$data2 = $sb
		? file_get_contents($db)
		: curl_website($db);

	$file1 = $sa
		? '[FILE]: '.(isset($_FILES['filea']['name']) ? $_FILES['filea']['name'] : 'WTF ?!')
		: '[URL]: '.$da;
	$file2 = $sb
		? '[FILE]: '.(isset($_FILES['fileb']['name']) ? $_FILES['fileb']['name'] : 'WTF ?!')
		: '[URL]: '.$db;

	$array1 = phpinfo_to_array($data1);
	$array2 = phpinfo_to_array($data2);

	/*
	 * pull the content, turn it into an array and compare
	 */
	echo '<pre>';

	#echo '<h1>array1</h1>';
	#print_r($array1); // for debugging

	#echo '<h1>array2</h1>';
	#print_r($array2); // for debugging

	echo '<p>Comparison between <span style="color: red;">'.$file1.'</span> and <span style="color: blue;">'.$file2.'</span></p>';

	// side-by-side comparison
	echo '<h2>side-by-side comparison</h2>';
	foreach ($array1 as $key => $value)	{
		$value2 = isset($array2[$key]) ? $array2[$key] : NULL;
		echo '<span style="font-weight: bold;">'.$key.'</span>'."\n";
		if ($verbose)
			echo "array1: $value\narray2: $value2\n";
		if ($value != $value2)
		{
			echo '<span style="text-decoration: underline;">diff</span>'."\n";
			echo arr_diff(array($value), array($value2));
		}
		echo "<br />";
	}

	echo '</pre>';
}
