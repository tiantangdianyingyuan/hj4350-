<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/29 15:59
 */
?>

<style scoped>
    .t-color-red {
        color: #d9534f;
        margin: 0 3px;
    }

    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        width: 250px;
        margin: 0 0 20px 20px;
    }

    .input-item .el-input__inner {
        border-right: 0;
        height: 32px;
    }

    .input-item .el-input__inner:hover {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input__inner:focus {
        border: 1px solid #dcdfe6;
        border-right: 0;
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

    .table-body .el-table .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }
</style>

<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <!-- 标题栏 -->
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer"
                                          @click="$navigate({r:'plugin/pintuan/mall/order-groups/index'})">拼团管理</span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>详情</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="table-body">
            <el-form style="display: flex;">
                <el-form-item v-if="list[0]">
                    <el-tag v-if="robotNum > 0">机器人数：{{robotNum}}</el-tag>
                    <el-tag v-if="list[0].pintuanOrder.status == 1">拼团中</el-tag>
                    <el-tag v-else-if='list[0].pintuanOrder.status == 2' type="success">拼团成功</el-tag>
                    <el-tag v-else='list[0].pintuanOrder.status == 3' type="danger">拼团失败</el-tag>
                </el-form-item>
                <el-form-item class="input-item">
                    <el-input v-model="keyword" placeholder="请输入订单号搜索">
                        <el-button slot="append" icon="el-icon-search" @click="searchOrder"></el-button>
                    </el-input>
                </el-form-item>
            </el-form>
            <el-table stripe border v-loading="tableLoading" :data="list">
                <el-table-column prop="id" width="120" label="ID"></el-table-column>
                <el-table-column prop="order_no" width="250" label="订单号"></el-table-column>
                <el-table-column label="用户信息" width="200">
                    <template slot-scope="scope">
                        <el-tag size="mini" type="danger" v-if="scope.row.orderRelation.is_parent == 1">团长</el-tag>
                        <el-tag size="mini" v-else>团员</el-tag>
                        <app-ellipsis :line="1">{{scope.row.user.nickname}}</app-ellipsis>
                    </template>
                </el-table-column>
                <el-table-column label="商品信息" width="350px">
                    <template slot-scope="scope">
                        <div flex="box:first">
                            <div style="padding-right: 10px">
                                <app-image width="60" height="60" mode="aspectFill"
                                           :src="scope.row.goods.goods_info.goods_attr.pic_url
                                            ? scope.row.goods.goods_info.goods_attr.pic_url
                                             : scope.row.goods.goods.goodsWarehouse.cover_pic"></app-image>
                            </div>
                            <div flex="dir:top">
                                <app-ellipsis :line="1">{{scope.row.goods.goods.goodsWarehouse.name}}</app-ellipsis>
                                <div flex="dir:left">
                                    规格:
                                    <span v-for="(gItem, index) in scope.row.goods.goods_info.attr_list">
                                        <span class="t-color-red">{{gItem.attr_name}}</span>
                                    </span>
                                    数量:
                                    <span class="t-color-red">{{scope.row.goods.num}}</span>
                                    {{scope.row.goods.goods.unit}}
                                </div>
                                <span>小计: <span class="t-color-red">{{scope.row.total_pay_price}}</span>元</span>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="总金额">
                    <template slot-scope="scope">
                        <div>
                            总金额:
                            <span style="color: blue;">{{scope.row.total_price}}</span>
                            (含运费:<span style="color: green;">{{scope.row.express_price}}</span>元)
                        </div>
                        <div v-if="scope.row.orderRelation.is_parent == 1">团长优惠：
                            <span class="t-color-red">{{scope.row.pintuanOrder.preferential_price}}</span>
                            元
                        </div>
                    </template>
                </el-table-column>
            </el-table>
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
                            @current-change="pagination"
                            layout="prev, pager, next, jumper"
                            :total="pagination.total_count">
                    </el-pagination>
                </div>
            </div>
        </div>
    </el-card>

</div>

<script>
    new Vue({
        el: '#app',
        data() {
            return {
                search: {
                    time: null,
                    r: 'plugin/pintuan/mall/order-groups/detail',
                    keyword: '',
                    keyword_1: '1',
                    date_start: '',
                    date_end: '',
                    id: getQuery('id')
                },
                tableLoading: false,
                page: 1,
                keyword: '',
                pageCount: 0,
                activeName: 0,
                list: [],
                address: [],
                robotNum: '',
            };
        },
        created() {
            this.getList();
        },
        methods: {
            searchOrder() {
                this.getList();
            },
            getList() {
                let self = this;
                self.tableLoading = true;
                request({
                    params: {
                        r: 'plugin/pintuan/mall/order-groups/detail',
                        page: self.page,
                        id: getQuery('id'),
                        keyword: self.keyword
                    },
                    method: 'get',
                }).then(e => {
                    self.tableLoading = false;
                    self.list = e.data.data.list;
                    self.pagination = e.data.data.pagination;
                    self.robotNum = e.data.data.robotNum;
                }).catch(e => {
                    console.log(e);
                });
            },
            // 分页
            pagination(page) {
                this.page = page;
                this.getList()
            },
        }
    });
</script>
