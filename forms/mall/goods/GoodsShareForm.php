<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/12/4
 * Time: 18:05
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\mall\goods;


use app\models\GoodsShare;
use app\models\Model;

class GoodsShareForm extends Model
{
    public $goods_id;
    public $goods_attr_id;
    public $share_commission_first;
    public $share_commission_second;
    public $share_commission_third;
    public $level;

    public function rules()
    {
        return [
            [['share_commission_first', 'share_commission_second', 'share_commission_third'], 'number', 'min' => 0],
            [['share_commission_first', 'share_commission_second', 'share_commission_third'], 'default', 'value' => 0],
            [['goods_id', 'goods_attr_id', 'level'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'share_commission_first' => '一级分销佣金',
            'share_commission_second' => '二级分销佣金',
            'share_commission_third' => '三级分销佣金',
            'level' => '分销商等级',
        ];
    }

    /**
     * @throws \Exception
     */
    public function save()
    {
        if (!$this->validate()) {
            throw new \Exception($this->getErrorMsg());
        }
    }
}
