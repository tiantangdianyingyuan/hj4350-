<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/7
 * Time: 11:46
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
?>
<style>
    .el-card__header {
        background-color: #ffffff;
    }
    .shelves {
        background-color: #f5f7fa;
        height: 50px;
        line-height: 50px;
        padding-left: 20px;
        margin-top: 20px;
    }
    .app-order-user {
        margin-right: 10px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" v-loading="detailLoading" style="border:0;background-color: #f3f3f3;" body-style="background-color: #f3f3f3;padding: 0 0 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'plugin/pintuan/mall/activity/index'})">拼团活动</span></el-breadcrumb-item>
                <el-breadcrumb-item>活动数据</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <el-card style="margin-top: 10px">
                <el-tabs v-model="status" @tab-click="getList">
                    <el-tab-pane label="全部" name="-1"></el-tab-pane>
                    <el-tab-pane label="进行中" name="1"></el-tab-pane>
                    <el-tab-pane label="拼团成功" name="2"></el-tab-pane>
                    <el-tab-pane label="拼团失败" name="3"></el-tab-pane>
                </el-tabs>
                <el-form size="small" :inline="true" :model="search">

                    <el-form-item label="开团时间:">
                        <el-date-picker
                                class="item-box"
                                size="small"
                                @change="toSearch"
                                v-model="search.time"
                                type="datetimerange"
                                value-format="yyyy-MM-dd HH:mm:ss"
                                range-separator="至"
                                start-placeholder="开始日期"
                                end-placeholder="结束日期">
                        </el-date-picker>
                    </el-form-item>
                    <el-form-item>
                        <div class="input-item">

                            <el-input v-model="search.keyword" placeholder="请输入搜索内容" clearable @clear="toSearch"
                                      @keyup.enter.native="toSearch">
                                <el-select style="width: 130px" slot="prepend" v-model="search.keyword_name">
                                    <el-option :label="item.label" :value="item.value" v-for="item in search_list"></el-option>
                                </el-select>
                                <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                            </el-input>
                        </div>
                    </el-form-item>
                </el-form>
                    <div flex="dir:left cross: center" style="margin-bottom: 15px;">
                        <span style="line-height: 76px;margin-right: 16px">商品信息:</span>
                        <div flex="box:first" style="width:420px;border: 1px solid #ebeef5;height: 76px;padding:12px;line-height:14px">
                            <app-image mode="aspectFill" :src="goods.cover_pic"></app-image>
                            <div style="font-size: 14px;color:#606266;margin-left:16px" flex="dir:top main:justify">
                                <div style="margin-top: 4px;-webkit-line-clamp: 2;word-break: break-all;" class="vue-line-clamp">{{goods.name}}</div>
                            </div>
                        </div>
                    </div>

            <el-table
                ref="multipleTable"
                :data="list"
                tooltip-effect="dark"
                style="width: 100%"
                border
                v-loading="listLoading"
               >

                <el-table-column
                    label="开团时间"
                    show-overflow-tooltip
                    prop="group_create_time"
                    width="380">
                </el-table-column>

                <el-table-column
                    label="拼团信息"
                    width="150"
                    show-overflow-tooltip
                >
                    <template slot-scope="scope">
                        {{scope.row.people_num}}人团{{scope.row.robot_num > 0 ? '(含机器人：' + scope.row.robot_num +')' : ''}}
                    </template>
                </el-table-column>

                <el-table-column
                    label="团长信息"
                    show-overflow-tooltip>
                    <template slot-scope="scope">
                        <div flex="">
                            <span>团长：</span>
                            <div class="app-order-user" flex="cross:center">
                                <img src="statics/img/mall/ali.png" v-if="scope.row.platform == 'aliapp'" alt="">
                                <img src="statics/img/mall/wx.png" v-else-if="scope.row.platform == 'wxapp'" alt="">
                                <img src="statics/img/mall/toutiao.png" v-else-if="scope.row.platform == 'ttapp'" alt="">
                                <img src="statics/img/mall/baidu.png" v-else-if="scope.row.platform == 'bdapp'" alt="">
                            </div>
                            <div>{{scope.row.group_nickname}}</div>
                        </div>
                        <div>团长优惠：￥{{scope.row.preferential_price}}</div>
                    </template>
                </el-table-column>

                <el-table-column
                    label="活动状态"
                    width="100"
                    show-overflow-tooltip>
                    <template slot-scope="scope">
                        <el-tag v-if="scope.row.status === '未开始'" type="info">未开始</el-tag>
                        <el-tag v-else-if="scope.row.status === 1">进行中</el-tag>
                        <el-tag v-else-if="scope.row.status === 2" type="success">拼团成功</el-tag>
                        <el-tag v-else-if="scope.row.status === 3" type="danger">拼团失败</el-tag>
                        <el-tag v-else-if="scope.row.status === 4" type="danger">未退款</el-tag>
                    </template>
                </el-table-column>

                <el-table-column
                    label="操作"
                    fixed="right"
                    show-overflow-tooltip>
                    <template slot-scope="scope">
                        <el-button  type="text" circle size="mini" @click="detail(scope.row)">
                            <el-tooltip class="item" effect="dark" content="拼团详情" placement="top">
                                <img src="statics/img/mall/detail.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div flex="box:last cross:center" style="margin-top: 20px;">
                <div style="visibility: hidden;">
                    <el-button plain type="primary" size="small">批量操作1</el-button>
                    <el-button plain type="primary" size="small">批量操作2</el-button>
                </div>
                <div>
                    <el-pagination
                        v-if="page_count > 0"
                        @current-change="pagination"
                        background
                        layout="prev, pager, next, jumper"
                        :page-count="page_count">
                    </el-pagination>
                </div>
        </el-card>
</div>
</el-card>
</div>


<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                form: {
                },
                id: -1,
                goods: {},
                page_count: 0,
                current_page: 0,
                list: [],
                page: 1,
                status: '-1',
                detailLoading: false,
                listLoading: false,
                selection_list: [],
                goods_warehouse_id: 0,
                search: {

                    keyword_name: '',
                    time: [],
                    keyword: '',
                    status: '',
                    date_start: '',
                    date_end: ''
                },
                search_list: []
            };
        },
        created() {
            this.id = getQuery('id');
            this.getList().then(e => {
                this.search.keyword_name = e[0].value;
            });
        },
        methods: {
            toSearch() {
                if (this.search.time) {
                    this.search.date_start = this.search.time[0];
                    this.search.date_end = this.search.time[1];
                } else {
                    this.search.date_start = '';

                    this.search.date_end = '';

                }
                this.getList();
            },
            async getList() {
                try {
                    this.listLoading = true;
                    const res = await request({
                        params: {
                            r: `plugin/pintuan/mall/activity/groups`,
                            id: this.id,
                            page: this.page,
                            search: {
                                status: this.status,

                                keyword: this.search.keyword,
                                keyword_name: this.search.keyword_name,
                                date_start: this.search.date_start,
                                date_end: this.search.date_end,
                            }
                        },
                        method: 'get'
                    });
                    this.listLoading = false;
                    if (res.data.code === 0) {
                        let {list, pagination, goods, search_list} = res.data.data;
                        this.goods = goods;
                        let {page_count, current_page} = pagination;
                        this.list = list;
                        this.page_count = page_count;
                        this.current_page = current_page;
                        this.search_list = search_list;
                        return this.search_list;
                    }
                } catch (e) {
                    throw new Error(e);
                }
            },



            selectionChange(list) {
                list.map((item) => {
                    this.selection_list.push(item.goods_id);
                    this.goods_warehouse_id = item.goods_warehouse_id;
                })
            },

            pagination(currentPage) {
                this.page = currentPage;
                this.getList();
            },

            detail(row) {
                navigateTo({
                    r: `plugin/pintuan/mall/activity/groups-orders`,
                    id: row.id,
                    page: this.page
                });
            },

            clickPane() {
                this.getList();
            }
        }
    });
</script>
