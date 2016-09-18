<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2015/1/7
 * Time: 13:46
 */

namespace Cms\Controller;

use Cms\Controller;
use Org\Net\Http;

class CostViewController extends PublicController{

    /*
     * 查看短信平台剩余费用
     * @param  get:
     *            POST /MWGate/wmgw.asmx/MongateQueryBalance HTTP/1.1
     *            Host: 192.169.1.130
     *            Content-Type: application/x-www-form-urlencoded
     *            Content-Length: length
     *            userId=string&password=string
     * @return    HTTP/1.1 200 OK
     *            Content-Type: text/xml; charset=utf-8
     *            Content-Length: length
     *            <?xml version="1.0" encoding="utf-8"?>
     *            <int xmlns="http://tempuri.org/">int</int>
     * */
    public function view_cost()
    {
        $host     = C('WEB_SERVICE_COST_VIEW_HOST');
        $url      = C('WEB_SERVICE_COST_VIEW_URL');
        $userId   = C('WEB_SERVICE_USER_ID');
        $password = C('WEB_SERVICE_PASSWORD');
        $combine  = $host.$url.'?userId='.$userId.'&password='.$password;

        $html  = file_get_contents($combine);
        $html  = $this->process_xml($html);

        $this->assign('list',$html);
        $this->display();
    }

    // 给用户发送短信
    public function sent_message(){

          
        if(IS_POST){
         
            define('SMS_SERVER_URL', 'http://61.145.229.29:9003/MWGate/wmgw.asmx/MongateCsSpSendSmsNew?userId=J02599&password=366021&pszMobis=%s&pszMsg=%s&iMobiCount=%d&pszSubPort=*');
 
            $url = sprintf(SMS_SERVER_URL,$_POST['phone'], urlencode($_POST['content']), 1);
            $xml = file_get_contents($url);

            preg_match('/<string[^>]*>([^<]*)<\/string>/i',$xml,$array);

            if($array[1] >0 ){
                $this->success('短信发送成功！');
            }else{
                $this->error('短信发送失败！');
            }
            exit;
        }
            
        $this->display();
    }

 




    /*
     * 处理xml
     * */
    protected function process_xml($html)
    {
        $matches = array();
        preg_match("/(<[\S\s]*?>+)([\d*]+)(<\/int>)/", $html, $matches,0,0);
        $ret = $matches[2];
        return $ret;
    }

    /*
     * 发送post请求
     * */
    protected function send_post($url, $param)
    {
        if(!is_array($param)){
            throw new \Exception("参数必须为array");
        }
        $httph =curl_init($url);
        curl_setopt($httph, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($httph, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($httph,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($httph, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
        curl_setopt($httph, CURLOPT_POST, 1);//设置为POST方式
        curl_setopt($httph, CURLOPT_POSTFIELDS, $param);
        curl_setopt($httph, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($httph, CURLOPT_HEADER,1);
        $result=curl_exec($httph);
        curl_close($httph);

        return $result;
    }

}

