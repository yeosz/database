<?php
header('Content-Type:text/html;charset=utf-8');
$mc= new Memcache;
$mc->connect('192.168.92.128', 11211);
//die($mc->get('test_name')); 
$key = 't';
// 添加
if( $mc->add($key,'测试',MEMCACHE_COMPRESSED,120) ){//MEMCACHE_COMPRESSED 代表压缩
    die('添加成功');
}else{
    var_dump($mc->get($key)); 
} 
// 修改
$mc->set($key,'测试2',MEMCACHE_COMPRESSED,120);
var_dump($mc->get($key));
// 删除
$mc->delete($key);
var_dump($mc->get($key)); // boolean false
