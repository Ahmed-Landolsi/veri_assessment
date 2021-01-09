<?php
require('lib.php');
// put attendances data into a php array 
$attendances = get_attendances_csv(); 
// put work places data into a php array 
$workPlaces = get_workplaces_csv(); 
// join the two arrays, make calculation and prepare needed attendances data
$perapredAttendances = prepare_attendances_data($attendances, $workPlaces); 
$dailypayouts = array();
// calculate all final payouts
foreach($perapredAttendances as $id=>$userAttendances) {
    $object = new stdClass();
    $object->id = $id;
    $object->payout = 0.00;
    foreach($userAttendances as $attendance){
        $object->payout += calcul_daily_payout($attendance); // addition of all students daily payouts
    }    
    $object->payout = number_format($object->payout, 2); // format payout (float with 2 decimals)
    $dailypayouts[$id] = $object; 
}
// show results
$executedfrom = php_sapi_name();
if($executedfrom !== 'cli'){ // see results on browser: http://localhost:80
    echo build_table($dailypayouts);
}else // print results to php stdout
    stdout_results($dailypayouts);

?>
