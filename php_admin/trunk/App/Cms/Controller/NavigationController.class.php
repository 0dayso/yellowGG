<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/12/30
 * Time: 14:38
 */

namespace Cms\Controller;

use Think\Controller;
class NavigationController extends Controller{

    /*
     * 设置页面导航
     * */
    public function getNavPath() {
        //获取控制器名称
        $controller = CONTROLLER_NAME;
        $controller = $this->process_url_name($controller);
        $action     = ACTION_NAME;
        $param      = $_GET;

        if((isset($param['menu']))&&(isset($param['type']))){
            $menu = $param['menu'];
            $type = $param['type'];
            switch($menu){
                case 'task_hall':
                    $ret[] = array('title'=>'任务大厅','url'=>U('TaskHall/index'));
                    break;
                case 'admin_task':
                    $ret[] = array('title'=>'我的任务','url'=>U('AdminTask/index'));
                    break;
                case 'content_manage':
                    $ret[] = array('title'=>'内容管理','url'=>U('ContentManage/index'));
                    break;
                case 'account_manage':
                    $ret[] = array('title'=>'帐号管理','url'=>U('AccountManage/index'));
                    switch($action){
                        case 'user_list':
                            $ret[] = array('title'=>'用户列表','url'=>U($controller.'/'.$action,array('menu'=>$menu,'type'=>$type)));
                            break;
                        case 'test_user_list':
                            $ret[] = array('title'=>'测试帐号列表','url'=>U($controller.'/'.$action,array('menu'=>$menu,'type'=>$type)));
                            break;
                        case 'push_user_list':
                            $ret[] = array('title'=>'地推帐号列表','url'=>U($controller.'/'.$action,array('menu'=>$menu,'type'=>$type)));
                            break;
                        case 'trend':
                            $ret[] = array('title'=>'账号注册趋势','url'=>U($controller.'/'.$action,array('menu'=>$menu,'type'=>$type)));
                            break;
                        default:
                            break;
                    }
                    break;
                default:
                    break;
            }
            switch($controller){
                case 'CertificateCar':
                    $ret[] = array('title'=>'车辆认证','url'=>U($controller.'/index',array('menu'=>$menu,'type'=>$type)));
                    break;
                case 'CertificateVideo':
                    $ret[] = array('title'=>'视频认证','url'=>U($controller.'/index',array('menu'=>$menu,'type'=>$type)));
                    break;
                case 'Accusation':
                    $ret[] = array('title'=>'举报业务','url'=>U($controller.'/index',array('menu'=>$menu,'type'=>$type)));
                    break;
                case 'HeadImage':
                    $ret[] = array('title'=>'图片审核','url'=>U($controller.'/index',array('menu'=>$menu,'type'=>$type)));
                    break;
                case 'UserFactory':
                    if($type != 'user_list')
                        $ret[] = array('title'=>'文字审查','url'=>U($controller.'/'.$action,array('menu'=>$menu,'type'=>$type)));
                    break;
                /*case 'User':
                    if($type != 'user_list')
                        $ret[] = array('title'=>'文字审查','url'=>U($controller.'/'.$action,array('menu'=>$menu,'type'=>$type)));
                    break;*/
                default:
                    break;
            }
            switch($type){
                case 'unallocated':
                    $ret[] = array('title'=>'待分配','url'=>U($controller.'/index',array('menu'=>$menu,'type'=>$type)));
                    break;
                case 'allocated':
                    $ret[] = array('title'=>'已分配','url'=>U($controller.'/index',array('menu'=>$menu,'type'=>$type)));
                    break;
                case 'processed':
                    $ret[] = array('title'=>'已处理','url'=>U($controller.'/index',array('menu'=>$menu,'type'=>$type)));
                    break;
                case 'unprocessed':
                    $ret[] = array('title'=>'待审核','url'=>U($controller.'/index',array('menu'=>$menu,'type'=>$type)));
                    break;
                case 'all_user':
                    $ret[] = array('title'=>'用户列表');
                    break;
                case 'push_user':
                    $ret[] = array('title'=>'地推帐号列表');
                    break;
                case 'test_user':
                    $ret[] = array('title'=>'测试帐号列表');
                    break;
                default:
                    break;
            }
        }//结束有menu参数和type参数的
        else{
            switch($controller){
                case 'AdminTask':
                    $ret[] = array('title'=>'我的任务','url'=>U($controller.'/index'));
                    break;
                case 'AdminInfo':
                    $ret[] = array('title'=>'个人管理','url'=>U($controller.'/index'));
                    $ret[] = array('title'=>'我的信息','url'=>U($controller.'/index'));
                    break;
                case 'TaskHall':
                    $ret[] = array('title'=>'任务大厅','url'=>U($controller.'/index'));
                    break;
                case 'ContentManage':
                    $ret[] = array('title'=>'内容管理','url'=>U($controller.'/index'));
                    break;
                case 'AccountManage':
                    $ret[] = array('title'=>'帐号管理','url'=>U($controller.'/index'));
                    break;
                case 'Test':
                    $ret[] = array('title'=>'测试','url'=>U($controller.'/index'));
                    break;
                case 'OperateToo':
                    $ret[] = array('title'=>'运营工具','url'=>U($controller.'/index'));
                    break;
                case 'RobotUpload':
                    $ret[] = array('title'=>'后台管理','url'=>U($controller.'/index'));
                    $ret[] = array('title'=>'机器人账号','url'=>U($controller.'/index'));
                    $ret[] = array('title'=>'图片上传','url'=>U($controller.'/index'));
                    break;
                case 'SystemSetting':
                    $ret[] = array('title'=>'后台管理','url'=>U($controller.'/index'));
                    break;
                case 'HeadimgLog':
                    $ret[] = array('title'=>'图像管理','url'=>U($controller.'/index'));
                    break;
                case 'FaceCarRate':
                    $ret[] = array('title'=>'打分系统','url'=>U($controller.'/index'));
                    break;
                case 'PrizeManage':
                    $ret[] = array('title'=>'运营工具','url'=>U($controller.'/index'));
                    $ret[] = array('title'=>'奖品管理','url'=>U($controller.'/index'));
                    break;
                case 'Closure':
                    $ret[] = array('title'=>'禁封账号列表','url'=>U($controller.'/index'));
                    break;
                case 'OperateToo':
                    $ret[] = array('title'=>'运营工具','url'=>U($controller.'/index'));
                    break;
                case 'User':
                    $ret[] = array('title'=>'内容管理','url'=>U($controller.'/index'));
                    break;
                case 'Search':
                    $ret[] = array('title'=>'认证工具');
                    $ret[] = array('title'=>'搜索功能');
                    break;
                case 'CostView':
                    $ret[] = array('title'=>'后台管理');
                    $ret[] = array('title'=>'第三方接口');
                case 'Recommend':
                    $ret[] = array('title'=>'认证工具');
                    break;
                case 'Banner':
                    $ret[] = array('title'=>'认证工具');
                    break;
                default:
                    break;
            }
            switch($action){
                case 'car_brand':
                    $ret[] = array('title'=>'汽车管理','url'=>U($controller.'/car_brand'));
                    $ret[] = array('title'=>'汽车品牌');
                    break;
                case 'car_model':
                    $ret[] = array('title'=>'汽车管理','url'=>U($controller.'/car_brand'));
                    $ret[] = array('title'=>'汽车车型');
                    break;
                case 'add_car_model':
                    $ret[] = array('title'=>'汽车管理','url'=>U($controller.'/car_brand'));
                    $ret[] = array('title'=>'添加车型');
                    break;
                case 'user_verify_code':
                    $ret[] = array('title'=>'验证码管理','url'=>U($controller.'/'.$action));
                    break;
                case 'admin':
                    $ret[] = array('title'=>'管理员管理','url'=>U($controller.'/'.$action));
                    break;
                case 'group':
                    $ret[] = array('title'=>'管理员管理','url'=>U($controller.'/'.$action));
                    break;
                case 'view_cost':
                    $ret[] = array('title'=>'短信平台费用查看','url'=>U($controller.'/'.$action));
                    break;
                case 'sent_message':
                    $ret[] = array('title'=>'发送短信','url'=>U($controller.'/'.$action));
                    break;
                case 'car':
                    $ret[] = array('title'=>'卡牌日志','url'=>U($controller.'/'.$action));
                    break;
                case 'pxchange':
                    $ret[] = array('title'=>'兑奖详情','url'=>U($controller.'/'.$action));
                    break;
                case 'prizerecord':
                    $ret[] = array('title'=>'中奖记录','url'=>U($controller.'/'.$action));
                    break;
                case 'create_admin':
                    $ret[] = array('title'=>'员管理','url'=>U($controller.'/'.$action));
                    $ret[] = array('title'=>'添加管理员','url'=>U($controller.'/'.$action));
                    break;
                case 'index':
                    if($controller == 'PrizeManage')
                        $ret[] = array('title'=>'中奖记录','url'=>U($controller.'/'.$action));
                    break;
                case 'check_user_image':
                    $ret[] = array('title'=>'用户信息查看','url'=>U($controller.'/'.$action));
                    $ret[] = array('title'=>'每日用户上传图片数量','url'=>U($controller.'/'.$action));
                    break;
                case 'workcount':
                    $ret[] = array('title'=>'工作管理');
                    $ret[] = array('title'=>'工作量统计','url'=>U($controller.'/'.$action));
                    break;
                case 'operation':
                    $ret[] = array('title'=>'工作管理');
                    $ret[] = array('title'=>'操作日志','url'=>U($controller.'/'.$action));
                    break;
                case 'set_sensitive':
                    $ret[] = array('title'=>'敏感词库管理');
                    $ret[] = array('title'=>'设置敏感词库');
                    break;
                case 'putinfo':
                    $ret[] = array('title'=>'首页推荐用户');
                    $ret[] = array('title'=>'推荐列表');
                    break;
                case 'putinfomodi':
                    $ret[] = array('title'=>'首页推荐用户');
                    $ret[] = array('title'=>'编辑推荐用户');
                    break;
                case 'putinfoadd':
                    $ret[] = array('title'=>'首页推荐用户');
                    $ret[] = array('title'=>'添加推荐');
                    break;
                case 'pushmessage':
                    $ret[] = array('title'=>'推送消息');
                    $ret[] = array('title'=>'发送短信');
                    break;
                case 'setbanneradd':
                    $ret[] = array('title'=>'首页banner');
                    $ret[] = array('title'=>'新增/编辑banner');
                    break;
                case 'setbanner':
                    $ret[] = array('title'=>'首页banner');
                    $ret[] = array('title'=>'历史banner');
                    break;
                case 'editbanner':
                    $ret[] = array('title'=>'首页banner');
                    $ret[] = array('title'=>'banner编辑');
                    break;
                case 'showbanner':
                    $ret[] = array('title'=>'首页banner');
                    $ret[] = array('title'=>'banner查看');
                    break;
                case 'pullconten':
                    $ret[] = array('title'=>'用户答疑','url'=>U($controller.'/'.$action));
                    $ret[] = array('title'=>'聊天信息','url'=>U($controller.'/'.$action));
                    break;
                case 'chatlogmonitor':
                    $ret[] = array('title'=>'聊天记录监控','url'=>U($controller.'/'.$action));
                    break;
                case 'stag':
                    $ret[] = array('title'=>'标签搜索');
                    break;
                case 'suser':
                    $ret[] = array('title'=>'用户搜索');
                    break;
                case 'gold':
                    $ret[] = array('title'=>'金币搜索');
                    break;
                case 'goldinfo':
                    $ret[] = array('title'=>'金币详情');
                    break;
                case 'library':
                    $ret ='';
                    $ret[] = array('title'=>'运营工具');
                    $ret[] = array('title'=>'活动用户库');
                    $ret[] = array('title'=>'活动列表');
                    break;
                case 'editlibrary':
                    $ret ='';
                    $ret[] = array('title'=>'运营工具');
                    $ret[] = array('title'=>'活动用户库');
                    $ret[] = array('title'=>'编辑活动');
                    break;
                case 'addlibrary':
                    $ret ='';
                    $ret[] = array('title'=>'运营工具');
                    $ret[] = array('title'=>'活动用户库');
                    $ret[] = array('title'=>'增加活动库');
                    break;
                case 'prizelist':
                    $ret ='';
                    $ret[] = array('title'=>'认证工具');
                    $ret[] = array('title'=>'认证');
                    $ret[] = array('title'=>'奖品列表');
                    break;
                case 'hostlist':
                    $ret ='';
                    $ret[] = array('title'=>'认证工具');
                    $ret[] = array('title'=>'认证');
                    $ret[] = array('title'=>'活动列表');
                    break;
                case 'achostset':
                    $ret ='';
                    $ret[] = array('title'=>'认证工具');
                    $ret[] = array('title'=>'认证');
                    $ret[] = array('title'=>'活动后台');
                    break;
                case 'addacprize':
                    $ret ='';
                    $ret[] = array('title'=>'认证工具');
                    $ret[] = array('title'=>'认证');
                    $ret[] = array('title'=>'添加奖品');
                    break;
                case 'editprize':
                    $ret ='';
                    $ret[] = array('title'=>'认证工具');
                    $ret[] = array('title'=>'认证');
                    $ret[] = array('title'=>'编辑奖品');
                    break;
                case 'host':
                    $ret ='';
                    $ret[] = array('title'=>'认证工具');
                    $ret[] = array('title'=>'认证');
                    $ret[] = array('title'=>'添加活动');
                    break;
                case 'deithost':
                    $ret ='';
                    $ret[] = array('title'=>'认证工具');
                    $ret[] = array('title'=>'认证');
                    $ret[] = array('title'=>'编辑活动');
                    break;
                default:
                    break;
            }
        }

        return $ret;
    }

    /*
     * 删除控制器中_，驼峰：首字母大写
     * */
    protected function process_url_name($name)
    {
        $str = '';
        $arr = explode('_',$name);
        foreach($arr as $value){
            $str .= ucwords($value);
        }

        return $str;
    }
}
