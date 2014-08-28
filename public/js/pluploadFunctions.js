$(function() {
	// Setup html5 version
	$("#html5_uploader").pluploadQueue({
		// General settings
		runtimes : 'html5',
		url : '/image/startupload',
		max_file_size : '10mb',
		chunk_size : '1mb',
		unique_names : true,
		filters : [
			{title : "Image files", extensions : "jpg,jpeg,gif,png"},
			{title : "Zip files", extensions : "zip"}
		],

		// Resize images on clientside if we can
		//resize : {width : 320, height : 240, quality : 90}
		resize : {width : 800, height : 600, quality : 100}
	});

});