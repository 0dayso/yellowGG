<?php
/**
 * Created by PhpStorm.
 * User: zsj
 * Date: 2015/7/15
 * Time: 15:52
 */

namespace Cms\Controller;

use Cms\Controller;

class AdvertController extends PublicController{

    public function index(){


        $start = trim($_GET['start']);
        $end   = trim($_GET['end']);

        $this->assign('data',A('Count','Event')->countldr($start,$end));
        $this->display();
    }

    public function infototalout(){

        if(IS_GET){
            $code   = $_GET['code'];
            $start = strtotime($_GET['start']);
            $end   = strtotime($_GET['end']);
            $this->assign('list',A('Count','Event')->getUserAnalysisInfo($code,$start,$end));
            $this->display();
        }else{
            $this->display();
        }

    }


}