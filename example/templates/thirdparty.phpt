<?php
	$this->layout ('index.phpt');
	$this->textdomain ('example');
?>

<h2>External accounts</h2>

<table class="table">
<?php foreach ($accounts as $v) { ?>

	<tr>
		<td><?php echo $v->getId (); ?></td>
		<td><?php echo $v->getType (); ?></td>
		<td><?php echo $v->getName (); ?></td>
	</tr>

<?php } ?>
</table>