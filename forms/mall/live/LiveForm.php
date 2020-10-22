<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\live;

use app\core\response\ApiCode;
use app\forms\common\CommonQrCode;
use app\forms\mall\live\CommonLive;
use app\models\Model;

class LiveForm extends Model
{
    public $room_id;
    public $is_refresh = 0;
    public $page = 1;

    private $limit = 20;
    private $second = 60;

    public function rules()
    {
        return [
            [['room_id', 'page', 'is_refresh'], 'integer'],
        ];
    }

    public function getList()
    {
        try {
            $accessToken = \Yii::$app->getWechat()->getAccessToken();
            if (!$accessToken) {
                throw new \Exception('微信配置有误');
            }
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }

        $cacheKey = $this->getCacheKey();
        $res = \Yii::$app->cache->get($cacheKey);
        if (!$res || $this->is_refresh) {
            try {
                // 接口每天上限调用10000次
                $api = "https://api.weixin.qq.com/wxa/business/getliveinfo?access_token={$accessToken}";
                $res = CommonLive::post($api, [
                    'start' => $this->page * $this->limit - $this->limit,
                    'limit' => $this->limit,
                ]);
                $res = json_decode($res->getBody()->getContents(), true);
            } catch (\Exception $exception) {
                $res = [
                    'errcode' => 0,
                    'room_info' => [],
                    'total' => 0,
                ]; 
            }
        }

        if ($res['errcode'] == 0) {
            \Yii::$app->cache->set($cacheKey, $res, $this->second);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => "请求成功",
                'data' => [
                    'list' => $this->getNewList($res['room_info']),
                    'pageCount' => ceil($res['total'] / $this->limit),
                    'total' => $res['total'],
                ],
            ];
        } else if ($res['errcode'] == 1 || $res['errcode'] == 9410000) {
            \Yii::$app->cache->set($cacheKey, $res, $this->second);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $res['errmsg'],
                'data' => [
                    'list' => [],
                    'pageCount' => 0,
                    'total' => 0,
                    'errmsg' => $res['errmsg'],
                ],
            ];
        } else {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $res['errmsg'],
            ];
        }
    }

    private function getCacheKey()
    {
        return 'LIVE_LIST_' . \Yii::$app->mall->id . $this->page;
    }

    private function getNewList($list)
    {
        $newList = [];
        foreach ($list as $item) {
            $item = $this->getApiData($item);
            $item['start_time'] = date('Y-m-d H:i:s', $item['start_time']);
            $item['end_time'] = date('Y-m-d H:i:s', $item['end_time']);
            $item['status_text'] = $this->getLiveStatusText($item['live_status']);

            if (isset($item['share_img']) && $item['share_img']) {
                $item['anchor_img'] = $item['share_img'];
            }

            foreach ($item['goods'] as &$goods) {
                $goods['price'] = price_format($goods['price'] / 100, 2);
                $goods['price2'] = price_format($goods['price2'] / 100, 2);
            }
            unset($goods);

            $newList[] = $item;
        }

        return $newList;
    }

    private function getApiData($item)
    {

        $item['text_time'] = date('H:i', $item['start_time']);
        // 今日预告
        if ($item['live_status'] === 102 || date('Y-m-d', $item['start_time']) == date('Y-m-d', time())) {
            $item['text_time'] = '今天' . date('H:i', $item['start_time']) . '开播';
        }

        // 长预告
        if (date('Y-m-d', $item['start_time']) > date('Y-m-d', time())) {
            $item['text_time'] = date('m', $item['start_time']) . '-' . date('d', $item['start_time']) . ' ' . date('H:i', $item['start_time']) . '开播';
        }

        // 判断时间上是否已结束
        if ($item['end_time'] < time()) {
            $item['live_status'] = 103;
        }

        return $item;
    }

    private function getLiveStatusText($status)
    {
        switch ($status) {
            case 101:
                $statusText = '直播中';
                break;
            case 102:
                $statusText = '预告';
                break;
            case 103:
                $statusText = '已结束';
                break;
            case 104:
                $statusText = '禁播';
                break;
            case 105:
                $statusText = '暂停中';
                break;
            case 106:
                $statusText = '异常';
                break;
            default:
                $statusText = '未知错误';
                break;
        }
        return $statusText;
    }

    public function getQrCode()
    {
        $token = \Yii::$app->security->generateRandomString();
        $form = new CommonQrCode();
        $form->appPlatform = APP_PLATFORM_WXAPP;

        $result = $form->getQrCode(['type' => 9, 'room_id' => $this->room_id], 430, 'plugin-private://wx2b03c6e691cd7370/pages/live-player-plugin'
        );

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'file_path' => $result['file_path'],
            ],
        ];
    }
}
