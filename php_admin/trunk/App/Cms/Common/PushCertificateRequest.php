<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/12/30
 * Time: 16:11
 */
class PushCertificateRequest
{
    public function test()
    {
        $param   = array('type'=>2,'status'=>0);
        $data    = array('uid'=>71);
        $this->push_unallocated_certificate_request($param,$data);
    }

    /*
     * php server端添加未分配认证任务
     * @param param  array  $data = array('status','type')
     * @param data   array
     *               车辆认证$data = array('uid','num')
     *               视频认证$data = array('type','uid')
     * @return ret   bool
     * */
    public function push_unallocated_certificate_request($param = array(), $data = array())
    {
        $ret = true;

        //状态必须为0
        if ((!isset($param['status'])) || ($param['status'] != 0) || (!isset($param['type'])) || (!isset($data['uid'])))
            return false;

        switch ($param['type']) {
            case '1'://车辆认证
                if (!isset($data['num']))
                    $data['num'] = 1;
                $mysql = new ProcessMysql();
                $mysql->process_mysql('cj_certificate_car_req_unallocated', $data);
                break;
            case '2'://视频认证
                $mysql = new ProcessMysql();
                $mysql->process_mysql('cj_certificate_video_req_unallocated', $data);
                break;
            default:
                $ret = false;
        }

        return $ret;
    }
}

class ProcessMysql{
    public  function process_mysql($table,$data)
    {
        $ret = true;

        if($sql = $this->get_sql($table,$data)){
            mysql_query($sql);
        }
        else{
            $ret = false;
        }

        return $ret;
    }

    /*
     * 根据数据表及数据生成sql语句
     * */
    protected function get_sql($table,$data)
    {
        switch($table)
        {
            case 'cj_certificate_car_req_unallocated':
                if(isset($data['uid'])&&isset($data['num'])){
                    $uid = $data['uid'];
                    $num = $data['num'];
                    $ret = "INSERT INTO ".$table."(uid,num) VALUES (".$uid.",".$num.");";
                }
                else{
                    $ret = false;
                }
                break;
            case 'cj_certificate_video_req_unallocated':
                if(isset($data['uid'])){
                    $uid = $data['uid'];
                    $ret = "INSERT INTO ".$table."(uid) VALUES (".$uid.");";
                }
                else{
                    $ret = false;
                }
                break;
            default:
                $ret = false;
                break;
        }

        return $ret;
    }
}
