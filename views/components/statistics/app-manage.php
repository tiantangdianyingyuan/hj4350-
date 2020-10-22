<?php
$user = Yii::$app->user->identity->identity;
$mch_id = Yii::$app->user->identity->mch_id;

?>
<style>
    .app-manage .el-radio-button__inner {
        color: #444444;
        background: #EBEEF5;
    }
    .app-manage .manage-head {
        position: relative;
        height: 61px;
        line-height: 61px;
        text-align: center;
        color: #ffffff;
        border-radius: 8px 8px 0 0;
    }

    .app-manage .manage-head img {
        position: absolute;
        right: 0;
        bottom: 0;
    }

    .app-manage .manage-content {
        flex-wrap: wrap;
        border-radius: 0 0 8px 8px;
    }

    .app-manage .icon-down {
        width: 12px;
        height: 15px;
        background-image: url(statics/img/mall/statistic/icon_down.png);
        background-size: 100% 100%;
        background-repeat: no-repeat;
    }

    .app-manage .icon-up {
        width: 12px;
        height: 15px;
        background-image: url(statics/img/mall/statistic/icon_up.png);
        background-size: 100% 100%;
        background-repeat: no-repeat;
    }

    .app-manage .icon-equal {
        width: 16px;
        height: 4px;
        background-image: url(statics/img/mall/statistic/icon_equal.png);
        background-size: 100% 100%;
        background-repeat: no-repeat;
    }

    .app-manage .manage-box {
        font-size: 16px;
        flex-grow: 1;
        color: #333333;
        height: 150px;
        position: relative;
    }

    .app-manage .manage-box .title {
        padding-top: 15px;
        font-size: 30px;
        line-height: 1;
        color: #333333;
    }
    .app-manage .manage-box .refund-title {
        font-size: 12px;
        line-height: 1;
        color: #333333;
        position: absolute;
        bottom: 8px;
    }
    .app-manage .manage-box .compare {
        padding-top: 15px;
    }

    .app-manage .manage-box .compare span {
        color: #92959B;
        font-size: 16px;
        margin-right: 6px;
    }
</style>
<template id="app-manage">
    <div class="app-manage">
        <el-card shadow="never" style="margin-bottom: 10px">
            <div slot="header">
                <span>{{labelTitle}}</span>
                <el-button
                        @click="exportManage"
                        style="float: right;margin-top: -5px"
                        v-if="showStatus !== 'operator'"
                        size="small"
                        type="primary"
                        :underline="false"
                >导出
                </el-button>
            </div>
            <div style="margin-top:10px;margin-bottom: 30px">
                <el-radio-group v-model="manageSearch.timeStatus" size="small" @change="changeManageRadio">
                    <el-radio-button label="today">今日</el-radio-button>
                    <el-radio-button label="yesterday">昨日</el-radio-button>
                    <el-radio-button label="one_week">7日</el-radio-button>
                    <el-radio-button label="all">汇总</el-radio-button>
                </el-radio-group>
                <el-date-picker
                        @change="changeManagePicker"
                        style="margin-left: 15px"
                        size="small"
                        v-model="manageSearch.time"
                        type="daterange"
                        value-format="yyyy-MM-dd"
                        range-separator="至"
                        start-placeholder="开始日期"
                        end-placeholder="结束日期">
                </el-date-picker>
            </div>
            <!-- 员工 -->
            <div v-if="showStatus === `operator`">
                <div v-loading="manageLoading" flex="dir:left cross:center" style="flex-grow: 1;height: 150px;width: 100%">
                    <div class="manage-head user" style="background:#6AD497">
                        <div>浏<br>览<br>数<br>据</div>
                        <img src="statics/img/mall/statistic/browse_icon.png" alt=""/>
                    </div>
                    <div flex="dir:left" class="manage-content" style="background-color: #f0fef6;width: 100%;height: 100%">
                        <div flex="dir:top cross:center main:center" class="manage-box" style="width: 50%">
                            <div flex="dir:left cross:center">
                                <div>访问数（人）</div>
                            </div>
                            <div class="title" style="color:#409EFF;cursor: pointer"
                                 @click="$navigate({r:'mall/user/index'},true)"
                            >{{all_num.user_data.user_num}}
                            </div>
                            <div v-if="rankingTitle" flex="dir:left cross:center" class="compare">
                                <span>{{rankingTitle}}</span>
                                <div v-if="all_num.user_data.user_num_status === `up`" class="icon-up"></div>
                                <div v-if="all_num.user_data.user_num_status === `down`" class="icon-down"></div>
                                <div v-if="all_num.user_data.user_num_status === `equal`" class="icon-down"></div>
                            </div>
                        </div>
                        <!-- -->
                        <div flex="dir:top cross:center main:center" class="manage-box" style="width: 50%">
                            <div flex="dir:left cross:center">
                                <div>访问量（次）</div>
                            </div>
                            <div class="title" v-text="formatNumText"></div>
                            <div v-if="rankingTitle" flex="dir:left cross:center" class="compare">
                                <span>{{rankingTitle}}</span>
                                <div v-if="all_num.user_data.data_num_status === `up`" class="icon-up"></div>
                                <div v-if="all_num.user_data.data_num_status === `down`" class="icon-down"></div>
                                <div v-if="all_num.user_data.data_num_status === `equal`" class="icon-down"></div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div v-else v-loading="manageLoading" flex="dir:left cross:center" style="width: 100%">
                <div :style="{'margin-right': showStatus === `store` ? `0px`: `27px`}" style="flex-grow:1">
                    <div class="manage-head" style="background:#409EFF">
                        <span>支付数据</span>
                        <img style="width: 63px;height: 51px" src="statics/img/mall/statistic/payment_icon.png" alt=""/>
                    </div>
                    <div flex="dir:left" class="manage-content" style="background-color: #F6FAFF">
                        <!-- -->
                        <div flex="dir:top cross:center main:center" class="manage-box" style="width: 50%">
                            <div flex="dir:left cross:center">
                                <div>支付订单数（笔）</div>
                                <el-tooltip class="item" effect="dark" content="不包含取消订单的支付订单数" placement="top">
                                    <i class="el-icon-question"></i>
                                </el-tooltip>
                            </div>
                            <div class="title">{{all_num.order_data.order_num}}</div>
                            <div v-if="rankingTitle" flex="dir:left cross:center" class="compare">
                                <span>{{rankingTitle}}</span>
                                <div v-if="all_num.order_data.order_num_status === `up`" class="icon-up"></div>
                                <div v-if="all_num.order_data.order_num_status === `down`" class="icon-down"></div>
                                <div v-if="all_num.order_data.order_num_status === `equal`" class="icon-equal"></div>
                            </div>
                        </div>
                        <!-- -->
                        <div flex="dir:top cross:center main:center" class="manage-box" style="width: 50%">
                            <div flex="dir:left cross:center">
                                <div>支付人数（人）</div>
                                <el-tooltip class="item" effect="dark" content="不包含取消订单的支付人数" placement="top">
                                    <i class="el-icon-question"></i>
                                </el-tooltip>
                            </div>
                            <div class="title">{{all_num.order_data.user_num}}</div>
                            <div v-if="rankingTitle" flex="dir:left cross:center" class="compare">
                                <span>{{rankingTitle}}</span>
                                <div v-if="all_num.order_data.user_num_status === `up`" class="icon-up"></div>
                                <div v-if="all_num.order_data.user_num_status === `down`" class="icon-down"></div>
                                <div v-if="all_num.order_data.user_num_status === `equal`" class="icon-equal"></div>
                            </div>
                        </div>
                        <!-- -->
                        <div flex="dir:top cross:center main:center" class="manage-box" style="width: 50%">
                            <div flex="dir:left cross:center">
                                <div>支付金额（元）</div>
                                <el-tooltip class="item" effect="dark" content="不包含取消订单的支付金额" placement="top">
                                    <i class="el-icon-question"></i>
                                </el-tooltip>
                            </div>
                            <div class="title">{{all_num.order_data.total_pay_price}}</div>
                            <div v-if="rankingTitle" flex="dir:left cross:center" class="compare">
                                <span>{{rankingTitle}}</span>
                                <div v-if="all_num.order_data.total_pay_price_status === `up`" class="icon-up"></div>
                                <div v-if="all_num.order_data.total_pay_price_status === `down`"
                                     class="icon-down"></div>
                                <div v-if="all_num.order_data.total_pay_price_status === `equal`"
                                     class="icon-equal"></div>
                            </div>
                        </div>
                        <!-- -->
                        <div flex="dir:top cross:center main:center" class="manage-box" style="width: 50%">
                            <div flex="dir:left cross:center">
                                <div>支付商品数（件）</div>
                                <el-tooltip class="item" effect="dark" content="不包含取消订单的支付商品数" placement="top">
                                    <i class="el-icon-question"></i>
                                </el-tooltip>
                            </div>
                            <div class="title">{{all_num.order_data.goods_num}}</div>
                            <div v-if="rankingTitle" flex="dir:left cross:center" class="compare">
                                <span>{{rankingTitle}}</span>
                                <div v-if="all_num.order_data.goods_num_status === `up`" class="icon-up"></div>
                                <div v-if="all_num.order_data.goods_num_status === `down`" class="icon-down"></div>
                                <div v-if="all_num.order_data.goods_num_status === `equal`" class="icon-equal"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="showStatus === `super-account` || showStatus === `sub-account`" style="flex-grow:1;width:300px">
                    <div class="manage-head" style="background:#6AD497">
                        <span>浏览数据</span>
                        <img src="statics/img/mall/statistic/browse_icon.png" alt=""/>
                    </div>
                    <div flex="dir:left" class="manage-content" style="background-color: #F0FEF6">
                        <!-- -->
                        <div flex="dir:top cross:center main:center" class="manage-box" style="width: 100%">
                            <div flex="dir:left cross:center">
                                <div>访客数（人）</div>
                            </div>
                            <div class="title"
                                 style="color:#409EFF;cursor: pointer"
                                 @click="$navigate({r:'mall/user/index'},true)">
                                {{all_num.user_data.user_num}}
                            </div>
                            <div v-if="rankingTitle" flex="dir:left cross:center" class="compare">
                                <span>{{rankingTitle}}</span>
                                <div v-if="all_num.user_data.user_num_status === `up`" class="icon-up"></div>
                                <div v-if="all_num.user_data.user_num_status === `down`" class="icon-down"></div>
                                <div v-if="all_num.user_data.user_num_status === `equal`" class="icon-equal"></div>
                            </div>
                        </div>
                        <!-- -->
                        <div flex="dir:top cross:center main:center" class="manage-box" style="width: 100%">
                            <div flex="dir:left cross:center">
                                <div>访问量（次）</div>
                            </div>
                            <div class="title" v-text="formatNumText"></div>
                            <div v-if="rankingTitle" flex="dir:left cross:center" class="compare">
                                <span>{{rankingTitle}}</span>
                                <div v-if="all_num.user_data.data_num_status === `up`" class="icon-up"></div>
                                <div v-if="all_num.user_data.data_num_status === `down`" class="icon-down"></div>
                                <div v-if="all_num.user_data.data_num_status === `equal`" class="icon-equal"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="showStatus === `super-account` || showStatus === `mch` || showStatus === `sub-account`"
                     style="flex-grow:1;margin-left: 27px">
                    <div class="manage-head" style="background:#FC9F4C">
                        <span>收益数据</span>
                        <img style="width: 63px;height: 51px" src="statics/img/mall/statistic/profit_icon.png" alt=""/>
                    </div>
                    <div flex="dir:left" class="manage-content" style="background-color: #FFF7E9">
                        <!-- -->
                        <div flex="dir:top cross:center main:center" class="manage-box" style="width: 50%">
                            <div flex="dir:left cross:center">
                                <div>余额收益（元）</div>
                                <el-tooltip class="item" effect="dark" content="实际收益：扣除所有退款订单的支付金额" placement="top">
                                    <i class="el-icon-question"></i>
                                </el-tooltip>
                            </div>
                            <div class="title">{{all_num.pay_data[3].amount}}</div>
                            <div v-if="rankingTitle" flex="dir:left cross:center" class="compare">
                                <span>{{rankingTitle}}</span>
                                <div v-if="all_num.pay_data[3].amount_status === `up`" class="icon-up"></div>
                                <div v-if="all_num.pay_data[3].amount_status === `down`" class="icon-down"></div>
                                <div v-if="all_num.pay_data[3].amount_status === `equal`" class="icon-equal"></div>
                            </div>
                            <div v-if="all_num.pay_data[3].refund > 0" class="refund-title">退款：
                                ￥{{all_num.pay_data[3].refund}}
                            </div>
                        </div>
                        <!-- -->
                        <div flex="dir:top cross:center main:center" class="manage-box" style="width: 50%">
                            <div flex="dir:left cross:center">
                                <div>微信收益（元）</div>
                                <el-tooltip class="item" effect="dark" content="实际收益：扣除所有退款订单的支付金额" placement="top">
                                    <i class="el-icon-question"></i>
                                </el-tooltip>
                            </div>
                            <div class="title">{{all_num.pay_data[1].amount}}</div>
                            <div v-if="rankingTitle" flex="dir:left cross:center" class="compare">
                                <span>{{rankingTitle}}</span>
                                <div v-if="all_num.pay_data[1].amount_status === `up`" class="icon-up"></div>
                                <div v-if="all_num.pay_data[1].amount_status === `down`" class="icon-down"></div>
                                <div v-if="all_num.pay_data[1].amount_status === `equal`" class="icon-equal"></div>
                            </div>
                            <div v-if="all_num.pay_data[1].refund > 0" class="refund-title">退款：
                                ￥{{all_num.pay_data[1].refund}}
                            </div>
                        </div>
                        <!-- -->
                        <div flex="dir:top cross:center main:center" class="manage-box" style="width: 50%">
                            <div flex="dir:left cross:center">
                                <div>支付宝收益（元）</div>
                                <el-tooltip class="item" effect="dark" content="实际收益：扣除所有退款订单的支付金额" placement="top">
                                    <i class="el-icon-question"></i>
                                </el-tooltip>
                            </div>
                            <div class="title">{{all_num.pay_data[4].amount}}</div>
                            <div v-if="rankingTitle" flex="dir:left cross:center" class="compare">
                                <span>{{rankingTitle}}</span>
                                <div v-if="all_num.pay_data[4].amount_status === `up`" class="icon-up"></div>
                                <div v-if="all_num.pay_data[4].amount_status === `down`" class="icon-down"></div>
                                <div v-if="all_num.pay_data[4].amount_status === `equal`" class="icon-equal"></div>
                            </div>
                            <div v-if="all_num.pay_data[4].refund > 0" class="refund-title">退款：
                                ￥{{all_num.pay_data[4].refund}}
                            </div>
                        </div>
                        <!-- -->
                        <div flex="dir:top cross:center main:center" class="manage-box" style="width: 50%">
                            <div flex="dir:left cross:center">
                                <div>货到付款收益（元）</div>
                                <el-tooltip class="item" effect="dark" content="实际收益：扣除所有退款订单的支付金额" placement="top">
                                    <i class="el-icon-question"></i>
                                </el-tooltip>
                            </div>
                            <div class="title">{{all_num.pay_data[2].amount}}</div>
                            <div v-if="rankingTitle" flex="dir:left cross:center" class="compare">
                                <span>{{rankingTitle}}</span>
                                <div v-if="all_num.pay_data[2].amount_status === `up`" class="icon-up"></div>
                                <div v-if="all_num.pay_data[2].amount_status === `down`" class="icon-down"></div>
                                <div v-if="all_num.pay_data[2].amount_status === `equal`" class="icon-equal"></div>
                            </div>
                            <div v-if="all_num.pay_data[2].refund > 0" class="refund-title">退款：
                                ￥{{all_num.pay_data[2].refund}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </el-card>
    </div>
</template>
<style>
    /*************/
    .manage-head.user {
        height: 150px;
        width: 80px;
        background: #6AD497;
        border-radius: 8px 0 0 8px;
    }

    .manage-head.user > div {
        line-height: 1.2;
        margin-top: 33px;
    }

    .manage-head.user > img {
        width: 63px;
        height: 51px;
    }
</style>
<script>
    Vue.component('app-manage', {
        template: '#app-manage',
        props: {
            storeId: String,
        },
        data() {
            return {
                manageSearch: {
                    store_id: '',
                    time: null,
                    date_start: '',
                    date_end: '',
                    timeStatus: 'today',
                },
                manageLoading: false,
                timeStr: {
                    'today': '今天',
                    'yesterday': '昨日',
                    'one_week': '7日',
                    'all': '汇总',
                },
                all_num: {
                    user_data: {},
                    order_data: {},
                    order_num: {},
                    pay_data: {
                        1: {},
                        2: {},
                        3: {},
                        4: {},
                    },
                },
            }
        },
        watch: {
            'manageSearch.time'(newData, oldData) {
                if (newData && newData.length >= 2) {
                    this.manageSearch.date_start = newData[0];
                    this.manageSearch.date_end = newData[1];
                } else {
                    this.manageSearch.date_start = '';
                    this.manageSearch.date_end = '';
                }
            },
            'storeId'(newData, oldData) {
                this.manageSearch.store_id = newData;
                this.getData();
            },
        },
        computed: {
            formatNumText() {
                if (this.all_num.user_data.data_num) {
                    let numberFormat = function (value) {
                        let param = {};
                        let k = 10000,
                            sizes = ['', '万', '亿', '万亿'],
                            i;
                        if (value < k) {
                            param.value = value
                            param.unit = ''
                        } else {
                            i = Math.floor(Math.log(value) / Math.log(k));
                            param.value = Math.floor(((value / Math.pow(k, i))) * 100) / 100;
                            param.unit = sizes[i];
                        }
                        return param;
                    }
                    let num = this.all_num.user_data.data_num;
                    let format = numberFormat(num);
                    return format.value + format.unit;
                }
            },
            showStatus() {
                if (this.storeId !== undefined) {
                    return 'store';
                }
                if ("<?= $mch_id ?>" > 0) {
                    return 'mch';
                }
                if ("<?= $user['is_admin'] ?>" == 1) {
                    return 'sub-account';
                }
                if ("<?= $user['is_super_admin'] ?>" == 1) {
                    return 'super-account';
                }
                if ("<?= $user['is_operator'] ?>" == 1) {
                    return 'operator';
                }
            },
            labelTitle() {
                switch (this.showStatus) {
                    case 'operator':
                        return '访问概况';
                    default:
                        return '经营概况';
                }
            },
            rankingTitle() {
                let timeStatus = this.manageSearch.timeStatus;
                let timeStr = {
                    '': '',
                    today: '较昨日',
                    yesterday: '较上周',
                    one_week: '较前七日',
                    all: '',
                };
                return timeStr[timeStatus];
            },
            tableForm() {
                if (this.tableSearch.type === `1`) {
                    return this.payForm;
                }
                if (this.tableSearch.type === `2`) {
                    return this.dataForm;
                }
            }
        },
        mounted() {
            let day = this.manageSearch.timeStatus;
            this.manageSearch.time = this.formatTime(day);
            if (this.storeId === undefined) {
                this.getData();
            }
        },

        methods: {
            exportManage() {
                let type = [];
                switch (this.showStatus) {
                    case 'store':
                        type.push('order_data');
                        break;
                    case 'mch':
                        type.push('order_data', 'pay_data');
                        break;
                    case 'sub-account':
                        type.push('order_data', 'user_data', 'pay_data');
                        break;
                    case 'super-account':
                        type.push('order_data', 'user_data', 'pay_data');
                        break;
                }
                navigateTo(Object.assign({
                    r: 'mall/data-statistics/all-num',
                    flag: 'EXPORT',
                    type: JSON.stringify(type),
                }, this.manageSearch))
            },
            formatTime(limit) {
                let time = ",";
                if (limit === 'today') {
                    time = "<?php
                        $date = new DateTime();
                        $currentTime = $date->format('Y-m-d');
                        echo join(',', [$currentTime, $currentTime]);
                        ?>";
                }
                if (limit === 'yesterday') {
                    time = "<?php
                        $date = new DateTime();
                        $currentTime = $date->format('Y-m-d');
                        $interval = new DateInterval('P1D');
                        $date->sub($interval);
                        echo join(',', [$date->format('Y-m-d'), $date->format('Y-m-d')]);
                        ?>";
                }
                if (limit === 'one_week') {
                    time = "<?php
                        $date = new DateTime();
                        $currentTime = $date->format('Y-m-d');

                        $interval = new DateInterval('P6D');
                        $date->sub($interval);
                        echo join(',', [$date->format('Y-m-d'), $currentTime]);
                        ?>";
                }
                return time.split(',');
            },
            changeManagePicker(row) {
                if (row) {
                    this.manageSearch.timeStatus = '';
                } else {
                    this.manageSearch.timeStatus = 'all';
                }
                this.getData();
            },
            changeManageRadio(row) {
                this.manageSearch.time = this.formatTime(row);
                this.getData();
            },
            getData() {
                setTimeout(() => {
                    let params = Object.assign({r: `mall/data-statistics/all-num`}, this.manageSearch);
                    this.manageLoading = true;
                    request({
                        params,
                    }).then(e => {
                        this.manageLoading = false;
                        if (e.data.code === 0) {
                            this.all_num = e.data.data;
                        }
                    }).catch(e => {
                        this.manageLoading = false;
                    })
                 });
            },
        }
    })
</script>