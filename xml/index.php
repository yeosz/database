<?php

$doc = simplexml_load_file('LocList.xml');

/*
$cou = $doc->CountryRegion;
$country = array();
foreach($cou as $id=>$v){	
	$temp = $v->attributes();
	$str = (string) $temp['Name'];
	if($str=='中国') continue;
	$country[$str] = array();
	$city = $v->State->City;
	if(empty($city)) continue;
	foreach($city as $vv){
		$tem = $vv->attributes();
		$country[$str][] = (string)$tem['Name'];
	}
};
print_r($country);
*/


$arr = object2array($doc);
print_r($arr);die;



function object2array($object) {
	return @json_decode(@json_encode($object),1);
}








