<html>
<head>
	<title>Orbital File Uploader</title>
	<link href="<?php echo base_url(); ?>css/bootstrap.min.css" rel="stylesheet">
	<link href="<?php echo base_url(); ?>css/fileuploader.css" rel="stylesheet">
	<script src="<?php echo base_url(); ?>js/jquery.min.js" type="text/javascript"></script>
	<script src="<?php echo base_url(); ?>js/fileuploader.js" type="text/javascript"></script>
</head>


<?php

$form_public = array(
	'name'	=> 'public',
	'id'	=> 'public',
	'value'	=> 'public',
);

$licences = $this->licences_model->list_all_available();

foreach ($licences as $licence)
{
	$file_licences[$licence['id']] = $licence['name'];
}

if (isset($file_licences) AND count($file_licences) > 0)
{
	echo form_label('Licence to release these files under (if public)', 'licence');
	echo form_label('Make these files publicly available?', 'public');
	echo form_checkbox($form_public);
	echo form_dropdown('licence', $file_licences, $default_licence, 'id="licence"');


	echo '<div class="well">
	
		<div id="licenceAllow" style="display:none">
			<h4>This licence allows:</h4>
			<div id="licenceAllowContent">
			</div>
		</div>
		
		<div id="licenceDeny" style="display:none">
			<h4>This licence forbids:</h4>
			<div id="licenceDenyContent">
			</div>
		</div>
		
		<div id="licenceConditions" style="display:none">
			<h4>This licence has the following conditions:</h4>
			<div id="licenceConditionsContent">
			</div>
		</div>
		
		<h4>Read More</h4>
			<p>More information about this licence, including legal text, is available at:<br>
			<span id="licenceInfoURL">Unknown Location</span></p>
		</div>
	Select as many files as you want to upload with these details, then hit the upload button<br><br>
	
	<div id="file-uploader">		
		<noscript>			
			<p>Your browser does not have JavaScript enabled. Please enable JavaScript to use the Archive Files uploader.</p>
			<!-- or put a simple form for upload here -->
		</noscript>         
	</div>';
}
else
{

	echo '<div class="well">
	

		<h4>No Licences Available</h4>
			<p>Unfortunately, as there are no enabled licences, file uploading is disabled.</p>
		</div>';

}

?>

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