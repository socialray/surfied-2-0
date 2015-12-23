var displayed = 0;

jQuery(document).ready(function($){
	
	var polaContainer = '.comment-form-comment';
	
	if( $('.form-textarea').length ) {
		polaContainer = '.form-textarea';
	}
	
	$("#respond "+polaContainer).append('<a href="#" id="bpci-polaroid" title="'+bp_checkins_single_vars.addPolaTitle+'"><span>'+bp_checkins_single_vars.addPolaTitle+'</span></a>');
	
	if( !$('.form-textarea').length && $( polaContainer ).length ) {
		polaCOffset = $('textarea[name="comment"]').offset();
		polaOffset = $("#bpci-polaroid").offset();
		polaTop = ( polaCOffset.top - polaOffset.top ) + 10 ;
		polaMarginRight = Number( $(polaContainer).width() - Number( $('textarea[name="comment"]').width() + parseInt( $('textarea[name="comment"]').css('padding-right') ) ) ) ;

		$("#bpci-polaroid").css( 'top', polaTop+'px' );
		$("#bpci-polaroid").css( 'right', polaMarginRight+'px' );
	}
	
	
	if( !$('.add-checkin.without').hasClass('checkedin') )
		$('#respond .form-submit').prepend('<span id="cbox-checkins"><input type="checkbox" value="1" name="_checkin_comment" id="checkin_comment" checked> '+$('.add-checkin.without').html()+'</span>');
	
	if( $('.form-textarea').length ) {
		$('#respond .comment-content').hide();
		$('#respond .comment-avatar-box').hide();
	} else {
		$('#respond p.comment-form-comment').hide();
		$('#respond p.form-submit').hide();
	}
	
	$('.add-checkin.without').click(function(){
		
		if( $(this).hasClass('bpci-loading') || $(this).hasClass('checkedin') ) {
			alert(bp_checkins_single_vars.pleaseWait);
			return false;
		}
			
		
		$(this).addClass('bpci-loading');
		
		justPostCheckin( $('#bpci_place_lat').val(), $('#bpci_place_lng').val(), $('#bpci_place_address').val(), $('#bpci_place_id').val(), $('#bpci_place_name').val() );
		
		return false;
	});
	
	$("#bpci-polaroid").click(function(){
		
		if( $(this).hasClass('disabled') ) {
			
			$('#bpci_attached').remove();
			$('#bpci_comment_image_url').val('');
			$(this).removeClass('disabled');
		} else {
			
			if( !$('#bpci-pola-adder').length ) {
				$(this).addClass('disabled');
				//$(this).parent().append('<div id="bpci-pola-adder"></div>');
				$(this).parent().append( bpciUploadEnable() );
			} else {
				$(this).parent().find('#bpci-pola-adder').remove();
				$(this).removeClass('disabled');
			}
		}
		return false;
	});
	
	function bpciUploadEnable() {
		if( window.File && window.FileReader && bp_checkins_single_vars.uploadAuthorized == 1 ){
			return '<div id="bpci-pola-adder"><a href="#" id="bpci_use_upload">'+bp_checkins_single_vars.imageAddUpload+'</a> <a href="#" id="bpci_use_link">'+bp_checkins_single_vars.imageAddExternal+'</a></link>';
		}
		else return '<div id="bpci-pola-adder"><input type="text" placeholder="'+bp_checkins_single_vars.imageUrlPlaceholder+'"><a href="#" id="bpci-polaroid-add-link" title="'+bp_checkins_single_vars.addPolaLinkTitle+'"><span>'+bp_checkins_single_vars.addPolaLinkTitle+'</span></a></div>';
	}
	
	$('#bpci_use_upload').live('click', function(){
		
		if( $('#bpci-polaroid-upload').length && $('#bpci-polaroid-upload').hasClass('bpci-loading') )
			return false;
		
		else
			$('#bpci-pola-adder').html('<span id="form-upcheckin"><input type="file" name="_checkin_pic" id="checkin_pic"></span><a href="#" id="bpci-polaroid-upload" title="'+bp_checkins_single_vars.addPolaLinkTitle+'"><span>'+bp_checkins_single_vars.addPolaLinkTitle+'</span></a>');
		
		return false;
	});
	
	$('#bpci_use_link').live('click', function(){
		$('#bpci-pola-adder').html('<input type="text" placeholder="'+bp_checkins_single_vars.imageUrlPlaceholder+'"><a href="#" id="bpci-polaroid-add-link" title="'+bp_checkins_single_vars.addPolaLinkTitle+'"><span>'+bp_checkins_single_vars.addPolaLinkTitle+'</span></a>');
		return false;
	});
	
	$('#bpci-polaroid-upload').live('click', function(){
		
		var file = $('#checkin_pic').get(0).files[0];
		var max_file_size = bp_checkins_single_vars.maxUploadFileSize;//size in MO
		
		if( file.type.indexOf('image') == -1 ) {
			alert( bp_checkins_single_vars.imageErrorType );
			return false;
		}	
		
		name = file.name;
		size = file.size / 1024;
		type = file.type;
		
		sizeInMo = Math.round((size / 1024) * 100) / 100 ;
		
		if( sizeInMo > max_file_size ){
			alert( bp_checkins_single_vars.imageTooBig + max_file_size + 'MO)');
			return false;
		}
		
		var reader = new FileReader();
		
		$('#bpci-polaroid-upload').addClass('bpci-loading');
		
		reader.onload = (function(theFile) {
			return function(e) {
				bpciImageUpload(e.target.result, type, name);
				return false;
		    };
		})(file);
      
		reader.readAsDataURL(file);
		
		return false;
	});
	
	function bpciImageUpload( img, type, name ) {
		
		$.post( ajaxurl, {
			action: 'upload_checkin_pic',
			'type_upload':'comment',
			'cookie': encodeURIComponent(document.cookie),
			'_wpnonce_place_post_checkin': $("input#_wpnonce_place_post_checkin").val(),
			'encodedimg': img,
			'imgtype':type,
			'imgname':name
		},
		function(response) {
			
			if( response[0] != "0" ) {
				addToComment( response[1], response[2]);
			}
			else alert(response[1]);
		}, 'json');
				
	}
	
	$('#bpci-polaroid-add-link').live('click', function(){
		if( $("#bpci-pola-adder input").val().length < 3  || !isImageLink( $("#bpci-pola-adder input").val() ) ){
			alert( bp_checkins_single_vars.imageErrorNoUrl );
			return false;
		}	
		
		addToComment( $('#bpci-pola-adder input').val() );
		return false;
	});
	
	function isImageLink( image ){
		var isimage = false;
		
		if( image.substring(image.length -4).indexOf('.jpg') != -1 )
			isimage = true;
		
		else if( image.substring(image.length -4).indexOf('jpeg') != -1 )
			isimage = true;
		
		else if( image.substring(image.length -4).indexOf('.png') != -1 )
			isimage = true;
		
		else if( image.substring(image.length -4).indexOf('.gif') != -1 )
			isimage = true;
			
		return isimage;
	}
	
	function addToComment( full, thumb) {
		if(!thumb){
			$('#bpci_comment_image_url').val(full);
			thumb=full;
		}
		else 
			$('#bpci_comment_image_url').val(full+'|'+thumb);
			
		$('p.form-submit').prepend('<a href="'+full+'" id="bpci_attached" target="_blank"><img src="'+thumb+'" width="40px" height="40px" class="thumbnail alignleft"></a>');
		$("#bpci-pola-adder").remove();
	}
	
	$('.add-checkin.with').click(function(){
		if( $('.form-textarea').length ) {
			$('#respond .comment-content').slideToggle();
			$('#respond .comment-avatar-box').slideToggle();
		} else {
			$('#respond p.comment-form-comment').slideToggle();
			$('#respond p.form-submit').slideToggle();
		}
		
		return false;
	});
	
	function justPostCheckin( lat, lng, address, place_id, place ) {
		
		$.post( ajaxurl, {
			action: 'places_simply_checkin',
			'bpci-lat': lat,
			'bpci-lng': lng,
			'bpci-address': address,
			'place_id': place_id,
			'place': place,
			'_wpnonce_place_post_checkin': $("input#_wpnonce_place_post_checkin").val(),
		},
		function(response)
		{
			if( response == "checkedin") {
				$('.add-checkin.without').removeClass('bpci-loading');
				$('.add-checkin.without').addClass('checkedin');
				$('.add-checkin.without').html('Checked-in');
				$('#cbox-checkins').remove();
			}
			else {
				alert(response);
				$('.add-checkin.without').removeClass('bpci-loading');
			}
		});
		
	}
	
	$('.bp-ci-zoompic').live('click', function(){
		
		if( $(this).find('.thumbnail').attr('width') != "100%" ){
			var thumb = $(this).find('.thumbnail').attr('src');
			var full = $(this).attr('href');
			$(this).find('.thumbnail').attr('src', full);
			$(this).attr('href', thumb);
			$('#footer').append('<div id="bpci-full" style="visibility:hidden"><img  src="'+full+'"></div>');
			var reverseh = $('#bpci-full img').height();
			var reversew = $('#bpci-full img').width();
			var ratio = Number( reverseh / reversew );
			$(this).find('.thumbnail').attr('width', '100%');
			//$(this).find('.thumbnail').attr('height', '100%');
			$(this).find('.thumbnail').css('max-width', '100%');
			$(this).find('.thumbnail').attr('height', Number(ratio * $(this).find('.thumbnail').width() ) +'px');
			$('#footer #bpci-full').remove();
			return false;
		} else {
			var full = $(this).find('.thumbnail').attr('src');
			var thumb = $(this).attr('href');
			$(this).find('.thumbnail').attr('src', thumb);
			$(this).attr('href', full);
			$('#footer').append('<div id="bpci-thumb" style="visibility:hidden"><img  src="'+thumb+'"></div>');
			var reverseh = $('#bpci-thumb img').height();
			var reversew = $('#bpci-thumb img').width();
			var ratio = Number( reverseh / reversew );
			$(this).find('.thumbnail').attr('width', '100px');
			$(this).find('.thumbnail').attr('height', Number(ratio * 100) +'px');
			$('#footer #bpci-thumb').remove();
			return false;
		}
		return false;
	});
	
	if( $('body').hasClass('bpci_is_live') ){
		
		if( bp_checkins_single_vars.disablePlaceTimer != 1) {
			placeCd = Number( bp_checkins_single_vars.livePlaceTimer / 1000 );
			$('article.places-cpt').append('<div id="place-countdown">'+bp_checkins_single_vars.livePlaceMessage+' in <span>'+placeCd+'</span></div>');
			ctDownTimer = setInterval ( "placeCountDown()", 1000 );
		}
	}
	
});


function placeCheckLiveComments( place_id ){
	
	jQuery("#place-countdown").prepend('<i class="bpci-loading"></i>');
	
	response = "";
	
	displayed = jQuery('.commentlist li').length;
	
	jQuery('.commentlist li').each(function(){
		jQuery(this).removeClass('bpci-latest');
	});
	
	var data = {
      action: 'place_live_comments',
      place_id: place_id,
	  displayed_count:displayed
    };

	jQuery.post( ajaxurl, data, function(response) {
      
		if( response.contents != 0 ){
			
			if( response.add_div ) {
				
				if( jQuery('#content .padder').length )
					jQuery('#content .padder').append(response.contents);
				else
					jQuery('#buddypress').append(response.contents);
				
			} else {
				jQuery('.commentlist').prepend(response.contents);
				jQuery('.commentlist li.bpci-latest').fadeOut( 100, function(){
					jQuery(this).fadeIn(100);
				});

				jQuery('#comments h3 span').html( response.comment_count );
			}
			
			
		}
		
		jQuery("#place-countdown i.bpci-loading").remove();
		
    }, 'json');

}


function placeCountDown(){
	
	var t = jQuery('#live-end-date').val().split(/[- :]/);
	var d = new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
	var now = new Date();

	if( d - now < 0 ){
		jQuery('#place-countdown').css('color', '#990000');
		jQuery('#place-countdown').html( bp_checkins_single_vars.livePlaceEnded );
		jQuery('#respond').hide();
		clearInterval(ctDownTimer);
		return false;
	}
	
	var cdvalue = jQuery('#place-countdown span').html();
	if(cdvalue != 0)
		jQuery('#place-countdown span').html( Number(cdvalue - 1) );
	else {
		placeCheckLiveComments( jQuery('#bpci_place_id').val() );
		jQuery('#place-countdown span').html( Number(bp_checkins_single_vars.livePlaceTimer / 1000) );
	}
		
}