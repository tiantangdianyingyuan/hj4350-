<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\integral_mall\forms\mall;

use app\core\response\ApiCode;
use app\plugins\integral_mall\forms\common\BannerListForm;
use app\models\Model;
use app\plugins\integral_mall\models\IntegralMallBanners;

class BannerForm extends Model
{
    public $ids;

    public function rules()
    {
        return [
            [['ids'], 'safe'],
            [['ids'], 'default', "value" => []]
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $list = (new BannerListForm())->search();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
            ]
        ];
    }

    //SAVE
    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        $t = \Yii::$app->db->beginTransaction();

        IntegralMallBanners::updateAll(['is_delete' => 1, 'deleted_at' => date('Y-m-d H:i:s')], [
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
        ]);

        foreach ($this->ids as $id) {
            $form = new IntegralMallBanners();
            $form->banner_id = $id;
            $form->mall_id = \Yii::$app->mall->id;
            $form->is_delete = 0;
            if (!$form->save()) {
                $t->rollBack();
                return $this->getErrorResponse($form);
            };
        };
        $t->commit();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功',
        ];
    }
}
