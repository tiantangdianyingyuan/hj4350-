<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/7/9
 * Time: 9:22
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\region\forms\common;


use app\core\response\ApiCode;
use app\forms\common\review\BaseReview;
use app\plugins\region\forms\mall\RegionEditForm;
use app\plugins\region\forms\mall\RegionForm;

class RegionReview extends BaseReview
{

    /**
     * @return array
     * @throws \Exception
     * 获取审核消息列表
     */
    public function getList()
    {
        $form = new RegionForm();
        $form->attributes = \Yii::$app->request->get();
        $form->status = 0;
        $form->search_type = 1;
        $res = $form->getList();
        if ($res['code'] != 0) {
            return $res;
        }
        $newList = [];
        foreach ($res['data']['list'] as $datum) {
            $newList[] = [
                'id' => $datum['id'],
                'user_id' => $datum['user_id'],
                'nickname' => $datum['user']['nickname'],
                'avatar' => $datum['user']['userInfo']['avatar'],
                'tip' => $datum['level_desc']
            ];
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $newList
            ]
        ];
    }

    /**
     * @return array
     * @throws \Exception
     * 获取审核详情
     * TODO 待补充。。。
     */
    public function getDetail()
    {
        return [];
    }

    /**
     * @return array
     * @throws \Exception
     * 处理审核消息
     */
    public function become()
    {
        $form = new RegionEditForm();
        $form->attributes = \Yii::$app->request->post();
        $form->city_id = json_decode(\Yii::$app->request->post('city_id'), true);
        $form->district_id = json_decode(\Yii::$app->request->post('district_id'), true);
        if (\Yii::$app->request->post('is_up') == 1) {
            return $form->save();
        } else {
            return $form->become();
        }
    }

    public function getCount()
    {
        $form = new RegionForm();
        return $form->getCount();
    }
}
