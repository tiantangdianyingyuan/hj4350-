<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/6/1
 * Time: 14:46
 */

namespace app\forms\api\admin;

use app\core\exceptions\ClassNotFoundException;
use app\core\response\ApiCode;
use app\forms\mall\share\ApplyForm;
use app\forms\mall\share\IndexForm;
use app\models\Model;
use app\models\Share;
use app\models\ShareSetting;

class ReviewForm extends Model
{
    public $id;
    public $page;
    public $type;
    public $status;
    public $keyword;
    public $queue_id;

    public $mch_per = false;//多商户权限

    public $tabs = [
        ['typeid' => 1, 'type' => 'mch'],
        ['typeid' => 2, 'type' => 'share'],
        ['typeid' => 3, 'type' => 'bonus'],
        ['typeid' => 4, 'type' => 'stock'],
    ];

    public function rules()
    {
        return [
            [['type', 'status', 'id', 'queue_id'], 'integer'],
            [['page', 'type'], 'default', 'value' => 1],
            [['keyword'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'type' => '审核消息类型',
            'statue' => '状态',
        ];
    }

    public function validate($attributeNames = null, $clearErrors = true)
    {
        $permission_arr = \Yii::$app->branch->childPermission(
            \Yii::$app->mall->user->adminInfo
        );//直接取商城所属账户权限，对应绑定管理员账户方法修改只给于app_admin权限
        if (!is_array($permission_arr) && $permission_arr) {
            $this->mch_per = true;
        } else {
            foreach ($permission_arr as $value) {
                if ($value == 'mch') {
                    $this->mch_per = true;
                    break;
                }
            }
        }
        return parent::validate($attributeNames, $clearErrors);
    }

    /**
     * 获取tab栏
     * @return array
     */
    public function getTabs()
    {
        foreach ($this->tabs as $key => $item) {
            if (!\Yii::$app->plugin->getInstalledPlugin($item['type']) || !\Yii::$app->plugin->getPlugin(
                    $item['type']
                )) {
                unset($this->tabs[$key]);
            }
        }
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => $this->tabs
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if ($this->type == 1) {
            try {
                $mch = $this->getMchReview();
                $mch->attributes = $this->attributes;
                $mch->review_status = $this->status;
                return $mch->getList();
            } catch (ClassNotFoundException | \Exception $exception) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $exception->getMessage()
                ];
            }
        } elseif ($this->type == 2) {
            $form = new IndexForm();
            $form->attributes = $this->attributes;
            return $form->getList();
        } elseif ($this->type == 3) {
            $form = $this->getBonusReview();
            $form->attributes = $this->attributes;
            $form->status = 0;//固定未审核状态
            $form->search_type = 1;//固定昵称搜索
            return $form->getList();
        } elseif ($this->type == 4) {
            $form = $this->getStockReview();
            $form->attributes = $this->attributes;
            $form->status = 0;//固定未审核状态
            $form->search_type = 1;//固定昵称搜索
            return $form->getList();
        } elseif ($this->type == 5) {
            $form = $this->getRegionReview();
            $form->attributes = $this->attributes;
            $form->status = 0;//固定未审核状态
            $form->search_type = 1;//固定昵称搜索
            return $form->getList();
        }
    }

    /**
     * 获取入驻商详情
     * @return array
     */
    public function getDetail()
    {
        try {
            $mch = $this->getMch();
        } catch (ClassNotFoundException | \Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
        $mch->id = $this->id;
        return $mch->getDetail();
    }

    public function switchStatus()
    {
        if ($this->type == 1) {
            try {
                $mch = $this->getMchEdit();
                $data = json_decode(\Yii::$app->request->post('form'), true);
                $mch->attributes = $data;
                $mch->username = $data['mchUser']['username'];
                $mch->province_id = $data['district'][0];
                $mch->city_id = $data['district'][1];
                $mch->district_id = $data['district'][2];
                $mch->review_status = \Yii::$app->request->post('status');
                $mch->is_review = 1;
            } catch (ClassNotFoundException | \Exception $exception) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $exception->getMessage()
                ];
            }
            return $mch->save();
        } elseif ($this->type == 2) {
            $form = new ApplyForm();
            $form->attributes = $this->attributes;
            $form->user_id = \Yii::$app->request->post('user_id');
            return $form->save();
        } elseif ($this->type == 3) {
            $form = $this->getBonusApply();
            $form->attributes = $this->attributes;
            $form->reason = \Yii::$app->request->post('reason');
            $form->user_id = \Yii::$app->request->post('user_id');
            return $form->become();
        } elseif ($this->type == 4) {
            $form = $this->getStockApply();
            $form->attributes = $this->attributes;
            $form->reason = \Yii::$app->request->post('reason');
            $form->user_id = \Yii::$app->request->post('user_id');
            return $form->become();
        } elseif ($this->type == 5) {
            $form = $this->getRegionApply();
            $form->attributes = $this->attributes;
            $form->reason = \Yii::$app->request->post('reason');
            $form->user_id = \Yii::$app->request->post('user_id');
            $form->province_id = \Yii::$app->request->post('province_id');
            $form->city_id = json_decode(\Yii::$app->request->post('city_id'), true);
            $form->district_id = json_decode(\Yii::$app->request->post('district_id'), true);
            $form->level = \Yii::$app->request->post('level');
            $form->status = \Yii::$app->request->post('status');
            if (\Yii::$app->request->post('is_up') == 1) {
                return $form->save();
            } else {
                return $form->become();
            }
        }
    }

    public function queueStatus()
    {
        if (!$this->queue_id) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '队列ID不能为空'
            ];
        }
        if (\Yii::$app->queue->isDone($this->queue_id)) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '执行成功'
            ];
        }
        return [
            'code' => ApiCode::CODE_ERROR,
            'msg' => '执行失败'
        ];
    }

    public function getCount()
    {
        if ($this->mch_per) {
            try {
                $mch = $this->getMch();
                $mchReviewCount = $mch->getCount();
            } catch (\Exception $exception) {
                $mchReviewCount = 0;
            }
        } else {
            $mchReviewCount = 0;
        }

        try {
            $bonus = $this->getBonusReview();
            $bonusReviewCount = $bonus->getCount();
        } catch (\Exception $exception) {
            $bonusReviewCount = 0;
        }

        try {
            $stock = $this->getStockReview();
            $stockReviewCount = $stock->getCount();
        } catch (\Exception $exception) {
            $stockReviewCount = 0;
            $regionReviewCount = 0;
        }

        try {
            $region = $this->getRegionReview();
            $regionReviewCount = $region->getCount();
        } catch (\Exception $exception) {
            $regionReviewCount = 0;
        }

        $shareCount = 0;

        $shareInfo = ShareSetting::findOne(['mall_id' => \Yii::$app->mall->id, 'key' => 'level', 'is_delete' => 0]);
        if (!empty($shareInfo) && $shareInfo['value'] >= 1) {
            $shareCount = Share::find()->where(
                [
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                    'status' => 0,
                ]
            )->count();
        }
        $allCount = $mchReviewCount + $shareCount + $bonusReviewCount + $stockReviewCount + $regionReviewCount;
        return $allCount;
    }

    private function getMchReview()
    {
        $plugin = \Yii::$app->plugin->getPlugin('mch');
        $form = $plugin->getMchReview();
        return $form;
    }

    private function getMch()
    {
        $plugin = \Yii::$app->plugin->getPlugin('mch');
        $form = $plugin->getMch();
        return $form;
    }

    private function getMchEdit()
    {
        $plugin = \Yii::$app->plugin->getPlugin('mch');
        $form = $plugin->getMchEdit();
        return $form;
    }

    private function getStockReview()
    {
        $plugin = \Yii::$app->plugin->getPlugin('stock');
        $form = $plugin->getStockReview();
        return $form;
    }

    private function getStockApply()
    {
        $plugin = \Yii::$app->plugin->getPlugin('stock');
        $form = $plugin->getStockApply();
        return $form;
    }


    private function getBonusReview()
    {
        $plugin = \Yii::$app->plugin->getPlugin('bonus');
        $form = $plugin->getBonusReview();
        return $form;
    }

    private function getBonusApply()
    {
        $plugin = \Yii::$app->plugin->getPlugin('bonus');
        $form = $plugin->getBonusApply();
        return $form;
    }

    private function getRegionReview()
    {
        $plugin = \Yii::$app->plugin->getPlugin('region');
        $form = $plugin->getRegionReview();
        return $form;
    }

    private function getRegionApply()
    {
        $plugin = \Yii::$app->plugin->getPlugin('region');
        $form = $plugin->getRegionApply();
        return $form;
    }
}
