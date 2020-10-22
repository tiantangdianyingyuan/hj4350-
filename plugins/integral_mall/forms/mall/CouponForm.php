<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\integral_mall\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\integral_mall\forms\common\CouponListForm;
use app\plugins\integral_mall\models\IntegralMallCoupons;

class CouponForm extends Model
{
    public $id;
    public $page;
    public $keyword;

    public function rules()
    {
        return [
            [['id', 'page'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['keyword'], 'string']
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $form = new CouponListForm();
        $form->page = $this->page;
        $form->keyword = $this->keyword;
        $res = $form->search();

        return $res;
    }

    public function detail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $form = new CouponListForm();
        $detail = $form->getCoupon($this->id);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $detail
            ]
        ];
    }

    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $model = IntegralMallCoupons::findOne([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id
        ]);

        if (!$model) {
            throw new \Exception('优惠券不存在');
        }

        $model->is_delete = 1;
        $res = $model->save();

        if (!$res) {
            throw new \Exception($this->getErrorMsg($model));
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '删除成功'
        ];
    }
}
