<style>
    .app-order-info .info-data {
        text-align: center;
        flex-grow: 1;
        border-left: 1px dashed #EFF1F7;
    }

    .app-order-info .info-data:first-of-type {
        border-left: 0;
    }

    .app-order-info .info-data .value {
        color: #409EFF;
        cursor: pointer;
        font-size: 28px;
    }

    .app-order-info .info-data .label {
        color: #92959B;
        font-size: 16px;
    }
</style>
<template id="app-order-info">
    <div class="app-order-info">
        <div class="table-body" flex="dir:left" v-loading="loading">
            <div class="info-data">
                <div class="value" @click="nav('wait_send')">{{all_data.wait_send_num}}</div>
                <div class="label">
                    <span>待发货订单（笔）</span>
                </div>
            </div>
            <div class="info-data">
                <div class="value" @click="nav('pro_order')">{{all_data.pro_order}}</div>
                <div class="label">
                    <span>售后订单（笔）</span>
                </div>
            </div>
            <div class="info-data">
                <div class="value" @click="nav('wait_pay')">{{all_data.wait_pay_num}}</div>
                <div class="label">
                    <span>待付款订单（笔）</span>
                </div>
            </div>
            <div class="info-data" v-if="isUser">
                <div class="value" @click="nav('user_count')">{{all_data.user_count}}</div>
                <div class="label">
                    <span>总用户数（人）</span>
                    <el-tooltip class="item" effect="dark" content="全平台授权用户总数" placement="top">
                        <i class="el-icon-question"></i>
                    </el-tooltip>
                </div>
            </div>
            <div class="info-data" v-if="isGoods">
                <div class="value" @click="nav('goods_num')">{{all_data.goods_num}}</div>
                <div class="label">
                    <span>商品数（款）</span>
                    <el-tooltip class="item" effect="dark" content="计算销售中，下架中和售罄的商品款数" placement="top">
                        <i class="el-icon-question"></i>
                    </el-tooltip>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    Vue.component('app-order-info', {
        template: '#app-order-info',
        data() {
            return {
                // 总体数据
                all_data: {
                    goods_num: "--",
                    wait_pay_num: "--",
                    pro_order: "--",
                    user_count: "--",
                    wait_send_num: "--",
                },
                search: {
                    store_id: '',
                },
                loading: false,
            }
        },
        props: {
            storeId: String,
            isGoods: {
                type: Boolean,
                default: true,
            },
            isUser: {
                type: Boolean,
                default: true,
            },
        },
        watch: {
            'storeId'(newData, oldData) {
                this.search.store_id = newData;
                this.getData();
            },
        },
        mounted() {
            if (this.storeId === undefined) {
                this.getData();
            }
        },
        methods: {
            nav(type) {
                let r = '';
                let params = {};
                switch (type) {
                    case 'wait_send':
                        r = 'mall/order/index';
                        params = {
                            'status': 1,
                        };
                        break;
                    case 'pro_order':
                        r = 'mall/order/refund';
                        break;
                    case 'wait_pay':
                        r = 'mall/order/index';
                        params = {
                            'status': 0,
                        };
                        break;
                    case 'user_count':
                        r = 'mall/user/index';
                        break;
                    case 'goods_num':
                        r = 'mall/goods/index';
                        break;
                }

                navigateTo(Object.assign({
                    r: r
                }, params), true);
            },
            getData() {
                this.loading = true;
                let params = Object.assign({r: 'mall/data-statistics/all-data'}, this.search);
                request({
                    params,
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.all_data = e.data.data;
                    }
                }).catch(e => {
                    this.loading = false;
                })
            },
        }
    });
</script>