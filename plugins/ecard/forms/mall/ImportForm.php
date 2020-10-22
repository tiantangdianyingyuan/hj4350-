<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/3/14
 * Time: 9:59
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\ecard\forms\mall;


use app\plugins\ecard\forms\Model;
use yii\helpers\Json;

class ImportForm extends Model
{
    public $file;
    public $ecard_id;

    public function rules()
    {
        return [
            [['ecard_id'], 'integer'],
            [['file'], 'file', 'extensions' => ['csv', 'excel']]
        ];
    }

    public function import()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            set_time_limit(0);
            if (empty($_FILES) || !isset($_FILES['file'])) {
                throw new \Exception('请上传csv或excel文件');
            }
            $fileName = $_FILES['file']['name'];
            $tmpName = $_FILES['file']['tmp_name'];
            $path = \Yii::$app->basePath . '/web/temp/';
            if (!is_dir($path)) {
                mkdir($path);
            }
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $file = time() . \Yii::$app->mall->id . '.' . $ext;
            $uploadFile = $path . $file;
            $result = move_uploaded_file($tmpName, $uploadFile);
            if (!$result) {
                throw new \Exception('文件上传失败，请检查目录是否有权限');
            }
            switch ($ext) {
                case 'csv':
                    $data = $this->getCsvData($uploadFile);
                    break;
                case 'xlsx':
                    $data = $this->getExcelData($uploadFile, $ext);
                    break;
                case 'xls':
                    $data = $this->getExcelData($uploadFile, $ext);
                    break;
                default:
                    throw new \Exception('请上传csv或excel文件');
            }
            if (empty($data)) {
                throw new \Exception('上传的数据为空');
            }
            $form = new ListEditForm();
            $form->attributes = [
                'ecard_id' => $this->ecard_id,
                'list' => Json::encode($data, JSON_UNESCAPED_UNICODE)
            ];
            return $form->batch(true);
        } catch (\Exception $exception) {
            return $this->fail(['msg' => $exception->getMessage()]);
        }
    }

    public function getCsvData($file)
    {
        if (!is_file($file)) {
            exit('没有文件');
        }
        $handle = fopen($file, 'r');
        if (!$handle) {
            exit('读取文件失败');
        }

        $count = 0;
        $newList = [];
        while (($data = fgetcsv($handle)) !== false) {
            $count++;
            // 跳过第一行标题
            if ($count <= 1) {
                continue;
            }
            $index = 1;
            $newItem = [];
            foreach ($data as $item) {
                // 下面这行代码可以解决中文字符乱码问题
                $newItem['key' . $index] = iconv('gbk', 'utf-8', $item);
                $index++;
            }
            $newList[] = $newItem;
        }

        fclose($handle);
        return $newList;
    }

    /**
     * @param $uploadFile
     * @param $ext
     * @throws \PHPExcel_Exception
     * @return array
     */
    public function getExcelData($uploadFile, $ext)
    {
        $reader = \PHPExcel_IOFactory::createReader(($ext == 'xls' ? 'Excel5' : 'Excel2007'));
        $excel = $reader->load($uploadFile);
        $sheet = $excel->getActiveSheet();
        $highestRow = $sheet->getHighestRow(); // 总行数
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnCount = \PHPExcel_Cell::columnIndexFromString($highestColumn); // 总列数
        $row = 0;
        $newList = [];
        while ($row < $highestRow) {
            $row++;
            // 跳过第一行标题
            if ($row <= 1) {
                continue;
            }
            $rowValue = [];
            $col = 0;
            $index = 1;
            while ($col < $highestColumnCount) {
                $rowValue['key' . $index] = (string)$sheet->getCellByColumnAndRow($col, $row)->getValue();
                $col++;
                $index++;
            }
            $newList[] = $rowValue;
        }
        return $newList;
    }
}
