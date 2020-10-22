<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/3/11
 * Time: 14:20
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\ecard\forms\mall;


use app\forms\common\ecard\CheckGoods;
use app\models\EcardData;
use app\forms\common\ecard\CommonEcard;
use app\plugins\ecard\forms\Model;
use app\models\Ecard;
use app\models\EcardOptions;
use yii\helpers\Json;

/**
 * Class ListEditForm
 * @package app\plugins\ecard\forms\mall
 * @property CommonEcard $commonEcard
 */
class ListEditForm extends Model
{
    public $ecard_id;
    public $list; // [{"key1":"123","key2":"312"...}]
    public $ecard_data_list; // [{"key":"222"...}]
    public $token;
    private $commonEcard;

    public function init()
    {
        parent::init();
        $this->commonEcard = CommonEcard::getCommon();
    }

    public function rules()
    {
        return [
            [['ecard_id'], 'integer'],
            [['list', 'ecard_data_list'], 'safe'],
            [['token'], 'trim'],
            [['token'], 'string'],
        ];
    }

    public function batch($filter = false)
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $transaction = \Yii::$app->db->beginTransaction();
        $list = Json::decode($this->list, true);
        try {
            if (!$list || empty($list)) {
                throw new \Exception('新建卡密数据不能为空');
            }
            $ecard = $this->commonEcard->getEcard($this->ecard_id);
            $ecardList = Json::decode($ecard['list'], true);
            $tokenList = [];
            $error = [];
            $newList = [];
            // 获取token
            foreach ($list as &$item) {
                // 处理成新的数据
                $newItem = [];
                $count = array_reduce(array_keys($item), function ($v1, $v) {
                    if (strpos($v, 'key') !== false) {
                        $v1++;
                    }
                    return $v1;
                }, 0);
                if ($count != count($ecardList)) {
                    throw new \Exception('数据格式不匹配，请检查重试！');
                }
                for ($i = 1; $i <= count($ecardList); $i++) {
                    if (!isset($item['key' . $i])) {
                        throw new \Exception('卡密数据字段数量不对');
                    }
                    if (trim($item['key' . $i]) === '' || trim($item['key' . $i]) === null) {
                        throw new \Exception('卡密数据不能为空');
                    }
                    $newItem[] = [
                        'key' => $ecardList[$i - 1],
                        'value' => trim($item['key' . $i])
                    ];
                }
                // 根据卡密数据生成唯一的token
                $token = $this->getToken($newItem, $ecard);
                $item['token'] = $token;
                if (in_array($token, $tokenList)) {
                    $error[] = $token;
                    continue;
                }
                $tokenList[] = $token;
                $newList[$token] = $newItem;
            }
            unset($item);
            // 获取重复的token
            $error = array_merge($error, $this->checkToken($ecard, $tokenList));
            $url = '';
            if (!empty($error)) {
                // 获取重复token的卡密数据
                $errorList = [];
                // 标记提交数据中重复的数据
                foreach ($list as &$item) {
                    if (in_array($item['token'], $error)) {
                        $item['is_repeat'] = true;
                        $errorList[] = $newList[$item['token']];
                    } else {
                        $item['is_repeat'] = false;
                    }
                }
                unset($item);
                if ($filter) {
                    // 过滤掉重复token的卡密数据
                    foreach ($error as $item) {
                        if (isset($newList[$item])) {
                            unset($newList[$item]);
                        }
                    }
                    // 重复的卡密数据保存到文件中
                    $model = new ExportForm();
                    $model->ecard = $ecard;
                    $model->ecardOptions = array_values($errorList);
                    $url = $model->exportError();
                } else {
                    throw new \Exception('卡密数据列表存在重复的数据');
                }
            }
            $data = [];
            $model = new EcardOptions();
            $modelData = new EcardData();
            $dataList = [];
            foreach ($newList as $token => $newItem) {
                // 拼接需要保存的卡密数据
                $model->attributes = [
                    'mall_id' => \Yii::$app->mall->id,
                    'ecard_id' => $ecard['id'],
                    'value' => Json::encode($newItem, JSON_UNESCAPED_UNICODE),
                    'token' => $token,
                    'is_delete' => 0,
                    'is_sales' => 0,
                    'is_occupy' => 0,
                ];
                $model->validate();
                // 拼接需要保存的卡密数据
                $data[] = $model->attributes;
                foreach ($newItem as $index => $value) {
                    $modelData->attributes = [
                        'mall_id' => \Yii::$app->mall->id,
                        'ecard_id' => $ecard['id'],
                        'key' => $ecardList[$index],
                        'value' => $value['value'],
                        'token' => $token,
                        'is_delete' => 0,
                    ];
                    $modelData->validate();
                    $dataList[] = $modelData->attributes;
                }
            }
            // 保存卡密数据
            \Yii::$app->db->createCommand()->batchInsert(
                EcardOptions::tableName(),
                array_keys($model->attributes),
                $data
            )->execute();
            \Yii::$app->db->createCommand()->batchInsert(
                EcardData::tableName(),
                array_keys($modelData->attributes),
                $dataList
            )->execute();
            // 修改卡密库存
            $this->commonEcard->updateStock($ecard);
            $transaction->commit();
            $this->commonEcard->log(new CheckGoods([
                'ecard' => $ecard,
                'status' => CheckGoods::STATUS_ADD,
                'sign' => 'ecard',
                'number' => count($data),
            ]));
            return $this->success(['msg' => '保存成功', 'data' => [
                'success' => count($data),
                'fail' => count($list) - count($data),
                'url' => $url
            ]]);
        } catch (\Exception $exception) {
            $transaction->rollBack();
            return $this->fail(['msg' => $exception->getMessage(), 'data' => $list]);
        }
    }

    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $token = Json::decode($this->token, true);
            $token = array_column($token, 'token');
            $ecardOption = $this->commonEcard->getEcardDataAll($token, $this->ecard_id);
            if (empty($ecardOption)) {
                throw new \Exception('卡密数据不存在或已删除');
            }
            foreach ($ecardOption as $item) {
                if ($item->is_occupy == 1) {
                    throw new \Exception('存在被占用的卡密数据，不能执行此次删除操作');
                }
            }
            $count = EcardOptions::updateAll(['is_delete' => 1], [
                'token' => $token,
                'ecard_id' => $this->ecard_id
            ]);
            if ($count <= 0) {
                throw new \Exception('卡密数据删除失败');
            }
            EcardData::updateAll(['is_delete' => 1], [
                'token' => $token,
                'ecard_id' => $this->ecard_id
            ]);
            $ecard = $this->commonEcard->getEcard($this->ecard_id);
            $ecard = $this->commonEcard->updateStock($ecard);
            $transaction->commit();
            $this->commonEcard->log(new CheckGoods([
                'ecard' => $ecard,
                'status' => CheckGoods::STATUS_DELETE,
                'sign' => 'ecard',
                'number' => $count,
            ]));
            return $this->success(['msg' => '删除成功']);
        } catch (\Exception $exception) {
            $transaction->rollBack();
            return $this->fail(['msg' => $exception->getMessage()]);
        }
    }

    public function editData()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $ecardOption = $this->commonEcard->getEcardData($this->token, $this->ecard_id);
            if (empty($ecardOption)) {
                throw new \Exception('卡密数据不存在或已删除');
            }
            if ($ecardOption->is_sales == 1) {
                throw new \Exception('已经出售的卡密数据不能修改');
            }
            $ecardData = Json::decode($this->ecard_data_list, true);
            if (empty($ecardData)) {
                throw new \Exception('编辑的数据不正确');
            }
            $ecardData = array_column($ecardData, 'key');
            if (empty($ecardData)) {
                throw new \Exception('编辑的数据不正确');
            }
            $ecard = $this->commonEcard->getEcard($this->ecard_id);
            $ecardList = Json::decode($ecard->list, true);
            $newItem = [];
            for ($i = 0; $i < count($ecardList); $i++) {
                if (!isset($ecardData[$i])) {
                    throw new \Exception('卡密数据字段数量不对');
                }
                if (trim($ecardData[$i]) === '' || trim($ecardData[$i]) === null) {
                    throw new \Exception('卡密数据不能为空');
                }
                $newItem[] = [
                    'key' => $ecardList[$i],
                    'value' => trim($ecardData[$i])
                ];
            }
            $token = $this->getToken($newItem, $ecard);
            $ignore = [$ecardOption->id];
            $error = $this->checkToken($ecard, [$token], $ignore);
            if (!empty($error)) {
                throw new \Exception('卡密数据列表存在重复数据');
            }
            $ecardOption->token = $token;
            $ecardOption->value = Json::encode($newItem, JSON_UNESCAPED_UNICODE);
            if (!$ecardOption->save()) {
                throw new \Exception($this->getErrorMsg($ecardOption));
            }
            foreach ($ecardOption->data as $index => $value) {
                $value->token = $token;
                $value->value = $ecardData[$index];
                if (!$value->save()) {
                    throw new \Exception($this->getErrorMsg($value));
                }
            }
            $transaction->commit();
            return $this->success(['msg' => '编辑成功']);
        } catch (\Exception $exception) {
            $transaction->rollBack();
            return $this->fail(['msg' => $exception->getMessage()]);
        }
    }

    /**
     * @param array $item
     * @param Ecard $ecard
     * @return string
     * @throws \Exception
     * 获取卡密数据的token
     */
    private function getToken($item, $ecard)
    {
        $tokenData = 'mall_id:' . \Yii::$app->mall->id . '--ecard_id:' . $ecard->id . '--';
        $tokenData = array_reduce($item, function ($v1, $v) {
            $v1 = array_reduce($v, function ($v1, $v) {
                $v1 .= $v . ':';
                return $v1;
            }, $v1);
            return $v1;
        }, $tokenData);
        $token = sha256($tokenData);
        return $token;
    }

    /**
     * @param Ecard $ecard
     * @param array $tokenList
     * @param array $ignore // 忽略判断的id
     * @return array
     * @throws \Exception
     * 校验token是否重复
     */
    private function checkToken($ecard, $tokenList, $ignore = [])
    {
        $error = [];
        if ($ecard->is_unique == 1) {
            $error = array_diff_assoc($tokenList, array_unique($tokenList));
            $exist = EcardOptions::find()
                ->where(['mall_id' => \Yii::$app->mall->id, 'token' => $tokenList])
                ->keyword(!empty($ignore), ['not in', 'id', $ignore])
                ->andWhere([
                    'or',
                    ['is_sales' => 1],
                    ['is_sales' => 0, 'is_delete' => 0]
                ])->select('token')
                ->column();
            $error = array_unique(array_merge($error, $exist));
        }
        return $error;
    }
}
