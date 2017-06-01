<?php

/**
 * Created by PhpStorm.
 * User: farhan.gul
 * Date: 5/31/17
 * Time: 12:27 PM
 */
//Yii::import('application.modules.opportunities.models');

//use application\modules\opportunites\models\Opportunity;

//var_dump( get_declared_classes() ); exit;

use Opportunity;

class MyOpportunity extends Opportunity
{
    public function search($resultsPerPage=null, $uniqueId=null) {
        echo 'inside search 2'; exit;
        $criteria=new CDbCriteria;
        // $parameters=array("condition"=>"salesStage='Working'",'limit'=>ceil(Profile::getResultsPerPage()));
        $parameters=array('limit'=>ceil(Profile::getResultsPerPage()));
        $criteria->scopes=array('findAll'=>array($parameters));

        return $this->searchBase($criteria, $resultsPerPage);
    }


}