<?php
$user = Yii::$app->user->identity->identity;
$mch_id = Yii::$app->user->identity->mch_id;

?>
<style>
    .app-table .el-radio-button__inner {
        color: #444444;
        background: #EBEEF5;
    }

    .app-table .trend {
        background-color: #f3f5f6;
        padding: 0 30px;
        margin: 20px 0 40px;
    }

    .app-table .trend .el-tabs__item {
        padding: 10px 20px !important;
        height: 60px !important;
    }

    .app-table .pay-info {
        padding: 40px 0;
        display: flex;
        justify-content: space-between;
        margin: 0 -1.5%;
    }

    .app-table .pay-info .pay-info-item {
        width: 22%;
        margin: 0 1.5%;
        text-align: center;
        border: 1px solid #EBEEF5;
        border-radius: 10px;
        height: 150px;
        position: relative;
        cursor: pointer;
    }

    .app-table .pay-info .pay-info-item.active {
        border: 1px solid #3399FF;
    }

    .app-table .pay-info .pay-info-item img {
        display: none;
    }

    .app-table .pay-info .pay-info-item.active img {
        position: absolute;
        top: 0;
        left: 0;
        display: block;
    }


    .app-table .echarts-title {
        color: #92959B;
        display: flex;
        font-size: 16px;
        margin-left: 45px;
    }

    .app-table .echarts-title-item {
        margin-right: 45px;
        display: flex;
        align-items: center;
    }

    .app-table .echarts-title-item .echarts-title-icon {
        height: 16px;
        width: 16px;
        margin-right: 10px;
    }

    .app-table .manage-box {
        font-size: 16px;
        flex-grow: 1;
        color: #333333;
        padding-top: 0;
        height: 100%;
    }

    .app-table .manage-box .title {
        padding-top: 15px;
        font-size: 30px;
        line-height: 1;
        color: #333333;
    }

    .app-table .manage-box .compare span {
        color: #92959B;
        font-size: 16px;
        margin-right: 6px;
    }
</style>
<template id="app-table">
    <div class="app-table">
        <el-card shadow="never" v-if="showStatus === 'store' || showStatus === 'mch' || showStatus === 'sub-account' || showStatus === 'super-account'">
            <div slot="header">
                <span v-if="isShowType">趋势概况</span>
                <span v-else>支付数据</span>
            </div>
            <div v-if="isShowType" class="trend">
                <el-tabs v-model="tableSearch.type" @tab-click="changeTableTabs">
                    <el-tab-pane label="支付数据" name="1"></el-tab-pane>
                    <el-tab-pane label="收益数据" name="2"></el-tab-pane>
                </el-tabs>
            </div>
            <div>
                <el-radio-group v-model="tableSearch.timeStatus" size="small" @change="changeTableRadio">
                    <el-radio-button label="today">今日</el-radio-button>
                    <el-radio-button label="yesterday">昨日</el-radio-button>
                    <el-radio-button label="one_week">7日</el-radio-button>
                </el-radio-group>
            </div>

            <div v-loading="tableLoading">
                <div class="pay-info" style="padding-top: 30px">
                    <div v-for="table in tableForm"
                         :key="table.sign"
                         class="pay-info-item manage-box"
                         :class="{active: table.is_show}"
                         @click="chooseInfo(table)"
                         flex="dir:top cross:center main:center"
                    >
                        <img src="statics/img/mall/statistic/active.png" alt="">
                        <div flex="dir:left cross:center">
                            <div>{{table.label}}</div>
                            <el-tooltip v-if="table.remark" class="item" effect="dark" :content="table.remark"
                                        placement="top">
                                <i class="el-icon-question"></i>
                            </el-tooltip>
                        </div>
                        <div class="title">{{table.value}}</div>
                    </div>
                </div>
                <div class="echarts-title">
                    <div v-for="table in tableForm" :key="table.sign" class="echarts-title-item" v-if="table.is_show">
                        <div :style="{'background-color': table.color}" class="echarts-title-icon"></div>
                        <div>{{table.label}}</div>
                    </div>
                </div>
            </div>
            <div style="overflow-x: auto">
                <div id="echarts" style="height:18rem;width: 1570px"></div>
            </div>
        </el-card>
    </div>
</template>
<script>
    Vue.component('app-table', {
        template: '#app-table',
        data() {
            return {
                tableLoading: false,
                payForm: [
                    {
                        label: '支付订单数（笔）',
                        value: '0',
                        sign: 'order',
                        is_show: true,
                        remark: '不包含取消订单的支付订单数',
                        color: '#3399ff',
                    },
                    {
                        label: '支付人数（人）',
                        value: '0',
                        sign: 'people',
                        is_show: true,
                        remark: '不包含取消订单的支付人数',
                        color: '#FFA360',
                    },
                    {
                        label: '支付金额（元）',
                        value: '0',
                        sign: 'price',
                        is_show: true,
                        remark: '不包含取消订单的支付金额',
                        color: '#4BC282',
                    },
                    {
                        label: '支付商品数（件）',
                        value: '0',
                        sign: 'num',
                        is_show: true,
                        remark: '不包含取消订单的支付商品数',
                        color: '#FF8585',
                    },
                ],
                dataForm: [
                    {
                        label: '余额收益（元）',
                        value: '0',
                        sign: 'balance',
                        is_show: true,
                        remark: '实际收益：扣除所有退款订单的支付金额',
                        color: '#3399ff',
                    },
                    {
                        label: '微信收益（元）',
                        value: '0',
                        sign: 'wx',
                        is_show: true,
                        remark: '实际收益：扣除所有退款订单的支付金额',
                        color: '#FFA360',
                    },
                    {
                        label: '支付宝收益（元）',
                        value: '0',
                        sign: 'ali',
                        is_show: true,
                        remark: '实际收益：扣除所有退款订单的支付金额',
                        color: '#4BC282',
                    },
                    {
                        label: '货到付款收益（元）',
                        value: '0',
                        sign: 'huohao',
                        is_show: true,
                        remark: '实际收益：扣除所有退款订单的支付金额',
                        color: '#FF8585',
                    },
                ],

                /** ss */
                tableSearch: {
                    store_id: '',
                    type: '',
                    platform: '',
                    time: null,
                    date_start: '',
                    date_end: '',
                    timeStatus: 'today',
                },

                // 时段
                pay_day: [],
                // 图表参数
                series: [
                    {
                        name: '',
                        type: 'line',
                        data: [],
                    },
                    {
                        name: '',
                        type: 'line',
                        data: [],
                    },
                    {
                        name: '',
                        type: 'line',
                        data: [],
                    },
                    {
                        name: '',
                        type: 'line',
                        data: [],
                    },
                ],
            }
        },
        watch: {
            'storeId'(newData, oldData) {
                this.tableSearch.store_id = newData;
                this.getTable();
            },
            'tableSearch.time'(newData, oldData) {
                if (newData && newData.length >= 2) {
                    this.tableSearch.date_start = newData[0];
                    this.tableSearch.date_end = newData[1];
                } else {
                    this.tableSearch.date_start = '';
                    this.tableSearch.date_end = '';
                }
            },
            'tableForm'(newData, oldData) {
                if (newData) {
                    newData.forEach((item, key) => {
                        this.series[key].name = item.label;
                    })
                }
            },
        },
        computed: {
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
            tableForm() {
                if (this.tableSearch.type === `1`) {
                    return this.payForm;
                }
                if (this.tableSearch.type === `2`) {
                    return this.dataForm;
                }
            }
        },
        props: {
            isShowType: {
                type: Boolean,
                default: true,
            },
            storeId: String,
        },
        mounted: function () {
            this.tableSearch.type = '1';
            this.tableSearch.time = this.formatTime(this.tableSearch.timeStatus);
            if (this.storeId === undefined) {
                this.getTable();
            }
        },

        methods: {
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

            changeTableRadio(row) {
                this.tableSearch.time = this.formatTime(row);
                this.getTable();
            },
            changeTableTabs() {
                this.getTable();
            },

            // 选择图表显示内容
            chooseInfo(row) {
                const self = this;
                row.is_show = !row.is_show;
                let myChart = echarts.init(document.getElementById('echarts'));
                let tableForm = self.tableForm;

                let selected = {};
                tableForm.forEach(table => {
                    selected[table.label] = table.is_show;
                });

                myChart.setOption({
                    legend: {
                        show: false,
                        data: tableForm.map(table => {
                            return table.label
                        }),
                        selected,
                    }
                })
            },
            // 生成图表
            form() {
                let that = this;
                var myChart = echarts.init(document.getElementById('echarts'));
                myChart.setOption({
                    tooltip: {
                        trigger: 'axis',
                        backgroundColor: '#fff',
                        textStyle: {color: '#303133'},
                        padding: 20,
                        extraCssText: 'box-shadow: 0 0 4px rgba(0, 0, 0.1);',
                        formatter: function (params, ticket, callback) {
                            let axisValue = params[0].axisValue;
                            if (that.tableSearch.timeStatus === 'yesterday') {
                                axisValue = "<?= (new DateTime())->sub(new DateInterval('P1D'))->format('Y-m-d') ?>" + ` ${axisValue}时`;
                            }
                            if (that.tableSearch.timeStatus === 'today') {
                                axisValue = "<?= (new DateTime())->format('Y-m-d') ?>" + ` ${axisValue}时`;
                            }
                            let list = '';
                            params.forEach(item => {
                                list += item.marker + item.seriesName + ': ' + item.value + '<br>';
                            })
                            return axisValue + "<br>" + list;
                        }
                    },
                    legend: {
                        show: false,
                        data: that.tableForm.map(table => {
                            return table.label;
                        })
                    },
                    color: ['#3399FF', '#FFA360', '#4BC282', '#FF8585'],
                    grid: {
                        left: '0',
                        right: '4%',
                        bottom: '3%',
                        containLabel: true
                    },
                    xAxis: {
                        type: 'category',
                        boundaryGap: false,
                        data: that.pay_day
                    },
                    yAxis: {
                        splitLine: {
                            show: true,
                            lineStyle: {
                                type: 'dashed'
                            }
                        },
                        axisLine: {
                            show: false
                        },
                        axisLabel: {
                            show: false
                        },
                        axisLabel: {
                            show: false
                        },
                        type: 'value'
                    },
                    series: that.series
                });
                myChart.showLoading({text: '正在加载数据'});
            },
            //初始化
            getTable() {
                const self = this;
                if (self.showStatus === 'operator') {
                    return;
                }
                self.pay_day = [];
                self.series[0].data = [];
                self.series[1].data = [];
                self.series[2].data = [];
                self.series[3].data = [];
                self.form();
                setTimeout(() => {
                    self.tableLoading = true;
                    let params = Object.assign({r: `mall/data-statistics/table`}, self.tableSearch);
                    request({
                        params,
                    }).then(e => {
                        self.tableLoading = false;
                        if (e.data.code === 0) {
                            let table_list = e.data.data.list;
                            let func = (type, table) => {
                                if (type === '1') {
                                    this.series[0].data.push(table.order_num);
                                    this.series[1].data.push(table.user_num);
                                    this.series[2].data.push(table.total_pay_price);
                                    this.series[3].data.push(table.goods_num);
                                }
                                if (type === '2') {
                                    this.series[0].data.push(table.balance_amount);
                                    this.series[1].data.push(table.wx_amount);
                                    this.series[2].data.push(table.ali_amount);
                                    this.series[3].data.push(table.huodao_amount);
                                }
                            }
                            table_list.forEach(table => {
                                this.pay_day.push(table.created_at);
                                func(self.tableSearch.type, table);
                            })
                            if (self.tableSearch.type === '1') {
                                this.payForm[0].value = e.data.data.table_data.order_num;
                                this.payForm[1].value = e.data.data.table_data.user_num;
                                this.payForm[2].value = e.data.data.table_data.total_pay_price;
                                this.payForm[3].value = e.data.data.table_data.goods_num;
                            }
                            if (self.tableSearch.type === '2') {
                                this.dataForm[0].value = e.data.data.table_data.balance_amount;
                                this.dataForm[1].value = e.data.data.table_data.wx_amount;
                                this.dataForm[2].value = e.data.data.table_data.ali_amount;
                                this.dataForm[3].value = e.data.data.table_data.huodao_amount;
                            }
                            this.form();
                            var myChart = echarts.init(document.getElementById('echarts'));
                            myChart.hideLoading();
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        this.tableLoading = false;
                    })
                })

            },
        }
    });
</script>