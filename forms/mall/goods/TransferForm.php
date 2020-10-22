<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/6/17
 * Time: 13:30
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\mall\goods;


use app\core\response\ApiCode;
use app\models\GoodsCatRelation;
use app\models\Model;

class TransferForm extends Model
{
    public $before;
    public $after;

    public function rules()
    {
        return [
            [['before', 'after'], 'required'],
            [['before', 'after'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'before' => '转移前分类',
            'after' => '转移后分类'
        ];
    }

    public function transfer()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            if ($this->before <= 0) {
                throw new \Exception('必须选择转移前的分类');
            }
            if ($this->after <= 0) {
                throw new \Exception('必须选择转移后的分类');
            }
            $count = GoodsCatRelation::updateAll(
                ['cat_id' => $this->after],
                ['cat_id' => $this->before, 'is_delete' => 0]
            );
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '转移成功，一共转移' . $count . '个商品',
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
