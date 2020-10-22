<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\common;

use app\plugins\exchange\models\ExchangeCode;
use app\plugins\exchange\models\ExchangeLibrary;

class CreateCode
{
    /** @var ExchangeLibrary $libraryModel */
    public $libraryModel;
    public $mall_id;

    public function __construct(ExchangeLibrary $libraryModel, $mall_id)
    {
        $this->libraryModel = $libraryModel;
        $this->mall_id = $mall_id;
    }

    public function createOne()
    {
        $libraryModel = $this->libraryModel;
        list($valid_start_time, $valid_end_time) = $this->getTime($libraryModel);

        $code = [];
        $this->testDatabase(1, $code);
        //兑换码
        $codeModel = new ExchangeCode();
        $codeModel->mall_id = $this->mall_id;
        $codeModel->library_id = $libraryModel->id;
        $codeModel->type = ExchangeCode::TYPE_APP;
        $codeModel->code = current($code);
        $codeModel->status = 1;
        $codeModel->validity_type = $libraryModel->expire_type;
        $codeModel->valid_start_time = $valid_start_time;
        $codeModel->valid_end_time = $valid_end_time;
        $codeModel->r_user_id = 0;
        $codeModel->r_raffled_at = '0000-00-00 00:00:00';
        $codeModel->r_origin = \Yii::$app->appPlatform;
        $codeModel->name = '';
        $codeModel->mobile = '';
        $codeModel->save();
        return $codeModel;
    }

    //并发问题队列或锁
    public function createAll($num)
    {
        $library = $this->libraryModel;
        list($valid_start_time, $valid_end_time) = $this->getTime($library);
        $row = [
            'mall_id' => \Yii::$app->mall->id,
            'library_id' => $library->id,
            'type' => 0,
            'code' => "",
            'status' => 1,
            'validity_type' => $library->expire_type, //是否可删 all 永久 fixed 固定 relatively相对
            'valid_start_time' => $valid_start_time,
            'valid_end_time' => $valid_end_time,
            'created_at' => date('Y-m-d H:i:s'),
            'r_user_id' => 0,
            'r_raffled_at' => '0000-00-00 00:00:00',
            'r_rewards' => '',
            'r_origin' => '',
            'name' => '',
            'mobile' => '',
        ];
        $code = [];
        $this->testDatabase($num, $code);


        $rows = [];
        for (
            $i = 0; $i < count($code);
            $i++
        ) {
            $row['code'] = $code[$i];
            array_push($rows, $row);
        }
        $success = \Yii::$app->db->createCommand()
            ->batchInsert(ExchangeCode::tableName(), array_keys($row), $rows)
            ->execute();
        //成功条
        return $success;
    }

    private function getTime(): array
    {
        $library = $this->libraryModel;
        switch ($library->expire_type) {
            case 'fixed':
                $valid_start_time = $library->expire_start_time;
                $valid_end_time = $library->expire_end_time;
                break;
            case 'relatively':
                $real_start_day = new \DateInterval(sprintf('P%dD', intval($library->expire_start_day) - 1));
                $real_end_day = new \DateInterval(sprintf('P%dD', intval($library->expire_end_day) - 1));
                $valid_start_time = (new \DateTime())->add($real_start_day)->format('Y-m-d H:i:s');
                $valid_end_time = (new \DateTime())->add($real_end_day)->format('Y-m-d H:i:s');
                break;
            default:
                $valid_start_time = "0000-00-00 00:00:00";
                $valid_end_time = "0000-00-00 00:00:00";
                break;
        }
        return [$valid_start_time, $valid_end_time];
    }

    private function testDatabase($num, &$code)
    {
        while (count($code) < $num) {
            while (count($code) < $num) {
                array_push($code, (new Code($this->libraryModel->code_format))->generate());
            }
            $ids = ExchangeCode::find()->select('code')->where([
                'AND',
                //['library_id' => $this->libraryModel->id],
                ['mall_id' => \Yii::$app->mall->id],
                ['in', 'code', $code],
            ])->column();
            $code = array_diff(array_unique($code), $ids);
        }
    }
}
