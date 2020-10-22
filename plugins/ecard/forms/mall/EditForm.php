<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/3/10
 * Time: 16:59
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\ecard\forms\mall;


use app\forms\common\ecard\CommonEcard;
use app\models\GoodsWarehouse;
use app\plugins\ecard\forms\Model;
use app\models\Ecard;
use yii\helpers\Json;

class EditForm extends Model
{
    public $id;
    public $name;
    public $content;
    public $list;
    public $is_unique;

    public function rules()
    {
        return [
            [['id', 'is_unique'], 'integer'],
            [['name'], 'trim'],
            [['name'], 'string'],
            [['content', 'list'], 'safe'],
            ['content', 'default', 'value' => ''],
            ['is_unique', 'default', 'value' => 1]
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $exists = Ecard::find()
                ->where(['name' => $this->name, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
                ->keyword($this->id, ['!=', 'id', $this->id])
                ->exists();
            if ($exists) {
                throw new \Exception('卡密名称已存在，请勿重复添加名称');
            }
            if ($this->id) {
                $model = Ecard::findOne(['id' => $this->id, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
                if (!$model) {
                    throw new \Exception('错误的电子卡密');
                }
            } else {
                $model = new Ecard();
                $model->is_delete = 0;
                $model->mall_id = \Yii::$app->mall->id;
                $list = Json::decode($this->list, true);
                $newList = [];
                $error = [];
                foreach ($list as $index => $item) {
                    if (!isset($item['key']) || $item['key'] === '' || $item['key'] === null) {
                        throw new \Exception('字段内容不能为空');
                    }
                    $trim = trim($item['key']);
                    if (in_array($trim, $newList)) {
                        $error[] = $trim;
                    }
                    $newList[] = $trim;
                }
                if (!empty($error)) {
                    $list = array_map(function ($item) use ($error) {
                        if (in_array(trim($item['key']), $error)) {
                            $item['is_repeat'] = true;
                        } else {
                            $item['is_repeat'] = false;
                        }
                        return $item;
                    }, $list);
                    return $this->fail(['msg' => '卡密字段存在重复的', 'data' => $list]);
                }
                $model->list = Json::encode($newList, JSON_UNESCAPED_UNICODE);
                $model->is_unique = $this->is_unique;
            }
            $model->name = $this->name;
            $model->content = $this->content;
            if (!$model->save()) {
                throw new \Exception($this->getErrorMsg($model));
            }
            return $this->success(['msg' => '保存成功']);
        } catch (\Exception $exception) {
            return $this->fail(['msg' => $exception->getMessage()]);
        }
    }

    public function getOne()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            if (!$this->id) {
                throw new \Exception('无效的请求');
            }
            $common = CommonEcard::getCommon();
            $ecard = $common->getEcard($this->id);
            return $this->success($common->getEcardArray($ecard));
        } catch (\Exception $exception) {
            return $this->fail(['msg' => $exception->getMessage()]);
        }
    }

    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            if (!$this->id) {
                throw new \Exception('无效的请求');
            }
            $ecard = CommonEcard::getCommon()->getEcard($this->id);
            $model = GoodsWarehouse::findAll(['ecard_id' => $ecard->id, 'is_delete' => 0]);
            if (!empty($model)) {
                throw new \Exception('卡密数据正在售卖中，不允许删除此卡密');
            }
            $ecard->is_delete = 1;
            if (!$ecard->save()) {
                throw new \Exception($this->getErrorMsg($ecard));
            }
            return $this->success(['msg' => '删除成功']);
        } catch (\Exception $exception) {
            return $this->fail(['msg' => $exception->getMessage()]);
        }
    }
}
