<?php


namespace app\plugins\quick_share\forms\api;

use app\core\response\ApiCode;
use app\models\Goods;
use app\models\Model;
use app\plugins\quick_share\forms\wechat\Config;
use app\plugins\quick_share\forms\wechat\Tools;
use yii\db\Exception;

class WechatForm extends Model
{
    use Config;

    public $url;
    public $id;

    public function rules()
    {
        return [
            [['url'], 'required'],
            [['url', 'id'], 'string'],
        ];
    }

    public function getInfo()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $ticket = $this->getOption()->getAccountToken()->getJsTicket();
            $sha = array(
                "jsapi_ticket" => $ticket,
                "timestamp" => empty($timestamp) ? time() : $timestamp,
                "noncestr" => Tools::createNoncestr(16),
                "url" => trim($this->url),
            );

            $data = array(
                'debug' => false,
                "url" => $this->url,
                "appId" => $this->app_id,
                "nonceStr" => $sha['noncestr'],
                "timestamp" => $sha['timestamp'],
                "signature" => Tools::getSignature($sha, 'sha1'),
                'jsApiList' => array(
                    'updateTimelineShareData',
                    'updateAppMessageShareData'
                )
            );
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'config' => $data,
                    'share' => $this->getGoodsInfo()
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'data' => $e->getMessage(),
            ];
        }
    }

    /**
     * 商品信息
     * @return array
     * @throws Exception
     */
    private function getGoodsInfo()
    {
        $goods = Goods::find()->where([
            'id' => base64_decode($this->id),
            'is_delete' => 0
        ])->with(['goodsWarehouse'])->one();
        if (!$goods) {
            throw new Exception('商品不存在');
        }
        $pic_url = \yii\helpers\BaseJson::decode($goods->goodsWarehouse->pic_url);
        return array(
            'title' => $goods->goodsWarehouse->name,
            'link' => $this->url,
            'imgUrl' => empty($pic_url) ? '' : $pic_url[0]['pic_url']
        );
    }
}