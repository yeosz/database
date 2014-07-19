<?php
/**
 * 生成orale数据字典
 *
 * @authoer ye.osz@qq.com
 * @version 1.0
 */
include 'oracleDb.class.php';

header("Content-type: text/html; charset=utf-8");
$doc_title = '数据库设计文档';

$config = array('host'=>'//192.168.1.113/orcl','user'=>'PHP','password'=>'PHP');
$db = new oracleDb($config['host'],$config['user'], $config['password']);

//所有表
$sql = "select * from user_tab_comments where TABLE_TYPE='TABLE'";
$result = $db->getAll($sql);
$tables = array();
foreach($result as $id=>$v){
	$tables[$v['TABLE_NAME']] = $v;
}

//所有字段
$sql = "select a.COMMENTS,b.* from user_col_comments a 
		left join user_tab_columns b on a.TABLE_NAME=B.TABLE_NAME AND a.COLUMN_NAME=b.COLUMN_NAME";
$fields = $db->getAll($sql);

//所有主键
$sql = "SELECT a.TABLE_NAME||'.'||A.COLUMN_NAME FROM user_cons_columns a 
		left join user_constraints b on a.constraint_name=b.constraint_name
		WHERE b.constraint_type='P'";
$primary = $db->getCol($sql);

//所有外键
$sql = "SELECT a.TABLE_NAME||'.'||a.COLUMN_NAME as key ,C.OWNER||'.'||c.TABLE_NAME||'.'||c.COLUMN_NAME as value FROM user_cons_columns a 
	left join user_constraints b on a.constraint_name=b.constraint_name
	left join user_cons_columns c on b.r_constraint_name=c.constraint_name
	WHERE b.constraint_type='R'";
$result = $db->getAll($sql);
$foreignkey = array();
foreach($result as $v){
	$foreignkey[$v['KEY']] = $v['VALUE'];	
}

foreach($fields as $id=>$v){
	$tables[$v['TABLE_NAME']]['COLUMN'][] = $v;
}

//print_r($primary);print_r($tables);die;

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
		
		//$primary
		$isPrimary = in_array($v['TABLE_NAME'].'.'.$f['COLUMN_NAME'],$primary) ? '是' : '';
		$isAbleNull = $f['NULLABLE']=='Y' ? '是' : '';
		$foreignkeyStr = isset($foreignkey[$v['TABLE_NAME'].'.'.$f['COLUMN_NAME']]) ? $foreignkey[$v['TABLE_NAME'].'.'.$f['COLUMN_NAME']] : '';
		
		$html .= '<tr>';
		$html .= '<td class="w120">' . $f['COLUMN_NAME'] . '</td>';
		$html .= '<td class="w120">' . $f['DATA_TYPE'].'('.$f['DATA_LENGTH'].')' . '</td>';			
		$html .= '<td class="w80 text-center">' .$f['DATA_DEFAULT'] . '</td>';
		$html .= '<td class="w80 text-center">' . $isAbleNull . '</td>';
		$html .= '<td class="w80 text-center">' . $isPrimary . '</td>';
		$html .= '<td class="w80 text-center">' . $foreignkeyStr . '</td>';
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
