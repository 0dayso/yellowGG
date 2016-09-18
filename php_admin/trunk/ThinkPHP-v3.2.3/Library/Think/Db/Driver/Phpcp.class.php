<?php
namespace Think\Db\Driver;
use Think\Db\Driver;

/**
 * PHP-CP数据库连接池驱动 
 */
class Phpcp extends Mysql
{

    /**
     * 连接数据库方法
     * @access public
     */
    public function connect($config='',$linkNum=0,$autoConnection=false)
    {
        if ( !isset($this->linkID[$linkNum]) )
        {
            if(empty($config))  $config =   $this->config;
            try{
                if(empty($config['dsn']))
                {
                    $config['dsn']  =   $this->parseDsn($config);
                }
                if(version_compare(PHP_VERSION,'5.3.6','<=')){ 
                    // 禁用模拟预处理语句
                    $this->options[PDO::ATTR_EMULATE_PREPARES]  =   false;
                }
                $this->linkID[$linkNum] = new \pdoProxy( $config['dsn'], $config['username'], $config['password'],$this->options);
            }
            catch (\PDOException $e)
            {
                if($autoConnection){
                    trace($e->getMessage(),'','ERR');
                    return $this->connect($autoConnection,$linkNum);
                }elseif($config['debug']){
                    E($e->getMessage());
                }
            }
        }
        return $this->linkID[$linkNum];
    }

}