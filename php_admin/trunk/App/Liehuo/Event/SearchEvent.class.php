<?php
/**
 * Created by PhpStorm.
 * User: zsj
 * Date: 2015/5/15
 * Time: 18:00
 */

namespace Liehuo\Event;
use Cms\Controller\MessageController;
use Cms\Event;
use Think\Cache\Driver\Redis;

class SearchEvent extends PublicEvent{

    // 标签搜索
    public function getlist($get){

        if(!empty($get['search'])){
            $order = 'ORDER BY '.$get['search'].' DESC ';
        }
        if($get['keyword']){
            $where = " AND  tu.id  = ".$get['keyword']." ";
        }
        if($get['start']!='' && $get['end']!=''){
            $start = substr($get['start'],0,10).' '.substr($get['start'],-8);
            $end   = substr($get['end'],0,10).' '.substr($get['end'],-8);
            $where .= ' AND tu.create_time BETWEEN '.strtotime($start).' AND '.strtotime($end).' ';
        }

        if($get['status']!=''  ){
            $where .= ' AND  u.status = '.$get['status'].'  ';

        }
        $csql = "SELECT  count(tu.id) as num
                FROM      cj_user_tag  AS tu
                LEFT JOIN cj_user      AS u  ON tu.uid = u.uid
                WHERE     t.certificate = 2 {$where}
                ";
        $count = D('UserTag')->query($csql);

        import("THINK.Page");
        $Page = new \Think\Page($count[0]['num'],50);
        $show = $Page->show();

        $sql = "SELECT tu.id,tu.title,COUNT(tu.uid) AS u_num,tu.thumb_up_cnt AS sum,tu.create_time
                FROM      cj_user_tag   AS tu
                LEFT JOIN cj_user       AS u  ON tu.uid = u.uid
                WHERE     tu.certificate = 2 {$where}
                GROUP BY  tu.id ".$order.' LIMIT '.$Page->firstRow.','.$Page->listRows;
        $data['list'] = D('UserTag')->query($sql);
        $data['page'] = $show;
        return $data;
    }
    
    // 用户搜索
    public function getuserlist($get){
        

        if( $get['sex'] !== '' && isset($get['sex'])){ // 性别
            $and .= ' AND r.sex = '.$get['sex'];
        }
        if(!empty($get['video_verify'])){ // 视频
            $and .= ' AND r.video_verify = 1 ';
        }
        if(!empty($get['uid'])){ // 用户UID
            $and .= ' AND r.uid = '.$get['uid'];
        }
        if($get['status']!=''  ){
            $and .= ' AND r.status = '.$get['status'].'  ';
        }

        if($get['tag']!=''){ // 标签ID
            $and .= ' AND u.id = '.$get['tag'];
        }
        if($get['start']!='' && $get['end']!=''){
            $and .= ' AND u.create_time BETWEEN '.strtotime($get['start']).' AND '.strtotime($get['end']).' ';
        }
        if($get['search']!=''){ // 排序方式
            $order = 'ORDER BY '.$get['search'].' DESC ';
        }

        $csql = "SELECT count(DISTINCT uid) num FROM cj_user WHERE uid IN (SELECT  u.uid
                 FROM      cj_user_tag          AS u
                 LEFT JOIN cj_user              AS r  ON u.uid    = r.uid
                 WHERE u.certificate = 2  $and  GROUP BY u.uid) ";
        $count =  D('UserTag')->query($csql);

        import("THINK.Page");
        $Page = new \Think\Page($count[0]['num'],50);
        $show = $Page->show();

        $sql = " SELECT r.nickname,r.uid,r.phone,r.sex,u.id,u.title,u.thumb_up_cnt
                 FROM      cj_user_tag          AS u
                 LEFT JOIN cj_user              AS r  ON u.uid    = r.uid
                 WHERE u.certificate = 2  $and
                 GROUP BY u.uid $order  LIMIT ".$Page->firstRow.','.$Page->listRows;
        $list_1 =  D('UserTag')->query($sql);
        if(!empty($list_1)){
            $usertag = D('UserTag');
            foreach($list_1 as $key => $val){
                $list_1[$key]['taglist'] = $usertag->searchs('uid ='.$val['uid'],'id,title,thumb_up_cnt');
            }
        }

        if(!empty($list_1)){
            //判断是否有更新
            $surging = D('Surging');
            foreach($list_1 as $key => $val){
                $sql    = "select count(uid) as num  from cj_surging  where uid = {$val['uid']}  and from_unixtime(create_time,'%Y-%m-%d') = date_format(now(),'%Y-%m-%d')";
                $update = $surging->query($sql);
                if($update[0]['num']>0){$list_1[$key]['update'] = $update[0]['num']; }
            }
            $data['list'] = $list_1;

            // 被推荐次数  
            $uidstr = implode(',',array_column($list_1,'uid','uid'));
            $sql    = "SELECT uid,count(uid) AS num  FROM cj_recommend_user WHERE uid IN($uidstr) GROUP BY uid ";
            $data['num'] = array_column(D('RecommendUser')->query($sql),'num','uid');
        }

        return array('list'=>$data,'page'=>$show);
    }

    // 金币搜索
    public function getgoldlist($get){
        $stauts = ($get['status']!='')?$get['status']:0;
        $and = '  status = '.$stauts.' AND ';

        if($get['uid']){
            $and .= '  uid = '.$get['uid'].' AND ';
        }
        $sql  = "SELECT uid,sex,gold_coin_cnt,phone FROM cj_user WHERE 1 AND $and gold_coin_cnt > 0 ORDER BY gold_coin_cnt DESC ";
        $list = D('User')->query($sql);
        $gold = D('CoinIncome');
        foreach($list as $k =>$val){
            $gsql = "SELECT sum(num) as allg
                    FROM cj_coin_income
                    WHERE state = 1 AND uid = {$val['uid']} AND from_unixtime(recv_time,'%Y-%m-%d') = curdate()";
            $nowt = $gold->query($gsql);
            $list[$k]['allg'] = $nowt[0]['allg'];
        }
        return $list;
    }

    // 金币详情 +金币
    public function getgoldinfo($get){
        $model   = D('User');
        $sql_add = "SELECT DISTINCT i.*,u.uid,u.sex
                    FROM cj_coin_income   AS i
                    LEFT JOIN cj_user     AS u ON i.uid = u.uid
                    WHERE i.uid = $get[uid] ORDER  BY i.id DESC ";

        $sql_dec = "SELECT DISTINCT i.*,u.uid,u.sex
                    FROM    cj_coin_log   AS i
                    LEFT JOIN cj_user     AS u ON i.uid = u.uid
                    WHERE i.uid = $get[uid] ORDER  BY i.id DESC ";

        $data['dec'] = $model->query($sql_dec);
        $data['add'] = $model->query($sql_add);
        return $data;
    }


    // 获取系统标签
    public function getsystemtag($name='今日兑换'){
        $sql = "SELECT u.id,u.title,u.uid,u.id AS u_tag
                FROM      cj_user_tag AS u
                WHERE   u.title = '$name' ";
        $info = D('UserTag')->query($sql);
        return $info[0];
    }


    // 获取奖品、活动列表
    public function hostpasiz($name){
        $id = $this->getsystemtag($name);
        $id = $id['u_tag'];

        $sql= " SELECT s.id,s.uid,s.thumb,s.create_time,g.user_tag_id,g.surging_id,s.description
                FROM       cj_user_tag_surging  AS g
                LEFT JOIN  cj_surging           AS s  ON s.id = g.surging_id
                WHERE  g.user_tag_id = $id AND s.uid !='' ORDER BY s.id DESC
                 ";
        return D('UserTag')->query($sql);

    }

    // 获取库详情
    public function getlibrary($id){
        $data['info'] = D('Library')->get_single_item('id= '.$id);
        $user = D('LibraryUser')->get_multi_items('library_id = '.$id);
        $data['user'] = $user;
        $data['uid']  = implode(',',array_column($user,'uid'));
        return $data;
    }

    // 用户库验证
    public function libraryck($uid){
        return array_column(D('User')->get_multi_items("uid IN($uid)"),'uid');
    }

    // 标签审核
    public function tagck($type='0'){
        $map['certificate'] = $type;
        $model = D('UserTag');
        switch ($type){ 
            case '0':   // 0未审核 
                $list = $model->get_multi_items($map);
                break;
            case '1':   // 1审核失败 
                $sql = "SELECT  tu.id,tu.title,l.remarks,a.nickname
                        FROM       chujian.cj_user_tag     AS tu
                        LEFT JOIN  cj_admin_log.cj_tag_log AS l ON tu.id  = l.tag_id
                        LEFT JOIN  cj_admin.cj_admin       AS a ON a.aid = l.aid 
                        WHERE tu.certificate = 1  ";
                $list= D('User')->query($sql);
                break;
            default:    // 2审核通过
                $list = $model->get_multi_items($map);
                break;
        }

        /*echo '<pre>';
        print_r($list);*/
        return $list;

    }

    // 删除tag 用户缓存
    public function deltagredis($uid){
        if($uid){
            $phpServer = D('PhpServerRedis');
            foreach (explode(',',$uid) as $key => $value) {
                //删除php server 用户标签
                $phpServer->delete_user_tag($value);
            }
        }
    }
    
    //  tag 不通过 tag id,  remarks 备注
    public function tagnopass($id,$remarks){
        $Message = new MessageController();
        // 1.删除标签 (修改标签状态)
        D('UserTag')->update_single_item('id='.$id,array('certificate'=>1));

        // 2.删除 用户标签关系
        $user_tag = D('UserTag')->get_multi_items('id='.$id,'uid,create_time');
        D('UserTag')->delete_single_item('id='.$id);

        // 3.写入 log日志
        foreach ($user_tag as $key => $value) {
            $data[$key]['aid']         = $_SESSION['authId'];
            $data[$key]['ck_time']     = time();
            $data[$key]['certificate'] = 1;
            $data[$key]['remarks']     = $remarks;
            $data[$key]['tag_id']      = $id;
            $data[$key]['uid']         = $value['uid'];
            $data[$key]['create_time'] = $value['create_time'];
            // 4.发送 im 
            $Message->send_system_message($value['uid'],'search','tag','很遗憾您的标签未通过审核，重新认证'); 
        }
        D('TagLog')->insert_multi_items($data);

    }

    // 给所有通过标签审核的用户发送im消息 
    public function sendimtag($tag){
        $list = D('UserTag')->get_multi_items("id IN($tag)",'uid');
        if(!empty($list)){
            $Message = new MessageController();
            foreach ($list as $key => $value){
                $Message->send_system_message($value['uid'],'search','tag','恭喜你通过标签认证！立即寻找更多有意思的伙伴'); 
            }
        }
    }


 

    // 活动后台
    public function getachostset($get,$post=array()){

        if( $post['uid'] !='' && $post['tag'] !='' ){
            $uidlist = $post['uid'];
            $and     = " AND u.id IN({$post['tag']}) ";

        }else{
            $id = $get['lib'];
            $library = D('LibraryUser')->get_multi_items("library_id = $id and gold = 0  ",'uid');
            $uidlist = implode(',',array_column($library,'uid'));

            if($get['valid_begin']!='' && $get['valid_end']!=''){
                $start = strtotime($get['valid_begin']);
                $end   = strtotime($get['valid_end']);
                $and   = " AND u.create_time BETWEEN  $start AND $end ";
            }

        }

        $sql = " SELECT r.nickname,r.uid,u.id,u.title,r.sex,p.thumb_up_id
                 FROM      cj_user_tag          AS u
                 LEFT JOIN cj_user_tag_thumb_up AS p  ON u.id     = p.user_tag_id 
                 LEFT JOIN cj_user              AS r  ON u.uid    = r.uid
                 WHERE  u.certificate = 2 AND  u.uid IN($uidlist) $and ORDER BY p.thumb_up_id DESC
                 " ;
        $data['list'] =  get_group(D('UserTag')->query($sql),'nickname');
        
        return $data;

    }

    /**
    *  发放金币 
    *  user  array 用户 
    *  golds array 金币 
    *  lib   int   库id
    **/ 
    public function sendgold($user,$golds,$lib){

        $Message     = new MessageController();
        //$user_model  = D('User');
        $coin_income = D('CoinIncome');
        $coin_log    = D('CoinLog');
        $lib_user    = D('LibraryUser');
        $i = 0;
        $n = 0;
        foreach ($user as $key => $value) {
            $uid   = $value;
            $num   = $golds[$key];

            
            //$user_model->where('uid='.$uid)->setInc('gold_coin_cnt',$num);  
            // 1.活动库用户金币记录
            $lib_user->where("uid = $uid AND library_id = $lib ")->setInc('gold',$num);  

            // 2.记录金币发放日志
            $coin[$key]['uid']       = $uid;
            $coin[$key]['num']       = $num;
            $coin[$key]['state']     = 0;
            $coin[$key]['source']    = 5;
            $coin[$key]['send_time'] = time();
            $coin[$key]['valid_time']= $coin[$key]['send_time']+86400*3;
            
            $coinlog[$key]['uid']    = $uid;
            $coinlog[$key]['num']    = $num;
            $coinlog[$key]['create_time']  = time();

            // 3.发送 im 消息
            $Message->send_system_message($uid,'search','golds');

            $i++;
            $n += $num;

        }
        $coin_income->insert_multi_items($coin);
        $coin_log   ->insert_multi_items($coinlog);

        return '共发放用户'.$i.'个，金币总量为 '.$n;

    }

    // 邀请码
    public function invitation($type,$where=''){

        //载入分页类,核心类
        import("THINK.Page");

        $uid   = intval($where['uid']);
        if($uid!=''){
            $getcode = D('Invitation')->search('oid ='.$uid,'code');
            $where['code'] = ($getcode['code']!='')?$getcode['code']:$where['code'];
        }

        $wcode = $where['code'];
        $and = ($where['code']!='')?" AND ic.code LIKE '%{$wcode}%' ":'';

        if($type =='1'){    // 官方

            $count = D('InvitationCode')->where('code_type = 1 '.$and)->count();
            $Page = new \Think\Page($count, 80);

            $show = $Page->show();
            $sql = "SELECT ic.*,COUNT(i.code) AS reg,td.num
                    FROM cj_invitation_code AS ic 
                    LEFT JOIN cj_invitation AS i   ON i.code = ic.code
                    LEFT JOIN (select code,count(code) as num from cj_invitation where from_unixtime(used_time,'%Y-%m-%d') =   date_format(now(),'%Y-%m-%d') group by code  ) as td ON td.code = ic.code
                    WHERE ic.code_type = 1 {$and}  GROUP BY ic.code ORDER BY reg desc LIMIT ".$Page->firstRow.','.$Page->listRows;
        }else{
            $count = D('InvitationCode')->where('code_type = 0 '.$and)->count();
            $Page = new \Think\Page($count, 50);

            $sql = "SELECT ic.*,u.nickname,u.uid,COUNT(i.code) AS reg,td.num
                    FROM      cj_invitation_code AS ic
                    LEFT JOIN cj_invitation      AS i  ON i.code = ic.code
                    LEFT JOIN cj_user            AS u  ON i.uid  = u.uid
                    LEFT JOIN (select code,count(code) as num from cj_invitation where from_unixtime(used_time,'%Y-%m-%d') =   date_format(now(),'%Y-%m-%d') group by code  ) as td ON td.code = ic.code
                    WHERE ic.code_type = 0 GROUP BY ic.code ORDER BY reg desc LIMIT ".$Page->firstRow.','.$Page->listRows;
        }

        $list = D('UserTag')->query($sql);
        foreach($list as $k =>$val){
            $list[$k]['reg']     = D('Invitation')->where("code = '{$val['code']}'")->count();
            $list[$k]['usertag'] = $this->getusercodes($val['code']);
        }
        return array('list'=>$list,'page'=>$show);
    }

    // 获取当前验证码注册用户认证的数量
    public function getusercodes($code){
        $users = D('Invitation')->get_multi_items("code = '{$code}' ",'oid');
        $users = implode(',',array_column($users,'oid'));
        $data['all']      = D('UserTag')->distinct(true)->field('uid')->where("uid IN ($users) ")->count();
        $data['all_day']  = D('UserTag')->distinct(true)->field('uid')->where("uid IN ($users) AND from_unixtime(create_time,'%Y-%m-%d') =   date_format(now(),'%Y-%m-%d') ")->count();
        $data['pass']     = D('UserTag')->distinct(true)->field('uid')->where("uid IN ($users) AND verify = 1 ")->count();
        $data['pass_day'] = D('UserTag')->distinct(true)->field('uid')->where("uid IN ($users) AND verify = 1  AND from_unixtime(create_time,'%Y-%m-%d') =   date_format(now(),'%Y-%m-%d') ")->count();
        return $data;
    }

    /**
     *  @生成验证码
     *  @类型  type int  
     *  @数量  num  int  
     *
    */ 
    public function codecreate($type='0',$num){
        /*if($num>50){
            $this->error('最多可以创建50个');
        }*/
        for($i=0; $i <$num; $i++) { 
            $data[$i]['code']        = $this->encodeID('int');
            $data[$i]['code_type']   = 1;
            $data[$i]['uid']         = 10001;
            $data[$i]['used_limit']  = 30000; 
            $data[$i]['create_time'] =  time();
        }

        return D('InvitationCode')->insert_multi_items($data);
    }

    public function encodeID($type=''){
        if($type=='int'){
            $code = rand(10,99).rand(10,99).rand(10,99);
        }else{
            static $guid = '';
            $uid = uniqid ( "", true );
            $data .= $_SERVER ['REQUEST_TIME'];     // 请求那一刻的时间戳
            $data .= time().rand(0,9000).rand(10000,9000);

            $hash = strtoupper ( hash ( 'ripemd128', $uid . $guid . md5 ( $data ) ) );
            $code = substr ( $hash,rand(0,10), 6);
        }

        $codeid = D('InvitationCode')->get_single_item("code = '{$code}'",'id');
        if($codeid['id']>0  || strlen($code) !=6 ){
            $this->encodeID($type);
        }else{
            return $code;
        }

    }

    /*
     * @ 标签任务大厅
     * @ $type 1. 未分配   2.已分配  3.已处理
     *
     * */
    public function tagfor($type,$flag=''){

        $UserTag = D('UserTag');
        $TagLog  = D('TagLog');

        //载入分页类,核心类
        import("THINK.Page");

        if($type=='1') {

            $tagid = implode(',', array_column(D('TagLog')->searchs('tag_id'), 'tag_id'));
            $count = $UserTag->field('id')->where("id NOT IN($tagid)")->count();
            if($flag=='yes'){
                return $count;
            }
            $Page = new \Think\Page($count,50);
            $list = $UserTag->field('id,title,tag_class_id,create_time')->where("id NOT IN($tagid)")->limit($Page->firstRow.','.$Page->listRows)->select();

        }elseif($type==2){

            $usgsql   = "SELECT user_tag_id FROM cj_user_tag_surging WHERE user_tag_id >0 GROUP BY user_tag_id";
            $allsearc = array_column(D('UserTagSurging')->query($usgsql),'user_tag_id');
            $usertaid = implode(',',$allsearc);

            $csql = "SELECT count(t.id) num
                FROM chujian.cj_user_tag              AS t
                LEFT JOIN cj_admin_log.cj_tag_log     AS l ON t.id  = l.tag_id
                LEFT JOIN cj_admin.cj_admin           AS a ON l.aid = a.aid
                WHERE l.certificate = 0 AND t.certificate = 0  AND l.tag_id IN($usertaid) ORDER BY t.id DESC  ";
            $count = D('User')->query($csql);

            $Page = new \Think\Page($count[0]['num'],50);

            $sql = "SELECT t.id,t.title,t.tag_class_id,l.distri,a.nickname,t.certificate,t.uid,t.create_time
                FROM chujian.cj_user_tag              AS t
                LEFT JOIN cj_admin_log.cj_tag_log     AS l ON t.id  = l.tag_id
                LEFT JOIN cj_admin.cj_admin           AS a ON l.aid = a.aid
                WHERE l.certificate = 0 AND t.certificate = 0  AND l.tag_id IN($usertaid) ORDER BY a.aid DESC   LIMIT ".$Page->firstRow.','.$Page->listRows;
            $list = D('User')->query($sql);
        }else{
            $csql = "SELECT count(t.id) num
                    FROM chujian.cj_user_tag                AS t
                    LEFT JOIN cj_admin_log.cj_tag_log  AS l ON t.id  = l.tag_id
                    LEFT JOIN cj_admin.cj_admin                 AS a ON l.aid = a.aid
                    WHERE l.confirm <= 0 AND t.certificate >0  ";
            $count = D('User')->query($csql);
            $Page = new \Think\Page($count[0]['num'],100);

            $sql = "SELECT t.id,t.title,l.distri,a.nickname,t.certificate,t.tag_class_id,t.create_time,t.uid
                    FROM chujian.cj_user_tag                AS t
                    LEFT JOIN cj_admin_log.cj_tag_log  AS l ON t.id  = l.tag_id
                    LEFT JOIN cj_admin.cj_admin                 AS a ON l.aid = a.aid
                    WHERE l.confirm <= 0 AND t.certificate >0  LIMIT ".$Page->firstRow.','.$Page->listRows;
            $list = D('User')->query($sql);
        }
        $show = $Page->show();
        return array('list'=>$list,'page'=>$show);

    }

    // 分配标签
    public function allocate_task_to_tag($post){
        $aid  = $post['aid'];
        if(intval($aid) == '' ){
            $this->error('请选择管理员！');
        }
        if($post['id'][0]==''){
            $this->error('请选择标签！');
        }
        $usertag = D('UserTag');
        $taglog  = D('TagLog');
        foreach($post['id'] as $key => $val ){
            $have = $taglog->search('tag_id = '.$val,'id');
            if($have['id']==''){
                $info = $usertag->search('id ='.$val);
                $data[$key]['tag_id']  = $val;
                $data[$key]['uid']     = $info['uid'];
                $data[$key]['remarks'] = $info['title'];
                $data[$key]['distri']  = time();  // 分配时间
                $data[$key]['aid']     = $aid;    // 分配管理员
            }
        }
        D('TagLog')->addAll($data);
        header('location:'.U('search/tagfor',array('type'=>1)));

    }

    // 重新分配分配标签
    public function allocate_task_again_to_tag($post){
        $aid  = $post['aid'];
        if(intval($aid) == '' ){
            $this->error('请选择管理员！');
        }
        if($post['id'][0]==''){
            $this->error('请选择标签！');
        }
        $usertag = D('UserTag');
        $tagid   = implode(',',$_POST['id']);
        $add     = D('TagLog')->where("tag_id IN($tagid)")->save(array('aid'=>$aid,'distri'=>time()));
        if($add!==false){
            $this->success('重新分配ok！');
        }
    }

    // 我的标签审核任务
    public function mytag($my=''){

        $usgsql   = "SELECT user_tag_id FROM cj_user_tag_surging WHERE user_tag_id >0 GROUP BY user_tag_id";
        $usertaid = implode(',',array_column(D('UserTagSurging')->query($usgsql),'user_tag_id'));
        //载入分页类,核心类
        import("THINK.Page");
        $count = "SELECT count(t.id) as num
                FROM chujian.cj_user_tag              AS t
                LEFT JOIN cj_admin_log.cj_tag_log     AS l ON t.id  = l.tag_id
                LEFT JOIN cj_admin.cj_admin           AS a ON l.aid = a.aid
                WHERE l.certificate = 0 AND t.certificate = 0 AND l.aid = {$_SESSION['authId']} AND t.id IN($usertaid)";
        $cq   = D('User')->query($count);
        if($my=='my'){
            return $cq[0]['num'];
        }
        $Page = new \Think\Page($cq[0]['num'],50);
        $show = $Page->show();
        $sql = "SELECT t.id,t.title,t.tag_class_id,l.distri,a.nickname,t.certificate,t.uid,t.create_time,ic.code,ic.memo
                FROM chujian.cj_user_tag              AS t
                LEFT JOIN cj_admin_log.cj_tag_log     AS l  ON t.id  = l.tag_id
                LEFT JOIN cj_admin.cj_admin           AS a  ON l.aid = a.aid
                LEFT JOIN chujian.cj_invitation       AS i  ON t.uid = i.oid
                LEFT JOIN chujian.cj_invitation_code  AS ic ON i.code = ic.code
                WHERE l.certificate = 0 AND t.certificate = 0 AND l.aid = {$_SESSION['authId']}  AND t.id IN($usertaid)
                ORDER BY t.id DESC LIMIT ".$Page->firstRow.','.$Page->listRows;
        $list = D('User')->query($sql);
        return array('list'=>$list,'page'=>$show);
    }

    /*
     * 标签删除
     * @tag  标签id
     * @name 修改标签名称
     * @type 删除原因  1 / 2
     */
    public function delete_tag($id,$name='',$type){

        $usertag    = D('UserTag');
        $taglog     = D('TagLog');
        $surging    = D('Surging');
        $surginglog = D('SurgingLog');
        $Message    = new MessageController();
        $usertagsurging = D('UserTagSurging');
        $info       = $usertag->search('id = '.$id);


        if($type==1){
            $change = $usertag->where('id = '.$id)->save(array('title'=>$name,'certificate'=>2));
            if($change!==false){
                $taglog->where('tag_id ='.$id)->save(array('certificate'=>4,'remarks'=>$name));
                $scontent = '您的（'.$info['title'].'）标签命名不规范，我们现已为您修正，如需修改请联系我们(意见反馈或给产品提意见)！';
                $Message->send_system_message($info['uid'], 'search', 'tag',$scontent);
                D('PhpServerRedis')->delete_user_tag($info['id']);
                return 'ok';
            }else{
                return 'no';
            }
        }

        // 获取 surging 表 id
        $surging_id = $usertagsurging->get_multi_items("user_tag_id  = {$info['id']}  ",'surging_id');

        if(!empty($surging_id)){
            $s_id   = implode(',',array_column($surging_id,'surging_id'));
            $source = $surging->get_multi_items("id IN ($s_id) ",'id,thumb,resource,uid');

            // 删除动态
            foreach($source as $key => $val){
                $this->aliydel('surging',$val['thumb']);
                if($val['resource']!=''){
                    $this->aliydel('surging',$val['resource']);
                }

                $datasurging[$key]['tag']      = $info['title'];
                $datasurging[$key]['uid']      = $val['uid'];
                $datasurging[$key]['surging']  = $val['id'];
                $datasurging[$key]['status'] = 2;
                $datasurging[$key]['certificate_time'] = time();
                $datasurging[$key]['aid']    = $datasurging[$key]['confirm'] =$_SESSION['authId'];
                $datasurging[$key]['confirm_time']    = time();

            }
            // surging_lo
            $surginglog->addAll($datasurging);
            // 删除 surging
            $surging->delete_multi_items("id IN ($s_id) ");
        }

        if(!empty($info)) {
            // 给标签的用户发消息
            $scontent  = '您的标签（'.$info['title'].'）涉及违规内容，经审核我们已做出删除处理。建议您重新认证您的个性标签！';
            $Message->send_system_message($info['uid'], 'search', 'tag', $scontent);

            // 删除 user_tag
            $usertag->delete_multi_items("id IN ($id) ");
            // 删除关系表
            $usertagsurging->where("user_tag_id  = {$info['id']}")->delete();

            $this->del_r_user_info($info['uid']);
            $this->user_tag_info_ch($info['uid'],'dec');
            // 删除标签缓存
            D('PhpServerRedis')->delete_user_tag($id);

            $datalog['aid'] = $_SESSION['authId'];
            $datalog['ck_time'] = time();
            $datalog['certificate'] = 3;
            $datalog['remarks'] = $info['title'];
            $datalog['uid']     = $info['uid'];
            $datalog['tag_id'] = $id;
            $datalog['confirm'] = $_SESSION['authId'];
            $taglog->add($datalog);
        }
        // 删除 usertag
        $usertag->delete_multi_items("id IN ($id) ");
        return 'ok';
    }   

    // banner 内容获取
    public function bannercontentget($id,$type,$is){

        $model = D('BannerContent');
        if($type==1){
            if($is=='banner_id'){
                $and = " AND s.banner_id = $id ";
            }
            if($is=='recommend_id'){
                $and = " AND s.recommend_id = $id ";
            }
            $sql  = "SELECT c.item as uid,c.id,i.thumb,i.id as surging_id
                     FROM  cj_banner_content   AS c
                     LEFT  JOIN cj_set_surging AS s ON s.uid  = c.item
                     LEFT  JOIN cj_surging     AS i ON i.id   = s.surging_id
                     WHERE c.banner_id = $id $and
                    ";
            $list = $model->query($sql);
            $list = get_group($list,'uid');
        }elseif($type==4){
            $sql  = "SELECT c.id,c.banner_id,s.thumb 
                     FROM cj_banner_content AS c 
                     LEFT JOIN cj_surging   AS s ON c.item = s.id 
                     WHERE c.banner_id = $id
                       ";
            $list = $model->query($sql);
        }else{
            $list = $model->get_multi_items('banner_id = '.$id);
        }
        //printr($list);
        return $list;
    }

    /*
    * 添加banner 内容
    * $item 34 /  31,4,52
    *
     */ 
    public function bannertwo($id,$type,$item){
        // 删除缓存
        $phpServer = D('PhpServerRedis');
        $phpServer->delete_recommend_user();

        $model = D('BannerContent');
        if( $type == 1 || $type == 4 ){
            $list = explode(',',$item);
            foreach ($list as $key => $val) {
                $have = $model->get_single_item(" banner_id = $id  and  item = ".intval($val),'id');
                if($have['id']==''){
                    $data[$key]['banner_id'] = $id;
                    $data[$key]['item']      = intval($val);
                    if($type == 1){
                        $datasg[$key]['uid']       = intval($val);
                        $datasg[$key]['banner_id'] = $id;
                    }
                }
            }
            D('SetSurging')->addAll($datasg);
            $add = $model->addAll($data);
        }else{
            $model->where('banner_id = '.$id)->delete();
            $data['banner_id'] = $id;
            $data['item']      = trim($item);
            $add = $model->add($data);
        }
        if($add!==false){
            $this->redirect('banner/bannertwo',array('id'=>$id,'type'=>$type));
        }else{
            $this->error('添加失败，用户可能已存在！');
        }

    }

    // 首页推荐用户列表
    public function putinfoadduser($uid,$id){

        $list = explode(',',$uid);
        $RecommendUser = D('RecommendUser')->field('id');

        $user = D('User');
        foreach ($list as $key => $val) {
            $r_user = $RecommendUser->where(" uid = $val and group_id = $id ")->find();
            if( $r_user['id'] =='' ){
                $u = $user->where('uid ='.$val)->find();
                $data[$key]['uid'] = $val;
                $data[$key]['sex'] = $u['sex'];
                $data[$key]['group_id'] = $id;

                $datasg[$key]['uid']          = $val;
                $datasg[$key]['recommend_id'] = $id;

            }
        }

        D('SetSurging')->addAll($datasg);
        D('RecommendUser')->addAll($data);

    }

    // 首页推荐用户列表
    public function gettuijianinfo($id){
        $sql  = "SELECT c.uid,c.id,i.thumb,i.id as surging_id
                 FROM  cj_recommend_user   AS c
                 LEFT  JOIN cj_set_surging AS s ON s.uid  = c.uid
                 LEFT  JOIN cj_surging     AS i ON i.id   = s.surging_id
                 WHERE c.group_id = $id and s.recommend_id = $id
                    ";
        $list = D('RecommendUser')->query($sql);
        return get_group($list,'uid');
    }


    // 默认关注推荐用户添加
    public function putdefaultattention($uid,$sex,$id){

        $list = explode(',',$uid);
        $RecommendUser = D('RecommendUser')->field('id');
        $user = D('User');
        foreach ($list as $key => $val) {
            $r_user = $RecommendUser->where(" uid = $val and group_id = $id ")->find();
            if( $r_user['id'] =='' ){
                $u = $user->where('uid ='.$val)->find();
                $data[$key]['uid'] = $val;
                $data[$key]['sex'] = $sex;
                $data[$key]['group_id'] = $id;
            }
        }
        $RecommendUser = D('RecommendUser');
        $RecommendUser->addAll($data);
    }

    // 默认关注推荐用户列表
    public function defaultattention($id){

        $list = D('RecommendUser')->searchs('group_id ='.$id);
        if(!empty($list)){
            $usertag = D('UserTag');
            $tagsql = " SELECT t.id,t.title,count(uts.user_tag_id) as num,t.uid,s.name as class_name
                        FROM cj_user_tag AS t
                        LEFT JOIN cj_tag_class AS s ON t.tag_class_id = s.id
                        LEFT JOIN cj_user_tag_surging AS uts ON t.id  = uts.user_tag_id
                        WHERE t.uid =  ";
            foreach($list as $key => $val){
                $content = json_decode($val['content'],true);
                $list[$key]['checked'] = $content['user_tag_id'];
                $list[$key]['taglist'] = $usertag->query($tagsql.$val['uid'].' group by uts.user_tag_id');
            }
        }
        return $list;
    }

    // 热门分类用户列表
    public function hostsetclass($get){

        if(!empty($get)){
            $usertag = D('UserTag');
            $tag_class_id = $get['tag_class_id'];
            $join         = $get['join'];

            import("THINK.Page");
            if($join==''){ // 未加入
                $count = " SELECT count(uid) as num FROM cj_user_tag
                           WHERE uid IN (SELECT uid FROM cj_user_tag WHERE tag_class_id = $tag_class_id  GROUP  by uid)";
                $count   = D('User')->query($count);
                $Page = new \Think\Page($count[0]['num'],100);
                $show = $Page->show();
                $tagsql = " SELECT t.id,t.title,count(t.id) as num,t.uid,s.name as class_name,t.tag_class_id
                            FROM cj_user_tag AS t
                            LEFT JOIN cj_tag_class AS s ON t.tag_class_id = s.id
                            LEFT JOIN cj_user_tag_surging AS uts ON t.id  = uts.user_tag_id
                            WHERE tag_class_id = $tag_class_id
                            GROUP  by t.id
                            ORDER BY num DESC LIMIT ".$Page->firstRow.','.$Page->listRows;
                // 获取已加入的用户
                $joinsql =" SELECT u.uid
                            FROM cj_recommend_user             AS u
                            LEFT JOIN  cj_recommend_user_group AS g ON g.id = u.group_id
                            WHERE g.group_type = 1 AND g.tag_class_id = $tag_class_id
                            ";
                $joinlist = array_column($usertag->query($joinsql),'uid');
                $list     = $usertag->query($tagsql);
                if(!empty($joinlist)){
                    foreach($list as $k => $val){
                        if(in_array($val['uid'],$joinlist)){
                            $list[$k]['join'] = 'join';
                        }
                    }
                }
                $data['list']   = $list;
            }else{
                $count = "SELECT count(u.id) as num
                          FROM       cj_recommend_user       AS u
                          LEFT JOIN  cj_recommend_user_group AS g ON g.id = u.group_id
                          WHERE g.group_type = 1 AND g.tag_class_id = $tag_class_id
                        ";
                $count   = $usertag->query($count);
                $Page = new \Think\Page($count[0]['num'],100);
                $show = $Page->show();

                $sql = "SELECT u.id,u.uid,u.group_id,u.content,g.tag_class_id
                        FROM cj_recommend_user             AS u
                        LEFT JOIN  cj_recommend_user_group AS g ON g.id = u.group_id
                        WHERE g.group_type = 1 AND g.tag_class_id = $tag_class_id
                        LIMIT ".$Page->firstRow.','.$Page->listRows;
                $list = $usertag->query($sql);
                $taginfo = "SELECT  ut.id,ut.title,COUNT(ut.id) as num,s.name as class_name
                            FROM cj_user_tag              AS ut
                            LEFT JOIN cj_user_tag_surging AS uts ON ut.id = uts.user_tag_id
                            LEFT JOIN cj_tag_class        AS s   ON s.id = ut.tag_class_id
                            WHERE ut.id =  ";
                foreach($list as $k=>$val){
                    $content = json_decode($val['content'],true);
                    $gettag = $usertag->query($taginfo.$content[0]['user_tag_id']." GROUP BY uts.user_tag_id ");
                    $list[$k]['num'] = $gettag[0]['num'];
                    $list[$k]['class_name'] = $gettag[0]['class_name'];
                    $list[$k]['title'] = $gettag[0]['title'];
                }
                $data['list']   = $list;
            }
            $data['page']   = $show;
        }

        return $data;
    }

    // 查看banner
    public function getcontentbanner($id,$type){
        $model = D('BannerContent');
        if($type!=4 ){
            $sql  = "SELECT c.clicknums,c.user_clicknums,i.item,i.clicknums AS num
                     FROM   cj_banner               AS c
                     LEFT  JOIN cj_banner_content   AS i ON  c.id = i.banner_id
                     WHERE c.id = $id  
                    ";
            $list = $model->query($sql);
        }elseif($type==4){
            $sql  = "SELECT c.id,c.banner_id,s.thumb,c.item 
                     FROM cj_banner_content AS c 
                     LEFT JOIN cj_surging   AS s ON c.item = s.id 
                     WHERE c.banner_id = $id
                       ";
            $list = $model->query($sql);
        }
        //printr($list);
        return $list;
    }


    public function tagsurging($type){
        //载入分页类,核心类
        import("THINK.Page");
        if($type==0){
            $sql = "SELECT count(s.id) AS num
                    FROM      cj_surging          AS s
                    LEFT JOIN cj_user_tag_surging AS uts ON s.id = uts.surging_id
                    LEFT JOIN cj_user_tag         AS ut  ON uts.user_tag_id = ut.id
                    LEFT JOIN cj_tag_class        AS cl  ON ut.tag_class_id = cl.id
                    WHERE s.status = {$type}  ";
            $count = D('User')->query($sql);
            $Page = new \Think\Page($count[0]['num'], 30);
            $show = $Page->show();
            $sql = "SELECT DISTINCT s.id,s.thumb,s.create_time,ut.title,s.uid,cl.name as cl_name
                    FROM      cj_surging          AS s
                    LEFT JOIN cj_user_tag_surging AS uts ON s.id = uts.surging_id
                    LEFT JOIN cj_user_tag         AS ut  ON uts.user_tag_id = ut.id
                    LEFT JOIN cj_tag_class        AS cl  ON ut.tag_class_id = cl.id
                    WHERE s.status = {$type} ORDER BY s.id DESC LIMIT ".$Page->firstRow.','.$Page->listRows;
        }else{
            $sql = "SELECT count(DISTINCT s.id) AS num
                    FROM      chujian.cj_surging          AS s
                    LEFT JOIN chujian.cj_user_tag_surging AS uts ON uts.surging_id  = s.id
                    LEFT JOIN chujian.cj_user_tag         AS ut  ON uts.user_tag_id = ut.id
                    LEFT JOIN cj_admin_log.cj_surging_log AS l   ON l.surging       = s.id
                    WHERE l.status = {$type} AND l.confirm = 0 ";
            $count = D('User')->query($sql);
            $Page = new \Think\Page($count[0]['num'],100);
            $show = $Page->show();
            $sql = "SELECT DISTINCT s.id,s.thumb,s.create_time,ut.title,s.uid,l.certificate_time
                    FROM      chujian.cj_surging          AS s
                    LEFT JOIN chujian.cj_user_tag_surging AS uts ON uts.surging_id  = s.id
                    LEFT JOIN chujian.cj_user_tag         AS ut  ON uts.user_tag_id = ut.id
                    LEFT JOIN cj_admin_log.cj_surging_log AS l   ON l.surging       = s.id
                    WHERE l.status = {$type} AND l.confirm = 0  ORDER BY s.id DESC LIMIT ".$Page->firstRow.','.$Page->listRows;
        }
        return array(
            'list'=>D('User')->query($sql),
            'show'=>$show,
        );

    }


    // 根据动态获取所有标签信息
    public function gatalltagsurging($id,$title=''){
        $sql = "SELECT s.id,s.thumb,s.create_time,ut.title,s.uid,uts.user_tag_id,uts.user_tag_id AS u_tag,s.description
                FROM      cj_surging          AS s
                LEFT JOIN cj_user_tag_surging AS uts ON s.id = uts.surging_id
                LEFT JOIN cj_user_tag         AS ut  ON uts.user_tag_id = ut.id
                WHERE s.id = $id ";
        $info = D('User')->query($sql);
        return $info[0];
    }

    // 根据标签动态获取用户，并清理用户缓存
    public function cleartaginfo($list){
        $usertaglist = D('UserTag')->field('id')->where(" id IN($list) ")->group('id')->select();
        if(!empty($usertaglist)){
            $phpServer = D('PhpServerRedis');
            foreach($usertaglist as $key=>$val){
                $phpServer->delete_user_tag($val['id']);
            }
        }
    }


    // 后台群发消息
    public function adminsend($post)
    {
        $data['index']      = $post['index'];
        $data['accept']     = $post['accept'];
        $data['content']    = $post['content'];
        $data['createtime'] = time();
        if($post['accept_uid']){
            $data['accept_uid'] = trim($post['accept_uid']);
        }
        $id = D('RedPush')->add($data);
        if($id){
            $Message = A('Message');
            if($post['accept']==1)
            {
                $modelmsg = D('TblSystemMsg');
                $sql  = "SELECT max(userid) AS num FROM cj_system_msg.cj_sys_msg_all_users LIMIT 0,1 ";
                $have = $modelmsg->query($sql);
                if($have[0]['num'] == ''){
                    $userar = D('UserBase')->field('uid as userid')->where('','uid')->select();
                    //$addyes = D('SysMsgAllUsers')->addAll($userar);
                    $insertval = $this->createinsertsqled($userar);
                    $addsql = "INSERT INTO cj_system_msg.cj_sys_msg_all_users (`userid`) VALUES ".$insertval;
                    $addyes = $modelmsg->execute($addsql);
                    if($addyes!=false){
                        $ret    = $modelmsg->insertMsgAlluser(trim($post['content']));
                    }
                }else{
                    $userar = D('UserBase')->field('uid as userid')->where('uid > '.$have[0]['num'])->select();
                    //$addyes = D('SysMsgAllUsers')->addAll($userar);
                    $insertval = $this->createinsertsqled($userar);
                    if($insertval != '' ){
                        $addsql = "INSERT INTO cj_system_msg.cj_sys_msg_all_users (`userid`) VALUES ".$insertval;
                        $addyes = $modelmsg->execute($addsql);   
                    }
                    if($addyes!=false || empty($userar)){
                        $ret      = $modelmsg->insertMsgAlluser(trim($post['content']));
                    } 
                    
                }
            }
            elseif($post['accept']==2)
            {
                // 获取最近两天没有登录的用户id
                $time  = time()-(3600*24*2);
                $sql = "SELECT uid FROM chujiandw.cj_location_base WHERE update_time <= $time ";
                $userar = array_column(D('UserBase')->query($sql),'uid');
                $ret = $Message->systemMessage($userar,trim($post['content']));
            }
            // 每天群发消息
            elseif($post['accept']==4)
            {
                $redis_key = 'php_sysmsg_everyday';
                $rds = D('PhpServerRedis')->new_redis();
                $now = time();
                $stime = strtotime(date('Y-m-d 14:00:01',$now - 60 * 60 * 24 * 1));
                $etime = strtotime(date('Y-m-d 14:00:00'));
                $ltime = $rds->hGet($redis_key,'ltime');
                if($etime >= $now) $this->error('请在每日14点以后执行该操作');
                if($ltime && date('Ymd',$ltime) == date('Ymd')) $this->error('该操作每天只能执行一次');
                $map = array('reg_time' => array(array('egt',$stime),array('elt',$etime)));
                $arr = D('UserBase')->field('uid,reg_time')->where($map)->select() ?: array();
                $userar = array_column($arr,'uid') ?: [];
                $userar[] = 12200022;//测试接收
                $rds->hSet($redis_key,'ltime',$now);
                $ret = $Message->systemMessage($userar,trim($post['content']));
                //var_dump([$ltime,$arr]);die;
            }
            // 指定UID发送
            else
            {
                if(preg_match_all('/\b(\d{4,11})\b/',$post['accept_uid'],$arr)) $userar = $arr[1];
                else $userar = explode(',',$post['accept_uid']);
                $userar = array_unique($userar ?: []);
                $ret = $Message->systemMessage($userar,trim($post['content']));
            }
            D('OperLog')->log('msg_send_bat',
            [
              '人数' => (string)count($userar ?: []),
              '话术' => $post['content'],
            ]);
        }
        else
        {
          $this->error('添加失败');
        }
        return $ret;
    }

    public function createinsertsqled($array){
        if(is_array($array)){
            foreach ($array as $key => $value) {
                 $str .= '('.$value['userid'].'),';
            }
            $str = trim($str,',');
            return $str;
        }
    }


    // 金币分布统计
    public function luotgold(){
        $user  = D('User');
        $total = $user->field(' max(gold_coin_cnt) as total ')->where('status = 0 and server_version = 1 and gold_coin_cnt >0  ')->select();
        $num   = ceil($total[0]['total']/10);
        $alluser = $total[0]['total'];

        for($i=1;$i<=10;$i++){
            $start = (($num*$i)-$num);
            $end   = ($num*$i);
            $intavls .= $start.',';
            $to      .= "'".$start."to".($end-1)."',";
        }
        $intavls = trim($intavls,',');
        $to      = trim($to,',');
        $sql = "select elt(interval(d.gold_coin_cnt,$intavls),$to) as yb_level,count(d.gold_coin_cnt) as cnt
                from cj_user d
                where d.status = 0 and  d.gold_coin_cnt > 0  group by elt(interval(d.gold_coin_cnt,$intavls),$to)";
        $info = D('User')->query($sql);
        foreach($info as $k => $val ){
            $sone  = explode('to',$val['yb_level']);
            $data[$sone[0]]= "['".str_replace('to','~',$val['yb_level'])."',".($val['cnt']/$alluser)."]";
        }
        ksort($data);
        return implode($data,',');
    }

    /**
     * 获取时间区间
     * 昨日15点~3点，3~15点，15~明日3点
     * */
    private function getTimeZone($time) {
        $date = date('Ymd',time());
        $zeroClock = intval(strtotime($date));
        $trigTime1 = $zeroClock + 3*3600;
        $trigTime2 = $zeroClock + 15*3600;

        if($time < $trigTime1) return 1;
        elseif($time >= $trigTime1 && $time < $trigTime2) return 2;
        else return 3;
    }

    /**
     * 判断当前是否需要切换时间区间
     * 如果需要则今日点赞、点损需要清空；
     * type 1为赞，0为损
     * */
    private function processActiveInfo($info) {
        $lastThumbTime   = intval($info['thumb_up_time']);
        $now             = time();

        $lastZone = $this->getTimeZone($lastThumbTime);
        $nowZone  = $this->getTimeZone($now);
        if($lastZone != $nowZone) {
            $info['t_thumb_up_cnt']   = 0;
            //$info['t_thumb_down_cnt'] = 0;
        }

        return $info;
    }

    public function selecthot($uid=''){

        if($uid!=''){
            $and = '  and s.uid =  '.$uid;
        }

        $sql = "SELECT count(DISTINCT ut.id ) num
                FROM  (SELECT id,thumb,create_time,uid FROM cj_surging WHERE unix_timestamp(from_unixtime(create_time,'%Y-%m-%d %H:%i:%m')) >=  unix_timestamp(date_sub(now(), interval '24:00:00' day_second)) ORDER BY create_time DESC ) AS s
                LEFT JOIN cj_user_tag_surging AS uts ON uts.surging_id  = s.id
                LEFT JOIN cj_user_tag         AS ut  ON uts.user_tag_id = ut.id
                LEFT JOIN cj_user             AS u   ON ut.uid  = u.uid
                WHERE 1 $and  ";
        $count = D('User')->query($sql);

        //载入分页类,核心类
        import("THINK.Page");
        $Page = new \Think\Page($count[0]['num'],50);
        $show = $Page->show();

        $sql = "SELECT s.id AS did,s.thumb,s.create_time,ut.id,ut.title,ut.uid,ut.thumb_up_time,ut.t_thumb_up_cnt,ut.t_thumb_down_cnt,u.status
                FROM  (SELECT id,thumb,create_time,uid FROM cj_surging WHERE unix_timestamp(from_unixtime(create_time,'%Y-%m-%d %H:%i:%m')) >=  unix_timestamp(date_sub(now(), interval '24:00:00' day_second)) ORDER BY create_time DESC ) AS s
                LEFT JOIN cj_user_tag_surging AS uts ON uts.surging_id  = s.id
                LEFT JOIN cj_user_tag         AS ut  ON uts.user_tag_id = ut.id
                LEFT JOIN cj_user             AS u   ON ut.uid  = u.uid
                WHERE 1 $and group by ut.id having ut.id > 0 ORDER BY s.create_time DESC  LIMIT ".$Page->firstRow.','.$Page->listRows;
        $list = D('User')->query($sql);

        if(!empty($list)){
            $usertagsuting = D('UserTagSurging');
            foreach($list as $key=>$value){
                $list[$key]['snum'] = $usertagsuting->where('user_tag_id = '.$value['id'])->count();
                $tThumbUpCnt = $value['t_thumb_up_cnt'];
                $thumbUpTime = $value['thumb_up_time'];
                $clearInfo   = $this->processActiveInfo(['t_thumb_up_cnt'=>$tThumbUpCnt,'thumb_up_time'=>$thumbUpTime]);
                $list[$key]['t_thumb_up_cnt'] = $clearInfo['t_thumb_up_cnt'];
            }
        }

        $data['list']  = $list;
        $data['page']  = $show;
        return $data;
    }

    public function changeusertag(){
        $select = D('TagClass')->searchs('id >49999');
        foreach($select as $val){
            $op .= '<option value="'.$val['id'].'">'.$val['name'].'</option>';
        }
        $data['opt']  = $op;
        $data['list'] = get_group(D('UserTag')->searchs('tag_class_id = 0 '),'uid');
        return $data;
    }

    public function tagclass(){
        return D('TagClass')->searchs('id >49999');
    }

    /*
     * @$tagid 标签id
     * @$oid   被点赞用户
     * @$num   要点赞次数
     * */
    public  function  sendup($tagid,$oid,$num=''){

        $tag_name = D('UserTag')->search('id ='.$tagid,'title');

        $uidlist  = implode(',',array_column(D('ThumbUp')->searchs(" item = $tagid and oid = $oid  ",'uid'),'uid'));
        if($uidlist!=''){
            $and = "AND uid NOT IN($uidlist".'10001,10002'.") ";
        }

        $sql  = "SELECT uid,nickname FROM cj_user WHERE status = 3 and nickname !='' $and ORDER BY RAND() LIMIT 0,$num ";
        $puid = D('User')->query($sql);

        if(!empty($puid)){
            if(API_TAG=='api.chujian.im'){
                $option = C('php_server_user_info_v2');
            }else{
                $option = C('php_server_redis_config');
            }
            $redis = new Redis($option);
            $location = D('Location');
            foreach($puid as $k =>$val ){
                $location->where('uid ='.$val['uid'])->save(array('update_time'=>time())); // 更新登录时间
                $newpuid['user_tag_id'] = $tagid;
                $newpuid['uid']         = $val['uid'];
                $newpuid['name']        = $val['nickname'];
                $newpuid['oid']         = $oid;
                $newpuid['title']       = $tag_name['title'];
                $time                   = time()+intval($k)+intval(rand(1,3580));
                $bzarray['type']     = 1;
                $bzarray['resource'] = $newpuid;
                $red = $redis->zAdd('auto_thumb_up',$time,json_encode($bzarray));
            }
            $priase['aid']          = $_SESSION['authId'];
            $priase['tag']          = $tagid;
            $priase['p_num']        = $num;         // 要点赞次数
            $priase['actual']       = count($puid); // 实际点赞次数
            $priase['praise_time']  = time();
            D('PraiseLog')->add($priase);
        }else{
            return 'no';
        }
    }

    public  function  nowhot(){
        if(API_TAG=='api.chujian.im'){
            $option = C('php_server_user_info_v2');
        }else{
            $option = C('php_server_redis_config');
        }

        $p = ($_GET['p']<=0)?1:$_GET['p'];

        $redis = new Redis($option);
        $host  = $redis->hGet('hot_info',$p);
        $content = $redis->hGetall('hot_info');

        //载入分页类,核心类
        import("THINK.Page");
        $Page = new \Think\Page(count($content), 1);
        $show = $Page->show();

        $list = unserialize($host);
        if (!empty($list)){
            $tag = D('UserTag');
            foreach ($list as $k => $val) {
                $info = $tag->search('id = ' . $val['user_tag_id'], 'title');
                $list[$k]['title'] = $info['title'];
                $list[$k]['index'] = $p;
            }
        }

        $datas['list'] = $list;
        $datas['page'] = $show;

        return $datas;
    }


    public function delhost($user_tag_id){

        if(API_TAG=='api.chujian.im'){
            $option = C('php_server_user_info_v2');
        }else{
            $option = C('php_server_redis_config');
        }
        $redis = new Redis($option);

        $expl  = explode(':',$user_tag_id);
        $user_tag_id = $expl[0];
        $host  = $redis->hGet('hot_info',$expl[1]);
        $list  = unserialize($host);
        if(!empty($list)){
            foreach($list as $k => $val){
                if($val['user_tag_id'] == $user_tag_id ){
                    unset($list[$k]);
                }
            }
        }
        $host  = $redis->hSet('hot_info',$expl[1], serialize($list));
    }


    public function classtagselecthtml($selected=''){
        $select = D('TagClass')->searchs();
        foreach($select as $val){
            if($val['id']==$selected){
                $op .= '<option selected value="'.$val['id'].'">'.$val['name'].'</option>';
            }else{
                $op .= '<option  value="'.$val['id'].'">'.$val['name'].'</option>';
            }

        }
        return $op;
    }

    /*
     * 用户标签数量修改
     * uid
     * type add 添加 dec 减
     * tag_created_cnt 创建过的标签个数
     * tag_used_cnt    正在使用的标签个数
     * */
    public function user_tag_info_ch($uid,$type){
        $model = D('UserInfo');
        $info = $model->search('uid = '.$uid);

        if( $info['id'] == '' ){
            $data['uid'] = $uid;
            $data['tag_created_cnt'] = 1;
        }else{
            $data['id']  = $info['id'];
            $data['uid'] = $info['uid'];
        }

        if($type=='add'){
            $data['tag_created_cnt'] = $info['tag_created_cnt']+1;
        }else{
            $dec = ($info['tag_created_cnt']<=0)?1:$info['tag_created_cnt'];
            $data['tag_created_cnt'] = $dec;
        }

        $data['tag_used_cnt']    = $this->usertagtotal($uid);

        if( $info['id'] != '' ){
            $sql = "UPDATE cj_user_info SET  tag_created_cnt = {$data['tag_created_cnt']},tag_used_cnt={$data['tag_used_cnt']} where uid = $uid ";
        }else{
            $sql = "INSERT INTO cj_user_info(uid,tag_created_cnt,tag_used_cnt)  VALUES({$data['uid']},{$data['tag_created_cnt']},{$data['tag_used_cnt']}) ";
        }
        $add = D('User')->execute($sql);
        if($add==false){
            $this->error('用户信息添加错误！');
        }
    }

    // 获取用户动态总数量
    public function usertagtotal($uid){
        $info = D('UserTag')->field(' count(uid) as num ')->where('uid = '.$uid)->find();
        if($info['num']==''){
            return 0;
        }else{
            return $info['num'];
        }
    }

    // 手动点赞记录
    public function praiselist($tag){
        $sql = "SELECT tl.*,a.nickname
                FROM cj_admin_log.cj_praise_log AS tl
                LEFT JOIN cj_admin.cj_admin     AS  a ON tl.aid = a.aid
                WHERE tl.tag = $tag
                  ";
        return D('User')->query($sql);
    }

    // 查看邀请证码注册的用户列表
    public function regcodelist($code){

        $c_sql = "SELECT count(*) num
                FROM cj_invitation        AS ic
                LEFT JOIN cj_user         AS u  ON ic.oid  = u.uid
                LEFT JOIN cj_user_tag     AS t  ON t.uid   = u.uid
                WHERE ic.code = '{$code}' ";
        $count = D("User")->query($c_sql);

        //载入分页类,核心类
        import("THINK.Page");
        $Page = new \Think\Page($count[0]['num'],200);
        $show = $Page->show();
        $sql = "SELECT u.uid,ic.code,u.nickname,u.phone,u.reg_time,t.title
                FROM cj_invitation        AS ic
                LEFT JOIN cj_user         AS u  ON ic.oid  = u.uid
                LEFT JOIN cj_user_tag     AS t  ON t.uid   = u.uid
                WHERE ic.code = '{$code}' GROUP BY u.uid  LIMIT ".$Page->firstRow.','.$Page->listRows;
        $list = D('User')->query($sql);
        if(!empty($list)){
            $usertag = D('UserTag');
            $surging = D('Surging');
            foreach($list as  $key => $val){
                $list[$key]['tag_num'] = $usertag->field('id')->where('uid ='.$val['uid'])->count();
                $list[$key]['sur_num'] = $surging->field('id')->where('uid ='.$val['uid'])->count();
                $list[$key]['tag_des'] = $surging->field('id')->where("description !='' and uid = {$val['uid']} ")->count();
            }
        }
        return array('list'=>$list,'page'=>$show);

    }

    /*  $data  参数
     *  index  展示位置 1 s首页  2 最新  3热门  4兑礼  5 消息列表
     *  accept 接受用户 1 全部用户   3 指定用户
     *  accept_uid 指定接受消息用户的 uid
     *  content    消息内容
     *  send_time  1 现在发送   3 定时发送
     *
     * */
    public function redpush($data){
        $send_start = strtotime($data['send_start']);
        $time_type  = $data['time_type'];

        if($send_start>time() && $time_type == 3 ){
            $send['policy'] = $data['send_start'];
        }

        $index      = $data['index'];
        $accept     = $data['accept'];
        switch($index){
            case 1:
                $time = time();
                $sql  = "select id,`type`,title from cj_banner where valid_begin< $time  and valid_end> $time  and page=0 order by `index` LIMIT 1";
                $info = D("Banner")->query($sql);
                $item = $info[0]['id']; // banner_id
                $send['bannertitle'] = $info[0]['title'];
                if($info[0]['type']==1){
                    $send['what'] = 1;   // 用户集合
                }
                if($info[0]['type']==5){
                    $send['what']   = 5; // web页面
                    $web = D('BannerContent')->search('banner_id ='.$item,'item');
                    $send['infoc']  = $web['item'];
                }

                break;
            case 2:
                $item = ''; //
                break;
            case 3:
                $item = ''; // 今日
                break;
            case 4:
                $time = time();
                $sql  = "select id,description,image from cj_activity where valid_begin< $time  and valid_end> $time  order by id DESC LIMIT 1";
                $info = D("Activity")->query($sql);
                $item = $info[0]['id'];
                $send['activity_desc']  = $info[0]['description'];
                $send['activity_pic']   = 'http://static.chujian.im/'.$info[0]['image'];
                break;
            default:
                $item = '';
        }

        if(API_TAG=='api.chujian.im'){
            $option = C('user_token');
        }else{
            $option = C('php_server_redis_config');
        }
        $redis = new Redis($option);
        $uid   = explode(',',$data['accept_uid']); // 接收用户uid
        foreach($uid as $key => $value){
            $usertoken = explode(':',$redis->hGet($value,'device_token'));
            if(!empty($usertoken[0])){
                if(strtolower($usertoken[2]) == 'ios' ){
                    $ios      .= $usertoken[0].',';
                }else{
                    $android  .= $usertoken[0].',';
                }
            }
        }

        $ios     = trim($ios,',');
        $android = trim($android,',');

        $send['device_token'] = trim($send['device_token'],',');
        $send['json']         = array('type'=>intval($index),'item'=>intval($item));
        $send['content']      = trim($data['content']);    // 消息内容

        if($accept==1){
            echo A('Pushmsg','Event')->sendIOSBroadcast($send);
            echo A('Pushmsg','Event')->sendAndroidBroadcast($send);
        }else{
            if($ios!=''){
                $send['device_token'] = $ios;
                echo A('Pushmsg','Event')->sendIOSUnicast($send);
            }
            if($android!=''){
                $send['device_token'] = $android;
                echo $sendandroid = A('Pushmsg','Event')->sendAndroidUnicast($send);
            }


        }
        echo '<a href="/index.php/search/redpush">点击返回</a>';

    }

    // 列出用户标签及其对应的图片数量
    public function tagsurgingnum($uid){
        $sql = "SELECT ut.title,count(uts.user_tag_id) as num
                FROM cj_user_tag              AS ut
                LEFT JOIN cj_user_tag_surging AS uts ON ut.id = uts.user_tag_id
                LEFT JOIN cj_surging          AS s   ON s.id  = uts.surging_id
                WHERE ut.uid IN($uid) GROUP BY uts.user_tag_id
                ";
        $list = D('User')->query($sql);
        if(!empty($list)){
            $tagnum = '';
            foreach($list as $key => $val){
                $tagnum .= $val['title'].':'.$val['num'].' | ';
            }
            return trim($tagnum,' | ');
        }
    }

    // 给默认推荐标签补全content
    public function addrecommendtag($post){
        $id  = intval($post['id']);
        $tag = intval($post['tag']);
        $uid = intval($post['uid']);
        $userinfo = D("User")->search('uid = '.$uid,'nickname');
        $sql = "SELECT t.id,t.title,u.id as surging_id,u.thumb
                FROM cj_user_tag_surging as uts
                LEFT JOIN cj_surging     as u   ON uts.surging_id  = u.id
                LEFT JOIN cj_user_tag    as t   ON uts.user_tag_id = t.id
                WHERE uts.user_tag_id = $tag and u.status = 1 LIMIT 0,8 ";
        $list = D('User')->query($sql);
        foreach($list as $k=>$value){
            $newar[$k]['surging_id'] = $value['surging_id'];
            $newar[$k]['thumb']      = 'http://surging.chujian.im/'.$value['thumb'];
        }
        if($uid!='' && $userinfo['nickname']!=''){
            $allarrs['uid']          = $uid;
            $allarrs['nickname']     = $userinfo['nickname'];
            $allarrs['user_tag_id']  = $list[0]['id'];
            $allarrs['title']        = $list[0]['title'];
            $allarrs['surging']      = $newar;
            $save = D('RecommendUser')->where('id='.$id)->save(array('content'=>json_encode($allarrs)));
            A('Recommend','Event')->addredisrecommend();// 更新redis
            if($save!==false){
                return 'ok';
            }else{
                return 'no';
            }
        }else{
            return 'error';
        }




    }






}