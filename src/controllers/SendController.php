<?php
namespace effsoft\eff\module\verify\controllers;

use effsoft\eff\EffController;
use effsoft\eff\module\verify\models\VerifyModel;
use effsoft\eff\module\verify\Verify;
use effsoft\eff\response\JsonResult;
use yii\web\Response;

class SendController extends EffController{

    public function actionIndex(){
        if(\Yii::$app->request->isPost && \Yii::$app->request->isAjax){
            \Yii::$app->response->format = Response::FORMAT_JSON;
            if (!\Yii::$app->request->validateCsrfToken()) {
                return JsonResult::getNewInstance()->setStatus(101)->setMessage('Invalid csrf token!')->getResponse();
            }
            $token = \Yii::$app->request->post('token');
            if (empty($token)){
                return JsonResult::getNewInstance()->setStatus(102)->setMessage('Invalid token!')->getResponse();
            }
            $verify_model = VerifyModel::findOne(['token' => $token]);
            if (empty($verify_model)){
                return JsonResult::getNewInstance()->setStatus(103)->setMessage('Can not get data by this token!')->getResponse();
            }
            $verify = new Verify();
            $verify_url = $verify->setType($verify_model->type)
                ->setProtocol($verify_model->protocol)
                ->setFrom($verify_model->from)
                ->setTo($verify_model->to)
                ->setUrl($verify_model->url)
                ->setData($verify_model->data)
                ->setSubject($verify_model->subject)
                ->setView($verify_model->view)
                ->send();
            if (!$verify_model->delete()){
                return JsonResult::getNewInstance()->setStatus(104)->setMessage('Can not delete token, please try again later!')->getResponse();
            }
            if (empty($verify_url)) {
                return JsonResult::getNewInstance()->setStatus(105)->setMessage('Can not send verify code, please try again later!')->getResponse();
            }
            return JsonResult::getNewInstance()->setStatus(0)->setMessage($verify_url)->getResponse();
        }
    }
}