<?php

namespace app\forms\api\cat;

use app\core\response\ApiCode;
use app\forms\common\CommonAppConfig;
use app\models\GoodsCats;
use app\models\Model;
use app\plugins\mch\models\Mch;

class CatsForm extends Model
{
    public $cat_id;
    public $mch_id;
    public $select_cat_id;

    public function rules()
    {
        return [
            [['cat_id', 'mch_id', 'select_cat_id'], 'integer'],
            [['cat_id', 'mch_id', 'select_cat_id'], 'default', 'value' => 0],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if ($this->mch_id && empty(Mch::findOne($this->mch_id))) {
                throw new \Exception('多商户不存在');
            }

            /**********start*************/
            $mall_cat_style_a = 6;
            $mall_cat_style_b = 7;
            $mall_cat_style_c = 11;
            $cat_style = CommonAppConfig::getAppCatStyle()['cat_style'];
            if (in_array($cat_style, [$mall_cat_style_a, $mall_cat_style_b, $mall_cat_style_c])) {
                $select_cat_id = 0;
                $this->cat_id = $this->select_cat_id ?: $this->cat_id;

                $select_cat_id ^= $this->cat_id;
                $this->cat_id ^= $select_cat_id;
                $select_cat_id ^= $this->cat_id;
            } else {
                $select_cat_id = $this->select_cat_id;
            }
            /**********end*************/
            $list = GoodsCats::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'parent_id' => $this->cat_id,
                'is_delete' => 0,
                'mch_id' => $this->mch_id,
                'status' => 1,
                'is_show' => 1,
            ])->with(['child' => function ($query) {
                $query->with(['child' => function ($query) {
                    $query->andWhere(['mch_id' => $this->mch_id, 'status' => 1, 'is_show' => 1])->orderBy(['sort' => SORT_ASC, 'created_at' => SORT_ASC]);
                }])->andWhere(['mch_id' => $this->mch_id, 'status' => 1, 'is_show' => 1])->orderBy(['sort' => SORT_ASC, 'created_at' => SORT_ASC]);
            }])
                ->orderBy(['sort' => SORT_ASC, 'created_at' => SORT_ASC])
                ->asArray()
                ->all();

            $func = function ($data) use (&$func) {
                if ($this->mch_id) {
                    $data['page_url'] = sprintf("/plugins/mch/shop/shop?mch_id=%u&cat_id=%u", $this->mch_id, $data['id']);
                } else {
                    $data['page_url'] = sprintf("/pages/goods/list?cat_id=%u", $data['id']);
                }
                $data['advert_params'] = \yii\helpers\BaseJson::decode($data['advert_params']);
                if (isset($data['child'])) {
                    foreach ($data['child'] as $key => $item) {
                        //$data['child'][$key] = $func($item);
                        $data['child'][$key] = array_merge($func($item), ['active' => $key === 0]);
                    }
                }
                return $data;
            };

            foreach ($list as $k => $v) {
                $list[$k] = array_merge($func($v), ['active' => $k === 0]);
            }

            //temp
            if (!empty($select_cat_id) && !empty($list)) {
                $func = function ($item) use (&$func, $select_cat_id) {
                    if ($item['id'] == $select_cat_id) {
                        return true;
                    }
                    if (isset($item['child'])) {
                        foreach ($item['child'] as $key => $item) {
                            return $func($item);
                        }
                    };
                    return false;
                };
                $sentinel = true;
                foreach ($list as $k => $item) {
                    $list[$k]['active'] = $func($item);
                    $list[$k]['active'] && $sentinel = false;
                }
                $sentinel && $list[0]['active'] = $sentinel;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $list,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    /**
     * @return array
     * 即将废弃
     */
    public function searchTemp()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if ($this->mch_id) {
                $mch = Mch::findOne($this->mch_id);
                if (!$mch) {
                    throw new \Exception('多商户不存在');
                }
            }

            $parent_id = 0;
            if ($this->cat_id) {
                $cat = GoodsCats::findOne([
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                    'id' => $this->cat_id,
                    'mch_id' => $this->mch_id ?: 0,
                    'status' => 1
                ]);
                $parent_id = $cat->parent_id;
            };

            $list = GoodsCats::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'parent_id' => $parent_id,
                'is_delete' => 0,
                'mch_id' => $this->mch_id ?: 0,
                'status' => 1,
                'is_show' => 1,
            ])->with(['child' => function ($query) {
                $query->andWhere(['mch_id' => $this->mch_id ?: 0, 'status' => 1, 'is_show' => 1])
                    ->with(['child' => function ($query) {
                        $query->andWhere(['mch_id' => $this->mch_id ?: 0, 'status' => 1, 'is_show' => 1])->orderBy('sort ASC');
                    }])->orderBy('sort ASC');
            }])
                ->orderBy('sort ASC')
                ->asArray()
                ->all();

            foreach ($list as $k => &$v) {
                if ($parent_id === 0) {
                    if ($this->cat_id) {
                        $list[$k]['active'] = $this->cat_id == $v['id'];
                    } else {
                        $list[$k]['active'] = $k == 0;
                    };
                } else {
                    $list[$k]['active'] = $this->cat_id == $v['id'];
                }
                if ($this->mch_id) {
                    $v['page_url'] = '/plugins/mch/shop/shop?mch_id=' . $this->mch_id . '&cat_id=' . $v['id'];
                } else {
                    $v['page_url'] = '/pages/goods/list?cat_id=' . $v['id'];
                }
                $v['advert_params'] = \yii\helpers\BaseJson::decode($v['advert_params']);
                if (isset($v['child']) && $v['child']) {
                    foreach ($v['child'] as &$cItem) {
                        if ($this->mch_id) {
                            $cItem['page_url'] =
                                '/plugins/mch/shop/shop?mch_id=' . $this->mch_id . '&cat_id=' . $cItem['id'];
                        } else {
                            $cItem['page_url'] = '/pages/goods/list?cat_id=' . $cItem['id'];
                        }
                        $cItem['advert_params'] = \yii\helpers\BaseJson::decode($cItem['advert_params']);
                        foreach ($cItem['child'] as &$ccItem) {
                            if ($this->mch_id) {
                                $ccItem['page_url'] =
                                    '/plugins/mch/shop/shop?mch_id=' . $this->mch_id . '&cat_id=' . $ccItem['id'];
                            } else {
                                $ccItem['page_url'] = '/pages/goods/list?cat_id=' . $ccItem['id'];
                            }
                            $ccItem['advert_params'] = \yii\helpers\BaseJson::decode($ccItem['advert_params']);
                        }
                    }
                }
            };

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $list,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
