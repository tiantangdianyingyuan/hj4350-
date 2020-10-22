<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\live;

use app\core\response\ApiCode;
use app\models\Model;

class LiveAddGoods extends Model
{
    public $room_id;
    public $goods_list;

    private $goodsIds;

    public function rules()
    {
        return [
            [['room_id'], 'required'],
            [['room_id'], 'integer'],
            [['goods_list'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'room_id' => '直播间ID',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $this->checkData();
            $accessToken = CommonLive::checkAccessToken();
            // 接口每天上限调用10000次
            $api = "https://api.weixin.qq.com/wxaapi/broadcast/room/addgoods?access_token={$accessToken}";
            $res = CommonLive::post($api, [
                'ids' => $this->goodsIds,
                'roomId' => $this->room_id,
            ]);
            $res = json_decode($res->getBody()->getContents(), true);

            if ($res['errcode'] != 0) {
                throw new \Exception($res['errmsg']);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => "添加成功",
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                ],
            ];
        }
    }

    private function checkData()
    {
        $goodsIds = [];
        if ($this->goods_list && is_array($this->goods_list)) {
            foreach ($this->goods_list as $key => $value) {
                if (isset($value['goods_id'])) {
                    $goodsIds[] = $value['goods_id'];
                }
            }
        }
        $this->goodsIds = $goodsIds;
        if (count($this->goodsIds) <= 0) {
            throw new \Exception("请先添加直播商品");
        }

        if (count($this->goodsIds) > 200) {
            throw new \Exception("直播间最多可添加200个商品");
        }
    }
}
