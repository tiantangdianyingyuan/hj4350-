<?php

namespace app\models;

/**
 * This is the model class for table "order_send_template".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property string $name 发货单名称
 * @property string $cover_pic 缩略图
 * @property string $params 模板参数
 * @property int $is_default 是否为默认模板0.否|1.是
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class OrderSendTemplate extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_send_template}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'params', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'mch_id', 'is_default', 'is_delete'], 'integer'],
            [['params'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name'], 'string', 'max' => 60],
            [['cover_pic'], 'string', 'max' => 255],
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
            'mch_id' => 'Mch ID',
            'name' => '发货单名称',
            'cover_pic' => '缩略图',
            'params' => '模板参数',
            'is_default' => '是否为默认模板0.否|1.是',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    /**
     * @param OrderSendTemplate $template
     * @return array
     */
    public function getNewData($template, $defaultParams = [])
    {
        $newTemplate = [];
        $newTemplate['id'] = $template->id;
        $newTemplate['name'] = $template->name;
        $newTemplate['cover_pic'] = $template->cover_pic;
        $newTemplate['is_default'] = $template->is_default;
        // 补充上新加的默认值
        $params = json_decode($template->params, true);
        foreach ($defaultParams as $key => $value) {
            if (!isset($params[$key])) {
                $params[$key] = $value;
            }
        }
        $newTemplate['params'] = $params;

        return $newTemplate;
    }
}
