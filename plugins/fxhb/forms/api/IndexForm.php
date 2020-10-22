<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/21
 * Time: 11:04
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fxhb\forms\api;


use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoodsList;
use app\forms\common\template\TemplateList;
use app\models\User;
use app\plugins\fxhb\forms\common\CommonFxhbDb;
use app\plugins\fxhb\forms\common\CommonRecommend;

/**
 * @property User $user
 */
class IndexForm extends ApiModel
{
    public $user;
    public $user_activity_id;

    public function search()
    {
        $common = CommonFxhbDb::getCommon($this->mall);
        $config = $common->getActivity([
            'id', 'number', 'count_price', 'share_title', 'share_pic_url', 'pic_url', 'remark', 'start_time',
            'end_time', 'sponsor_count_type', 'sponsor_count'
        ]);
        if (!$config) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '红包未开启'
            ];
        }
        if ($config->start > time()) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '活动未开始'
            ];
        }
        if ($config->end <= time()) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '活动已结束'
            ];
        }
        if ($config->sponsor_count_type == 0 && $config->sponsor_count == 0) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '活动已结束'
            ];
        }
        $userActivity = $common->getUserActivityByUserId($this->user->id, $config->id);
        if (!$userActivity && $this->user_activity_id) {
            $userActivity = $common->getUserActivity($this->user_activity_id, \Yii::$app->user->id);
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'config' => $config,
                'template_message' => TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, ['enroll_success_tpl']),
                'user_activity_id' => $userActivity
                    ? ($userActivity->parent_id == 0 ? $userActivity->id : $userActivity->parent_id)
                    : null
            ]
        ];
    }

    public function getNewList()
    {
        try {
            $form = new CommonRecommend();
            $setting = $form->getSetting();

            $list = [];
            foreach ($setting as $key => $item) {
                if ($key == 'fxhb') {
                    $list = $this->getGoodsList($item);
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $list
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    private function getGoodsList($item)
    {
        if ($item['is_recommend_status'] == 0) {
            return [];
        }

        $goodsIds = [];
        $form = new CommonGoodsList();
        if ($item['is_custom'] == 1) {
            // 推荐商品自定义
            foreach ($item['goods_list'] as $gItem) {
                if (!in_array($gItem['id'], $goodsIds)) {
                    $goodsIds[] = $gItem['id'];
                }
            }
            $form->goods_id = $goodsIds;
            $form->limit = count($goodsIds);
        } else {
            // 获取商品列表排序前10件商品
            $form->limit = 10;
            $form->sort = 1;
        }

        $form->status = 1;
        $form->sign = ['mch', ''];
        $list = $form->getList();

        // 商品重新排序
        $newList = [];
        if (isset($item['is_custom']) && $item['is_custom']) {
            foreach ($goodsIds as $id) {
                foreach ($list as $item) {
                    if ($item['id'] == $id) {
                        $newList[] = $item;
                    }
                }
            }
        } else {
            $newList = $list;
        }

        return $newList;
    }
}
