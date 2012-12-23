<?php
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
//

//Set page starter variables//
$includeDirectory = "/opt/mmcFE-litecoin/www/includes/";

//Include site functions
include($includeDirectory."requiredFunctions.php");
include($includeDirectory."btceapi2.php");

//update ticker value every 30 minutes
$TICKER_INTERVAL = 30 * 60;

$lastticker = (int) $settings->getsetting('lastticker');
$runtime = time();

if ($runtime - $lastticker >= $TICKER_INTERVAL) {
   
   $result = btce_api2("btc_usd/ticker","getInfo");
   $btcusd = $result["ticker"]["avg"];
   $settings->setsetting('mtgoxlast',$btcusd);
      
   $result = btce_api2("ltc_usd/ticker","getInfo");     
   $ltcusd = $result["ticker"]["avg"];
   $settings->setsetting('ltcusdlast',$ltcusd);
      
   $result = btce_api2("ltc_btc/ticker","getInfo");     
   $ltcbtc = $result["ticker"]["avg"];
   $settings->setsetting('ltcbtclast',$ltcbtc);
   
   $settings->setsetting('lastticker',$runtime);
   
}
?>
