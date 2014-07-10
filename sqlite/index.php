<?php 

$db = new PDO('sqlite:test.db');
//查询
$sql = "select * from test where 1 limit 0,10";
$rs = $db->query($sql);
//$rs->setFetchMode(PDO::FETCH_ASSOC);
$data = $rs->fetchAll(PDO::FETCH_ASSOC);
var_dump($data);
echo count($data);
//查询
$sql = "select * from test where id=200";
$rs = $db->query($sql);
$rs->setFetchMode(PDO::FETCH_ASSOC);
$data = $rs->fetchAll();
var_dump($data);
echo count($data);
//插入
$sql = "insert into test (T_NAME,T_POI,T_ADDRESS,T_PHONE,T_UID) values('tset','22.657457,114.347698','布吉竹头金鹏物流园A区A栋','89570995,13902475348','sf')";
$rs = $db->query($sql);
var_dump($rs);
//删除
$sql = "delete from test where id=200";
$db->query($sql);
$rs = $db->query($sql);
var_dump($rs);


