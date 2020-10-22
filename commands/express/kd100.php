<?php

namespace app\commands\express;

use app\forms\mall\order\OrderSendForm;

class kd100 extends Base
{
    public const FILE = 'kuaidi100_list.json';
    public const MAP = [
        '韵达速递' => '韵达快递',
        '龙邦快递' => '龙邦物流',
        '联昊通速递' => '联昊通',
        '快捷快递' => '快捷速递',
        '安捷快递' => '安捷物流',
        '平安达腾飞快递' => '平安达腾飞',
        '全日通快递' => '全日通',
        '亚风快递' => '亚风速递',
        '增益快递' => '增益速递',
        '亚马逊物流' => '亚马逊中国',
        '安信达快递' => '安信达',
        '澳邮专线' => '澳邮中国快运',
        'COE东方快递' => 'COE',
        'D速物流' => 'D速快递',
        'FEDEX联邦(国际件）' => 'FedEx-英国件',
        '好来运快递' => '好来运',
        '跨越物流' => '跨越速运',
        '速腾快递' => '广东速腾物流',
        '源安达快递' => '源安达',
        '邮政包裹信件' => 'EMS物流',
        '程光' => '程光快递',
        '富腾达' => '富腾达国际货运',
        '万家康' => '万家康物流',
        'EWE' => 'EWE全球快递',
        '德邦快运' => '德邦',
        '京东快运' => '京东物流',
        '邮政国内标快' => 'EMS物流',
        '秦远海运' => '秦远物流',
        '速必达物流' => '速必达',
        '中铁快运' => '中铁物流',
        //空
        '泛捷快递' => '',
        '全晨快递' => '',
        '快客快递' => '',
        '义达国际物流' => '',
        '原飞航物流' => '',
        '运通快递' => '',
        '亿翔快递' => '',
        '众通快递' => '',
        '北青小红帽' => '',
        'CCES快递' => '',
        '城市100' => '',
        '长沙创一' => '',
        '成都善途速运' => '',
        'FEDEX联邦(国内件）' => '',
        '汇丰物流' => '',
        '鸿桥供应链' => '',
        '海派通物流公司' => '',
        '华强物流' => '',
        '华夏龙物流' => '',
        '嘉里物流' => '',
        '捷特快递' => '',
        '瑞丰速递' => '',
        '盛邦物流' => '',
        '腾林物流' => '',
        '唐山申通' => '',
        'UEQ Express' => '',
        '新邦物流' => '',
        '希优特' => '',
        '丰恒物流' => '',
        '佳润达物流' => '',
    ];

    public function create()
    {
        $url = 'aHR0cDovL2FwaS5rdWFpZGkxMDAuY29tL21hbmFnZXIvb3BlbmFwaS9kb3dubG9hZC9rZGJtLmRv';
        $url = base64_decode($url);
        $send = new OrderSendForm();
        $temp = $send->up($url);
        $data = $send->getExcel($temp);

        if (empty($data)) {
            return [];
        }
        array_shift($data);
        $newArr = array_column($data, 1, 0);
        $this->encrypt(self::FILE, $newArr);
        @unlink($temp);
        $this->ok('[kd100生成成功][OK]');
    }
}