<!DOCTYPE html>
<html lang="en">
<head>
	<link rel="icon" href="/assets/img/Logo.svg">
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Document</title>

	<link rel="stylesheet" type="text/css" href="/assets/css/application.css">
	<script
	src="http://code.jquery.com/jquery-2.2.4.min.js"
	integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44="
	crossorigin="anonymous"></script>
</head>
<body>
	<?php $msg = new \Plasticbrain\FlashMessages\FlashMessages(); ?>
	<?php $msg->display(); ?>
	<!-- this is the yield -->
	<?= $str ?>
	<!-- this is the yield -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

</body>
</html>