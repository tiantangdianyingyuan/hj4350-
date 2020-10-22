<?php

namespace app\plugins\exchange\jobs;

use app\models\Mall;
use app\plugins\exchange\forms\common\CommonResult;
use app\plugins\exchange\forms\exchange\core\Create;
use app\plugins\exchange\forms\exchange\core\Reward;
use app\plugins\exchange\forms\exchange\exception\RollBackException;
use app\plugins\exchange\forms\exchange\validate\FacadeAdmin;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class ExchangeJob extends BaseObject implements JobInterface
{
    public $user;
    public $origin;
    public $code;
    public $token;
    public $result_token;
    public $extra_info;

    public function execute($queue)
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            $f = new FacadeAdmin();
            $f->admin($this->user->mall_id, $this->code);
            $model = $f->validate;
            $codeModel = $model->codeModel;

            //商城
            $mall = Mall::findOne(['id' => $this->user->mall_id]);
            \Yii::$app->setMall($mall);

            //token检测
            $model->libraryModel->mode > 0 && $f->token($model->libraryModel->rewards, $this->token);

            //保存日志
            $create = new Create();
            $create->start(
                $codeModel,
                $model->libraryModel,
                $this->user,
                $this->token,
                $this->code,
                array_merge($this->extra_info, ['origin' => $this->origin])
            );

            //追加奖品
            $reward = new Reward();
            $reward->reward(
                $codeModel,
                $this->user,
                $this->result_token,
                $this->token,
                array_merge($this->extra_info, ['origin' => $this->origin])
            );
            $t->commit();
        } catch (RollBackException $e) {
            $t->rollBack();
            \Yii::error($e->getMessage());
            CommonResult::save($this->result_token, $e->getToken(), $e->getMessage());
        } catch (\Exception $e) {
            $t->rollBack();
            \Yii::error($e->getMessage());
        }
    }
}
