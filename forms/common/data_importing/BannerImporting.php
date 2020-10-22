<?php


namespace app\forms\common\data_importing;


use app\forms\PickLinkForm;
use app\models\Banner;
use app\models\MallBannerRelation;
use app\plugins\bargain\models\BargainBanner;
use app\plugins\integral_mall\models\IntegralMallBanners;
use app\plugins\lottery\models\LotteryBanner;
use app\plugins\pintuan\models\PintuanBanners;
use app\plugins\step\models\StepBannerRelation;

/**
 * Class DemoImporting
 * @package app\forms\common\data_importing
 */
class BannerImporting extends BaseImporting
{
    public function import()
    {
        if (!is_array($this->v3Data)) {
            throw new \Exception('数据格式不正确');
        }

        $last_names = array_column($this->v3Data, 'sort');
        array_multisort($last_names, SORT_ASC, $this->v3Data);

        foreach ($this->v3Data as $datum) {
            $this->save($datum);
        }
        return true;
    }

    private function getData($datum)
    {
        $res = PickLink::getNewLink($datum['page_url']);
        $info = [
            'mall_id' => $this->mall->id,
            'pic_url' => $datum['pic_url'],
            'title' => $datum['title'],
            'page_url' => $res['url'],
            'open_type' => isset($res['data']['open_type']) ? $res['data']['open_type'] : PickLinkForm::OPEN_TYPE_2,
            'params' => \Yii::$app->serializer->encode(isset($res['data']['params']) ? $res['data']['params'] : []),
            'is_delete' => $datum['is_delete'],
            'created_at' => mysql_timestamp($datum['addtime']),
            'deleted_at' => '0000-00-00 00:00:00',
            'updated_at' => '0000-00-00 00:00:00',
        ];
        return $info;
    }

    private function childData($banner)
    {
        $info = [
            'mall_id' => $this->mall->id,
            'banner_id' => $banner->attributes['id'],
            'created_at' => $banner->attributes['created_at'],
            'is_delete' => $banner->is_delete,
            'deleted_at' => '0000-00-00 00:00:00',
        ];
        return $info;
    }

    /**
     * @param $datum
     * @return bool
     * 单条数据添加
     * @throws \Exception
     */
    protected function save($datum)
    {
        $banner = new Banner();
        $banner->attributes = $this->getData($datum);
        if (!$banner->save()) {
            throw new \Exception($this->getErrorMsg($banner));
        } else {
            switch ($datum['type']) {
                case 1:
                    $model = new MallBannerRelation();
                    break;
                case 2:
                    $model = new PintuanBanners();
                    break;
                case 3:
                    $model = new IntegralMallBanners();
                    break;
                case 4:
                    $model = new BargainBanner();
                    break;
                case 5:
                    $model = new LotteryBanner();
                    break;
                case 6:
                    $model = new StepBannerRelation();
                    break;
                default:
                    $model = new \StdClass();
                    \Yii::error('TYPE NOT ERROR');
            }
            $model->attributes = $this->childData($banner);
            if (!$model->save()) {
                throw new \Exception($this->getErrorMsg($model));
            }
        }
        return true;
    }
}
