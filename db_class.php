<?php
/*
*	 <php database class for multiple type of database>
*    Copyright (C) <2013>  <Chao Zheng>
*
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation, either version 3 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
*    Database class
*    @author    Chao Zheng <chadzhengwork@gmail.com>
*    @copyright Chao Zheng
*	 @version   0.0.1
*/
class db_class{
	/*
	*variable for database types accepted
	*	mssql : Microsoft Sql Server
	*	mysql : Mysql (PHP version under 5)
	*	mysqli: Mysql (PHP version 5 or higher)
	*	odbc  : ODBC
	*/
	var $db_types					=array (
											"mssql",
											"mysql",
											"mysqli",
											"odbc"
											);
	/*
	*variable for odbc types accepted
	*	mssql : Microsoft Sql Server
	*	mysql : Mysql
	*/
	var $odbc_types					=array (
											"mssql",
											"mysql"
											);		
	
	var $db_type					="";    //variable for database type
	var $func_connect				="";    //variable for database connect function_name
	var $func_close					="";	//variable for database close connect function name
	var $func_select_db				="";	//variable for database select db function name
	var $func_query					="";	//variable for database query function name
	var $func_error					="";	//variable for database error function name
	var $func_error_nr				="";	//variable for database error function name
	var $func_fetch_array			="";	//variable for database fetch array function name
	var $func_fetch_obj				="";	//variable for database fetch object function name
	var $func_num_rows				="";	//variable for database num_rows function name
	var $func_insert_id				="";	//variable for database insert_id function name
	var $func_transaction_start 	="";	//variable for database transaction start function name
	var $func_transaction_commit 	="";	//variable for database transaction commit function name
	var $func_transaction_rollback 	="";	//variable for database transaction rollback function name
	var $func_escape_string 		="";	//variable for database connect function name
	
	var $databace_config;					//variable for hold all config veriables
	var $host;								//variable for host name or odbc dns
	var $database;							//variable for database name
	var $user;								//variable for database username
	var $password;							//variable for database password
	var $port;								//variable for database port number
	var $odbc_type					="";    //variable for odbc type
	
	var $sql;								//variable for sql statement
	var $db_link;                   		//variable for db link
	var $con_string;                		//variable for connection string
	var $resource_id;               		//variable for resource id
	var $isConnect;							//variable for connection status
	
	var $error_nr;							//variable for error code
	var $error;								//variable for error message
	
	var $debug 						=false; //debug mode off
	
	/**
	* Constructor of class - Initializes class and connects to the database
	* @param $databace_config array which contain all the parameter needed
	* $databace_config = array (
	 							'db_type'	=>$db_type,
								'host' 		=>$host,
								'database'	=>$database,
								'user'		=>$user,
								'password'	=>$password,
								'port'		=>$port,		//(optional)
								'odbc_type'	=>$odbc_type	//(optional only when odbc, defualt mssql )
								'debug'		=>$debug		//(optional true on, false off, defualt false)
								);
	* @param string $host the host of the database
	* @param string $database the name of the database
	* @param string $user the name of the user for the database
	* @param string $password the passord of the user for the database
	* @param string $port the port of the database (optional)
	* @param string $odbc_type the type of the odbc (optional only when odbc, defualt mssql )
	* @param string $debug switch for debug mode (optional true on, false off, defualt false)
	* @desc Constructor of class - Initializes class and connects to the database.
	*
	*/
	function __construct($databace_config){
		
		$this->db_type	= strtolower(isset($databace_config['db_type'])?$databace_config['db_type']:"");
		$this->host		= isset($databace_config['host'])?$databace_config['host']:"";
		$this->database	= isset($databace_config['database'])?$databace_config['database']:"";
		$this->user		= isset($databace_config['user'])?$databace_config['user']:"";
		$this->password	= isset($databace_config['password'])?$databace_config['password']:"";
		$this->port		= isset($databace_config['port'])?$databace_config['port']:false;
		$this->odbc_type= isset($databace_config['odbc_type'])?$databace_config['odbc_type']:"mssql";
		$this->debug	= isset($databace_config['debug'])?$databace_config['debug']:false;
		
		// Setting database type and connect to database
		if(!empty($this->db_type) && in_array($this->db_type, $this->db_types)){
			$this->func_connect				=$this->db_type."_connect";
			$this->func_close				=$this->db_type."_close";
			$this->func_select_db			=$this->db_type."_select_db";
			$this->func_num_rows			=$this->db_type."_num_rows";
			$this->func_fetch_obj			=$this->db_type."_fetch_object";
			$this->func_transaction_start 	=$this->db_type."_autocommit";
			$this->func_transaction_commit 	=$this->db_type."_commit";	
			$this->func_transaction_rollback=$this->db_type."_rollback";
			$this->func_escape_string		=$this->db_type."_real_escape_string";
			
			if($this->db_type=="odbc"){
				$this->func_query			=$this->db_type."_exec";
				$this->func_fetch_array		=$this->db_type."_fetch_row";
				$this->func_error			=$this->db_type."_errormsg";
				$this->func_error_nr		=$this->db_type."_error";
			}else{
				$this->func_query			=$this->db_type."_query";
				$this->func_fetch_array		=$this->db_type."_fetch_array";
				$this->func_error			=$this->db_type."_error";
				$this->func_error_nr		=$this->db_type."_errno";
				$this->func_insert_id		=$this->db_type."_insert_id";
			}
			
			$this->isConnect = $this->connect();
		}else{
			$this->halt("Database type not supported");
		}
	}
	
	/**
	* This function connects the database
	* @return mix: true $db_link if connection was successful otherwise false
	* @desc This function connects to the database which is set in the constructor
	*/
	function connect(){
		// Selecting connection function and connecting
		if($this->db_link==""){
			// With port
			if($this->port){
				if($this->db_type=="mysql"){
					$this->db_link=call_user_func($this->func_connect,$this->host.":".$this->port,$this->user,$this->password);
				}else if($this->db_type=="mysqli"){
					$this->db_link=call_user_func($this->func_connect,$this->host,$this->user,$this->password,$this->port);
				}else if($this->db_type=="mssql"){
					$this->db_link=call_user_func($this->func_connect,$this->host.",".$this->port,$this->user,$this->password);
				}
			}
			// Without port
			else{
				if($this->db_type=="mysql"){
					$this->db_link=call_user_func($this->func_connect,$this->host,$this->user,$this->password);
				}else if($this->db_type=="mysqli"){
					$this->db_link=call_user_func($this->func_connect,$this->host,$this->user,$this->password);
				}else if($this->db_type=="mssql"){
					$this->db_link=call_user_func($this->func_connect,$this->host,$this->user,$this->password);
				}else if($this->db_type=="odbc"){
					$this->db_link=call_user_func($this->func_connect,$this->host,$this->user,$this->password);
				}
			}
			
			if(!$this->db_link){
				$this->halt("Wrong connection data! Can't establish connection to host.");
				return false;
			}else{
				if($this->db_type!="odbc"){
					//need select database
					if($this->db_type=="mysqli"){
						$result = call_user_func($this->func_select_db,$this->db_link,$this->database);
					}else{
						$result = call_user_func($this->func_select_db,$this->database,$this->db_link);
					}
					if(!$result){
						$this->halt("Wrong database data! Can't select database.");
						return false;
					}else{
						$this->notice("Database connection established!");
						return $this->db_link;
					}
				}else{
					if(empty($this->odbc_type)){
						$this->halt("Wrong database data! Can't select database.");
						return false;
					}else{
						$this->notice("Database connection established!");
						return $this->db_link;
					}
				}
			}
		}else{
			$this->halt("Already connected to database.");
			return false;
		}
	}
	
	/**
	* This function disconnects from the database
	* @desc This function disconnects from the database
	*/
	function disconnect(){
		if($this->db_type != 'odbc'){
			if(call_user_func($this->func_close,$this->db_link)){
				$this->notice("Database dissconnected!");
				return true;
			}else{
				$this->halt("Not connected yet!");
				return false;
			}
		}
	}
	
	/**
	* This function starts the sql query
	* @param string $sql_statement the sql statement
	* @return $rescources on successfull returns false on errors otherwise true
	* @desc This function disconnects from the database
	*/
	function query($sql_statement){
		$this->sql= $sql_statement;
		$this->notice("SQL statement: ".$this->sql);			
		if($this->db_type=="odbc" || $this->db_type=="mysqli"){
			$this->resource_id=call_user_func($this->func_query,$this->db_link,$this->sql);
		}else{
			$this->resource_id=call_user_func($this->func_query,$this->sql,$this->db_link);
		}
		if (!$this->resource_id) {
			$this->halt("invalid query");
		}else{
			$this->notice("query successful");
		}
		return $this->resource_id;
	}
	
	/**
	* This function returns the last error
	* @return string $error the error as string
	* @desc This function returns the last error
	*/
	function get_error(){
		return call_user_func($this->func_error,$this->db_link);
	}
	
	/**
	* This function returns the last error id
	* @return int $error the error as integer
	* @desc This function returns the last error id
	*/
	function get_error_nr(){
		return call_user_func($this->func_error_nr,$this->db_link);
	}
	
	/**
	* This function returns a array row of the resultset
	* @return array $row the row as array or false if there is no more row
	* @desc This function returns a row of the resultset
	*/
	function get_row($resource_id){
		if($this->db_type=="odbc"){
			// ODBC database
			if($row=call_user_func($this->func_fetch_array,$resource_id)){				
				for ($i=1; $i<=odbc_num_fields($resource_id); $i++) {
					$fieldname=odbc_field_name($resource_id,$i);
					$row_array[$fieldname]=odbc_result($resource_id,$i);
				}
				return $row_array;
			}else{
				return false;
			}
		}else{
			// All other databases
			return call_user_func($this->func_fetch_array,$resource_id);
		}
	}
	
	/**
	* This function returns a object row of the resultset
	* @return object $row the row as object or false if there is no more row
	* @desc This function returns a object row of the resultset
	*/
	function get_row_obj(){
		return call_user_func($this->func_fetch_obj,$this->resource_id);
	}
	
	/**
	* This function returns number of rows in the resultset
	* @return int $row_count the nuber of rows in the resultset
	* @desc This function returns number of rows in the resultset
	*/
	function count_rows(){
		$row_count=call_user_func($this->func_num_rows, $this->resource_id);
		if($row_count>=0){
			return $row_count;
		}else{
			$this->halt("Can't count rows before query was made");
			return false;
		}
	}
	
	/**
	* 
	* This function returns last insert id in table
	* @return int $last_insert_id , 0 or false on fail.
	* @desc This function returns last insert id in the table
	*/
	function get_last_insert_id(){
		if($this->db_type == "odbc"){
			if($this->odbc_type == "mssql"){
				$sql = "SELECT @@IDENTITY AS id";
			}else if($this->odbc_type == "mysql"){
				$sql = "SELECT LAST_INSERT_ID() as id";
			}
			$id =false;
			$r = $this->query($sql);
			while($row = $this->get_row($r)){
				$id = $row['id'];
			}
			return $id;
		}else{
			return call_user_func($this->func_insert_id, $this->db_link);
		}
	}
	
	/**
	* This function start a transaction in database
	* @desc This function start a transaction in database
	*/
	function transaction_start(){
		if($this->db_type == "odbc" || $this->db_type == "mysqli"){
			$r = call_user_func($this->func_transaction_start, $this->db_link, false);
			if($r !== 0){
				$this->notice("TRANSACTION START");
			}else{
				$this->halt("TRANSACTION START fail");
			}
		}else{
			if($this->db_type == "mysql"){
				$sql = "START TRANSACTION;";
			}else if($this->db_type == "mssql"){
				$sql = "BEGIN TRANSACTION;";
			}
			$r = $this->query($sql);
			if($r){
				$this->notice("TRANSACTION START");
			}else{
				$this->halt("TRANSACTION START fail");
			}
		}
	}
	
	/**
	* This function commit a transaction in database
	* @desc This function commit a transaction in database
	*/
	function commit(){
		if($this->db_type == "odbc" || $this->db_type == "mysqli"){
			$r = call_user_func($this->func_transaction_commit, $this->db_link);
			if($r){
				$this->notice("TRANSACTION COMMIT");
			}else{
				$this->halt("TRANSACTION COMMIT fail");
			}
		}else{
			if($this->db_type == "mysql"){
				$sql = "COMMIT;";
			}else if($this->db_type == "mssql"){
				$sql = "COMMIT TRANSACTION;";
			}
			$r = $this->query($sql);
			if($r){
				$this->notice("TRANSACTION COMMIT");
			}else{
				$this->halt("TRANSACTION COMMIT fail");
			}
		}
	}
	
	/**
	* This function rollback a transaction in database
	* @desc This function rollback a transaction in database
	*/
	function rollback(){
		if($this->db_type == "odbc" || $this->db_type == "mysqli"){
			$r = call_user_func($this->func_transaction_rollback, $this->db_link);
			if($r){
				$this->notice("TRANSACTION ROLLBACK");
			}else{
				$this->halt("TRANSACTION ROLLBACK fail");
			}
		}else{
			if($this->db_type == "mysql"){
				$sql = "ROLLBACK;";
			}else if($this->db_type == "mssql"){
				$sql = "ROLLBACK TRANSACTION;";
			}
			$r = $this->query($sql);
			if($r){
				$this->notice("TRANSACTION ROLLBACK");
			}else{
				$this->halt("TRANSACTION ROLLBACK fail");
			}
		}
	}
	
	/**
	* This function returns escapes special characters string
	* @return string $sql the Escapes special characters in a string
	* @desc This function returns escapes special characters string
	*/
	function escape_string($sql){
		if($this->db_type == "mysqli" || ($this->db_type == 'odbc' && $this->odbc_type == 'mysql') ){
			return call_user_func($this->func_escape_string, $this->db_link, $sql);
		}else if($this->db_type == "mysql"){
			return call_user_func($this->func_escape_string, $sql);
		}else if($this->db_type == 'mssql' ||($this->db_type == 'odbc' && $this->odbc_type == 'mssql')){
			if ( !isset($sql) or empty($sql) ) return '';
			if ( is_numeric($sql) ) return $sql;

			$non_displayables = array(
				'/%0[0-8bcef]/',            // url encoded 00-08, 11, 12, 14, 15
				'/%1[0-9a-f]/',             // url encoded 16-31
				'/[\x00-\x08]/',            // 00-08
				'/\x0b/',                   // 11
				'/\x0c/',                   // 12
				'/[\x0e-\x1f]/'             // 14-31
			);
			foreach ( $non_displayables as $regex )
				$sql = preg_replace( $regex, '', $sql );
			$sql = str_replace("'", "''", $sql );
			return $sql;
		}
	}
	
	/**
	* Prints out a error message
	* @param string $message all occurred errors as array
	* @desc Returns all occurred errors
	*/
	function halt($message){
		if($this->debug){
			$this->error_nr=$this->get_error_nr();
			$this->error=$this->get_error();
					
			printf("Error: %s<br />\n", $message);
			
			if($this->error_nr!="" && $this->error!=""){
				printf("Database Error: %s (%s)<br />\n",$this->error_nr,$this->error);
			}
			die ("Session halted.");
		}
	}
	
	/**
	* Prints out a notice message
	* @param string $message all occurred notice as array
	* @desc Returns all occurred notice
	*/
	function notice($message){
		if($this->debug){
			echo $message."<br/>";
		}
	}
	
	/**
	* Switches to debug mode
	* @param boolean $switch
	* @desc Switches to debug mode
	*/
	function debug_mode($debug=true){
		$this->debug=$debug;
	}
	/**
	* destruct for this class
	*/
	function __destruct(){
		 if($this->db_link){
			$this->disconnect();
		 }
		 unset($this);
	}
}

?>
