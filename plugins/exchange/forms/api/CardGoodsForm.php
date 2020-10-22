<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\api;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoodsList;
use app\models\Model;
use app\plugins\exchange\forms\common\CommonModel;
use app\plugins\exchange\forms\common\CommonSetting;
use app\plugins\exchange\forms\exchange\validate\FacadeAdmin;
use yii\db\Exception;

class CardGoodsForm extends Model
{
    public $id;
    public $page;

    public function rules()
    {
        return [
            [['id', 'page'], 'integer'],
        ];
    }

    public function list()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $form = new CommonGoodsList();
            $form->status = 1;
            $form->page = $this->page;
            $form->mch_id = 0;
            $form->status = 1;
            $form->is_array = true;
            $form->sign = \Yii::$app->plugin->getCurrentPlugin()->getName();
            $form->isSignCondition = true;
            $form->relations = ['goodsWarehouse', 'mallGoods'];
            $list = $form->getList();

            $list = array_map(function ($item) {
                $item['is_level'] == 0 && $item['level_show'] = 0;
                return $item;
            }, $list);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $list,
                    'pagination' => $form->pagination,
                ],
            ];
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

    public function detail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $eModel = CommonModel::getCardGoods($this->id);
            if (!$eModel || !($libraryModel = $eModel->library)) {
                throw new \Exception('商品信息有误');
            }
            $f = new FacadeAdmin();
            $f->validate->libraryModel = $libraryModel;
            $f->validate->hasLibrary();
            $f->validate->hasExpireLibrary();
            $f->validate->hasDisableLibrary();

            $form = new \app\forms\common\goods\CommonGoodsDetail();
            $form->user = \Yii::$app->user->identity;
            $form->mall = \Yii::$app->mall;
            $goods = $form->getGoods($this->id);
            if (!$goods) {
                throw new Exception('商品不存在');
            }
            if ($goods->status != 1) {
                throw new Exception('商品未上架');
            }
            $form->goods = $goods;
            $setting = (new CommonSetting())->get();
            $form->setMember(intval($setting['is_member_price']) === 1);
            $form->setShare(intval($setting['is_share']) === 1);
            $res = $form->getAll();
            //组件使用
            $res['is_negotiable'] = 0;
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '获取成功',
                'data' => [
                    'goods' => $res,
                    //'delivery' => ''
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'errors' => $e->getLine()
            ];
        }
    }
}
