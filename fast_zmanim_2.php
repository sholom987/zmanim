<?php
$heb_cal_dates_json = json_decode(file_get_contents('http://www.hebcal.com/hebcal/?v=1&cfg=json&mf=on&year=now&geo=zip&zip=33319&c=0&m=0&maj=on&min=on'));
$hebcal_items = $heb_cal_dates_json -> {'items'};
$hebcal_fast_title = array("sdfgsdfgsdfgsdfgsdfg", "Erev Tish'a B'Av", "Tish'a B'Av", "Yom Kippur", "Erev Yom Kippur");
$fast_dates = array();
foreach ($hebcal_items as $key => $value) {
    if((array_key_exists('subcat', $value) && $value -> {'subcat'}  == 'fast') || array_search($value -> {'title'}, $hebcal_fast_title, true)){
        array_push($fast_dates, $value);
    }
}
if(!isset($_POST['zips']) && !isset($_GET['zip'])){

    ?>
    <form action="?k=<?php echo $_GET['k']; ?>" method="post">
Zip code:
<pre>
85014
</pre>
        <br />
        <textarea name="zips" cols="50" rows="20" placeholder="Paste zip here"></textarea>

        <br />
        <br />
        <br />
<pre>
Date range is 9/1/<?php echo date("Y"); ?> - 10/1/<?php echo (date("Y")+1); ?>
</pre>
        <br />
        <input type="submit" value="Go get em'" />
    </form>
<?php


}
else {

    $headers = array();
    $zip = isset($_POST['zips']) ? $_POST['zips'] : $_GET['zip'];

    function getZmanim($zip, $date){
        //print_r('http://www.chabad.org/tools/rss/zmanim.xml?locationId=' . $zip . '&locationType=2&tdate=' . $date);
        return file_get_contents('http://www.chabad.org/tools/rss/zmanim.xml?locationId=' . $zip . '&locationType=2&tdate=' . $date);
    }

    function getAllTimes($zmanim, $fast_date){
        global $headers;
        $reg_ex = "/title>(.+)\-.+?([0-9]{1,}:[0-9]{1,} [A|P]M).+?--.+?([0-9]{1,}\/[0-9]{1,}\/[0-9]{1,}).+?<\/title/";
        preg_match_all($reg_ex, $zmanim, $matches);

        $days = $matches[1];
        $times = $matches[2];
        $dates = $matches[3];
        $resp = array();
        $resp["date"] = $fast_date -> {'date'};
        $resp["title"] = $fast_date -> {'title'};
        foreach($dates as $key => $date){
            if(!in_array($days[$key], $headers)){
                $headers[$days[$key]] = $days[$key];
            }
            $resp[$days[$key]] = $times[$key];
        }

        return $resp;
    }
    $times = array();
    $count=0;
    foreach($fast_dates as $fast_date) {
        $zip = trim($zip);
        $file_name = md5($zip.$count++) . ".part";
        if(!file_exists($file_name)){
            $date = date_create($fast_date -> {'date'}) -> format("m/d/Y");
            $zmanim = getZmanim($zip, $date);
            $all_times = getAllTimes($zmanim, $fast_date);
            $link = fopen($file_name, "w");
            fwrite($link, serialize($all_times));
            fclose($link);
        }
    }
    $all_times = array();
    for($i = 1; $i <= $count; $i++){
        $file_name = md5($zip.$i) . ".part";
        if(file_exists($file_name)){
            $all_times[] = unserialize(file_get_contents($file_name));
            unlink($file_name);
        }
    }
    $headers = array();
    foreach($all_times as $all_time){
        foreach($all_time as $k => $v){
            $headers[$k] = $k;
        }
    }
    $times = array();
    foreach($all_times as $key => $all_time){
        foreach($headers as $header){
            $times[$key][$header] = isset($all_time[$header]) ? $all_time[$header] : "";
        }
    }

    $file = "all_zmanim_" . time() . ".csv";
    $link = fopen($file, "w");
    fputcsv($link, $headers);
    foreach($times as $time){
        fputcsv($link, $time);
    }
    fclose($link);
    header('Content-type: application/csv');
    header("Content-Disposition: inline; filename=".$file);
    readfile($file);
    unlink($file);

}						