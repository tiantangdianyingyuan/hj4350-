<?php

namespace app\models;

/**
 * This is the model class for table "{{%option}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property string $group
 * @property string $name
 * @property string $value
 * @property string $created_at
 * @property string $updated_at
 */
class Option extends ModelActiveRecord
{
    const GROUP_ADMIN = 'admin';
    const GROUP_APP = 'app';

    const NAME_COPYRIGHT = 'copyright';//版权管理
    const NAME_NAVBAR = 'navbar';//导航栏
    const NAME_USER_CENTER = 'user_center';//导航栏
    const NAME_PAGE_TITLE = 'page_title';//小程序页面标题
    const NAME_HOME_PAGE = 'home_page';//首页布局
    const NAME_RECHARGE_SETTING = 'recharge_setting'; //充值相关
    const NAME_ORDER_FORM = 'order_form'; //商城下单表单
    const NAME_TERRITORIAL_LIMITATION = 'territorial_limitation'; //区域允许购买
    const NAME_OFFER_PRICE = 'offer_price'; // 起送规则
    const NAME_WX_PLATFORM = 'wx_platform';//微信公众号配置
    const NAME_WX_TEMPLATE = 'wx_template';//群发模版
    const NAME_TUTORIAL = 'tutorial'; //教程管理
    const NAME_SMS = 'sms';//短信配置
    const NAME_SHARE_CUSTOMIZE = 'share_customize_data'; //分销自定义设置
    const NAME_PERMISSIONS_STATUS = 'permissions_status';// 微擎子账号权限状态
    const NAME_DELIVERY_DEFAULT_SENDER = 'delivery_default_sender'; //电子面单默认发件人信息
    const NAME_POSTER = 'poster';// 商城海报配置
    const NAME_IND_SETTING = 'ind_setting'; // 独立版设置
    const NAME_APP_SHARE_SETTING = 'app_share_setting'; // 自定义分享
    const NAME_CAT_STYLE_SETTING = 'cat_style_setting'; // 分类页面样式配置
    const NAME_OVERRUN = 'overrun'; // 分类页面样式配置
    const NAME_VERSION = 'version'; // 当前商城版本
    const NAME_WX_TEMPLATE_TEST_USER = 'wx_template_test_user';//微信模板测试用户
    const NAME_ALI_TEMPLATE_TEST_USER = 'ali_template_test_user';//支付宝模板测试用户
    const NAME_BD_TEMPLATE_TEST_USER = 'bd_template_test_user';//百度模板测试用户
    const NAME_TT_TEMPLATE_TEST_USER = 'tt_template_test_user';//头条模板测试用户
    const NAME_RECOMMEND_SETTING = 'recommend_setting';//商品推荐设置
    const NAME_FXHB_RECOMMEND_SETTING = 'fxhb_recommend_setting';//商品推荐设置
    const NAME_ROLE_SETTING = 'role_setting';//员工管理基础设置
    const NAME_IMPORT_ERROR_LOG = 'import_error_log';//商品导入错误数据
    const NAME_IMPORT_CAT_ERROR_LOG = 'import_cat_error_log';//商品导入错误数据
    const NAME_GLOBAL_PERMISSION = 'global_permission';// 独立版账户 全局权限设置
    const NAME_POSTER_NEW = 'poster_new'; //商城海报新
    const NAME_RECHARGE_PAGE = 'recharge_setting_page'; //充值页面自定义

    /**
     * 多商户设置
     */
    const NAME_MCH_MALL_SETTING = 'mch_mall_setting'; // 多商户设置

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%option}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'name', 'value', 'created_at', 'updated_at',], 'required'],
            [['mall_id', 'mch_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['value'], 'string'],
            [['group', 'name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'mall ID',
            'mch_id' => 'Mch ID',
            'group' => 'Group',
            'name' => 'Name',
            'value' => 'Value',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
