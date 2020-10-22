<?php
/**
 * Created by PhpStorm.
 * User: fjt
 * Date: 2019/3/6
 * Time: 11:02
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
Yii::$app->loadViewComponent('goods/app-search');
Yii::$app->loadViewComponent('goods/app-batch');
?>

<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }
    .el-form-item--small.el-form-item {
        margin-bottom: 0;
    }
    .shelves {
        background-color: #f5f7fa;
        height: 50px;
        line-height: 50px;
        padding-left: 20px;
        margin-top: 20px;
    }
</style>

<div id="app-activity-list" v-cloak>
    <el-card  class="box-card" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>{{activity_name}}活动</span>
            <el-button style="float: right; margin: -5px 0" type="primary" size="small" @click="edit">新建活动</el-button>
        </div>
        <div class="table-body">
            <app-search :is-show-cat="false" :tabs="tabs" date-label="活动时间" :new-search="search" place-holder="请输入商品名称搜索" @to-search="toSearch" date_label="活动时间" :new-active-name="newActiveName"></app-search>
            <div class="shelves">
                <el-button size="mini" @click="operatingActivity(0)">上架</el-button>
                <el-button size="mini" @click="operatingActivity(1)">下架</el-button>
                <el-button size="mini" @click="operatingActivity(2)">删除</el-button>
            </div>

            <el-table
                ref="multipleTable"
                :data="list"
                v-loading="listLoading"
                tooltip-effect="dark"
                style="width: 100%"
                border
                @selection-change="selectionChange">
                <el-table-column
                    type="selection"
                    width="55">
                </el-table-column>
                <el-table-column
                    label="商品ID"
                    prop="id"
                    width="120">
                </el-table-column>
                <el-table-column
                    prop="name"
                    label="商品名称"
                    width="320">
                    <template slot-scope="scope">
                        <div flex="box:first">
                            <div style="padding-right: 10px;">
                                <app-image mode="aspectFill" :src="scope.row.goods_cover_pic"></app-image>
                            </div>
                            <div style="display: -webkit-box;height:50px;line-height: 25px;-webkit-box-orient: vertical;-webkit-line-clamp: 2;">
                                {{scope.row.goods_name}}
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                    prop="goods_stock"
                    label="库存"
                    show-overflow-tooltip>
                </el-table-column>
                <el-table-column
                    label="活动时间"
                    show-overflow-tooltip>
                    <template slot-scope="scope">
                       <template v-if="sign != 'miaosha'">
                           <div>
                               {{scope.row.open_date}}至
                           </div>
                           <div > {{scope.row.end_date === '0000-00-00 00:00:00' ? '无期限' : scope.row.end_date}}</div>
                       </template>
                        <template v-if="sign == 'miaosha'">
                            <div>
                                {{scope.row.open_date}} 至 {{scope.row.end_date}}
                            </div>
                        </template>
                    </template>
                </el-table-column>
                <slot name="after_status"></slot>
                <el-table-column
                    prop="status_cn"
                    label="活动状态"
                    width="100"
                    show-overflow-tooltip>
                    <template slot-scope="scope">
                        <el-tag v-if="scope.row.status_cn === '未开始'" type="info">未开始</el-tag>
                        <el-tag v-if="scope.row.status_cn === '进行中'">进行中</el-tag>
                        <el-tag  v-if="scope.row.status_cn === '下架中'" type="warning">下架中</el-tag>
                        <el-tag v-if="scope.row.status_cn === '已结束'" type="danger">已结束</el-tag>
                    </template>
                </el-table-column>
                <el-table-column
                    label="操作"
                    fixed="right"
                    width="200"
                    >
                    <template slot-scope="scope">
                        <el-button type="text" circle size="mini" @click="edit(scope.row)" v-if="no_edit === 0 && scope.row.status_cn !== '已结束'">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button type="text" circle size="mini" @click="detail(scope.row)" v-if="sign !== 'miaosha' && scope.row.status_cn !== '未开始'">
                            <el-tooltip class="item" effect="dark" content="活动数据" placement="top">
                                <img src="statics/img/mall/detail.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button type="text" circle size="mini" @click="detail(scope.row)" v-if="sign === 'miaosha'">
                            <el-tooltip class="item" effect="dark" content="活动数据" placement="top">
                                <img src="statics/img/mall/detail.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div flex="dir:right">
                <div style="visibility: hidden;">
                </div>
                <div>
                    <el-pagination
                        hide-on-single-page
                        @current-change="pagination"
                        background
                        layout="prev, pager, next, jumper"
                        :page-count="page_count">
                    </el-pagination>
                </div>
            </div>
        </div>
    </el-card>
</div>
<script>
    Vue.component('app-activity-list', {
        template: '#app-activity-list',
        props: {
            activity_name: '',
            tabs: {
                type: Array,
            },
            edit_activity_url: '',
            activity_url: '',
            edit_activity_status_url: '',
            edit_activity_destroy_url: '',
            activity_detail_url: '',
            no_edit: {
                type: Number,
                default: 0
            },
            sign: ''
        },
        data() {
            return {
                listLoading: false,
                search: {
                    keyword: '',
                    date_start: '',
                    date_end: '',
                    status: '1',
                    date_picker: []
                },
                page: 1,
                page_count: 1,
                current_page: 1,
                list: [],
                selection_list: [],
                choose_list: [],
                newActiveName: '1'
            }
        },
        created() {
            this.requestList();
        },
        methods: {

            toSearch(searchData) {
                this.page = 1;
                this.search = searchData;
                this.getList();
            },

            getList() {
                this.selection_list = [];
                this.requestList();
            },

            async requestList() {
                this.listLoading = true;
                try {
                    const response = await request({
                        params: {
                            r: this.activity_url,
                            page: this.page,
                            search: this.search
                        },
                        method: 'get'
                    });
                    this.listLoading = false;
                    if (response.data.code === 0) {
                        let {list, pagination} = response.data.data;
                        let {page_count, current_page} = pagination;

                        this.list = list;
                        this.page_count = page_count;
                        this.current_page = current_page;
                    }
                } catch(e) {
                    this.listLoading = false;
                    throw new Error(e);
                }
            },
            pagination(currentPage) {
                let self = this;
                self.page = currentPage;
                self.requestList();
            },

            edit(row) {
                if (row.id) {
                    navigateTo({
                        r: this.edit_activity_url,
                        id: row.id,
                        page: this.page
                    });
                } else {
                    navigateTo({
                        r: this.edit_activity_url,
                        page: this.page
                    });
                }
            },

            detail(row) {
                navigateTo({
                    r: this.activity_detail_url,
                    id: row.id,
                    page: this.page
                });
            },

            selectionChange(list) {
                this.selection_list = [];
                list.map((item) => {
                    this.selection_list.push(item.id);
                })
            },

            async operatingActivity(status) {
                if (this.selection_list.length === 0) {
                    this.$message.warning('请先勾选要设置的活动');
                    return;
                }
                if (status === 0) {
                    this.$confirm('此操作将上架活动?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        request({
                            params: {
                                r: this.edit_activity_status_url,
                                page: this.page,
                                search: this.search
                            },
                            method: 'post',
                            data: {
                                is_all:  0,
                                batch_ids: this.selection_list,
                                activity_status: 1,
                            }
                        }).then((res) => {
                            if (res.data.code === 0) {
                                this.getList();
                            }
                        });
                    }).catch(() => {
                    });

                } else if (status === 1) {
                    this.$confirm('此操作将下架活动?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        request({
                            params: {
                                r:  this.edit_activity_status_url,
                                page: this.page,
                                search: this.search
                            },
                            method: 'post',
                            data: {
                                is_all:  0,
                                batch_ids: this.selection_list,
                                activity_status: 0,
                            }
                        }).then((res) => {
                            if (res.data.code === 0) {
                                this.getList();
                            }
                        });
                    }).catch(() => {
                    });
                } else if (status === 2) {
                    this.$confirm('此操作将删除活动?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        request({
                            params: {
                                r: this.edit_activity_destroy_url,
                                page: this.page,
                                search: this.search
                            },
                            method: 'post',
                            data: {
                                is_all:  0,
                                batch_ids: this.selection_list,
                            }
                        }).then(res => {
                            if (res.data.code === 0) {
                                this.getList();
                            }
                        });
                    })
                }
            },
        }
    })
</script>
