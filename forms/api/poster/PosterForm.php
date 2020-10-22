<?php


namespace app\forms\api\poster;

use app\core\response\ApiCode;
use app\models\Model;

class PosterForm extends Model
{

    public function poster($method)
    {
        $p = func_get_args();
        try {
            if (method_exists($this, $method)) {
                $model = $this->$method();
                $model->attributes = $p[1];
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => $model->get()
                ];
            }
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine(),
            ];
        }
    }

    public function goodsNew()
    {
        return new GoodsNewPosterForm();
    }

    public function goods()
    {
        return new GoodsPosterForm();
    }

    public function share()
    {
        return new SharePosterForm();
    }

    public function topic()
    {
        return new TopicPosterForm();
    }
    public function footprint()
    {
        return new FootprintPosterForm();
    }
}
