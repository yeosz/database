<?php
/**
 * 生成mysql数据文档（字段+主外键关系+触发器）
 * 
 * @authoer ye.osz@qq.com
 * @version 4.0 
 */
$doc_title = '数据库设计文档';
header("Content-type: text/html; charset=utf-8");
//配置数据库
$dbserver   = 'localhost';
$dbusername = 'php';
$dbpassword = 'php';
$database   = 'php';
//其他配置
$mysql_conn = @mysql_connect($dbserver, $dbusername, $dbpassword) or die('MySQL connect is error');
mysql_select_db($database, $mysql_conn);
mysql_query('SET NAMES UTF8');
$no_show_table = array();    //不需要显示的表
$no_show_field = array();   //不需要显示的字段,二维数组，表名为KEY
//取得所有的表名
$sql = "SELECT TABLE_NAME,TABLE_COMMENT,TABLE_TYPE,ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA='{$database}'";
$table_result = mysql_query($sql, $mysql_conn);
while($row = mysql_fetch_array($table_result,MYSQL_ASSOC)){
	if(!in_array($row['TABLE_NAME'],$no_show_table)){
		$tables[] = array('TABLE_NAME'=>$row['TABLE_NAME'],'TABLE_COMMENT'=>$row['TABLE_COMMENT'],'TABLE_TYPE'=>$row['TABLE_TYPE'],'ENGINE'=>$row['ENGINE']);
	}
}
//获取所有主键
$sql = "SELECT TABLE_SCHEMA,TABLE_NAME,COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE CONSTRAINT_NAME='PRIMARY' AND TABLE_SCHEMA='{$database}'";
$primary_result= mysql_query($sql, $mysql_conn);
$primary = array();
while ($t = mysql_fetch_array($primary_result,MYSQL_ASSOC) ) {
        $primary[] = $t['TABLE_SCHEMA'].'.'.$t['TABLE_NAME'].'.'. $t['COLUMN_NAME'];
}
//print_r($primary);die;
//取得所有外键
$sql = "SELECT concat(table_name, '.', column_name) AS foreignkey,concat(REFERENCED_TABLE_SCHEMA,'.',REFERENCED_TABLE_NAME,'.',REFERENCED_COLUMN_NAME) AS field
	FROM information_schema.KEY_COLUMN_USAGE
	WHERE table_schema = '{$database}' AND REFERENCED_TABLE_NAME IS NOT NULL";
$foreignkey_result= mysql_query($sql, $mysql_conn);
$foreignkey = array();
while ($t = mysql_fetch_array($foreignkey_result,MYSQL_ASSOC) ) {
        $foreignkey[$t['foreignkey']] = str_replace($database.'.','',$t['field']);
}
//print_r($foreignkey);die;
//取得所有的触发器
$triggers = $temp = array();
$sql = "show TRIGGERS";
$triggers_result = mysql_query($sql, $mysql_conn);
while($row = mysql_fetch_array($triggers_result,MYSQL_ASSOC)){
	$temp[] = $row;	
}
foreach($temp as $v){
	$triggers[$v['Table']][] = array('name'=>$v['Trigger'],'event'=>$v['Event'],'tatement'=>$v['Statement'],'timing'=>$v['Timing']);
}
//取得所有索引
$sql = "SELECT t.table_name,t.index_name,GROUP_CONCAT(t.column_name) AS column_name,t.index_type,t.non_unique FROM
	( SELECT table_name,index_name,column_name,index_type,non_unique FROM information_schema.STATISTICS WHERE INDEX_SCHEMA = '{$database}' AND index_name!='PRIMARY' ORDER BY index_name ASC, seq_in_index ASC ) t 
	GROUP BY t.table_name, t.index_name";
$index_result = mysql_query($sql, $mysql_conn);
$index = array();
while ($t = mysql_fetch_array($index_result, MYSQL_ASSOC) ) {
	if(!isset($index[$t['table_name']])) $index[$t['table_name']] = array();
    $index[$t['table_name']][] = $t;
}
//print_r($index);die;
//循环取得所有表的备注及表中列消息
foreach ($tables as $k=>$v) {
    $sql  = 'SELECT * FROM ';
    $sql .= 'INFORMATION_SCHEMA.COLUMNS ';
    $sql .= 'WHERE ';
    $sql .= "table_name = '{$v['TABLE_NAME']}' AND table_schema = '{$database}'";
    $fields = array();
    $field_result = mysql_query($sql, $mysql_conn);
    while ($t = mysql_fetch_array($field_result,MYSQL_ASSOC) ) {
        $fields[] = $t;
    }
    $tables[$k]['COLUMN'] = $fields;
}
mysql_close($mysql_conn);
//print_r($tables);die;
$html = '';
//循环所有表
foreach ($tables as $k=>$v) {
    $html .= "\n";	
    $html .= '<table>';	
	//字段
    $html .= '<thead>';
	$html .= '<tr><th colspan="8">' . $v['TABLE_COMMENT'] .'&nbsp;'. $v['TABLE_NAME']. ' </th><th>'.$v['ENGINE'].'</th></tr>';
	$html .= '<tr>';
	$html .= '<td>序号</td>';
	$html .= '<td>字段名</td>';
	$html .= '<td>数据类型</td>';
	$html .= '<td>默认值</td>';
	$html .= '<td>允许非空</td>';
	$html .= '<td>自动递增</td>';
	$html .= '<td>是否主键</td>';
	$html .= '<td>外键关系</td>';
	$html .= '<td>备注</td>';
	$html .= '</tr>';
	$html .= '</thead><tbody>';	
    foreach ($v['COLUMN'] as $r=>$f) {
		if(!isset($no_show_field[$v['TABLE_NAME']]) || !is_array($no_show_field[$v['TABLE_NAME']])){
			$no_show_field[$v['TABLE_NAME']] = array();
		}
		$primaryStr = in_array($f['TABLE_SCHEMA'].'.'.$f['TABLE_NAME'].'.'.$f['COLUMN_NAME'],$primary) ? '是' : '';
		$foreignkeyStr = isset($foreignkey[$f['TABLE_NAME'].'.'.$f['COLUMN_NAME']]) ? $foreignkey[$f['TABLE_NAME'].'.'.$f['COLUMN_NAME']] : '';
		if(!in_array($f['COLUMN_NAME'],$no_show_field[$v['TABLE_NAME']])){
			$html .= '<tr>';
			$html .= '<td class="w50 text-center">' . ($r+1) . '</td>';
			$html .= '<td class="w120">' . $f['COLUMN_NAME'] . '</td>';
			$html .= '<td class="w120">' . $f['COLUMN_TYPE'] . '</td>';
			$html .= '<td class="w80 text-center">' . $f['COLUMN_DEFAULT'] . '</td>';
			$html .= '<td class="w80 text-center">' . $f['IS_NULLABLE'] . '</td>';
			$html .= '<td class="w80 text-center">' . ($f['EXTRA']=='auto_increment'?'是':'&nbsp;') . '</td>';
			$html .= '<td class="w80 text-center">' . $primaryStr . '</td>';
			$html .= '<td class="w300">' . $foreignkeyStr . '</td>';
			$html .= '<td class="w300">' . $f['COLUMN_COMMENT'] . '</td>';
			$html .= '</tr>';
		}
    }
    $html .= '</tbody>';
	
	//触发器
	if(isset($triggers[$v['TABLE_NAME']])){
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<td colspan="2">触发器名称</td>';
		$html .= '<td>触发</td>';
		$html .= '<td>类型</td>';
		$html .= '<td colspan="5">定义</td>';
		$html .= '</tr>';
		$html .= '</thead><tbody>';
		foreach($triggers[$v['TABLE_NAME']] as $t){
			$html .= '<tr>';
			$html .= '<td colspan="2" class="w120">' . $t['name'] . '</td>';
			$html .= '<td class="w120 text-center">' . $t['timing'] . '</td>';
			$html .= '<td class="w80 text-center">' . $t['event'] . '</td>';
			$html .= '<td colspan="5">' . $t['tatement'] . '</td>';
			$html .= '</tr>';	
			$html .= '</tbody>';
		}
	}
	// 索引
	if(isset($index[$v['TABLE_NAME']])){
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<td colspan="2">索引名称</td>';
		$html .= '<td>唯一索引</td>';
		$html .= '<td>索引类型</td>';
		$html .= '<td colspan="5">字段</td>';
		$html .= '</tr>';
		$html .= '</thead><tbody>';
		foreach($index[$v['TABLE_NAME']] as $t){
			$html .= '<tr>';
			$html .= '<td colspan="2" class="w120">' . $t['index_name'] . '</td>';			
			$html .= '<td class="w80 text-center">' . ($t['non_unique'] ? '是' : '否') . '</td>';
			$html .= '<td class="w120 text-center">' . $t['index_type'] . '</td>';
			$html .= '<td colspan="5">' . $t['column_name'] . '</td>';
			$html .= '</tr>';	
			$html .= '</tbody>';
		}
	}
	
	$html .= '</table>'."\n";
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $doc_title?></title>
<style>
body, td, th { font-family: "微软雅黑"; font-size: 14px; }
.warp{margin:auto; width:80%;}
.warp h3{margin:0px; padding:0px; line-height:30px; margin-top:10px;}
table { border-collapse: collapse; border: 1px solid #000; background: #efefef; margin-bottom:20px; }
table thead th { background-color:#d3d3d3;text-align: left; font-weight: bold; height: 30px; line-height: 30px; font-size: 16px; border: 1px solid #000; padding:5px;}
table thead td { background-color:#d3d3d3;text-align: left; font-weight: bold; height: 26px; line-height: 26px; font-size: 14px; text-align:center; border: 1px solid #000; padding:5px; color:grey;}
table td { height: 20px; font-size: 14px; border: 1px solid #000; background-color: #fff; padding:5px;}
.w120 { width: 120px; }
.w80 { width: 80px; }
.w50 { width: 50px; }
.w300 { width: 300px; }
.text-center{text-align:center;}
</style>
</head>
<body>
<div class="warp">
	<h1 style="text-align:center;"><?php echo $doc_title?></h1>
<?php echo $html; ?>
</div>
</body>
</html>