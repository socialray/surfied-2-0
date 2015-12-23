jQuery(document).ready(function($){
	
	bp_init_checkins();
	
	$('.bp-ci-zoompic').live('click', function(){
		
		if( $(this).find('.thumbnail').attr('width') != "100%" ){
			var thumb = $(this).find('.thumbnail').attr('src');
			var full = $(this).attr('href');
			$(this).find('.thumbnail').attr('src', full);
			$(this).attr('href', thumb);
			$(this).find('.thumbnail').attr('width', '100%');
			$(this).find('.thumbnail').attr('height', '100%');
			$(this).find('.thumbnail').css('max-width', '100%');
			return false;
		} else {
			var full = $(this).find('.thumbnail').attr('src');
			var thumb = $(this).attr('href');
			$(this).find('.thumbnail').attr('src', thumb);
			$(this).attr('href', full);
			$(this).find('.thumbnail').attr('width', '100px');
			return false;
		}
		return false;
	});
	
	$('#places-filter-select select').live('change', function() {
		var selected_tab = $( 'div.checkins-type-tabs li.selected a' );

		if ( !selected_tab.length )
			var scope = null;
		else
			var scope = selected_tab.attr('id').replace( '-area', '' );

		var filter = $(this).val();

		bp_checkins_request(scope, filter);

		return false;
	});
	
	function bp_checkins_hide_comments() {
		if ( typeof( bp_dtheme_hide_comments ) != "undefined" )
			bp_dtheme_hide_comments();
		else
			bp_legacy_theme_hide_comments();
	}
	
	/* Checkins Loop Requesting */
	function bp_checkins_request(scope, filter) {
		
		/* Save the type and filter to a session cookie */
		$.cookie( 'bp-checkins-scope', scope, {path: '/'} );
		$.cookie( 'bp-'+scope+'-filter', filter, {path: '/'} );
		$.cookie( 'bp-checkins-oldestpage', 1, {path: '/'} );
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
	
	function bp_init_checkins() {
		/* Reset the page */
		$.cookie( 'bp-checkins-places-oldestpage', 1, {path: '/'} );

		if ( null != $.cookie('bp-places-filter') && $('#places-filter-select').length )
		 	$('#places-filter-select select option[value="' + $.cookie( 'bp-places-filter') + '"]').prop( 'selected', true );
	}
	
});