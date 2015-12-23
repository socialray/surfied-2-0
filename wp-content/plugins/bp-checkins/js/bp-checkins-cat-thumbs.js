jQuery(document).ready(function($) {
 
$('#places_category_upload_image_button').click(function() {
 	formfield = $('#places_category_thumbnail').attr('name');
 	tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
 	return false;
});
 
window.send_to_editor = function(html) {
 	imgurl = $('img',html).attr('src');
	imgPostIdtoParse = $('img',html).attr('class');
	imgPostIdArray = imgPostIdtoParse.split('wp-image-');
	imgId = parseInt(imgPostIdArray[1]);
	
	if( $('#bpci_places_cat_img').length ) {
		$('#bpci_places_cat_img').attr('src', imgurl);
	}
	
	$('#places_category_thumbnail_id').val(imgId);
	
 	$('#places_category_thumbnail').val(imgurl);
 	tb_remove();
}
 
});