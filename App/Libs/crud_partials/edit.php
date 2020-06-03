<?php ob_start() ?>
<div class="container">
	<form action="/<?=$pluralname ?>/<?=$object->id ?>" method='post'>
		<?php foreach($columns as $column): ?>
			<?php $cn=$column->name ?>
			<?php if($column->name!=$pk): ?>
				<div class="form-group">
					<label for=""><?= $column->name ?></label>
					<input 
					class='form-control input-sm' 
					type="<?= self::getInputType($column->type) ?>" 
					name="<?= $singularname ?>[<?=$column->name ?>]"
					value='<?= $object->$cn ?>'
					>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
		<div class="form-group">
			<button class='btn btn-success' type="submit">Save Changes</button>
			<a class='btn btn-info' href="/<?= $pluralname  ?>" >Back to List</a>
		</div>
	</form>
</div>
<?php $html=ob_get_clean() ?>