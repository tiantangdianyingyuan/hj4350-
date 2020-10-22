<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/10/31
 * Time: 9:25
 */
namespace app\plugins\vip_card\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoodsList;
use app\models\Goods;
use app\models\Model;
use app\plugins\vip_card\models\VipCardAppointGoods;

class GoodsForm extends Model
{
    public $batch_ids = [];
    public $status;
    public $is_all;
    public $plugin_sign;

    public function rules()
    {
        return [
            [['status', 'is_all'], 'integer'],
            [['plugin_sign'],'string'],
            [['batch_ids'], 'safe']
        ];
    }

    public function batchUpdateAppoint()
    {
        if ($this->is_all) {
            $this->batch_ids = Goods::find()->where(['mall_id' => \Yii::$app->mall->id,'sign'=> $this->plugin_sign,'is_delete' => 0])->select('id')->column();
        }
        if ($this->status == 1) {
            $appoint = VipCardAppointGoods::find()->select('goods_id')->where(['goods_id' => $this->batch_ids])->asArray()->all();
            if ($appoint) {
                $goodsIds = array_column($appoint,'goods_id');
                $deal = array_diff($this->batch_ids,$goodsIds);
            } else {
                $deal = $this->batch_ids;
            }
            $adds = [];
            $time = mysql_timestamp();
            foreach ($deal as $v) {
                $temp['goods_id'] = $v;
                $temp['created_at'] = $time;
                $adds[] = $temp;
            }
            $res = \Yii::$app->db->createCommand()->batchInsert(VipCardAppointGoods::tableName(), ['goods_id','created_at'], $adds)->execute();
        } else {
            $res = VipCardAppointGoods::deleteAll(['goods_id' => $this->batch_ids]);
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '更新成功',
            'data' => [
                'num' => $res
            ]
        ];
    }

    public function recommend()
    {
        $form = new CommonGoodsList();
        $form->limit = 9;
        $form->status = 1;
        $form->sign = [''];
        $list = $form->getList();
        foreach ($list as &$item) {
            unset($item['is_negotiable']);
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list
            ]
        ];
    }
}
