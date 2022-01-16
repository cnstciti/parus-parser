<?php

namespace frontend\controllers;

use frontend\models\ResendVerificationEmailForm;
use frontend\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use frontend\models\ON;
use yii\web\Response;
use frontend\models\BFunction;

/**
 * On controller
 */
class OnController extends Controller
{
    /**
     * Возвращает список ИД ОН аренды (квартиры + комнаты)
     *
     * Вызов:
     *      GET <SERVER>/on/delete
     *
     * Вход:
     *      'id' => <int> // ИД ОН аренды
     *
     * Выход:
     *      нет
     */
    public function actionDelete()
    {
        $request = Yii::$app->getRequest();
        $params  = $request->isPost ? $request->getBodyParams() : [];
        $params  = BFunction::arrayChangeKeyCase($params);

        ON::delete($params['id']);
    }
}
