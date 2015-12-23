jQuery(document).ready(function($){
	var position, adresse, buttonAction, geocoder, buttonTitle;
	buttonTitle = bp_checkins_dir_vars.addMapViewTitle;
	$("#whats-new-textarea").append('<a href="#" id="bpci-position-me" title="'+bp_checkins_dir_vars.addCheckinTitle+'"><span>'+bp_checkins_dir_vars.addCheckinTitle+'</span></a><a href="#" id="bpci-polaroid" title="'+bp_checkins_dir_vars.addPolaTitle+'"><span>'+bp_checkins_dir_vars.addPolaTitle+'</span></a>');
	
	if( ( !$.cookie("bp-ci-data-delete") || $.cookie("bp-ci-data-delete").indexOf('delete') == -1 ) && $.cookie("bp-ci-data") && $.cookie("bp-ci-data").length > 8){
		$("#bpci-position-me").addClass('disabled');
		var tempPositionToParse = $.cookie("bp-ci-data").split('|');
		position = new google.maps.LatLng(tempPositionToParse[0], tempPositionToParse[1]);
		adresse = tempPositionToParse[2];
		buttonAction = 'show';
		buttonTitle = bp_checkins_dir_vars.addMapViewTitle;
		$("#whats-new-textarea").append('<div id="bpci-position-inputs"><input type="hidden" name="bpci-lat" id="bpci-lat" value="'+position.lat()+'"><input type="hidden" name="bpci-lng" id="bpci-lng" value="'+position.lng()+'"><input type="text" readonly value="'+adresse+'" id="bpci-address" name="bpci-address" placeholder="'+bp_checkins_dir_vars.addressPlaceholder+'"><a href="#" id="bpci-show-on-map" class="map-action" title="'+buttonTitle+'"><span>'+buttonTitle+'</span></a><a href="#" id="bpci-mod-position" class="map-action" title="'+bp_checkins_dir_vars.modCheckinTitle+'"><span>'+bp_checkins_dir_vars.modCheckinTitle+'</span></a><a href="#" id="bpci-refresh-position" class="map-action" title="'+bp_checkins_dir_vars.refreshCheckinTitle+'"><span>'+bp_checkins_dir_vars.refreshCheckinTitle+'</span></a><div id="bpci-map" class="map-hide"></div></div>');
		
	} else {
		$("#bpci-position-me").removeClass('disabled');
	}
	$("#bpci-position-me").click(function(){
		
		if( $.cookie("bp-ci-data") ) {
			$.cookie("bp-ci-data-delete", '', { path: '/' });
			$("#bpci-position-me").addClass('disabled');
			if( !position ){
				var tempPositionToParse = $.cookie("bp-ci-data").split('|');
				position = new google.maps.LatLng(tempPositionToParse[0], tempPositionToParse[1]);
				adresse = tempPositionToParse[2];
				buttonAction = 'show';
				buttonTitle = bp_checkins_dir_vars.addMapViewTitle;
				$("#whats-new-textarea").append('<div id="bpci-position-inputs"><input type="hidden" name="bpci-lat" id="bpci-lat" value="'+position.lat()+'"><input type="hidden" name="bpci-lng" id="bpci-lng" value="'+position.lng()+'"><input type="text" readonly value="'+adresse+'" id="bpci-address" name="bpci-address" placeholder="'+bp_checkins_dir_vars.addressPlaceholder+'"><a href="#" id="bpci-show-on-map" class="map-action" title="'+buttonTitle+'"><span>'+buttonTitle+'</span></a><a href="#" id="bpci-mod-position" class="map-action" title="'+bp_checkins_dir_vars.modCheckinTitle+'"><span>'+bp_checkins_dir_vars.modCheckinTitle+'</span></a><a href="#" id="bpci-refresh-position" class="map-action" title="'+bp_checkins_dir_vars.refreshCheckinTitle+'"><span>'+bp_checkins_dir_vars.refreshCheckinTitle+'</span></a><div id="bpci-map" class="map-hide"></div></div>');
			}
			
			return false;
		}
		
		if( $("#bpci-position-me").hasClass('disabled') != true ){
			$(this).parent().append('<div id="bpci-position-inputs"><span class="bpci-loader">loading...</span></div>');
			$("#bpci-position-me").addClass('disabled');
			buttonAction = 'show';

			$('#bpci-position-inputs').gmap3({
				action : 'geoLatLng',
		        callback : function(latLng){
					if(latLng){
						position = latLng;
						$(this).gmap3({
							action:'getAddress',
		                    latLng:latLng,
		                    callback:function(results){
								adresse = results && results[1] ? results && results[1].formatted_address : 'no address';
								$.cookie("bp-ci-data", latLng.lat()+"|"+latLng.lng()+"|"+adresse, { path: '/' });
								$.cookie("bp-ci-data-delete", '', { path: '/' });
								$("#bpci-position-inputs").html('<input type="hidden" name="bpci-lat" id="bpci-lat" value="'+latLng.lat()+'"><input type="hidden" name="bpci-lng" id="bpci-lng" value="'+latLng.lng()+'"><input type="text" readonly value="'+adresse+'" id="bpci-address" name="bpci-address" placeholder="'+bp_checkins_dir_vars.addressPlaceholder+'"><a href="#" id="bpci-show-on-map" class="map-action" title="'+buttonTitle+'"><span>'+buttonTitle+'</span></a><a href="#" id="bpci-mod-position" class="map-action" title="'+bp_checkins_dir_vars.modCheckinTitle+'"><span>'+bp_checkins_dir_vars.modCheckinTitle+'</span></a><a href="#" id="bpci-refresh-position" class="map-action" title="'+bp_checkins_dir_vars.refreshCheckinTitle+'"><span>'+bp_checkins_dir_vars.refreshCheckinTitle+'</span></a><div id="bpci-map" class="map-hide"></div>');
							}
						});
					} else {
						buttonAction = 'search';
						buttonTitle = bp_checkins_dir_vars.addMapSrcTitle;
						$("#bpci-position-inputs").html('<input type="hidden" name="bpci-lat" id="bpci-lat"><input type="hidden" name="bpci-lng" id="bpci-lng"><input type="text" id="bpci-address" name="bpci-address" placeholder="'+bp_checkins_dir_vars.addressPlaceholder+'"><a href="#" id="bpci-show-on-map" class="map-action" title="'+buttonTitle+'"><span>'+buttonTitle+'</span></a><a href="#" id="bpci-mod-position" class="map-action" title="'+bp_checkins_dir_vars.modCheckinTitle+'"><span>'+bp_checkins_dir_vars.modCheckinTitle+'</span></a><a href="#" id="bpci-refresh-position" class="map-action" title="'+bp_checkins_dir_vars.refreshCheckinTitle+'"><span>'+bp_checkins_dir_vars.refreshCheckinTitle+'</span></a><div id="bpci-map" class="map-hide"></div>');
						alert(bp_checkins_dir_vars.html5LocalisationError);
						$("#bpci-address").focus();
					}
				}
			});
			
		}
		
		return false;
	});
	
	
	$('#bpci-show-on-map').live( 'click', function(){
		$("#bpci-map").show();
		
		if( buttonAction == 'show' ) {
			$("#bpci-map").gmap3({
	            action: 'addMarker', 
	            latLng:position,
				map:{
					center: position,
					zoom: 16
				}
			},
			{
				action : 'clear',
				name: 'marker'
			},
			{ action:'addOverlay',
	          latLng: position,
	          options:{
	            content: '<div class="bpci-avatar"><s></s><i></i><span>' + $("#whats-new-avatar").html() + '</span></div>',
	            offset:{
	              y:-40,
	              x:10
	            }
	          }
			});
		} else if( buttonAction == 'search' ) {
			address = $('#bpci-address').val();

			bpci_search_position( address, '#bpci-map', '#bpci-address', '#bpci-lat', '#bpci-lng', true, $("#whats-new-avatar").html() );
		}
		
		return false;
	});
	
	$('#bpci-place-show-on-map').click(function(){
		address = $('#bpci-place-address').val();
		
		$("#bpci-place-map").show();
		
		if( $('#new-place-avatar').length )
			avatar = $('#new-place-avatar').html();
		else
			avatar = $("#whats-new-avatar").html();

		bpci_search_position( address, '#bpci-place-map', '#bpci-place-address', '#bpci-place-lat', '#bpci-place-lng', false, avatar );
		
		return false;
	});
	
	function bpci_search_position( address, map, addressField, latField, lngField, cookie, avatar ) {
		geocoder = new google.maps.Geocoder();

		geocoder.geocode( { 'address': address}, function(results, status) {
		    /* Si l'adresse a pu être géolocalisée */
		    if (status == google.maps.GeocoderStatus.OK) {
		     /* Récupération de sa latitude et de sa longitude */
		     var glat = results[0].geometry.location.lat();
		     var glng = results[0].geometry.location.lng();
		     position = new google.maps.LatLng(glat, glng);

			$(map).gmap3({
	            action: 'addMarker', 
	            latLng:position,
				map:{
					center: position,
					zoom: 16
				},
				callback : function(marker){
					$(this).gmap3({
	                    action:'getAddress',
	                    latLng:marker.getPosition(),
	                    callback:function(results){
	                      	adresse = results && results[1] ? results && results[1].formatted_address : 'no address';
	
							$(addressField).val(adresse);
							$(addressField).attr("readonly","readonly");
							
							$(latField).val( position.lat() );
							$(lngField).val( position.lng() );
							
							if( cookie ) {
								$.cookie("bp-ci-data", position.lat()+"|"+position.lng()+"|"+adresse, { path: '/' });
								$.cookie("bp-ci-data-delete", '', { path: '/' });
								buttonAction = 'show';
								$('#bpci-show-on-map').attr('title',bp_checkins_dir_vars.addMapViewTitle);
							}
	                    }
	                  });
				}
			},
			{
				action : 'clear',
				name: 'marker'
			},
			{ action:'addOverlay',
	          latLng: position,
	          options:{
	            content: '<div class="bpci-avatar"><s></s><i></i><span>' + avatar + '</span></div>',
	            offset:{
	              y:-40,
	              x:10
	            }
	          }
			});

		     } else {
		      alert( bp_checkins_dir_vars.addErrorGeocode+": " + status);
		     }
		    });
	}
	
	$('#bpci-mod-position').live( 'click', function(){
		$("#bpci-map").gmap3({
			action : 'clear',
			name: 'overlay'
		});
		$("#bpci-map").hide();
		buttonAction = 'search';
		$('#bpci-show-on-map').attr('title', bp_checkins_dir_vars.addMapSrcTitle);
		$("#bpci-address").val("");
		$("#bpci-address").attr("readonly",false);
		$("#bpci-address").focus();
		/* need to write over this cookie
		$.cookie("bp-ci-data", null);*/
		return false;
	});
	
	$('#bpci-refresh-position').live( 'click', function(){
		$("#bpci-map").gmap3({
			action : 'clear',
			name: 'overlay'
		});
		
		$('#bpci-position-inputs').remove();
		$("#bpci-map").hide();
		$("#bpci-position-me").removeClass('disabled')
		$.cookie("bp-ci-data", '', { path: '/' });		
		$("#bpci-position-me").trigger('click');
		return false;
	});
	
	$("#bpci-position-me").trigger('click');
	
	bp_init_checkins();
	
	$("#bpci-polaroid").click(function(){
		
		if( $(this).hasClass('disabled') && $('.bp-checkins-whats-new .bp-ci-zoompic').length ) {
			
			$('.bp-checkins-whats-new .bp-ci-zoompic').remove();
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
		if( window.File && window.FileReader && bp_checkins_dir_vars.uploadAuthorized == 1 ){
			return '<div id="bpci-pola-adder"><a href="#" id="bpci_use_upload">'+bp_checkins_dir_vars.imageAddUpload+'</a> <a href="#" id="bpci_use_link">'+bp_checkins_dir_vars.imageAddExternal+'</a></link>';
		}
		else return '<div id="bpci-pola-adder"><input type="text" placeholder="'+bp_checkins_dir_vars.imageUrlPlaceholder+'"><a href="#" id="bpci-polaroid-add-link" title="'+bp_checkins_dir_vars.addPolaLinkTitle+'"><span>'+bp_checkins_dir_vars.addPolaLinkTitle+'</span></a></div>';
	}
	
	$('#bpci_use_upload').live('click', function(){
		
		if( $('#bpci-polaroid-upload').length && $('#bpci-polaroid-upload').hasClass('bpci-loading') )
			return false;
		
		else
			$('#bpci-pola-adder').html('<span id="form-upcheckin"><input type="file" name="_checkin_pic" id="checkin_pic"></span><a href="#" id="bpci-polaroid-upload" title="'+bp_checkins_dir_vars.addPolaLinkTitle+'"><span>'+bp_checkins_dir_vars.addPolaLinkTitle+'</span></a>');
		
		return false;
	});
	
	$('#bpci_use_link').live('click', function(){
		$('#bpci-pola-adder').html('<input type="text" placeholder="'+bp_checkins_dir_vars.imageUrlPlaceholder+'"><a href="#" id="bpci-polaroid-add-link" title="'+bp_checkins_dir_vars.addPolaLinkTitle+'"><span>'+bp_checkins_dir_vars.addPolaLinkTitle+'</span></a>');
		return false;
	});
	
	$('#bpci-polaroid-upload').live('click', function(){
		
		var file = $('#checkin_pic').get(0).files[0];
		var max_file_size = bp_checkins_dir_vars.maxUploadFileSize;//size in MO
		
		if( file.type.indexOf('image') == -1 ) {
			alert(bp_checkins_dir_vars.imageErrorType);
			return false;
		}	

		name = file.name;
		size = file.size / 1024;
		type = file.type;
		
		sizeInMo = Math.round((size / 1024) * 100) / 100 ;
		
		if( sizeInMo > max_file_size ){
			alert( bp_checkins_dir_vars.imageTooBig + max_file_size + 'MO)');
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
			'type_upload':'checkin',
			'cookie': encodeURIComponent(document.cookie),
			'_wpnonce_post_checkin': $("input#_wpnonce_post_checkin").val(),
			'encodedimg': img,
			'imgtype':type,
			'imgname':name
		},
		function(response) {
			
			if( response[0] != "0" ) {
				sendToContentEditable( response[1], response[2]);
			}
			else alert(response[1]);
		}, 'json');
				
	}
	
	function sendToContentEditable(fullimage, resizedimage) {
		
		if($(".bp-checkins-whats-new").html()=="") {
			$(".bp-checkins-whats-new").append('<span>'+bp_checkins_dir_vars.yourcontenthere+'</span>');
		}
		$(".bp-checkins-whats-new").prepend('<a href="'+fullimage+'" class="bp-ci-zoompic align-left"><img src="'+resizedimage+'" width="100px" alt="Photo" class="align-left thumbnail" /></a>');
			
		$(".bp-checkins-whats-new").append('<div id="bpci-to-remove" style="clear:both"></div>');
		$("#bpci-pola-adder").remove();
	}
	
	$("#bpci-pola-adder a#bpci-polaroid-add-link").live('click', function(){
		if( $("#bpci-pola-adder input").val().length < 3 || !isImageLink( $("#bpci-pola-adder input").val() ) )
			alert( bp_checkins_dir_vars.imageErrorNoUrl );
			
		else {
			
			if($(".bp-checkins-whats-new").html()=="") {
				$(".bp-checkins-whats-new").append('<span>'+bp_checkins_dir_vars.yourcontenthere+'</span>');
			}
			
			$(".bp-checkins-whats-new").prepend('<a href="'+$("#bpci-pola-adder input").val()+'" class="bp-ci-zoompic align-left"><img src="'+$("#bpci-pola-adder input").val()+'" width="100px" alt="Photo" class="align-left thumbnail" /></a>');
				
			$(".bp-checkins-whats-new").append('<div id="bpci-to-remove" style="clear:both"></div>');
			$(this).parent().remove();
		}
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
	
	$('.bp-checkins-whats-new').click(function(){
		if( !$.cookie("bp-ci-data") ){
			alert( bp_checkins_dir_vars.pleaseLocalizeU);
		} else {
			$(this).attr('contenteditable', 'true');
		}
	});
	
	$("input#aw-whats-new-submit").click( function() {
		$("#bpci-map").hide();
	});
	
	
	$("input#bpci-whats-new-submit").click( function() {
		$("#bpci-map").hide();
		var button = $(this);
		var form = button.parent().parent().parent().parent();
		var textareaCheckin;

		form.children().each( function() {
			if( $.nodeName(this, "input") ) {
				$(this).prop( 'disabled', true );
			}
			if($(this).attr('id') == "whats-new-content") {
				textareaCheckin = $(this).find(".bp-checkins-whats-new");
				textareaCheckin.attr('contenteditable', false);
			}
		});
		
		textareaCheckin.find('#bpci-to-remove').remove();

		/* Remove any errors */
		$('div.error').remove();
		button.addClass('loading');
		button.prop('disabled', true);

		/* Default POST values */
		var object = 'checkin';
		var item_id = $("#whats-new-post-in").val();
		var content = textareaCheckin.html();

		/* Set object for non-profile posts */
		if ( item_id > 0 ) {
			object = $("#whats-new-post-object").val();
		}

		$.post( ajaxurl, {
			action: 'post_checkin',
			'cookie': encodeURIComponent(document.cookie),
			'_wpnonce_post_checkin': $("input#_wpnonce_post_checkin").val(),
			'content': content,
			'object': object,
			'item_id': item_id
		},
		function(response) {

			form.children().each( function() {
				if( $.nodeName(this, "input") ) {
					$(this).prop( 'disabled', false );
				}
				if($(this).attr('id') == "whats-new-content") {
					textareaCheckin = $(this).find(".bp-checkins-whats-new");
					textareaCheckin.attr('contenteditable', true);
				}
			});

			/* Check for errors and append if found. */
			if ( response[0] + response[1] == '-1' ) {
				form.prepend( response.substr( 2, response.length ) );
				$( 'form#' + form.attr('id') + ' div.error').hide().fadeIn( 200 );
			} else {
				if ( 0 == $("ul.activity-list").length ) {
					$("div.error").slideUp(100).remove();
					$("div#message").slideUp(100).remove();
					$("div.activity").append( '<ul id="activity-stream" class="activity-list item-list">' );
				}

				$("ul#activity-stream").prepend(response);
				$("ul#activity-stream li:first").addClass('new-update');

				$("li.new-update").hide().slideDown( 300 );
				$("li.new-update").removeClass( 'new-update' );
				$("#bpci-polaroid").removeClass( 'disabled' );
				textareaCheckin.html('');
			}

			$("#whats-new-options").animate({height:'0px'});
			$("form#whats-new-form .bp-checkins-whats-new").animate({height:'40px'});
			$("#bpci-whats-new-submit").prop("disabled", false).removeClass('loading');
		});

		return false;
	});
	
	
	$('#places-area').click(function(){
		
		if( !$('div.checkins-type-tabs').hasClass('no-ajax') ) {
			
			$("#new-place-detailed-content").animate({height:'0px'});
			$('#whats-new-form').slideUp('slow');
			$("#checkins-area").parent('li').removeClass('selected');
			$('#places-form').slideDown('slow');
			$(this).parent('li').addClass('selected');

			bp_checkins_switch('places');

			return false;
		}
		
	});
	
	$('#checkins-area').click(function(){
		
		if( !$('div.checkins-type-tabs').hasClass('no-ajax') ) {
			$('#places-form').slideUp('slow');
			$("#places-area").parent('li').removeClass('selected');
			$('#whats-new-form').slideDown('slow');
			$(this).parent('li').addClass('selected');

			bp_checkins_switch('checkins');

			return false;
		}
		
	});
	
	$('#bp-checkins-place-title').focus( function(){
		$("#new-place-detailed-content").animate({height:'100%'});
	});
	
	$("#bp_checkins_remove_featured").click(function(){
		$("#bp_checkins_featured_image_id").val('');
		$("#bp_checkins_featured_image img").remove();
		$("#bp_checkins_featured_image").hide();
		return false;
	});
	
	$('#checkins-filter-select select').change( function() {
		var selected_tab = $( 'div.checkins-type-tabs li.selected a' );

		if ( !selected_tab.length )
			var scope = null;
		else
			var scope = selected_tab.attr('id').replace( '-area', '' );

		var filter = $(this).val();

		bp_checkins_request(scope, filter);

		return false;
	});
	
	$('#places-filter-select select').live('change', function() {
		var selected_tab = $( 'div.checkins-type-tabs li.selected a' );

		if ( !selected_tab.length )
			var scope = null;
		else
			var scope = selected_tab.attr('id').replace( '-area', '' );

		var filter = $(this).val();
		
		if('browse_search' == filter){
			url = window.location.toString() + 'place';
			window.location.href = url;
			return false;
		}

		bp_checkins_request(scope, filter);

		return false;
	});
	
	function bp_checkins_hide_comments() {
		if ( typeof( bp_dtheme_hide_comments ) != "undefined" )
			bp_dtheme_hide_comments();
		else
			bp_legacy_theme_hide_comments();
	}

	function bp_checkins_switch( scope ){
		
		
		$.cookie( 'bp-checkins-scope', scope, {path: '/'} );
		$.cookie( 'bp-activity-oldestpage', 1, {path: '/'} );
		$.cookie( 'bp-checkins-places-oldestpage', 1, {path: '/'} );
		
		$('div.item-list-tabs li').each( function() {
			$(this).removeClass('selected loading');
		});

		/* Set the correct selected nav and filter */
		$('a#'+scope+'-area').parent('li').addClass('selected');
		$('div.checkins-type-tabs li.selected').addClass('loading');
		
		if( scope == 'places' )
			$('#checkins-dir-title').html( bp_checkins_dir_vars.placeTitle );
		else
			$('#checkins-dir-title').html( bp_checkins_dir_vars.checkinTitle );
		
		if ( bp_ajax_request )
			bp_ajax_request.abort();
		
		bp_ajax_request = $.post( ajaxurl, {
			action: 'switch_checkins_template',
			object:scope
		},
		function(response)
		{
			$('div.activity').fadeOut( 100, function() {
				jq(this).html(response.contents);
				jq(this).fadeIn(100);

				/* Selectively hide comments */
				bp_checkins_hide_comments();
				
				if( response.selectbox != 0) {
					
					$('#subnav ul li#checkins-filter-select').hide();
					if( !$('#places-filter-select').length )
						$('#subnav ul').append(response.selectbox);
					if ( null != $.cookie('bp-places-filter') && $('#places-filter-select').length )
					 	$('#places-filter-select select option[value="' + $.cookie( 'bp-places-filter') + '"]').prop( 'selected', true );
				} else {
					$('#subnav ul li#checkins-filter-select').show();
					$('#subnav ul li#places-filter-select').remove();
				}
			});

			$('div.checkins-type-tabs li.selected').removeClass('loading');

		}, 'json' );
		return false;
	}
	/* Checkins Loop Requesting */
	function bp_checkins_request(scope, filter) {
		
		/* Save the type and filter to a session cookie */
		$.cookie( 'bp-checkins-scope', scope, {path: '/'} );
		$.cookie( 'bp-'+scope+'-filter', filter, {path: '/'} );
		$.cookie( 'bp-activity-oldestpage', 1, {path: '/'} );
		$.cookie( 'bp-checkins-places-oldestpage', 1, {path: '/'} );

		/* Remove selected and loading classes from tabs */
		$('div.item-list-tabs li').each( function() {
			$(this).removeClass('selected loading');
		});

		/* Set the correct selected nav and filter */
		$('a#'+scope+'-area').parent('li').addClass('selected');
		$('div.checkins-type-tabs li.selected').addClass('loading');
		$('#'+scope+'-filter-select select option[value="' + filter + '"]').prop( 'selected', true );
		if ( bp_ajax_request )
			bp_ajax_request.abort();

		bp_ajax_request = $.post( ajaxurl, {
			action: scope+'_apply_filter',
			'cookie': encodeURIComponent(document.cookie),
			'_wpnonce_activity_filter': jq("input#_wpnonce_activity_filter").val(),
			'scope': scope,
			'filter': filter
		},
		function(response)
		{
			$('div.activity').fadeOut( 100, function() {
				jq(this).html(response.contents);
				jq(this).fadeIn(100);

				/* Selectively hide comments */
				bp_checkins_hide_comments();
			});

			/* Update the feed link */
			if ( null != response.feed_url )
				$('.directory div#subnav li.feed a, .home-page div#subnav li.feed a').attr('href', response.feed_url);

			$('div.checkins-type-tabs li.selected').removeClass('loading');

		}, 'json' );
	}
	
	/* places form */
	
	$('#bpci-place-address').click(function(){
		if( !$(this).val() )
			return false;
		else {
			$(this).val('');
			$("#bpci-place-lat").val('');
			$("#bpci-place-lng").val('');
			$(this).attr('readonly', false);
			$('#bpci-place-map').hide();
			
		}
	});
	
	$('input[name=bp_checkins_place_type]:radio').click(function(){
		
		if( $(this).val() == 'live' )
			$('#bp_checkins_place_time').removeClass('bp-checkins-hide');
		else
			$('#bp_checkins_place_time').addClass('bp-checkins-hide');
			
	});
	
	$('#bpci-place-new-submit').click(function(){
		
		var button = $(this);
		var form = button.parent().parent().parent().parent().parent();
		var errors = new Array();
		
		var content         = tinyMCE.activeEditor.getContent();
		var title           = $("#bp-checkins-place-title").val();
		var address         = $("#bpci-place-address").val();
		var lat             = $("#bpci-place-lat").val();
		var lng             = $("#bpci-place-lng").val();
		var group_id        = $('#new-place-post-in').val();
		var attached_images = $('#bp_checkins_attachment_ids').val();
		var featured_image  = $('#bp_checkins_featured_image_id').val();
		
		$('div.error').remove();
		button.addClass('loading');
		button.prop('disabled', true);
		
		/* Form validation */
		
		if( !title.length || title.length < 3 ) {
			alert( $("#bp-checkins-place-title").attr('placeholder') +' '+ bp_checkins_dir_vars.isrequired );
			button.removeClass('loading');
			button.prop('disabled', false);
			return false;
		}
		
		if( !content.length || content.length < 3 ) {
			alert( $('#_place_description_label').html() +' '+ bp_checkins_dir_vars.isrequired );
			button.removeClass('loading');
			button.prop('disabled', false);
			return false;
		}
		
		if( !lat || !lng || address.length < 3 ) {
			alert( $("#bpci-place-address").attr('placeholder') +' '+ bp_checkins_dir_vars.isrequired );
			button.removeClass('loading');
			button.prop('disabled', false);
			return false;
		}
		
		if( $('input[name=bp_checkins_place_category]:radio:checked').length < 1 ) {
			alert($('#bp_checkins_place_category_label').html());
			button.removeClass('loading');
			button.prop('disabled', false);
			return false;
		}
		
		var cat = $('input[name=bp_checkins_place_category]:radio:checked').val();
		
		if( $('input[name=bp_checkins_place_type]:radio:checked').val() == 'live' ) {
			var dateRegex = new RegExp(/^[0-9]{4}\-(0[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01])/);
			var timeRegex = new RegExp(/^[0-9]{2}\:[0-9]{2}/);
			
			if(!$("#bp_checkins_place_start_date").val().length || !dateRegex.test( $("#bp_checkins_place_start_date").val() ) ){
				alert( $("#bp_checkins_place_start_time_label").html() + ' ('+ $("#bp_checkins_place_start_date").attr('placeholder') +')');
				$("#bp_checkins_place_start_date").val('');
				button.removeClass('loading');
				button.prop('disabled', false);
				return false;
			}
			if(!$("#bp_checkins_place_end_date").val().length || !dateRegex.test( $("#bp_checkins_place_end_date").val() ) ){
				alert( $("#bp_checkins_place_end_time_label").html() + ' ('+ $("#bp_checkins_place_end_date").attr('placeholder') +')');
				$("#bp_checkins_place_end_date").val('');
				button.removeClass('loading');
				button.prop('disabled', false);
				return false;
			}
			if(!$("#bp_checkins_place_start_time").val().length || !timeRegex.test( $("#bp_checkins_place_start_time").val() ) ){
				alert( $("#bp_checkins_place_start_time_label").html() + ' ('+ $("#bp_checkins_place_start_time").attr('placeholder') +')');
				$("#bp_checkins_place_start_time").val('');
				button.removeClass('loading');
				button.prop('disabled', false);
				return false;
			}
			if(!$("#bp_checkins_place_end_time").val().length || !timeRegex.test( $("#bp_checkins_place_end_time").val() ) ){
				alert( $("#bp_checkins_place_end_time_label").html() + ' ('+ $("#bp_checkins_place_end_time").attr('placeholder') +')');
				$("#bp_checkins_place_end_time").val('');
				button.removeClass('loading');
				button.prop('disabled', false);
				return false;
			}
		}
		var type = $('input[name=bp_checkins_place_type]:radio:checked').val();
		var timebegin = $("#bp_checkins_place_start_date").val() +' '+ $("#bp_checkins_place_start_time").val();
		var timeend = $("#bp_checkins_place_end_date").val() +' '+ $("#bp_checkins_place_end_time").val();

		$('#'+form.attr('id')+' :input').each( function() {
			$(this).prop( 'disabled', true );
		});
		
		$.post( ajaxurl, {
			action: 'post_places',
			'cookie': encodeURIComponent(document.cookie),
			'_wpnonce_post_places': $("input#_wpnonce_post_places").val(),
			'content': content,
			'group_id' : group_id,
			'title': title,
			'bpci-address': address,
			'bpci-lat': lat,
			'bpci-lng': lng,
			'category': cat,
			'type': type,
			'start': timebegin,
			'end': timeend,
			'attached_images': attached_images,
			'featured_image': featured_image,
		},
		function(response) {
			
			if ( response[0] + response[1] == '-1' ) {
				form.append( response.substr( 2, response.length ) );
				$( 'form#' + form.attr('id') + ' div.error').hide().fadeIn( 200 );
			} else {
				if ( 0 == $("ul.places-list").length ) {
					$("div.error").slideUp(100).remove();
					$("div#message").slideUp(100).remove();
					$("div.activity").append( '<ul id="places-stream" class="places-list item-list">' );
				}

				$("ul#places-stream").prepend(response);
				$("ul#places-stream li:first").addClass('new-place');

				if ( 0 != $("div#latest-update").length ) {
					var l = $("ul#places-stream li.new-place .places-content .places-inner p").html();
					var v = $("ul#places-stream li.new-place .places-content .places-header p a.view").attr('href');

					var ltext = $("ul#places-stream li.new-place .places-content .places-inner p").text();

					var u = '';
					if ( ltext != '' )
						u = '&quot;' + l + '&quot; ';

					u += '<a href="' + v + '" rel="nofollow">' + BP_DTheme.view + '</a>';

					$("div#latest-update").slideUp(300,function(){
						$("div#latest-update").html( u );
						$("div#latest-update").slideDown(300);
					});
				}

				$("li.new-place").hide().slideDown( 300 );
				$("li.new-place").removeClass( 'new-place' );
				tinyMCE.activeEditor.setContent('');
				$("#bp-checkins-place-title").val('');
				$("#bpci-place-address").val('');
				$("#bpci-place-lat").val('');
				$("#bpci-place-lng").val('');
				$('#new-place-post-in select option[value="0"]').prop( 'selected', true );
				$('#bp_checkins_attachment_ids').val('');
				$('#bp_checkins_featured_image_id').val('');
				
				$('input[name=bp_checkins_place_category]:radio').each( function(){
					if($(this).attr('checked') == 'checked' )
						$(this).attr('checked', false);
				});
				$('input[name=bp_checkins_place_type]:radio').each(function(){
					if($(this).val() == 'live')
						$(this).attr('checked', false);
					else 
						$(this).attr('checked', 'checked');
				});
				$("#bp_checkins_place_start_date").val('');
				$("#bp_checkins_place_start_time").val('');
				$("#bp_checkins_place_end_date").val('');
				$("#bp_checkins_place_end_time").val('');
				$("#bpci-place-address").attr('readonly', false);
				$("#bp_checkins_featured_image img").remove();
				$("#bp_checkins_featured_image").hide();
				$("#bpci-place-map").hide();
				$('#bp_checkins_place_time').addClass('bp-checkins-hide');
			}
			
			$('#'+form.attr('id')+' :input').each( function() {
				$(this).prop( 'disabled', false );
			});

			$("#new-place-detailed-content").animate({height:'0px'});
			
			$("#bpci-place-new-submit").prop("disabled", false).removeClass('loading');
			
		});
		$(window).scrollTop($('#content').offset().top);
		return false;
	});
	
	$('.bpci-place-load-more a').live('click', function(){
		$(this).addClass('loading');
		var liElement = $(this).parent();

		if ( null == $.cookie('bp-checkins-places-oldestpage') )
			$.cookie('bp-checkins-places-oldestpage', 1, {path: '/'} );

		var bpci_places_oldest_page = ( $.cookie('bp-checkins-places-oldestpage') * 1 ) + 1;

		$.post( ajaxurl, {
			action: 'places_get_older_updates',
			'cookie': encodeURIComponent(document.cookie),
			'page': bpci_places_oldest_page
		},
		function(response)
		{
			liElement.removeClass('loading');
			$.cookie( 'bp-checkins-places-oldestpage', bpci_places_oldest_page, {path: '/'} );
			$("#content ul.places-list").append(response.contents);

			liElement.hide();
		}, 'json' );

		return false;
	});
	
	$('.delete-place').live('click', function(){
		
		var li        = $(this).parent().parent().parent();
		var delete_link = $(this);
		var placeid   = $(this).parent().parent().parent().attr('id').replace( 'place-', '' );
		var link_href = $(this).attr('href');
		var nonce     = link_href.split('_wpnonce=');

		nonce = nonce[1];

		delete_link.addClass('loading');
		
		$.post( ajaxurl, {
			action: 'place_delete',
			'cookie': encodeURIComponent(document.cookie),
			'place_id': placeid,
			'_wpnonce': nonce
		},
		function(response)
		{
			if ( response[0] + response[1] == '-1' ) {
				li.prepend( response.substr( 2, response.length ) );
				li.find('div#message').fadeOut(3000);
			} else {
				li.slideUp(300);
			}
			delete_link.removeClass('loading');
		});
		
		return false;
	})
	
	function bp_init_checkins() {
		/* Reset the page */
		$.cookie( 'bp-activity-oldestpage', 1, {path: '/'} );
		$.cookie( 'bp-checkins-places-oldestpage', 1, {path: '/'} );

		if ( null != $.cookie('bp-checkins-filter') && $('#checkins-filter-select').length )
			$('#checkins-filter-select select option[value="' + $.cookie('bp-checkins-filter') + '"]').prop( 'selected', true );

		/* Activity Tab Set */
		if ( null != $.cookie('bp-checkins-scope') && $('div.checkins-type-tabs').length) {
			if( !$('body').hasClass('groups') && !$('body').hasClass('bpci-user')){
				$('div.checkins-type-tabs li').each( function() {
					$(this).removeClass('selected');
				});
				$('a#' + $.cookie('bp-checkins-scope') + '-area').parent('li').addClass('selected');

				$('form.bp-ci-form').each( function() {
					$(this).removeClass('bp-checkins-hide')
				})

				formtohide = $('div.checkins-type-tabs li').not('.selected').find('a').attr('id').replace('-area', '');
				templatetoload = $('div.checkins-type-tabs li.selected').find('a').attr('id').replace('-area', '');
				$('form.' + formtohide +'-new').addClass('bp-checkins-hide');

				if( templatetoload != 'checkins' ){
					bp_checkins_switch('places');
				}
			}
			
			if ( null != $.cookie('bp-places-filter') && $('#places-filter-select').length )
			 	$('#places-filter-select select option[value="' + $.cookie( 'bp-places-filter') + '"]').prop( 'selected', true );
		}
	}
	
});