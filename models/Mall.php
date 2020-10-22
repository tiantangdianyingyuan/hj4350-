<?php

namespace app\models;

use app\forms\common\version\Compatible;
use app\forms\permission\role\AdminRole;
use app\forms\permission\role\BaseRole;
use app\forms\permission\role\SuperAdminRole;
use Yii;

/**
 * This is the model class for table "{{%mall}}".
 *
 * @property string $id
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $expired_at
 * @property string $name
 * @property string $user_id
 * @property int $is_recycle
 * @property int $is_disable
 * @property int $is_delete
 * @property User $user
 * @property MallSetting[] $option
 * @property BaseRole $role
 */
class Mall extends ModelActiveRecord
{
    public $options;
    private $mallAllOptions;
    private $role;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%mall}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'deleted_at', 'expired_at'], 'safe'],
            [['user_id', 'is_recycle', 'is_delete', 'is_disable'], 'integer'],
            [['name'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'expired_at' => 'Expired At',
            'name' => '商城名称',
            'user_id' => '用户 ID',
            'is_recycle' => '商城回收状态',
            'is_disable' => '商城禁用状态',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getOption()
    {
        return $this->hasMany(MallSetting::className(), ['mall_id' => 'id']);
    }

    /**
     * 获取商城单个配置
     * @param $column
     * @return mixed|null
     * @throws \Exception
     */
    public function getMallSettingOne($column)
    {
        $settings = $this->getMallSetting([$column]);
        if (isset($settings[$column])) {
            return $settings[$column];
        }
        return null;
    }

    /**
     * 获取商城多个配置
     * @param array $columns
     * @return array|null|\yii\db\ActiveRecord
     * @throws \Exception
     */
    public function getMallSetting($columns = [])
    {
        if ($this->mallAllOptions) {
            $newOption = $this->mallAllOptions;
        } else {
            $detail = Yii::$app->mall->toArray();
            $detail['option'] = Yii::$app->mall->option;
            foreach ($detail['option'] as $key => &$value) {
                if ($value->key == 'send_type') {
                    $value->value = Compatible::getInstance()->sendType($value->value);
                }
            }
            unset($value);

            $defaultList = $this->getDefault();
            $detailOptions = [];
            foreach ($detail['option'] as $item) {
                $detailOptions[$item['key']] = $item['value'];
            }

            // 查找出列表中 没有的默认参数
            foreach ($defaultList as $dKey => $dItem) {
                if (!isset($detailOptions[$dKey])) {
                    $detail['option'][] = [
                        'key' => $dKey,
                        'value' => $dItem,
                    ];
                }
            }

            $newOption = [];
            $arr = ['add_app_bg_transparency', 'add_app_bg_radius', 'is_show_cart', 'is_show_goods_name', 'is_show_stock', 'is_sales', 'is_must_login', 'is_goods_video'];
            foreach ($detail['option'] as $k => $item) {
                if (in_array($item['key'], $arr)) {
                    $item['value'] = (int) $item['value'];
                }
                if (in_array($item['key'], ['good_negotiable', 'payment_type', 'send_type', 'business_time_type_week', 'business_time_type_day', 'quick_customize_new_params', 'send_type_desc'])) {
                    $value = is_array($item['value']) ? $item['value'] : json_decode($item['value'], true);
                    $newOption[$item['key']] = $value;
                } else {
                    if ($item['key'] == 'web_service_url') {
                        $newOption[$item['key']] = urldecode($item['value']);
                    } else if ($item['key'] == 'quick_customize_params') {
                        $newOption[$item['key']] = '';
                    } else {
                        $newOption[$item['key']] = $item['value'];
                    }
                }
            }

            // 添加商城配置默认值
            $defaultArr = $this->getDefault();
            foreach ($defaultArr as $k => $item) {
                if (!isset($newOption[$k])) {
                    $newOption[$k] = $item;
                }
            }

            $this->mallAllOptions = $newOption;
        }

        // 返回指定字段配置
        if ($columns) {
            $newData = [];
            foreach ($columns as $column) {
                if (!isset($newOption[$column])) {
                    throw new \Exception('字段' . $column . '不存在');
                }
                $newData[$column] = $newOption[$column];
            }

            return $newData;
        }

        $detail['setting'] = $newOption;
        return $detail;
    }

    /**
     * TODO 商城配置默认值
     * @return array
     */
    public function getDefault()
    {
        $host = PHP_SAPI != 'cli' ? \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . "/" : '';
        return [
            'share_title' => '', //分享标题
            'share_pic' => '', //分享图片
            'contact_tel' => '', // 联系电话
            'over_time' => 10, // 未支付订单超时时间（分钟）
            'delivery_time' => 15, // 收货时间（天）
            'after_sale_time' => 0, // 售后时间（天）
            /**
             * 支付方式
             * online_pay 线上支付
             * balance 余额支付
             * huodao 货到付款
             */
            'payment_type' => [
                'online_pay',
            ],
            'send_type' => [
                'express', 'offline',
            ], // 发货方式 express--快递 offline--自提 city--同城
            'send_type_desc' => [
                [
                    'key' => 'express',
                    'origin' => '快递配送',
                    'modify' => '',
                ],
                [
                    'key' => 'offline',
                    'origin' => '到店自提',
                    'modify' => '',
                ],
                [
                    'key' => 'city',
                    'origin' => '同城配送',
                    'modify' => '',
                ],
            ],
            /////////////////////////////////////////////////////////////////////////////////
            'mall_logo_pic' => $host . 'statics/img/mall/poster-big-shop.png', //商城logo
            'kdniao_mch_id' => '', // 快递鸟商户ID
            'kdniao_api_key' => '', // 快递鸟API KEY
            'express_select_type' => '', //0快递鸟 'wd' 支付宝
            'express_aliapy_code' => '', //支付宝code
            /////////////////////////////////////////////////////////////////////////////////
            'member_integral' => '0', // 会员积分抵扣比例
            'member_integral_rule' => '', // 会员积分使用规格
            /**
             * 商品面议联系方式
             * contact 客服
             * contact_tel 联系电话
             * contact_web 外链客服
             */
            'good_negotiable' => [
                'contact',
            ],
            'mobile_verify' => '1', // 商城手机号是否验证 0.关闭 1.开启
            'is_small_app' => '0', //跳转小程序开关
            'small_app_id' => '', // 跳转小程序APP ID
            'small_app_url' => '', // 跳转小程序APP URL
            'small_app_pic' => $host . 'statics/img/mall/small_app_pic.png', // 跳转小程序APP 图标
            'is_customer_services' => '0', // 是否开启在线客服 0.关闭 1.开启
            'customer_services_pic' => $host . 'statics/img/mall/customer_services_pic.png', // 在线客服图标
            'is_dial' => '0', // 是否开启一键拨号 0.关闭 1.开启
            'dial_pic' => $host . 'statics/img/mall/dial_pic.png', // 一键拨号图标
            'is_web_service' => '0', // 客服外链开关
            'web_service_url' => '', // 客服外链
            'web_service_pic' => $host . 'statics/img/mall/web_service_pic.png', // 客服外链图标

            'is_quick_customize' => '0', //自定义按钮开关
            'quick_customize_pic' => '', //图片路径
            'quick_customize_open_type' => '', //跳转方式
            //'quick_customize_params' => '', //跳转参数 (数据庞大 无用途)
            'quick_customize_new_params' => '', //跳转参数
            'quick_customize_link_url' => '', //跳转参数

            'is_show_stock' => '1', // 是否显示售罄图标
            'is_use_stock' => '1', //是否使用默认的售罄图标
            'sell_out_pic' => '', //售罄图标
            'sell_out_other_pic' => '', //4:3售罄图片
            /**
             * 快捷导航样式
             * 1.样式1（点击收起）
             * 2.样式2（全部展示）
             */
            'is_quick_navigation' => '0',
            'quick_navigation_style' => '1',
            'quick_navigation_opened_pic' => $host . 'statics/img/mall/quick_navigation_opened_pic.png', // 快捷导航展开图标
            'quick_navigation_closed_pic' => $host . 'statics/img/mall/quick_navigation_closed_pic.png', // 快捷导航收起图标
            /**
             * 分类样式
             * 1.大图模式（不显示侧栏）
             * 2.大图模式（显示侧栏）
             * 3.小图模式（不显示侧栏）
             * 4.小图模式（显示侧栏）
             * 5.商品列表模式
             */
            'is_common_user_member_price' => '1', // 普通用户会员价显示开关 0.关闭 1.开启
            'is_member_user_member_price' => '1', // 会员用户会员价显示开关 0.关闭 1.开启
            'is_share_price' => '1', // 分销价显示开关 0.关闭 1.开启
            'is_purchase_frame' => '1', // 首页购买记录框 0.关闭 1.开启
            'purchase_num' => '0', //轮播订单数
            'is_comment' => '1', // 商城评价开关 0.关闭 1.开启
            'is_sales' => '1', // 商城商品销量开关 0.关闭 1.开启
            // 'is_recommend' => '1',// TODO 即将废弃 推荐商品状态 0.关闭 1.开启
            'is_mobile_auth' => '0', // 首页授权手机号 0.关闭 1.开启
            'is_official_account' => '0', // 关联公众号组件 0.关闭 1.开启
            'is_manual_mobile_auth' => '1', // 手动授权手机号 0.关闭 1.开启
            'is_icon_members_grade' => '0', //会员等级标识 0关闭 1.开启
            'is_quick_map' => '0', // 一键导航是否开启 0.关闭 1.开启
            'quick_map_pic' => $host . 'statics/img/mall/quick_map_pic.png', // 一键导航图标
            'quick_map_address' => '', // 商家地址
            'latitude' => '', //纬度
            'longitude' => '', // 经度
            'is_quick_home' => '0', //返回首页开关
            'quick_home_pic' => $host . 'statics/img/mall/quick_home_pic.png', // 返回首页图标
            // 'nav_row_num' => '4',// TODO 废弃 导航图标每行显示个数
            'is_icon_super_vip' => '1', // 超级会员卡显示开关 0.关闭 1.开启
            'is_show_normal_vip' => '1', // 普通用户超级会员卡显示开关 0.关闭 1.开启
            'is_show_super_vip' => '1', // 会员用户超级会员卡显示开关 0.关闭 1.开启
            'is_show_cart' => '1', // 购物车显示开关
            'is_show_sales_num' => '1', //已售量（商品列表） 0.关闭  1.开启
            'is_show_goods_name' => '1', //商品名称
            'is_underline_price' => '1', //划线价
            'is_express' => '1', //快递
            'is_not_share_show' => '1', //非分销商分销中心显示
            'is_show_cart_fly' => '0', //购物车悬浮按钮
            'is_show_score_top' => '0', //回到顶部悬浮按钮
            'is_goods_video' => '1', // 商品视频特色展示开关

            'logo' => '', //手机端商城管理店铺设置页面，logo可自定义图片上传
            // 添加到我的小程序
            'is_add_app' => '0',
            'add_app_bg_color' => '#000000',
            'add_app_bg_transparency' => 100,
            'add_app_bg_radius' => 36,
            'add_app_text' => '添加到我的小程序，购买更便捷',
            'add_app_text_color' => '#ffffff',
            'add_app_icon_color_type' => '1',
            'theme_color' => 'a', // 商城风格
            'is_required_position' => 0, // 定位地址是否必填
            'is_share_tip' => 0, // 下单申请分销上提醒开关
            'is_must_login' => 0, // 强制授权开关

            'is_show_hot_goods' => 1, //是否显示热搜开关

            'kd100_key' => '', //快递100
            'kd100_customer' => '',//快递100
            'kd100_secret' => '',//快递100
            'kd100_siid' => '', //快递100
            'print_type' => '', //那个电子面单
        ];
    }

    /**
     * @return AdminRole|BaseRole|SuperAdminRole
     * @throws \Exception
     * 获取商城所属账户的权限
     */
    public function getRole()
    {
        if (!$this->role) {
            $user = \Yii::$app->mall->user;
            $userIdentity = $user->identity;
            $config = [
                'userIdentity' => $user->identity,
                'user' => $user,
                'mall' => \Yii::$app->mall
            ];
            if ($userIdentity->is_super_admin == 1) {
                // 总管理员
                $this->role = new SuperAdminRole($config);
            } elseif ($userIdentity->is_admin == 1) {
                // 子管理员
                $this->role = new AdminRole($config);
            } else {
                throw new \Exception('未知用户权限');
            }
        }
        return $this->role;
    }
}
