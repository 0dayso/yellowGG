<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/2/9
 * Time: 17:35
 */

namespace Cms\Model;

use Cms\Model;
class UserInfoModifyRequestLogModel extends CjAdminLogModel{

    public function reset_test_data()
    {
        $resetModel = C('RESET_MODEL');
        $Model = D($this->name);
        switch($resetModel) {
            case 'local':
                $Model->truncate_table();
                break;
            case 'server':
                break;
            default:
                break;
        }
    }

    // 根据年月获取车类审核信息
    public function getdatewords($date,$aid=''){
        $words = D($this->name);
 
        if($aid){
            $awhere  =  ' AND aid = '.$aid;
        }
       
        $sql = "SELECT  reason,COUNT(reason) AS r,date_format(pass_time,'%Y-%m-%d') AS day,date_format(pass_time,'%d') AS d
                FROM cj_user_info_modify_request_log  
                WHERE date_format(pass_time,'%Y-%m') = '{$date}' {$awhere}
                GROUP BY reason,day HAVING reason >0  ORDER BY day
            ";
        $info = $words->query($sql);
        
        // 所有通过的数据
        $passsql = " SELECT COUNT(id) AS num,DATE_FORMAT(pass_time,'%Y-%m-%d') AS day,date_format(pass_time,'%d') AS d
                     FROM cj_user_info_modify_request_log 
                     WHERE operation = 3 AND DATE_FORMAT(pass_time,'%Y-%m') = '{$date}' {$awhere}
                     GROUP BY day ";
        $passinfo= $words->query($passsql);
      

        for($i=1; $i<=31; $i++){ 
            if($i<10){
                $d   = '0'.$i;
            }else{
                $d   = $i;
            }

            $day['1'][$i]     = 0;
            $day['2'][$i]     = 0;// 欺诈&广告
            $day['3'][$i]     = 0; // 招嫖卖淫
            $day['4'][$i]     = 0;// 违法&反动政治
            $day['5'][$i]     = 0; // 其他
            $day['6'][$i]     = 0;// 托
            $day['7'][$i]     = 0; // 骚扰
            
            foreach ($info as $key => $value) {
                if($value['d'] == $d && $value['reason'] == 1){
                    $day['1'][intval($d)] = $value['r'];
                }elseif ($value['d'] == $d && $value['reason'] == 2) {
                    $day['2'][intval($d)] = $value['r'];
                }elseif ($value['d'] == $d && $value['reason'] == 3) {
                    $day['3'][intval($d)] = $value['r'];  
                }elseif ($value['d'] == $d && $value['reason'] == 4) {
                    $day['4'][intval($d)] = $value['r']; 
                }elseif ($value['d'] == $d && $value['reason'] == 5) {
                    $day['5'][intval($d)] = $value['r']; 
                }elseif ($value['d'] == $d && $value['reason'] == 6) {
                    $day['6'][intval($d)] = $value['r'];  
                }elseif ($value['d'] == $d && $value['reason'] == 7) {
                    $day['7'][intval($d)] = $value['r'];
                } 
            }
            $day['pass'][$i] = 0;
            foreach ($passinfo as $key => $va) {
                if($va['d'] == $d){
                    $day['pass'][intval($d)] = $va['num'];
                }
            }
   
            $day['count'][$i]  = $day['1'][$i] + $day['2'][$i] + $day['3'][$i] + $day['4'][$i] + $day['5'][$i] + $day['6'][$i]+ $day['7'][$i]; 
        
        }
 
        return $day;
    }



}
