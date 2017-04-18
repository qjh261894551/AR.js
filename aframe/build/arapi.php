<?php
/**
 * 用于AR的api
 */
$data = $_GET['data'];
$data = json_decode($data);
var_dump($data->type);
$mydata = $data->data;
var_dump($mydata[0]);

exit();
// urldecode($data);
// $data = json_decode(str_replace('&quot;', '"', $data), true);
// // $data = json_decode($data);
//     	$result['result'] = 'success';
//     	echo $_GPC["somfun"].'('.json_encode($result).')';   //修改为此格式
//     	exit();
?>