
<h1 style="text-align:center">Notificacion de cobranza</h1>

<p>DATOS:</p>
<ul>
	<li>Aseguradora: <?= $local->client->company->name ?> </li>
	<li>Fecha: <?= $local->payment_date->format('d/m/Y') ?> </li>
	<li>Cliente: <?= $local->client->first_name ?></li>
	<li>Poliza: <?= $local->client->policy_number ?></li>
	<li>Operador: <?= $local->user->name ?> </li>

</ul>
<p>DESCUENTOS</p>
<ul>
	<li>Aseguradora:  <?= $local->company_discount.' '.$local->currency?></li>
	<li>Agencia:  <?= $local->agency_discount.' '.$local->currency?></li>
	<li>Agente:  <?= $local->agent_discount.' '.$local->currency?></li>
</ul>
<p>DETALLE DE PAGO</p>

<ul>
	<li>Metodo de Pago: <?= $local->payment_method ?></li>
	<li>Monto Cancelado: <?= $local->amount ?></li>
	<li>Tipo de Pago:  <?= $local->payment_type ?> </li>
</ul>

<p>NOTAS ADICIONALES</p>
<p> <?= $local->comment ?></p>