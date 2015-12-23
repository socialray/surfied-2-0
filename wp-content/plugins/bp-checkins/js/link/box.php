<?php
$admin = dirname( __FILE__ ) ;
$admin = substr( $admin , 0 , strpos( $admin , "wp-content" ) ) ;
require_once( $admin . 'wp-admin/admin.php' ) ;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title><?php _e('Add link', 'bp-checkins');?></title>
	<meta name="generator" content="TextMate http://macromates.com/">
	<meta name="author" content="imath">
	<!-- Date: 2012-02-17 -->
	<?php wp_print_scripts('jquery')?>
	<script type="text/javascript" src="<?php echo get_option( 'siteurl' ) ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
</head>
<body>
<p>
	<label><?php _e('Url of your link', 'bp-checkins');?></label>
	<input type="text" name="link" id="link" style="width:98%" value="http://">
</p>
<p>
	<label><?php _e('Caption of your link (selected text by default)', 'bp-checkins');?></label>
	<input type="text" name="link_alias" id="link_alias" style="width:98%">
</p>
<p>
	<label><?php _e('Tooltip (or title) of your link (selected text by default)', 'bp-checkins');?></label>
	<input type="text" name="link_title" id="link_title" style="width:98%">
</p>
<p>
	<input type="button" value="<?php _e('Add link', 'bp-checkins');?>" id="addLink" class="button">
	<input type="button" value="<?php _e('Cancel', 'bp-checkins');?>" id="cancelLink" class="button">
</p>
	
	<script type="text/javascript">
	
	jQuery(document).ready(function($){
		
		$("#link_alias").val(tinyMCEPopup.editor.selection.getContent());
		$("#link_title").val(tinyMCEPopup.editor.selection.getContent());
		$("#link").focus();
		
		$("#cancelLink").click(function(){ tinyMCEPopup.close(); });
		
		$("#addLink").click(function(){
			
			var testlink = $("#link").val();
			var testalias = $("#link_alias").val();
			var testttitle = $("#link_title").val();
			
			if( testlink.substring(0,7) != "http://") {
				alert("<?php _e('You must add a well formed url beginning par http://', 'bp-checkins');?>");
				$("#link").focus();
				return false;
			}
			
			if( testlink.substring(7, testlink.length).indexOf("http://")!=-1 ){
				alert("<?php _e('You must add a well formed url with only one reference to http://', 'bp-checkins');?>");
				$("#link").focus();
				return false;
			}
			
			if(testlink.length <= 7){
				alert("<?php _e('Please add an url.', 'bp-checkins');?>");
				$("#link").focus();
				return false;
			}
			
			if(!testttitle)
				testttitle = tinyMCEPopup.editor.selection.getContent();
				
			if(!testalias)
				testalias = tinyMCEPopup.editor.selection.getContent();
			
			tinyMCEPopup.editor.execCommand('mceInsertContent', false, '<a href="'+$('#link').val()+'" title="'+testttitle+'" target="_blank" rel="nofollow">'+testalias+'</a>' ) ;
			tinyMCEPopup.close();
		});
	});
	</script>
</body>
</html>
