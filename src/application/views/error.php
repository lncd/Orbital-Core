<!DOCTYPE html>
<html lang="en">
<head>

	<meta charset="utf-8">
	<title>Orbital Core</title>

	<link href='https://fonts.googleapis.com/css?family=Spinnaker' rel='stylesheet' type='text/css'>

	<style type="text/css">
	
		body {
			background: #000 url(<?php echo base_url(); ?>img/bg.png);
			color: #FFF;
			font-family: 'Spinnaker', sans-serif;
			text-align: center;
			font-size: 1.2em;
			margin-top: 50px;
		}
		
		a {
			color: #F5D89A;
		}
	
	</style>
	
</head>
<body>

	<h1>System Error</h1>
	
	<p><?php echo $message; ?></p>
	
	<p><b>Don't panic!</b> you've not done anything wrong, but something has happened behind the scenes that we weren't expecting. Sorry about that.</p>
	
	<p>If you keep seeing this error, please contact <?php echo $this->config->item('orbital_contact_name'); ?> at <a href="mailto:<?php echo $this->config->item('orbital_contact_name'); ?>"><?php echo $this->config->item('orbital_contact_name'); ?></a>.</p>

</body>
</html>