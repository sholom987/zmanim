<?php

if(!isset($_POST['zips']) || !isset($_POST['dates'])){
 
    ?>
    <form action="?k=<?php echo $_GET['k']; ?>" method="post">
Zips:
<pre>
85014
91020
11213
</pre>
        <br />
        <textarea name="zips" cols="50" rows="20" placeholder="Paste zips here"></textarea>
 
        <br />
        <br />
        <br />
<pre>
Dates:
</pre>
        <br />
        <textarea name="dates" cols="50" rows="20" placeholder="Paste dates here">
10/02/2019
1/7/2020
03/9/2020
4/8/2020
7/09/2020
7/29/2020
7/30/2020
9/21/2020
</textarea>
        <input type="submit" value="Go get em'" />
    </form>
<?php
 
 
}
else {
 
    $zips = explode("\n", $_POST['zips']);
    $dates = explode("\n", $_POST['dates']);
 
    function getZmanim($zip, $date){
        //print_r('http://www.chabad.org/tools/rss/zmanim.xml?locationId=' . $zip . '&locationType=2&tdate=' . $date);
        return file_get_contents('http://www.chabad.org/tools/rss/zmanim.xml?locationId=' . $zip . '&locationType=2&tdate=' . $date);
    }
 
    function getFastTimes($zmanim){
        preg_match('/Fast Begins - ([0-9]{1,}:[0-9]{1,} [A|P]M)/', $zmanim, $matches);
        $start = isset($matches[1]) ? $matches[1] : "";
 
        preg_match('/Fast Ends - ([0-9]{1,}:[0-9]{1,} [A|P]M)/', $zmanim, $matches);
        $end = isset($matches[1]) ? $matches[1] : "";
 
        return (object) array("start" => $start, "end" => $end);
    }
 
    function getPesachTimes($zmanim){
        preg_match('/Finish Eating Chametz before - ([0-9]{1,}:[0-9]{1,} [A|P]M)/', $zmanim, $matches);
        $finish_eating = isset($matches[1]) ? $matches[1] : "";
 
        preg_match('/Sell and Burn Chametz before - ([0-9]{1,}:[0-9]{1,} [A|P]M)/', $zmanim, $matches);
        $sell_burn = isset($matches[1]) ? $matches[1] : "";
 
        return (object) array("finish_eating" => $finish_eating, "sell_burn" => $sell_burn);
    }
 
    $times = array();
    foreach($zips as $zip){
        foreach($dates as $date){
            $zip = trim($zip);
            $date = trim($date);
            $zmanim = getZmanim($zip, $date);
            $fast_times = getFastTimes($zmanim);
            if(!empty($fast_times->start)){
                $times[] = array(
                    $zip, $date, "fast starts", $fast_times->start,
                );
            }
            if(!empty($fast_times->end)){
            $times[] = array(
                $zip, $date, "fast ends", $fast_times->end,
            );
            }
 
            $pesach_times = getPesachTimes($zmanim);
            if(!empty($pesach_times->finish_eating)){
                $times[] = array(
                    $zip, $date, "finish eating chametz by", $pesach_times->finish_eating,
                );
            }
            if(!empty($pesach_times->sell_burn)){
                $times[] = array(
                    $zip, $date, "sell and burn chametz by", $pesach_times->sell_burn,
                );
            }
        }
    }
 
    $file = "zmanim_" . time() . ".csv";
    $link = fopen($file, "w");
    fputcsv($link, array("zip", "date", "info", "time"));
    foreach($times as $time){
        fputcsv($link, $time);
    }
    fclose($link);
    header('Content-type: application/csv');
    header("Content-Disposition: inline; filename=".$file);
    readfile($file);
    unlink($file);
 
 
}                       