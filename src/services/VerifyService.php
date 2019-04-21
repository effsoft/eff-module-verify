<?php
namespace effsoft\eff\module\verify\services;

use effsoft\eff\module\verify\enums\Protocol;
use effsoft\eff\module\verify\enums\Type;
use effsoft\eff\module\verify\models\VerifyModel;
use yii\helpers\Url;

class VerifyService{

    private $type = false;
    private $protocol = false;
    private $from = false;
    private $to = false;
    private $subject = false;
    private $view = false;
    private $url = false;
    private $data = false;

    private $token = false;
    private $verify_url = false;
    private $code_length = 4;
    private $code = false;
    private $error = false;
    private $sent = false;

    public function getErrorMessage()
    {
        return $this->error;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
        return $this;
    }
    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }
    public function setTo($to)
    {
        $this->to = $to;
        return $this;
    }
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }
    public function setView($view)
    {
        $this->view = $view;
        return $this;
    }
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }


    public function send(){
        //generate token
        $this->token = \Yii::$app->getSecurity()->generateRandomString();
        //generate code
        $factory = new \RandomLib\Factory();
        $generator = $factory->getGenerator(new \SecurityLib\Strength(\SecurityLib\Strength::MEDIUM));
        $this->code = $generator->generateString($this->code_length,'0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
        //verify model entity
        $verify_model = new VerifyModel();
        $verify_model->type = intval($this->type);
        $verify_model->protocol = intval($this->protocol);
        $verify_model->from = $this->from;
        $verify_model->to = $this->to;
        $verify_model->url = $this->url;
        $verify_model->subject = $this->subject;
        $verify_model->token = $this->token;
        $verify_model->code = $this->code;
        $verify_model->data = $this->data;
        //save to database
        if (!$verify_model->save()){
            $this->error = 'Can not save verify data, please check your database!';
            return false;
        }

        if ($this->protocol === Protocol::EMAIL){
            $this->verify_url = Url::to([$this->url,
                'token' => $this->token,
            ],true);
            $register_email = \Yii::$app->mailer->compose($this->view,[
                'verify_code' => $this->code,
                'verify_url' =>  $this->verify_url,
            ]);
            //TODO: for test
//            $register_email
//                ->setFrom($this->from)
//                ->setTo($this->to)
//                ->setSubject($this->subject);
//            if (!@$register_email->send()){
//                return false;
//            }
            $this->sent = true;
        }

        if (!$this->sent){
            $this->error = 'Please complete your verify protocol information!';
            return false;
        }

        return $this->verify_url;
    }

    public function validate($token, $code){
        $verify_model = VerifyModel::findOne(['token' => $token]);
        if (empty($verify_model)){
            $this->error = 'Can not get data by this token!';
            return false;
        }
        if ($verify_model->isExpired()){
            $this->error = 'The verify code was expired, please re-send your verify code!';
            return false;
        }
        if ($verify_model->code === $code){
            if (!$verify_model->delete()){
                $this->error = 'Can not delete this verify information!';
                return false;
            }

            return $verify_model->data;
        }else{
            $this->error = 'Invalid verify code!';
            return false;
        }

    }
}