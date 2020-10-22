<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\mch\forms\common\CommonCat;
use app\plugins\mch\forms\common\CommonMchForm;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchCommonCat;

class CommonCatForm extends Model
{
    public $id;
    public $page;
    public $keyword;

    public function rules()
    {
        return [
            [['id', 'page'], 'integer'],
            [['keyword',], 'string'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $common = new CommonCat();
            $common->keyword = $this->keyword;
            $common->page = $this->page;
            $res = $common->getList();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $res['list'],
                    'pagination' => $res['pagination']
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $detail = MchCommonCat::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
            'is_delete' => 0
        ])->asArray()->one();

        if (!$detail) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '类目不存在'
            ];
        }

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
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            /** @var MchCommonCat $detail */
            $detail = MchCommonCat::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->id,
            ])->one();

            if (!$detail) {
                throw new \Exception('类目不存在');
            }

            $count = Mch::find()->where([
                'mch_common_cat_id' => $detail->id,
                'is_delete' => 0
            ])->count();
            if ($count) {
                throw new \Exception('有商户正在使用该类目,无法删除');
            }

            $detail->is_delete = 1;
            $res = $detail->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($detail));
            }

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function switchStatus()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $detail = MchCommonCat::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
            'is_delete' => 0
        ])->one();

        if (!$detail) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '类目不存在'
            ];
        }

        $detail->status = $detail->status ? 0 : 1;
        $res = $detail->save();
        if (!$res) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '更新失败'
            ];
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '更新成功',
        ];
    }

    public function allList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $common = new CommonCat();
            $res = $common->getAllList();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $res,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}
