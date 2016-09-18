<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2015/1/15
 * Time: 18:06
 */

namespace Cms\Model;

use Cms\Model;
class LocationModel extends CjDataModel{

    /*
     * 删除单条记录
     * */
    public function delete_single_item($map)
    {
        $Location = D($this->name);
        $Location->where($map)->delete();
    }

    public function update_single_item($map,$data)
    {
        $Location = D($this->name);
        $Location->where($map)->data($data)->save();
    }

    public function truncate_table()
    {
        $Location = D($this->name);
        $sql = "TRUNCATE ".$this->trueTableName;
        $count = $Location->count();
        if($count>0)
            $Location->execute($sql);
    }

    /*
     * 重置测试数据
     * */
    public function reset_test_data()
    {
        $resetModel = C('RESET_MODEL');//判断是本地还是线上测试
        switch($resetModel)
        {
            case 'local':
                $this->truncate_table();
                $sql = "INSERT INTO cj_location (uid,lat,lng,geohash,update_time,sex,car_verify,video_verify) VALUES
                (23, 31.1003, 121.502, 'wtw2uvek25tr', 1422846495, 0, 1, 1),
                (26, 31.1003, 121.502, 'wtw2uvehpeer', 1422846495, 0, 1, 1);";
                $Location = D($this->name);
                $Location->execute($sql);
                $sql = $Location->getLastSql();
                break;
            case 'server':
                break;
            default:
                $this->error('测试配置出错');
                break;
        }
    }

    // 用户最近一次登录
    public function lastlogin($uid){

        return D('Location')->field('update_time')->where(" uid = $uid ")->order('update_time DESC')->limit('0,1')->find();


    }

    /**
     * 获取某些用户的最近操作时间
     * */
    public function usersUpdateTime($uidStr) {
        $ret  = array();
        $Location = D('Location');
        $sql = "select uid,update_time from cj_location where uid in ".$uidStr;
        $data = $Location->query($sql);
        foreach($data as $value) {
            $uid = $value['uid'];
            $time = $value['update_time'];
            $ret[$uid] = $time;
        }
        return $ret;
    }

}
