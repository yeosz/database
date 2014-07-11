<?php

include 'oracleDb.class.php';

header("Content-type: text/html; charset=utf-8");
$doc_title = '数据库设计文档';

$config = array('host'=>'//192.168.1.113/orcl','user'=>'ZENWAY','password'=>'ZENWAY');
$db = new oracleDb($config['host'],$config['user'], $config['password']);


$sql = "select * from user_tab_comments where TABLE_TYPE='TABLE'";
$result = $db->getAll($sql);
$tables = array();
foreach($result as $id=>$v){
	$tables[$v['TABLE_NAME']] = $v;
}


$sql = "select a.COMMENTS,b.* from user_col_comments a left join user_tab_columns b on a.TABLE_NAME=B.TABLE_NAME AND a.COLUMN_NAME=b.COLUMN_NAME";
$fields = $db->getAll($sql);


foreach($fields as $id=>$v){
	$tables[$v['TABLE_NAME']]['COLUMN'][] = $v;
}

$html = '';
//循环所有表
foreach ($tables as $k=>$v) {
	$html .= "\n";

	$html .= '<table>';
	$html .= '<thead>';
	$html .= '<tr><th colspan="7">' . $v['COMMENTS'] .'&nbsp;'. $v['TABLE_NAME']. ' </th></tr>';
	$html .= '<tr>';
	$html .= '<td>字段名</td>';
	$html .= '<td>数据类型</td>';
	$html .= '<td>默认值</td>';
	$html .= '<td>允许非空</td>';
	$html .= '<td>是否主键</td>';
	$html .= '<td>外键关系</td>';
	$html .= '<td>备注</td>';
	$html .= '</tr>';
	$html .= '</thead><tbody>';

	foreach ($v['COLUMN'] as $f) {
		
		
		$html .= '<tr>';
		$html .= '<td class="w120">' . $f['COLUMN_NAME'] . '</td>';
		$html .= '<td class="w120">' . $f['DATA_TYPE'].'('.$f['DATA_LENGTH'].')' . '</td>';			
		$html .= '<td class="w80 text-center">' .$f['DATA_DEFAULT'] . '</td>';
		$html .= '<td class="w80 text-center">' . $f['NULLABLE'] . '</td>';
		$html .= '<td class="w80 text-center">' . '' . '</td>';
		$html .= '<td class="w80 text-center">' . '' . '</td>';
		$html .= '<td class="w300">' . $f['COMMENTS'] . '</td>';
		$html .= '</tr>';
		
	}
	$html .= '</tbody>';
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
