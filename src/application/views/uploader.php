<div id="file-uploader">		
	<noscript>			
		<p>Please enable JavaScript to use file uploader.</p>
		<!-- or put a simple form for upload here -->
	</noscript>         
</div>

<script src="https://orbital-core/js/fileuploader.js" type="text/javascript"></script>
<script>        
    function createUploader(){            
        var uploader = new qq.FileUploader({
            element: document.getElementById('file-uploader'),
            action: 'https://orbital-core/fileuploader',
            sizeLimit: 524288000,
            debug: true,
            params: {
		        upload_token: 'ASdwYffMx1vWrLGuy6zRxL1jbR2ls8dkGW89kAi57TfAcWRTWE7JyscW3uDTKx2K',
		        return_uri: 'http://example.com',
		        licence: '1'
		    }
        });           
    }
    
    // in your app create uploader as soon as the DOM is ready
    // don't wait for the window to load  
    window.onload = createUploader;     
</script>