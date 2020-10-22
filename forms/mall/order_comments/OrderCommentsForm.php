<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\order_comments;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoodsList;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\models\OrderComments;
use app\models\OrderCommentsTemplates;
use app\models\User;
use app\models\UserInfo;
use yii\helpers\ArrayHelper;

class OrderCommentsForm extends Model
{
    public $id;
    public $page_size;
    public $is_show;
    public $keyword;
    public $sign;
    public $type;
    public $comment_type;
    public $is_reply;
    public $platform;
    public $batch_ids;
    public $template_id;
    public $reply_type;
    public $reply_text;
    public $status;
    public function rules()
    {
        return [
            [['id', 'page_size', 'is_show', 'type', 'comment_type', 'template_id', 'reply_type', 'status'], 'integer'],
            [['keyword', 'sign', 'platform', 'reply_text'], 'string'],
            [['keyword', 'sign'], 'default', 'value' => ''],
            [['page_size'], 'default', 'value' => 10],
            [['batch_ids'], 'safe']
        ];
    }

    //GET
    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = OrderComments::find()->alias('o')->where([
            'o.mall_id' => \Yii::$app->mall->id,
            'o.is_delete' => 0,
            'o.mch_id' => \Yii::$app->user->identity->mch_id,
        ])->keyword($this->sign, ['o.sign' => $this->sign])
            ->leftJoin(['g' => Goods::tableName()], 'o.goods_id = g.id')
            ->leftJoin(['gw' => GoodsWarehouse::tableName()], 'gw.id = g.goods_warehouse_id')
            ->leftJoin(['u' => User::tableName()], 'o.user_id = u.id')
            ->leftJoin(['i' => UserInfo::tableName()], 'i.user_id = u.id');
        $query->keyword($this->platform, ['i.platform' => $this->platform]);
        switch ($this->type) {
            case 1:
                $query->keyword($this->keyword, ['or', [
                    'like', 'u.nickname', $this->keyword],
                    ['like', 'o.virtual_user', $this->keyword]
                ]);
                break;
            case 2:
                $query->keyword($this->keyword, ['like', 'gw.name', $this->keyword]);
                break;
            case 3:
                $query->keyword($this->keyword, ['like', 'o.content', $this->keyword]);
                break;
            default:
                break;
        }

        if ($this->is_reply == 1) {
            $query->andWhere(['!=', 'o.reply_content', '']);
        } elseif ($this->is_reply == 2) {
            $query->andWhere(['o.reply_content' => '']);
        }

        $query->keyword($this->comment_type, ['score' => $this->comment_type]);
        $list = $query->select('o.*, u.nickname, gw.name, gw.cover_pic, i.platform')
            ->orderBy('id DESC')
            ->with('detail', 'goods.goodsWarehouse')
            ->page($pagination, $this->page_size)
            ->all();
        $newList = [];
        /** @var OrderComments $item */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['platform'] = isset($item->user->userInfo->platform) ? $item->user->userInfo->platform : '';
            if ($item->is_virtual == 1) {
                $newItem['nickname'] = '(' . $item->virtual_user . ')';
            } else {
                $newItem['nickname'] = isset($item->user->nickname) ? $item->user->nickname : '';
            }
            try {
                $newItem['pic_url'] = \Yii::$app->serializer->decode($item->pic_url);
            } catch (\Exception $exception) {
                $newItem['pic_url'] = [];
            }
            try {
                $goodsInfo = \Yii::$app->serializer->decode($item->detail->goods_info);
                $newItem['cover_pic'] = $goodsInfo['goods_attr']['pic_url'] ?: $goodsInfo['goods_attr']['cover_pic'];
            } catch (\Exception $exception) {
                $newItem['cover_pic'] = $item['goods']['goodsWarehouse']['cover_pic'];
            }
            $newItem['name'] = $item->goods->name;
            $newList[] = $newItem;
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,
            ]
        ];
    }

    public function goodsSearch()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $form = new CommonGoodsList();
        $form->keyword = $this->keyword;
        $form->limit = 10;
        $form->sign = \Yii::$app->user->identity->mch_id > 0 ? 'mch' : '';
        $form->mch_id = \Yii::$app->user->identity->mch_id;
        $form->relations = ['goodsWarehouse'];
        $list = $form->search();
        $newList = [];
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['name'] = $item->goodsWarehouse->name;
            {
                $newItem['attr'] = ArrayHelper::toArray($item->attr);
                foreach ($newItem['attr'] as $k => $v) {
                    $attr_list = (new Goods())->signToAttr($v['sign_id'], $item['attr_groups']);
                    $attr_str = '';
                    foreach ($attr_list as $v2) {
                        $attr_str .= $v2['attr_group_name'] . ':' . $v2['attr_name'] . ';';
                    }
                    $newItem['attr'][$k]['attr_str'] = trim($attr_str, ';');
                }
            }
            $newList[] = $newItem;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $newList,
                'pagination' => $form->pagination
            ]
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $list = OrderComments::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'id' => $this->id,
            'is_virtual' => 1,
        ])
            ->with('goods.goodsWarehouse')
            ->one();
        if ($list) {
            $attr = ArrayHelper::toArray($list->goods->attr);
            foreach ($attr as $k => $v) {
                $attr_list = (new Goods())->signToAttr($v['sign_id'], $list->goods['attr_groups']);
                $attr_str = '';
                foreach ($attr_list as $v2) {
                    $attr_str .= $v2['attr_group_name'] . ':' . $v2['attr_name'] . ';';
                }
                $attr[$k]['attr_str'] = trim($attr_str, ';');
            }
            $pic_url = json_decode($list['pic_url'], true);
            $goods_name = $list['goods']['goodsWarehouse']['name'];
            $goods_info = \yii\helpers\BaseJson::decode($list['goods_info']);
            $attr_id = $goods_info['goods_attr']['id'] ?? null;
            $list = \yii\helpers\ArrayHelper::toArray($list);

            $list['pic_url'] = $pic_url;
            $list['goods_name'] = $goods_name;
            $list['attr_id'] = $attr_id;
            $list['attr'] = $attr;
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'attr' => $attr,
            ]
        ];
    }


    //DELETE
    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $model = OrderComments::findOne([
            'id' => $this->id,
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已经删除',
            ];
        }
        $model->is_delete = 1;
        $model->deleted_at = date('Y-m-d H:i:s');
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '删除成功'
        ];
    }

    //Hide
    public function show()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $model = OrderComments::findOne([
            'id' => $this->id,
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已经删除',
            ];
        }
        $model->is_show = $this->is_show;
        $model->deleted_at = date('Y-m-d H:i:s');
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功'
        ];
    }

    public function batchReply()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if (!is_array($this->batch_ids) || count($this->batch_ids) <= 0) {
                throw new \Exception('未选择用户');
            }

            if ($this->reply_type == 1) {
                if (!$this->reply_text) {
                    throw new \Exception('请输入回复评论内容');
                }
                $replyText = $this->reply_text;
            } elseif ($this->reply_type == 2) {
                $template = OrderCommentsTemplates::findOne([
                    'mall_id' => \Yii::$app->mall->id,
                    'id' => $this->template_id,
                    'is_delete' => 0
                ]);
                if (!$template) {
                    throw new \Exception('模板不存在');
                }
                $replyText = $template->content;
            } else {
                throw new \Exception('未知操作');
            }

            $res = OrderComments::updateAll([
                'reply_content' => $replyText,
            ], [
                'id' => $this->batch_ids,
            ]);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function updateTop()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $orderComment = OrderComments::findOne(['id' => $this->id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id,]);
            if (!$orderComment) {
                throw new \Exception('数据不存在或已经删除');
            }

            $orderComment->is_top = $this->status;
            $res = $orderComment->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($orderComment));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function batchDestroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if (!is_array($this->batch_ids) || count($this->batch_ids) <= 0) {
                throw new \Exception('未选择用户');
            }

            $res = OrderComments::updateAll([
                'is_delete' => 1,
                'deleted_at' => mysql_timestamp()
            ], [
                'id' => $this->batch_ids,
            ]);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function batchUpdateStatus()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if (!is_array($this->batch_ids) || count($this->batch_ids) <= 0) {
                throw new \Exception('未选择用户');
            }

            $res = OrderComments::updateAll([
                'is_show' => $this->status,
            ], [
                'id' => $this->batch_ids,
            ]);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
