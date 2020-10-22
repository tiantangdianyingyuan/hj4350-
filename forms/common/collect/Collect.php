<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/5/16
 * Time: 10:56
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\collect;


use app\forms\common\collect\collect_data\AlibabaData;
use app\forms\common\collect\collect_data\AppTaobaoData;
use app\forms\common\collect\collect_data\AppTmallData;
use app\forms\common\collect\collect_data\JdData;
use app\forms\common\collect\collect_data\JuTaobaoData;
use app\forms\common\collect\collect_data\PddData;
use app\forms\common\collect\collect_data\SuningData;
use app\forms\common\collect\collect_data\TaobaoData;
use app\forms\common\collect\collect_data\TmallData;
use app\forms\common\CommonOption;
use yii\base\BaseObject;

class Collect extends BaseObject
{
    public static $is_download = 1;
    public static function getData($url)
    {
        $mallId = \Yii::$app->mall->id;
        $mchId = \Yii::$app->user->identity->mch_id;
        $res = CommonOption::get('assistant_api_key', $mallId, 'plugin', null, $mchId);
        if (!$res) {
            throw new \Exception('请先配置采集助手中的api key');
        }
        // 获取链接的域名
        $host = parse_url($url, PHP_URL_HOST);
        if (strpos($host, 'tmall') !== false) {
            $host = 'detail.tmall.com';
        }
        switch ($host) {
            case 'item.taobao.com':
                $form = new TaobaoData();
                $form->type = 0;
                break;
            case 'detail.ju.taobao.com':
                $form = new JuTaobaoData();
                $form->type = 0;
                break;
            case 'h5.m.taobao.com':
                $form = new AppTaobaoData();
                $form->type = 1;
                break;
            case 'detail.tmall.com':
                $form = new TmallData();
                $form->type = 2;
                break;
            case 'detail.m.tmall.com':
                $form = new AppTmallData();
                $form->type = 3;
                break;
            case 'item.jd.com':
                $form = new JdData();
                $form->type = 4;
                break;
            case 'mobile.yangkeduo.com':
            case 'yangkeduo.com':
                $form = new PddData();
                $form->type = 5;
                break;
            case 'detail.1688.com':
            case 'detail.m.1688.com':
            case 'm.1688.com':
                $form = new AlibabaData();
                $form->type = 6;
                break;
            case 'product.suning.com':
                $form = new SuningData();
                $form->type = 7;
                break;
            case 'item.yiyaojd.com':
                $form = new JdData();
                $form->type = 8;
                break;
            case 'item.jd.hk':
                $form = new JdData();
                $form->type = 9;
                break;
            default:
                throw new \Exception('暂未支持的采集方式');
        }
        $form->api_key = $res;
        return $form->getData($url);
    }
}
