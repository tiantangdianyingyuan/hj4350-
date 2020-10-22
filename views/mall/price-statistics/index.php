<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .toolbar {
        padding: 20px 20px 0;
        background-color: #fff;
    }

    .bill-table-head {
        padding: 12px;
        color: #606266;
        background: #f3f5f6;
        width: 100%;
        min-width: calc(1562px - 24px);
    }

    .bill-table-head .tab {
        width: 310px;
        min-width: 310px;
    }

    .bill-table-head .tab .el-icon-question {
        margin-top: 2px;
        margin-left: 3px;
    }

    .bill-table-head .tab .label {
        flex-shrink: 0;
    }

    .bill-jia {
        line-height: 1;
        margin: 0 auto;
        font-weight: bold;
        font-size: 24px;
        display: inline-block;
    }

    .item {
        color: #92959B;
        margin-left: 1px;
    }

    .innerbox {
        overflow-x: auto;
    }

    .innerbox::-webkit-scrollbar {
        height: 4px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>对账单</span>
            <el-button @click="$navigate(Object.assign({r: 'mall/price-statistics/index', flag: 'EXPORT'},search))"
                       style="float: right; margin: -5px 0"
                       type="primary"
                       size="small"
            >导出对账单
            </el-button>
        </div>

        <!--工具条 过滤表单和新增按钮-->
        <div class="toolbar" style="margin-bottom: 20px">
            <el-form size="small" :inline="true" :model="search">
                <!-- 搜索框 -->
                <el-form-item prop="platform" label="所属平台">
                    <el-select style="width: 120px;" size="small" v-model="search.platform" @change='searchList'>
                        <el-option key="all" label="全部平台" value=""></el-option>
                        <el-option key="wxapp" label="微信" value="wxapp"></el-option>
                        <el-option key="aliapp" label="支付宝" value="aliapp"></el-option>
                        <el-option key="ttapp" label="抖音/头条" value="ttapp"></el-option>
                        <el-option key="bdapp" label="百度" value="bdapp"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item prop="time">
                    <span style="color:#606266;padding-right: 10px">账单时间</span>
                    <el-date-picker
                            v-model="search.time"
                            @change="searchList"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            type="datetimerange"
                            range-separator="至"
                            start-placeholder="开始日期"
                            end-placeholder="结束日期"
                    ></el-date-picker>
                </el-form-item>
                <el-form-item>
                    <el-link style="margin-right: 15px" type="text" @click="handleDate(`one_week`)" :underline="false">
                        7日
                    </el-link>
                    <el-link style="margin-right: 15px" type="text" @click="handleDate(`one_month`)" :underline="false">
                        近一个月
                    </el-link>
                </el-form-item>
                <el-form-item v-if="false">
                    <span style="color:#606266;margin-right: 10px">支付方式</span>
                    <el-select v-model="search.pay_type" @change="searchList">
                        <el-option
                                v-for="payType in optionsPayType"
                                :key="payType.label"
                                :label="payType.label"
                                :value="payType.value">
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item v-if="false">
                    <span style="color:#606266;margin-right: 10px">收益来源</span>
                    <el-select v-model="search.sign" @change="searchList">
                        <el-option key="" label="全部" value=""></el-option>
                        <el-option
                                v-for="source in optionSource"
                                :key="source.sign"
                                :label="source.name"
                                :value="source.sign">
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item v-if="search.time || search.platform">
                    <el-link type="info" @click="clear" :underline="false" style="color:#409EFF">清空筛选条件</el-link>
                </el-form-item>
            </el-form>
        </div>

        <!-- 列表 -->
        <div class="innerbox table-body" v-loading="loading">
            <div class="bill-table-head" flex="dir:left cross:center">
                <div class="tab" flex="dir:left cross:center">
                    <span class="label">订单收益（元）</span>
                    <el-tooltip class="item" effect="dark" content="扣除所有退款订单金额的实际订单收益" placement="top">
                        <i class="el-icon-question"></i>
                    </el-tooltip>
                    <div class="bill-jia">+</div>
                </div>
                <div class="tab" flex="dir:left cross:center">
                    <span class="label">会员购买收益（元）</span>
                    <el-tooltip class="item" effect="dark" content="购买会员等级的收益" placement="top">
                        <i class="el-icon-question"></i>
                    </el-tooltip>
                    <div class="bill-jia">+</div>
                </div>
                <div class="tab" flex="dir:left cross:center">
                    <span class="label">余额充值收益（元）</span>
                    <el-tooltip class="item" effect="dark" content="用户在线充值的余额收益" placement="top">
                        <i class="el-icon-question"></i>
                    </el-tooltip>
                    <div class="bill-jia">−</div>
                </div>
                <div class="tab" flex="dir:left cross:center">
                    <span class="label">提现支出（元）</span>
                    <el-tooltip v-if="cash_price_desc" class="item" effect="dark" :content="cash_price_desc"
                                placement="top">
                        <i class="el-icon-question"></i>
                    </el-tooltip>
                    <div class="bill-jia">=</div>
                </div>
                <div class="tab" flex="dir:left cross:center">
                    <span class="label">实际收益（元）</span>
                </div>
            </div>
            <div v-for="(item,index) in list" class="bill-table-head" style="background: #FFFFFF"
                 flex="dir:left cross:center">
                <div class="tab" v-text="item.order_price"></div>
                <div class="tab" v-text="item.member_price"></div>
                <div class="tab" v-text="item.balance"></div>
                <div class="tab" v-text="item.cash_price"></div>
                <div class="tab" v-text="item.income_price"></div>
            </div>
            <!--<el-table :data="list" style="width: 100%;margin-bottom: 15px" :show-header="false">-->
            <!--    <el-table-column prop="order_price" label="订单收益(元)" width="244"></el-table-column>-->
            <!--    <el-table-column prop="member_price" label="会员购买收益(元)" width="244"></el-table-column>-->
            <!--    <el-table-column prop="balance" label="余额充值收益(元)" width="244"></el-table-column>-->
            <!--    <el-table-column prop="cash_price" label="提现支出(元)" width="244"></el-table-column>-->
            <!--    <el-table-column prop="income_price" label="实际收益（元）"></el-table-column>-->
            <!--</el-table>-->
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            let optionsPayType = [
                {
                    'label': '全部',
                    'value': '',
                }, {
                    'label': '微信支付',
                    'value': '1',
                }, {
                    'label': '支付宝支付',
                    'value': '2',
                }, {
                    'label': '余额支付',
                    'value': '3',
                }, {
                    'label': '货到付款',
                    'value': '4'
                }
            ];
            return {
                optionsPayType,
                optionSource: [],
                list: [],
                loading: false,

                flag: '',
                search: {
                    sign: '',
                    pay_type: '',
                    time: '',
                    start_time: '',
                    end_time: '',
                    platform: '',
                },
                cash_price_desc: '',
            };
        },
        watch: {
            'search.time'(newData, oldData) {
                if (newData && newData.length >= 2) {
                    this.search.start_time = newData[0];
                    this.search.end_time = newData[1];
                } else {
                    this.search.start_time = '';
                    this.search.end_time = '';
                }
            }
        },
        methods: {
            formatTime(limit) {
                let time = ",";
                if (limit === 'one_week') {
                    time = "<?php
                        $date = new DateTime();
                        $date2 = clone $date;

                        $endTime = $date->format('Y-m-d');
                        $startTime = $date2->sub(new DateInterval('P7D'))->format('Y-m-d');
                        echo join(',', [$startTime, $endTime]);
                        ?>";
                }
                if (limit === 'one_month') {
                    time = "<?php
                        $date = new DateTime();
                        $date2 = clone $date;

                        $endTime = $date->format('Y-m-d');
                        $startTime = $date2->sub(new DateInterval('P1M'))->format('Y-m-d');
                        echo join(',', [$startTime, $endTime]);
                        ?>";
                }
                return time.split(',');
            },
            handleDate(limit) {
                this.search.time = this.formatTime(limit);
                this.getList();
            },
            clear() {
                this.search = {
                    sign: '',
                    pay_type: '',
                    time: '',
                    start_time: '',
                    end_time: '',
                    platform: '',
                };
                this.getList();
            },

            searchList() {
                this.getList();
            },
            getList() {
                setTimeout(() => {
                    this.loading = true;
                    let param = Object.assign({r: 'mall/price-statistics/index'}, this.search);
                    console.table(param);
                    request({
                        params: param,
                    }).then(e => {
                        this.loading = false;
                        if (e.data.code === 0) {
                            let {order_price, member_price, balance, cash_price, income_price} = e.data.data;
                            this.list = [{order_price, member_price, balance, cash_price, income_price}];
                            this.optionSource = e.data.data.sign;
                            this.cash_price_desc = e.data.data.cash_map;
                        }
                    }).catch(e => {
                        this.loading = false;
                    });
                })
            },
        },
        mounted() {
            this.getList();
        }
    })
</script>
