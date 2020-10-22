<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020-02-19
 * Time: 09:11
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\composition\forms\mall;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\composition\models\Composition;

class UpdateForm extends Model
{
    public $prop;
    public $prop_value;
    public $id;
    public $ids;

    public function rules()
    {
        return [
            [['prop', 'prop_value'], 'required'],
            [['prop'], 'trim'],
            [['prop'], 'string'],
            [['id'], 'integer'],
            [['prop_value'], 'safe'],
            [['prop'], 'in', 'range' => ['sort', 'is_delete', 'status'], 'message' => '目前支持修改排序、上下架和删除操作'],
            [['ids'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'prop' => '需要修改的字段',
            'prop_value' => '修改后的值',
            'id' => '需要修改的id'
        ];
    }

    public function update()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            /* @var Composition $composition */
            $composition = Composition::find()->with('compositionGoods.goods')
                ->where(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id, 'is_delete' => 0])
                ->one();
            if (!$composition) {
                throw new \Exception('套餐不存在或者已经删除，请刷新后重试');
            }
            if ($this->prop == 'status' && $this->prop_value == 1) {
                foreach ($composition->compositionGoods as $compositionGoods) {
                    if ($compositionGoods->goods->is_delete != 0) {
                        throw new \Exception('该套餐中有商品被删除，无法上架，请先到套餐中删除商品');
                    }
                }
            }
            $prop = $this->prop;
            $composition->$prop = $this->prop_value;
            if (!$composition->save()) {
                throw new \Exception($this->getErrorMsg($composition));
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '修改成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function batch()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $ids = $this->ids;
            $can = [];
            if ($this->prop == 'status') {
                $ignore = [];
                /* @var Composition[] $compositionList */
                $compositionList = Composition::find()->with('compositionGoods.goods')
                    ->where(['mall_id' => \Yii::$app->mall->id, 'id' => $ids, 'is_delete' => 0])
                    ->all();
                foreach ($compositionList as $composition) {
                    if ($composition->status == $this->prop_value) {
                        $can[] = $composition->id;
                    }
                    if ($this->prop_value == 1) {
                        foreach ($composition->compositionGoods as $compositionGoods) {
                            if ($compositionGoods->goods->is_delete != 0) {
                                $ignore[] = $composition->id;
                                break;
                            }
                        }
                    }
                }
                $ids = array_diff($ids, $ignore);
            }
            $count = Composition::updateAll([$this->prop => $this->prop_value], ['id' => $ids, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
            $actualCount = $count + count($can);
            $idsCount = count($this->ids);
            if ($idsCount == $actualCount) {
                $msg = '操作成功';
            } else {
                if ($idsCount == 1 && $actualCount == 0) {
                    throw new \Exception('操作失败，套餐存在异常，无法上架');
                } else {
                    $msg = '操作成功，部分套餐存在异常，无法上架';
                }
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $msg
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
