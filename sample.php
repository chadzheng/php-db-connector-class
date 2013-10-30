<?php
//config array
$databace_config = array (
							'db_type'	=>"mssql", //"mysql","mysqli","odbc"				
							'host' 		=>'xxx.xxx.xxx.xxx',
							'database'	=>'db_name',
							'user'		=>'username',
							'password'	=>'password',
							'port'		=>'port number',		//(optional)
							'odbc_type'	=>'mssql'	//(optional only when odbc, defualt mssql )
							'debug'		=>true		//(optional true on, false off, defualt false)
						 );
//include class
include "db_class.php";
//new object
$db = new db_class($databace_config);
//sql statement
$select = "SELECT * FROM your_table";
$insert = " INSERT INTO your_table .... ";
$update = " UPDATE your_table ....";
//start transaction
$db->transaction_start();
//insert
$insert_r = $db->query($insert);
//get last insert id
print($db->get_last_insert_id());
//update
$update_r = $db->query($update.);
//result check
if($insert_r && $update_r){
	//transaction commit
	$db->commit();
}else{
	//transaction rollback
	$db->rollback();
}
//select
$result = $db->query($select);
//result print
while($row = $db->get_row($result)){
	echo $row['name']."</br>";
}
//free resourse
unset($db);

?>
