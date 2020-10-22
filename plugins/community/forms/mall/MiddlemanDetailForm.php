<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/7/13
 * Time: 17:15
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\mall;


use app\core\Pagination;
use app\models\User;
use app\plugins\community\forms\common\CommonMiddleman;
use app\plugins\community\forms\Model;
use app\plugins\community\models\CommunityRelations;

class MiddlemanDetailForm extends Model
{
    public $user_list;
    public $page;
    public $id;
    public $prop;
    public $prop_value;

    public function rules()
    {
        return [
            [['prop', 'prop_value'], 'trim'],
            [['prop', 'prop_value'], 'string'],
            ['prop', 'in', 'range' => ['id', 'name']],
            [['id'], 'integer'],
            ['user_list', 'safe'],
            ['page', 'default', 'value' => 1]
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $common = CommonMiddleman::getCommon();
        $middleman = $common->getConfig($this->id);
        if (!$middleman) {
            throw new \Exception('所选用户不是团长');
        }
        $condition = [];
        if ($this->prop_value !== '') {
            switch ($this->prop) {
                case 'id':
                    $condition = ['user_id' => $this->prop_value];
                    break;
                case 'name':
                    $userId = User::find()
                        ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
                        ->andWhere(['like', 'nickname', $this->prop_value])
                        ->select('id');
                    $condition = ['user_id' => $userId];
                    break;
                default:
            }
        }
        /**
         * @var CommunityRelations[] $list
         * @var Pagination $pagination
         */
        $list = CommunityRelations::find()->with('user.userInfo')
            ->where(['middleman_id' => $middleman->user_id, 'is_delete' => 0])
            ->keyword(!empty($condition), $condition)
            ->page($pagination, 20, $this->page)
            ->all();
        $newList = [];
        foreach ($list as $item) {
            $newList[] = [
                'id' => $item->user_id,
                'nickname' => $item->user->nickname,
                'avatar' => $item->user->userInfo->avatar
            ];
        }
        $array = $common->getMiddleman($middleman);
        $array['children_count'] = $pagination->total_count;
        return $this->success([
            'msg' => '',
            'middleman' => $array,
            'list' => $newList,
            'pagination' => $pagination
        ]);
    }

    public function relieve()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            if (!$this->user_list || empty($this->user_list)) {
                throw new \Exception('请选择需要解除关系的用户');
            }
            $count = CommunityRelations::updateAll(
                ['middleman_id' => 0],
                ['user_id' => $this->user_list, 'middleman_id' => $this->id, 'is_delete' => 0]
            );
            return $this->success(['msg' => '解除成功', 'count' => '总共' . $count . '人被解除']);
        } catch (\Exception $exception) {
            return $this->fail(['msg' => $exception->getMessage()]);
        }
    }
}
