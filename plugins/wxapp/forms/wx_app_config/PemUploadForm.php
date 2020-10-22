<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/7/15
 * Time: 9:19
 */

namespace app\plugins\wxapp\forms\wx_app_config;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\wxapp\models\WxappConfig;
use yii\web\UploadedFile;

class PemUploadForm extends Model
{
    public $id;
    /** @var UploadedFile */
    public $file;
    public $type;

    public function save()
    {
        try {
            /**@var WxappConfig $wxAppConfig**/
            $wxAppConfig = WxappConfig::find()
                ->where(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id])
                ->with(['service'])
                ->one();

            if (!$wxAppConfig || !$wxAppConfig->service) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '请先保存服务商配置',
                ];
            }

            if ($this->file->extension !== 'pem') {
                throw new \Exception('文件格式不正确, 请上传 .pem 格式文件');
            }

            if ($this->type == 'cert') {
                $wxAppConfig->service->cert_pem = file_get_contents($this->file->tempName);
            } elseif ($this->type = 'key') {
                $wxAppConfig->service->key_pem = file_get_contents($this->file->tempName);
            } else {
                throw new \Exception('未知的类型');
            }
            $res = $wxAppConfig->service->save();

            if (!$res) {
                throw new \Exception('保存失败');
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '上传成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
