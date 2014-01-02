<?php
//This is a library from some functions needed to get repeating dates.

$cycle = array("day" => 1, "week" => 2, "month" => 3, "year" => 4);

function getNextDate($nextDate, $repeatInt, $repeatFreq) {
    if ($repeatInt == 1)
        return strtotime("+" . $repeatFreq . " days", $nextDate);
    if ($repeatInt == 2)
        return strtotime("+" . $repeatFreq . " weeks", $nextDate);
    if ($repeatInt == 3)
        return getNextMonth($nextDate, $repeatFreq);
    if ($repeatInt == 4)
        return getNextYear($nextDate, $repeatFreq);
}

function getNextMonth($date, $repeatFreq) {
    $newDate = strtotime("+{$n} months", $date);
    // adjustment for events that repeat on the 29th, 30th and 31st of a month
    if (date('j', $date) !== (date('j', $newDate))) {
        $newDate = strtotime("+" . $n + 1 . " months", $date);
    }
    return $newDate;
}

function getNextYear($date, $repeatFreq) {
    $newDate = strtotime("+{$n} years", $date);
    // adjustment for events that repeat on february 29th
    if (date('j', $date) !== (date('j', $newDate))) {
        $newDate = strtotime("+" . $n + 3 . " years", $date);
    }
    return $newDate;
}

?>