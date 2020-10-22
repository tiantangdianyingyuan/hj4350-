<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\we7;

use app\core\response\ApiCode;
use app\forms\common\CommonAuth;
use app\forms\common\CommonOption;
use app\models\Model;
use app\models\Option;

class AuthForm extends Model
{
    public $page;
    public $search;
    public $status;

    public function rules()
    {
        return [
            [['page'], 'integer'],
            [['search'], 'safe'],
            [['status'], 'string'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [];
    }

    public function getList()
    {
        $search = \Yii::$app->serializer->decode($this->search);
        $res = CommonAuth::getChildrenUsers($search);
        $plugins = [];
        foreach (\Yii::$app->plugin->list as $item) {
            $plugins[] = $item['name'];
        }
        foreach ($res['list'] as $key => $item) {
            $permissions = $item['adminInfo']['permissions'] ? \Yii::$app->serializer->decode($item['adminInfo']['permissions']) : [];

            $newPermissions = [
                'mall' => [],
                'plugins' => [],
            ];
            foreach ($permissions as $pItem) {
                if (!in_array($pItem, $plugins)) {
                    $newPermissions['mall'][] = $pItem;
                } else {
                    $newPermissions['plugins'][] = $pItem;
                }
            }

            $res['list'][$key]['adminInfo']['permissions'] = $newPermissions;
            $res['list'][$key]['adminInfo']['permissions_num'] = count($permissions);
        }

        $status = CommonOption::get(
            Option::NAME_PERMISSIONS_STATUS,
            0,
            Option::GROUP_ADMIN
        );

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $res['list'],
                'pagination' => $res['pagination'],
                'status' => $status ? $status : '0'
            ]
        ];
    }

    public function updateStatus()
    {
        $option = CommonOption::set(
            Option::NAME_PERMISSIONS_STATUS,
            $this->status,
            0,
            Option::GROUP_ADMIN
        );

        if (!$option) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '保存失败'
            ];
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功'
        ];
    }
}
