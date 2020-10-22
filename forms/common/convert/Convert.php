<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/9
 * Time: 16:50
 */

namespace app\forms\common\convert;


use app\core\response\ApiCode;
use app\models\Goods;
use app\models\Mall;
use app\models\User;
use app\models\UserInfo;
use app\models\We7App;
use yii\db\Query;

class Convert extends ConvertBase
{
    public function checkStore()
    {
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => 'ok',
            'data' => [
                'storeList' => $this->v3Db
                    ->createCommand('SELECT id,name FROM ' . $this->v3Db->tablePrefix . 'store')
                    ->queryAll(),
            ],
        ];
    }

    public function convertStore($storeId)
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            $v3Store = (new Query())->from('{{%store}}')->where(['id' => $storeId])->createCommand($this->v3Db)->queryOne();
            $mall = new Mall();
            $mall->id = $v3Store['id'];
            $mall->name = $v3Store['name'];
            $mall->user_id = $v3Store['user_id'];
            $mall->is_recycle = $v3Store['is_recycle'] == 1 ? 1 : 0;
            $mall->is_delete = $v3Store['is_delete'] == 1 ? 1 : 0;
            $mall->is_disable = $v3Store['status'] == 1 ? 1 : 0;
            if (!$mall->save()) {
                throw new \Exception($this->getModelError($mall));
            }
            if ($v3Store['wechat_app_id']) {
                $v3WechatApp = (new Query())->from('{{%wechat_app}}')->where(['id' => $v3Store['wechat_app_id']])->createCommand($this->v3Db)->queryOne();
                if ($v3WechatApp) {
                    $we7App = new We7App();
                    $we7App->mall_id = $mall->id;
                    $we7App->acid = $v3WechatApp['acid'];
                    if (!$we7App->save()) {
                        throw new \Exception($this->getModelError($we7App));
                    }
                }
            }
            $this->convertUser($storeId, $mall->id);
            $this->convertGoods($storeId, $mall->id);
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => 'success',
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }

    private function convertUser($storeId, $mallId)
    {
        $v3UserList = (new Query())->from('{{%user}}')->where(['store_id' => $storeId])->createCommand($this->v3Db)->queryAll();
        foreach ($v3UserList as $v3User) {
            $user = new User();
            $user->id = $v3User['id'];
            $user->mall_id = $mallId;
            $user->username = $v3User['username'];
            $user->password = $v3User['password'];
            $user->nickname = $v3User['nickname'];
            $user->auth_key = $v3User['auth_key'];
            $user->access_token = $v3User['access_token'];
            $user->mobile = $v3User['binding'] ? $v3User['binding'] : '';
            $user->created_at = $this->convertTime($v3User['addtime']);
            $user->updated_at = $this->convertTime();
            $user->is_delete = $v3User['is_delete'] == 1 ? 1 : 0;
            if ($user->is_delete == 1) {
                $user->deleted_at = $this->convertTime();
            }
            if (!$user->save()) {
                throw new \Exception($this->getModelError($user));
            }
            $userInfo = new UserInfo();
            $userInfo->user_id = $user->id;
            $userInfo->avatar = $v3User['avatar_url'];
            $userInfo->platform_user_id = $v3User['wechat_open_id'];
            $userInfo->integral = $v3User['integral'];
            $userInfo->total_integral = $v3User['total_integral'];
            $userInfo->balance = $v3User['money'];
            $userInfo->parent_id = $v3User['parent_id'];
            $userInfo->is_blacklist = $v3User['blacklist'] == 1 ? 1 : 0;
            $userInfo->contact_way = $v3User['contact_way'] ? $v3User['contact_way'] : '';
            $userInfo->remark = $v3User['comments'] ? $v3User['comments'] : '';
            $userInfo->is_delete = 0;
            $userInfo->junior_at = $this->convertTime($v3User['time']);
            $userInfo->store_id = $v3User['shop_id'] ? $v3User['shop_id'] : 0;
            switch ($v3User['platform']) {
                case 0:
                    $userInfo->platform = UserInfo::PLATFORM_WXAPP;
                    break;
                case 1:
                    $userInfo->platform = UserInfo::PLATFORM_ALIAPP;
                    break;
                default:
                    break;
            }
            if (!$userInfo->save()) {
                throw new \Exception($userInfo);
            }
        }
    }

    private function convertGoods($storeId, $mallId)
    {
        $v3MallGoodsList = (new Query())->from('{{%goods}}')->where(['store_id' => $storeId])->createCommand($this->v3Db)->queryAll();
        foreach ($v3MallGoodsList as $v3Goods) {
            $goods = new Goods();
            $goods->mall_id = $mallId;
            $goods->mch_id = $v3Goods['mch_id'];
            $goods->name = $v3Goods['name'];
            $goods->price = $v3Goods['price'];
            $goods->original_price = $v3Goods['original_price'];
            $goods->cost_price = $v3Goods['cost_price'];
            $goods->detail = $v3Goods['detail'];
            $goods->status = $v3Goods['status'] == 1 ? 1 : 0;
            $goods->use_attr = $v3Goods['use_attr'] == 1 ? 1 : 0;
            $goods->attr_groups = \Yii::$app->serializer->encode([]);
            $v3GoodsPicList = (new Query())->from('{{%goods_pic}}')
                ->where(['goods_id' => $v3Goods['id'], 'is_delete' => 0,])->createCommand($this->v3Db)->queryAll();
            foreach ($v3GoodsPicList as &$v3GoodsPic) {
                $v3GoodsPic = [
                    'id' => '0',
                    'pic_url' => $v3GoodsPic['pic_url'],
                ];
            }
            if ($v3Goods['cover_pic']) {
                $goods->cover_pic = $v3Goods['cover_pic'];
            } else {
                $goods->cover_pic = count($v3GoodsPicList) ? $v3GoodsPicList[0]['pic_url'] : '';
            }
            $goods->pic_url = \Yii::$app->serializer->encode($v3GoodsPicList);
            $goods->video_url = $v3Goods['video_url'];
            $goods->unit = $v3Goods['unit'];
            $goods->virtual_sales = $v3Goods['virtual_sales'];
            $goods->is_quick_shop = $v3Goods['quick_purchase'] == 1 ? 1 : 0;
            $goods->is_sell_well = $v3Goods['hot_cakes'] == 1 ? 1 : 0;
            $goods->is_negotiable = $v3Goods['is_negotiable'] == 1 ? 1 : 0;
            $goods->created_at = $this->convertTime($v3Goods['addtime']);
            $goods->updated_at = $this->convertTime();
            $goods->is_delete = $v3Goods['is_delete'] == 1 ? 1 : 0;
            if ($goods->is_delete == 1) {
                $goods->deleted_at = $this->convertTime();
            }
            $goods->sort = $v3Goods['sort'];
            $goods->confine_count = $v3Goods['confine_count'] == 0 ? -1 : $v3Goods['confine_count'];
            if ($v3Goods['full_cut']) {
                $v3GoodsFullCut = json_decode($v3Goods['full_cut'], true);
                if ($v3GoodsFullCut && $v3GoodsFullCut['pieces']) {
                    $goods->pieces = $v3GoodsFullCut['pieces'];
                }
                if ($v3GoodsFullCut && $v3GoodsFullCut['forehead']) {
                    $goods->forehead = $v3GoodsFullCut['forehead'];
                }
            }
            if ($v3Goods['integral']) {
                $v3GoodsIntegral = json_decode($v3Goods['integral']);
                if ($v3GoodsIntegral && $v3GoodsIntegral['give']) {
                    if (mb_stripos($v3GoodsIntegral['give'], '%') !== false) {
                        $goods->give_integral = mb_substr($v3GoodsIntegral['give'], 0, mb_stripos($v3GoodsIntegral['give'], '%'));
                        $goods->give_integral_type = 2;
                    } else {
                        $goods->give_integral = $v3GoodsIntegral['give'];
                        $goods->give_integral_type = 1;
                    }
                }
                if ($v3GoodsIntegral && $v3GoodsIntegral['forehead']) {
                    $goods->forehead_integral = $v3GoodsIntegral['forehead'];
                }
                if ($v3GoodsIntegral && $v3GoodsIntegral['more']) {
                    $goods->accumulative = 1;
                }
            }
            $goods->individual_share = $v3Goods['individual_share'] == 1 ? 1 : 0;
            $goods->attr_setting_type = $v3Goods['attr_setting_type'] == 1 ? 1 : 0;
        }
    }
}
