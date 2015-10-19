<?php

	defined( '_PHP_CONGES' ) or die( 'Restricted access' );
	
	/*************************************/
	/***  fin de la page             ***/
	
				
			echo '<p id="back-top">
				<a href="#top"><span></span>Back to Top</a>
			</p>';
			echo '</center>';
		echo '</div>';
				
		echo '<div id="bottom" class="ui-widget-header ui-helper-clearfix ui-corner-all">';
			echo BOTTOM_TEXT;
		echo '</div>';
		if ( SHOW_SQL ) {
			echo '<table class="tablo"><thead>';
				echo '<tr>
						<th>#</th>
						<th>Time</th>
						<th>Total</th>
						<th>Results</th>
						<th>File</th>
						<th>Line</th>
						<th>Query</th>
					</tr>';
			echo '</thead><tbody>';
			$querys = SQL::getQuerys();
			$total = 0;
			foreach($querys as $num => $v) {
				$time = $v['t2'] - $v['t1'];
				$total += $time;
				echo '<tr>
						<td>'.$num.'</td>
						<td>'.$time.'</td>
						<td>'.$total.'</td>
						<td>'.$v['results'].'</td>
						<td>'.$v['back']['file'].'</td>
						<td>'.$v['back']['line'].'</td>
						<td>'.$v['query'].'</td>
					</tr>';
			}
			echo '</tbody></table>';
		}
	echo '</body>';
echo '</html>';
