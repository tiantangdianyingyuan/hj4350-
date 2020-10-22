<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\order_send_template;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\OrderSendTemplate;

class OrderSendTemplateForm extends Model
{
    public $id;
    public $keyword;

    const getList = 'getList';
    const getDetail = 'getDetail';
    const getDefaultTemplate = 'getDefaultTemplate';
    const destroy = 'destroy';

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['keyword'], 'string'],
            [['keyword'], 'trim'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '模板ID',
        ];
    }

    public function main($method)
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if (method_exists($this, $method)) {
                return $this->$method();
            } else {
                throw new \Exception('方法不存在');
            }

        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => [
                    'line' => $exception->getLine(),
                ],
            ];
        }
    }

    public function getList()
    {
        // 检测是否有默认模板
        $this->checkIsDefault();

        $query = OrderSendTemplate::find()
            ->where([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'is_delete' => 0,
            ]);

        if ($this->keyword) {
            $query->andWhere(['like', 'name', $this->keyword]);
        }

        $list = $query->orderBy(['is_default' => SORT_DESC, 'created_at' => SORT_DESC])->page($pagination)->all();

        $newList = array_map(function ($item) {
            /** @var OrderSendTemplate $item */
            return $item->getNewData($item, $this->getDefaultParams());
        }, $list);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,
            ],
        ];
    }

    public function getDetail()
    {
        /** @var OrderSendTemplate $template */
        $template = OrderSendTemplate::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'id' => $this->id,
            'is_delete' => 0,
        ])->one();

        if (!$template) {
            throw new \Exception('模板不存在');
        }
        $newTemplate = $template->getNewData($template, $this->getDefaultParams());

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $newTemplate,
            ],
        ];
    }

    /**
     * 获取默认模板
     * @return array
     */
    public function getDefaultTemplate()
    {
        $template = $this->checkIsDefault();
        $newTemplate = $template->getNewData($template, $this->getDefaultParams());

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $newTemplate,
            ],
        ];
    }

    /**
     * 检测是否存在默认模板，不存在则添加
     * @return OrderSendTemplate|array|null|\yii\db\ActiveRecord
     */
    private function checkIsDefault()
    {
        $template = OrderSendTemplate::find()
            ->where([
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'is_default' => 1,
                'mch_id' => \Yii::$app->user->identity->mch_id,
            ])->one();
        if (!$template) {
            $template = $this->setDefaultTemplate();
        }

        return $template;
    }

    private function destroy()
    {
        /** @var OrderSendTemplate $template */
        $template = OrderSendTemplate::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'id' => $this->id,
            'is_delete' => 0,
        ])->one();

        if (!$template) {
            throw new \Exception('模板不存在');
        }
        if ($template->is_default == 1) {
            throw new \Exception('默认模板不能删除');
        }

        $template->is_delete = 1;
        $res = $template->save();
        if (!$res) {
            throw new \Exception($this->getErrorMsg($template));
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '删除成功',
        ];
    }

    private function setDefaultTemplate()
    {
        $template = new OrderSendTemplate();
        $template->mall_id = \Yii::$app->mall->id;
        $template->mch_id = \Yii::$app->user->identity->mch_id;
        $template->name = '默认模板';
        $params = $this->getDefaultParams();
        $template->params = json_encode($params, true);
        $template->cover_pic = \Yii::$app->request->hostInfo . '/web/statics/img/mall/send-template/default.png';
        $template->is_default = 1;
        $res = $template->save();

        if (!$res) {
            throw new \Exception($this->getErrorMsg($template));
        }

        return $template;
    }

    private function getDefaultParams()
    {
        return [
            "order" => [
                "orderNumber" => true,
                "time" => true,
                "date" => true,
            ],
            "personalInf" => [
                "name" => true,
                "nickname" => true,
                "phone" => true,
                "shipMethod" => true,
                "address" => true,
                "payMethod" => true,
                "mention_address" => true,
                "leaveComments" => true,
            ],
            "goodsInf" => [
                "serial" => true,
                "name" => true,
                "attr" => true,
                "number" => true,
                "unit" => true,
                "univalent" => true,
                "article_number" => true,
                "amount" => true,
                "fare" => true,
                "discount" => true,
                "actually_paid" => true,
            ],
            "sellerInf" => [
                "branch" => true,
                "name" => true,
                "phone" => true,
                "postcode" => true,
                "address" => true,
                "remark" => true,
            ],
            "stencil_width" => 204,
            "stencil_high" => 142,
            "left_right_margins" => 0,
            "border_width" => 1,
            "headline" => [
                "name" => "发货单",
                "fimaly" => "微软雅黑",
                "font" => 16,
                "align" => 0,
                "line" => 48,
                "space" => 0,
                "bold" => true,
                "italic" => false,
                "underline" => false,
            ],
            "offset" => [
                "left" => 0,
                "right" => 0,
            ],
            "customize" => "",
            "customize_image" => "",
            "img_url" => [],
            "printSetting" => [
                "page_type" => 1, // 1.按间距连续打印  2.每版打印一页
                "space" => 10, // 发货单间距
            ],
        ];
    }
}
