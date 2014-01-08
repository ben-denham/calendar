<?php
/**
 * Copyright (c) 2011 Bart Visscher <bartv@thisnet.nl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */



// Check if we are a user
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('calendar');
OCP\JSON::callCheck();

if(trim($_POST['name']) == '') {
	OCP\JSON::error(array('message'=>'empty'));
	exit;
}
$calendar_names = OC_Calendar_Calendar::getCalendarDisplayNames(OCP\USER::getUser());
foreach($calendar_names as $calid => $calname) {
	if($calname == $_POST['name'] && $calid != $_POST['id']) {
		OCP\JSON::error(array('message'=>'namenotavailable'));
		exit;
	}
}

$calendarid = $_POST['id'];

$calendar = OC_Calendar_Calendar::find($calendarid);
try {
    if ($calendar['userid'] == OCP\User::getUser()) {
        OC_Calendar_Calendar::editCalendar($calendarid, strip_tags($_POST['name']), null, null, null, $_POST['color']);
    }
    else {
        OC_Calendar_Calendar::editCalendarPreferences($calendarid, strip_tags($_POST['name']), $_POST['color']);
    }
} catch(Exception $e) {
	OCP\JSON::error(array('message'=>$e->getMessage()));
	exit;
}

$calendar = OC_Calendar_Calendar::find($calendarid);
$tmpl = new OCP\Template('calendar', 'part.choosecalendar.rowfields');
$tmpl->assign('calendar', $calendar);

$shared = false;
if ($calendar['userid'] != OCP\User::getUser()) {
	$sharedCalendar = OCP\Share::getItemSharedWithBySource('calendar', $calendarid);
	if ($sharedCalendar && ($sharedCalendar['permissions'] & OCP\PERMISSION_UPDATE)) {
		$shared = true;
	}
}

$tmpl->assign('shared', $shared);
OCP\JSON::success(array(
	'page' => $tmpl->fetchPage(),
	'eventSource' => OC_Calendar_Calendar::getEventSourceInfo($calendar),
));
