<?php ob_start(); ?>
<div class="container">
	<h2 class="text-center">List View</h2>
	<a class='btn btn-success' href="<?= '/'.$pluralname.'/new' ?>">New <?= $singularname ?></a>
	<table class="table table-bordered">
		<tr>
			<?php foreach($columns as $column): ?>
				<th><?= $column->name ?></th>
			<?php endforeach; ?>
		</tr>
		<?php foreach($list as $item): ?>
			<tr>
				<?php foreach($columns as $k=>$v): ?>
					<td><?= $item->$k ?></td>
				<?php endforeach; ?>
				<td><a href="<?= '/'.$pluralname.'/edit/'.$item->id; ?>">Edit</a></td>
				<td><a href="<?= '/'.$pluralname.'/destroy/'.$item->id; ?>">Delete</a></td>
			</tr>
		<?php endforeach; ?>
	</table>
</div>
<?php $html=ob_get_clean(); ?>