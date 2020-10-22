<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/10/10
 * Time: 14:33
 */

namespace app\plugins\advance\forms\mall;


use app\core\response\ApiCode;
use app\models\Mall;
use app\models\Model;
use app\plugins\advance\models\AdvanceBanner;
use yii\db\Exception;

/**
 * @property Mall $mall
 */
class BannerForm extends Model
{
    public $mall;
    public $ids;

    public function rules()
    {
        return [
            [['ids'], 'safe'],
            [['ids'], 'default', "value" => []]
        ];
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
            AdvanceBanner::updateAll(['is_delete' => 1, 'deleted_at' => mysql_timestamp()], ['is_delete' => 0, 'mall_id' => $this->mall->id]);
            // 循环添加新的数据
            foreach ($this->ids as $item) {
                $form = new AdvanceBanner();
                $form->banner_id = $item;
                $form->mall_id = $this->mall->id;
                $form->is_delete = 0;
                $form->created_at = mysql_timestamp();
                if (!$form->save()) {
                    throw new Exception($this->getErrorMsg($form));
                }
            }
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}

