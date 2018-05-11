<?php
$zip_codes = isset($_POST['zips']) ? explode(',', $_POST['zips']) : null;
$to_csv = [];
$headers = [];
$set_date_col = true;
if($zip_codes != null) {
    foreach ($zip_codes as $zip) {
        $csv_rows = explode("\n", file_get_contents("https://www.chabad.org/calendar/candlelighting/candlelighting.csv.asp?aid=6226&locationId=" . $zip . "&locationType=2&tdate=" . $_POST['tdate'] ."&weeks=52"));
        if($set_date_col) {
            add_dates_col($csv_rows);
            $set_date_col = false;
        }
        add_zip_time_col($csv_rows, $zip);
    }
    output_csv($to_csv, $headers); 
} else { ?>
    <h1>Candle Lighting Times By Zip Code</h1>
    <form method="post" action="">
        <textarea name="zips" cols="50" rows="20" placeholder="Enter A Coma Delimited List Of Zip Codes Like 11213,33319,90278"></textarea><br /><br />
        <lable for="tdate">Start Date <input id="tdate" name="tdate" type="text" value="9/1/<?php echo date("Y") ?>"></input></lable><br /><br />
        <lable for="start_times">Get Start Times<input id="start_times" name="start_times" type="checkbox" checked></input></lable><br /><br />
        <lable for="end_times">Get End Times<input id="end_times" name="end_times" type="checkbox"></input></lable><br /><br />
        <input type="submit" value="Retrieve Candle Lighting Times" />
    </form>
<?php     
}

function check_row($row) {
    return array_key_exists(1, $row) && array_key_exists(2, $row) && array_key_exists(3, $row);
}

function is_start_time($row) {
    return isset($_POST['start_times']) && $_POST['start_times'] && strpos($row[3], 'Candle') !== false;
}

function is_end_time($row) {
    return isset($_POST['end_times']) && $_POST['end_times'] && strpos($row[3], 'Ends') !== false;
}

function add_zip_time_col($row_array, $zip) {
    global $to_csv;
    global $headers;
    $headers[] = $zip;
    $i = 0;
    foreach($row_array as $row) {
        $row = str_getcsv($row);
        if (check_row($row) && (is_start_time($row) || is_end_time($row))) {
            array_push($to_csv[$i], $row[2]);
            $i++;
        }
    }
}

function add_dates_col($row_array) {
    global $to_csv;
    global $headers;
    $headers[] = 'Dates';
    foreach($row_array as $row) {
        $row = str_getcsv($row);
        if (check_row($row) && (is_start_time($row) || is_end_time($row))) {
            $to_csv[] = array(0 => trim(strstr($row[1], ' ', false)));
        }
    }
}

function output_csv($data, $headers) {
    $file = "candle_lighting_for_zip" . time() . ".csv";
    $link = fopen($file, "w");
    fputcsv($link, $headers);
    foreach($data as $row){
        fputcsv($link, $row);
    }
    fclose($link);
    header('Content-type: application/csv');
    header("Content-Disposition: inline; filename=".$file);
    readfile($file);
    unlink($file);
}
?>