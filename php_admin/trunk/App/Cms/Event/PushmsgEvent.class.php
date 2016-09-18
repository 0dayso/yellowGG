<?php
/**
 * Created by PhpStorm.
 * User: fengj
 * Date: 2015/7/29
 * Time: 15:00
 */

namespace Cms\Event;

use Cms\Event;

require_once("./ThinkPHP/Library/Org/notification/android/AndroidBroadcast.php");
require_once("./ThinkPHP/Library/Org/notification/android/AndroidFilecast.php");
require_once("./ThinkPHP/Library/Org/notification/android/AndroidGroupcast.php");
require_once("./ThinkPHP/Library/Org/notification/android/AndroidUnicast.php");
require_once("./ThinkPHP/Library/Org/notification/android/AndroidCustomizedcast.php");
require_once("./ThinkPHP/Library/Org/notification/ios/IOSBroadcast.php");
require_once("./ThinkPHP/Library/Org/notification/ios/IOSFilecast.php");
require_once("./ThinkPHP/Library/Org/notification/ios/IOSGroupcast.php");
require_once("./ThinkPHP/Library/Org/notification/ios/IOSUnicast.php");
require_once("./ThinkPHP/Library/Org/notification/ios/IOSCustomizedcast.php");



class PushMsgEvent extends PublicEvent{



    protected $appkey           = NULL;
    protected $appMasterSecret  = NULL;
    protected $timestamp        = NULL;
    protected $validation_token = NULL;

    public function __construct() {
        $this->appkey = '55af0cd567e58e5dc8002e06';
        $this->appMasterSecret = 'ynkfdfa17z1tkhp3iaxipkwpkd7yvv64';
        $this->timestamp = strval(time());
    }

    public function sendAndroidBroadcast($data) {
        try {
            /*$this->appkey = '54c5f8d2fd98c5849600027e';  // 开发
            $this->appMasterSecret = 'zac6hjckr3cufomyrh4utscjbdmj2llo';*/
            $this->appkey = '55af0c7e67e58e40020050f0';  // 正式
            $this->appMasterSecret = '5f3r1zbqvcrp1wsc2rahmojytl4exqan';

            $brocast = new \AndroidBroadcast();
            $brocast->setAppMasterSecret($this->appMasterSecret);
            $brocast->setPredefinedKeyValue("appkey",           $this->appkey);
            $brocast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            $brocast->setPredefinedKeyValue("ticker",           "初见");
            $brocast->setPredefinedKeyValue("title",            "初见");
            $brocast->setPredefinedKeyValue("text",             $data['content']);
            $brocast->setPredefinedKeyValue("after_open",       "go_custom");
            $brocast->setPredefinedKeyValue("custom",           '{hi}');

            if($data['policy']!=''){
                $brocast->setCustomizedField("start_time", $data['policy']);
            }
            $brocast->setExtraField("type", $data['json']['type']);
            $brocast->setExtraField("item", $data['json']['item']);
            if($data['what']!='') {
                $brocast->setExtraField("what", $data['what']);
            }
            if($data['infoc']!='') {
                $brocast->setExtraField("infoc", $data['infoc']);
            }
            if($data['bannertitle']!=''){
                $brocast->setExtraField("bannertitle", $data['bannertitle']);
            }

            if($data['activity_desc']!='') {
                $brocast->setExtraField("activity_desc", $data['activity_desc']);
            }
            if($data['activity_pic']!=''){
                $brocast->setExtraField("activity_pic", $data['activity_pic']);
            }

            $brocast->setPredefinedKeyValue("production_mode", "true");

            $info = json_decode($brocast->send(),true);
            if($info['ret']=='SUCCESS'){
                return 'Android 广播发送成功!';
            }else{
                return 'Android 广播发送失败!';
            }
        } catch (Exception $e) {
            print("Caught exception: " . $e->getMessage());
        }
    }

    public function sendAndroidUnicast($data) {
        try {
            /*$this->appkey = '54c5f8d2fd98c5849600027e';  // 开发
            $this->appMasterSecret = 'zac6hjckr3cufomyrh4utscjbdmj2llo';*/
            $this->appkey = '55af0c7e67e58e40020050f0';  // 正式
            $this->appMasterSecret = '5f3r1zbqvcrp1wsc2rahmojytl4exqan';
            /*echo $data['device_token'];
            exit;*/
            $unicast = new \AndroidUnicast();
            $unicast->setAppMasterSecret($this->appMasterSecret);
            $unicast->setPredefinedKeyValue("appkey",           $this->appkey);
            $unicast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            // Set your device tokens here
            $unicast->setPredefinedKeyValue("device_tokens",    $data['device_token']);
            $unicast->setPredefinedKeyValue("ticker",           "初见");
            $unicast->setPredefinedKeyValue("title",            "初见");
            $unicast->setPredefinedKeyValue("text",             $data['content']);
            $unicast->setPredefinedKeyValue("after_open",       "go_custom");


            $unicast->setPredefinedKeyValue("production_mode", "true");
            $unicast->setPredefinedKeyValue("custom", '{hi}'); // "after_open"为"go_custom"时， 该字段必填。用户自定义内容, 可以为字符串或者JSON格式。

            $unicast->setExtraField("type", $data['json']['type']);
            $unicast->setExtraField("item", $data['json']['item']);
            if($data['what']!='') {
                $unicast->setExtraField("what", $data['what']);
            }
            if($data['infoc']!='') {
                $unicast->setExtraField("infoc", $data['infoc']);
            }
            if($data['bannertitle']!=''){
                $unicast->setExtraField("bannertitle", $data['bannertitle']);
            }
            if($data['activity_desc']!='') {
                $unicast->setExtraField("activity_desc", $data['activity_desc']);
            }
            if($data['activity_pic']!=''){
                $unicast->setExtraField("activity_pic", $data['activity_pic']);
            }

            //$unicast->send();
            $info = json_decode($unicast->send(),true);
            if($info['ret']=='SUCCESS'){
                return 'Android 发送成功!';
            }else{
                return 'Android 发送失败!';
            }
        } catch (Exception $e) {
            print("Caught exception: " . $e->getMessage());
        }
    }

    public function sendAndroidFilecast() {
        try {
            $filecast = new AndroidFilecast();
            $filecast->setAppMasterSecret($this->appMasterSecret);
            $filecast->setPredefinedKeyValue("appkey",           $this->appkey);
            $filecast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            $filecast->setPredefinedKeyValue("ticker",           "Android filecast ticker");
            $filecast->setPredefinedKeyValue("title",            "Android filecast title");
            $filecast->setPredefinedKeyValue("text",             "Android filecast text");
            $filecast->setPredefinedKeyValue("after_open",       "go_app");  //go to app
            print("Uploading file contents, please wait...\r\n");
            // Upload your device tokens, and use '\n' to split them if there are multiple tokens
            $filecast->uploadContents("aa"."\n"."bb");
            print("Sending filecast notification, please wait...\r\n");
            $filecast->send();
            print("Sent SUCCESS\r\n");
        } catch (Exception $e) {
            print("Caught exception: " . $e->getMessage());
        }
    }

    public function sendAndroidGroupcast() {
        try {
            /*
              *  Construct the filter condition:
              *  "where":
              * {
              *     "and":
              *     [
                *           {"tag":"test"},
                *           {"tag":"Test"}
              *     ]
              * }
              */
            $filter =   array(
                "where" =>  array(
                    "and"   =>  array(
                        array(
                            "tag" => "test"
                        ),
                        array(
                            "tag" => "Test"
                        )
                    )
                )
            );

            $groupcast = new AndroidGroupcast();
            $groupcast->setAppMasterSecret($this->appMasterSecret);
            $groupcast->setPredefinedKeyValue("appkey",           $this->appkey);
            $groupcast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            // Set the filter condition
            $groupcast->setPredefinedKeyValue("filter",           $filter);
            $groupcast->setPredefinedKeyValue("ticker",           "Android groupcast ticker");
            $groupcast->setPredefinedKeyValue("title",            "Android groupcast title");
            $groupcast->setPredefinedKeyValue("text",             "Android groupcast text");
            $groupcast->setPredefinedKeyValue("after_open",       "go_app");
            // Set 'production_mode' to 'false' if it's a test device.
            // For how to register a test device, please see the developer doc.
            $groupcast->setPredefinedKeyValue("production_mode", "true");
            print("Sending groupcast notification, please wait...\r\n");
            $groupcast->send();
            print("Sent SUCCESS\r\n");
        } catch (Exception $e) {
            print("Caught exception: " . $e->getMessage());
        }
    }

    public function sendAndroidCustomizedcast() {
        try {
            $customizedcast = new AndroidCustomizedcast();
            $customizedcast->setAppMasterSecret($this->appMasterSecret);
            $customizedcast->setPredefinedKeyValue("appkey",           $this->appkey);
            $customizedcast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            // Set your alias here, and use comma to split them if there are multiple alias.
            // And if you have many alias, you can also upload a file containing these alias, then
            // use file_id to send customized notification.
            $customizedcast->setPredefinedKeyValue("alias",            "xx");
            // Set your alias_type here
            $customizedcast->setPredefinedKeyValue("alias_type",       "xx");
            $customizedcast->setPredefinedKeyValue("ticker",           "Android customizedcast ticker");
            $customizedcast->setPredefinedKeyValue("title",            "Android customizedcast title");
            $customizedcast->setPredefinedKeyValue("text",             "Android customizedcast text");
            $customizedcast->setPredefinedKeyValue("after_open",       "go_app");
            print("Sending customizedcast notification, please wait...\r\n");
            $customizedcast->send();
            print("Sent SUCCESS\r\n");
        } catch (Exception $e) {
            print("Caught exception: " . $e->getMessage());
        }
    }

    public function sendIOSBroadcast($data) {
        try {

            $brocast = new \IOSBroadcast();
            $brocast->setAppMasterSecret($this->appMasterSecret);
            $brocast->setPredefinedKeyValue("appkey",           $this->appkey);
            $brocast->setPredefinedKeyValue("timestamp",        $this->timestamp);

            $brocast->setPredefinedKeyValue("alert", $data['content']);
            $brocast->setPredefinedKeyValue("badge", 1);
            $brocast->setPredefinedKeyValue("sound", "push_alert.aif");

            if($data['policy']!=''){
                $brocast->setCustomizedField("start_time", $data['policy']);
            }

            $brocast->setCustomizedField("type", $data['json']['type']);
            $brocast->setCustomizedField("item", $data['json']['item']);
            if($data['what']!=''){
                $brocast->setCustomizedField("what", $data['what']);
            }
            if($data['infoc']!=''){
                $brocast->setCustomizedField("infoc", $data['infoc']);
            }
            if($data['bannertitle']!=''){
                $brocast->setCustomizedField("bannertitle", $data['bannertitle']);
            }
            if($data['activity_desc']!='') {
                $brocast->setCustomizedField("activity_desc", $data['activity_desc']);
            }
            if($data['activity_pic']!=''){
                $brocast->setCustomizedField("activity_pic", $data['activity_pic']);
            }

            $brocast->setPredefinedKeyValue("production_mode", "false");
            //$brocast->send();
            $info = json_decode($brocast->send(),true);
            if($info['ret']=='SUCCESS'){
                return 'ios 广播发送成功!';
            }else{
                return 'ios 广播发送失败!';
            }

        } catch (Exception $e) {
            print("Caught exception: " . $e->getMessage());
        }
    }

    public function sendIOSUnicast($data) {
        try {
            //$data['device_token'] = '21f46974f2db357f8a9a4f3195ef08c13482b3dd11af9c589998bc5cb9482b0f';
            $unicast = new \IOSUnicast();
            $unicast->setAppMasterSecret($this->appMasterSecret);
            $unicast->setPredefinedKeyValue("appkey",           $this->appkey);
            $unicast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            // Set your device tokens here
            $unicast->setPredefinedKeyValue("device_tokens", $data['device_token']);
            $unicast->setPredefinedKeyValue("alert",         $data['content']);
            $unicast->setPredefinedKeyValue("badge", 1);
            $unicast->setPredefinedKeyValue("sound", "push_alert.aif");

            $unicast->setPredefinedKeyValue("production_mode", "false");
            // Set customized fields
            $unicast->setCustomizedField("type", $data['json']['type']);
            $unicast->setCustomizedField("item", $data['json']['item']);
            if($data['what']!=''){
                $unicast->setCustomizedField("what", $data['what']);
            }
            if($data['infoc']!=''){
                $unicast->setCustomizedField("infoc", $data['infoc']);
            }
            if($data['bannertitle']!=''){
                $unicast->setCustomizedField("bannertitle", $data['bannertitle']);
            }

            if($data['activity_desc']!='') {
                $unicast->setCustomizedField("activity_desc", $data['activity_desc']);
            }
            if($data['activity_pic']!=''){
                $unicast->setCustomizedField("activity_pic", $data['activity_pic']);
            }

            $info = json_decode($unicast->send(),true);
            if($info['ret']=='SUCCESS'){
                return 'ios 发送成功!';
            }else{
                return 'ios 发送失败!';
            }
        } catch (Exception $e) {
            echo "<pre>";
            print("Caught exception: " . $e->getMessage());
        }



    }







    public function sendIOSFilecast() {
        try {
            $filecast = new IOSFilecast();
            $filecast->setAppMasterSecret($this->appMasterSecret);
            $filecast->setPredefinedKeyValue("appkey",           $this->appkey);
            $filecast->setPredefinedKeyValue("timestamp",        $this->timestamp);

            $filecast->setPredefinedKeyValue("alert", "IOS 文件播测试");
            $filecast->setPredefinedKeyValue("badge", 0);
            $filecast->setPredefinedKeyValue("sound", "chime");
            // Set 'production_mode' to 'true' if your app is under production mode
            $filecast->setPredefinedKeyValue("production_mode", "false");
            print("Uploading file contents, please wait...\r\n");
            // Upload your device tokens, and use '\n' to split them if there are multiple tokens
            $filecast->uploadContents("aa"."\n"."bb");
            print("Sending filecast notification, please wait...\r\n");
            $filecast->send();
            print("Sent SUCCESS\r\n");
        } catch (Exception $e) {
            print("Caught exception: " . $e->getMessage());
        }
    }

    public function sendIOSGroupcast() {
        try {
            /*
              *  Construct the filter condition:
              *  "where":
              * {
              *     "and":
              *     [
                *           {"tag":"iostest"}
              *     ]
              * }
              */
            $filter =   array(
                "where" =>  array(
                    "and"   =>  array(
                        array(
                            "tag" => "iostest"
                        )
                    )
                )
            );

            $groupcast = new IOSGroupcast();
            $groupcast->setAppMasterSecret($this->appMasterSecret);
            $groupcast->setPredefinedKeyValue("appkey",           $this->appkey);
            $groupcast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            // Set the filter condition
            $groupcast->setPredefinedKeyValue("filter",           $filter);
            $groupcast->setPredefinedKeyValue("alert", "IOS 组播测试");
            $groupcast->setPredefinedKeyValue("badge", 0);
            $groupcast->setPredefinedKeyValue("sound", "chime");
            // Set 'production_mode' to 'true' if your app is under production mode
            $groupcast->setPredefinedKeyValue("production_mode", "false");
            print("Sending groupcast notification, please wait...\r\n");
            $groupcast->send();
            print("Sent SUCCESS\r\n");
        } catch (Exception $e) {
            print("Caught exception: " . $e->getMessage());
        }
    }

    public function sendIOSCustomizedcast() {
        try {
            $customizedcast = new IOSCustomizedcast();
            $customizedcast->setAppMasterSecret($this->appMasterSecret);
            $customizedcast->setPredefinedKeyValue("appkey",           $this->appkey);
            $customizedcast->setPredefinedKeyValue("timestamp",        $this->timestamp);

            // Set your alias here, and use comma to split them if there are multiple alias.
            // And if you have many alias, you can also upload a file containing these alias, then
            // use file_id to send customized notification.
            $customizedcast->setPredefinedKeyValue("alias", "xx");
            // Set your alias_type here
            $customizedcast->setPredefinedKeyValue("alias_type", "xx");
            $customizedcast->setPredefinedKeyValue("alert", "IOS 个性化测试");
            $customizedcast->setPredefinedKeyValue("badge", 0);
            $customizedcast->setPredefinedKeyValue("sound", "chime");
            // Set 'production_mode' to 'true' if your app is under production mode
            $customizedcast->setPredefinedKeyValue("production_mode", "false");
            print("Sending customizedcast notification, please wait...\r\n");
            $customizedcast->send();
            print("Sent SUCCESS\r\n");
        } catch (Exception $e) {
            print("Caught exception: " . $e->getMessage());
        }
    }


}

 
