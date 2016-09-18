<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2015/1/6
 * Time: 18:57
 */

namespace Cms\Model;

use Cms\Model;
class ReportModel extends CjDataModel{

    /*
     * 获取单条记录
     * */
    public function get_single_item($map,$field='')
    {
        $Model = D('Report');
        if($field == '')
            $ret = $Model->where($map)->find();
        else
            $ret = $Model->where($map)->field($field)->find();

        return $ret;
    }

    public function get_multi_items($map,$field='')
    {
        $Model = D('Report');
        if($field == '')
            $ret = $Model->where($map)->select();
        else
            $ret = $Model->where($map)->field($field)->select();

        return $ret;
    }

    /*
     * 更新单条记录
     * */
    public function update_single_item($map,$data)
    {
        $Model = D('Report');
        $Model->where($map)->data($data)->save();
    }

    public function update_multi_items($map,$data)
    {
        $Model = D($this->name);
        $Model->where($map)->data($data)->save();
    }

    public function insert_single_item($data)
    {
        $Model = D($this->name);
        $Model->data($data)->add();
    }

    public function insert_multi_items($data)
    {
        $Model = D($this->name);
        $Model->addAll($data);
    }

    /*
     * 删除单条记录
     * */
    public function delete_single_item($map)
    {
        $Model = D('Report');
        $Model->where($map)->delete();
    }

    /*
     * 批量删除数据
     * */
    public function delete_multi_items($map)
    {
        $Req = D('Report');
        $Req->where($map)->delete();
    }

    /*
     * 获取针对同一uid的多条举报记录中封禁时间最长的一条记录
     * */
    public function get_max_blocking_time_item($uid)
    {

    }

    /*
     *
     * */
    public function insert_items_for_same_offender()
    {
        $resetModel = C('RESET_MODEL');
        switch($resetModel)
        {
            case 'local':
                for($i=30;$i<33;$i++){
                    $items[] = array(
                        'uid'=>$i,
                        'offender_uid'=>23,
                        'dtime'=>1423021226,
                        'report_type'=> 2,
                    );
                }
                $Model = D($this->name);
                $Model->insert_multi_items($items);
                break;
            case 'server':
                break;
            default:
                $this->error('测试配置出错');
                break;
        }
    }

    /*
     * 重置测试数据
     * */
    public function reset_test_data()
    {
        $resetModel = C('RESET_MODEL');
        switch($resetModel)
        {
            case 'local':
                $in  = array();
                for($i=1;$i<9;$i++){
                    array_push($in,$i);
                }
                $item  = array(
                    'report_type'=>1,
                    'reason'=>0,
                    'status'=>0,
                    'dtime'=>time(),
                    'atime'=>0,
                );
                $map   = array('id'=>array('IN',$in));
                $Model = D($this->name);
                $Model->update_multi_items($map,$item);

                //删除多余的记录
                $map   = array('id'=>array('GT',8));
                $Model->delete_multi_items($map);
                break;
            case 'server':
                break;
            default:
                $this->error('测试配置出错');
                break;
        }
    }

    /*
     * 获取被举报用户数量
     * */
    public function get_count($map=null)
    {
        $Report = D('Report');
        $ret    = $Report->where($map)->group('offender_uid')->count();
        return $ret;
    }

    /*
     * 获取被举报人信息
     * */
    public function lists($map,$Page)
    {
        $Report = D('Report');
        $ret    = $Report->join('cj_user ON cj_user.uid=cj_report.offender_uid')
                         ->group('cj_report.offender_uid')
                         ->where($map)
                         ->field('cj_report.offender_uid as uid,
                                  cj_report.atime as atime,
                                  cj_report.dblocking_time as dblocking_time,
                                  cj_user.phone as phone,
                                  cj_user.reg_time as reg_time,
                                  cj_user.sex as sex')
                         ->limit($Page->firstRow.','.$Page->listRows)
                         ->select();
        //$sql = $Report->getLastSql();
        return $ret;
    }

    /*
     * 获取被举报人未处理的举报表 id
     * */
    public function getreportidlsit($uid){
        $list   = D('Report')->field('id,uid')->where('offender_uid ='.$uid.' AND status = 0 ')->order('id DESC')->select();
        if(!empty($list)){
            $idstr  = '';
            $uid    = '';
            foreach ($list as $value) {
                $idstr .= $value['id'].',';
                $uid   .= $value['uid'].',';
            }
            $array['idstr']  =  rtrim($idstr,',');
            $array['uid']    =  rtrim($uid,',');
            return $array;
        }
    }



}
