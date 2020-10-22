<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/6/3
 * Time: 9:38
 */

namespace app\forms\api\admin;

use app\core\exceptions\ClassNotFoundException;
use app\core\response\ApiCode;
use app\forms\mall\finance\FinanceFactory;
use app\forms\mall\finance\FinanceForm;
use app\forms\mall\share\CashApplyForm;
use app\forms\mall\share\CashListForm;
use app\models\Model;

class CashForm extends Model
{
    public $id;
    public $page;
    public $type;
    public $status;
    public $keyword;
    public $content;
    public $transfer_type;
    public $model_type;

    public $mch_per = false;//多商户权限

    public $tabs = [
        ['typeid' => 1, 'type' => 'mch'],
        ['typeid' => 2, 'type' => 'share'],
    ];

    public function rules()
    {
        return [
            [['type', 'status', 'id'], 'integer'],
            [['page', 'type'], 'default', 'value' => 1],
            [['keyword', 'model_type'], 'string'],
            [['content', 'transfer_type'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'type' => '提现类型',
            'statue' => '状态',
        ];
    }

    public function validate($attributeNames = null, $clearErrors = true)
    {
        $permission_arr = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);//直接取商城所属账户权限，对应绑定管理员账户方法修改只给于app_admin权限
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
                if (!$plugin->needCash()) {
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

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if ($this->type == 1) {
            try {
                $mch = $this->getCashForm();
                $mch->keyword_nickname = $this->keyword;
                $this->keyword = null;
                $mch->attributes = $this->attributes;
                $mch->transfer_status = 0;
                return $mch->getList();
            } catch (ClassNotFoundException | \Exception $exception) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $exception->getMessage()
                ];
            }
        } else {
            $form = new CashListForm();
            $form->attributes = $this->attributes;
            return $form->search();
        }
    }

    /**
     * @deprecated
     * @return array
     */
    public function verify()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if ($this->type == 1) {
            try {
                $mch = $this->getCashEditForm();
                $mch->attributes = $this->attributes;
                return $mch->save();
            } catch (ClassNotFoundException | \Exception $exception) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $exception->getMessage()
                ];
            }
        } else {
            $form = new CashApplyForm();
            $form->attributes = $this->attributes;
            return $form->save();
        }
    }

    /**
     * @deprecated
     * 打款
     */
    public function cash()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if ($this->type == 1) {
            try {
                $mch = $this->getCashEditForm();
                $mch->attributes = $this->attributes;
                return $mch->transfer();
            } catch (ClassNotFoundException | \Exception $exception) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $exception->getMessage()
                ];
            }
        } else {
            $form = new CashApplyForm();
            $form->attributes = $this->attributes;
            return $form->save();
        }
    }

    public function getCount()
    {
        try {
            $form = new FinanceForm();
            $count = $form->getCount();
        } catch (\Exception $exception) {
            $count = 0;
        }
        return $count;
    }

    /**
     * @deprecated
     * @return mixed
     * @throws ClassNotFoundException
     */
    private function getCashForm()
    {
        $plugin = \Yii::$app->plugin->getPlugin('mch');
        $form = $plugin->getCashForm();
        return $form;
    }

    /**
     * @deprecated
     * @return mixed
     * @throws ClassNotFoundException
     */
    private function getCashEditForm()
    {
        $plugin = \Yii::$app->plugin->getPlugin('mch');
        $form = $plugin->getCashEditForm();
        return $form;
    }

    public function search()
    {
        try {
            $form = new FinanceForm();
            $form->attributes = $this->attributes;
            $form->model_type = $this->model_type;
            $form->type = 'api';
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $form->search()
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $factory = new FinanceFactory();
            $class = $factory->create($this->model_type);
            $class->attributes = $this->attributes;
            return $class->save();
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}
