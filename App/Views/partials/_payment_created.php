
<!DOCTYPE html>
<html lang="en">
<head> 
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<style>
		*{
			color:black;
		}
		table{
			border-collapse:collapse;
      width:70%;
      margin-left:auto;
      margin-right:auto;
		}
		td,th{
			border:black 1px solid;
      		padding:5px;
      text-align:center;
		}
		th{
			color:white;
			background-color: #0747a6;
      
		}
	</style>
</head>
<body>
		<table>
		<thead>
			<tr>
				<th style="background-color:black" colspan="5">NOTIFICACION DE COBRANZA </th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<th>Aseguradora</th>
				<th>Fecha</th>
				<th>Cliente</th>
				<th>Poliza</th>
				<th>Cobrado Por </th>
			</tr>
			<tr>
				<td><?= $local->policy->plan->company->name ?> </td>
				<td><?= $local->payment_date->format('d/m/Y') ?> </td>
				<td><?= $local->policy->client->first_name ?> </td>
				<td><?= $local->policy->policy_number ?> </td> 
				<td> <?= $local->user->name ?></td>
			</tr>
			<tr>
				<th colspan="5">DESCUENTOS</th>

			</tr>
			<tr>
				<th colspan="2">Aseguradora</th>
				<th colspan="2">Agencia</th> 
				<th>Agente</th>
			</tr>
			<tr>
				<td colspan="2"> <?= $local->company_discount.' '.$local->currency?></tdh>
				<td colspan="2"><?= $local->agency_discount.' '.$local->currency?></td> 
				<td><?= $local->agent_discount.' '.$local->currency?></td>
			</tr>
			<tr>
				<th colspan="5"> DETALLE DEL PAGO </th>
			</tr>
			<tr>
				<th colspan="2">Metodo</th>
				<th colspan="2">Monto</th>
				<th>Tipo de Pago</th>
			</tr>
			<tr>
				<td colspan="2"><?= $local->payment_method ?></td>
				<td colspan="2"><?= $local->amount ?></td>
				<td><?= $local->payment_type ?></td>
			</tr>
			<tr>
				<th colspan="5">COMENTARIOS ADICIONALES</th>
			</tr>
			<tr>
				<td colspan="5">
					<?php if (!$local->comment): ?>
							No hay comentarios
					<?php else: ?>
							<?= $local->comment; ?>

					<?php endif; ?>
				</td>
			</tr>
		</tbody>
	</table>

</body>
</html>
