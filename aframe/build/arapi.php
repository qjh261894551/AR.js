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
*{"data":[0.4,0.3,0.3,1.1,0.1,0.4,0.4,1,0.4,0.4,0.3,1.2,0.4,0.4,0.3,1.2],"type":"user","location":{"lng":123,"lat":321},"title":"abc","url":"www.baidu.com","grayData":[32,0.123]}
*{"data":[0.7,0.8,0.8,2.3,0.1,0.4,0.4,0.85,0.4,0.27,0.23,0.85,0.5,0.4,0.4,1.45],"type":"user","location":{"lng":123,"lat":321},"title":"abc","url":"www.baidu.com","grayData":[26,0.53]}
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

$key='abc';//约定key
if(isset($_GET['match'])){
	if($_GET['match']!=$key){
	$result=array('result'=>'unmatchkey','code'=>1);
	exit(json_encode($result,JSON_UNESCAPED_UNICODE));
	}else{
	$result=array('result'=>'match','code'=>1);
	exit(json_encode($result,JSON_UNESCAPED_UNICODE));
	}
}
if(isset($_GET['geturl'])){
		$data=json_decode($_GET['geturl']);
		$title=$data->title;
		$url=getgoodsurl($title);
		exit(json_encode($url,JSON_UNESCAPED_UNICODE));
}

if(isset($_GET['data'])){
	$data = $_GET['data'];

	$data=json_decode($data);
	$mydata = $data->data;
	$hashData = $data->hashData;
	$grayData=$data->grayData;
	$location=$data->location;
	$source=$data->source;
	if(isset($data->id)){
	$url="";
	if($source=='ios'){
	$url='http://memorecool.cn/app/index.php?i=2&c=entry&m=ewei_shopv2&do=mobile&r=goods.detail&';
	$url.="id=".$data->id;

	}
	}else $url=$data->url;
	$result['result'] = 'error';
	$result['code']="0";//初始化
	//1.type为stuff
	//2.type为user
	//3.
	//测试例子1:{"data":[0.3,0.3,0.3,1.1,0.1,0.4,0.4,1,0.4,0.4,0.3,1.2,0.4,0.4,0.3,1.2],"type":"user","location":{"lng":123,"lat":321},"title":"abc","url":"www.baidu.com"}
	//返回结果：{"result": "fail","code": 3}
	//测试例子2：{"data":[0.3,0.3,0.3,0.98,0.18,0.49,0.45,1.14,0.6,0.61,0.61,1.8,0.51,0.4,0.45,1.4],"type":"user","location":{"lng":123,"lat":321},"title":"abc","url":"www.baidu.com"}
	//返回结果：{"result": "success","title": "测试标题","url": "baidu.com"}
	
	//3.type非以上两类型
	//测试例子3:{"data":[0.3,0.3,0.3,0.98,0.18,0.49,0.45,1.14,0.6,0.61,0.61,1.8,0.51,0.4,0.45,1.4],"type":"abc","location":{"lng":123,"lat":321},"title":"abc","url":"www.baidu.com"}
	//返回结果：{"result": "error", "code": "1"}
	
	//4.非正常访问(无数据请求)
	//结果：{"result": "error","code": 0}
	$DVALUE=0.03;//分色差值
	$MGL=1;//灰度等级差值
	$BR=0.04;//binaRate 差值
	$RGB=0.1;//合色差值
	$length=5;




	$result=array();
	switch ($data->type) {
		case 'stuff':
			$result=saveStuffImgInfo($hashData,$mydata,$location,$data->title,$url,$grayData,$source);
			$result=json_encode($result,JSON_UNESCAPED_UNICODE);
			exit($result);
			break;
		case 'user':
		for($i=0;$i<$length;$i++){
			if(!isset($result['success'])){
			$result=getSimilarImgInfo($hashData,$mydata,$location,$grayData,$DVALUE,$MGL,$BR,$RGB,$source);
			$DVALUE+=0.01;
			$MGL+=0.2;
			$BR+=0.01;
			$RGB+=0.01;
			}else break;
		}
			$result=json_encode($result,JSON_UNESCAPED_UNICODE);
			exit($result);
			break;
		default:
			$result['code']="1";//传入类型不明
			$result['type']=$data->type;
			$result['data']=$mydata;
			$result['all']=$_GET['data'];
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


function getgoodsurl($title){
	$staticurl='http://memorecool.cn/app/index.php?i=2&c=entry&m=ewei_shopv2&do=mobile&r=goods.detail&';
	//$result=pdo_fetchall("SELECT id FROM".tablename('ewei_shop_goods')."WHERE title=:title",array(':title'=>'%'.$title.'%'));
	
	$discuz_database = array(
    'host' => 'qdm107873695.my3w.com', //数据库IP或是域名
    'username' => 'qdm107873695', // 数据库连接用户名
    'password' => 'sunshine', // 数据库连接密码
    'database' => 'qdm107873695_db', // 数据库名
    'port' => 3306, // 数据库连接端口
    'tablepre' => 'ims_', // 表前缀，如果没有前缀留空即可
    'charset' => 'utf8', // 数据库默认编码
    'pconnect' => 0, // 是否使用长连接
);
$discuz_db = new DB($discuz_database);
//查询uid为1的会员信息
//$result = $discuz_db->get('ewei_shop_goods', array('title like' => '%'.$title.'%'));
$result = $discuz_db->fetch("SELECT id FROM".tablename('ewei_shop_goods')."WHERE title LIKE :title ORDER BY title ASC ",array(':title'=>$title));		
if(empty($result)){
		$ins=$discuz_db->insert('ewei_shop_goods',array('title'=>$title,'uniacid'=>2));
		$id=$discuz_db->insertid();
	}else $id=$result['id'];
	$staticurl .= 'id='.$id;

	$re['url']=$staticurl;
	$re['id']=$id;
	return $re;
}

function saveStuffImgInfo($hashData,$data,$location,$title,$url,$grayData,$source)//$data数组由16个数字构成，$location为GPS经纬度，
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
		if (!empty($hashData)) {
			$imgInfoData['hashData0'] = $hashData[0];
			$imgInfoData['hashData1'] = $hashData[1];
			$imgInfoData['hashData2'] = $hashData[2];
			$imgInfoData['hashData3'] = $hashData[3];
			$imgInfoData['hashData4'] = $hashData[4];
			$imgInfoData['hashData5'] = $hashData[5];
			$imgInfoData['hashData6'] = $hashData[6];
			$imgInfoData['hashData7'] = $hashData[7];
			$imgInfoData['hashData8'] = $hashData[8];
			$imgInfoData['hashData9'] = $hashData[9];
			$imgInfoData['hashData10'] = $hashData[10];
			$imgInfoData['hashData11'] = $hashData[11];
			$imgInfoData['hashData12'] = $hashData[12];
			$imgInfoData['hashData13'] = $hashData[13];
			$imgInfoData['hashData14'] = $hashData[14];
			$imgInfoData['hashData15'] = $hashData[15];
			$imgInfoData['hashData16'] = $hashData[16];
			$imgInfoData['hashData17'] = $hashData[17];
			$imgInfoData['hashData18'] = $hashData[18];
			$imgInfoData['hashData19'] = $hashData[19];
			$imgInfoData['hashData20'] = $hashData[20];
			$imgInfoData['hashData21'] = $hashData[21];
			$imgInfoData['hashData22'] = $hashData[22];
			$imgInfoData['hashData23'] = $hashData[23];
			$imgInfoData['hashData24'] = $hashData[24];
			$imgInfoData['hashData25'] = $hashData[25];
			$imgInfoData['hashData26'] = $hashData[26];
			$imgInfoData['hashData27'] = $hashData[27];
			$imgInfoData['hashData28'] = $hashData[28];
			$imgInfoData['hashData29'] = $hashData[29];
			$imgInfoData['hashData30'] = $hashData[30];
			$imgInfoData['hashData31'] = $hashData[31];
			$imgInfoData['hashData32'] = $hashData[32];
			$imgInfoData['hashData33'] = $hashData[33];
			$imgInfoData['hashData34'] = $hashData[34];
			$imgInfoData['hashData35'] = $hashData[35];
			$imgInfoData['hashData36'] = $hashData[36];
			$imgInfoData['hashData37'] = $hashData[37];
			$imgInfoData['hashData38'] = $hashData[38];
			$imgInfoData['hashData39'] = $hashData[39];
			$imgInfoData['hashData40'] = $hashData[40];
			$imgInfoData['hashData41'] = $hashData[41];
			$imgInfoData['hashData42'] = $hashData[42];
			$imgInfoData['hashData43'] = $hashData[43];
			$imgInfoData['hashData44'] = $hashData[44];
			$imgInfoData['hashData45'] = $hashData[45];
			$imgInfoData['hashData46'] = $hashData[46];
			$imgInfoData['hashData47'] = $hashData[47];
			$imgInfoData['hashData48'] = $hashData[48];
			$imgInfoData['hashData49'] = $hashData[49];
			$imgInfoData['hashData50'] = $hashData[50];
			$imgInfoData['hashData51'] = $hashData[51];
			$imgInfoData['hashData52'] = $hashData[52];
			$imgInfoData['hashData53'] = $hashData[53];
			$imgInfoData['hashData54'] = $hashData[54];
			$imgInfoData['hashData55'] = $hashData[55];
			$imgInfoData['hashData56'] = $hashData[56];
			$imgInfoData['hashData57'] = $hashData[57];
			$imgInfoData['hashData58'] = $hashData[58];
			$imgInfoData['hashData59'] = $hashData[59];
			$imgInfoData['hashData60'] = $hashData[60];
			$imgInfoData['hashData61'] = $hashData[61];
			$imgInfoData['hashData62'] = $hashData[62];
			$imgInfoData['hashData63'] = $hashData[63];
		}
		



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

		$imgInfoData['maxGrayLevel']=$grayData[0];
		$imgInfoData['binaRate']=$grayData[1];
		$imgInfoData['source']=$source;
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

function getSimilarImgInfo($hashData,$data,$location,$grayData,$DVALUE,$MGL,$BR,$RGB,$source)//$data由16个数字构成，$location为GPS经纬度，$grayData灰度相关值
	{
		
		//location gps暂时空 todo
		//var_dump($data[3]);
		//test data
		//$data=array('0.427474417892157',0.364802121629902,0.327411420036765,1.11968795955882,0.161835107741013,0.464367149203431,0.451726485906863,1.07792874285131,0.414360945159314,0.403491727941176,0.385738242953431,1.20359091605392,0.442446895424837,0.410886999591503,0.388292049632353,1.24162594464869);
		//$data=array('0.487474417892157',0.304802121629902,0.237411420036765,1.29968795955882,0.101835107741013,0.404367149203431,0.401726485906863,1.17792874285131,0.324360945159314,0.453491727941176,0.295738242953431,1.10359091605392,0.352446895424837,0.320886999591503,0.298292049632353,1.14162594464869);
		//var_dump($data[0]);
		
		 //$desition=" and abs(redRate1-data1=:data1)<0.1 and abs(greenRate1-data2=:data2)<0.1 and abs(blueRate1-data3=:data3)<0.1 and abs(rgbRate1-data4=:data4)<0.2 and abs(redRate2-data5=:data5)<0.1 and abs(greenRate2-data6=:data6)<0.1 and abs(blueRate2-data7=:data7)<0.1 and abs(rgbRate2-data8=:data8)<0.2 and abs(redRate3-data9=:data9)<0.1 and abs(greenRate3-data10=:data10)<0.1 and abs(blueRate3-data11=:data11)<0.1 and abs(rgbRate3-data12:data12)<0.2 and abs(redRate-data13=:data13)<0.1 and abs(greenRate-data14=:data14)<0.1 and abs(blueRate-data15=:data15)<0.1 and abs(rgbRate-data16=:data16)<0.2";
		if (!empty($hashData)) {

			$hashLevel="(abs(hashData0-".$hashData[0].")+abs(hashData1-".$hashData[1].")+abs(hashData2-".$hashData[2].")+abs(hashData3-".$hashData[3].")+abs(hashData4-".$hashData[4].")+abs(hashData5-".$hashData[5].")+abs(hashData6-".$hashData[6].")+abs(hashData7-".$hashData[7].")+abs(hashData8-".$hashData[8].")+abs(hashData9-".$hashData[9].")+abs(hashData10-".$hashData[10].")+abs(hashData11-".$hashData[11].")+abs(hashData12-".$hashData[12].")+abs(hashData13-".$hashData[13].")+abs(hashData14-".$hashData[14].")+abs(hashData15-".$hashData[15].")+abs(hashData16-".$hashData[16].")+abs(hashData17-".$hashData[17].")+abs(hashData18-".$hashData[18].")+abs(hashData19-".$hashData[19].")+abs(hashData20-".$hashData[20].")+abs(hashData21-".$hashData[21].")+abs(hashData22-".$hashData[22].")+abs(hashData23-".$hashData[23].")+abs(hashData24-".$hashData[24].")+abs(hashData25-".$hashData[25].")+abs(hashData26-".$hashData[26].")+abs(hashData27-".$hashData[27].")+abs(hashData28-".$hashData[28].")+abs(hashData29-".$hashData[29].")+abs(hashData30-".$hashData[30].")+abs(hashData31-".$hashData[31].")+abs(hashData32-".$hashData[32].")+abs(hashData33-".$hashData[33].")+abs(hashData34-".$hashData[34].")+abs(hashData35-".$hashData[35].")+abs(hashData36-".$hashData[36].")+abs(hashData37-".$hashData[37].")+abs(hashData38-".$hashData[38].")+abs(hashData39-".$hashData[39].")+abs(hashData40-".$hashData[40].")+abs(hashData41-".$hashData[41].")+abs(hashData42-".$hashData[42].")+abs(hashData43-".$hashData[43].")+abs(hashData44-".$hashData[44].")+abs(hashData45-".$hashData[45].")+abs(hashData46-".$hashData[46].")+abs(hashData47-".$hashData[47].")+abs(hashData48-".$hashData[48].")+abs(hashData49-".$hashData[49].")+abs(hashData50-".$hashData[50].")+abs(hashData51-".$hashData[51].")+abs(hashData52-".$hashData[52].")+abs(hashData53-".$hashData[53].")+abs(hashData54-".$hashData[54].")+abs(hashData55-".$hashData[55].")+abs(hashData56-".$hashData[56].")+abs(hashData57-".$hashData[57].")+abs(hashData58-".$hashData[58].")+abs(hashData59-".$hashData[59].")+abs(hashData60-".$hashData[60].")+abs(hashData61-".$hashData[61].")+abs(hashData62-".$hashData[62].")+abs(hashData63-".$hashData[63]."))";

			$desition=" and abs(maxGrayLevel-".$grayData[0].")<".$MGL." and abs(binaRate-".$grayData[1].")<".$BR."";
			$desition .=" and abs(redRate1-".$data[0].")<".$DVALUE." and abs(greenRate1-".$data[1].")<".$DVALUE." and abs(blueRate1-".$data[2].")<".$DVALUE." and abs(rgbRate1-".$data[3].")<".$RGB." and abs(redRate2-".$data[4].")<".$DVALUE." and abs(greenRate2-".$data[5].")<".$DVALUE." and abs(blueRate2-".$data[6].")<".$DVALUE."  and abs(rgbRate2-".$data[7].")<".$RGB." and abs(redRate3-".$data[8].")<".$DVALUE."  and abs(greenRate3-".$data[9].")<".$DVALUE." and abs(blueRate3-".$data[10].")<".$DVALUE." and abs(rgbRate3-".$data[11].")<".$RGB." and abs(redRate-".$data[12].")<".$DVALUE." and abs(greenRate-".$data[13].")<".$DVALUE." and abs(blueRate-".$data[14].")<".$DVALUE." and abs(rgbRate-".$data[15].")<".$RGB." and source=:source ";
			$desition2="(abs(redRate1-".$data[0].")+abs(greenRate1-".$data[1].")+abs(blueRate1-".$data[2].")+abs(rgbRate1-".$data[3].")+abs(redRate2-".$data[4].")+abs(greenRate2-".$data[5].")+abs(blueRate2-".$data[6].")+abs(rgbRate2-".$data[7].")+abs(redRate3-".$data[8].")+abs(greenRate3-".$data[9].")+abs(blueRate3-".$data[10].")+abs(rgbRate3-".$data[11].")+abs(redRate-".$data[12].")+abs(greenRate-".$data[13].")+abs(blueRate-".$data[14].")+abs(rgbRate-".$data[15].")+abs(maxGrayLevel-".$grayData[0].")+abs(binaRate-".$grayData[1].")+".$hashLevel.")";

			
		 //$desition2="(abs(redRate1-".$data[0]."))";
			$fetch=pdo_fetch("SELECT goodsid,".$desition2." as desition2 FROM".tablename('mcar_goods_imgInfo')."WHERE  1".$desition."ORDER BY desition2 ASC limit 1",array(':source'=>$source));
			// $fetch=pdo_fetch("SELECT goodsid,".$hashLevel." as hashLevel FROM".tablename('mcar_goods_imgInfo')."WHERE source=:source ORDER BY hashLevel ASC limit 1",array(':source'=>$source));

			// $fetch=pdo_fetch("SELECT goodsid,".$desition2." as desition,".$hashLevel." as hashLevel FROM".tablename('mcar_goods_imgInfo')."WHERE  1".$desition." and hashLevel < 10"." ORDER BY hashLevel ASC limit 1",array(':source'=>$source));
		}else{
			$desition=" and abs(maxGrayLevel-".$grayData[0].")<".$MGL." and abs(binaRate-".$grayData[1].")<".$BR."";
			$desition .=" and abs(redRate1-".$data[0].")<".$DVALUE." and abs(greenRate1-".$data[1].")<".$DVALUE." and abs(blueRate1-".$data[2].")<".$DVALUE." and abs(rgbRate1-".$data[3].")<".$RGB." and abs(redRate2-".$data[4].")<".$DVALUE." and abs(greenRate2-".$data[5].")<".$DVALUE." and abs(blueRate2-".$data[6].")<".$DVALUE."  and abs(rgbRate2-".$data[7].")<".$RGB." and abs(redRate3-".$data[8].")<".$DVALUE."  and abs(greenRate3-".$data[9].")<".$DVALUE." and abs(blueRate3-".$data[10].")<".$DVALUE." and abs(rgbRate3-".$data[11].")<".$RGB." and abs(redRate-".$data[12].")<".$DVALUE." and abs(greenRate-".$data[13].")<".$DVALUE." and abs(blueRate-".$data[14].")<".$DVALUE." and abs(rgbRate-".$data[15].")<".$RGB." and source=:source ";
			$desition2="(abs(redRate1-".$data[0].")+abs(greenRate1-".$data[1].")+abs(blueRate1-".$data[2].")+abs(rgbRate1-".$data[3].")+abs(redRate2-".$data[4].")+abs(greenRate2-".$data[5].")+abs(blueRate2-".$data[6].")+abs(rgbRate2-".$data[7].")+abs(redRate3-".$data[8].")+abs(greenRate3-".$data[9].")+abs(blueRate3-".$data[10].")+abs(rgbRate3-".$data[11].")+abs(redRate-".$data[12].")+abs(greenRate-".$data[13].")+abs(blueRate-".$data[14].")+abs(rgbRate-".$data[15].")+abs(maxGrayLevel-".$grayData[0].")+abs(binaRate-".$grayData[1]."))";
		 //$desition2="(abs(redRate1-".$data[0]."))";
			$fetch=pdo_fetch("SELECT goodsid,".$desition2." as desition FROM".tablename('mcar_goods_imgInfo')."WHERE  1".$desition."ORDER BY desition ASC limit 1",array(':source'=>$source));
		}

		//$fetchall=pdo_fetchall("SELECT goodsid FROM".tablename('mcar_goods_imgInfo')."WHERE 1".$desition."ORDER BY ");

		 // $fetch=pdo_fetch("SELECT * FROM".tablename('mcar_goods_imginfo')."WHERE 1".$desition,array(':data1'=>$data[0],':data2'=>$data[1],':data3'=>$data[2],':data4'=>$data[3],':data5'=>$data[4],':data6'=>$data[5],':data7'=>$data[6],':data8'=>$data[7],':data9'=>$data[8],':data10'=>$data[9],':data11'=>$data[10],':data12'=>$data[11],':data13'=>$data[12],':data14'=>$data[13],':data15'=>$data[14],':data16'=>$data[15]));
		
		if(empty($fetch)){
		 $result['result'] = 'fail';
		 $result['code']=3;
		 return $result;
		}else {
			$goods=pdo_fetch("SELECT * FROM".tablename('mcar_goods')." WHERE id=:id",array(':id'=> $fetch['goodsid']));
			$result['result'] = 'success';
			$result['success'] = 'true';
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