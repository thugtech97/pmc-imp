<?php 
		
	$serverName = "DESKTOP-HBTCDK6\SQLEXPRESS";	$connectionInfo = array( "Database"=>"wfs", "UID"=>"judeescol97", "PWD"=>"!Q2w3e!1997" );
	//$serverName = "172.16.20.28";	$connectionInfo = array( "Database"=>"WFS", "UID"=>"sa", "PWD"=>"philsaga" );
	// $connectionInfo = array( "Database"=>"PMC-WFS", "UID"=>"sa", "PWD"=>"@Temp123!" );
	$conn = sqlsrv_connect($serverName, $connectionInfo);
	
	
	//$conn_d['davao']['type'] = 'sqlsrv';$conn_d['davao']['host'] = '172.16.10.42\philsaga_db';$conn_d['davao']['name'] = 'PMC-DAVAO';$conn_d['davao']['uname'] = 'app_user_read_only';$conn_d['davao']['pword'] = 'P4TGQkPz';
	
	$conn_d['davao']['type'] = 'sqlsrv';$conn_d['davao']['host'] = 'DESKTOP-HBTCDK6\SQLEXPRESS';$conn_d['davao']['name'] = 'SyncHRIS';$conn_d['davao']['uname'] = 'judeescol97';$conn_d['davao']['pword'] = '!Q2w3e!1997';

	$conn_a['agusan']['type'] = 'sqlsrv';
	$conn_a['agusan']['host'] = '172.16.20.42\agusan_db';
	$conn_a['agusan']['name'] = 'PMC-AGUSAN-NEW';
	$conn_a['agusan']['uname'] = 'app_user_read_only';
	$conn_a['agusan']['pword'] = 'P4TGQkPz';

?>