<?php

namespace app\plugins\quick_share\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoods;
use app\models\Model;
use app\plugins\quick_share\models\QuickShareGoods;

class GoodsEditForm extends Model
{
    public $id;
    public $goods_id;
    public $share_text;
    public $share_pic;
    public $material_sort;
    public $is_top;
    public $material_video_url;
    public $material_cover_url;

    public $tabs;
    public $attr;
    public $goods_warehouse_id;

    public function rules()
    {
        return [
            [['attr'],'safe'],
            [['tabs'], 'in', 'range' => ['four', 'first']],

            [['share_pic'], 'trim'],
            [['is_top', 'material_sort'], 'default', 'value' => 0],
            [['share_text', 'share_pic'], 'required'],
            [['goods_id', 'material_sort', 'is_top', 'id', 'goods_warehouse_id'], 'integer'],
            [['share_text', 'material_video_url', 'material_cover_url'], 'default', 'value' => ''],
            [['share_text', 'material_video_url', 'tabs', 'material_cover_url'], 'string', 'max' => 255],

        ];
    }

    public function attributeLabels()
    {
        return [
            'tabs' => '类型',
            'attr' => '规格',
            'goods_warehouse_id' => '',

            'id' => 'ID',
            'share_text' => '分享文本',
            'share_pic' => '素材图片',
            'material_sort' => '素材排序',
            'is_top' => '是否置顶',
            'material_video_url' => '动态视频',
            'material_cover_url' => '视频封面',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $goods_id = 0;
            if ($this->tabs === 'first') {
                if (empty($this->attr[0]['goods_id'])) {
                    throw new \Exception('请先获取商品');
                } else {
                    $goods_id = $this->attr[0]['goods_id'];
                }
            }

            if ($this->id) {
                $form = QuickShareGoods::findOne(['id' => $this->id, 'is_delete' => 0]);
                if (empty($form)) {
                    throw new \Exception('数据不存在或已删除');
                }
            } else {
                if ($goods_id && QuickShareGoods::findOne(['goods_id' => $goods_id, 'is_delete' => 0])) {
                    throw new \Exception('商品已存在素材，添加失败');
                }
                $form = new QuickShareGoods();
                $form->mall_id = \Yii::$app->mall->id;
                $form->is_delete = 0;
                $form->status = 1;
            }

            if ($this->is_top == 1) {
                QuickShareGoods::updateAll(['is_top' => 0, 'updated_at' => mysql_timestamp()], [
                    'AND',
                    ['is_delete' => 0],
                    ['is_top' => 1],
                    ['mall_id' => \Yii::$app->mall->id],
                    $goods_id ? ['<>', 'goods_id', 0] : ['goods_id' => 0]
                ]);
            }
            $form->attributes = $this->attributes;
            $form->goods_id = $goods_id;
            $form->share_pic = \yii\helpers\BaseJson::encode($this->share_pic);
            if ($form->save()) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '保存成功'
                ];
            } else {
                throw new \Exception($this->getErrorMsg($form));
            }
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    protected function setGoodsSign()
    {
        return (new \app\plugins\quick_share\Plugin())->getName();
    }

    private function checkData()
    {
        if (!$this->goods_warehouse_id) {
            throw new \Exception('请先选择商品');
        }

        $goodsWarehouse = (CommonGoods::getCommon())->getGoodsWarehouse($this->goods_warehouse_id);
        if (!$goodsWarehouse) {
            throw new \Exception('商品以删除，请重新选择商品');
        }

        if (!isset($this->attr) || !is_array($this->attr)) {
            throw new \Exception('商品数据异常');
        }
    }
}
