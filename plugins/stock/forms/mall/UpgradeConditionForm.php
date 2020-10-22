<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019/7/3
 * Time: 16:23
 */

namespace app\plugins\stock\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\stock\models\StockLevel;
use app\plugins\stock\models\StockLevelUp;
use Yii;
use yii\helpers\ArrayHelper;

class UpgradeConditionForm extends Model
{

    public $type;
    public $remark;


    public function rules()
    {
        return [
            [['type',], 'integer'],
            [['remark',], 'string'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $t = Yii::$app->db->beginTransaction();
        try {
            $model = StockLevelUp::findOne(['mall_id' => Yii::$app->mall->id]);
            if (empty($model)) {
                $model = new StockLevelUp();
                $model->mall_id = Yii::$app->mall->id;
            }
            //改变自动升级类型时，重置所有等级升级条件
            if ($model->type != $this->type) {
                StockLevel::updateAll(['condition' => 0], ['mall_id' => Yii::$app->mall->id]);
            }
            $model->type = $this->type;
            $model->remark = $this->remark;
            if (!$model->save()) {
                throw new \Exception($this->getErrorMsg($model));
            }

            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $e) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function search()
    {
        $model = StockLevelUp::findOne(['mall_id' => Yii::$app->mall->id]);
        if (empty($model)) {
            $model = new StockLevelUp();
            $model->mall_id = Yii::$app->mall->id;
            $model->type = 1;
            $model->remark = '股东分红是基于分销商身份新建立起来的一种全新身份——股东。股东分红区别于团队分红的点在于股东分红的订单范围不再局限于某一个关系链的订单，而是全部自营商品的订单。（注意：不适用于多商户）商家设置订单实付金额的一定比例作为订单分红金额，这部分金额将被所有股东瓜分。各股东可瓜分的分红，取决于他的股东等级，等级不同，分红比例不同。分红比例越大的股东等级，股东获得的分红越高。

分红计算细则：
案例:过售后的订单实付金额为100元，订单分红比例为10%，则分红总金额为10元；
等级1股东的股东分红比例为10%，等级1共有2个股东；
等级2股东的股东分红比例为20%，等级2共有5个股东；
等级3股东的股东分红比例为30%，等级3共有10个股东；

等级1每个股东可得：
10%*10元/(10%*2+20%*5+30%*10)=0.24元
等级2每个股东可得：
20%*10元/(10%*2+20%*5+30%*10)=0.48元
等级3每个股东可得：
30%*10元/(10%*2+20%*5+30%*10)=0.71元';
            $model->save();
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => ArrayHelper::toArray($model)
        ];
    }
}