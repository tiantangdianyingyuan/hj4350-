<?php

namespace app\plugins\quick_share\models;

use app\models\Goods;
use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%quick_share_goods}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $goods_id
 * @property int $status 状态
 * @property string $share_text 分享文本
 * @property string $share_pic 素材图片
 * @property int $material_sort 素材排序
 * @property int $is_top 是否置顶
 * @property string $material_video_url 动态视频
 * @property string $material_cover_url 商品封面
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class QuickShareGoods extends ModelActiveRecord
{
     const GOODSTYPE = 'goods';
     const DYNAMICTYPE = 'dynamic';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%quick_share_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'share_text', 'share_pic', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'goods_id', 'status', 'material_sort', 'is_top', 'is_delete'], 'integer'],
            [['share_pic'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['share_text', 'material_video_url', 'material_cover_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'Mall ID',
            'goods_id' => 'Goods ID',
            'status' => '状态',
            'share_text' => '分享文本',
            'share_pic' => '素材图片',
            'material_sort' => '素材排序',
            'is_top' => '是否置顶',
            'material_video_url' => '动态视频',
            'material_cover_url' => '视频封面',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }

}
