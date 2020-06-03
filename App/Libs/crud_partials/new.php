<?php ob_start() ?>
<div class="container">
	<h2 class="text-center">New <?=$classname ?></h2>
	<form action="/<?=$pluralname ?>/<?=$object->id ?>" method='post'>
		<?php foreach($columns as $column): ?>
			<?php $cn=$column->name ?>
			<?php if($column->name!=$pk): ?>
				<div class="form-group">
					<label for=""><?= $column->name ?></label>
					<input 
					class='form-control input-sm' 
					type="text" 
					name="<?= $classname ?>[<?=$column->name ?>]"
					
					>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
		<div class="form-group">
			<button class='btn btn-success' type="submit">Create <?=$classname ?></button>
		</div>
	</form>
</div>
<?php $html=ob_get_clean() ?>