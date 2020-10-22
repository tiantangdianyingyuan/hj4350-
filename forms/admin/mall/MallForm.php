<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\admin\mall;


use app\core\response\ApiCode;
use app\models\Mall;
use app\models\Model;

class MallForm extends Model
{
    public $id;

    public function rules()
    {
        return [
            [['id'], 'required']
        ];
    }

    protected function getMall()
    {
        if (!$this->validate()) {
            throw new \Exception($this->getErrorMsg($this));
        }
        if (\Yii::$app->user->identity->identity->is_super_admin == 1) {
            $mall = Mall::findOne([
                'id' => $this->id,
                'is_delete' => 0,
            ]);
        } else {
            $mall = Mall::findOne([
                'id' => $this->id,
                'is_delete' => 0,
                'user_id' => \Yii::$app->user->identity->id,
            ]);
        }
        if (!$mall) {
            throw new \Exception('商城不存在。');
        }
        return $mall;
    }

    public function disable()
    {
        try {
            $mall = $this->getMall();
            $mall->is_disable = $mall->is_disable ? 0 : 1;
            if (!$mall->save()) {
                throw new \Exception($this->getErrorMsg($mall));
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功',
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

    /**
     * 回收站删除
     * @return array
     */
    public function delete()
    {
        try {
            $mall = $this->getMall();
            $mall->is_delete = 1;
            if (!$mall->save()) {
                throw new \Exception($this->getErrorMsg($mall));
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功',
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
