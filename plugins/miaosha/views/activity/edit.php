<?php
/**
 * Created by PhpStorm.
 * User: fjt
 * Date: 2019/12/7
 * Time: 11:46
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
Yii::$app->loadViewComponent('app-goods');
?>
<style>
    .active-color {
        color: #409eff;
    }
    .no-active-color {
        color: #cfd0d1;
    }
    .el-date-editor .el-range-separator {
        padding: 0;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'plugin/miaosha/mall/activity/index'})">秒杀活动</span></el-breadcrumb-item>
                <el-breadcrumb-item v-if="goods_id">编辑活动</el-breadcrumb-item>
                <el-breadcrumb-item v-else>新建活动</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <app-goods ref="appGoods"
                   :is_edit="is_edit"
                   :is_info="0"
                   :is_show="0"
                   :is_cats="0"
                   sign="miaosha"
                   :is_detail="0"
                   :url="url"
                   :is_virtual_sales="0"
                   :form="form"
                   :rule="rule"
                   :no_price="0"
                   :is_display_setting="0"
                   :referrer="referrerUrl"
                   :get_goods_url="get_goods_url"
                   :preview-info="previewInfo"
                   @handle-preview="handlePreview"
                   @goods-success="childrenGoods">
            <template slot="before_info">
                <el-card shadow="never" style="margin-bottom: 24px">
                    <div slot="header">活动设置</div>
                    <el-col :span="24">
                        <el-form-item v-if="is_add == 1" label="开放日期">
                            <el-date-picker
                                v-model="form.open_date"
                                type="daterange"
                                align="left"
                                unlink-panels
                                :disabled="Number(form.id) != 0"
                                value-format="yyyy-MM-dd"
                                range-separator="至"
                                prefix-icon="el-icon-time"
                                start-placeholder="开始日期"
                                end-placeholder="结束日期"
                                :picker-options="pickerOptions">
                            </el-date-picker>
                        </el-form-item>
                    </el-col>

                    <el-col v-if="is_add == 1" :span="24">
                        <el-form-item label="开放时间">
                            <template v-if="!form.id > 0">
                                <el-checkbox  :indeterminate="isIndeterminate" v-model="checkAll"
                                             @change="handleCheckAllChange">
                                    全选
                                </el-checkbox>
                                <div  style="margin: 15px 0;"></div>
                                <el-checkbox-group style="width: 1000px" v-model="form.open_time" @change="handleCheckedCitiesChange">
                                        <el-checkbox :label="option.value" style="width: 120px; display: inline-block" v-for="option in options" :key="option.value">
                                            {{option.label}}
                                        </el-checkbox>
                                </el-checkbox-group>
                            </template>
                            <template v-else>
                                <div style="width: 600px">
                                    <span v-for="option in options" style="margin-right: 13px;" :class="{'active-color' : filter_color(option), 'no-active-color' : !filter_color(option)}">
                                        {{option.label}}
                                    </span>
                                </div>
                            </template>
                        </el-form-item>
                    </el-col>
                </el-card>
            </template>

            <!-- 秒杀价 -->
            <template slot="before_price">
                <el-form-item label="秒杀价" prop="price">
                    <el-input type="number"
                              placeholder="请输入商品秒杀价"
                              :disabled="use_attr == 1"
                              oninput="this.value = this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3');"
                              min="0"
                              v-model="form.price">
                        <template slot="append">元</template>
                    </el-input>
                </el-form-item>
            </template>

            <!-- 已秒杀量 -->
            <template slot="before_services">
                <el-form-item prop="virtual_sales">
                    <template slot='label'>
                        <span>已秒杀量</span>
                        <el-tooltip effect="dark" content="前端展示的销量=实际销量+已秒杀量" placement="top">
                            <i class="el-icon-info"></i>
                        </el-tooltip>
                    </template>
                    <el-input type="number" oninput="this.value = this.value.replace(/[^0-9]/g, '')" min="0" v-model="form.virtual_sales">
                        <template slot="append">{{form.unit}}</template>
                    </el-input>
                </el-form-item>
            </template>

            <template slot="member_route_setting">
                 <span class="red">注：必须在“
                    <el-button type="text" @click="$navigate({r: 'plugin/miaosha/mall/index'}, true)">秒杀设置=>基本设置=>优惠叠加设置</el-button>
                    ”中开启，才能使用
                </span>
            </template>

            <template slot="preview">
                <div v-if="previewData" flex="dir:top">
                    <el-image style="height:44px"
                              src="<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl() ?>/img/453.png"></el-image>
                    <div class="goods">
                        <div class="goods-name">{{previewData.name}}</div>
                        <div flex="dir:left" style="font-size:14px">
                            <div flex="dir:top" style="font-size: 10px">
                                <div style="font-size:26px;color:#ff4544;" :class="previewData.t_type">{{previewData.actualPrice}}</div>
                                <div flex="dir:left">
                                    <div style="color: #999999;text-decoration: line-through;">￥{{previewData.original_price}}</div>
                                    <div style="color: #999999;margin-left: 6px">销量{{previewData.virtual_sales}}{{previewData.unit}}</div>
                                </div>
                            </div>
                            <div class="share" flex="dir:top main:center cross:center">
                                <el-image src="statics/img/mall/goods/icon-share.png"></el-image>
                                <div>分享</div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </app-goods>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                goods_id: -1,
                previewInfo: {
                    is_head: false,
                    is_cart: false,
                    is_attr: false,
                },
                previewData: null,
                is_add: 1,
                form: {
                    buy_limit: -1,
                    open_date: [],
                    open_time: [],
                    virtual_miaosha_num: 0,
                    activity_status: 1,
                    id: 0,
                    price: '',
                    form: {},
                    unit: '件',
                    virtual_sales: 0,
                    extra: {
                        price: '秒杀价'
                    }
                },
                use_attr: 0,
                rule: {
                    is_alone_buy: [
                        {required: true, message: '请选择是否允许单独购买', trigger: 'change'},
                    ],
                    price: [
                        {required: true, message: '请输入商品价格', trigger: 'change'}
                    ]
                },
                referrerUrl: 'plugin/miaosha/mall/activity/index',
                url: 'plugin/miaosha/mall/activity/edit',
                get_goods_url: 'plugin/miaosha/mall/goods/edit',
                checkAll: false,
                options: [
                    {
                        label: '00:00~00:59',
                        value: 0
                    },
                    {
                        label: '01:00~01:59',
                        value: 1
                    },
                    {
                        label: '02:00~02:59',
                        value: 2
                    },
                    {
                        label: '03:00~03:59',
                        value: 3
                    },
                    {
                        label: '04:00~04:59',
                        value: 4
                    },
                    {
                        label: '05:00~05:59',
                        value: 5
                    },
                    {
                        label: '06:00~06:59',
                        value: 6
                    },
                    {
                        label: '07:00~07:59',
                        value: 7
                    },
                    {
                        label: '08:00~08:59',
                        value: 8
                    },
                    {
                        label: '09:00~09:59',
                        value: 9
                    },
                    {
                        label: '10:00~10:59',
                        value: 10
                    },
                    {
                        label: '11:00~11:59',
                        value: 11
                    },
                    {
                        label: '12:00~12:59',
                        value: 12
                    },
                    {
                        label: '13:00~13:59',
                        value: 13
                    },
                    {
                        label: '14:00~14:59',
                        value: 14
                    },
                    {
                        label: '15:00~15:59',
                        value: 15
                    },
                    {
                        label: '16:00~16:59',
                        value: 16
                    },
                    {
                        label: '17:00~17:59',
                        value: 17
                    },
                    {
                        label: '18:00~18:59',
                        value: 18
                    },
                    {
                        label: '19:00~19:59',
                        value: 19
                    },
                    {
                        label: '20:00~20:59',
                        value: 20
                    },
                    {
                        label: '21:00~21:59',
                        value: 21
                    },
                    {
                        label: '22:00~22:59',
                        value: 22
                    },
                    {
                        label: '23:00~23:59',
                        value: 23
                    },
                ],
                isIndeterminate: false,
                pickerOptions: {
                    disabledDate(time) {
                        return time.getTime() > Date.now() + 30 * 24 * 60 * 60 * 1000 ||
                            time.getTime() <= Date.now() - 24 * 60 * 60 * 1000;
                    },
                    shortcuts: [{
                        text: '未来一周',
                        onClick(picker) {
                            const end = new Date();
                            const start = new Date();
                            end.setTime(start.getTime() + 3600 * 1000 * 24 * 6);
                            picker.$emit('pick', [start, end]);
                        }
                    }, {
                        text: '未来半月',
                        onClick(picker) {
                            const end = new Date();
                            const start = new Date();
                            end.setTime(start.getTime() + 3600 * 1000 * 24 * 14);
                            picker.$emit('pick', [start, end]);
                        }
                    }, {
                        text: '未来一个月',
                        onClick(picker) {
                            const end = new Date();
                            const start = new Date();
                            end.setTime(start.getTime() + 3600 * 1000 * 24 * 29);
                            picker.$emit('pick', [start, end]);
                        }
                    }]
                },
                is_edit: 0
            };
        },
        created() {
        },
        methods: {
            // 预览
            handlePreview(e) {
                const price = Number(e.price);
                const attr = e.attr;
                let arr = [];
                attr.map(v => {
                    arr.push(Number(v.price));
                });
                let max = Math.max.apply(null, arr);
                let min = Math.min.apply(null, arr);

                let actualPrice = -1;
                let type = 'text-price';
                if (max > min && min >= 0) {
                    actualPrice = min + '-' + max;
                } else if (max == min && min >= 0) {
                    actualPrice = min;
                } else if (price > 0) {
                    actualPrice = price;
                } else if (price == 0) {
                    actualPrice = '免费';
                    type = '';
                }

                this.previewData = Object.assign({},e,{
                    actualPrice,
                    t_type:type,
                });
            },

            handleCheckAllChange(val) {
                let arr = [];
                this.options.forEach(function (item) {
                    arr.push(item.value)
                });
                this.form.open_time = val ? arr : [];
                this.isIndeterminate = false;
            },
            handleCheckedCitiesChange(value) {
                let checkedCount = value.length;
                this.checkAll = checkedCount === this.options.length;
                this.isIndeterminate = checkedCount > 0 && checkedCount < this.options.length;
            },
            // 监听子组件事件
            childrenGoods(e) {
                console.log(e);
                this.form.price = e.price;
                this.use_attr = e.use_attr;
                if (getQuery('id')) {
                    this.form.id = e.activity.id;
                    console.log(e);
                    this.form.price = e.price;
                    this.form.activity_id = e.activity.id;
                    this.form.open_date = [];
                    this.form.open_date.push(e.activity.open_date, e.activity.open_date);
                    this.form.open_time = e.open_time;
                    this.form.virtual_sales = e.virtual_sales;
                    // this.referrerUrl = {
                    //     r: 'plugin/miaosha/mall/activity/index',
                    //     id: e.goods_warehouse_id
                    // };
                    this.form.activity_status = 1;
                }
            },
            filter_color(item) {
                let active = false;
                this.form.open_time.map((it) => {
                    if (item.value === it) {
                        active = true;
                    }
                });
                return active;
            }
        },
        mounted() {
            this.goods_id = getQuery('id');
            let activity_id = getQuery('activity_id');
            let sessions_id = getQuery('sessions_id');
            if (this.goods_id) {
                this.is_edit = 1;
                this.get_goods_url = 'plugin/miaosha/mall/goods/edit';
                this.url = 'plugin/miaosha/mall/goods/edit';
                if (activity_id) {
                    this.referrerUrl = {
                        r: `plugin/miaosha/mall/activity/detail`,
                        id: activity_id,
                    }
                } else {
                    this.referrerUrl = {
                        r: `plugin/miaosha/mall/activity/data`,
                    }
                }
            } else {
                this.get_goods_url = 'mall/goods/edit';
                this.url = 'plugin/miaosha/mall/activity/edit'
            }
        },
    });
</script>
