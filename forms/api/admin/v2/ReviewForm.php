<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/7/7
 * Time: 11:34
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\api\admin\v2;


use app\core\response\ApiCode;
use app\forms\common\review\BaseReview;
use app\forms\common\review\ShareReview;
use app\models\Model;

class ReviewForm extends Model
{

    public $key;

    public function rules()
    {
        return [
            [['key'], 'required'],
            [['key'], 'trim'],
            [['key'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'key' => '请求类型'
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $form = $this->createClass();
            $form->attributes = \Yii::$app->request->get();
            return $form->getList();
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }

    public function getTabs()
    {
        // 当前商城所属账号的权限
        $permissions = \Yii::$app->mall->role->permission;
        $tabs = [];
        if (in_array('share', $permissions)) {
            $tabs[] = [
                'key' => 'share',
                'name' => '分销商',
                'plugin' => '分销商'
            ];
        }
        foreach ($permissions as $name) {
            try {
                $plugin = \Yii::$app->plugin->getPlugin($name);
                if (!$plugin->needCheck()) {
                    continue;
                }
                $tabs[] = [
                    'key' => $plugin->getName(),
                    'name' => $plugin->identityName(),
                    'plugin' => $plugin->getDisplayName(),
                ];
            } catch (\Exception $exception) {
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => $tabs
        ];
    }

    public function switchStatus()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $form = $this->createClass();
            $form->attributes = \Yii::$app->request->post();
            return $form->become();
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $form = $this->createClass();
            $form->attributes = \Yii::$app->request->get();
            return $form->getDetail();
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @return BaseReview
     * @throws \Exception
     */
    private function createClass()
    {
        $permission = \Yii::$app->mall->role->permission;
        if (!in_array($this->key, $permission)) {
            throw new \Exception('没有访问的权限');
        }
        if ($this->key == 'share') {
            $form = new ShareReview();
        } else {
            $plugin = \Yii::$app->plugin->getPlugin($this->key);
            if (!method_exists($plugin, 'getReviewClass')) {
                throw new \Exception('系统错误');
            }
            $form = $plugin->getReviewClass();
        }
        return $form;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        $permission = \Yii::$app->mall->role->permission;
        $count = 0;
        foreach ($permission as $name) {
            try {
                if ($name == 'share') {
                    $form = new ShareReview();
                } else {
                    $plugin = \Yii::$app->plugin->getPlugin($name);
                    if (!method_exists($plugin, 'getReviewClass')) {
                        throw new \Exception('系统错误');
                    }
                    $form = $plugin->getReviewClass();
                }
                $count += $form->getCount();
            } catch (\Exception $exception) {
            }
        }
        return $count;
    }
}
