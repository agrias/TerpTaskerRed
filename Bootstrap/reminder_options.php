<?php

function getReminderOptions($time) {
	echo ($time == NULL) ? "<option value='NULL' selected>never</option>" : "<option value='NULL'>never</option>";
	echo ($time == 600) ? "<option value='600' selected>10 minutes</option>" : "<option value='600'>10 minutes</option>";
	echo ($time == 1800) ? "<option value='1800' selected>30 minutes</option>" : "<option value='1800'>30 minutes</option>";
	echo ($time == 3600) ? "<option value='3600' selected>1 hour</option>" : "<option value='3600'>1 hour</option>";
	echo ($time == 21600) ? "<option value='21600' selected>6 hours</option>" : "<option value='21600'>6 hours</option>";
	echo ($time == 86400) ? "<option value='86400' selected>1 day</option>" : "<option value='86400'>1 day</option>";
	echo ($time == 172800) ? "<option value='175800' selected>2 days</option>" : "<option value='172800'>2 days</option>";
	echo ($time == 604800) ? "<option value='604800' selected>1 week</option>" : "<option value='604800'>1 week</option>";
}
?>