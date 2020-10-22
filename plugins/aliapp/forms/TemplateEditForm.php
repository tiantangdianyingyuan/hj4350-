<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/20
 * Time: 10:20
 */

namespace app\plugins\aliapp\forms;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\aliapp\models\AliappTemplate;

class TemplateEditForm extends Model
{
    public $data;

    public function save()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (!$this->data || !is_array($this->data)) {
                throw new \Exception('数据异常');
            }
            $newData = [];
            foreach ($this->data as $item) {
                foreach ($item['list'] as $item2) {
                    if (!isset($item2[$item2['tpl_name']])) {
                        throw new \Exception('默认数据有误、请排查<' . $item2['name'] . '>字段信息');
                    }
                    $newData[$item2['tpl_name']] = $item2[$item2['tpl_name']];
                }
            }

            foreach ($newData as $k => $item) {
                $tpl = AliappTemplate::find()->where(['mall_id' => \Yii::$app->mall->id, 'tpl_name' => $k])->one();

                if ($tpl) {
                    $tpl->tpl_id = $item;
                    $res = $tpl->save();

                    if (!$res) {
                        throw new \Exception('保存失败x01');
                    }
                } else {
                    $tpl = new AliappTemplate();
                    $tpl->mall_id = \Yii::$app->mall->id;
                    $tpl->tpl_name = $k;
                    $tpl->tpl_id = $item;
                    $res = $tpl->save();

                    if (!$res) {
                        throw new \Exception('保存失败x02');
                    }
                }
            }

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
