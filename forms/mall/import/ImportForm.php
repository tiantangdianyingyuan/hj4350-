<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/11
 * Time: 11:01
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\mall\import;


use app\core\response\ApiCode;
use app\forms\common\data_importing\V3DataImporting;
use app\models\Model;

class ImportForm extends Model
{
    public $data;

    public function rules()
    {
        return [
            [['data'], 'file', 'extensions' => ['json']]
        ];
    }

    public function import()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $t1 = microtime(true);
            if (empty($_FILES) || !isset($_FILES['data'])) {
                throw new \Exception('请上传商城数据文件');
            }
            $fileName = $_FILES['data']['name'];
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if (($ext != 'json')) {
                throw new \Exception('请上传商城数据文件--json格式');
            }
            $file = file_get_contents($_FILES['data']['tmp_name']);
            if (!$file) {
                throw new \Exception('文件内容为空');
            }
            $data = json_decode($file, true);
            if (!is_array($data)) {
                throw new \Exception('文件内容格式不正确');
            }

            // v3商城数据导入
            $import = new V3DataImporting();
            $import->v3Data = $data;
            $import->mall = \Yii::$app->mall;
            if ($import->import()) {
                $t2 = microtime(true);
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '导入成功，导入时间：' . round($t2 - $t1, 3) . '秒'
                ];
            } else {
                throw new \Exception('导入失败x01');
            }
        } catch (\Exception $exception) {
            throw $exception;
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
