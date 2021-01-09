<?php
// contains helper functions

function get_attendances_csv () {
    $fat = fopen('data/attendance.csv', 'r');
    $attendances = array();    
    $flagfat = true;
    while (($data = fgetcsv($fat, 1000, ",")) !== FALSE) {
        if($flagfat) { $flagfat = false; continue; }
        $object = new stdClass();
        $object->userid = $data[0];
        $object->username = $data[1];
        $object->userlocation = $data[2];
        $object->dob = $data[3];
        $object->workplaceid = $data[4];
        $object->status = $data[5];
        $attendances[] = $object;
    }
    fclose($fat);
    return $attendances;
}

function get_workplaces_csv () {
    $fwp = fopen('data/workplaces.csv', 'r');
    $workPlaces = array();
    $flagfwp = true;
    while (($data = fgetcsv($fwp, 1000, ",")) !== FALSE) {
        if($flagfwp) { $flagfwp = false; continue; }
        $object = new stdClass();
        $object->workplaceid = $data[0];
        $object->workplacename = $data[1];
        $object->workplacelocation = $data[2];
        $workPlaces[$data[0]] = $object;
    }
    fclose($fwp);
    return $workPlaces;
}

function prepare_attendances_data ($attendances, $workPlaces) {
    $perapredAttendances = array();
    foreach ($attendances as $attendance) {
        $object = new stdClass();
        $object->id = $attendance->userid;
        $object->age = calcul_age($attendance->dob);
        $object->distance = calcul_distance($attendance->userlocation, $workPlaces[$attendance->workplaceid]->workplacelocation);
        $object->status = $attendance->status;    
        $perapredAttendances[] = $object;
    }
    $sortedAttendances = reorder_attendances($perapredAttendances);
    return $sortedAttendances;
}

function calcul_age ($dateOfBirth) {
    return floor((time() - strtotime($dateOfBirth)) / 31556926);
}

function calcul_distance ($from, $to) {
    list($from_x, $from_y) = split_tuple($from);
    list($to_x, $to_y) = split_tuple($to);
    return round(sqrt(pow(($from_x - $to_x), 2) + pow(($from_y - $to_y), 2)));
}

function reorder_attendances ($perapredAttendances) {
    $sortedAttendances = array();
    foreach ($perapredAttendances as $element) {
        $sortedAttendances[$element->id][] = $element;
    }
    ksort($sortedAttendances);
    return $sortedAttendances;
}

function split_tuple ($tuple) {
    if (preg_match('~([0-9]+)\s?,\s?([0-9]+)~', $tuple, $m))
    {
        return [$m[1], $m[2]];
    }
}

function calcul_daily_payout ($preparedAttendanceRecord) {
    $daily_basic_pay = get_participant_basic_pay($preparedAttendanceRecord->age);
    $meal_allowance = 5.50;
    $travel_allowance = 0.00;
    $daily_pay = 0.00;
    if ($preparedAttendanceRecord->distance >= 5) {
        $travel_allowance = calcul_distance_payout($preparedAttendanceRecord->distance);
    }
    switch ($preparedAttendanceRecord->status) {
        case "AT":
            $daily_pay = $daily_basic_pay + $meal_allowance + $travel_allowance;
            break;
        case "USL":
                break;
        default:
            $daily_pay = $daily_basic_pay;
            break;
    }
    return $daily_pay;
}

function get_participant_basic_pay ($age) {
    $daily_basic_pay = 0.00 ;
    switch (true) {
        case $age < 18:
            $daily_basic_pay = 72.50;
            break;
        case in_array($age, range(18,24)):
                $daily_basic_pay = 81.00;
                break;
        case $age == 25:
            $daily_basic_pay = 85.90;
            break;    
        default:
            $daily_basic_pay = 90.50;
            break;
    }
    return $daily_basic_pay;
}

function calcul_distance_payout ($distance) {
    return (1.09 * $distance) * 2.00 + 1.00;
}

function build_table($array){
    $html = '<table>';
    $html .= '<tr>';
    $properties = get_object_vars ($array[1]);
    foreach($properties as $key=>$value){
        $html .= '<th>' . htmlspecialchars($key) . '</th>';
    }
    $html .= '</tr>';
    foreach( $array as $key=>$value){
        $html .= '<tr>';
        foreach($value as $key2=>$value2){
            $html .= '<td>' . htmlspecialchars($value2) . '</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</table>';
    return $html;
}

function stdout_results ($dailypayouts) {
    $output = "id, payout" . "\r\n";
    foreach ($dailypayouts as $key=>$payout) {
        $output .= $payout->id . ", ". $payout->payout . "\r\n";
    }
    $out = fopen('php://stdout', 'w');
    fputs ($out, $output);
    fclose($out);
}