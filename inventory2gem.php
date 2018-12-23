<?php
//$exclude : exclude list(appid => keyword)
define('STEAM_ID', '');
define('STEAM_UID', '');  //user id in number
$cookie = '';
$inventory = urllib('GET', 'https://steamcommunity.com/inventory/' . STEAM_UID . '/753/6?l=schinese&count=5000', array(), array('Cookie' => $cookie));
$inventory = json_decode($inventory, true);
$res = inventory_filter($inventory, array('class' => 4), array('385800' => 'NEKOPARA', '333600' => 'NEKOPARA', '420110' => 'NEKOPARA', '602520' => 'NEKOPARA', '386480' => 'Blood Code', '212200' => 'Mabinogi', '776490' => 'The Disappearing of Gensokyo', '289070' => "Sid Meier's Civilization VI", '546080'=>'Coffin of Ashes', '262390'=>'Cloudbuilt', '552780'=>'Dexodonex', '525610' => 'Hardware Engineering', '567150'=>'Mosaics Galore', '563520'=>'When Our Journey Ends', '455120'=>'Stay Close', '931500'=>'Touhou Genso Wanderer'));
var_dump(count($res));
foreach($res as $r){
	$gem = gem($r, $cookie);
	if($gem){
      echo $r['type'] . ' ' . $gem . "\n";
    } else{
      echo $r['type'] . " Failed\n";
    }
  usleep(500000);
}

function gem($des, $cookie){
  $session = substr($cookie, strpos($cookie,"sessionid=")+10, 24);
	$item_type = 0;
	foreach($des['owner_actions'] as $oa){
		if(stristr($oa['link'], 'javascript:GetGooValue')){
			$item_type = explode(', ', $oa['link'])[3];
		}
	}
	if($item_type == 0){
		return 0;
	}
	for($i=0;$i<5;$i++){
		$goo_value = json_decode(urllib('GET', 'https://steamcommunity.com/auction/ajaxgetgoovalueforitemtype/?appid=' . $des['market_fee_app'] . '&item_type=' . $item_type . '&border_color=0', array(), array('Cookie' => $cookie)), true);
		if($goo_value['success'] == 1){
			$goo_value = $goo_value['goo_value'];
			break;
		}
	}
	if(is_array($goo_value)){
		return 0;
	}
	for($i=0;$i<5;$i++){
		$post_data = array('sessionid' => $session, 'appid' => $des['market_fee_app'], 'assetid' => $des['assetid'], 'contextid' => 6, 'goo_value_expected' => $goo_value);
		$res = json_decode(urllib('POST', 'https://steamcommunity.com/id/' . STEAM_ID . '/ajaxgrindintogoo/', $post_data, array('Cookie' => $cookie)), true);
		if($res['success'] == 1){
			return $res['goo_value_received '];
		}
	}
	return 0;
}

function inventory_filter($inventory, $filter, $exclude = array()){
	$asset = $inventory['assets'];
	$ret = $inventory['descriptions'];
	$tmp = array();
	foreach($asset as $a){
		foreach($ret as $r){
			if($r['appid'] == $a['appid'] && $r['classid'] == $a['classid'] && $r['instanceid'] == $a['instanceid']){
				$r['assetid'] = $a['assetid'];
				$tmp[] = $r;
				break;
			}				
		}
	}
	$ret = $tmp;
	if(isset($filter['tradable'])){	//0不可交易 1可交易
		$tmp = array();
		foreach($ret as $r){
			if($r['tradable'] == $filter['tradable']){
				$tmp[] = $r;
			}
		}
		$ret = $tmp;
	}
	if(isset($filter['marketable'])){	//0不可出售 1可出售
		$tmp = array();
		foreach($ret as $r){
			if($r['marketable'] == $filter['marketable']){
				$tmp[] = $r;
			}
		}
		$ret = $tmp;
	}
	if(isset($filter['droprate'])){	//0普通1罕见2稀有
		$tmp = array();
		foreach($ret as $r){
			if($r['tags'][0]['internal_name'] == 'droprate_' . $filter['droprate']){
				$tmp[] = $r;
			}
		}
		$ret = $tmp;
	}
	if(isset($filter['appid'])){	//appid
		$tmp = array();
		foreach($ret as $r){
			if($r['market_fee_app'] == $filter['appid']){
				$tmp[] = $r;
			}
		}
		$ret = $tmp;
	}
	if(isset($filter['class'])){	//3个人资料背景7宝石4表情2卡牌
		$tmp = array();
		foreach($ret as $r){
			if(end($r['tags'])['internal_name'] == 'item_class_' . $filter['class']){
				$tmp[] = $r;
			}
		}
		$ret = $tmp;
	}
	if(count($exclude) > 0){
		$tmp = array();
		foreach($ret as $r){
          $flag = true;
			foreach($exclude as $appid => $name){
				if($r['market_fee_app'] == $appid || stristr($r['type'], $name)){
					$flag = false;
                    break;
				}
			}
          if($flag){
            $tmp[] = $r;
          }
		}
		$ret = $tmp;
	}
	return $ret;
}


function urllib($function, $url, $data = array(), $header = array()){
	if(!in_array('User-Agent', $header)){
		$header['User-Agent'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36';
	}
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_TIMEOUT, 10);
	curl_setopt($curl, CURLOPT_HEADER, 0);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_COOKIE, $header['Cookie']);
	//curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);  
	//curl_setopt($curl, CURLOPT_PROXY, "127.0.0.1:1080");  
	if($function == 'POST'){
		$data = http_build_query($data);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	}
	curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	$r = curl_exec($curl);
	//var_dump(curl_error($curl));
	//var_dump(curl_errno($curl));
	curl_close($curl);
	return $r;
}
