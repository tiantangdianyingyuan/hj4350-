<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/14
 * Time: 13:58
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\api;


use app\plugins\community\forms\common\CommonMiddleman;
use app\plugins\community\forms\Model;
use app\plugins\community\models\CommunityAddress;
use app\plugins\community\models\CommunityMiddleman;

class MiddlemanListForm extends Model
{
    public $longitude;
    public $latitude;
    public $page;

    public function rules()
    {
        return [
            [['longitude', 'latitude'], 'required'],
            [['longitude', 'latitude'], 'number'],
            ['page', 'integer'],
            ['page', 'default', 'value' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [
            'longitude' => '定位地址',
            'latitude' => '定位地址',
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $list = CommunityMiddleman::find()->alias('m')->with(['address', 'user'])
                ->where(['m.mall_id' => \Yii::$app->mall->id, 'm.is_delete' => 0, 'm.status' => 1])
                ->leftJoin(['a' => CommunityAddress::tableName()], 'a.user_id=m.user_id')
                ->select(['m.*', "(st_distance(point(a.longitude, a.latitude), point($this->longitude, $this->latitude)) * 111195) as distance"])
                ->orderBy(['distance' => SORT_ASC, 'm.id' => SORT_ASC])
                ->apiPage(20, $this->page)
                ->all();
            $commonMiddleman = CommonMiddleman::getCommon();
            /* @var CommunityMiddleman[] $list */
            $newList = [];
            foreach ($list as $middleman) {
                $newItem = $commonMiddleman->getMiddleman($middleman);
                $longitude = floatval($middleman->address->longitude);
                $latitude = floatval($middleman->address->latitude);
                $newItem['distance'] = get_distance($longitude, $latitude, $this->longitude, $this->latitude);
                $newList[] = $newItem;
            }
            return $this->success([
                'list' => $newList
            ]);
        } catch (\Exception $exception) {
            return $this->fail([
                'msg' => $exception->getMessage()
            ]);
        }
    }
}
