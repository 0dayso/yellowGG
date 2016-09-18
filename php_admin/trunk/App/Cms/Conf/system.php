<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2014/12/15
 * Time: 20:45
 * 保存后台业务方面的配置信息
 */
return array(
    /*'CERTIFICATE_CAR_PIC'                => 'http://api.chujian.im/static/upload/carsimg',//车辆认证图片服务器路径
    'CERTIFICATE_VIDEO_PIC'              => 'http://api.chujian.im/static/upload/videoimg',//视频认证图片服务器路径*/
    'CERTIFICATE_CAR_PIC'                => 'http://certimage.chujian.im',//车辆认证图片服务器路径
    'CERTIFICATE_VIDEO_PIC'              => 'http://certimage.chujian.im',//视频认证图片服务器路径
    'USER_HEAD_IMG'                      => 'http://headimage.chujian.im', //用户头像图片服务器路径
    'USER_HEAD_IMG_SIZE'                 => '146',//api用户头像尺寸
    'USER_HEAD_IMG_ENLARGE_SIZE'         => '640',//用户头像尺寸
    'ALIYUN_HEAD_IMG_DOMAIN_NAME'        => 'http://headimage.oss-cn-hangzhou.aliyuncs.com',
    'ALIYUN_CERTIFICATE_DOMAIN_NAME'     => 'http://certimage.chujian.im',
    'CAR_LOGO_PATH'                      => 'http://static.chujian.im/car_logo/',
    'BANNER_PATH'                        => 'http://static.chujian.im/',
    'SURGING_PATH'                       => 'http://surging.chujian.im/',

    'ITEMS_PER_PAGE'                     => '50', //每页显示N条记录

    'USER_PASSWORD_SUFFIX'               => '4jfr84fjad',//批量添加用户时，用户密码加密时密码后缀
    'HEAD_IMG_EXT'                       => 'png',//上传图片时图片后缀

    'DELETE_HEAD_IMG_ARRAY'              => 1,//前端在属于某用户的一组图片中勾选删除，需记住是第几张图片
    'DELETE_HEAD_IMG_SINGLE'             => 2,//根据七牛key值删除图片

    'CERTIFICATE_CAR_REQUEST_LIMIT'      => 6,//从总的请求stack中获取前N条请求

    'CERTIFICATE_CAR_REQUEST_AID'        => 'cj_certificate_car_req_aid_',//根据aid生成的cj_certificate_car_req_aid表
    'CERTIFICATE_VIDEO_REQUEST_AID'      => 'cj_certificate_video_req_aid_',//根据aid生成的cj_certificate_video_req_aid表
    'ACCUSATION_REQUEST_AID'             => 'cj_accusation_req_aid_',//根据aid生成的cj_accusation_req_aid表
    'FEEDBACK_REQUEST_AID'               => 'cj_feedback_req_aid_',//根据aid生成的cj_feedback_req_aid表

    'REVIEW_CHAT_LOG_TIME_LIMIT'         => 7,//查看聊天记录时限设置为7天

    'WEB_SERVICE_COST_VIEW_HOST'         => 'http://61.145.229.29:9003', //第三方短信接口host
    'WEB_SERVICE_COST_VIEW_URL'          => '/MWGate/wmgw.asmx/MongateQueryBalance',//第三方短信接口url
    'WEB_SERVICE_USER_ID'                => 'J02599',
    'WEB_SERVICE_PASSWORD'               => '366021',

    'REDIS_ADMIN_LIST_KEY'               => 'cpp_systemchat_sms',//后台管理系统redis list
    'REDIS_DATA_JSON_TYPE'               => 1,//系统消息是1，意见反馈是2

    'ROBOT_ACCOUNT_UID_MIN'              => 16,//机器人帐号uid 最小值
    'ROBOT_ACCOUNT_UID_MAX'              => 75,//机器人帐号uid 最大值，机器人帐号取自这二者之间

    'CHAT_LOG_REVIEW_TIME'               => 1,//聊天记录可以查看的时间长度，单位为天

    'SYSTEM_ACCOUNT'                     => 10000,//系统帐号
    'AUTO_ALLOCATE_TASK_AVG'             => 2,//自动分配50%,
);
