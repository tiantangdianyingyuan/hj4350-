<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\cat;

use app\core\response\ApiCode;
use app\forms\common\CommonAppConfig;
use app\forms\common\CommonOption;
use app\models\Model;
use app\models\Option;

class CatStyleForm extends Model
{
    public $cat_style;
    public $recommend_count;// 即将废弃
    public $cat_goods_count;
    public $cat_goods_cols;

    public function rules()
    {
        return [
            [['cat_style'], 'required'],
            [['cat_style', 'cat_goods_count', 'cat_goods_cols', 'recommend_count'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'cat_style' => '分类样式',
            'cat_goods_count' => '推荐商品显示数量',
            'cat_goods_cols' => '每个分类商品显示总数',
            'recommend_count' => '商品每行显示数量',
        ];
    }

    public function search()
    {
        $mchId = \Yii::$app->user->identity->mch_id;
        $option = CommonAppConfig::getAppCatStyle($mchId);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'setting' => $option
            ],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $num = 100;
            if ($this->attributes['cat_goods_count'] > $num) {
                throw new \Exception('分类商品显示总数为' . $num . '个');
            }
            $option = CommonOption::set(
                Option::NAME_CAT_STYLE_SETTING,
                $this->attributes,
                \Yii::$app->mall->id,
                Option::GROUP_APP,
                \Yii::$app->user->identity->mch_id
            );

            if (!$option) {
                throw new \Exception($this->getErrorMsg('保存失败'));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功。',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                ]
            ];
        }
    }
}
