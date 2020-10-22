<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/24
 * Time: 14:11
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\api;


use app\plugins\community\forms\common\CommonMiddleman;
use app\plugins\community\forms\Model;

class MiddlemanForm extends Model
{
    public $middleman_id;

    public function rules()
    {
        return [
            ['middleman_id', 'required'],
            ['middleman_id', 'integer'],
        ];
    }

    public function bind()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $common = CommonMiddleman::getCommon();
            $relation = $common->getParent(\Yii::$app->user->id);
            if (!$relation || $relation->middleman_id == 0) {
                return $this->success(['msg' => '自然流量不需要绑定团长', 'is_private' => true]);
            }
            $middleman = $common->getConfig($this->middleman_id);
            $common->bindMiddleman($middleman, \Yii::$app->user->id, true);
            return $this->success(['msg' => '绑定成功', 'is_private' => false]);
        } catch (\Exception $exception) {
            return $this->fail(['msg' => $exception->getMessage()]);
        }
    }
}
