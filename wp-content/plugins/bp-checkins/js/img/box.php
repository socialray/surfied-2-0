<?php
$admin = dirname( __FILE__ ) ;
$admin = substr( $admin , 0 , strpos( $admin , "wp-content" ) ) ;
require_once( $admin . 'wp-admin/admin.php' ) ;

wp_enqueue_style( 'global' );
wp_enqueue_style( 'wp-admin' );
wp_enqueue_style( 'colors' );
wp_enqueue_style( 'media' );

$max_upload_size =  wp_max_upload_size();

$max_fileup_option = intval(get_option('bp-checkins-max-upload-size')) * 1024 * 1024;

$upload_size_unit = empty( $max_fileup_option ) ? $max_upload_size : $max_fileup_option;
$max_upload_image = $upload_size_unit;

$sizes = array( 'KB', 'MB', 'GB' );
for ( $u = -1; $upload_size_unit > 1024 && $u < count( $sizes ) - 1; $u++ )
	$upload_size_unit /= 1024;
if ( $u < 0 ) {
	$upload_size_unit = 0;
	$u = 0;
} else {
	$upload_size_unit = (int) $upload_size_unit;
}
$max_width_option = intval(get_option('bp-checkins-max-width-image'));
$max_width = empty( $max_width_option ) ? 300 : $max_width_option;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title><?php _e('Add a photo', 'bp-checkins');?></title>
	<meta name="generator" content="TextMate http://macromates.com/">
	<meta name="author" content="imath">
	<!-- Date: 2012-02-17 -->
	<style>
		table.bp_checkins_image_editor{
			padding:5px;
			margin-bottom:10px;
			border:solid 1px #E2E2E2;
			width:100%;
		}
		table.bp_checkins_image_editor td{
			text-align:center;
		}
		table.bp-checkins-media-container{
			width:100%;
		}
		table.bp-checkins-media-container label{
			font-weight:bold;
		}
	</style>
	<?php do_action('admin_print_styles');?>
	<?php wp_print_scripts('jquery')?>
	<script type="text/javascript" src="<?php echo get_option( 'siteurl' ) ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
</head>
<body style="min-width:90%!important;width:90%;height:90%">
	<?php
	if(!empty( $_POST['bpci-img-send'] ) && $_FILES["bpci_upload_image"]["name"] && preg_match('!^image/!', $_FILES["bpci_upload_image"]["type"]) && $_FILES["bpci_upload_image"]["size"] <= $max_upload_image ){
		  // check to make sure its a successful upload
		  if ($_FILES['bpci_upload_image']['error'] !== UPLOAD_ERR_OK) __return_false();
		  

		  require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		  require_once(ABSPATH . "wp-admin" . '/includes/file.php');
		  require_once(ABSPATH . "wp-admin" . '/includes/media.php');

		  $attach_id = media_handle_upload( 'bpci_upload_image', 0 );
		
		
		$upload_dir = wp_upload_dir();
		$img_data = wp_get_attachment_metadata( $attach_id );
		$img_dir_array = explode('/', $img_data["file"]);
		$img_dir = $upload_dir['baseurl'] .'/'. $img_dir_array[0] .'/'. $img_dir_array[1];

		//if too small
		if(!$img_data["sizes"]["thumbnail"]["file"]) $img_preview = $upload_dir['baseurl'] .'/'. $img_data["file"];
		else{
			$img_preview =   $img_dir .'/'. $img_data["sizes"]["thumbnail"]["file"] ;
		}
		?>
		<div style="margin:10px;">
			<div id="message" class="updated"><p><?php _e('Your image was successfully uploaded', 'bp-checkins');?></p></div>
			<div class="wrap">
			<div class='media-item'>
				<input type="hidden" id="img-full" value="<?php echo $upload_dir['baseurl'] .'/'. $img_data["file"];?>">
				<table class="bp-checkins-media-container">
					<thead class='media-item-info'>
						<tr valign='top'>
							<td class='A1B1'><p style="text-align:center"><img src="<?php echo $img_preview;?>" id="bpci-preview"></p></td>
						</tr>
					</thead>
					<tbody>
						<tr><td><p><label for="img_alt"><?php _e('Alternative text', 'bp-checkins');?></label> <input type="text" name="alt_text" id="alt_text" style="width:60%"><input type="hidden" name="img_title" id="img_title" value="<?php echo $img_dir_array[2];?>"></p></td></tr>
						<tr><td>
							<table class="bp_checkins_image_editor">
								<tr><th colspan="4"><?php _e('Image alignment', 'bp-checkins');?></th></tr>
								<tr>
									<td>
										<input type="radio" name="img_align" value="alignnone" checked><?php _e('None', 'bp-checkins');?>
									</td>
									<td>
										<input type="radio" name="img_align" value="alignleft"><?php _e('Left', 'bp-checkins');?>
									</td>
									<td>
										<input type="radio" name="img_align" value="alignright"><?php _e('Right', 'bp-checkins');?>
									</td>
									<td>
										<input type="radio" name="img_align" value="aligncenter"><?php _e('Center', 'bp-checkins');?>
									</td>
								</tr>
							</table>
						</td></tr>
						<tr><td>
							<table class="bp_checkins_image_editor">
								<tr><th colspan="3"><?php _e('Image Size', 'bp-checkins');?></th></tr>
								<tr>
									<?php if( !empty( $img_data["sizes"]["thumbnail"]["file"] ) ):?>
										<td>
											<input type="radio" name="img_size" value="<?php echo $img_dir .'/'. $img_data["sizes"]["thumbnail"]["file"];?>" class="size-thumbnail" checked><?php _e('Thumbnail', 'bp-checkins');?><br/>(<?php echo $img_data["sizes"]["thumbnail"]["width"];?>x<?php echo $img_data["sizes"]["thumbnail"]["height"];?>)
										</td>
									<?php endif;?>
									<?php if( !empty(  $img_data["sizes"]["medium"]["file"] ) ):?>
										<td>
											<input type="radio" name="img_size" value="<?php echo $img_dir .'/'. $img_data["sizes"]["medium"]["file"];?>" class="size-medium"><?php _e('Medium', 'bp-checkins');?><br/>(<?php echo $img_data["sizes"]["medium"]["width"];?>x<?php echo $img_data["sizes"]["medium"]["height"];?>)
										</td>
									<?php endif;?>
									<?php if( !empty( $img_data["sizes"]["large"]["file"] ) ):?>
										<td>
											<input type="radio" name="img_size" value="<?php echo $upload_dir['baseurl'] .'/'. $img_data["sizes"]["large"]["file"];?>" class="size-large"><?php _e('Large size', 'bp-checkins');?><br/>(<?php echo $img_data["sizes"]["large"]["width"];?>x<?php echo $img_data["sizes"]["large"]["height"];?>)
										</td>
									<?php else:?>
										<td>
											<input type="radio" name="img_size" value="<?php echo $upload_dir['baseurl'] .'/'. $img_data["file"];?>" class="size-full" <?php if(!$img_data["sizes"]["thumbnail"]["file"]) echo "checked";?>><?php _e('Full size', 'bp-checkins');?><br/>(<?php echo $img_data["width"];?>x<?php echo $img_data["height"];?>)
										</td>
									<?php endif;?>
								</tr>
							</table>
						</td></tr>
						<tr>
							<td><input type="button" value="<?php _e('Insert Image', 'bp-checkins');?>" class="bpci_insert_btn button-secondary" rel="<?php echo $attach_id;?>">
							&nbsp;<span id="set_as_featured"><a href="#" class="add_featured_btn button-secondary" rel="<?php echo $attach_id;?>"><?php _e('Use as featured', 'bp-checkins');?></a></span><span id="remove_as_featured" style="display:none"><a href="#" class="remove_featured_btn button-secondary"><?php _e('Remove featured', 'bp-checkins');?></a>&nbsp;<a href="#" class="button-secondary" id="bp_checkins_close_button"><?php _e('Close', 'bp-checkins');?></a></span>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			</div>
		<?php
	} else {
		?>
		<div>
		<div id="photo-upload">
			<?php if($_POST):?>
				<div id="message" class="error"><p><?php printf(__('Something went wrong with your upload, only images under %d%s can be uploaded..', 'bp-checkins'), $upload_size_unit, $sizes[$u]);?></p></div>
			<?php endif;?>
			<form enctype="multipart/form-data" method="post" action="">
				<?php if( 1 == bp_get_option( 'bp-checkins-enable-place-uploads' )):?>
				<p style="text-align:center">
				<input type="file" name="bpci_upload_image" id="bpci_upload_image">
				&nbsp;<input type="submit" name="bpci-img-send" value="<?php _e('Upload your photo','bp-checkins');?>" class="button-secondary">
				</p>
				<p class="media-upload-size" style="text-align:center"><small><?php printf( __( 'Maximum upload file size: %d%s' ), $upload_size_unit, $sizes[$u] ); ?></small></p>
				<p style="text-align:center"><?php _e('Or','bp-checkins');?>
				&nbsp;<input type="button" value="<?php _e('Add external url','bp-checkins');?>" class="tp_add_url button-secondary">
				</p>
				<?php endif;?>
			</form>
		</div>
		<div id="photo-url" <?php if( 1 == bp_get_option( 'bp-checkins-enable-place-uploads' )):?>style="display:none"<?php endif;?>>
			<p>
				<label><?php _e('Url to your image', 'bp-checkins');?></label>
				<input type="text" name="imageLink" id="imageLink" style="width:98%" value="http://">
			</p>
			<div id="hidepreview" style="display:none">
				<img src="<?php echo BP_CHECKINS_PLUGIN_URL_IMG;?>/ed-bg.gif" id="originalimage">
			</div>
			<div id="showpreview"></div>
			<p>
				<label><?php _e('Text replacement for your picture', 'bp-checkins');?></label>
				<input type="text" name="imageAlt" id="imageAlt" style="width:98%">
			</p>
			<div id="info">
				<label><?php _e('Image original height', 'bp-checkins');?></label> <input type="text" id="ht"><br/>
				<label><?php _e('Image original width', 'bp-checkins');?></label> <input type="text" id="wt">
			</div>
			<p>
				<label><?php _e('Width in pixels for your picture (only numbers)', 'bp-checkins');?></label>
				<input type="text" name="imageWidth" id="imageWidth" style="width:50%" value="<?php echo $max_width;?>">
			</p>
			<table style="width:98%">
				<tr><td>
					<table class="bp_checkins_image_editor">
						<tr><th colspan="4"><?php _e('Image alignment', 'bp-checkins');?></th></tr>
						<tr>
							<td>
								<input type="radio" name="img_align" value="alignnone" checked><?php _e('None', 'bp-checkins');?>
							</td>
							<td>
								<input type="radio" name="img_align" value="alignleft"><?php _e('Left', 'bp-checkins');?>
							</td>
							<td>
								<input type="radio" name="img_align" value="alignright"><?php _e('Right', 'bp-checkins');?>
							</td>
							<td>
								<input type="radio" name="img_align" value="aligncenter"><?php _e('Center', 'bp-checkins');?>
							</td>
						</tr>
					</table>
				</td></tr>
			</table>
			<p>
				<input type="button" value="<?php _e('Add Picture', 'bp-checkins');?>" id="addImage" class="button">
				<input type="button" value="<?php _e('Cancel', 'bp-checkins');?>" id="cancelImage" class="button">
			</p>
		</div>
		</div>
		<?php
	}
	
	?>
	
	<script type="text/javascript">
	
	jQuery(document).ready(function($){
		
		$('.tp_add_url').click(function(){
			$('#photo-url').show();
			$('#photo-upload').hide();
			//$("#imageLink").focus();
		});
		
		$('.bpci_insert_btn').click(function(){
			if($("#bp_checkins_attachment_ids", top.document).val().indexOf($(this).attr("rel")+',')==-1){
				$("#bp_checkins_attachment_ids", top.document).val($("#bp_checkins_attachment_ids", top.document).val()+$(this).attr("rel")+',');
			}
			
			/*var win = window.dialogArguments || opener || parent || top;*/
			if("aligncenter" == $("input[name=img_align]:checked").val()){
				img_post = '<p style="text-align: center;"><a href="'+$("#img-full").val()+'" title="'+$("#img_title").val()+'"><img src="'+$("input[name=img_size]:checked").val()+'" class="'+$("input[name=img_size]:checked").attr('class')+' '+$("input[name=img_align]:checked").val()+'" alt="'+$("#alt_text").val()+'"></a></p>';
			}
			else img_post = '<a href="'+$("#img-full").val()+'" title="'+$("#img_title").val()+'"><img src="'+$("input[name=img_size]:checked").val()+'" class="'+$("input[name=img_size]:checked").attr('class')+' '+$("input[name=img_align]:checked").val()+'" alt="'+$("#alt_text").val()+'"></a>';
			
			if(img_post){
				tinyMCEPopup.editor.execCommand('mceInsertContent', false, img_post ) ;
			}	
			
			tinyMCEPopup.close();
			return false;

		});
		
		$(".add_featured_btn").click(function(){
			$("#bp_checkins_featured_image_id", top.document).val($(this).attr("rel"));
			$("#bp_checkins_attachment_ids", top.document).val($("#bp_checkins_attachment_ids", top.document).val()+$(this).attr("rel")+',');
			$("#bp_checkins_featured_image", top.document).show();
			$("#bp_checkins_featured_image", top.document).prepend('<img src="'+$('#bpci-preview').attr("src")+'" height="150px" width="150px" class="alignleft">');
			$("#set_as_featured").hide();
			$("#remove_as_featured").show();
			return false;
		});
		$(".remove_featured_btn").click(function(){
			$("#bp_checkins_featured_image_id", top.document).val('');
			$("#bp_checkins_featured_image img", top.document).remove();
			$("#bp_checkins_featured_image", top.document).hide();
			$("#set_as_featured").show();
			$("#remove_as_featured").hide();
			return false;
		});
		
		$("#imageLink").blur(function(){
			$("#showpreview").html('<img src="'+$(this).val()+'" width="150px" id="imageprev">');
			$("#originalimage").attr('src', $(this).val());
		})
		$('#originalimage').bind('load readystatechange', function(e) {

			if(this.src == "<?php echo BP_CHECKINS_PLUGIN_URL_IMG;?>/ed-bg.gif")
				return false;

			if (this.complete || (this.readyState == 'complete' && e.type == 'readystatechange')) {
				$("#ht").val(this.height);
				$('#wt').val(this.width);

				if(this.height > this.width){
					$('#showpreview img').attr('height','150px');
					ratio = 150 / this.height;
					$('#showpreview img').attr('width', ratio * this.width +'px');
				}
			}
		});
		
		$("#cancelImage").click(function(){ tinyMCEPopup.close(); });
		$("#bp_checkins_close_button").click(function(){
			tinyMCEPopup.close();
			return false;
		});
		
		$("#addImage").click(function(){
			
			var testimage = $("#imageLink").val();
			var testalt = $("#imageAlt").val();
			var testwidth = $("#imageWidth").val();
			
			if( testimage.substring(0,7) != "http://") {
				alert("<?php _e('You must add a well formed url to your picture beginning by http://', 'bp-checkins');?>");
				$("#imageLink").focus();
				return false;
			}
			
			if( testimage.substring(7, testimage.length).indexOf("http://")!=-1 ){
				alert("<?php _e('You must add a well formed url to your picture with only one reference to http://', 'bp-checkins');?>");
				$("#imageLink").focus();
				return false;
			}
			
			if(testimage.length <= 7){
				alert("<?php _e('Please add the url of your picture', 'bp-checkins');?>");
				$("#imageLink").focus();
				return false;
			}
			
			if(!testalt)
				testalt = 'image';
				
			if(!testwidth || testwidth > <?php echo $max_width;?> )
				testwidth = <?php echo $max_width;?>;
			
			tinyMCEPopup.editor.execCommand('mceInsertContent', false, '<img src="'+testimage+'" width="'+testwidth+'px" class="'+$("input[name=img_align]:checked").val()+'" alt="'+testalt+'">' ) ;
			
			tinyMCEPopup.close();
		});
	});
	
	</script>
</body>
</html>
