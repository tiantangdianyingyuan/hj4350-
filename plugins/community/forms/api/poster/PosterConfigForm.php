<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/23
 * Time: 9:58
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\api\poster;


use app\forms\common\poster\PosterConfigTrait;
use app\plugins\community\forms\Model;
use app\plugins\community\Plugin;

class PosterConfigForm extends Model
{
    use PosterConfigTrait;
    public $goods_id;

    public function rules()
    {
        return [
            [['goods_id'], 'required'],
            [['goods_id'], 'integer'],
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            return $this->success([
                'config' => $this->getConfig(),
                'info' => $this->getAll(),
            ]);
        } catch (\Exception $e) {
            return $this->fail(['msg' => $e->getMessage()]);
        }
    }

    public function getPlugin(): array
    {
        return [
            'sign' => (new Plugin())->getName()
        ];
    }
}
