<?php
//DEMO

error_reporting(0);
include 'oracleDb.class.php';

$db = new oracleDb('//192.168.1.113/orcl','zenway', 'zenway');

echo '插入';
$data = array(
			'ID'=>'TEST_SEQUENCE.NEXTVAL',
			'SS'=>'sss',
			'DD'=>"TO_DATE('2014-05-05 04:05','yyyy-mm-dd hh24:mi')",
			'TE'=>'3332333333333333333333333333333333333333333333333333333333333'
		);

$result = $db->table('TEST')->insert($data);
var_dump($result);
var_dump($db->error());

echo '更新';
$data['SS'] = 'SSSSSSSSSSSSSS';
unset($data['ID']);
$result = $db->update($data,'id='.$result);
var_dump($result);
var_dump($db->error());

echo '查询';
$sql = "select * from TEST ORDER BY ID DESC";
$data = $db->getRow($sql);

var_dump($data);
var_dump($db->error());

echo '事务';
$data = array(
			'ID'=>'TEST_SEQUENCE.NEXTVAL',
			'SS'=>'sss',
			'DD'=>"TO_DATE('2014-05-05 04:05','yyyy-mm-dd hh24:mi')",
			'TE'=>'3332333333333333333333333333333333333333333333333333333333333'
		);
$db->startTrans()->insert($data);
$data = array('ID'=>'TEST_SEQUENCE.NEXTVAL','SS'=>'sss','DD'=>"TO_DATE(5,'yyyy-mm-dd hh24:mi')",'TE'=>'333');
$result = $db->insert($data);

if($result){
	echo 'commit';
	$db->commit();
}else{
	echo 'rollback';
	$db->rollback();
}

echo '<br />删除';
$result = $db->delete('id=1');
var_dump($result);
var_dump($db->error());

echo '查询';
$sql = "select * from TEST ORDER BY ID DESC";
$data = $db->getAll($sql);
var_dump($data);
var_dump($db->error());

