<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\order;

use app\core\response\ApiCode;
use app\forms\common\order\send\ExpressSendForm;
use app\models\Express;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderDetailExpressRelation;
use GuzzleHttp\Client;
use yii\web\UploadedFile;

class OrderSendForm extends Model
{
    public $order_id;
    public $is_express;
    public $express;
    public $express_no;
    public $words;
    public $arrCSV;
    public $file_name;//文件名称无用

    public function rules()
    {
        return [
            [['order_id', 'is_express'], 'required'],
            [['order_id', 'is_express'], 'integer'],
            [['words', 'express_no', 'express'], 'string'],
            [['words', 'express', 'express_no'], 'default', 'value' => ''],
            [['arrCSV'], 'trim'],
        ];
    }

    public function batchDetail()
    {
        $express_list = Express::getExpressList();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'express_list' => $express_list
            ],
        ];
    }

    public function up($url, $file_type = '.xlsx')
    {
        $path = \Yii::$app->basePath . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . 'temp';
        is_dir($path) || mkdir($path);
        $save_to = $path . DIRECTORY_SEPARATOR . md5($url) . $file_type;
        $client = new Client(['verify' => false]);
        $response = $client->get($url, ['save_to' => $save_to]);
        if ($response->getStatusCode() == 200) {
            return $save_to;
        } else {
            throw new \Exception('上传本地失败');
        }
    }

    public function getExcel($file)
    {
        $PHPExcel = \PHPExcel_IOFactory::load($file);
        $sheet = $PHPExcel->getSheet(0); // 读取第一個工作表
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        $highestColumm = $sheet->getHighestColumn(); // 取得总列数

        // 把Excel数据保存数组中
        $data = array();
        for ($rowIndex = 1; $rowIndex <= $highestRow; $rowIndex++) {
            for ($colIndex = 'A'; $colIndex <= $highestColumm; $colIndex++) {
                $addr = $colIndex . $rowIndex;
                $cell = $sheet->getCell($addr)->getFormattedValue();
                if ($cell instanceof PHPExcel_RichText) {
                    $cell = $cell->__toString();
                }
                $data[$rowIndex][] = trim($cell);
            }
        }
        array_shift($data);
        return $data;
    }


    protected function getCSV($file)
    {
        $arrCSV = array();
        if (($handle = fopen($file, "r")) !== false) {
            $key = 0;
            while (($data = fgetcsv($handle, 0, ",")) !== false) {
                $c = count($data);
                for (
                    $x = 0; $x < $c;
                    $x++
                ) {
                    $arrCSV[$key][$x] = trim($data[$x]);
                }
                $key++;
            }
            fclose($handle);
        }
        array_shift($arrCSV);
        return $arrCSV;
    }

    public function saveLocal()
    {
        $file = UploadedFile::getInstanceByName('file');
        if (empty($file)) {
            throw new \Exception('请上传文件');
        }
        if (!in_array($file->getExtension(), ['xls', 'xlsx', 'csv', 'pdf'])) {
            throw new \Exception('文件不合法');
        }
        $fileName = md5_file($file->tempName) . '.' . $file->getExtension();
        $savePath = \Yii::$app->basePath . '/web/temp/';
        if (!is_dir($savePath)) {
            if (!make_dir($savePath)) {
                throw new \Exception('temp 目录创建失败');
            }
        }
        $fileUrl = $savePath . $fileName;
        if (!$file->saveAs($fileUrl)) {
            if (!copy($file->tempName, $fileUrl)) {
                throw new \Exception('temp 文件保存失败');
            }
        }
        return $fileUrl;
    }

    public function batchSave()
    {
        try {
            $file = $this->saveLocal();
            $this->validateExpress();
            if (!$file) {
                throw new \Exception('模板不能为空');
            }
            if (pathinfo($file, PATHINFO_EXTENSION) === 'csv') {
                $data = $this->getCSV($file);
            } else {
                $data = $this->getExcel($file);
            }
            $info = $this->batch($data);
            @unlink($file);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功',
                'data' => [
                    'list' => $info
                ],
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    private function batch($arrCSV)
    {
        $empty = [];  //是否存在
        $error = [];   //操作失败
        $cancel = [];  //是否取消
        $offline = []; //到店自提
        $send = [];  //是否发货
        $success = []; //是否成功
        $pay = []; //未支付(已发货)

        foreach ($arrCSV as $v) {
            /** @var Order $order */
            $order = Order::find()->where([
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'order_no' => $v[1],
                'mch_id' => \Yii::$app->user->identity->mch_id,
            ])->with('detail')->one();
            if (!$order) {
                $empty[] = $v[1];
                continue;
            }

            if ($order->status == 0) {
                continue;
            }

            if ($order->cancel_status != 0) {
                $cancel[] = $v[1];
                continue;
            }
            if ($order->is_send) {
                $send[] = $v[1];
                continue;
            }
            if ($order->send_type == 1) {
                $offline[] = $v[1];
                continue;
            }
            if ($order->is_pay == 0 && $order->pay_type != 2) {
                $pay[] = $v[1];
            }

            if ($order->send_type == 1) {
                $order->send_type = 0;
            }

            $detailIds = [];
            /** @var OrderDetail $detail */
            $t = \Yii::$app->db->beginTransaction();
            try {
                foreach ($order->detail as $detail) {
                    $relation = OrderDetailExpressRelation::find()->where([
                        'order_detail_id' => $detail->id,
                        'is_delete' => 0,
                        'mall_id' => \Yii::$app->mall->id
                    ])->one();
                    if (!$relation) {
                        $detailIds[] = $detail->id;
                    }
                }

                $res = $order->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($order));
                }

                $orderSendForm = new ExpressSendForm();
                $orderSendForm->order_id = $order->id;
                $orderSendForm->order_detail_id = $detailIds;
                $orderSendForm->express = $this->express;
                $orderSendForm->express_no = $v[2];
                $orderSendForm->merchant_remark = '';
                $orderSendForm->send();
                $success[] = $v[1];
                $t->commit();
            } catch (\Exception $exception) {
                $error[] = $v[1];
                \Yii::error('批量发货错误');
                \Yii::error($exception);
                $t->rollBack();
            }
        };
        $data = [];
        $max = max(count($empty), count($error), count($cancel), count($send), count($offline), count($pay), count($success));
        for (
            $i = 0, $k = 0; $i < $max;
            $k++, $i++
        ) {
            $data[$k]['empty'] = $empty[$k] ?? '';
            $data[$k]['cancel'] = $cancel[$k] ?? '';
            $data[$k]['send'] = $send[$k] ?? '';
            $data[$k]['offline'] = $offline[$k] ?? '';
            $data[$k]['pay'] = $pay[$k] ?? '';
            $data[$k]['error'] = $error[$k] ?? '';
            $data[$k]['success'] = $success[$k] ?? '';
        }
        return $data;
    }

    private function validateExpress()
    {
        $expressList = Express::getExpressList();
        $sentinel = false;
        foreach ($expressList as $value) {
            if ($value['name'] == $this->express) {
                $sentinel = true;
                break;
            }
        }
        if (!$sentinel && $this->is_express) {
            throw new \Exception('快递公司错误');
        }
    }
}
