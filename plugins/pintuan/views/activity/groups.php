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
    .el-select .el-input {
        width: 100px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>活动数据</span>
            </div>
        </div>
        <div class="table-body">
            <div >
                <el-tabs v-model="search.status" @tab-click="requestList">
                    <el-tab-pane label="全部" name="-1"></el-tab-pane>
                    <el-tab-pane label="进行中" name="1"></el-tab-pane>
                    <el-tab-pane label="拼团成功" name="2"></el-tab-pane>
                    <el-tab-pane label="拼团失败" name="3"></el-tab-pane>
                </el-tabs>
                <el-form size="small" :inline="true" @submit.native.prevent>
                    <el-form-item  label="开团时间" >
                        <el-date-picker
                                type="datetimerange"
                                @change="requestList"
                                v-model="search.date_picker"
                                range-separator="至"
                                start-placeholder="开始日期"
                                value-format="yyyy-MM-dd HH:mm:ss"
                                end-placeholder="结束日期">
                        </el-date-picker>
                    </el-form-item>
                    <el-form-item >
                        <div class="input-item">
                            <el-input @keyup.enter.native="requestList" size="small" class="input-with-select" placeholder="请输入内容"
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
            <el-table :data="list" border tooltip-effect="dark" v-loading="loading" style="width: 100%">
                <el-table-column
                        label="商品信息"
                        >
                    <template slot-scope="scope">
                        <div flex="box:first">
                            <div style="padding-right: 10px;">
                                <app-image mode="aspectFill" :src="scope.row.goods_cover_pic"></app-image>
                            </div>
                            <app-ellipsis :line="2" v-if="scope.row.goods_name">{{scope.row.goods_name}}</app-ellipsis>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                        prop="group_create_time"
                        label="开团时间"
                        width="250">
                </el-table-column>
                <el-table-column
                        label="拼团信息"
                        width="350"
                        >
                    <template slot-scope="scope">
                      {{scope.row.people_num}}人团{{scope.row.robot_num > 0 && scope.row.status === 2 ? '（含机器人：' + scope.row.robot_num  +'）' : ''}}
                    </template>
                </el-table-column>
                <el-table-column
                        prop="date"
                        label="团长信息"
                        width="250">
                    <template slot-scope="scope">
                        <div >
                            团长：
                            <img style="vertical-align:middle;" src="statics/img/mall/ali.png" v-if="scope.row.platform == 'aliapp'" alt="">
                            <img style="vertical-align:middle;" src="statics/img/mall/wx.png" v-else-if="scope.row.platform == 'wxapp'" alt="">
                            <img style="vertical-align:middle;" src="statics/img/mall/toutiao.png" v-else-if="scope.row.platform == 'ttapp'" alt="">
                            <img style="vertical-align:middle;" src="statics/img/mall/baidu.png" v-else-if="scope.row.platform == 'bdapp'" alt="">
                            {{scope.row.group_nickname}}
                        </div>
                        <div>
                            团长优惠：￥{{scope.row.preferential_price}}
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                        prop="status_cn"
                        label="活动状态"
                        width="180">
                    <template slot-scope="scope">
                        <el-tag v-if="scope.row.status === 1">{{scope.row.status_cn}}</el-tag>
                        <el-tag  v-if="scope.row.status === 2" type="success">{{scope.row.status_cn}}</el-tag>
                        <el-tag v-if="scope.row.status === 3" type="danger">{{scope.row.status_cn}}</el-tag>
                        <el-tag v-if="scope.row.status === 4" type="warning">{{scope.row.status_cn}}</el-tag>
                    </template>
                </el-table-column>
                <el-table-column
                        prop="date"
                        label="操作"
                        fixed="right"
                        width="100">
                    <template slot-scope="scope">

                        <el-button type="text" circle size="mini" @click="detail(scope.row)">
                            <el-tooltip class="item" effect="dark" content="拼团详情" placement="top">
                                <img src="statics/img/mall/detail.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div  style="margin-top: 20px;" flex="dir:right">
                <el-pagination hide-on-single-page @current-change="pagination" background layout="prev, pager, next, jumper"
                               :page-count="pageCount"></el-pagination>
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
                    date_picker: [],
                    keyword: '',
                    keyword_name: '订单号',
                    status: '-1',
                    date_start: '',
                    date_end: '',
                    page: 1
                },
                list: [],
                pageCount: 1,
                search_list: [],
                loading: false
            };
        },
        created() {
            this.request().then(e => {
                this.search.keyword_name = e[0].value;
            });
        },
        methods: {
            requestList() {
                if (this.search.date_picker) {
                    this.search.date_start = this.search.date_picker[0];
                    this.search.date_end = this.search.date_picker[1];
                } else {
                    this.search.date_start = '';
                    this.search.date_end = '';
                }
                this.request();
            },

            async request() {
                this.loading = true;
                const e = await request({
                    params: {
                        r: `/plugin/pintuan/mall/activity/groups`,
                        search: JSON.stringify({
                            status: this.search.status,
                            keyword: this.search.keyword,
                            date_start: this.search.date_start,
                            date_end: this.search.date_end,
                            keyword_name: this.search.keyword_name,
                        }),
                        page: this.search.page
                    }
                });
                this.list = e.data.data.list;
                this.pageCount = e.data.data.pagination.page_count;
                this.search_list = e.data.data.search_list;
                this.loading = false;
                return this.search_list;
            },

            detail(item) {
                navigateTo({
                    r: 'plugin/pintuan/mall/activity/groups-orders',
                    id: item.id,
                    is_group: 1
                });
                console.log(item.id);
            },

            pagination(e) {
                this.search.page  = e;
                this.request();
            }
        }
    });
</script>
