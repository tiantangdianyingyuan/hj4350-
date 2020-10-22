<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */
Yii::$app->loadViewComponent('order/app-edit-template');
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        display: inline-block;
        width: 350px;
        margin: 0 0 20px;
    }

    .input-item .el-input-group__prepend {
        background-color: #fff;
    }

    .input-item .el-input__inner {
        border-right: 0;
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

    .label-text {
        height: 32px;
        margin-right: 10px;
    }

    .select {
        float: left;
        width: 100px;
        margin-right: 10px;
    }

    .table .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }

    .goods-info div {
        height: 50px;
        line-height: 50px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .open-img .el-dialog {
        margin-top: 0 !important;
    }

    .click-img {
        width: 100%;
    }

    .input-item .el-input-group__append .el-button {
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        margin: 0;
    }
    .text {
        cursor: pointer;
        color: #419EFB;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div flex="cross:center box:first">
                <div><span @click="$navigate({r:'mall/order-comments/index'})" class="text">评价管理</span>/回复模板</div>
                <div flex="dir:right">
                    <div>
                        <el-button type="primary" size="small"
                                   @click="openDialog(null, templateVisible = true)">添加模板
                        </el-button>
                    </div>
                </div>
            </div>
        </div>
        <div class="table table-body">
            <div flex="dir:left">
                <div flex="cross:center" class="label-text">模板类型</div>
                <el-select size="small" v-model="type" class="select" @change="change">
                    <el-option key="-1" label="全部回复" :value="-1"></el-option>
                    <el-option key="1" label="好评" :value="1"></el-option>
                    <el-option key="2" label="中评" :value="2"></el-option>
                    <el-option key="3" label="差评" :value="3"></el-option>
                </el-select>
                <div class="input-item">
                    <el-input @keyup.enter.native="change" size="small" placeholder="请输入搜索内容" v-model="keyword" clearable
                              @clear="change">
                        <el-select size="small" v-model="keyword_name" slot="prepend" class="select">
                            <el-option key="1" label="模板名称" value="title"></el-option>
                            <el-option key="2" label="ID" value="id"></el-option>
                        </el-select>
                        <el-button slot="append" icon="el-icon-search" @click="change"></el-button>
                    </el-input>
                </div>
            </div>
            <el-table :data="list" border v-loading="listLoading">
                <el-table-column prop="id" label="ID" width="80"></el-table-column>
                <el-table-column prop="title" label="模板名称" width="180"></el-table-column>
                <el-table-column prop="content" label="模板内容"></el-table-column>
                <el-table-column label="模板类型" width="120">
                    <template slot-scope="scope">
                        <span v-if="scope.row.type == 1">好评回复</span>
                        <span v-if="scope.row.type == 2">中评回复</span>
                        <span v-if="scope.row.type == 3">差评回复</span>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="220">
                    <template slot-scope="scope">
                        <el-button size="small" type="text"
                                   @click="openDialog(scope.row, templateVisible = true)" circle>
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button size="small" type="text" @click="destroy(scope.row)" circle>
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div style="text-align: right;margin: 20px 0;">
                <el-pagination @current-change="pagination" background layout="prev, pager, next, jumper"
                               :page-count="pageCount"></el-pagination>
            </div>
        </div>
        <app-edit-template
                @close="dialogClose"
                @submit="dialogSubmit"
                :is-show="templateVisible"
                :template="template">
        </app-edit-template>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                list: [],
                pageCount: 0,
                listLoading: false,
                type: -1,
                keyword: '',
                keyword_name: 'title',
                btnLoading: false,
                template: null,
                templateVisible: false,
            };
        },
        methods: {
            pagination(currentPage) {
                this.page = currentPage;
                this.search();
            },
            change() {
                this.page = 1;
                this.search();
            },

            search() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'mall/order-comment-templates/index',
                        page: this.page,
                        type: this.type,
                        keyword: this.keyword,
                        keyword_name: this.keyword_name,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pageCount = e.data.data.pagination.page_count;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
            },

            //删除
            destroy: function (column) {
                this.$confirm('确认删除该记录吗?', '提示', {
                    type: 'warning'
                }).then(() => {
                    request({
                        params: {
                            r: 'mall/order-comment-templates/destroy'
                        },
                        data: {id: column.id},
                        method: 'post'
                    }).then(e => {
                        if (e.data.code === 0) {
                            this.$message.success(e.data.msg)
                            this.search();
                        } else  {
                            this.$message.error(e.data.msg)
                        }
                    }).catch(e => {
                    });
                });
            },
            openDialog(template) {
                this.template = template;
            },
            dialogClose() {
                this.templateVisible = false;
            },
            dialogSubmit() {
                this.search();
            },
        },
        mounted: function () {
            this.search();
        }
    });
</script>