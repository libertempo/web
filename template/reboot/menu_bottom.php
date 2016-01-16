<?php
defined( '_PHP_CONGES' ) or die( 'Restricted access' );
$querys = \includes\SQL::getQuerys();
$total = 0;
?>
</div>
</div>
<footer>
<div id="bottom">
<?= BOTTOM_TEXT; ?>
</div>
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
                <?php foreach($querys as $num => $v):
                    $time = $v['t2'] - $v['t1'];
                    $total += $time;
                ?>
                    <tr>
                        <td><?= $num ?></td>
                        <td><?= $time ?></td>
                        <td><?= $total ?></td>
                        <td><?= $v['results'] ?></td>
                        <td><?= $v['back']['file'] ?></td>
                        <td><?= $v['back']['line'] ?></td>
                        <td><?= $v['query'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
</footer>
</section>
</section>
</section>
</body>
</html>
