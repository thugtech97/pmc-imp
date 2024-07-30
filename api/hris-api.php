<?php

$conn_d['davao']['type'] = 'sqlsrv';$conn_d['davao']['host'] = 'DESKTOP-HBTCDK6\SQLEXPRESS';$conn_d['davao']['name'] = 'SyncHRIS';$conn_d['davao']['uname'] = 'judeescol97';$conn_d['davao']['pword'] = '!Q2w3e!1997';

$array_emp = array();

$pdoempdata = new PDO($conn_d['davao']['type'].":server=".$conn_d['davao']['host'].";Database=".$conn_d['davao']['name'], $conn_d['davao']['uname'], $conn_d['davao']['pword']);
$pdoempdata->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$empdatasql = $pdoempdata->prepare("SELECT e.EmpID, e.FullName,d.DeptDesc, d.deptid FROM ViewHREmpMaster e left join hrdepartment d on d.deptid=e.deptid WHERE e.Active = 1");

$empdatasql->execute();
$empdatasql->setFetchMode(PDO::FETCH_ASSOC);

for($i=0; $rowempdata = $empdatasql->fetch(); $i++){     
	$array_emp[] .= str_replace(',',' ',$rowempdata['FullName']).' : '.$rowempdata['DeptDesc'];
}

	echo json_encode($array_emp);
?>

