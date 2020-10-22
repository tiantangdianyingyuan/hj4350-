<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/7
 * Time: 11:46
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
Yii::$app->loadViewComponent('app-goods');
?>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'plugin/miaosha/mall/goods/index'})">秒杀活动</span></el-breadcrumb-item>
                <el-breadcrumb-item v-if="form.goods_id > 0">详情</el-breadcrumb-item>
                <el-breadcrumb-item v-else>新建活动</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <app-goods ref="appGoods"
                   :is_info="0"
                   :is_show="0"
                   sign="miaosha"
                   :is_detail="0"
                   :url="url"
                   :form="form"
                   :rule="rule"
                   :is_price="3"
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
                                    type="datetimerange"
                                    align="left"
                                    unlink-panels
                                    value-format="yyyy-MM-dd"
                                    range-separator="至"
                                    start-placeholder="开始日期"
                                    end-placeholder="结束日期"
                                    :picker-options="pickerOptions">
                            </el-date-picker>
                        </el-form-item>
                    </el-col>
                    <el-col v-if="is_add == 1" :span="24">
                        <el-form-item label="开放时间">
                            <template v-if="options.length > 0">
                                <el-checkbox :indeterminate="isIndeterminate" v-model="checkAll"
                                             @change="handleCheckAllChange">
                                    全选
                                </el-checkbox>
                                <div style="margin: 15px 0;"></div>
                                <el-checkbox-group v-model="form.open_time" @change="handleCheckedCitiesChange">
                                    <div style="width: 120px; display: inline-block" v-for="option in options">
                                        <el-checkbox :label="option.value" :key="option.value">
                                            {{option.label}}
                                        </el-checkbox>
                                    </div>
                                </el-checkbox-group>
                            </template>
                            <template v-else>
                                <el-tag disable-transitions type="danger">请先设置秒杀开放时间</el-tag>
                            </template>
                        </el-form-item>
                    </el-col>
<!--                    <el-col :span="12">-->
<!---->
<!--                        <el-form-item label="限单数量" prop="buy_limit">-->
<!--                            <div flex="dir:left">-->
<!--                                <el-input :disabled="isBuyLimit" type="number" placeholder="请输入限单数量"-->
<!--                                          v-model="form.buy_limit">-->
<!--                                </el-input>-->
<!--                                <el-checkbox style="margin-left: 5px;" @change="itemChecked(1)" v-model="isBuyLimit">无限制-->
<!--                                </el-checkbox>-->
<!--                            </div>-->
<!--                        </el-form-item>-->
<!--                        <el-form-item label="已秒杀数">-->
<!--                            <el-input type="number" placeholder="请输入已秒杀数"-->
<!--                                      v-model="form.virtual_miaosha_num">-->
<!--                            </el-input>-->
<!--                        </el-form-item>-->
<!--                    </el-col>-->
                </el-card>
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
                    extra: {
                        price: '秒杀价'
                    },
                },
                rule: {
                    is_alone_buy: [
                        {required: true, message: '请选择是否允许单独购买', trigger: 'change'},
                    ],
                },
                referrerUrl: 'plugin/miaosha/mall/goods/index',
                url: 'plugin/miaosha/mall/activity/edit',
                get_goods_url: 'plugin/miaosha/mall/goods/edit',

                checkAll: false,
                options: [],
                isIndeterminate: false,

                // 批量设置
                batch: {},
                // 默认操作
                isBuyLimit: false,
                pickerOptions: {
                    disabledDate(time) {
                        return time.getTime() > Date.now() + 30 * 24 * 60 * 60 * 1000 ||
                            time.getTime() <= Date.now() - 24 * 60 * 60 * 1000;
                    },
                },
            };
        },
        created() {
            this.getSetting();
        },
        methods: {
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
            // 暂时无用
            store() {
                let self = this;
                if (!self.form.open_time.length) {
                    self.$message.error('请添加秒杀时间')
                    return;
                }

                if (!self.form.open_date.length) {
                    self.$message.error('请添加秒杀日期')
                    return;
                }

                let dateArr = self.diffTime();
                self.$refs.appGoods.btnLoading = true;
                let sign = 0;
                dateArr.forEach(function (item) {
                    self.form.open_date = [item, item];
                    request({
                        params: {
                            r: self.url
                        },
                        method: 'post',
                        data: {
                            form: JSON.stringify(self.form),
                            attrGroups: JSON.stringify(self.$refs.appGoods.attrGroups),
                            defaultMemberPrice: JSON.stringify(self.$refs.appGoods.defaultMemberPrice)
                        }
                    }).then(e => {
                        sign++;
                        if (e.data.code == 0) {
                            if (sign == dateArr.length) {
                                if (sign == dateArr.length) {
                                    self.$refs.appGoods.btnLoading = false;
                                    self.$message.success(e.data.msg);
                                    if (typeof self.referrerUrl === 'object') {
                                        navigateTo(self.referrerUrl)
                                    } else {
                                        navigateTo({
                                            r: self.referrerUrl,
                                        })
                                    }
                                }
                            }
                        } else {
                            if (sign == 1) {
                                self.$refs.appGoods.btnLoading = false;
                                self.$message.error(e.data.msg);
                            }
                        }
                    }).catch(e => {
                        console.log(e);
                    });
                })
            },
            diffTime() {
                let time1 = new Date(this.form.open_date[0] + ' 00:00:00').getTime();
                let time2 = new Date(this.form.open_date[1] + ' 00:00:00').getTime();

                let diff = (time2 - time1) / 86400000;

                let arr = [this.form.open_date[0]];
                for (let i = 1; i <= diff; i++) {
                    let date = new Date(time1 + 86400000 * i)
                    let Y = date.getFullYear() + '-';
                    let M = (date.getMonth() + 1 <= 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
                    let D = (date.getDate() + 1 <= 10 ? '0' + (date.getDate()) : date.getDate());
                    arr.push(Y + M + D)
                }

                return arr;
            },
            getSetting() {
                request({
                    params: {
                        r: 'plugin/miaosha/mall/index/index',
                    },
                    method: 'get'
                }).then(e => {
                    if (e.data.code == 0) {
                        this.options = e.data.data.detail.options;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.cardLoading = false;
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
                let self = this;
                if (getQuery('id')) {
                    self.form.virtual_miaosha_num = e.miaoshaGoods.virtual_miaosha_num;
                    self.form.buy_limit = e.miaoshaGoods.buy_limit;
                    self.form.open_date = [];
                    self.form.open_time = [];
                    self.form.open_date.push(e.miaoshaGoods.open_date, e.miaoshaGoods.open_date);
                    self.form.open_time.push(e.miaoshaGoods.open_time);
                    self.form.extra = {
                        price: '秒杀价'
                    };
                    self.referrerUrl = {
                        r: 'plugin/miaosha/mall/goods/miaosha-list',
                        id: e.goods_warehouse_id
                    };
                }

                this.isBuyLimit = this.form.buy_limit == -1;
            },
            itemChecked(type) {
                if (type === 1) {
                    Vue.set(this.form, 'buy_limit', this.isBuyLimit ? -1 : 0);
                } else {
                }
            },
        },
        mounted() {
            let id = getQuery('id');
            if (id) {
                this.is_add = 0;
                this.get_goods_url = 'plugin/miaosha/mall/goods/edit';
            } else {
                this.get_goods_url = 'mall/goods/edit';
            }
        }
    });
</script>
