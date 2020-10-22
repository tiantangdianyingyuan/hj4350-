<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\forms\common;


use app\forms\common\goods\CommonGoodsStatistic;
use app\forms\common\order\CommonOrderStatistic;
use app\models\Model;
use app\models\Store;
use app\models\User;
use app\plugins\mch\models\Mch;
use app\plugins\mch\Plugin;

class CommonMchForm extends Model
{
    public $keyword;
    public $page;
    //
    public $id;
    public $is_review_status;

    public function getList()
    {
        $query = Mch::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'review_status' => 1,
        ]);

        if ($this->keyword) {
            $mchIds = Store::find()->where(['like', 'name', $this->keyword])->select('mch_id');
            $userIds = User::find()->where(['like', 'nickname', $this->keyword])->andWhere(['mall_id' => \Yii::$app->mall->id])->select('id');
            $query->andWhere([
                'or',
                ['id' => $mchIds],
                ['user_id' => $userIds],
            ]);
        }

        $list = $query->orderBy(['sort' => SORT_ASC])
            ->with('user.userInfo', 'store', 'category')
            ->page($pagination)->asArray()->all();

        return [
            'list' => $list,
            'pagination' => $pagination
        ];
    }

    /**
     * @param string $type mall--后台数据|api--小程序端接口数据
     * @return array
     * @throws \Exception
     * 获取首页布局的数据
     */
    public function getHomePage($type)
    {
        if ($type == 'mall') {
            $baseUrl = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl;
            $plugin = new Plugin();
            return [
                'list' => [
                    [
                        'key' => $plugin->getName(),
                        'name' => '好店推荐',
                        'relation_id' => 0,
                        'is_edit' => 0
                    ]
                ],
                'bgUrl' => [
                    $plugin->getName() => [
                        'bg_url' => $baseUrl . '/statics/img/mall/home_block/yuyue-bg.png',
                    ]
                ],
                'key' => $plugin->getName()
            ];
        } elseif ($type == 'api') {
            /* @var Mch[] $list*/
            $list = Mch::find()->with('store')->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'review_status' => 1,
                'status' => 1,
                'is_recommend' => 1
            ])->orderBy(['sort' => SORT_ASC, 'created_at' => SORT_DESC])
                ->limit(20)
                ->all();
            $newList = [];
            foreach ($list as $item) {
                $newList[] = [
                    'name' => $item->store->name,
                    'cover_url' => $item->store->cover_url,
                    'mch_id' => $item->id,
                    'id' => $item->id,
                    'picUrl' => $item->store->cover_url,
                ];
            }
            return $newList;
        } else {
            throw new \Exception('无效的数据');
        }
    }

    public function getDetail()
    {
        $query = Mch::find()->where([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);
        if (!$this->is_review_status) {
            $query->andWhere(['review_status' => 1]);
        }

        /** @var Mch $detail */
        $detail = $query->with('user.userInfo', 'mchUser', 'store', 'category')->one();
        if (!$detail) {
            throw new \Exception('商户不存在');
        }

        $detail->form_data = !$detail->form_data ?: \Yii::$app->serializer->decode($detail->form_data);
        $detail->store->pic_url = !$detail->store->pic_url ?: \Yii::$app->serializer->decode($detail->store->pic_url);

        return $detail;
    }
}
