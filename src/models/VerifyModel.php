<?php

namespace effsoft\eff\module\verify\models;

use effsoft\eff\EffMysqlActiveRecord;
use yii\mongodb\ActiveRecord;

class VerifyModel extends EffMysqlActiveRecord {

//    public static function collectionName()
//    {
//        return 'Verify';
//    }

    public static function tableName()
    {
        return 'verify';
    }

    public function attributes()
    {
        return ['id', 'type', 'protocol','from', 'to', 'url', 'subject', 'view', 'token', 'code', 'data', 'date_created'];
    }

    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            $now = time();
            VerifyModel::deleteAll(['<', 'date_created' , (time() - 60 * 60 * 24 * 30)]);
            $this->date_created = $now;
            return true;
        }
        return false;
    }

    public function isExpired(){
        return $this->date_created < (time() - 30 * 60);
    }
}