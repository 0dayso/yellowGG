<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Think\Cache\Driver;
use Think\Cache;
defined('THINK_PATH') or exit();

/**
 * Redis缓存驱动 
 * 要求安装phpredis扩展：https://github.com/nicolasff/phpredis
 */
class Redis extends Cache {
	 /**
	 * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    public function __construct($options=array()) {
        if ( !extension_loaded('redis') ) {
            E(L('_NOT_SUPPERT_').':redis');
        }
        if(empty($options)) {
            $options = array (
                'host'          => C('REDIS_HOST') ? C('REDIS_HOST') : '127.0.0.1',
                'port'          => C('REDIS_PORT') ? C('REDIS_PORT') : 6379,
                'timeout'       => C('DATA_CACHE_TIMEOUT') ? C('DATA_CACHE_TIMEOUT') : false,
                'persistent'    => false,
            );
        }
        $this->options =  $options;
        $this->options['expire'] =  isset($options['expire'])?  $options['expire']  :   C('DATA_CACHE_TIME');
        $this->options['prefix'] =  isset($options['prefix'])?  $options['prefix']  :   C('DATA_CACHE_PREFIX');        
        $this->options['length'] =  isset($options['length'])?  $options['length']  :   0;        
        $func = $options['persistent'] ? 'pconnect' : 'connect';
        $this->handler  = new \Redis;
        $options['timeout'] === false ?
            $this->handler->$func($options['host'], $options['port']) :
            $this->handler->$func($options['host'], $options['port'], $options['timeout']);
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        N('cache_read',1);
        $value = $this->handler->get($this->options['prefix'].$name);
        $jsonData  = json_decode( $value, true );
        return ($jsonData === NULL) ? $value : $jsonData;	//检测是否为JSON数据 true 返回JSON解析数组, false返回源数据
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return boolean
     */
    public function set($name, $value, $expire = null) {
        N('cache_write',1);
        if(is_null($expire)) {
            $expire  =  $this->options['expire'];
        }
        $name   =   $this->options['prefix'].$name;
        //对数组/对象数据进行缓存处理，保证数据完整性
        $value  =  (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        if(is_int($expire)) {
            $result = $this->handler->setex($name, $expire, $value);
        }else{
            $result = $this->handler->set($name, $value);
        }
        if($result && $this->options['length']>0) {
            // 记录缓存队列
            $this->queue($name);
        }
        return $result;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm($name) {
        return $this->handler->delete($this->options['prefix'].$name);
    }

    /**
     * 判断key是否存在
     * @access public
     * @param string $key 缓存变量名
     * @return boolean
     */
    public function exists( $key ) {
        return $this->handler->exists($this->options['prefix'].$key);
    }

    /**
     * 为了实现生成token写的接口
     * @access public
     * @param string $key key值
     * @param string $hashkey 哈希值
     * @param string $value
     * @return boolean
     */
    public function hSet($key, $hashKey,$value) {
        return $this->handler->hSet($this->options['prefix'].$key, $hashKey,$value);
    }

    /**
     * 为了实现删除token些的接口
     * @access public
     * @param string $key key值
     * @param string $hashkey1 哈希值
     * @return boolean
     */
    public function hDel($key, $hashKey1) {
        return $this->handler->hDel($this->options['prefix'].$key, $hashKey1);
    }

    /**
     * list头部添加元素
     * @access public
     * @param string $key list key值
     * @param string $value1 插入的数据
     * @return boolean
     */
    public function lPush($key, $value1)
    {
        return $this->handler->lPush($this->options['prefix'].$key, $value1 ,$value2 = null, $valueN = null);
    }


    /**
     * list尾部添加元素
     * @access public
     * @param string $key list key值
     * @param string $value1 插入的数据
     * @return boolean
     */
    public function rPush( $key, $value1) {
        return $this->handler->rPush($this->options['prefix'].$key, $value1);
    }

    /**
     * list弹出数据
     * @access public
     * @param string $key list key值
     * @param string $value1 插入的数据
     * @return boolean
     */
    public function lPop( $key ) {
        return $this->handler->lPop($this->options['prefix'].$key);
    }

    /**
     * 清除缓存
     * @access public
     * @return boolean
     */
    public function clear() {
        return $this->handler->flushDB();
    }

    /**
     * 建立list对象
     * @access public
     * @param  key 缓存对象名
     * @param  index 索引
     * @param  value 初始值
     * @return boolean
     */
    public function lSet( $key, $index, $value ) {
        return $this->handler->lSet($this->options['prefix'].$key,$index,$value);
    }

}
