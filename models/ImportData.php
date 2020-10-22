<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%import_data}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property int $user_id 操作账户ID
 * @property int $status 导入状态|1.全部失败|2.部分失败|3.全部成功
 * @property string $file_name 导入文件名
 * @property int $count
 * @property int $success_count
 * @property int $error_count
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 * @property int $type
 * @property User $user
 */
class ImportData extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%import_data}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'status', 'count', 'success_count', 'error_count', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'user_id', 'status', 'count', 'success_count', 'error_count', 'is_delete', 'mch_id', 'type'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['file_name'], 'string', 'max' => 191],
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
            'user_id' => '操作账户ID',
            'status' => '导入状态|1.全部失败|2.部分失败|3.全部成功',
            'file_name' => '导入文件名',
            'count' => 'Goods Count',
            'success_count' => 'Success Count',
            'error_count' => 'Error Count',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
            'type' => '1.商品导入|2.分类导入',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @param ImportData $importData
     * @return string $status
     */
    public function getStatusText($importData)
    {
        // 导入状态|1.全部失败|2.部分失败|3.全部成功
        switch ($importData->status) {
            case 1:
                $status = '导入失败';
                break;
            case 2:
                $status = '导入失败';
                break;
            case 3:
                $status = '导入成功';
                break;
            default:
                $status = '状态未知';
                break;
        }

        return $status;
    }
}
