<html>
<head>
	<title>Orbital File Uploader</title>
	<link href="<?php echo base_url(); ?>css/fileuploader.css" rel="stylesheet">
</head>

<div id="file-uploader">		
	<noscript>			
		<p>Your browser does not have JavaScript enabled. Please enable JavaScript to use the Archive Files uploader.</p>
		<!-- or put a simple form for upload here -->
	</noscript>         
</div>

<script src="<?php echo base_url(); ?>js/fileuploader.js" type="text/javascript"></script>
<script>        
    function createUploader(){            
        var uploader = new qq.FileUploader({
            element: document.getElementById('file-uploader'),
            action: '<?php echo site_url('fileupload'); ?>',
            sizeLimit: 524288000,
            debug: true,
            params: {
		        token: '<?php echo $token; ?>',
		        licence: '<?php echo $licence; ?>',
		        public: 'public'
		    }
        });           
    }
    
    // in your app create uploader as soon as the DOM is ready
    // don't wait for the window to load  
    window.onload = createUploader;     
</script>

</html>