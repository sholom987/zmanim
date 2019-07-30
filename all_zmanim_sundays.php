<?php

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

    function getAllTimes($zmanim){
        global $headers;
        $reg_ex = "/title>(.+)\-.+?([0-9]{1,}:[0-9]{1,} [A|P]M).+?--.+?([0-9]{1,}\/[0-9]{1,}\/[0-9]{1,}).+?<\/title/";
        preg_match_all($reg_ex, $zmanim, $matches);

        $days = $matches[1];
        $times = $matches[2];
        $dates = $matches[3];

        $resp = array();
        $resp["date"] = $dates[0];
        foreach($dates as $key => $date){
            if(!in_array($days[$key], $headers)){
                $headers[$days[$key]] = $days[$key];
            }
            $resp[$days[$key]] = $times[$key];
        }

        return $resp;
    }

    $all_times_storage = array();
    $times = array();

    if(isset( $_GET['date']) && isset($_GET['count'])){
        $count = $_GET['count'];
        $date = date("m/d/Y", $_GET['date']);
    }
    else{
        $count = 1;
        $start = strtotime("September 1 " . date("Y"));
        $date = date("m/d/Y", $start);
    }

    $zip = trim($zip);
    $file_name = md5($zip.$count) . ".part";
    $max = 400;
    $move_along = false;
    if(!file_exists($file_name) && date_create($date)->format('w') == '0'){
        $zmanim = getZmanim($zip, $date);
        $all_times = getAllTimes($zmanim);
        $link = fopen($file_name, "w");
        fwrite($link, serialize($all_times));
        fclose($link);
    } else {
        $move_along;
    }
    
    if($count <= $max || $move_along){
        $count++;
        $date =  (isset($_GET['date']) ? $_GET['date'] : $start) + (3600*24);
        echo '<h1>In Progress: ' . $count . ' (Go get some coffee... ' . ($max - $count) . ' more to go)</h1><script>parent.location = "' . "?k=" . $_GET['k'] . "&date=" . $date . "&count=" . $count . "&zip=" . $zip        . '";</script>';
        die();
    }
    else{
        $all_times = array();
        for($i = 1; $i <= $max; $i++){
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
        //echo '<a href="' . $file .'">DOWNLOAD</a>';
        
    }

}						