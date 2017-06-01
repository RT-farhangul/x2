<?php

//use \X2Widget;

/**
 * Created by PhpStorm.
 * User: farhan.gul
 * Date: 5/26/17
 * Time: 12:09 PM
 */
class CustomWidget extends X2Widget
{
    private $_widget;

    public function run(){
        echo "Inside Custom Widget...";
        $this->render('CustomWidget', array(
            'msg' => 'welcome...',
        ));
        //Yii::app()->end();
    }

}