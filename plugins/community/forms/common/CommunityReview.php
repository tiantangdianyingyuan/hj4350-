<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/7/8
 * Time: 17:05
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\common;


use app\core\response\ApiCode;
use app\forms\common\review\BaseReview;
use app\models\User;
use app\plugins\community\models\CommunityMiddleman;

class CommunityReview extends BaseReview
{
    public $keyword;
    public $page;
    public $user_id;
    public $status;
    public $reason;

    public function rules()
    {
        return [
            [['keyword', 'reason'], 'trim'],
            [['keyword', 'reason'], 'string'],
            [['page', 'user_id', 'status'], 'integer'],
            [['page'], 'default', 'value' => 1],
            ['status', 'in', 'range' => [1, 2]]
        ];
    }

    /**
     * @return array
     * @throws \Exception
     * 获取审核消息列表
     */
    public function getList()
    {
        if (!$this->validate()) {
            throw new \Exception($this->getErrorMsg());
        }
        if ($this->keyword !== '') {
            $condition[] = ['like', 'name', $this->keyword];
        }
        $condition[] = ['status' => 0];
        array_unshift($condition, 'and');
        $list = CommunityMiddleman::find()->with('address')
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->keyword(!empty($condition), $condition)
            ->orderBy(['status' => SORT_ASC, 'apply_at' => SORT_DESC])
            ->apiPage(20, $this->page)
            ->all();
        /* @var CommunityMiddleman[] $list */
        $newList = [];
        foreach ($list as $middleman) {
            $newList[] = [
                'id' => $middleman->id,
                'user_id' => $middleman->user_id,
                'avatar' => $middleman->user->userInfo->avatar,
                'nickname' => $middleman->name,
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
        if (!$this->validate()) {
            throw new \Exception($this->getErrorMsg());
        }
        $common = CommonMiddleman::getCommon();
        $middleman = $common->getConfig($this->user_id);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => $common->getMiddleman($middleman)
        ];
    }

    /**
     * @return array
     * @throws \Exception
     * 处理审核消息
     */
    public function become()
    {
        if (!$this->validate()) {
            throw new \Exception($this->getErrorMsg());
        }
        $common = CommonMiddleman::getCommon();
        $middleman = $common->getConfig($this->user_id);
        if (!$middleman) {
            throw new \Exception('团长不存在，或已被解除');
        }
        switch ($this->status) {
            case 1:
                $middleman->status = 1;
                $msg = '审核成功';
                $middleman->reason = $this->reason;
                break;
            case 2:
                if ($this->reason === '') {
                    throw new \Exception('拒绝申请必须填写理由');
                }
                $middleman->status = 2;
                $middleman->reason = $this->reason;
                $msg = '拒绝成功';
                break;
            default:
                throw new \Exception('错误的操作');
        }
        if (!$middleman->save()) {
            throw new \Exception($this->getErrorMsg($middleman));
        }
        if ($this->status == 2 && $middleman->pay_price > 0) {
            \Yii::$app->payment->refund($middleman->token, $middleman->pay_price);
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => $msg,
        ];
    }

    public function getCount()
    {
        $count = CommunityMiddleman::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'status' => 0,
            'is_delete' => 0
        ])->count();
        return $count;
    }
}
