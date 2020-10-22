<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\common\goods;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\forms\common\mptemplate\MpTplMsgCSend;
use app\forms\common\mptemplate\MpTplMsgSend;
use app\models\Goods;
use app\models\Model;
use app\models\Option;
use app\plugins\mch\models\MchGoods;

class PluginMchGoods extends Model
{
    public $goods_id;
    public $mch_id;

    public function rules()
    {
        return [
            [['goods_id', 'mch_id'], 'required'],
            [['goods_id', 'mch_id'], 'integer'],
        ];
    }

    /**
     * 多商户申请上架
     * @return array
     */
    public function applyStatus()
    {
        try {
            $mchGoods = MchGoods::findOne([
                'goods_id' => $this->goods_id,
                'mch_id' => $this->mch_id,
                'mall_id' => \Yii::$app->mall->id
            ]);
            if (!$mchGoods) {
                throw new \Exception('商品不存在');
            }
            $mchGoods->status = 1;
            $mchGoods->remark = '申请上架';
            $res = $mchGoods->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($mchGoods));
            }
            $this->sendMpTplMsg();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '申请成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    /**
     * 发给管理员公众号消息
     */
    private function sendMpTplMsg()
    {
        try {
            $option = CommonOption::get(Option::NAME_WX_PLATFORM, \Yii::$app->mall->id, Option::GROUP_APP);
            $customize = new MpTplMsgCSend();
            $newAdminOptionList = [];
            foreach ($option['admin_open_list'] as $item) {
                $newAdminOptionList[] = json_encode($item);
            }
            $customize->admin_open_list = $newAdminOptionList;
            $sign = false;
            foreach ($option['template_list'] as $item) {
                if ($item['key_name'] == 'mch_good_apply_tpl') {
                    $customize->template_id = $item['mch_good_apply_tpl'];
                    $sign = true;
                }
            }
            if (!$sign) {
                throw new \Exception('未找到公众号模板ID');
            }
            $customize->app_id = $option['app_id'];
            $customize->app_secret = $option['app_secret'];

            $goods = Goods::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->goods_id
            ]);

            $tplMsg = new MpTplMsgSend();
            $tplMsg->method = 'mchGoodApplyTpl';
            $tplMsg->params = [
                'goods' => $goods->goodsWarehouse->name
            ];
            $tplMsg->sendTemplate($customize);
        } catch (\Exception $exception) {
            \Yii::error('公众号模板消息发送: ' . $exception->getMessage());
        }
    }
}