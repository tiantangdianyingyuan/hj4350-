<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: fjt
 */
?>

<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }
    .border-table {
        height: 42px;
        line-height: 42px;
        border-bottom: 1px solid #EBEEF5;
        border-left: 1px solid #EBEEF5;
        border-right: 1px solid #EBEEF5;
        padding-left: 10px;
    }
    .el-input-group__prepend {
        width: 130px;
    }

</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;" v-loading="listLoading">
        <div slot="header">
            <div>
                <template v-if="is_group != 1">
                    <span style="color: #409eff; cursor: pointer" @click="$navigate({ r: 'plugin/pintuan/mall/activity/index'})">拼团活动</span> /
                </template>
                <span style="color: #409eff; cursor: pointer" @click="$navigate({ r: 'plugin/pintuan/mall/activity/groups'})">活动数据</span> /
                <span>拼团详情</span>
            </div>
        </div>
        <div class="table-body">
            <div >
                <el-form size="small" :inline="true" @submit.native.prevent>
                    <el-tag  :type="group.status === 2 ? 'success' : group.status === 1 ? '' : 'danger'" style="margin-right: 10px">{{group.status_cn}}</el-tag>
                    <el-tag type="info" style="margin-right: 10px">{{group.people_num}}人团</el-tag>
                    <el-form-item >
                        <div class="input-item">
                            <el-input @keyup.enter.native="requestList" size="small" placeholder="请输入订单号搜索"
                                      v-model="search.keyword" clearable @clear='requestList'>
                                <el-select v-model="search.keyword_name" slot="prepend" placeholder="请选择">
                                    <el-option :label="item.label"  v-for="item in search_list" :value="item.value"></el-option>
                                </el-select>
                                <el-button  slot="append" icon="el-icon-search" @click="requestList"></el-button>
                            </el-input>
                        </div>
                    </el-form-item>
                </el-form>
            </div>
            <el-table :data="list" border tooltip-effect="dark" style="width: 100%">
                <el-table-column
                    label="用户信息"
                    width="450">
                    <template slot-scope="scope">
                        <div flex="box:first">
                            <div style="padding-right: 10px;">
                                <app-image mode="aspectFill" :src="scope.row.avatar"></app-image>
                            </div>
                            <div>
                                <app-ellipsis :line="1">{{scope.row.nickname}}</app-ellipsis>
                                <el-tag :type="scope.row.is_parent === 1 ? 'danger' : 'warning'" size="small">{{scope.row.is_parent === 1 ? '团长' : '团员'}}</el-tag>
                            </div>
                        </div>
                    </template>
                </el-table-column>

                <el-table-column
                    label="总金额"
                    width="250">
                    <template slot-scope="scope">
                        <div>总金额：<span style="color: #0000ff;margin-right: 10px">{{scope.row.total_pay_price}}</span>(含运费：<span style="color: #008000;">{{scope.row.express_price}}</span>)</div>
                        <div v-if="scope.row.is_parent === 1">团长优惠：<span style="color: #d9534f;">{{group.preferential_price}}</span> 元</div>
                    </template>
                </el-table-column>

                <el-table-column
                    label="订单号"
                    prop="order_no"
                    >
                </el-table-column>
                <el-table-column
                    label="操作"
                    fixed="right"
                    prop="order_no"
                    >
                    <template slot-scope="scope">
                        <el-button type="text" circle size="mini" @click="refund(scope.row)" v-if="scope.row.is_show_refund">
                            <el-tooltip class="item" effect="dark" content="退款" placement="top">
                                <img src="statics/img/plugins/manual-refund.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div class="border-table" v-if="group.robot_num > 0">
                机器人数：{{group.robot_num}}
            </div>
            <div style="text-align: right;margin: 20px 0;" v-if="pageCount > 1">
                                <el-pagination @current-change="pagination" background layout="prev, pager, next, jumper"
                                               :page-count="pageCount"
                                ></el-pagination>
            </div>
        </div>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                search: {
                    keyword: '',
                    keyword_name: ''
                },
                list: [],
                group: {},
                pageCount: 1,
                id: -1,
                page: 1,
                listLoading: false,
                is_group: 0,
                search_list: []
            };
        },
        created() {
            this.id = getQuery('id');
            this.is_group = getQuery('is_group');
            this.request().then(e => {
                if (e.length > 0) {
                    this.search.keyword_name = e[0].value;
                }
            });
        },
        methods: {
            requestList() {
                this.request();
            },

            async request() {
                this.listLoading = true;
                const e = await request({
                    params: {
                        r: `/plugin/pintuan/mall/activity/groups-orders`,
                        id: this.id,
                        page: this.page,
                        keyword: this.search.keyword,
                        keyword_name: this.search.keyword_name
                    }
                });
                this.listLoading = false;
                this.list = e.data.data.list;
                this.group = e.data.data.group;
                this.pageCount = e.data.data.pagination.page_count;
                this.search_list = e.data.data.search_list;
                return this.search_list;
            },

            detail(item) {
                navigateTo({
                    r: 'plugin/pintuan/mall/activity/groups-orders',
                    id: item.id,
                });
            },

            pagination(currentPage) {
                this.page = currentPage;
                this.request();
            },
            refund(order) {
                this.$confirm('确认退款,是否继续', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning',
                    center: true
                }).then(() => {
                    request({
                        params: {
                            r: 'plugin/pintuan/mall/order/order-cancel',
                        },
                        data: {
                            'order_id': order.order_id,
                            'pintuan_order_id': order.pintuan_order_id,
                        },
                        method: 'post',
                    }).then(e => {
                        if (e.data.code === 0) {
                            this.$message({
                                message: e.data.msg,
                                type: 'success'
                            });
                            this.request();
                        } else {
                            this.$message({
                                message: e.data.msg,
                                type: 'warning'
                            });
                        }
                    }).catch(e => {
                    });
                }).catch(() => {
                });
            }
        }
    });
</script>
