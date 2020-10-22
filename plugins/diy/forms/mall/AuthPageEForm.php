<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/17
 * Time: 10:16
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\diy\forms\mall;


use app\core\response\ApiCode;
use app\models\Mall;
use app\models\Model;
use app\plugins\diy\forms\common\CommonAlonePage;

/**
 * @property Mall $mall
 */
class AuthPageEForm extends Model
{
    public $mall;

    public $pic_url;
    public $is_open;
    public $hotspot;
    public $hotspot_cancel;

    public function rules()
    {
        $picUrl = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/app/mall';
        return [
            [['pic_url', 'is_open', 'hotspot', 'hotspot_cancel'], 'trim'],
            [['pic_url'], 'default', 'value' => $picUrl . '/auth-default.png']
        ];
    }

    public function attributeLabels()
    {
        return [
            'pic_url' => '图片',
            'hotspot' => '热区'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if (!$this->hotspot) {
                throw new \Exception('需要添加登录按钮热区');
            }
            if (!$this->hotspot_cancel) {
                throw new \Exception('需要添加暂不登录按钮热区');
            }
            $commonAlonePage = CommonAlonePage::getCommon($this->mall);
            $attributes = [
                'params' => \Yii::$app->serializer->encode([
                    'pic_url' => $this->pic_url,
                    'hotspot' => $this->hotspot,
                    'hotspot_cancel' => $this->hotspot_cancel,
                ]),
                'type' => 'auth',
                'is_open' => $this->is_open
            ];
            $commonAlonePage->saveAlonePage($attributes);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => $exception->getTrace()
            ];
        }
    }

    public function search()
    {
        $commonAlonePage = CommonAlonePage::getCommon($this->mall);
        $model = $commonAlonePage->getAlonePage();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'is_open' => $model->is_open,
                'pic_url' => $model->params ? $model->params->pic_url : '',
                'hotspot' => $model->params ? $model->params->hotspot : '',
                'hotspot_cancel' => $model->params && isset($model->params->hotspot_cancel) ? $model->params->hotspot_cancel : '',
            ]
        ];
    }
}
