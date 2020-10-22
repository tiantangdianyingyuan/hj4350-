<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/7/9
 * Time: 9:46
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\stock\forms\common;


use app\core\response\ApiCode;
use app\forms\common\review\BaseReview;
use app\plugins\stock\forms\mall\StockForm;

class StockReview extends BaseReview
{

    /**
     * @return array
     * @throws \Exception
     * 获取审核消息列表
     */
    public function getList()
    {
        $form = new StockForm();
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
                'nickname' => $datum['nickname'],
                'avatar' => $datum['avatar'],
                'tip' => ''
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
        $form = new CommonStock();
        $form->attributes = \Yii::$app->request->post();
        return $form->become();
    }

    public function getCount()
    {
        $form = new StockForm();
        return $form->getCount();
    }
}
