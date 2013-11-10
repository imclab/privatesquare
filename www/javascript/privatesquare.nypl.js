/*
function privatesquare_nypl_search(){

	if (searching){
		return false;
	}

	searching = true;

	_privatesquare_nypl_hide_map();
	$("#venues").hide();

	var query = prompt("Search for a particular place");

	if (! query){
		var msg = 'Okay, I\'m giving up. <a href="#" onclick="privatesquare_nypl_init();return false;">Start over</a> if you want change your mind.';
		privatesquare_nypl_set_status(msg);
		return false;
	}

	var _onsuccess = function(rsp){
		var lat = rsp['coords']['latitude'];
		var lon = rsp['coords']['longitude'];
		privatesquare_nypl_fetch_venues(lat, lon, query);
		searching = false;
		return;
	};

	var _onerror = function(rsp){};

	privatesquare_nypl_whereami(_onsuccess, _onerror);

	privatesquare_nypl_set_status("Re-checking your location first...");
	return false;
}
*/

function privatesquare_nypl_fetch_venues(lat, lon, query){

	var args = {
		'method': 'nypl.gazetteer.search',
		'latitude': lat,
		'longitude': lon
	};

	if (query != ''){
		args['query'] = query;
	}

	$.ajax({
		'url': _cfg.abs_root_url + 'api/',
		'data': args,
		'success': _privatesquare_nypl_fetch_venues_onsuccess
	});
 
	privatesquare_set_status("Fetching nearby places...");
}

function _privatesquare_nypl_fetch_venues_onsuccess(rsp){

	privatesquare_unset_status();

	if (rsp['stat'] != 'ok'){

		/*
		I am unsure how I feel about this; the maybe better alternative
		is to wrap the lat/lon used to call the API in all the various
		callbacks... (20120429/straup)
		*/

		var _okay = function(rsp){
			var lat = rsp['coords']['latitude'];
			var lon = rsp['coords']['longitude'];
			privatesquare_api_error(rsp);
		};

		var _not_okay = function(){
			privatesquare_api_error(rsp);
		}

		// fix me...
		// privatesquare_nypl_whereami(_okay, _not_okay);
		return;
	}

	var count = rsp['venues'].length;

	if (! count){
		var msg = 'You appear to have fallen in to a black hole. There\'s nothing around here';

		if (rsp['query']){
			msg += ' that looks like <q>' + htmlspecialchars(rsp['query']) + '</q>';
		}

		msg += '. <a href="#" onclick="privatesquare_nypl_search();return false;">Try again</a>';
		msg += ' or <a href="#" onclick="privatesquare_init(\'nypl\');return false;">start from scratch</a>?';

		privatesquare_set_status(msg);
		return;
	}

	var html = '';

	for (var i=0; i < count; i++){
		var v = rsp['venues'][i];
		html += '<option value="' + v['id'] + '">' + v['name'] + '</option>';
	}

	html += '<option value="-1">–– none of the above / search ––</option>';

	var where = $("#where");
	where.attr("data-crumb", rsp['crumb']);
	where.html(html);
	where.change(_privatesquare_where_onchange);

	$("#what").change(_privatesquare_what_onchange);

	// draw the map...

	_privatesquare_show_map(rsp['latitude'], rsp['longitude']);

	privatesquare_unset_status();
	$("#venues").show();

	$("#checkin").submit(privatesquare_submit);
}
