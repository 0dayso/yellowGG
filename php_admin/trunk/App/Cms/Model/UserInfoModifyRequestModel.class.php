<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/2/9
 * Time: 17:29
 */
 
namespace Cms\Model;

use Cms\Controller\CrossDatabaseController;
use Cms\Model;
class UserInfoModifyRequestModel extends PublicModel{

    public function get_content_manage_count($map)
    {
        $Model = D($this->name);
        $ret   = $Model->query("SELECT uid FROM cj_user_info_modify_request AS a WHERE a.uid !='0'  $map GROUP BY a.uid ");
        return count($ret);
    }

    /*
     * 获取用户修改文字的信息
     * 同一时间同一个人修改的同一字段只可能有一条
     * */
    public function get_content_manage_list($map,$Page)
    {
        $map  = str_replace('AND ','a.',$map);
        $mgci = D('SensitiveWords')->get_multi_items('','word');
        $newarr = array();
        foreach ($mgci as $value) {
            $array[$value['word']] = '<a style=color:red;font-weight:900;>'.$value['word'].'</a>';
        } 

        $Model = D($this->name);
         
        $guid  = "SELECT uid FROM cj_user_info_modify_request AS a WHERE a.uid !='0' AND $map GROUP BY a.uid ".' LIMIT '.$Page->firstRow.','.$Page->listRows;
        $guid  = ' uid IN('.trim(implode(',',array_column($Model->query($guid),'uid')),',').')   ';
        if($guid!=''){
            $sql = "SELECT a.id,a.uid,a.operation,a.aid,a.field_name,a.field_value,a.reason,a.sub_time,a.status,a.pass_time,a.result 
                    FROM (
                            SELECT  id,uid,operation,aid,field_name,field_value,reason,sub_time,status,pass_time,result 
                            FROM cj_user_info_modify_request WHERE {$guid}  ORDER BY id DESC  ) AS a 
                    WHERE  {$map} GROUP BY a.uid,a.field_name ORDER BY a.sub_time DESC ";
            $temp  = $Model->query($sql);
        }

        require_once("./ThinkPHP/Library/Think/Emoji.class.php");
        // 获取所有的管理员
        $Admin = D('Admin');
        $adminarr = $Admin->field('aid,nickname')->where()->select();
        foreach ($adminarr as $value) {
            $adminItem[$value['aid']] = $value['nickname'];
        } 
        
        $fieldArr = array(
            'nickname' => '昵称',
            'birthday' => '生日',
            'phone'=> '电话',
            'tags' => '标签',
            'height'=>'身高',
            'hometown'=>'家乡',
            'signature' => '个性签名',
            'job' => '职业',
            'movie' => '电影',
            'weekend' => '周末',
            'cooking' => '做菜',
            'travel' => '旅行',
            'restaurant' => '餐馆',
            'sport' => '运动',
        );
        foreach($temp as $key=>$value){
            $temp[$key]['nickname']     = strtr($value['aid'],$adminItem);
            $temp[$key]['field_name']   = $fieldArr[$value['field_name']];
            $temp[$key]['field_valued'] =  emoji_unified_to_html(strtr($temp[$key]['field_value'],$array));
        }
        return $temp;
    }

    public function reset_test_data()
    {
        $resetModel = C('RESET_MODEL');
        $Model = D($this->name);
        switch($resetModel) {
            case 'local':
                $Model->truncate_table();
                $data = array(
                    array(
                        'uid'=>10010,
                        'field_name'=>'nickname',
                        'field_value'=>'heihei',
                        'sub_time'=>'2015-02-09 06:12:30',
                    ),
                    array(
                        'uid'=>10011,
                        'field_name'=>'tags',
                        'field_value'=>'你全家都傻叉',
                        'sub_time'=>'2015-02-09 10:22:30',
                    ),
                    array(
                        'uid'=>10010,
                        'field_name'=>'nickname',
                        'field_value'=>'hahahahah',
                        'sub_time'=>'2015-02-09 22:08:29',
                    ),
                    array(
                        'uid'=>10011,
                        'field_name'=>'tags',
                        'field_value'=>'超级傻叉',
                        'sub_time'=>'2015-02-09 23:22:30',
                    ),
                    array(
                        'uid'=>10010,
                        'field_name'=>'job',
                        'field_value'=>'it',
                        'sub_time'=>'2015-02-10 12:22:30',
                    ),
                    array(
                        'uid'=>10011,
                        'field_name'=>'job',
                        'field_value'=>'it',
                        'sub_time'=>'2015-02-10 13:22:30',
                    ),
                    array(
                        'uid'=>10010,
                        'field_name'=>'job',
                        'field_value'=>'game',
                        'sub_time'=>'2015-02-10 22:22:30',
                    ),
                    array(
                        'uid'=>10011,
                        'field_name'=>'job',
                        'field_value'=>'dota',
                        'sub_time'=>'2015-02-10 23:12:30',
                    ),
                );
                $Model->insert_multi_items($data);
                break;
            case 'server':
                break;
            default:
                break;
        }
    }
}
