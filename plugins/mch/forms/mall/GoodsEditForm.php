<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\forms\mall;


use app\core\response\ApiCode;
use app\forms\mall\goods\BaseGoodsEdit;
use app\models\Mall;
use app\plugins\mch\Plugin;

/**
 * @property Mall $mall;
 */
class GoodsEditForm extends BaseGoodsEdit
{
    public $mall;
    public $id;

    public function rules()
    {
        return array_merge(parent::rules(), []);
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), []);
    }


    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        if (!$this->mall) {
            $this->mall = \Yii::$app->mall;
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            if (isset($this->extra['integral_num']['value']) && $this->extra['integral_num']['value'] <= 0) {
                throw new \Exception('商品积分必需大于0');
            }
            $this->attrValidator();
            $this->setGoods();

            $this->setAttr();
            $this->setGoodsCat();
            $this->setGoodsService();
            $this->setCard();
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    protected function setGoodsSign()
    {
        return (new Plugin())->getName();
    }

    protected function checkExtra($goodsAttr)
    {
        if (!isset($goodsAttr['extra']['integral_num']) || $goodsAttr['extra']['integral_num']['value'] <= 0) {
            throw new \Exception('请填写多规格积分价');
        }
    }
}
