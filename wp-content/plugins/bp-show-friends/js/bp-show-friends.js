jQuery(document).ready( function($) {

  $( '.bp-show-friends-action' ).on( 'click', function( event ){

    $(this).parent('div').find( 'a' ).each( function(){
      if( $(this).hasClass( 'current' ) )
        $(this).removeClass( 'current' );
    });

    $(this).addClass( 'current' );

    event.preventDefault();

    var friendscontainer = $(this).parent().parent().find( '.friends-container' ).first();

    friendscontainer.html( '<p><span class="loader">&nbsp;</span></p>' );

    var type = $(this).data( 'type' );
    var number = $(this).data( 'number' );
    var data = {
      action: 'bpsf_refresh_friends',
      bpsf_type: type,
      bpsf_number: number
    };
        
    $.post(ajaxurl, data, function( response ) {
      friendscontainer.html( response );
    });

    return;

  });

});