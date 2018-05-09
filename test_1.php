<?php
$zips = array(11213, 33319);
$all_zips = array();
foreach ($zips as $zip) {
    $data = file_get_contents("https://www.chabad.org/calendar/candlelighting/candlelighting.csv.asp?locationId=" . $zip . "&locationType=2&tdate=1/1/2018&weeks=60");
    $rows = explode("\n", $data);
    foreach($rows as $row) {
        $line = str_getcsv($row);
        
        array_push($all_zips, $line);
    }
}
foreach($all_zips as $row) {

}
var_dump($all_zips);
?>