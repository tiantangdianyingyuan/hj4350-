<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\admin\mall;


use app\core\response\ApiCode;
use app\models\Model;

class FileForm extends Model
{
    public $file;

    public function save()
    {
        try {
            if ($this->file->extension !== 'txt') {
                throw new \Exception('文件格式不正确, 请上传 .txt 格式文件');
            }

            if ($this->file->size !== 32) {
                throw new \Exception('文件内容长度不正确, 请检查');
            }

            $saveFile = \Yii::$app->basePath . '/' . $this->file->name;
            if ($this->file->saveAs($saveFile)) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '上传成功',
                ];
            } else {
                throw new \Exception('文件保存失败，请检查目录写入权限。');
            }
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
