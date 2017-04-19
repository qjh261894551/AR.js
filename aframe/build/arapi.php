<?php
/**
 * 用于AR的api
 */
require_once './../framework/bootstrap.inc.php';
$data = $_GET['data'];
$data = json_decode($data);
var_dump($data->type);
$mydata = $data->data;
var_dump($mydata[0]);
$goods = pdo_fetchall("SELECT * FROM " . tablename('mcar_goods'));
var_dump($goods);
exit();
// urldecode($data);
// $data = json_decode(str_replace('&quot;', '"', $data), true);
// // $data = json_decode($data);
//     	$result['result'] = 'success';
//     	echo $_GPC["somfun"].'('.json_encode($result).')';   //修改为此格式
//     	exit();
?>