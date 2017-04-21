<?php
/**
 * 用于AR的api
 */
/*测试地址
* https://weixin.memorecool.cn/mcar/arapi.php
*
* 请求方式：GET
* 
*GET json 测试数据
*{"data":[0.4,0.3,0.3,1.1,0.1,0.4,0.4,1,0.4,0.4,0.3,1.2,0.4,0.4,0.3,1.2],"type":"user","location":{"lng":123,"lat":321},"title":"abc","url":"www.baidu.com"}
* 
*数据需要以JSON传入，内容为：data 16位数组，title 图片标题（非用户操作时）,url 图片地址,
*location 坐标以对象形式传入,type 为操作类型（值：user为用户 进行图片比对，stuff为
*管理员，具体操作未定，默认插入图片） 
*code =1 传入类型相关 code=2 插入相关 code= 查找相关 code=0 初始值
* 
*目前还未使用urldecode
* 
*/

require_once './../framework/bootstrap.inc.php';


if(isset($_GET['data'])){
	$data = $_GET['data'];
	$data=json_decode($data);
	$mydata = $data->data;
	$location=$data->location;
	$result['result'] = 'error';
	$result['code']="0";//初始化
	
	
	//1.type为stuff
	
	//2.type为user
	//测试例子1:{"data":[0.3,0.3,0.3,1.1,0.1,0.4,0.4,1,0.4,0.4,0.3,1.2,0.4,0.4,0.3,1.2],"type":"user","location":{"lng":123,"lat":321},"title":"abc","url":"www.baidu.com"}
	//返回结果：{"result": "fail","code": 3}
	//测试例子2：{"data":[0.3,0.3,0.3,0.98,0.18,0.49,0.45,1.14,0.6,0.61,0.61,1.8,0.51,0.4,0.45,1.4],"type":"user","location":{"lng":123,"lat":321},"title":"abc","url":"www.baidu.com"}
	//返回结果：{"result": "success","title": "测试标题","url": "baidu.com"}
	
	//3.type非以上两类型
	//测试例子3:{"data":[0.3,0.3,0.3,0.98,0.18,0.49,0.45,1.14,0.6,0.61,0.61,1.8,0.51,0.4,0.45,1.4],"type":"abc","location":{"lng":123,"lat":321},"title":"abc","url":"www.baidu.com"}
	//返回结果：{"result": "error", "code": "1"}
	
	//4.非正常访问(无数据请求)
	//结果：{"result": "error","code": 0}
	
	switch ($data->type) {
		case 'stuff':
			$result=saveStuffImgInfo($mydata,$location,$data->title,$data->url);
			$result=json_encode($result,JSON_UNESCAPED_UNICODE);
			exit($result);
			break;
		case 'user':
			$result=getSimilarImgInfo($mydata,$location);
			$result=json_encode($result,JSON_UNESCAPED_UNICODE);
			exit($result);
			break;
		default:
			$result['code']="1";//传入类型不明
			$result=json_encode($result,JSON_UNESCAPED_UNICODE);
			exit($result);
			break;
	}
}else{
	$result['result']='error';
	$result['code']=0;
	$result=json_encode($result,JSON_UNESCAPED_UNICODE);
	exit($result);
}
function saveStuffImgInfo($data,$location,$title,$url)//$data数组由16个数字构成，$location为GPS经纬度，
	{
		$good = pdo_fetch('SELECT id FROM ' . tablename('mcar_goods') . ' WHERE title=:title and url=:url ', array(':title' => $title, ':url' => $url));
		if (empty($good)) {
			$goodData['title'] = $title;
			$goodData['url'] = $url;
			pdo_insert('mcar_goods', $goodData);
			$id = pdo_insertid();
		}else{
			$id = $good['id'];
		}
		$imgInfoData['goodsid'] = $id;

		$imgInfoData['redRate1'] = $data[0];
		$imgInfoData['greenRate1'] = $data[1];
		$imgInfoData['blueRate1'] = $data[2];
		$imgInfoData['rgbRate1'] = $data[3];

		$imgInfoData['redRate2'] = $data[4];
		$imgInfoData['greenRate2'] = $data[5];
		$imgInfoData['blueRate2'] = $data[6];
		$imgInfoData['rgbRate2'] = $data[7];

		$imgInfoData['redRate3'] = $data[8];
		$imgInfoData['greenRate3'] = $data[9];
		$imgInfoData['blueRate3'] = $data[10];
		$imgInfoData['rgbRate3'] = $data[11];

		$imgInfoData['redRate'] = $data[12];
		$imgInfoData['greenRate'] = $data[13];
		$imgInfoData['blueRate'] = $data[14];
		$imgInfoData['rgbRate'] = $data[15];

		$result = pdo_insert('mcar_goods_imgInfo', $imgInfoData);
		if ($result) {
			$re['result']="success";
			$re['code']="2";//插入成功
			return $re;
		}else{
			$re['result']="fail";
			$re['code']="2";
			return $re;
		}
	}

function getSimilarImgInfo($data,$location)//$data由16个数字构成，$location为GPS经纬度，
	{

		//location gps暂时空 todo
		//var_dump($data[3]);
		//test data
		//$data=array('0.427474417892157',0.364802121629902,0.327411420036765,1.11968795955882,0.161835107741013,0.464367149203431,0.451726485906863,1.07792874285131,0.414360945159314,0.403491727941176,0.385738242953431,1.20359091605392,0.442446895424837,0.410886999591503,0.388292049632353,1.24162594464869);
		//$data=array('0.487474417892157',0.304802121629902,0.237411420036765,1.29968795955882,0.101835107741013,0.404367149203431,0.401726485906863,1.17792874285131,0.324360945159314,0.453491727941176,0.295738242953431,1.10359091605392,0.352446895424837,0.320886999591503,0.298292049632353,1.14162594464869);
		//var_dump($data[0]);
		
		 //$desition=" and abs(redRate1-data1=:data1)<0.1 and abs(greenRate1-data2=:data2)<0.1 and abs(blueRate1-data3=:data3)<0.1 and abs(rgbRate1-data4=:data4)<0.2 and abs(redRate2-data5=:data5)<0.1 and abs(greenRate2-data6=:data6)<0.1 and abs(blueRate2-data7=:data7)<0.1 and abs(rgbRate2-data8=:data8)<0.2 and abs(redRate3-data9=:data9)<0.1 and abs(greenRate3-data10=:data10)<0.1 and abs(blueRate3-data11=:data11)<0.1 and abs(rgbRate3-data12:data12)<0.2 and abs(redRate-data13=:data13)<0.1 and abs(greenRate-data14=:data14)<0.1 and abs(blueRate-data15=:data15)<0.1 and abs(rgbRate-data16=:data16)<0.2";
		 $desition=" and abs(redRate1-".$data[0].")<0.1 and abs(greenRate1-".$data[1].")<0.1 and abs(rgbRate1-".$data[3].")<0.12 and abs(redRate2-".$data[4].")<0.1 and abs(greenRate2-".$data[5].")<0.1 and abs(blueRate2-".$data[6].")<0.1  and abs(rgbRate2-".$data[7].")<0.12 and abs(redRate3-".$data[8].")<0.1  and abs(greenRate3-".$data[9].")<0.1 and abs(blueRate3-".$data[10].")<0.1 and abs(rgbRate3-".$data[11].")<0.12 and abs(redRate-".$data[12].")<0.1 and abs(greenRate-".$data[13].")<0.1 and abs(blueRate-".$data[14].")<0.1 and abs(rgbRate-".$data[15].")<0.12";
		 $desition2="(abs(redRate1-".$data[0].")+abs(greenRate1-".$data[1].")+abs(blueRate1-".$data[2].")+abs(rgbRate1-".$data[3].")+abs(redRate2-".$data[4].")+abs(greenRate2-".$data[5].")+abs(blueRate2-".$data[6].")+abs(rgbRate2-".$data[7].")+abs(redRate3-".$data[8].")+abs(greenRate3-".$data[9].")+abs(blueRate3-".$data[10].")+abs(rgbRate3-".$data[11].")+abs(redRate-".$data[12].")+abs(greenRate-".$data[13].")+abs(blueRate-".$data[14].")+abs(rgbRate-".$data[15]."))";
		 //$desition2="(abs(redRate1-".$data[0]."))";
		$fetch=pdo_fetch("SELECT goodsid,".$desition2." as desition FROM".tablename('mcar_goods_imgInfo')."WHERE 1".$desition."ORDER BY desition ASC limit 1");

		//$fetchall=pdo_fetchall("SELECT goodsid FROM".tablename('mcar_goods_imgInfo')."WHERE 1".$desition."ORDER BY ");

		 // $fetch=pdo_fetch("SELECT * FROM".tablename('mcar_goods_imginfo')."WHERE 1".$desition,array(':data1'=>$data[0],':data2'=>$data[1],':data3'=>$data[2],':data4'=>$data[3],':data5'=>$data[4],':data6'=>$data[5],':data7'=>$data[6],':data8'=>$data[7],':data9'=>$data[8],':data10'=>$data[9],':data11'=>$data[10],':data12'=>$data[11],':data13'=>$data[12],':data14'=>$data[13],':data15'=>$data[14],':data16'=>$data[15]));
		
		if(empty($fetch)){
		 $result['result'] = 'fail';
		 $result['code']=3;
		 return $result;
		}else {
			$goods=pdo_fetch("SELECT * FROM".tablename('mcar_goods')." WHERE id=:id",array(':id'=> $fetch['goodsid']));
			$result['result'] = 'success';
			$result['title'] = $goods['title'];		
			$result['url'] = $goods['url'];
			return $result;
		} 
	}//返回$result


// urldecode($data);
// $data = json_decode(str_replace('&quot;', '"', $data), true);
// // $data = json_decode($data);
//     	$result['result'] = 'success';
//     	echo $_GPC["somfun"].'('.json_encode($result,JSON_UNESCAPED_UNICODE).')';   //修改为此格式
//     	exit();
?>