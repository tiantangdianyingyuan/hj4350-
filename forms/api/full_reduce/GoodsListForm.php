<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/7/9
 * Time: 17:59
 */

namespace app\forms\api\full_reduce;

use app\core\response\ApiCode;
use app\forms\api\cat\CatsForm;
use app\forms\common\coupon\CommonCoupon;
use app\forms\common\goods\CommonGoodsList;
use app\models\Mall;
use app\models\Model;

class GoodsListForm extends Model
{
    public $sort;
    public $sort_type;
    public $keyword;
    public $page;
    public $cat_id;

    public function rules()
    {
        return [
            [['page'], 'default', 'value' => 1],
            [['cat_id', 'keyword', 'sort', 'sort_type'], 'integer'],
        ];
    }

    public function getList()
    {
        try {
            $form = new CommonGoodsList();
            $form->sign = ['', 'mch'];
            $form->sort = $this->sort;
            $form->status = 1;
            $form->sort_type = $this->sort_type;
            $form->keyword = $this->keyword;
            $form->page = $this->page;
            $form->cat_id = $this->cat_id;
            $form->is_sales = (new Mall())->getMallSettingOne('is_sales');
            $form->relations = ['goodsWarehouse', 'mallGoods'];
            $form->is_full_reduce = true;
            $list = $form->getList();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'pagination' => $form->pagination,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}
