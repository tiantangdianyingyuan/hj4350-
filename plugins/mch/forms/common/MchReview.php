<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/7/8
 * Time: 18:32
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\mch\forms\common;


use app\core\response\ApiCode;
use app\forms\common\review\BaseReview;
use app\plugins\mch\forms\mall\MchEditForm;
use app\plugins\mch\forms\mall\MchForm;
use app\plugins\mch\forms\mall\MchReviewForm;

class MchReview extends BaseReview
{

    /**
     * @return array
     * @throws \Exception
     * 获取审核消息列表
     */
    public function getList()
    {
        $mch = new MchReviewForm();
        $mch->attributes = \Yii::$app->request->get();
        $mch->review_status = 0;
        $res = $mch->getList();
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
                'tip' => $datum['store']['name']
            ];
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => $res['msg'],
            'data' => [
                'list' => $newList
            ]
        ];
    }

    /**
     * @return array
     * @throws \Exception
     * 获取审核详情
     */
    public function getDetail()
    {
        $form = new MchForm();
        $form->attributes = \Yii::$app->request->get();
        return $form->getDetail();
    }

    /**
     * @return array
     * @throws \Exception
     * 处理审核消息
     */
    public function become()
    {
        $mch = new MchEditForm();
        $data = json_decode(\Yii::$app->request->post('form'), true);
        $mch->attributes = $data;
        $mch->username = $data['mchUser']['username'];
        $mch->province_id = $data['district'][0];
        $mch->city_id = $data['district'][1];
        $mch->district_id = $data['district'][2];
        $mch->review_status = \Yii::$app->request->post('status');
        $mch->is_review = 1;
        return $mch->save();
    }

    public function getCount()
    {
        $form = new MchForm();
        return $form->getCount();
    }
}
