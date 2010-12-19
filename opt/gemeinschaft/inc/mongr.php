<?php
/*
* Gemeinschaft Realtime Monitor Display Library
* Copyright 2010, AMOOMA GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
*/

function window_create($px, $py, $width, $heihght, $id, $opacity=false, $overflow='hidden',  $border=false)
{
	global $bg_color, $fg_color;
	
	echo "<div ";
	echo 'id="'.$id.'" ';
	echo 'style="';
	if ($bg_color) echo 'background:'.$bg_color.'; ';
	if ($fg_color) echo 'color:'.$fg_color.'; ';
	if ($border) {
       echo 'border-width:'.$border.'px; ';
       echo 'border-color:'.$fg_color.'; ';
       echo 'border-style:solid; ';
       }
       if ($opacity) {
	       echo 'background-image:url('.IMG_PATH.$opacity.'trans.png); ';
	       echo 'background-repeat:repeat; ';
       }
       echo 'overflow: '.$overflow.'; ';
       echo 'position:absolute; ';
       echo 'top: '.$py.'px; ';
       echo 'left: '.$px.'px; ';
       echo 'width: '.$width.'px; ';
       echo 'height: '.$heihght.'px;"';
       echo ">\n";
}

function show()
{
	echo "</div>\n";
}

function queue_window($px, $py, $width, $height, $id, $title='', $members, $cols=3, $rows=3, $bgcolor=gray, $fgcolor=white, $stats=False)
{
	global $bg_color, $fg_color;
	
	$bg_color = $bgcolor;
	$fg_color = $fgcolor;
	$offset_py = 22;
	window_create($px,$py, $width, $height, $id);
	echo '<table class="extmonhd">',"\n";
	echo '<tr>',"\n";
	echo '<th class="extmonhd">',"\n";
	echo '<span id="'.$id.'_title">',htmlentities($title),'</span>',"\n";
	echo '</th>',"\n";
	if ($stats)
		foreach ($stats as $stat) {
		echo '<td class="extmonhd">',"\n";
		echo $stat;
		echo '</td>',"\n";
		}
	echo '<td class="extmonhdcalls">',"\n";
	echo '<span id="'.$id.'_calls">?</span>',"\n";
	echo '</td>',"\n";
	echo '</tr>',"\n";
	echo '</table>',"\n";

	if ($rows == 0) {
		$member_count = count($members);
		$rows = ceil( $member_count / $cols);
		if ($rows == 0) $rows = 1;
	}

	$a_width = (int) floor(($width - 1 - $cols) / $cols);
	$a_height = (int) floor(($height - $offset_py - $rows) / $rows);
	$i = 0;
	$tablewidth = 100;
	if ($member_count < $cols) {
		$tablewidth = floor(100 * ($member_count / $cols));		
	}
	
	echo '<table class="extmon" style="width: ',$tablewidth,'%">',"\n";
	
	for ($row = 0; $row < $rows; $row++) {
		echo '<tr>',"\n";
		for ($col = 0; $col < $cols; $col++) {
			if ($i < $member_count) {
				$agent_id = $id.'_a'.$members[$i]['ext'];
				$cellwidth = floor(100 * (1 / $cols));
				echo '<td id="'.$agent_id.'" class="extmon" style="width: ',$cellwidth,'%">',"\n";
				echo $members[$i]['name'],"\n";
				echo '</td>',"\n";

			}else break;
			$i++;
		}
		echo '</tr>',"\n";
	}
	echo '</table>',"\n";
	
	show();

}

function group_window($px, $py, $width, $height, $id, $title='', $members, $cols=3, $rows=0, $bgcolor=gray, $fgcolor=white, $stats=False)
{
	global $bg_color, $fg_color;

	$bg_color = $bgcolor;
	$fg_color = $fgcolor;
	$offset_py = 22;
	window_create($px,$py, $width, $height, $id);
	echo '<table class="extmonhd">',"\n";
	echo '<tr>',"\n";
	echo '<th class="extmonhd">',"\n";
	echo '<span id="'.$id.'_title">',htmlentities($title),'</span>',"\n";
	echo '</th>',"\n";
	if ($stats)
		foreach ($stats as $stat) {
		echo '<td class="extmonhd">',"\n";
		echo $stat;
		echo '</td>',"\n";
		}
	echo '</td>',"\n";
	echo '</tr>',"\n";
	echo '</table>',"\n";
	$member_count = count($members);
	
	if ($member_count == 0) {
		show();
		return;
	}

	if ($rows == 0) $rows = ceil( $member_count / $cols);
	$a_width = (int) floor(($width - 1 - $cols) / $cols);
	$a_height = (int) floor(($height - $offset_py - $rows) / $rows);
	$i = 0;
	$tablewidth = 100;
	if ($member_count < $cols) {
		$tablewidth = floor(100 * ($member_count / $cols));
	}

	echo '<table class="extmon" style="width: ',$tablewidth,'%">',"\n";

	for ($row = 0; $row < $rows; $row++) {
		echo '<tr>',"\n";
		for ($col = 0; $col < $cols; $col++) {
			if ($i < $member_count) {
				$agent_id = $id.'_a'.$members[$i]['ext'];
				$cellwidth = floor(100 * (1 / $cols));
				echo '<td id="'.$agent_id.'" class="extmon" style="width: ',$cellwidth,'%">',"\n";
				echo $members[$i]['name'];
				echo '</td>',"\n";

			}else break;
			$i++;
		}
		echo '</tr>',"\n";
	}
	echo '</table>',"\n";

	show();

}


?>
