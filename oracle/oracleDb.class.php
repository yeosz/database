<?php 
/**
 * PHP操作Oracle数据库
 * 
 * =========================================================================
 * 相关资料
 * http://www.php.net/manual/zh/function.oci-connect.php
 * http://www.oracle.com/technetwork/cn/articles/index-087357-zhs.html
 * 
 * =========================================================================
 * 
 * @author ye.osz@qq.com
 * @version 1.0
 * @created 2014-07
 *
 */
class oracleDb{
	
	public	$conn;
	private $error;
	private $ociError;
	private $ociFetchModes;//OCI_BOTH,OCI_ASSOC,OCI_NUM ,OCI_RETURN_NULLS,OCI_RETURN_LOBS
	private $ociExecuteModel;//OCI_COMMIT_ON_SUCCESS||OCI_NO_AUTO_COMMIT
	private $sql;
	
	public function __construct($dbhost='', $dbuser='', $dbpw='',$charset='utf8') {
		$this->conn = oci_connect($dbuser, $dbpw, $dbhost,$charset);  
		if(!$this->conn) {  
			$e = oci_error();  
			die(htmlentities($e['message'])); 
		}

		$this->ociFetchModes = OCI_ASSOC + OCI_RETURN_LOBS + OCI_RETURN_NULLS;
		$this->ociExecuteModel = OCI_COMMIT_ON_SUCCESS;
    }
	
    private function ociExecute($stid,$model=null){
    	if(!oci_execute($stid,$model)){
    		$this->setOciError($stid);
    		oci_free_statement($stid);
    		return false;
    	}else{
    		return true;
    	}
    	 
    }
    
    /**
     * 执行
     * 
     * @param string $sql
     * @return boolean
     */
    public function execute($sql){    	
    	$stid = oci_parse($this->conn, $this->sql = $sql);    	
    	if(!oci_execute($stid)){
    		$this->setOciError($stid);
    		oci_free_statement($stid);
    		return false;
    	}else{
    		return true;
    	}
    	
    }
        
    /**
     * 插入
     * 
     * @param string $table 表名
     * @param array $data 数据
     * @return mixed 插入的ID/结果
     */
    public function insert($table,$data,$bind=array()){
		$this->sql = array("INSERT INTO {$table} (",'',') VALUES (','',')');
		
    	foreach($data as $key=>&$v){
    		$this->sql[1] = $this->sql[1] . $key . ',';
    		if(preg_match('/(to_date\()|(to_char\()|(to_number\()|(\.nextval)/i', $v)){
    			if(preg_match('/(\.nextval)/i',$v)) $sequence = $v;
    			$this->sql[3] .= $v . ',';
    		}else{
    			$this->sql[3] .= ':'. $key . ',';
    		}    		
    	}
    	
    	$this->sql[1] = rtrim($this->sql[1],',');
    	$this->sql[3] = rtrim($this->sql[3],',');
    	$this->sql = implode($this->sql,'');
    	
    	$stid = oci_parse($this->conn,$this->sql);    	
    	foreach($data as $key=>&$v){
    		if(preg_match('/(to_date\()|(to_char\()|(to_number\()|(\.nextval)/i', $v)) continue;
    		/*if(isset($bind[$key])){
    			oci_bind_by_name($stid, ":{$key}", $v,-1,$bind[$key]);   
    		}else{
    			oci_bind_by_name($stid, ":{$key}", $v);    			
    		}*/
    		oci_bind_by_name($stid, ":{$key}", $v);
    	}
    	if($this->ociExecute($stid,$this->ociExecuteModel)){
    		if(isset($sequence)){
    			oci_free_statement($stid);
    			$sql = 'select ' . str_replace(array('nextval','NEXTVAL'), 'CURRVAL', $sequence).' from dual';
    			return  $this->getOne($sql);
    		}    		
    		return true;
    	}else{
    		return false;    		
    	}    	 
    }
    
    /**
     * 更新
     * 
     * @param string $table
     * @param array $data
     * @param string $condition
     * @param array $bind
     * @return number|boolean
     */
    public function update($table,$data,$condition,$bind=array()){
    	$this->sql = "update {$table} set ";    	
    	foreach($data as $key=>&$v){
    		if(preg_match('/(to_date\()|(to_char\()|(to_number\()|(\.nextval)/i', $v)){
    			$this->sql .= $key.'='.$v . ',';
    		}else{
    			$this->sql .= $key.'= :'.$key . ',';
    		}
    	}    	 
    	$this->sql = rtrim($this->sql,',');
    	if(!empty($condition)) $this->sql .= ' WHERE ' .$condition;
    	$stid = oci_parse($this->conn,$this->sql);
    	foreach($data as $key=>&$v){
    		if(preg_match('/(to_date\()|(to_char\()|(to_number\()|(\.nextval)/i', $v)) continue;
    		/*
    		if(isset($bind[$key])){
    			oci_bind_by_name($stid, ":{$key}", $v,-1,$bind[$key]);
    		}else{
    			oci_bind_by_name($stid, ":{$key}", $v);
    		}*/
    		oci_bind_by_name($stid, ":{$key}", $v);
    	}
    	if($this->ociExecute($stid,$this->ociExecuteModel)){
    		$row = oci_num_rows($stid);
    		oci_free_statement($stid);
    		return $row;
    	}else{
    		return false;
    	}
    	
    }
    
	/**
	 * 删除
	 * 
	 * @param string $table
	 * @param string $condition
	 * @return boolean|number
	 */
    public function delete($table,$condition){
    	$this->sql = "delete from {$table}";    	
    	if(!empty($condition)) $this->sql .= ' WHERE ' .$condition;
    	$stid = oci_parse($this->conn,$this->sql); 
    	if(!$this->ociExecute($stid)){
    		return false;
    	}else{
    		$row = oci_num_rows($stid);
    		oci_free_statement($stid);
    		return $row;    		
    	}    	
    }
    
    
	/**
	 * 查询单个字段
	 * 
	 * @param string $sql 
	 * @return mixed 
	 */
	public function getOne($sql){
		$stid = oci_parse($this->conn, $this->sql = $sql);
		if(!$this->ociExecute($stid)) return false;	
		$row =  oci_fetch_array($stid,$this->ociFetchModes);
		oci_free_statement($stid);
		return $row===false ? null : current($row);
	}
	
	/**
	 * 查询，返回二维数组
	 * 
	 * @param string $sql
	 * @return mixed
	 */
	public function getRow($sql){
		$stid = oci_parse($this->conn, $this->sql = $sql);		
		if(!$this->ociExecute($stid)) return false;	
		$row =  oci_fetch_array($stid,$this->ociFetchModes);		
		oci_free_statement($stid);		
		return $row===false ? null : $row;
	}
	
	/**
	 * 查询，返回二维数组
	 * 
	 * @param string $sql
	 * @return mixed
	 */
	public function getAll($sql){
		$stid = oci_parse($this->conn, $this->sql = $sql);		
		if(!$this->ociExecute($stid)) return false;		
		$data = array();
		while (($row =  oci_fetch_array($stid, $this->ociFetchModes)) != false) {
			$data[] = $row;
		}
		oci_free_statement($stid);		
		return empty($data) ? null : $data;
	}
	
	/**
	 * 查询一列
	 * 
	 * @param string $sql
	 * @return mixed
	 */
	public function getCol($sql){
		$stid = oci_parse($this->conn, $this->sql = $sql);
		if(!$this->ociExecute($stid)) return false;
		$data = array();
		while (($row =  oci_fetch_array($stid, $this->ociFetchModes)) != false) {
			$data[] = current($row);
		}
		oci_free_statement($stid);
		return empty($data) ? null : $data;	
	}
	
	/**
	 * 开启事务
	 * 
	 * @return obj
	 */
	public function startTrans(){
		$this->ociExecuteModel = OCI_NO_AUTO_COMMIT;
		return $this;
	}
	
	/**
	 * 回滚事务
	 * 
	 * @return boolean
	 */
	public function rollback(){
		$result = oci_rollback($this->conn);
		$this->ociExecuteModel = OCI_COMMIT_ON_SUCCESS;
		if(!$result){
			$this->setOciError($this->conn);
			return false;
		}
		return true;
	}
	
	/**
	 * 提交事务
	 * 
	 * @return boolean
	 */
	public function commit(){
		$result = oci_commit($this->conn);
		$this->ociExecuteModel = OCI_COMMIT_ON_SUCCESS;
		if(!$result){
			$this->setOciError($this->conn);
			return false;
		}
		return true;
	}	
	
	/**
	 * 获取表的所有字段
	 * 
	 * @param string $table
	 * @return mixed
	 */
	public function getFields($table){		
		$stid = oci_parse($this->conn, $this->sql = "SELECT * FROM {$table}");
		if(!$this->ociExecute($stid, OCI_DESCRIBE_ONLY)) return false;
		$ncols = oci_num_fields($stid);
		$result = array();
		for ($i = 1; $i <= $ncols; $i++) {
			$temp['column']  = oci_field_name($stid, $i);
			$temp['type']  = oci_field_type($stid, $i);		
			$result[] = $temp;
		}
		oci_free_statement($stid);
		return $result;
	}
	
	/**
	 * 设置 Models
	 * 
	 * @param $model OCI_BOTH,OCI_ASSOC,OCI_NUM ,OCI_RETURN_NULLS,OCI_RETURN_LOBS
	 * @return object
	 */
	public function setOciFetchModes($model){
		$this->ociFetchModes = $model;
		return $this;
	}
	
	private function setOciError($stid){		
		$e = oci_error($stid);
		$this->ociError = $e['message'];
		return $this;		
	}
	
	/**
	 * 获取错误
	 * 
	 */
	public function error(){		
		return $this->error ? $this->error : $this->ociError;
	}
	
	public function sql(){
		echo $this->sql ? $this->sql : '';
		return $this;
	}
	
	public function __toString(){
		echo $this->sql ? $this->sql : '';
	}
	
	public function __destruct(){	
		oci_close($this->conn);
	}
	
}
