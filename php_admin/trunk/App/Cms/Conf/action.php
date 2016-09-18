<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/12/15
 * Time: 20:38
 * 管理员操作及权限设置
 */
return array(
    'ACTION_CERTIFICATE_VIDEO'         => 1,//视频认证
    'ACTION_CERTIFICATE_CAR'           => 2,//普通管理员车辆认证
    'ACTION_DELETE_VIDEO_LOG'          => 3,//删除视频认证记录
    'ACTION_DELETE_CAR_LOG'            => 4,//删除车辆认证记录
    'ACTION_ADD_USER'                  => 5,//添加用户

    //TODO 需要添加的操作权限
    'ACTION_DELETE_HEAD_IMG'               => 6,//删除用户头像

    'ACTION_CERTIFICATE_CAR_ROOT'          => 7,//任务大厅车辆认证
    'ACTION_CERTIFICATE_VIDEO_ROOT'        => 8,//任务大厅视频认证
    'ACTION_ALLOCATE_CERTIFICATE_CAR'      => 9,//任务大厅分配车辆认证任务
    'ACTION_ALLOCATE_CERTIFICATE_VIDEO'    => 10,//任务大厅分配视频认证任务

    'ACTION_PULL_CERTIFICATE_CAR_REQUEST'  => 11,//任务大厅拉取车辆认证任务
    'ACTION_PULL_CERTIFICATE_VIDEO_REQUEST'=> 12,//任务大厅拉取视频认证任务

    'ACTION_ADMIN_QUIT_GROUP'              => 13,//管理员退出所在组
    'ACTION_DELETE_ADMIN'                  => 14,//删除管理员
    'ACTION_GROUP_ADD_ADMIN'               => 15,//添加管理员进组
    'ACTION_CREATE_ADMIN'                  => 16,//创建管理员
    'ACTION_DELETE_ADMIN_GROUP_PERMISSION' => 17,//移除管理员组权限
    'ACTION_ADD_ADMIN_GROUP_PERMISSION'    => 18,//添加管理员组权限
    'ACTION_CREATE_ADMIN_GROUP'            => 19,//创建管理员组
    'ACTION_DELETE_ADMIN_GROUP'            => 20,//删除管理员组

    'ACTION_ALLOCATE_ACCUSATION'                 => 21,//任务大厅分配举报任务
    'ACTION_CONFIRM_CERTIFICATE_CAR_PROCESSED'   => 22, //车辆认证任务大厅确认已完成任务
    'ACTION_CONFIRM_CERTIFICATE_VIDEO_PROCESSED' => 23, //视频认真任务大厅确认已完成任务

    'ACTION_RESET_CERTIFICATE_CAR_TEST_DATA'     => 24,//重置车辆认证测试数据
    'ACTION_RESET_CERTIFICATE_VIDEO_TEST_DATA'   => 25,//重置视频认证测试数据
    'ACTION_RESET_ACCUSATION_TEST_DATA'          => 26,//重置举报及封禁测试数据
    'ACTION_CONFIRM_ACCUSATION_PROCESSED'        => 27,//举报任务大厅确认已完成任务

    'ACTION_CONFIRM_MODIFY_USER_INFO_ALL_PASS'   => 28,//文字审查确认全部通过
    'ACTION_CONFIRM_MODIFY_USER_INFO_SINGLE'     => 29,//文字审查单个确认

    'ACTION_RESET_MODIFY_USER_INFO_TEST_DATA'    => 30,//重置文字审查测试数据

    'ACTION_CERTIFICATE_ACCUSATION'              => 31,//普通管理员举报审核
    'ACTION_CERTIFICATE_ACCUSATION_ROOT'         => 32,//任务大厅举报审核

    'ACTION_ALLOCATE_FEEDBACK'                   => 33,//任务大厅分配意见反馈任务
    'ACTION_RESET_FEEDBACK_TEST_DATA'            => 34,//重置用户反馈测试数据

    'ACTION_UNDO_CERTIFICATE_VIDEO'              => 35,//取消视频认证
    'ACTION_UNDO_CERTIFICATE_CAR'                => 36,//取消车辆认证
    'ACTION_MODIFY_USER_INFO'                    => 37,//修改用户资料

    'ACTION_UNDO_CLOSURE'                        => 38,//解除封禁

    'ACTION_ADD_PUSH_USER'                       => 39,//添加地推帐号
    'ACTION_UNDO_PUSH_USER'                      => 40,//取消地推帐号

    'ACTION_USER_INFO_CERTIFICATE'               => 41,//文字审核
    'ACTION_HEAD_IMAGE_CERTIFICATE'              => 42,//图片审核
    'ACTION_USER_INFO_CERTIFICATE_ALL_PASS'      => 43,//文字审核全部通过
    'ACTION_USER_INFO_REQUEST_DELETE'            => 44,//文字审核确认结束
    'ACTION_HEAD_IMG_CERTIFICATE_ALL_PASS'       => 45,//图片审核全部通过
    'ACTION_HEAD_IMG_REQUEST_DELETE'             => 46,//图片审核确认结束

    
    'PRIZE_MANAGE_INDEX'                         => 47,// 奖品管理首页

    'FACE_CAR_INFOSHOW'                          => 48,// 打分系统总览
    'SENTMESSAGE_GROUP'                          => 49,// 群发消息


    'REDPUSH'                                    => 50,// 后台红点推送
    'PULLCONTEN'                                 => 51,// 意见反馈
    'PUTINF_LIST'                                => 52,// 首页推荐

    'BANNER_ADD'                                 => 53,// banner添加
    'BANNER_LIST'                                => 54,// banner列表
    'SEND_GOLD'                                  => 55,// 批量发金币

    'SELECT_HOT'                                 => 56,// 选择热门动态
    'NOW_HOT'                                    => 57,// 当前热门

);
