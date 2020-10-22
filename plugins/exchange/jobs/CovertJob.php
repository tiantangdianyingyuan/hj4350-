<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\jobs;

use app\models\Mall;
use app\plugins\exchange\forms\exchange\core\Reward;
use app\plugins\exchange\forms\exchange\validate\FacadeAdmin;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class CovertJob extends BaseObject implements JobInterface
{
    public $user;
    public $origin;
    public $code;
    public $token;
    public $result_token;

    public function execute($queue)
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            $f = new FacadeAdmin();
            $f->validate->user = $this->user;
            $f->cover($this->user->mall_id, $this->code);
            $model = $f->validate;
            $codeModel = $model->codeModel;

            //商城
            $mall = Mall::findOne(['id' => $this->user->mall_id]);
            \Yii::$app->setMall($mall);

            //token检测
            $f->token($codeModel->r_rewards, $this->token);

            //追加奖品
            $reward = new Reward();
            $reward->reward(
                $codeModel,
                $this->user,
                $this->result_token,
                $this->token,
                ['origin' => $this->origin]
            );
             $t->commit();
        } catch (\Exception $e) {
            $t->rollBack();
            \Yii::error($e->getMessage());
        }
    }
}
