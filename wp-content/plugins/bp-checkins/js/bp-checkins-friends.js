jQuery(document).ready(function($){
	var latLong,content,bpciPosition;
	var arrayMarkers = new Array();
	
	if( typeof displayedUserLat !=='undefined' && typeof displayedUserLng !=='undefined' ){
		bpciPosition = new google.maps.LatLng(displayedUserLat,displayedUserLng);
		arrayMarkers.push( {lat:displayedUserLat, lng:displayedUserLng, data:$("#item-header-avatar a").html()} );
	}
	
	$("#members-list .action .activity-checkin").each(function(){
		
		var latlongtoparse = $(this).find("a").attr('rel').split(',');
		var avatar = $(this).parent().parent().find('.item-avatar').html();
		
		lat = Number(latlongtoparse[0]);
		lng = Number(latlongtoparse[1]);
		
		if(!bpciPosition){
			bpciPosition = new google.maps.LatLng(lat,lng);
		}
		
		arrayMarkers.push( {lat:lat, lng:lng, data:avatar} );

	});
	
	function bpci_is_on_map(lat, lng, avatar){
		var onmap = -1;
		for(var i=0; i < arrayMarkers.length ; i++){
			if(arrayMarkers[i].lat == lat && arrayMarkers[i].lng == lng && arrayMarkers[i].data == avatar)
				onmap=i;
		}
		return onmap;
	}
	
	$(".link-checkin").live('click', function(){
		
		var latlongtoparse = $(this).attr('rel').split(',');
		
		lat = Number(latlongtoparse[0]);
		lng = Number(latlongtoparse[1]);
		
		var latLong = new google.maps.LatLng(lat, lng);
		
		
		map = $('#bpci-map').gmap3({ action:'get', name:'map'});
		
		if( -1 == bpci_is_on_map( lat, lng, $(this).parent().parent().parent().find('.item-avatar').html() ) ) {
			add($('#bpci-map'), arrayMarkers.length, lat, lng, $(this).parent().parent().parent().find('.item-avatar').html());
			arrayMarkers.push( {lat:lat, lng:lng, data:$(this).parent().parent().parent().find('.item-avatar').html()} );
		}
		
		map = $('#bpci-map').gmap3({ action:'get', name:'map'});
		map.setCenter(latLong);
		
	});
	
	$("#bpci-map_container").append('<div id="bpci-map"></div>');
	$("#bpci-map").css('width','100%');
	
	$('#bpci-map').gmap3(
      { action: 'init',
        center:bpciPosition,
		callback:function(map){
			for (var i=0; i < arrayMarkers.length ; i++ ) {
              add($(this), i, arrayMarkers[i].lat, arrayMarkers[i].lng, arrayMarkers[i].data);
			  map.setCenter(bpciPosition);
			  map.setZoom(6);
            }
		}
    
  });
  
  function add($this, i, lat, lng, data){
    $this.gmap3(
    { action: 'addMarker',
      latLng: [lat, lng],
      
    },
    { action:'addOverlay',
      latLng: [lat, lng],
      options:{
        content: '<div class="bpci-avatar"><s></s><i></i><span>' + data + '</span></div>',
        offset:{
          	y:-40,
          	x:10
        	}
      	}

    },
	{ action:'clear', name:'marker'});
  }
	
});