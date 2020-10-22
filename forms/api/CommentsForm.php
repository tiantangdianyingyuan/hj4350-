<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/21
 * Time: 14:43
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\api;


use app\core\response\ApiCode;
use app\models\Goods;
use app\models\Mall;
use app\models\Model;
use app\models\OrderComments;

/**
 * @property Mall $mall
 */
class CommentsForm extends Model
{
    public $mall;

    public $goods_id;
    public $page;
    public $limit;
    public $status;

    public function rules()
    {
        return [
            ['goods_id', 'required'],
            [['goods_id', 'page', 'limit', 'status'], 'integer'],
            ['page', 'default', 'value' => 1],
            ['limit', 'default', 'value' => 20],
            ['status', 'default', 'value' => 0]
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $setting = $this->mall->getMallSetting(['is_comment']);
        if ($setting['is_comment'] == 0) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '',
                'data' => [
                    'comments' => [],
                    'comment_count' => [],
                ]
            ];
        }
        $goods = Goods::findOne($this->goods_id);

        $list = OrderComments::find()
            ->where([
                'goods_warehouse_id' => $goods->goods_warehouse_id, 'mall_id' => $this->mall->id, 'is_delete' => 0,
                'is_show' => 1
            ])
            ->keyword($this->status, ['score' => $this->status])
            ->select(['*', 'time' => 'case when `is_virtual` = 1 then `virtual_time` else `created_at` end'])
            ->with('user')->apiPage($this->limit, $this->page)
            ->orderBy(['is_top' => SORT_DESC, 'created_at' => SORT_DESC])
            ->all();

        $newList = [];
        /* @var OrderComments[] $list */
        foreach ($list as $item) {
            $goods_info = \yii\helpers\BaseJson::decode($item['goods_info']);
            if (empty($goods_info)) {
                $attr_name = '';
            } else {
                $attr = array_column($goods_info['attr_list'], 'attr_name');
                $attr_name = rtrim(join(',', $attr), ',');
            };
            $newItem = [
                'content' => $item->content,
                'pic_url' => \Yii::$app->serializer->decode($item->pic_url),
                'reply_content' => $item->reply_content,
                'attr_name' => $attr_name,
            ];
            if ($item->is_virtual == 1) {
                $newItem['avatar'] = $item->virtual_avatar;
                $newItem['time'] = date('Y-m-d', strtotime($item->virtual_time));
                $newItem['nickname'] = $this->substrCut($item->virtual_user);
            } else {
                $newItem['avatar'] = $item->user->userInfo->avatar;
                $newItem['time'] = date('Y-m-d', strtotime($item->created_at));
                $newItem['nickname'] = $this->substrCut($item->user->nickname);
            }
            if ($item->is_anonymous == 1) {
                $newItem['avatar'] = \Yii::$app->request->hostInfo .
                    \Yii::$app->request->baseUrl . '/statics/img/common/default-avatar.png';
                $newItem['nickname'] = '匿名用户';
            }
            $newList[] = $newItem;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'comments' => $newList,
                'comment_count' => $this->countData($goods),
            ]
        ];
    }

    private function countData($goods)
    {
        $list = OrderComments::find()
            ->where([
                'goods_warehouse_id' => $goods->goods_warehouse_id, 'mall_id' => $this->mall->id, 'is_delete' => 0,
                'is_show' => 1
            ])
            ->select([
                'count(1) score_all', 'SUM(IF(score = 3, 1, 0)) score_3',
                'SUM(IF(score = 2, 1, 0)) score_2', 'SUM(IF(score = 1, 1, 0)) score_1'
            ])->asArray()->one();
        $list = array_map(function ($v) {
            if (!$v) {
                $v = 0;
            }
            return $v;
        }, $list);
        $newList = [];
        foreach ($list as $key => $value) {
            switch ($key) {
                case 'score_all':
                    $name = '全部';
                    $index = 0;
                    break;
                case 'score_3':
                    $name = '好评';
                    $index = 3;
                    break;
                case 'score_2':
                    $name = '中评';
                    $index = 2;
                    break;
                case 'score_1':
                    $name = '差评';
                    $index = 1;
                    break;
                default:
                    $name = $key;
                    $index = 0;
            }
            $newList[] = [
                'name' => $name,
                'count' => $value,
                'index' => $index,
            ];
        }
        return $newList;
    }

    // 将用户名 做隐藏
    private function substrCut($user_name)
    {
        $strlen = mb_strlen($user_name, 'utf-8');
        $firstStr = mb_substr($user_name, 0, 1, 'utf-8');
        $lastStr = mb_substr($user_name, -1, 1, 'utf-8');
        return $strlen <= 2 ? $firstStr . '*' : $firstStr . str_repeat("*", 2) . $lastStr;
    }
}
