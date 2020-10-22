<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/11
 * Time: 10:37
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\data_importing;


use app\helpers\CurlHelper;
use app\models\Model;

/**
 * Class V3DataImporting
 * @package app\forms\common\data_importing
 * @pro
 */
class V3DataImporting extends Model
{
    public $v3Data;
    public $mall;

    public $code;
    protected $store_id;
    protected $type;
    protected $page;
    protected $api;
    protected $token;

    /**
     * v3商城数据导入
     * @throws \Exception
     * @return bool
     */
    public function import()
    {
        try {
            $array = json_decode(base64_decode($this->code), true);
            if (!isset($array['api'])) {
                throw new \Exception('错误的商城数据导入码，请从v3商城中获取x01');
            }
            $this->api = $array['api'];
            if (!isset($array['params'])) {
                throw new \Exception('错误的商城数据导入码，请从v3商城中获取x02');
            }
            if (!isset($array['params']['store_id'])) {
                throw new \Exception('错误的商城数据导入码，请从v3商城中获取x03');
            }
            $this->store_id = $array['params']['store_id'];
            if (!isset($array['params']['token'])) {
                throw new \Exception('错误的商城数据导入码，请从v3商城中获取x04');
            }
            $this->token = $array['params']['token'];
            $chunkKey = 'import_mall_' . $this->mall->id;
            $chunk = \Yii::$app->cache->get($chunkKey);
            if ($chunk) {
                $res = json_decode($chunk, true);
                $this->type = $res['type'];
                $this->page = $res['page'];
                \Yii::$app->cache->delete($chunkKey);
                UserImporting::$isCheck = true;
            }
            while (true) {
                $result = json_encode([
                    'type' => $this->type,
                    'page' => $this->page
                ], JSON_UNESCAPED_UNICODE);
                \Yii::$app->cache->set($chunkKey, $result);
                $data = $this->getData();
                if (count($data['result']) <= 0) {
                    break;
                }
                $this->v3Data = $data['result'];
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $this->handle();
                    $transaction->commit();
                    if (UserImporting::$userInfo && count(UserImporting::$userInfo)) {
                        $userImporting = new UserImporting();
                        $userImporting->saveParentId();
                    }
                    if (!$data['type']) {
                        break;
                    }
                    $this->page = $data['page'];
                    $this->type = $data['type'];
                } catch (\Exception $exception) {
                    $transaction->rollBack();
                    throw $exception;
                    break;
                }
            }
            \Yii::$app->cache->delete($chunkKey);
            return true;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    protected function getData()
    {
        $result = CurlHelper::getInstance()->httpPost($this->api, [], [
            'store_id' => $this->store_id,
            'type' => $this->type,
            'page' => $this->page,
            'token' => $this->token
        ]);
        if ($result['code'] == 1) {
            throw new \Exception($result['msg']);
        }
        return $result['data'];
    }

    /**
     * @throws \Exception
     */
    protected function handle()
    {
        foreach ($this->v3Data as $index => $item) {
            $class = '\\app\\forms\\common\\data_importing\\' . hump($index) . 'Importing';
            if (!class_exists($class)) {
                \Yii::error('|--目前暂不支持的导入：' . $class);
                continue;
            }
            /* @var BaseImporting $object */
            $object = new $class();
            $object->v3Data = $item;
            $object->mall = $this->mall;
            $object->import();
        }
    }

    protected function saveAsFile()
    {
        $path = \Yii::$app->basePath . '/web/temp/' . date('YmdHis') . rand(1, 99) . '.json';
        $handle = fopen($path, 'w+');
        fwrite($handle, json_encode($this->v3Data, JSON_UNESCAPED_UNICODE));
        fclose($handle);
    }
}
