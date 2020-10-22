<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\User;
use app\plugins\exchange\forms\common\CommonModel;
use app\plugins\exchange\forms\mall\export\ExchangeExport;
use app\plugins\exchange\models\ExchangeCode;
use app\plugins\exchange\models\ExchangeLibrary;

class CodeForm extends Model
{
    public $library_id;
    public $status; //-1 过期 0禁用 1 启用 2兑换
    public $code;
    public $created_at;
    public $type; //0 后台 礼品
    public $flag;
    public $page;

    public function rules()
    {
        return [
            [['library_id'], 'required'],
            [['library_id', 'type', 'status', 'page'], 'integer'],
            [['code', 'flag'], 'string', 'max' => 100],
            [['created_at'], 'trim'],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            /** @var ExchangeLibrary $library */
            $library = CommonModel::getLibrary($this->library_id);
            if (!$library) {
                throw new \Exception('兑换库错误');
            }

            $where = [
                'AND',
                ['library_id' => $this->library_id],
                ['mall_id' => \Yii::$app->mall->id],
            ];
            switch ($this->status) {
                case '-1':
                    in_array($library->expire_type, ['fixed', 'relatively'], true)
                        ? array_push($where, ['<', 'valid_end_time', date('Y-m-d H:i:s')])
                        : array_push($where, ['is', 'id', null]);
                    break;
                case '1':
                    array_push($where, ['status' => $this->status]);
                    if ($library->expire_type !== 'all') {
                        array_push($where, ['>', 'valid_end_time', date('Y-m-d H:i:s')]);
                    };
                    break;
                case '0':
                    array_push($where, ['status' => $this->status]);
                    break;
                case '2':
                    array_push($where, ['in', 'status', [2, 3]]);
                    break;
                default:
                    break;
            }

            if (!is_null($this->type) && $this->type !== '' && $this->type != -1) {
                array_push($where, ['type' => $this->type]);
            }
            empty($this->code) || array_push($where, ['like', 'code', $this->code]);
            empty($this->created_at) || array_push(
                $where,
                ['>=', 'created_at', current($this->created_at)],
                ['<=', 'created_at', next($this->created_at)]
            );

            $query = ExchangeCode::find()->where($where)->orderBy(['id' => SORT_DESC]);

            if ($this->flag == "EXPORT") {
                $exp = new ExchangeExport();
                $exp->library_id = $this->library_id;
                $exp->page = $this->page;
                return $exp->export($query);
            }

            $list = $query->page($pagination)->asArray()->all();

            $qrcode = new QrcodeForm();
            $qrcode->setTempList();
            $list = array_map(function ($item) use ($library, $qrcode) {
                $qrcode->code = $item['code'];

                $item['qrcode_url'] = $qrcode->generate();
                $item['validity_type'] = $library->expire_type;
                $item['status'] = (new CommonModel())->getStatus($library, $item);
                if (in_array($item['status'], [2, 3])) {
                    $u = User::findOne($item['r_user_id']);
                    $item['r_rewards'] = (new CommonModel())->getFormatRewards($item['r_rewards']);
                    $item['nickname'] = $u->nickname;
                    $item['avatar'] = $u->userInfo->avatar;
                    $item['platform'] = $u->userInfo->platform;
                } else {
                    $item['platform'] = '';
                    $item['r_rewards'] = [];
                    $item['nickname'] = '';
                    $item['avatar'] = '';
                }
                return $item;
            }, $list);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '获取成功',
                'data' => [
                    'list' => $list,
                    'pagination' => $pagination,
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
