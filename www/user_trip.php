<?php

	include("include/init.php");
	loadlib("trips");

	features_ensure_enabled("trips");

	login_ensure_loggedin();

	$user = $GLOBALS['cfg']['user'];

	if ($id = get_int64("trip_id")){
		$trip = trips_get_by_id($id);
	}

	elseif ($id = get_int32("dopplr_id")){
		$trip = trips_get_by_dopplr_id($id);
	}

	else {
		error_404();
	}

	if (! $trip){
		error_404();
	}

	if ($trip['user_id'] != $user['id']){
		error_403();
	}

	trips_inflate_trip($trip);

 	$GLOBALS['smarty']->assign_by_ref("trip", $trip);

	# TO DO: other trips to this locality

	$loc = $trip['locality'];

        $more = array();

        $more['where'] = $loc['place_type'];
        $more['woeid'] = $loc['woeid'];

        $rsp = trips_get_for_user($user, $more);
        $other_trips = array();

        foreach ($rsp['rows'] as $row){
		trips_inflate_trip($row);
                $other_trips[] = $row;
        }

 	$GLOBALS['smarty']->assign_by_ref("other_trips", $other_trips);

	# TO DO: get checkins and atlas (want to go here) for locality

	$travel_map = trips_travel_type_map();
	$GLOBALS['smarty']->assign_by_ref("travel_map", $travel_map);

	$status_map = trips_travel_status_map();
	$GLOBALS['smarty']->assign_by_ref("status_map", $status_map);

	$edit_crumb = crumb_generate("api", "privatesquare.trips.editTrip");
	$GLOBALS['smarty']->assign("edit_crumb", $edit_crumb);

	$delete_crumb = crumb_generate("api", "privatesquare.trips.deleteTrip");
	$GLOBALS['smarty']->assign("delete_crumb", $delete_crumb);

	$GLOBALS['smarty']->display("page_user_trip.txt");
	exit();

?>
