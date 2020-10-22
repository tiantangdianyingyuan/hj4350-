<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/7
 * Time: 15:59
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\mall;


use app\models\User;
use app\plugins\community\forms\common\CommonMiddleman;
use app\plugins\community\forms\Model;
use app\plugins\community\models\CommunityMiddleman;

class MiddlemanListForm extends Model
{
    public $status;
    public $page;
    public $date_start;
    public $date_end;
    public $prop;
    public $prop_value;

    public function rules()
    {
        return [
            [['status', 'page'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['prop', 'prop_value', 'date_start', 'date_end'], 'trim'],
            [['prop', 'prop_value', 'date_start', 'date_end'], 'string'],
            ['prop', 'in', 'range' => ['id', 'name', 'mobile', 'nickname']],
            ['status', 'default', 'value' => -2],
            ['status', 'in', 'range' => [-2, -1, 0, 1, 2]]
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $condition = [];
        if ($this->prop_value !== '') {
            switch ($this->prop) {
                case 'id':
                    $condition[] = ['user_id' => $this->prop_value];
                    break;
                case 'name':
                    $condition[] = ['like', 'name', $this->prop_value];
                    break;
                case 'mobile':
                    $condition[] = ['like', 'mobile', $this->prop_value];
                    break;
                case 'nickname':
                    $userId = User::find()
                        ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
                        ->andWhere(['like', 'nickname', $this->prop_value])
                        ->select('id');
                    $condition[] = ['user_id' => $userId];
                    break;
                default:
            }
        }
        switch ($this->status) {
            case -1:
                $condition[] = ['status' => -1];
                break;
            case 0:
                $condition[] = ['status' => 0];
                break;
            case 1:
                $condition[] = ['status' => 1];
                break;
            case 2:
                $condition[] = ['status' => 2];
                break;
            default:
                $condition[] = ['status' => [-1, 0, 1, 2]];
        }
        // 筛选条件合并
        if (!empty($condition)) {
            array_unshift($condition, 'and');
        }
        $list = CommunityMiddleman::find()->with('address')
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->keyword($this->date_start, ['>=', 'apply_at', $this->date_start])
            ->keyword($this->date_end, ['<=', 'apply_at', $this->date_end])
            ->keyword(!empty($condition), $condition)
            ->orderBy(['status' => SORT_ASC, 'apply_at' => SORT_DESC])
            ->page($pagination, 20, $this->page)
            ->all();
        /* @var CommunityMiddleman[] $list */
        $newList = [];
        $common = CommonMiddleman::getCommon();
        foreach ($list as $middleman) {
            $newList[] = $common->getMiddleman($middleman);
        }
        return $this->success(['list' => $newList, 'pagination' => $pagination]);
    }
}
