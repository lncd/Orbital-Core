<html>
<head>
	<title>Orbital File Uploader</title>
	<link href="<?php echo base_url(); ?>css/bootstrap.min.css" rel="stylesheet">
	<link href="<?php echo base_url(); ?>css/fileuploader.css" rel="stylesheet">
	<script src="<?php echo base_url(); ?>js/jquery.min.js" type="text/javascript"></script>
	<script src="<?php echo base_url(); ?>js/fileuploader.js" type="text/javascript"></script>
</head>


<?php

if ($this->input->get('public') === 'checked')
{
	echo form_hidden('public', 'public');
}
echo form_hidden('licence', $this->input->get('licence'));

$form_public = array(
	'name'	=> 'public',
	'id'	=> 'public',
	'value'	=> 'public',
);
?>

<div id="file-uploader">		
	<noscript>			
		<p>Your browser does not have JavaScript enabled. Please enable JavaScript to use the Archive Files uploader.</p>
		<!-- or put a simple form for upload here -->
	</noscript>         
</div>





<script>        
    function createUploader(){            
        var uploader = new qq.FileUploader({
            element: document.getElementById('file-uploader'),
            action: '<?php echo site_url('fileupload'); ?>',
            sizeLimit: 314572800,
            debug: true,
			onSubmit: function(id, fileName){
		    	uploader.setParams({
		    		token: '<?php echo $token; ?>',
					licence: $('#licence').val(),
					public: $('#public').val()
				});
		    },
		    maxConnections: 2
        });           
    }
    
    // in your app create uploader as soon as the DOM is ready
    // don't wait for the window to load  
    window.onload = createUploader;
    
    $.getJSON('<?php echo base_url(); ?>licences/licence_json/' + $('#licence').val(), function(data) {

		if (data.allow !== null)
		{
			$('#licenceAllowContent').html(data.allow);
			$('#licenceAllow').show();
		}
		
		if (data.conditions !== null)
		{
			$('#licenceConditionsContent').html(data.conditions);
			$('#licenceConditions').show();
		}
		
		if (data.forbid !== null)
		{
			$('#licenceDenyContent').html(data.forbid);
			$('#licenceDeny').show();
		}
		
		$('#licenceInfoURL').html('<a href="' + data.summary_uri + '" target="_blank">' + data.summary_uri + '</a>');
		
	  
	});
	
	$('#licence').change(function(){
	
			
		$.getJSON('<?php echo base_url(); ?>licences/licence_json/' + $('#licence').val(), function(data) {
		  
			if (data.allow !== null)
			{
				$('#licenceAllowContent').html(data.allow);
				$('#licenceAllow').show();
			}
			else
			{
				$('#licenceAllow').hide();
			}
			
			if (data.conditions !== null)
			{
				$('#licenceConditionsContent').html(data.conditions);
				$('#licenceConditions').show();
			}
			else
			{
				$('#licenceConditions').hide();
			}
			
			if (data.forbid !== null)
			{
				$('#licenceDenyContent').html(data.forbid);
				$('#licenceDeny').show();
			}
			else
			{
				$('#licenceDeny').hide();
			}
			
			$('#licenceInfoURL').html('<a href="' + data.summary_uri + '" target="_blank">' + data.summary_uri + '</a>');
		  
		});
	  
	});
  
</script>

</html>