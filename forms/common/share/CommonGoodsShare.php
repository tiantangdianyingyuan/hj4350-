<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/13
 * Time: 11:58
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\share;


use app\models\Goods;
use app\models\Model;
use yii\db\Exception;

class CommonGoodsShare extends Model
{
    /**
     * @return CommonGoodsShare
     */
    public static function getCommonGoodsShare()
    {
        return new CommonGoodsShare();
    }

    /**
     * @param $params
     * @return \app\models\GoodsShare|null
     * @throws Exception
     */
    public function getGoodsShare($params)
    {
        if ($params instanceof Goods) {
            $goods = $params;
        } elseif (is_numeric($params)) {
            $goods = Goods::findOne($params);
        } else {
            throw new Exception('错误的参数,$param必须是\app\models\GoodsAttr的对象或对象ID');
        }
        $goodsShare = null;
        foreach ($goods->share as $item) {
            if ($item->goods_attr_id == 0) {
                $goodsShare = $item;
            }
        }
        return $goodsShare;
    }
}
