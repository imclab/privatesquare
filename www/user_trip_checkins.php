<?php

	include("include/init.php");
	loadlib("trips");

	features_ensure_enabled("trips");

	login_ensure_loggedin();

	# in advance of a proper fix... (20140125/straup)
	$GLOBALS['cfg']['pagination_assign_smarty_variable'] = 0;

	$user = $GLOBALS['cfg']['user'];
	$GLOBALS['smarty']->assign_by_ref("owner", $user);

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

	$loc = $trip['locality'];
	$GLOBALS['smarty']->assign_by_ref("locality", $loc);

	$when = implode(";", array(
		$trip['arrival'],
		$trip['departure'],
	));

	$more = array(
		'locality' => $loc['woeid'],
		'when' => $when,
	);

	if ($page = get_int32("page")){
		$more['page'] = $page;
	}

	$rsp = privatesquare_checkins_for_user($user, $more);
	$GLOBALS['smarty']->assign_by_ref("checkins", $rsp['rows']);
	$GLOBALS['smarty']->assign_by_ref("pagination", $rsp['pagination']);

	$GLOBALS['smarty']->display("page_user_trip_checkins.txt");
	exit();

?>
