<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/7
 * Time: 15:59
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\mall;


use app\plugins\community\forms\common\CommonMiddleman;
use app\plugins\community\forms\Model;
use app\plugins\community\models\CommunityRelations;

class MiddlemanForm extends Model
{
    public $status;
    public $id;
    public $reason;
    public $content;

    public function rules()
    {
        return [
            [['reason', 'content'], 'trim'],
            [['reason', 'content'], 'string'],
            [['status', 'id'], 'integer'],
            ['status', 'default', 'value' => -1],
            ['status', 'in', 'range' => [1, 2, 3, -1]],
        ];
    }

    public function check()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $common = CommonMiddleman::getCommon();
            $middleman = $common->getConfigById($this->id);
            if (!$middleman) {
                throw new \Exception('团长不存在，或已被解除');
            }
            switch ($this->status) {
                case 1:
                    $middleman->status = 1;
                    $middleman->delete_first_show = 1;
                    $middleman->become_at = mysql_timestamp();
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
                case 3:
                    if ($this->reason === '') {
                        throw new \Exception('解除团长必须填写理由');
                    }
                    $middleman->status = 3;
                    $middleman->reason = $this->reason;
                    $msg = '解除成功';
                    break;
                case -1:
                    if ($this->content == '') {
                        throw new \Exception('必须填写备注');
                    }
                    $middleman->content = $this->content;
                    $msg = '备注填写成功';
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
            if ($this->status == 3) {
                // 解除团长时，删除所有关系
                CommunityRelations::updateAll(['middleman_id' => 0], ['middleman_id' => $middleman->user_id]);
            }
            return $this->success(['msg' => $msg]);
        } catch (\Exception $exception) {
            return $this->fail(['msg' => $exception->getMessage()]);
        }
    }
}
