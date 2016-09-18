<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/2/2
 * Time: 10:16
 */

namespace Cms\Event;

use Cms\Controller\MessageController;
use Cms\Event;
class AccusationEvent extends PublicEvent{

    public function get_request_data($case,$map)
    {
        $Model   = D('AccusationRequest');
        $itemsPerPage = C('ITEMS_PER_PAGE');
        import("THINK.Page");
        switch($case) {
            case 'task_hall_unallocated'://未分配
                $map['operation'] = array('EQ',0);
                break;
            case 'task_hall_allocated'://已分配且未处理
                $map['operation'] = array('EQ',1);
                $map['status']    = array('EQ',0);
                break;
            case 'task_hall_processed'://已处理或已取消认证
                $map['operation'] = array('IN',array(2,3));
                break;
            case 'admin_task_unprocessed'://已分配,未处理,aid对应
                $map['operation'] = array('EQ',1);
                $map['aid']     = array('EQ',$_SESSION['authId']);
                $map['status']    = array('EQ',0);
                break;
            default:
                break;
        }
        $count = $Model->task_hall_count($map);
        $Page  = new \Think\Page($count, $itemsPerPage);
        $ret   = $Model->task_hall_list($map, $Page,$case);
        $ret   = $this->get_display_list($case,$ret);
        $show = $Page->show();

        return array(
            'list'=>$ret,
            'page'=>$show,
        );
    }

    protected function get_display_list($case,$list)
    {
        $reportTypeArr = C('STATE_ACCUSATION_TYPE');
        for($i=0;$i<count($list);$i++){
            $list[$i] = unit_to_time_single($list[$i],'sub_time');
            $list[$i]['report_type'] = $reportTypeArr[$list[$i]['report_type']];
            $User = D('User');
            $userItem = $User->get_single_item('uid='.$list[$i]['offender_uid'],'dblocking_time');
            $list[$i]['dblocking_status'] = $userItem['dblocking_time']>time() ? "forbidden" : "normal";
        }

        return $list;
    }

    /*
     * 任务大厅分配任务
     * 未分配里进行分配按用户进行归类;
     * 已分配里进行再分配按allocate_group_id进行归类,不再修改allocate_group_id
     * @param data array(1=>'1:1:16:23',...,'aid'=>1) 冒号隔开的是：request表id，report表id，举报人,被举报人
     * */
    public function allocate_task_to_admin($data,$auto=null)
    {
        if($auto == null){
            $admin_id = $this->admin_permission(C('ACTION_ALLOCATE_ACCUSATION'));
            if(!$admin_id)
                $this->error('没有权限');

            if((!isset($_POST['aid']))||($_POST['aid']=='请选择管理员')){
                $this->error('请选择管理员');
            }
        }

        $aid  = $data['aid'];
        $time = date('Y-m-d H:i:s',time());
        $in   = array();
        unset($data['aid']);

        foreach($data as $value){
            $temp          = explode(':',$value);
            $id            = $temp[0];
            $accusation_id = $temp[1];
            $uid           = $temp[2];
            $offender_uid  = $temp[3];
            array_push($in,$id);
            $logItems[] = array('accusation_id'=>$accusation_id,'operation'=>1,'aid'=>$aid,'allocate_time'=>$time,'uid'=>$uid,'offender_uid'=>$offender_uid);
        }
        $dataItem = array('operation' => 1,'aid' => $aid,'allocate_time' => $time);
        $map = array('id'=>array('IN',$in));
        $DataModel = D('AccusationRequest');
        $DataModel->update_multi_items($map,$dataItem);

        $LogModel = D('AccusationRequestLog');
        $LogModel->insert_multi_items($logItems);
    }

    /*
     * 客服组成员审核完一条记录后自动补入一条记录
     * */
    public function auto_allocate_single_request()
    {
        $Model = D('AccusationRequest');
        $map   = array('operation'=>array('EQ',0));
        $items = $Model->get_limit_items($map,1,'id,accusation_id');
        if(count($items)>0){
            $item  = current($items);
            $data  = array();
            $data['aid'] = $_SESSION['authId'];
            array_push($data,$item['id'].':'.$item['accusation_id'].':'.$item['uid'].':'.$item['offender_uid']);
            $this->allocate_task_to_admin($data,$auto=1);
        }
    }

    /*
     * 任务大厅确认删除已审核request
     * */
    public function confirm_delete_request($list)
    {
        $admin_id = $this->admin_permission(C('ACTION_CONFIRM_ACCUSATION_PROCESSED'));
        if(!$admin_id)
            $this->error('没有权限');

        $Req     = D('AccusationRequest');
        $map['id'] = array('IN',$list);
        $Req->delete_multi_items($map,$list);
    }

    // 根据年月获取车类审核信息
    public function getdateaccsation($date,$aid=''){
        $video = D('AccusationRequestLog');
        $map['status']  = array('gt',1); // 处理过的举报
        if($aid){
            $map['aid'] = $all['aid'] = $return['aid'] = $aid;
        }

        for($i=1; $i<=31; $i++){ 
            if($i<10){
                $das = '-0'.$i;
            }else{
                $das = '-'.$i;
            }
            $map['reason']           = 1; // 色情传播
            $map['certificate_time'] = array('like','%'.$date.$das.'%');
            $day['1'][$i]  = $video->where($map)->count();

            $map['reason']           = 2; // 欺诈&广告
            $map['certificate_time'] = array('like','%'.$date.$das.'%');
            $day['2'][$i]  = $video->where($map)->count();

            $map['reason']           = 3; // 招嫖卖淫
            $map['certificate_time'] = array('like','%'.$date.$das.'%');
            $day['3'][$i]  = $video->where($map)->count();

            $map['reason']           = 4; // 违法&反动政治
            $map['certificate_time'] = array('like','%'.$date.$das.'%');
            $day['4'][$i]  = $video->where($map)->count();

            $map['reason']           = 5; // 其他
            $map['certificate_time'] = array('like','%'.$date.$das.'%');
            $day['5'][$i]  = $video->where($map)->count();

            $map['reason']           = 6; // 托
            $map['certificate_time'] = array('like','%'.$date.$das.'%');
            $day['6'][$i]  = $video->where($map)->count();

            $map['reason']           = 7; // 骚扰
            $map['certificate_time'] = array('like','%'.$date.$das.'%');
            $day['7'][$i]  = $video->where($map)->count();

            $day['count'][$i]  = $day['1'][$i] + $day['2'][$i] + $day['3'][$i] + $day['4'][$i] + $day['5'][$i] + $day['6'][$i]+ $day['7'][$i];

            $all['certificate_time'] = array('like','%'.$date.$das.'%');
            $day['all'][$i]  = $video->where($all)->count();

            $return['status']  = 1;  
            $return['certificate_time'] = array('like','%'.$date.$das.'%');
            $day['return'][$i] = $video->where($return)->count();


        }
        return $day;
    }


    // 举报操作日志
    public function operationlog($data){
        $map['status']           = array('gt',0);
        ($data['aid']>0)?$map['aid'] = $data['aid']:'';
        ($data['uid']>0)?$map['uid'] = $data['uid']:'';
        $map['certificate_time'] = array('between',array($data['s_date'],$data['t_date']));
        
        $itemsPerPage = C('ITEMS_PER_PAGE');
        $count = D('AccusationRequestLog')->where($map)->count();
        //载入分页类,核心类
        import("THINK.Page");
        $Page = new \Think\Page($count, $itemsPerPage);
        $show = $Page->show();

        $vlist = D('AccusationRequestLog')->field('certificate_time,uid,aid,result,remark')->where($map)->limit($Page->firstRow,$Page->listRows)->select();
        $user  = D('Admin')->get_multi_items('','aid,nickname');
        foreach ($user as $k => $u) {
            $uarray[$u['aid']] = $u['nickname'];
        }
        
        foreach ($vlist as $key => $value) {
            $vlist[$key]['aid'] = strtr($vlist[$key]['aid'],$uarray);
        }

        $data['list'] = $vlist;
        $data['show'] = $show;
        return $data;

    } 




}
