<?php
/**
 * Created by PhpStorm.
 * User: fuk
 * Date: 2015/1/7
 * Time: 10:39
 */

namespace Cms\Model;

use Cms\Model;
class SendModel extends CjChatLogModel{
    protected $tablePrefix = '';

    /*
     * 获取聊天记录的N张表表名
     * */
    public function get_table_name()
    {
        $tableNameArr = array();
        $timeLimit    = C('REVIEW_CHAT_LOG_TIME_LIMIT');
        for($i=0;$i<$timeLimit;$i++){
            $date = date("Ymd",strtotime('-'.$i.'day'));
            array_push($tableNameArr,'send'.$date);
        }

        return $tableNameArr;
    }
}
