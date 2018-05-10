<?php
$zip_codes = isset($_POST['zips']) ? explode(',', $_POST['zips']) : null;
$to_csv = [];
$headers = [];
$set_date_col = true;
if($zip_codes != null) {
    foreach ($zip_codes as $zip) {
        $csv_rows = explode("\n", file_get_contents("https://www.chabad.org/calendar/candlelighting/candlelighting.csv.asp?aid=6226&locationId=" . $zip . "&locationType=2&tdate=9/1/" . (date("Y")-1) ."&weeks=52"));
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
        <input type="submit" value="Retrieve Candle Lighting Times" />
    </form>
<?php     
}

function add_zip_time_col($row_array, $zip) {
    global $to_csv;
    global $headers;
    $headers[] = $zip;
    $i = 0;
    foreach($row_array as $row) {
        $row = str_getcsv($row);
        if (array_key_exists(1, $row) && array_key_exists(2, $row) && array_key_exists(3, $row) && strpos($row[3], 'Candle') !== false) {
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
        if (array_key_exists(1, $row) && array_key_exists(2, $row) && array_key_exists(3, $row) && strpos($row[3], 'Candle') !== false) {
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