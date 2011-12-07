<!DOCTYPE html>
<html lang="en">
<head>

	<meta charset="utf-8">
	<title>Orbital Core</title>

	<link href='http://fonts.googleapis.com/css?family=Spinnaker' rel='stylesheet' type='text/css'>

	<style type="text/css">
	
		body {
			background: #000 url(<?php echo base_url(); ?>img/bg.png);
			color: #FFF;
			font-family: 'Spinnaker', sans-serif;
			text-align: center;
			font-size: 1.2em;
			margin-top: 100px;
		}
		
		a {
			color: #F5D89A;
		}
	
	</style>
	
</head>
<body>

	<h1><img src="<?php echo base_url(); ?>img/logo.png" title="Orbital" alt="Orbital Logo"></h1>
	
	<p>This is Orbital Core version <?php echo $this->config->item('orbital_core_version'); ?> at <?php echo $this->config->item('orbital_institution_name'); ?>.</p>
	
	<p>Want more? Hit up the API's 'ping' command by firing a HTTP GET to <a href="<?php echo site_url('core/ping'); ?>"><?php echo site_url('core/ping'); ?></a></p>
	
	<p><a href="https://github.com/lncd/Orbital-Core">Orbital Core on Github</a></p>
	<p><a href="http://orbital.blogs.lincoln.ac.uk/">Orbital Blog</a></p>

</body>
</html>