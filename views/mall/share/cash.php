<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/26
 * Time: 14:07
 */
?>
<style>
    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
    }

    .export-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 2;
    }

    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .table-body .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }

    .el-tabs__header {
        margin: 0;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0;">
        <div slot="header">
            <span>提现详情</span>
            <el-form size="small" :inline="true" :model="search" style="float: right;margin-top: -5px;">
                <el-form-item>
                    <app-export-dialog :field_list='exportList' :params="search" @selected="confirmSubmit">
                    </app-export-dialog>
                </el-form-item>
            </el-form>
        </div>
        <el-tabs v-model="activeName" @tab-click="handleClick">
            <el-tab-pane label="全部" name="-1"></el-tab-pane>
            <el-tab-pane label="未审核" name="0"></el-tab-pane>
            <el-tab-pane label="待打款" name="1"></el-tab-pane>
            <el-tab-pane label="已打款" name="2"></el-tab-pane>
            <el-tab-pane label="驳回" name="3"></el-tab-pane>
            <div class="table-body">
                <el-table :data="list" size="small" border v-loading="loading" style="margin-bottom: 15px">
                    <el-table-column label="基本信息">
                        <template slot-scope="scope">
                                <app-image mode="aspectFill" :src="scope.row.user.avatar" style="float: left;margin-right: 10px"></app-image>
                            <div>{{scope.row.user.nickname}}</div>
                            <img src="statics/img/mall/wx.png" v-if="scope.row.user.platform == 'wxapp'" alt="">
                            <img src="statics/img/mall/ali.png" v-else-if="scope.row.user.platform == 'aliapp'" alt="">
                            <img src="statics/img/mall/toutiao.png" v-else-if="scope.row.user.platform == 'ttapp'" alt="">
                            <img src="statics/img/mall/baidu.png" v-else-if="scope.row.user.platform == 'bdapp'" alt="">
                        </template>
                    </el-table-column>
                    <el-table-column label="账户信息">
                        <template slot-scope="scope">
                            <div>提现方式:{{scope.row.pay_type}}</div>
                            <template v-if="scope.row.type == 'wechat'">
                                <div>微信昵称:{{scope.row.extra.name}}</div>
                                <div>微信号:{{scope.row.extra.mobile}}</div>
                            </template>
                            <template v-if="scope.row.type == 'alipay'">
                                <div>支付宝姓名:{{scope.row.extra.name}}</div>
                                <div>支付宝账号:{{scope.row.extra.mobile}}</div>
                            </template>
                            <template v-if="scope.row.type == 'bank'">
                                <div>开户人:{{scope.row.extra.name}}</div>
                                <div>银行卡号:{{scope.row.extra.mobile}}</div>
                                <div>开户行:{{scope.row.extra.bank_name}}</div>
                            </template>
                        </template>
                    </el-table-column>
                    <el-table-column label="提现信息">
                        <template slot-scope="scope">
                            <div>用户申请提现金额:{{scope.row.cash.price}}元</div>
                            <div>手续费:{{scope.row.cash.service_charge}}元</div>
                            <div>实际打款金额:<span style="color: #ff4544">{{scope.row.cash.actual_price}}</span>元</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="状态" prop="status_text"></el-table-column>
                    <el-table-column label="时间">
                        <template slot-scope="scope">
                            <div>申请时间:{{scope.row.time.created_at}}</div>
                            <div v-if="scope.row.status == 1 || scope.row.status == 2">审核时间:{{scope.row.time.apply_at}}</div>
                            <div v-if="scope.row.status == 2">打款时间:{{scope.row.time.remittance_at}}</div>
                            <div v-if="scope.row.status == 3">驳回时间:{{scope.row.time.reject_at}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="备注">
                        <template slot-scope="scope">
                            <div v-if="scope.row.status == 1 || scope.row.status == 2">审核备注:{{scope.row.content.apply_content}}</div>
                            <div v-if="scope.row.status == 2">打款备注:{{scope.row.content.remittance_content}}</div>
                            <div v-if="scope.row.status == 3">驳回备注:{{scope.row.content.reject_content}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作">
                        <template slot-scope="scope">
                            <el-button size="mini" circle style="margin-top: 10px" v-if="scope.row.status == 0" @click="apply(scope.row, 1)">
                                <el-tooltip class="item" effect="dark" content="同意" placement="top">
                                    <img src="statics/img/mall/pass.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button size="mini" circle v-if="scope.row.status == 1" style="margin-top: 10px" @click="apply(scope.row, 2)">
                                <el-tooltip class="item" effect="dark" content="打款" placement="top">
                                    <img src="statics/img/mall/pay.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button size="mini" circle style="margin-left: 10px;margin-top: 10px" v-if="scope.row.status < 2" @click="apply(scope.row, 3)">
                                <el-tooltip class="item" effect="dark" content="拒绝" placement="top">
                                    <img src="statics/img/mall/nopass.png" alt="">
                                </el-tooltip>
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>
                <div flex="box:last cross:center">
                    <div></div>
                    <div>
                        <el-pagination
                            v-if="list.length > 0"
                            style="display: inline-block;float: right;"
                            background :page-size="pagination.pageSize"
                            @current-change="pageChange"
                            layout="prev, pager, next, jumper" :current-page="pagination.current_page"
                            :total="pagination.totalCount">
                        </el-pagination>
                    </div>
                </div>
            </div>
        </el-tabs>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                search: {
                    keyword: '',
                    status: -1,
                },
                loading: false,
                activeName: '-1',
                list: [],
                pagination: null,
                exportList: [],
            };
        },
        mounted() {
            this.loadData();
        },
        methods: {
            confirmSubmit() {
                this.search.status = this.activeName
            },
            loadData(status = -1, page = 1) {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/share/cash-data',
                        status: status,
                        page: page,
                        user_id: getQuery('user_id'),
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                        this.exportList = e.data.data.export_list;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            pageChange(page) {
                this.loadData(this.activeName, page);
            },
            handleClick(tab, event) {
                this.loadData(this.activeName)
            },
            apply(cash, status) {
                this.$prompt('请输入备注', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    beforeClose: (action, instance, done) => {
                        if (action === 'confirm') {
                            instance.confirmButtonLoading = true;
                            instance.confirmButtonText = '执行中...';
                            request({
                                params: {
                                    r: 'mall/share/cash-apply',
                                },
                                method: 'post',
                                data: {
                                    id: cash.id,
                                    status: status,
                                    content: instance.inputValue,
                                }
                            }).then(e => {
                                instance.confirmButtonLoading = false;
                                if (e.data.code === 0) {
                                    this.loadData(this.activeName);
                                    done();
                                } else {
                                    instance.confirmButtonText = '确定';
                                    this.$message.error(e.data.msg);
                                }
                            }).catch(e => {
                                done();
                                instance.confirmButtonLoading = false;
                            });
                        } else {
                            done();
                        }
                    }
                }).then(value => {

                }).catch(e => {
                    this.$message({
                        type: 'info',
                        message: '取消输入'
                    });
                });
            }
        }
    })
</script>
