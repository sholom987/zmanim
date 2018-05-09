<?php
$zips = array(11213, 33319);
$all_zip_times = array();
$headers = array();
$dates = array();
$get_dates = true;
foreach ($zips as $zip) {
    $data = file_get_contents("https://www.chabad.org/calendar/candlelighting/candlelighting.csv.asp?locationId=" . $zip . "&locationType=2&tdate=1/1/2018&weeks=60");
    $rows = explode("\n", $data);
    $times = array();
    $found_header = false;
    foreach($rows as $row) {
        $line = str_getcsv($row);
        if ($found_header && array_key_exists(1, $line) && array_key_exists(2, $line) && array_key_exists(3, $line) && strpos($line[3], 'Candle') !== false) {
            if($get_dates) {
                array_push($dates, trim(strstr($line[1], ' ', false)));
            }
            array_push($times, $line[2]);

        } else if ($get_dates && $line[0] == 'Torah Portion') {
            $headers = $line;
            $found_header = true;
        } else if ($line[0] == 'Torah Portion') {
            $found_header = true;
        }
    }
    if($get_dates) {
        array_push($all_zip_times, $dates);
        array_push($headers, "Dates");
        array_unshift($dates, "Dates");
    }
    $get_dates = false;
    array_push($headers, $zip);
    array_push($all_zip_times, $times);
}
var_dump($all_zip_times);
//outputCSV($all_zip_times);
//function outputCSV($data) {
//    $outputBuffer = fopen("php://output", 'w');
    
    $rows1 = array();
    $i = 0;
    foreach($header as $val) {
        $rows1[$i] -> $val[$i];
        fputcsv($outputBuffer, $val);
    }
//    fclose($outputBuffer);
//}
?>