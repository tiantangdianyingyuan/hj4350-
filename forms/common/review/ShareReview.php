<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/7/8
 * Time: 16:45
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\review;


use app\core\response\ApiCode;
use app\forms\mall\share\ApplyForm;
use app\forms\mall\share\IndexForm;
use app\models\Share;

class ShareReview extends BaseReview
{
    public $user_id;

    public function rules()
    {
        return [
            ['user_id', 'integer'],
        ];
    }
    /**
     * @return array
     * 获取审核消息列表
     */
    public function getList()
    {
        $form = new IndexForm();
        $form->attributes = \Yii::$app->request->get();
        $form->status = 0;
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
                'name' => $datum['name'],
                'mobile' => $datum['mobile'],
                'avatar' => $datum['avatar'],
                'tip' => '推荐人：' . $datum['parent_name'],
                'form' => $datum['form'],
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
     * 处理审核消息
     */
    public function become()
    {
        $form = new ApplyForm();
        $form->attributes = \Yii::$app->request->post();
        return $form->save();
    }

    public function getCount()
    {
        $shareCount = Share::find()->where(
            [
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'status' => 0,
            ]
        )->count();
        return $shareCount;
    }
}
