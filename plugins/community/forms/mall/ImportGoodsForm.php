<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/8/24
 * Time: 4:26 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoods;
use app\plugins\community\forms\Model;
use app\plugins\community\models\CommunityActivity;
use app\plugins\community\models\CommunityGoods;
use app\plugins\community\models\CommunityGoodsAttr;
use yii\helpers\ArrayHelper;

class ImportGoodsForm extends Model
{
    public $activity_id;
    public $selected_activity_id;

    public function rules()
    {
        return [
            [['activity_id', 'selected_activity_id'], 'required'],
            [['activity_id', 'selected_activity_id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'activity_id' => '当前活动id',
            'selected_activity_id' => '选择导入商品的活动id',
        ];
    }

    public function import()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $selectActivity = CommunityActivity::findOne([
                'mall_id' => \Yii::$app->mall->id, 'id' => $this->selected_activity_id, 'is_delete' => 0
            ]);
            if (!$selectActivity) {
                throw new \Exception('所选的活动不存在');
            }
            $activity = CommunityActivity::findOne([
                'mall_id' => \Yii::$app->mall->id, 'id' => $this->activity_id, 'is_delete' => 0
            ]);
            if (!$activity) {
                throw new \Exception('活动不存在');
            }
            $common = CommonGoods::getCommon();
            foreach ($selectActivity->communityGoods as $communityGoods) {
                try {
                    $detail = $common->getGoodsDetail($communityGoods->goods_id);
                } catch (\Exception $exception) {
                    continue;
                }
                $detail['status'] = intval($detail['status']);
                foreach ($detail['attr'] as &$item) {
                    $extra_attr = CommunityGoodsAttr::findOne([
                        'goods_id' => $item['goods_id'], 'attr_id' => $item['id'], 'is_delete' => 0
                    ]);
                    $item['supply_price'] = empty($extra_attr) ? 0 : $extra_attr->supply_price;
                }
                unset($item);
                $detail['supply_price'] = ($detail['use_attr'] == 1) ? 0 : $detail['attr'][0]['supply_price'];

                $goodsDetail = $detail;
                $goods = new GoodsEditForm();
                $goods->attributes = $goodsDetail;
                $goods->status = 0;//添加商品，默认下架状态
                $goods->attrGroups = ArrayHelper::toArray($goodsDetail['attr_groups']);
                $goods->attr = $item['attr'] ?? ArrayHelper::toArray($goodsDetail['attr']);
                $goods->member_price = [];
                $goods->status = 1;
                $goods->setSign('community');
                $goods_res = $goods->save();
                if ($goods_res['code'] == ApiCode::CODE_ERROR) {
                    throw new \Exception($goods_res['msg']);
                }
                $communityGoods = new CommunityGoods();
                $communityGoods->goods_id = $goods->goods_id;
                $communityGoods->mall_id = \Yii::$app->mall->id;
                $communityGoods->sort = $goodsDetail['sort'] ?? 100;
                $communityGoods->activity_id = $activity->id;
                $res = $communityGoods->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($communityGoods));
                }
            }
            return $this->success([
                'msg' => '导入成功'
            ]);
        } catch (\Exception $exception) {
            return $this->fail(['msg' => $exception->getMessage()]);
        }
    }
}
