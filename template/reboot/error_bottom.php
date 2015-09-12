		</div>
		<footer>
		<?php if(SHOW_SQL): ?>
			<div id="show-sql">
				<table class="table table-striped table-hover">
					<thead>
						<tr>
							<th>#</th>
							<th>Time</th>
							<th>Total</th>
							<th>Results</th>
							<th>File</th>
							<th>Line</th>
							<th>Query</th>
						</tr>
					</thead>
					<tbody>
						<?php
							$querys = \includes\SQL::getQuerys();
							$total = 0;
							foreach($querys as $num => $v):
						?>
						<?php 
							$time = $v['t2'] - $v['t1'];
							$total += $time;
						?>
						<tr>
							<td><?php echo $num;?></td>
							<td><?php echo $time;?></td>
							<td><?php echo $total;?></td>
							<td><?php echo $v['results'];?></td>
							<td><?php echo $v['back']['file'];?></td>
							<td><?php echo $v['back']['line']?></td>
							<td><?php echo $v['query'];?></td>
						</tr>
						<?php 
							endforeach;
						?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
		</footer>
	<body>
</html>
