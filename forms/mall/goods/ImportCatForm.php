<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\goods;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\models\GoodsWarehouse;
use app\models\ImportData;
use app\models\Model;
use app\models\ModelActiveRecord;
use app\models\Option;
use app\plugins\mch\models\MchGoods;

class ImportCatForm extends Model
{
    public $is_show;
    public $status;
    public $file;
    public $current_num;
    public $file_path;
    public $import_data_id;


    public function rules()
    {
        return [
            [['is_show', 'status', 'current_num'], 'required'],
            [['file'], 'file', 'extensions' => ['csv']],
            [['file_path'], 'string'],
            [['import_data_id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return array_merge(parent::rules(), [
            'is_show' => '是否显示',
            'status' => "启用状态",
            'file' => "csv文件",
        ]);
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        // 关闭日志存储
        ModelActiveRecord::$log = false;
        try {
            if ($this->current_num <= 1) {
                if (empty($_FILES) || !isset($_FILES['file'])) {
                    return [
                        'code' => 1,
                        'msg' => '请上传csv文件'
                    ];
                }

                $fileName = $_FILES['file']['name'];
                $tmpName = $_FILES['file']['tmp_name'];
                $path = \Yii::$app->basePath . '/web/temp/csv/';
                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                if ($ext != 'csv') {
                    return [
                        'code' => 1,
                        'msg' => '请上传csv文件'
                    ];
                }
                $file = '分类列表' . '.' . $ext;
                $uploadFile = $path . $file;
                $result = move_uploaded_file($tmpName, $uploadFile);
                $importData = new ImportData();
                $importData->mall_id = \Yii::$app->mall->id;
                $importData->mch_id = \Yii::$app->user->identity->mch_id;
                $importData->user_id = \Yii::$app->user->id;
                $importData->type = 2;
                $importData->file_name = $fileName;
            } else {
                $uploadFile = $this->file_path;
                $importData = ImportData::findOne($this->import_data_id);
            }

            $errorList = [];
            $successCount = 0;
            $errorMsg = [];
            $list = $this->read_csv($uploadFile);
            if (count($list) > 300) {
                throw new \Exception('单次最多上传300条分类数据');
            }
            $actionNum = 10;
            $minNum = $this->current_num * $actionNum - $actionNum;
            $maxNum = $this->current_num * $actionNum - 1;
            foreach ($list as $key => $item) {
                if ($key >= $minNum && $key <= $maxNum) {
                    try {
                        $this->saveCat($item);
                        $successCount += 1;
                    } catch (\Exception $exception) {
                        $errorList[] = $item;
                        $errorItem = [];
                        $errorItem['line'] = $exception->getLine();
                        $errorItem['msg'] = $exception->getMessage();
                        $errorItem['name'] = $item['name'];
                        $errorItem['number'] = $item['number'];
                        $errorMsg[] = $errorItem;
                    }
                    if ($key > $maxNum) {
                        break;
                    }
                }
            }

            if (count($errorList) > 0) {
                $newArr['error_msg'] = $errorMsg;
                $newArr['error_list'] = $errorList;
            } else {
                $newArr['error_msg'] = [];
                $newArr['error_list'] = [];
            }
            // 记录错误数据
            $option = CommonOption::get(Option::NAME_IMPORT_CAT_ERROR_LOG, \Yii::$app->mall->id, Option::GROUP_ADMIN);
            if (!$option) {
                $option = CommonOption::set(Option::NAME_IMPORT_CAT_ERROR_LOG, $newArr, \Yii::$app->mall->id, Option::GROUP_ADMIN);
            }
            // 追加 错误数据
            if (count($errorList) > 0 || $this->current_num <= 1) {
                if ($this->current_num > 1) {
                    $newArr['error_msg'] = array_merge($option['error_msg'], $newArr['error_msg']);
                    $newArr['error_list'] = array_merge($option['error_list'], $newArr['error_list']);
                }
                $option = CommonOption::set(Option::NAME_IMPORT_CAT_ERROR_LOG, $newArr, \Yii::$app->mall->id, Option::GROUP_ADMIN);
            }

            $importData->count = count($list);
            $importData->success_count = $importData->success_count > 0 ? $importData->success_count + $successCount : $successCount;
            $importData->error_count = count($list) - $importData->success_count;
            if (count($list) == $importData->success_count) {
                $importData->status = 3;
            } elseif (count($list) - $importData->success_count > 0) {
                $importData->status = 2;
            } else {
                $importData->status = 1;
            }
            $res = $importData->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($importData));
            }

            $number = count($list) / $actionNum;
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '导入成功',
                'data' => [
                    'error_count' => $importData->error_count,
                    'success_count' => $importData->success_count,
                    'import_params' => [
                        'count' => count($list),
                        'num_count' => $number > 0 ? ceil($number) : 1,
                        'current_num' => (int)$this->current_num,
                        'file_path' => $uploadFile,
                        'import_data_id' => $importData->id,
                    ]
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => [
                    'line' => $exception->getLine()
                ]
            ];
        }
    }


    function read_csv($file)
    {
        try {
            setlocale(LC_ALL, 'zh_CN');//linux系统下生效
            $data = [];//返回的文件数据行
            if (!is_file($file) && !file_exists($file)) {
                throw new \Exception('csv文件错误');
            }

            $cvs_file = fopen($file, 'r'); //开始读取csv文件数据
            $i = 0;//记录cvs的行
            while ($file_data = fgetcsv($cvs_file)) {
                $i++;
                if ($i == 1) {
                    continue;//过滤表头
                }
                if (count($file_data) != 10) {
                    throw new \Exception('csv文件数据格式错误');
                }

                if ($file_data[0] != '') {
                    $arr = [];
                    $arr['number'] = $this->getNewData($file_data[0]);
                    $arr['name'] = $this->getNewData($file_data[1]);
                    $arr['pic_url'] = $this->getNewData($file_data[2]);
                    $arr['sort'] = $this->getNewData($file_data[3]);
                    $arr['big_pic_url'] = $this->getNewData($file_data[4]);
                    $arr['advert_pic'] = $this->getNewData($file_data[5]);
                    $arr['advert_url'] = $this->getNewData($file_data[6]);
                    $arr['advert_open_type'] = $this->getNewData($file_data[7]);
                    $arr['advert_params'] = $this->getNewData($file_data[8]);
                    $arr['child'] = $this->getNewData($file_data[9]);
                    $data[$i] = $arr;
                }
            }
            fclose($cvs_file);
            return array_values($data);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    private function getNewData($data)
    {
        // 解决乱码
        $text = iconv('GBK', 'UTF-8//IGNORE', $data);
        return trim($text);
    }

    private function saveCat($data)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $firstCat = $this->saveItem($data, 0);

            $child = json_decode($data['child'], true);
            foreach ($child as $item) {
                $secondCat = $this->saveItem($item, $firstCat->id);

                foreach ($item['child'] as $cItem) {
                    $this->saveItem($cItem, $secondCat->id);
                }
            }

            $transaction->commit();

            return true;
        } catch (\Exception $exception) {
            $transaction->rollBack();
            throw $exception;
        }
    }

    private function saveItem($data, $parentId)
    {
        $goodsCat = new GoodsCats();
        $goodsCat->mall_id = \Yii::$app->mall->id;
        $goodsCat->mch_id = \Yii::$app->user->identity->mch_id;
        $goodsCat->parent_id = $parentId;
        $goodsCat->name = $data['name'];
        $goodsCat->pic_url = $data['pic_url'];
        $goodsCat->sort = $data['sort'];
        $goodsCat->big_pic_url = $data['big_pic_url'];
        $goodsCat->advert_pic = $data['advert_pic'];
        $goodsCat->advert_url = $data['advert_url'];
        $goodsCat->status = $this->status;
        $goodsCat->is_show = $this->is_show;
        $goodsCat->advert_open_type = $data['advert_open_type'];
        $goodsCat->advert_params = $data['advert_params'];

        $res = $goodsCat->save();
        if (!$res) {
            throw new \Exception($this->getErrorMsg($goodsCat));
        }

        return $goodsCat;
    }
}