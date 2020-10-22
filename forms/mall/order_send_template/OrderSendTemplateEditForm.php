<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\order_send_template;


use app\core\response\ApiCode;
use app\models\Model;
use app\models\OrderSendTemplate;

class OrderSendTemplateEditForm extends Model
{
    public $id;
    public $name;
    public $file;
    public $params;
    public $data;
    public $is_edit_cover_pic = 1;

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['id', 'is_edit_cover_pic'], 'integer'],
            [['file'], 'safe'],
            [['params', 'data'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '模板ID',
            'name' => '模板名称',
            'file' => '模板图片',
            'params' => '模板参数',
            'data' => '模板数据',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $template = OrderSendTemplate::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'is_delete' => 0,
                'id' => $this->id
            ])->one();


            if (!$template) {
                $template = new OrderSendTemplate();
                $template->mall_id = \Yii::$app->mall->id;
                $template->mch_id = \Yii::$app->user->identity->mch_id;
            }

            // 编辑的时候不修改图片
            if ($this->is_edit_cover_pic) {
                $imageUrl = $this->saveFile();
                if (!$imageUrl) {
                    throw new \Exception('图片保存失败');
                }
                $template->cover_pic = $imageUrl;
            }

            $template->name = $this->name;
            $template->params = $this->params;
            $res = $template->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($template));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
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

    private function saveFile()
    {
        $base64 = \Yii::$app->request->post('file');
        //匹配出图片的格式
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64, $result)) {
            $type = $result[2];
            $basePath = \Yii::$app->basePath;
            $filePath = '/web/uploads/send-template/' . date('Y') . date('m') . date('d') . '/';
            $newPath = $basePath . $filePath;
            if (!file_exists($newPath)) {
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($newPath, 0777, true);
            }
            $newFilePath = $filePath . time() . ".{$type}";
            if (file_put_contents($basePath . $newFilePath, base64_decode(str_replace($result[1], '', $base64)))) {
                return \Yii::$app->request->hostInfo . $newFilePath;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}