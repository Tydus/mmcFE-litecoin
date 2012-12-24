<?php

$includeDirectory = "/opt/mmcFE-litecoin/www/includes/";

include($includeDirectory."requiredFunctions.php");

if (isset($hashrateInterval)) {
   $HASHRATE_INTERVAL = $hashrateInterval;
} else {
   $HASHRATE_INTERVAL = 15 * 60;
}

//Hashrate by worker
$sql =  "SELECT IFNULL(sum(a.id),0) as id, p.username FROM pool_worker p LEFT JOIN ".
                        "((SELECT count(id) as id, username ".
                        "FROM shares ".
                        "WHERE time > DATE_SUB(now(), INTERVAL $HASHRATE_INTERVAL SECOND) ".
                        "GROUP BY username) ".
                "UNION ".
                        "(SELECT count(id) as id, username ".
                        "FROM shares_history ".
                        "WHERE time > DATE_SUB(now(), INTERVAL $HASHRATE_INTERVAL SECOND) ".
                        "GROUP BY username)) a ".
                "ON p.username=a.username ".
                "GROUP BY username";
$result = mysql_query($sql);
while ($resultrow = mysql_fetch_object($result)) {
        $retarget = $rewritePower;
        $hashrate = $resultrow->id;
        $key = bcpow(2,$retarget) or die("bcpow err");
        $hashrate = round((($hashrate*$key)/$HASHRATE_INTERVAL)/1000, 3);
        mysql_query("UPDATE pool_worker SET hashrate = $hashrate WHERE username = '$resultrow->username'");
}

//Total Hashrate (more exact than adding)
$sql =  "SELECT sum(a.id) as id FROM ".
                        "((SELECT count(id) as id FROM shares WHERE time > DATE_SUB(now(), INTERVAL $HASHRATE_INTERVAL SECOND)) ".
                	"UNION ".
                        "(SELECT count(id) as id FROM shares_history WHERE time > DATE_SUB(now(), INTERVAL $HASHRATE_INTERVAL SECOND)) ".
                        ") a ";
$result = mysql_query($sql);
if ($resultrow = mysql_fetch_object($result)) {
        $retarget = $rewritePower;
        $hashrate = $resultrow->id;
        $key = bcpow(2,$retarget) or die("bcpow err");
        $hashrate = round((($hashrate*$key)/$HASHRATE_INTERVAL)/1000, 3);
        mysql_query("UPDATE settings SET value = '$hashrate' WHERE setting='currenthashrate'");
}

//Hashrate by user
$sql = "SELECT u.id, IFNULL(sum(p.hashrate),0) as hashrate ".
                "FROM webUsers u LEFT JOIN pool_worker p ".
                "ON p.associatedUserId = u.id ".
                "GROUP BY id";
$result = mysql_query($sql);
while ($resultrow = mysql_fetch_object($result)) {
        mysql_query("UPDATE webUsers SET hashrate = $resultrow->hashrate WHERE id = $resultrow->id");

        // Enable this for lots of stats for graphing
        if ($resultrow->hashrate > 0) {
                mysql_query("INSERT INTO userHashrates (userId, hashrate) VALUES ($resultrow->id, $resultrow->hashrate)"); // active users hashrate
        }
}

mysql_query("INSERT INTO userHashrates (userId, hashrate) VALUES (0, $hashrate)"); // the pool total hashrate

$currentTime = time();
mysql_query("update settings set value='$currentTime' where setting='statstime'");

// Clean up the userHashrate table (anything older than 4 days)
mysql_query("DELETE FROM userHashrates WHERE timestamp < DATE_SUB(now(), INTERVAL 96 HOUR)");

?>

