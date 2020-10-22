<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\common\clerk_user;


use app\models\ClerkUser;
use app\models\Model;
use yii\db\Query;

class CommonClerkUser extends Model
{
    /**
     * @var Query $query
     */
    public $query;
    public $pagination;
    public $is_pagination;
    public $clerkUser;

    public $mall_id;
    public $all_mch;
    public $mch_id;
    public $limit;
    public $page;
    public $keyword;
    public $is_array;
    public $sort;
    /**
     * 关联关系
     * @var
     */
    public $is_user;
    public $is_mch;

    public function rules()
    {
        return [
            [['keyword'], 'trim'],
            [['mall_id', 'limit', 'mch_id', 'is_array', 'limit', 'sort',
                'is_pagination', 'is_user', 'is_mch', 'all_mch'], 'integer'],
            [['limit',], 'default', 'value' => 10],
            [['sort', 'mch_id', 'is_array'], 'default', 'value' => 0],
            [['page', 'is_pagination'], 'default', 'value' => 1],
        ];
    }

    /**
     * @param $key
     * @return mixed|null
     * 获取字段对应的设置sql方法
     */
    private function getMethod($key)
    {
        $array = [
            'keyword' => 'setKeyword',
            'sort' => 'setSortWhere',
            'all_mch' => 'setAllMch',
            'mch_id' => 'setMchId',
            'is_mch' => 'setWithMch',
            'is_user' => 'setWithUser',
        ];
        return isset($array[$key]) ? $array[$key] : null;
    }

    //持续改进
    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $this->query = $query = ClerkUser::find()->alias('u')->where([
                'u.mall_id' => \Yii::$app->mall->id,
                'u.is_delete' => 0,
            ]);
            foreach ($this->attributes as $key => $value) {
                $method = $this->getMethod($key);
                if ($method && method_exists($this, $method) && $value !== null) {
                    $this->$method();
                }
            }
            if ($this->is_pagination) {
                $this->query->page($this->pagination, $this->limit, $this->page);
            }
            $list = $this->query->asArray($this->is_array)->groupBy('u.id')->all();

            return $list;
        } catch (\Exception $e) {
            throw $e;
        }
    }


    private function setKeyword()
    {
        // $this->query->andWhere(['LIKE', 'u.nickname', $this->keyword]);
    }

    private function setAllMch()
    {
        $this->query->andWhere(['>', 'u.mch_id', 0]);
    }

    private function setMchId()
    {
        $this->query->andWhere(['u.mch_id' => $this->mch_id]);
    }

    private function setWithMch()
    {
        $this->query->with('mch');
    }

    private function setWithUser()
    {
        $this->query->with('user');
    }

    private function setSortWhere()
    {
        switch ($this->sort) {
            case 1:
                $this->query->orderBy(['u.created_at' => SORT_DESC]);
                break;
            default:
        }
    }
}
