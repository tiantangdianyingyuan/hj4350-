<?php
Yii::$app->loadViewComponent('app-order');
?>
<style>
    .form-body {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 20px;
    }

    .input-item {
        display: inline-block;
        width: 350px;
        margin: 0 0 20px;
    }

    .input-item .el-input-group__prepend {
        background-color: #fff;
    }

    .input-item .el-input__inner:hover{
        border: 1px solid #dcdfe6;
        outline: 0;
    }

    .input-item .el-input__inner:focus{
        border: 1px solid #dcdfe6;
        outline: 0;
    }

    .input-item .el-input-group__append {
        background-color: #fff;
        border-left: 0;
        width: 10%;
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        padding: 15px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;" v-loading="loading">
        <!-- 标题栏 -->
        <div slot="header">
            <span>售后订单</span>
            <app-export-dialog action_url="index.php?r=plugin/advance/mall/order/refund"
                               style="float: right;margin-top: -5px" :field_list='export_list'
                               :params="search">
            </app-export-dialog>
        </div>
        <div class="form-body">
            <el-form size="small" :inline="true" :model="search">
                <el-form-item style="margin-bottom: 0">
                    <!-- 时间选择框 -->
                    <el-date-picker
                        v-model="search.time"
                        type="datetimerange"
                        value-format="yyyy-MM-dd HH:mm:ss"
                        range-separator="至"
                        start-placeholder="开始日期"
                        end-placeholder="结束日期">
                    </el-date-picker>
                </el-form-item>
                <!-- 搜索框 -->
                <el-form-item style="margin-bottom: 0">
                    <div class="input-item">
                        <el-input size="small" placeholder="请输入搜索内容" v-model="search.keyword">
                            <el-select style="width: 120px" slot="prepend" v-model="search.keyword_1">
                                <el-option label="订单号" value="1"></el-option>
                                <el-option label="用户名" value="2"></el-option>
                                <el-option label="用户ID" value="4"></el-option>
                                <el-option label="商品名称" value="5"></el-option>
                                <el-option label="收件人" value="3"></el-option>
                                <el-option label="收件人电话" value="6"></el-option>
                            </el-select>
                            <el-button slot="append" icon="el-icon-search" @click="searchOrder"></el-button>
                        </el-input>
                    </div>
                </el-form-item>
            </el-form>
            <!-- 订单状态选择 -->
            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-tab-pane label="全部" name="0"></el-tab-pane>
                <el-tab-pane label="待处理" name="1"></el-tab-pane>
                <el-tab-pane label="已处理" name="2"></el-tab-pane>
            </el-tabs>
            <!-- 订单内容 -->
            <app-order :select_list="selectList" @get="getList" :list="list" refund :other_list="address">
                <template slot-scope="item">
                    <!-- 售后类型 -->
                    <div class="app-order-info" style="width: 8%">
                        <div>
                            <div v-if="item.item.type == 1" type="danger">退货退款</div>
                            <div v-if="item.item.type == 2" type="warning">换货</div>
                        </div>
                        <el-tooltip style="margin-top: 10px;" effect="dark" :content="item.item.seller_comments"
                                    placement="bottom">
                            <div slot="content">
                                <span v-if="item.item.merchant_remark != ''">{{item.item.merchant_remark}}</span>
                            </div>
                            <div v-if="item.item.merchant_remark != ''"><img src="statics/img/mall/order/remark.png" alt=""></div>
                        </el-tooltip>
                    </div>
                    <!-- 订单状态 -->
                    <div class="app-order-info" style="width: 7.5%">
                        <div>
                            <div size="medium" hit v-if="item.item.status == 1" type="info">
                                <span>待处理</span>
                                <el-tooltip v-if="item.item.pay_type == 2" effect="dark" content="货到付款方式的退款需要线下与客户自行协商"
                                            placement="top">
                                    <i class="header-icon el-icon-info"></i>
                                </el-tooltip>
                            </div>
                            <div v-if="item.item.status == 2">已同意
                            </div>
                            <div v-if="item.item.status == 3">已拒绝
                            </div>
                            <div style="margin-top: 10px;" v-if="item.item.status == 2 && item.item.is_send == 0"
                                 type="warning">等待买家发货
                            </div>
                            <div style="margin-top: 10px;" v-if="item.item.status == 2 && item.item.is_send == 1"
                                 type="success">买家已发货
                            </div>
                        </div>
                    </div>
                    <!-- 退款金额 -->
                    <div class="app-order-info" :style="{'width': price}">
                        <div>￥{{item.item.refund_price}}</div>
                    </div>
                </template>
            </app-order>
            <div flex="box:last cross:center">
                <div style="visibility: hidden">
                    <el-button plain type="primary" size="small">批量操作1</el-button>
                    <el-button plain type="primary" size="small">批量操作2</el-button>
                </div>
                <div>
                    <el-pagination
                        v-if="pagination"
                        style="display: inline-block;float: right;"
                        background
                        :page-size="pagination.pageSize"
                        @current-change="pageChange"
                        layout="prev, pager, next, jumper"
                        :total="pagination.total_count">
                    </el-pagination>
                </div>
            </div>
        </div>
    </el-card>

</div>

<style>
</style>

<script>
    new Vue({
        el: '#app',
        data() {
            return {
                search: {
                    time: null,
                    r: 'plugin/advance/mall/order/refund',
                    keyword: '',
                    keyword_1: '1',
                    date_start: '',
                    date_end: '',
                },
                loading: false,
                pagination: null,
                activeName: 0,
                list: [],
                selectList: [{value:'1',name:'订单号'},{value:'2',name:'用户名'},{value:'4',name:'用户ID'},{value:'5',name:'商品名称'},{value:'3',name:'收件人'},{value:'6',name:'收件人电话'}],
                address: [],
                price: '9%',
                export_list: [],
            };
        },
        created() {
            this.getList();
        },
        methods: {
            // 获取列表
            getList() {
                this.loading = true;
                loadList('plugin/advance/mall/order/refund').then(e => {
                    this.$message({
                        message: '请求成功',
                        type: 'success'
                    });
                    this.loading = false;
                    this.list = e.list;
                    let detail = [];
                    for(let i = 0;i < this.list.length;i++) {
                        this.list[i].detail = [this.list[i].detail]
                    }
                    this.pagination = e.pagination;
                    this.address = e.address;
                    this.export_list = e.export_list;
                });
            },
            // 分页
            pageChange(page) {
                this.loading = true;
                this.list = [];
                loadList('plugin/advance/mall/order/refund', page).then(e => {
                    this.loading = false;
                    this.list = e.list;
                    this.pagination = e.pagination;
                });
            },
            // 搜索
            searchOrder() {
                if (this.search.time != null) {
                    this.search.date_start = this.search.time[0];
                    this.search.date_end = this.search.time[1];
                }
                this.loading = true;
                request({
                    params: this.search,
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        let detail = [];
                        for(let i = 0;i < this.list.length;i++) {
                            this.list[i].detail = [this.list[i].detail]
                        }
                        this.pagination = e.data.data.pagination;
                    }

                }).catch(e => {
                });
            },
            // 切换订单状态
            handleClick(e) {
                this.list = [];
                let status = e.name - 1;
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/advance/mall/order/refund',
                        status: status,
                    },
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        let detail = [];
                        for(let i = 0;i < this.list.length;i++) {
                            this.list[i].detail = [this.list[i].detail]
                        }
                        this.pagination = e.data.data.pagination;
                        console.log(1235);
                    }

                }).catch(e => {
                });
            }
        }
    });
</script>
