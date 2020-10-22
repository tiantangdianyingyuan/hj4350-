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
use app\plugins\stock\models\StockSetting;
use app\plugins\stock\models\StockUser;
use Yii;

class LevelForm extends Model
{
    public $id;
    public $type;
    public $remark;
    public $page;

    //等级相关
    public $name;
    public $bonus_rate;
    public $condition;//升级条件


    public function rules()
    {
        return [
            [['id', 'type', 'page'], 'integer'],
            [['bonus_rate'], 'number', 'max' => 99999999.99],
            [['condition'], 'integer', 'max' => 999999999],
            [['remark', 'name'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'bonus_rate' => '分红比例',
            'remark' => '备注',
            'name' => '姓名',
            'condition' => '升级条件',
        ];
    }

    public function search()
    {

        $list = StockLevel::find()->where(['is_delete' => 0, 'mall_id' => Yii::$app->mall->id])
            ->orderBy('is_default desc,bonus_rate,created_at')
            ->page($pagination)
            ->asArray()
            ->all();
        $up_info = StockLevelUp::findOne(['mall_id' => Yii::$app->mall->id]);
        if (empty($up_info)) {
            throw new \Exception('股东分红升级条件未设置');
        }
        if (($up_info->type == 1 || $up_info->type == 4)) {
            foreach ($list as &$item) {
                $item['condition'] = (int)$item['condition'];
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'up_type' => $up_info->type,
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $t = Yii::$app->db->beginTransaction();
        try {
            $up_info = StockLevelUp::findOne(['mall_id' => Yii::$app->mall->id]);
            if (empty($up_info)) {
                throw new \Exception('股东分红升级条件未设置');
            }
            if ($this->bonus_rate <= 0) {
                throw new \Exception('分红比例必须大于0');
            }
            if ($this->condition < 0) {
                throw new \Exception('请填写正确的升级条件');
            }
            //排除同名等级
            $list = StockLevel::find()->where(['mall_id' => Yii::$app->mall->id, 'is_delete' => 0, 'name' => $this->name])->all();
            foreach ($list as $item) {
                if ($item->id != $this->id) {
                    throw new \Exception('等级名称已存在，请修改');
                }
            }

            if ($this->id) {
                $model = StockLevel::findOne(['id' => $this->id, 'mall_id' => Yii::$app->mall->id, 'is_delete' => 0]);
                if ($model->is_default == 1) {
                    //默认等级修改分红比例，需要改设置中的默认等级分红比例
                    if (!StockSetting::set(Yii::$app->mall->id, 'base_rate', sprintf("%.2f", $this->bonus_rate))) {
                        throw new \Exception('基本设置更新失败');
                    }
                }
            } else {
                $model = new StockLevel();
                $model->mall_id = Yii::$app->mall->id;
            }
            $model->name = $this->name;
            $model->bonus_rate = $this->bonus_rate;
            $model->condition = $this->condition;
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

    public function del()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $t = Yii::$app->db->beginTransaction();
        try {
            if (!$this->id) {
                throw new \Exception('ID不能为空');
            }

            $model = StockLevel::findOne(['id' => $this->id, 'mall_id' => Yii::$app->mall->id, 'is_delete' => 0]);
            if (empty($model)) {
                throw new \Exception('等级ID错误');
            }
            if ($model->is_default == 1) {
                throw new \Exception('默认等级不能删除');
            }
            $model->is_delete = 1;
            if (!$model->save()) {
                throw new \Exception('删除失败');
            }
            $default_model = StockLevel::findOne(['is_default' => 1, 'mall_id' => Yii::$app->mall->id, 'is_delete' => 0]);
            if (empty($default_model)) {
                throw new \Exception('找不到默认等级');
            }
            //关联等级用户，等级降为默认等级
            StockUser::updateAll(['level_id' => $default_model->id], ['level_id' => $this->id]);

            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功'
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }


}