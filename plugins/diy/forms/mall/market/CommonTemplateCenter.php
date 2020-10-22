<?php

/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/11/7
 * Time: 14:26
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\diy\forms\mall\market;

use app\models\Mall;
use app\models\Model;
use app\plugins\diy\models\CoreTemplate;
use app\plugins\diy\models\CoreTemplateEdit;

/**
 * Class CommonTemplateCenter
 * @package app\forms\common\template_center
 * @property Mall $mall
 */
class CommonTemplateCenter extends Model
{
    private static $instance;
    public $mall;
    public $page;
    public $keyword;
    public $type;
    public $is_buy;
    public static function getInstance()
    {
        if (self::$instance) {
            return self::$instance;
        }
        self::$instance = new self();
        return self::$instance;
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     * 获取已购买的模板中心
     */
    public function getList()
    {
        $list = CoreTemplate::find()->where(['is_delete' => 0])
            ->keyword($this->keyword !== '' && $this->keyword !== null, ['like', 'name', $this->keyword])
            ->keyword($this->type, ['type' => $this->type])
            ->select('id,name,template_id')->groupBy('template_id')->apiPage(20, $this->page)->all();
        return $this->changeName($list);
    }

    /**
     * @param $ids
     * @return array|\yii\db\ActiveRecord[]
     * 获取本地已安装的
     */
    public function getListById($ids)
    {
        $list = CoreTemplate::find()->where(['is_delete' => 0, 'template_id' => $ids])
            ->select('id,name,template_id')->all();
        return $this->changeName($list);
    }

    public function getTemplatePermission()
    {
        $templatePermission = \Yii::$app->role->getTemplate();
        return $templatePermission;
    }

    /**
     * @param CoreTemplate[] $list
     * @return array
     * 处理本地的模板名称
     */
    public function changeName($list)
    {
        $id = array_column($list, 'template_id');
        $editList = CoreTemplateEdit::findAll(['template_id' => $id]);
        $newList = [];
        foreach ($list as $item) {
            $newItem = [
                'id' => $item->template_id,
                'name' => $item->name,
            ];
            foreach ($editList as $value) {
                if ($value->template_id == $item->template_id) {
                    $newItem['name'] = $value->name;
                }
            }
            $newList[] = $newItem;
        }
        return $newList;
    }

    /**
     * @return array
     * @throws \app\core\cloud\CloudException
     * 获取云市场上的模板列表
     */
    public function getCloudList()
    {
        $params = [
            'page' => $this->page - 1
        ];
        $params['type'] = 'diy';
        if ($this->is_buy !== '') {
            $params['is_buy'] = $this->is_buy;
        }
        // 模板市场中的模板
        $res = \Yii::$app->cloud->template->getList($params);
        return $this->changeCloudName($res['list']);
    }

    /**
     * @return array
     * @throws \app\core\cloud\CloudException
     * 获取指定id的云市场上的模板列表
     */
    public function getCloudListById($idList)
    {
        $params['id'] = urldecode(json_encode($idList, JSON_UNESCAPED_UNICODE));
// 模板市场中的模板
        $res = \Yii::$app->cloud->template->getList($params);
        return $this->changeCloudName($res['list']);
    }

    /**
     * @param $list
     * @return array
     * 处理云市场上的模板名称
     */
    public function changeCloudName($list)
    {
        $id = array_column($list, 'id');
        $editList = CoreTemplateEdit::findAll(['template_id' => $id]);
        $newList = [];
        foreach ($list as $item) {
            $newItem = [
                'id' => $item['id'],
                'name' => $item['name'],
            ];
            foreach ($editList as $value) {
                if ($value->template_id == $item['id']) {
                    $newItem['name'] = $value->name;
                }
            }
            $newList[] = $newItem;
        }
        return $newList;
    }

    /**
     * @param array $templatePermission // 模板权限
     * @return array
     * 获取已拥有的模板id列表
     */
    public function getUseTemplate($templatePermission)
    {
        if ($templatePermission['use_all'] == 1) {
// 已购买的模板
            $useList = CoreTemplate::find()->where([
                'is_delete' => 0
            ])->select('template_id')->column();
        } else {
            $useList = $templatePermission['use_list'];
        }
        if (empty($useList)) {
            $useList = [0];
        }
        return $useList;
    }

    /**
     * @param $templatePermission
     * @return array
     * 获取可显示的模板id列表
     */
    public function getShowTemplate($templatePermission)
    {
        if ($templatePermission['is_all'] != 1) {
            $showList = $templatePermission['list'];
            if (empty($showList)) {
                $showList = [0];
            }
        } else {
            $showList = \Yii::$app->cloud->template->allId(['dont_validate_domain' => 1]);
        }
        return $showList;
    }

    /*
     * 新建微页面
     */
    public function getUseShowTemplate($templatePermission)
    {
        if ($templatePermission['is_all'] == 1) {
            if ($templatePermission['use_all'] == 1) {
                $useShowList = [];
            } else {
                $useShowList = $templatePermission['use_list'] ?: [0];
            }
        } else {
            $showList = $templatePermission['list'];
            $useList = $templatePermission['use_list'];
            $useList = array_filter($useList, function ($item) use ($showList) {
                return in_array($item, $showList);
            });
            $useShowList = $useList ?: [0];
        }
        return $useShowList;
    }
}
