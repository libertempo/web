<?php
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
?>	
		</center>
	</body>
</html>