<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/11/24
 * Time: 14:50
 */

function download($file)
{
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($file));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    echo file_get_contents($file);
    exit();
}

function md5_password($password)
{
    return md5($password.C('USER_PASSWORD_SUFFIX'));
}

function admin_search()
{
    $map = array();
    if(isset($_GET['search'])){
        $search  = trim($_GET['search']);
        $keyword = trim($_GET['keyword']);

        if($keyword==''){
            $map['cj_admin.aid'] = array('GT',0);
        }
        else{
            if($search == '1'){
                $map['cj_admin.aid'] = $keyword;
            }
            elseif($search == '2'){
                $map['cj_admin.nickname'] = array('like','%'.$keyword.'%');
            }
            elseif($search == '3'){
                $map['cj_admin_group.name'] = array('like'.'%'.$keyword.'%');
            }
            else{

            }
        }
    }

    return $map;
}

function group_search()
{
    $map = array();
    if(isset($_GET['search'])){
        $search = trim($_GET['search']);
        $keyword = trim($_GET['keyword']);

        if($keyword == ''){
            $map['cj_admin_group.admin_group_id'] = array('GT',0);
        }
        else{
            if($search == 1){
                $map['cj_admin_group.admin_group_id'] = array('EQ',$keyword);
            }
            elseif($search == 2){
                $map['cj_admin_group.name'] = array('like','%'.$keyword.'%');
            }
            elseif($search == 3){
                $map['cj_action.name'] = array('like','%'.$keyword.'%');
            }
            else{

            }
        }
    }

    return $map;
}

function user_search()
{
    $map = array();
    if(isset($_GET['search'])){
        $search  = trim($_GET['search']);
        $keyword = trim($_GET['keyword']);
        switch($search){
            case 'uid'://按初见号搜索
                if($keyword == ''){
                    $map['cj_user.uid'] = array('GT',0);
                }else{
                    $map['cj_user.uid'] = $keyword;
                }
                break;
            case 'nickname'://按昵称搜索
                $map['cj_user.nickname'] = array('like', '%'.$keyword.'%');
                break;
            case 'phone'://按电话搜索
                $map['cj_user.phone'] = array('like', '%'.$keyword.'%');
                break;
            case 'verify_video_pass'://视频认证通过
                $map['cj_user.video_verify'] = array('EQ',1);
                break;
            case 'verify_video_fail'://视频认证失败
                $map['cj_user.video_verify'] = 2;
                break;
            case 'verify_car_pass'://车辆认证通过
                $map['cj_user.car_verify'] = 1;
                break;
            case 'verify_car_fail'://车辆认证失败
                $map['cj_user.car_verify'] = 2;
                break;
            case 'sex_male':
                $map['cj_user.sex'] = 0;
                break;
            case 'sex_female':
                $map['cj_user.sex'] = 1;
                break;
            default:
                break;
        }
    }

    return $map;
}

function car_brand_search()
{
    $map = array();
    $keyword = trim($_REQUEST['keyword']);
    $type = trim($_REQUEST['type']);

    if(empty($keyword)) {
        $map['id']  = array('GT','0');
        //$this->assign('formTitle', '全部品牌：'.$keyword);
    } else {
        if($type == 1) {
            $map['id'] = $keyword;
        } elseif($type == 2) {
            $map['name'] = array('like', '%'.$keyword.'%');
        } else {

        }
        //$this->assign('formTitle', '搜索结果：'.$keyword);
    }

    return $map;
}

function car_model_search()
{
    $map = array();
    $keyword = trim($_REQUEST['keyword']);
    $type = trim($_REQUEST['type']);

    if(empty($keyword)&&(!isset($_GET['model']))) {
        $map['cj_car_model.id']  = array('GT','0');
        //$this->assign('formTitle', '全部车型：'.$keyword);
    }elseif(isset($_GET['model'])){
        $map['cj_car_model.brand_id'] = array('EQ', $_GET['model']);
        //$this->assign('formTitle', '车型：'.$keyword);
    }
    else {
        if($type == 1) {
            $map['cj_car_model.id'] = $keyword;
        } elseif($type == 2) {
            $map['cj_car_model.name'] = array('like', '%' . $keyword . '%');
        }
        else{

        }

        //$this->assign('formTitle', '搜索结果：'.$keyword);
    }

    return$map;
}

function car_license_search()
{
    $map = array();

    $keyword = trim($_REQUEST['keyword']);
    $type = trim($_REQUEST['type']);

    if(empty($keyword)) {
        $map['cj_car_license_series.id']  = array('GT','0');
        //$this->assign('formTitle', '全部车型：'.$keyword);
    }
    else {
        if($type == 1) {
            $map['cj_car_license_series.id']    = $keyword;
        } elseif($type == 2) {
            $map['cj_car_license_series.style'] = array('like', '%' . $keyword . '%');
        }
        elseif($type==3){
            $map['cj_car_model.name']         = array('like', '%' . $keyword . '%');
        }
        else{

        }

        //$this->assign('formTitle', '搜索结果：'.$keyword);
    }

    return $map;
}

function certificate_car_search()
{
    $map = array();

    if(isset($_GET['search'])){
        $search  = trim($_GET['search']);
        $keyword = trim($_GET['keyword']);

        if($keyword=='') {
            $map['cj_certificate_car.uid']  = array('GT','0');
        } else {
            if($search == '1') {
                $map['cj_certificate_car.uid'] = $keyword;
            } elseif($search == '2') {
                $map['cj_user.nickname'] = array('like', '%'.$keyword.'%');
            } else {

            }
        }
    }

    return $map;
}

function certificate_video_search()
{
    $map = array();

    if(isset($_GET['search'])){
        $search  = trim($_GET['search']);
        $keyword = trim($_GET['keyword']);

        if($keyword=='') {
            $map['cj_certificate_video.uid']  = array('GT','0');
        } else {
            if($search == '1') {
                $map['cj_certificate_video.uid'] = $keyword;
            } elseif($search == '2') {
                $map['cj_user.nickname'] = array('like', '%'.$keyword.'%');
            } elseif($search == '3'){
                $map['cj_certificate_video.dtime'] = array('like', '%'.$keyword.'%');
            } else{

            }
        }
    }

    return $map;
}

function head_img_search()
{
    $map = array();
    if(isset($_GET['search'])){
        $search  = trim($_GET['search']);
        $keyword = trim($_GET['keyword']);

        if($keyword=='') {
            $map['uid']  = array('GT','0');
            //$this->assign('formTitle', '全部用户：'.$keyword);
        } else {
            if($search == '1') {
                $map['uid'] = $keyword;
            } else {

            }
            //$this->assign('formTitle', '搜索结果：'.$keyword);
        }
    }

    return $map;
}

function certificate_video_log_search()
{
    $map = array();
    if(isset($_GET['search'])){
        $search = trim($_GET['search']);
        $keyword = trim($_GET['keyword']);

        if($keyword == ''){
            $map['cj_certificate_video_log.id'] = array('GT',0);
        }else{
            if($search == '1'){
                $map['cj_admin.nickname'] = array('like','%'.$keyword.'%');
            }
            elseif($search == '2'){
                $map['cj_user.nickname'] = array('like','%'.$keyword.'%');
            }
            else{

            }
        }
    }

    return $map;
}

function certificate_car_log_search()
{
    $map = array();
    if(isset($_GET['search']))
    {
        $search  = $_GET['search'];
        $keyword = $_GET['keyword'];

        if($keyword == ''){
            $map['cj_certificate_car_log.id'] = array('GT',0);
        }else{
            if($search == '1'){
                $map['cj_admin.nickname'] = array('like','%'.$keyword.'%');
            }
            elseif($search == '2'){
                $map['cj_user.nickname'] = array('like','%'.$keyword.'%');
            }
            else{

            }
        }

    }
    return $map;
}

function verify_code_search()
{
    $map = array();
    if(isset($_GET['search']))
    {
        $search  = $_GET['search'];
        $keyword = $_GET['keyword'];

        if($keyword == ''){
            $map['phone'] = array('GT',0);
        }else{
            if($search == '1'){
                $map['phone'] = array('like','%'.$keyword.'%');
            }
            else{

            }
        }
    }
    return $map;
}

//获取当前月份
function get_current_month()
{
    //$timeTemp = date('y-m',time());
    $timeTemp = date('Y-n',time());
    $time     = explode('-',$timeTemp);
    $month    = end($time);

    return $month;
}

//获取当前日期
function get_current_date()
{
    $timeTemp = date('y-m-d',time());

    return $timeTemp;
}


//根据生日获取对应星座
//时间格式为y-m-d
function get_constellation($birthday)
{
    $birthDateTemp = explode('-', $birthday);
    array_shift($birthDateTemp);
    $birthDate = array('Month' => current($birthDateTemp), 'Day' => end($birthDateTemp));
    //所有数组索引从1开始 到12结束
    $constellation = array("XO", "水瓶座", "双鱼座", "白羊座",
        "金牛座", "双子座", "巨蟹座",
        "狮子座", "处女座", "天秤座",
        "天蝎座", "射手座", "魔羯座");

    $month = (int)$birthDate['Month'];
    $day = (int)$birthDate['Day'];
    $dateArr = array('0', '20', '19', '21', '20', '21', '22', '23', '23', '23', '24', '23', '22');

    if ($day < $dateArr[$month]) {
        //一月
        if ($month == 1) {
            return $constellation[12];
        } else {
            return $constellation[$month - 1];
        }
    }
    $ret = $constellation[$month];

    return $ret;
}

function objectToArray($obj)
{
    $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
    foreach ($_arr as $key => $val) {
        $val = (is_array($val)) || is_object($val) ? $this->objectToArray($val) : $val;
        $arr[$key] = $val;
    }
    return $arr;
}

function json_to_array($str)
{
    $ret = json_decode($str);
    return $ret;
}

function array_to_json($arr)
{
    $ret = json_encode($arr);
    return $ret;
}

function explode_colon($str)
{
    return explode(':',$str);
}

function array_to_str($arr)
{
    $ret = '';
    foreach($arr as $value){
        $ret .= $value.',';
    }
    return $ret;
}

function unit_to_time_single($data,$field)
{
    $data[$field] = date('Y-m-d H:i:s',$data[$field]);
    return $data;
}

function unixToTime($list,$field)
{
    for($i=0;$i<count($list);$i++){
        $list[$i][$field] = date('Y-m-d H:i:s',$list[$i][$field]);
    }

    return $list;
}

function sex_to_text_single($data,$field)
{
    if($data[$field] == 0)
        $data[$field] = '男';
    else
        $data[$field] = '女';
    return $data;
}

function dblocking_time_to_state($data,$field)
{
    $nowTime = time();
    if($data[$field]>$nowTime)
        $data[$field] = '封禁';
    else
        $data[$field] = '正常';

    return $data;
}

function certificate_to_state($data,$field)
{
    if($data[$field] == '0')
        $data[$field] = '待处理';
    elseif($data[$field] == '1')
        $data[$field] = '通过认证';
    else
        $data[$field] = '未通过认证';
    return $data;
}

/*
 * 根据某个field对二维数组进行排序
 * */
function sort_array($arr,$field,$type)
{
    $ret = $arr;
    foreach ($ret as $value) {
        $temp[] = $value[$field];
    }

    switch($type){
        case 'asc':
            array_multisort($temp, SORT_ASC, $ret);
            break;
        case 'desc':
            array_multisort($temp, SORT_DESC, $ret);
            break;
        default:
            array_multisort($temp, SORT_ASC, $ret);
    }

    return $ret;
}

/*
 * 对已有的数据进行分组
 * @param data 形式如array(0=>array('uid'=>1),'1'=>array('uid'=>2)...)
 * @return array 形式如array(field=>array('uid'=>1),field=>array('uid'=>2))
 * */
function get_group($data=array(),$field)
{
    $ret = array();
    $len = count($data);
    if($len>0){
        foreach($data as $key=>$value){
            $fieldValue = $value[$field];
            if(!isset($ret[$fieldValue])){
                $ret[$fieldValue] = array();
            }
            array_push($ret[$fieldValue],$value);
        }
    }

    return $ret;
}

function create_cloud_img_path($uid)
{
    $md5 = md5($uid);
    $path = substr($md5, 0,2).'/'.substr($md5, 2, 2).'/';
    $path .= 'img_'.md5(uniqid(rand(), true));

    return $path;
}

/*
 * 生成随机时间
 * */
function get_rand_time($a,$b)
{
    $a=strtotime($a);
    $b=strtotime($b);
    return date( "Y-m-d H:m:s", mt_rand($a,$b));
}

/*
 * 生成随机字符串
 * */
function get_rand_char($length){
    $str = null;
    $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
    $max = strlen($strPol)-1;

    for($i=0;$i<$length;$i++){
        $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
    }

    return $str;
}

/*
 * 跨数据库时构造page
 * 正常情况下$Page是一个object，在执行sql语句时使用，用到的参数只有
 * 1、firstRow
 * 2、listRows
 * 在执行sql时将这两参数用到limit里获取前N条数据
 * */
function create_page($data=array(),&$Page)
{
    $ret = array();
    $cnt = count($data);
    $start = $Page->firstRow;

    if($start+$Page->listRows>$cnt)
    {
        $end = $cnt;
    }
    else{
        $end = $Page->firstRow+$Page->listRows;
    }

    for($i=$start;$i<$end;$i++){
        $ret[] = $data[$i];
    }

    return $ret;
}


function printr($data){
    echo "<pre/>";
    print_r($data);
    exit;
}

if(!function_exists('array_column')){ 
    function array_column($input, $columnKey, $indexKey=null){
            $columnKeyIsNumber  = (is_numeric($columnKey))?true:false; 
            $indexKeyIsNull            = (is_null($indexKey))?true :false; 
            $indexKeyIsNumber     = (is_numeric($indexKey))?true:false; 
            $result                         = array(); 
            foreach((array)$input as $key=>$row){ 
                if($columnKeyIsNumber){ 
                    $tmp= array_slice($row, $columnKey, 1); 
                    $tmp= (is_array($tmp) && !empty($tmp))?current($tmp):null; 
                }else{ 
                    $tmp= isset($row[$columnKey])?$row[$columnKey]:null; 
                } 
                if(!$indexKeyIsNull){ 
                    if($indexKeyIsNumber){ 
                      $key = array_slice($row, $indexKey, 1); 
                      $key = (is_array($key) && !empty($key))?current($key):null; 
                      $key = is_null($key)?0:$key; 
                    }else{ 
                      $key = isset($row[$indexKey])?$row[$indexKey]:0; 
                    } 
                } 
                $result[$key] = $tmp; 
            } 
            return $result; 
     }
}

function makeFileName($uid) {
    $md5  = md5($uid);
    $path = substr($md5, 0,2).'/'.substr($md5, 2, 2).'/';
    $path .= 'img_'.md5(uniqid(rand(), true)).'.jpg';
    return $path;
}

function surgingmakeFileName($filename) {
    //需要判断是否有后缀
    $temp  = explode('.',$filename);
    $ext   = (count($temp) > 1) ? '.'.$temp[1] : '';
    $path  = date("Ymd").'/';
    $path .= md5(uniqid(rand(), true)).$ext;
    return $path;
}



function c_url($data,$url){

    $ch = curl_init ();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    return json_decode(curl_exec($ch),true);
    
}



// http://php.net/manual/zh/function.array-column.php
if(!function_exists('array_column'))
{
  function array_column($array,$column_name)
  {
    return array_map(function($element) use($column_name){return $element[$column_name];},$array);
  }
}