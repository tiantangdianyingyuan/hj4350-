<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\cat;


use app\core\response\ApiCode;
use app\events\GoodsCatEvent;
use app\forms\mall\export\GoodsCatExport;
use app\models\GoodsCards;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\models\Model;
use yii\helpers\ArrayHelper;

class CatForm extends Model
{
    public $id;
    public $page;
    public $keyword;
    public $mch_id;
    public $sort;

    public $first_list;
    public $second_list;
    public $third_list;

    public $choose_list;
    public $flag;

    public function rules()
    {
        return [
            [['id', 'mch_id', 'sort'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['keyword', 'first_list', 'second_list', 'third_list', 'flag'], 'string'],
            [['keyword'], 'trim'],
            [['choose_list'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '角色ID',
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if ($this->keyword) {
            return $this->getListByKeyword();
        } elseif ($this->id) {
            /* @var GoodsCats $cat */
            $cat = GoodsCats::find()->with('parent')->where([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'is_delete' => 0,
                'id' => $this->id
            ])->one();
            if (!$cat) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '无效的分类'
                ];
            }
            if ($cat->parent_id == 0) {
                $id = $cat->id;
            } elseif ($cat->parent) {
                if ($cat->parent->parent_id == 0) {
                    $id = $cat->parent_id;
                } else {
                    $id = $cat->parent->parent_id;
                }
            } else {
                $id = $cat->id;
            }
            $list = $this->getDefault($id);
            $this->checkId($list);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $this
                ]
            ];
        } else {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $this->getDefault()
                ]
            ];
        }
    }

    public function getAllList()
    {
        try {
            $query = GoodsCats::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'is_delete' => 0,
            ]);

            if ($this->keyword) {
                $query->andWhere(['like', 'name', $this->keyword]);
            }
            $list = $query->andWhere(['parent_id' => 0])->with('child.child')->all();
            $newList = [];
            /** @var GoodsCats[] $list */
            foreach ($list as $item) {
                $newItem = [];
                $newItem['value'] = $item->id;
                $newItem['label'] = $item->name;

                if (isset($item->child) && count($item->child)) {
                    $newChildrenList = [];
                    foreach ($item->child as $children) {
                        $newChildren = [];
                        $newChildren['value'] = $children->id;
                        $newChildren['label'] = $children->name;
                        $newChildrenList[] = $newChildren;
                    }
                    $newItem['children'] = $newChildrenList;
                } else {
                    $newItem['children'] = null;
                }
                $newList[] = $newItem;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $newList,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    public function getOptionList()
    {
        $list = GoodsCats::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => $this->mch_id ?: 0,
            'is_delete' => 0,
        ])->orderBy(['sort' => SORT_ASC])->asArray()->all();

        $newList = [];
        // 一级分类
        foreach ($list as $key => $item) {
            if ($item['parent_id'] == 0) {
                $newList[] = [
                    'value' => $item['id'],
                    'label' => $item['name'],
                ];
                unset($list[$key]);
            }
        }
        $list = array_values($list);

        // 二级分类
        foreach ($newList as &$item) {
            foreach ($list as $lKey => $lItem) {
                if ($item['value'] == $lItem['parent_id']) {
                    $item['children'][] = [
                        'value' => $lItem['id'],
                        'label' => $lItem['name'],
                    ];
                    unset($list[$lKey]);
                }
            }
        }
        $list = array_values($list);

        // 三级分类
        foreach ($newList as &$item) {
            if (isset($item['children'])) {
                foreach ($item['children'] as &$cItem) {
                    foreach ($list as $lItem) {
                        if ($cItem['value'] == $lItem['parent_id']) {
                            $cItem['children'][] = [
                                'value' => $lItem['id'],
                                'label' => $lItem['name'],
                            ];
                        }
                    }
                }
            }
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList
            ]
        ];
    }


    public function getChildrenList()
    {
        $list = GoodsCats::find()->alias('gc')->where([
            'gc.mall_id' => \Yii::$app->mall->id,
            'gc.mch_id' => \Yii::$app->user->identity->mch_id,
            'gc.is_delete' => 0,
            'gc.parent_id' => $this->id,
        ])->with(['parent', 'child' => function ($query) {
            $query->alias('c')->andWhere(['c.is_delete' => 0]);
        }])
            ->page($pagination)
            ->orderBy('sort ASC')
            ->asArray()->all();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            /** @var GoodsCats $detail */
            $detail = GoodsCats::find()->where([
                'id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
            ])->with('parent.parent')->one();

            if (!$detail) {
                throw new \Exception('分类不存在,ID:' . $this->id);
            }

            $newDetail = ArrayHelper::toArray($detail);

            $parents = [];
            if ($detail->parent) {
                $parents[] = $detail->parent;
                if ($detail->parent->parent) {
                    $parents[] = $detail->parent->parent;
                }
            }
            $newDetail['parents'] = $parents;
            $newDetail['advert_params'] = \yii\helpers\BaseJson::decode($detail['advert_params']);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $newDetail,
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function destroy()
    {
        $transaction = \Yii::$app->db->beginTransaction();

        try {
            $list = $this->getDefault($this->id, true, false);
            if (empty($list)) {
                throw new \Exception('无效的分类');
            }
            $catIdList = $this->getCatId($list);
            $goodsCatRelation = GoodsCatRelation::find()
                ->where(['is_delete' => 0, 'cat_id' => $catIdList])
                ->with('goods')
                ->all();

            $sign = false;
            /** @var GoodsCatRelation $item */
            foreach ($goodsCatRelation as $item) {
                if (count($item->goods)) {
                    $sign = true;
                }
            }

            if ($sign) {
                throw new \Exception('所选分类下还有商品，无法删除');
            }

            $cats = GoodsCats::updateAll(
                [
                    'deleted_at' => date('Y-m-d H:i:s', time()),
                    'is_delete' => 1,
                ],
                ['id' => $catIdList]
            );

            $res = GoodsCatRelation::updateAll(
                [
                    'is_delete' => 1,
                ],
                ['cat_id' => $catIdList]
            );

            if ($cats) {
                $transaction->commit();

                // 触发分类删除事件
                \Yii::$app->trigger(GoodsCats::EVENT_DESTROY, new GoodsCatEvent([
                    'catsList' => $catIdList,
                ]));

                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '删除成功，共删除' . $cats . '条分类',
                ];
            }
            throw new \Exception('无效的分类，无法删除');

        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function switchStatus()
    {
        try {
            $detail = GoodsCats::find()->where([
                'id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
            ])->one();

            if (!$detail) {
                throw new \Exception('分类不存在');
            }

            $detail->status = $detail->status ? 0 : 1;
            $res = $detail->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($detail));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function getDefault($id = null, $isArray = true, $isParent = true)
    {
        $query = GoodsCats::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'is_delete' => 0,
        ])->keyword($id, ['id' => $id])->with(['child' => function ($query) {
            $query->orderBy(['sort' => SORT_ASC, 'created_at' => SORT_ASC]);
        }, 'child.child' => function ($query) {
            $query->orderBy(['sort' => SORT_ASC, 'created_at' => SORT_ASC]);
        }]);
        if ($isParent) {
            $query->andWhere(['parent_id' => 0]);
        }
        $list = $query->orderBy(['sort' => SORT_ASC, 'created_at' => SORT_ASC])
            ->asArray($isArray)->all();

        return $list;
    }

    /**
     * @param $list
     * @return bool
     * 判断指定id属于哪一条关系链
     */
    public function checkId(&$list)
    {
        $flag = false;
        if (!is_array($list)) {
            return $flag;
        }
        foreach ($list as &$item) {
            if ($item['id'] == $this->id) {
                $flag = true;
            } else {
                $flag = $this->checkId($item['child']);
            }
            $item['is_select'] = $flag;
            if ($flag) {
                break;
            }
        }
        unset($item);
        return $flag;
    }

    /**
     * @param array $list
     * @return array
     * 获取list中及其下级的所有id
     */
    public function getCatId($list)
    {
        $catId = [];
        if (empty($list)) {
            return $catId;
        }
        foreach ($list as $item) {
            $catId[] = $item['id'];
            if (!empty($item['child'])) {
                $catId = array_merge($catId, $this->getCatId($item['child']));
            }
        }
        return $catId;
    }

    public function getListByKeyword()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $list = GoodsCats::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'is_delete' => 0
        ])->with('parent')
            ->keyword($this->keyword, ['like', 'name', $this->keyword])
            ->page($pagination, 20, $this->page)
            ->orderBy(['sort' => SORT_ASC, 'created_at' => SORT_DESC])->all();

        $newList = [];
        /* @var GoodsCats[] $list */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            if ($item->parent_id == 0) {
                $newItem['status_text'] = '一级分类';
            } elseif ($item->parent && $item->parent->parent_id == 0) {
                $newItem['status_text'] = '二级分类';
            } else {
                $newItem['status_text'] = '三级分类';
            }
            $newList[] = $newItem;
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination
            ]
        ];
    }

    public function sortSave()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        /* @var GoodsCats $cat */
        $cat = GoodsCats::find()->where([
            'id' => $this->id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id
        ])->one();
        $cat->sort = $this->sort;
        if (!$cat->save()) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $this->getErrorMsg($cat)
            ];
        } else {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功'
            ];
        }
    }

    public function transferCat()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        /* @var GoodsCats[] $goodsCat */
        $goodsCat = GoodsCats::find()->with(['parent', 'child', 'parent.parent'])
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'status' => 1, 'parent_id' => $this->id])
            ->andWhere(['mch_id' => \Yii::$app->user->identity->mch_id])
            ->keyword($this->keyword, ['like', 'name', $this->keyword])
            ->orderBy(['sort' => SORT_ASC, 'created_at' => SORT_DESC])
            ->page($pagination)->all();
        $parent = null;
        $grandParent = null;
        $newList = [];
        foreach ($goodsCat as $item) {
            $newItem = $item->toArray();
            $parent = $item->parent;
            $grandParent = $item->parent ? $item->parent->parent : null;
            $newItem['child'] = $item->child;
            $newList[] = $newItem;
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $newList,
                'parent' => $parent,
                'grandParent' => $grandParent,
                'pagination' => $pagination
            ]
        ];
    }

    public function storeSort()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            try {
                $firstList = \Yii::$app->serializer->decode($this->first_list);
                $secondList = \Yii::$app->serializer->decode($this->second_list);
                $thirdList = \Yii::$app->serializer->decode($this->third_list);
            } catch (\Exception $exception) {
                throw new \Exception($exception->getMessage());
            }

            foreach ($firstList as $index => $item) {
                $goodsCats = GoodsCats::findOne($item['id']);
                if (!$goodsCats) {
                    throw new \Exception('分类不存在');
                }
                $goodsCats->sort = $index;
                $res = $goodsCats->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($goodsCats));
                }
            }

            foreach ($secondList as $index => $item) {
                $goodsCats = GoodsCats::findOne($item['id']);
                if (!$goodsCats) {
                    throw new \Exception('分类不存在');
                }
                $goodsCats->sort = $index;
                $res = $goodsCats->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($goodsCats));
                }
            }

            foreach ($thirdList as $index => $item) {
                $goodsCats = GoodsCats::findOne($item['id']);
                if (!$goodsCats) {
                    throw new \Exception('分类不存在');
                }
                $goodsCats->sort = $index;
                $res = $goodsCats->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($goodsCats));
                }
            }

            $transaction->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $exception) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine(),
            ];
        }
    }

    public function exportCat()
    {
        $query = GoodsCats::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'is_delete' => 0,
            'parent_id' => 0,
            'id' => $this->choose_list
        ]);

        $new_query = clone $query;

        $exp = new GoodsCatExport();
        $res = $exp->export($new_query);
        return $res;
    }
}
