<?php
//tools for merge cards
//change $total_page to total pages you got in https://steamcommunity.com/id/{STEAMID}/badges/
define('STEAM_ID', '');
$cookie = '';
$total_page = 5;
for($i=1;$i<$total_page;$i++){
  mergecard($i, $cookie);
}
function mergecard($i, $cookie){
  $session = substr($cookie, strpos($cookie,"sessionid=")+10, 24);
  $x = process_page(urllib('GET', 'https://steamcommunity.com/id/' . STEAM_ID . '/badges/?p='.$i, array(), array('Cookie'=>$cookie)));
  foreach($x as $s){
    for($q=0;$q<5;$q++){
			$res = urllib('POST', 'https://steamcommunity.com/id/' . STEAM_ID . '/ajaxcraftbadge/', array('appid'=>$s, 'series'=>1,'border_color'=>0,'sessionid'=>$session), array('Cookie'=>$cookie));
			$res = json_decode($res, true);
			if($res['success'] == 1){
				echo $res['Badge']['game']. ' '. count($res['rgDroppedItems']) . "\n";
			}
	 }
  }
}
function process_page($content){
  //file_put_contents('xx.html',$content);die;
	$ret = array();
	$a = explode('											Ready										', $content);
	foreach($a as $t){
		$tmp = substr($t, -101, 101);
		$tmp = explode('/', $tmp);
		foreach($tmp as $q){
			if(is_numeric($q)){
				$ret[] = $q;
				break;
			}
		}
	}
	return $ret;
}

function urllib($function, $url, $data = array(), $header = array(), $timedout = 10){
	if(!in_array('User-Agent', $header)){
		$header['User-Agent'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36';
	}
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_TIMEOUT, $timedout);
	curl_setopt($curl, CURLOPT_HEADER, 0);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	if(isset($header['Cookie'])){
		curl_setopt($curl, CURLOPT_COOKIE, $header['Cookie']);
		unset($header['Cookie']);
	}
	if(isset($header['Referer'])){
		curl_setopt($curl, CURLOPT_REFERER, $header['Referer']);
		unset($header['Referer']);
	}
	$curl_header = array();
	foreach($header as $k => $v){
		$curl_header[] = $k . ": " . $v;
	}
	//curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);  
	//curl_setopt($curl, CURLOPT_PROXY, "127.0.0.1:1080");  
	if($function == 'POST'){
		$data = http_build_query($data);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	}
	curl_setopt($curl, CURLOPT_HTTPHEADER, $curl_header);
	$r = curl_exec($curl);
	//var_dump(curl_error($curl));
	//var_dump(curl_errno($curl));
	curl_close($curl);
	return $r;
}
