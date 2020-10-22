<?php

namespace app\plugins\lottery\forms\api;

use app\core\response\ApiCode;
use app\forms\common\grafika\CommonFunction;
use app\forms\common\template\TemplateList;
use app\forms\common\video\Video;
use app\models\Model;
use app\plugins\lottery\forms\common\CommonLottery;
use app\plugins\lottery\models\Lottery;
use app\plugins\lottery\models\LotteryBanner;
use app\plugins\lottery\models\LotteryLog;
use yii\helpers\ArrayHelper;

class LotteryForm extends Model
{
    public $id;
    public $goods_id;

    public function rules()
    {
        return [
            [['id', 'goods_id'], 'integer'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'banner_list' => $this->getBanner(),
                'list' => $this->getList(),
                'template_message' => TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, ['lottery_tpl']),
            ]
        ];
    }

    private function getBanner()
    {
        $list = LotteryBanner::find()->where([
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id
            ])
            ->with('banner')
            ->asArray()
            ->all();

        $list = array_map(function ($item) {
            return $item['banner'];
        }, $list);

        return $list;
    }

    // 获得商品信息
    private function getList()
    {
        $query = Lottery::find()->select(["l.*","ll.user_status lstatus", "ln.lottery_log_count"])->alias('l')->where([
                'AND',
                ['l.mall_id' => \Yii::$app->mall->id],
                ['l.is_delete' => 0],
                ['l.status' => 1],
                ['l.type' => 0],
                //['<=', 'l.start_at', date('Y-m-d H:i:s')],
                ['>=', 'l.end_at', date('Y-m-d H:i:s')]
            ])
            ->leftJoin([
                'll' => LotteryLog::find()
                        ->select('lottery_id, COUNT(1) user_status')
                        ->where([
                                'mall_id' => \Yii::$app->mall->id,
                                'user_id' => \Yii::$app->user->id,
                                'child_id' => 0
                            ])->groupBy('lottery_id'),

                ], 'll.lottery_id = l.id')
            ->leftJoin([
                'ln' => LotteryLog::find()
                        ->select('lottery_id, COUNT(1) lottery_log_count')
                        ->where([
                                'AND',
                                ['mall_id' => \Yii::$app->mall->id],
                                ['child_id' => 0],
                                ['in', 'status', [1,2,3,4]]
                            ])->groupBy('lottery_id'),
                ], 'ln.lottery_id = l.id')
            ->innerJoinWith(["goods g" => function ($query) {
                $query->where(['g.is_delete' => 0])->with('goodsWarehouse');
            }])
            ->with(['goods.attr']);

        $list = $query->groupBy('l.id')->page($pagination, 10)
                ->orderBy('ll.user_status ASC, sort ASC, end_at DESC, id DESC')
                ->asArray()
                ->all();

        $new_list = [];
        foreach ($list as $v) {
            $new_list[] = [
                'id' => $v['id'],
                'goods_id' => $v['buy_goods_id'],
                'stock' => $v['stock'],
                'end_at' => $v['end_at'],
                'start_at' => $v['start_at'],
                'status' => $v['lstatus'] ? 1 : 0,
                'new_status' => $v['start_at'] < date('Y-m-d H:i:s') ? $v['lstatus'] ? 1 : 0 : 2,
                'lottery_log_count' => $v['lottery_log_count'] ?: 0,
                'cover_pic' => $v['goods']['goodsWarehouse']['cover_pic'],
                'goods_name' => $v['goods']['goodsWarehouse']['name'],
                'price' => $v['goods']['price'],
                'video_url' => Video::getUrl($v['goods']['goodsWarehouse']['video_url']),
            ];
        }
        return $new_list;
    }

    public function setting()
    {
        $setting = CommonLottery::getSetting();
        $setting = ArrayHelper::toArray($setting);

        $func = function ($data, $key, &$rand = 0) {
            if (!empty($data) && is_array($data)) {
                $rand = array_rand($data, 1);
                $pic = $data[$rand][$key];

                /* oos 转 当前域名*/
                if (isset($pic) && (stripos($pic, 'http://') === 0 || stripos($pic, 'https://') === 0)) {
                    $local = CommonFunction::saveTempImage($pic);
                    $pic = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/../runtime/image/' . substr($local, strripos($local, '/') + 1);
                    $pic = str_replace('http://', 'https://', $pic);
                }
                return $pic;
            } else {
                return '';
            }
        };

        $rand = '';
        $setting['cs_wechat_qrcode'] = $func($setting['cs_wechat'], 'qrcode_url', $rand);
        $setting['cs_wechat'] = $setting['cs_wechat'][$rand]['name'] ?? '';

        $setting['cs_wechat_flock_qrcode'] = $func($setting['cs_wechat_flock_qrcode_pic'], 'url');
        unset($setting['cs_wechat_flock_qrcode_pic']);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'setting' => $setting,
            ]
        ];
    }
}
