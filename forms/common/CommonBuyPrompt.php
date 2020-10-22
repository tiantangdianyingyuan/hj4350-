<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\common;

class CommonBuyPrompt
{
    public $goods_name;
    public $nickname;
    public $url;
    public $avatar;

    private $goods_key;
    private $cache;

    public function __construct()
    {
        $this->cache = \Yii::$app->cache;
        $this->goods_key = sprintf('buy_info_%s', \Yii::$app->mall->id);
    }

    public function get()
    {
        $data = $this->cache->get($this->goods_key);

        if ($this->cache->exists($this->goods_key)) {
            $nickname = mb_strlen($data['nickname'], 'UTF-8') > 5 ? mb_substr($data['nickname'], 0, 4, 'UTF-8') . '...' : $data['nickname'];
            $goods_name = mb_strlen($data['goods_name'], 'UTF-8') > 8 ? mb_substr($data['goods_name'], 0, 7, 'UTF-8') . '...' : $data['goods_name'];
            $diff = time() - (int)$data['time'];

            $minute = floor($diff / 60);
            $second = $diff - $minute * 60;
            $time_str = $minute === 0 ? sprintf('%u秒前', $second) : sprintf('%u分%u秒前', $minute, $second);

            return [
                'content' => sprintf('%s购买了%s,', $nickname, $goods_name),
                'time_str' => $time_str,
                'avatar' => $data['avatar'],
                'url' => $data['url'],
            ];
        }
        return null;
    }

    public function set()
    {
        $data = [
            'mall_id' => \Yii::$app->mall->id,
            'nickname' => $this->nickname,
            'goods_name' => $this->goods_name,
            'url' => $this->url,
            'time' => time(),
            'avatar' => $this->avatar,
        ];
        $this->cache->set($this->goods_key, $data, 300);
    }
}
