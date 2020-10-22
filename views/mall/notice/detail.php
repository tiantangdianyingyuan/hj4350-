<?php defined('YII_ENV') or exit('Access Denied');
Yii::$app->loadViewComponent('app-goods');
?>
<style>
    .table-body {
        background-color: #fff;
    }

    .table-body .notice-label {
        border-bottom: 1px solid #f5f5f5;
        padding: 20px;
    }

    .table-body .t-omit {
        display: block;
        white-space: nowrap;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .history-list {
        padding: 10px 13px;
        border-bottom: 1px solid #f5f5f5;
    }

    .table-body .el-scrollbar__wrap {
        overflow-y: scroll;
        overflow-x: hidden;
    }

    .toolbar {
        padding: 20px 20px 0;
        background-color: #fff;
    }

    .detail {
        background: #ffffff;
    }

    .detail img {
        max-width: 100%;
        max-height: 100%;
    }

    .detail p {
        margin: 0;
    }

    detail table {
        width: 100% !important;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" style="justify-content:space-between;display: flex">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer"
                          @click="$navigate({r:'mall/data-statistics/index'})">数据概况</span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>公告</el-breadcrumb-item>
            </el-breadcrumb>
        </div>

        <div v-loading="listLoading">
            <div class="table-body">
                <div class="notice-label">{{labelTitle}} {{detail.created_at}}</div>
                <div style="padding: 15px 0">
                    <el-scrollbar style="height: 500px;">
                        <div class="detail" style="padding: 0 20px;width:1240px" v-html="detail.content"></div>
                    </el-scrollbar>
                </div>
            </div>


            <div class="table-body" style="margin-top: 25px">
                <div class="notice-label">历史记录</div>
                <div v-loading="hLoading">
                    <div v-for="(history, key) in list" :key="key" flex="dir:left cross:center"
                         class="history-list">
                        <div style="flex-shrink: 0" v-text="formatterType(history.type)"></div>
                        <div style="max-width: 722px">
                            <div class="t-omit" v-text="history.content_text"></div>
                        </div>
                        <el-button type="text" style="margin-left: 12px" @click="openDialog(history)">查看详情</el-button>
                        <div style="margin-left: auto;min-width:145px;color: #999999;margin-right: 20px;">{{history.created_at}}</div>
                    </div>
                </div>
            </div>

            <!--工具条 批量操作和分页-->
            <el-col :span="24" class="toolbar">
                <el-pagination
                        background
                        layout="prev, pager, next"
                        @current-change="pageChange"
                        :page-size="pagination.pageSize"
                        :total="pagination.total_count"
                        style="float:right;margin-bottom:15px"
                        v-if="pagination">
                </el-pagination>
            </el-col>
        </div>

        <!-- 公告MODEL -->
        <el-dialog :visible.sync="dialogVisible" width="1240px" top="20vh">
            <span slot="title">
                <div style="font-size: 16px;">
                    <span>{{historyForm.title}}</span>
                    <span style="margin-left: 10px">{{historyForm.created_at}}</span>
                </div>
            </span>
            <div style="overflow-y:auto;height: 100%">
                <div class="detail" v-html="historyForm.content"></div>
            </div>
            <span slot="footer" class="dialog-footer">
                <el-button size="small" type="primary" @click="dialogVisible = false">知道了</el-button>
            </span>
        </el-dialog>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                detail: {},
                listLoading: false,
                hLoading: false,
                page: 1,
                pagination: null,
                list: null,

                dialogVisible: false,
                historyForm: {
                    title: '',
                    content: '',
                },
                options: {
                    '': '',
                    'update': '更新公告',
                    'important': '重要通知',
                    'urgent': '紧急维护',
                }
            };
        },
        computed: {
            labelTitle() {
                return this.options[this.detail.type];
            }
        },
        methods: {
            pageChange(page) {
                this.page = page;
                this.getList();
            },
            openDialog(column) {
                this.historyForm = Object.assign(column, {title: this.options[column.type]});
                this.dialogVisible = true;
            },
            formatterType(type) {
                return '【' + this.options[type] + '】 ';
            },

            getDetail() {
                this.listLoading = true;
                let params = Object.assign({r: 'mall/notice/detail'}, {id: getQuery('id')});
                request({
                    params,
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code === 0) {
                        this.detail = e.data.data;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.listLoading = false
                });
            },
            getList() {
                this.hLoading = true;
                let param = Object.assign({r: 'admin/notice/list'}, {page: this.page}, {id: getQuery('id')});
                request({
                    params: param,
                }).then(e => {
                    this.hLoading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    }
                }).catch(e => {
                    this.hLoading = false;
                });
            },
        },

        mounted() {
            this.getDetail();
            this.getList();
        }
    })
</script>