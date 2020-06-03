<?php ob_start() ?>
<div class="container">
	<ul class='list-group'>
		<?php foreach($columns as $column): ?>
			<?php $cn=$column->name ?>
			<li class='list-group-item'><?= $column->name.' : '.$object->$cn ?></li>
		<?php endforeach; ?>
	</ul>
	<div class="buttons">
		<a class='btn btn-primary' href="/<?= $pluralname ?>/">Back to List</a>
		<a class='btn btn-info' href="/<?= $pluralname ?>/edit/<?= $object->id ?>">Edit</a>
		<a class='btn btn-danger' href="/<?= $pluralname ?>/destroy/<?= $object->id ?>">Delete</a>
	</div>
</div>
<?php $html=ob_get_clean() ?>