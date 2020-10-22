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

    .select {
        float: left;
        width: 100px;
        margin-right: 10px;
    }

    .el-table .cell .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
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

    .label-text {
        height: 32px;
        margin-right: 10px;
    }

    /*弹出框表格样式 start*/
    .el-dialog__body {
        padding: 5px 20px;
    }

    .card-box {
        padding: 10px;
        border: 1px solid #E3E3E3;
    }

    .table-box {
        border-top: 1px solid #E3E3E3;
        border-left: 1px solid #E3E3E3;
    }

    .table-item {
        border-bottom: 1px solid #E3E3E3;
        border-right: 1px solid #E3E3E3;
        height: 50px;
        padding-left: 5px;
    }

    .table-item .el-radio__label {
        display: none;
    }

    .select-type-box {
        padding: 25px 0;
        height: 40px;
    }

    /*弹出框表格样式 end*/
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div flex="cross:center box:first">
                <div>评价管理</div>
                <div flex="dir:right">
                    <div>
                        <el-button type="primary" size="small"
                                   @click="$navigate({r: 'mall/order-comment-templates/index'})">回复模板
                        </el-button>
                        <el-button type="primary" size="small"
                                   @click="$navigate({r: 'mall/order-comments/edit'})">添加客户评价
                        </el-button>
                    </div>
                </div>
            </div>
        </div>
        <div class="table table-body">
            <div flex="dir:left">
                <div flex="dir:left">
                    <div class="label-text" flex="cross:center">评价类型</div>
                    <el-select size="small" v-model="comment_type" class="select" @change="change">
                        <el-option key="1" label="全部评价" value="0"></el-option>
                        <el-option key="2" label="好评" value="3"></el-option>
                        <el-option key="3" label="中评" value="2"></el-option>
                        <el-option key="4" label="差评" value="1"></el-option>
                    </el-select>
                </div>
                <div flex="dir:left">
                    <div class="label-text" flex="cross:center">所属平台</div>
                    <el-select size="small" class="select" v-model="platform" @change="change" placeholder="所属平台">
                        <el-option label="全平台" value=""></el-option>
                        <el-option label="微信" value="wxapp"></el-option>
                        <el-option label="支付宝" value="aliapp"></el-option>
                        <el-option label="抖音/头条" value="ttapp"></el-option>
                        <el-option label="百度" value="bdapp"></el-option>
                    </el-select>
                </div>
                <div class="input-item">
                    <el-input @keyup.enter.native="change" size="small" placeholder="请输入搜索内容" v-model="keyword"
                              clearable
                              @clear="change">
                        <el-select size="small" v-model="type" slot="prepend" class="select">
                            <el-option key="1" label="昵称" value="1"></el-option>
                            <el-option key="2" label="商品名称" value="2"></el-option>
                            <el-option key="3" label="评价内容" value="3"></el-option>
                        </el-select>
                        <el-button slot="append" icon="el-icon-search" @click="change"></el-button>
                    </el-input>
                </div>
                <div style="float: left;margin-left: 10px;">
                    <el-button @click="batchReplyDialog" size="small" plain>批量回复</el-button>
                </div>
                <div style="float: left;margin-left: 10px;">
                    <el-button @click="batchHide" size="small" plain>批量隐藏</el-button>
                </div>
                <div style="float: left;margin-left: 10px;" plain>
                    <el-button @click="batchDestroy" size="small">批量删除</el-button>
                </div>
            </div>
            <el-table :data="form" border style="width: 100%" v-loading="listLoading"
                      @selection-change="handleSelectionChange">
                <el-table-column
                        type="selection"
                        width="55">
                </el-table-column>
                <el-table-column prop="id" label="ID" width="80"></el-table-column>
                <el-table-column prop="nickname" label="用户" width="100"></el-table-column>
                <el-table-column prop="platform" align="center" label="平台" width="80">
                    <template slot-scope="scope">
                        <el-tooltip class="item" effect="dark" v-if="scope.row.platform == 'wxapp'" content="微信"
                                    placement="top">
                            <img src="statics/img/mall/wx.png" alt="">
                        </el-tooltip>
                        <el-tooltip class="item" effect="dark" v-else-if="scope.row.platform == 'aliapp'" content="支付宝"
                                    placement="top">
                            <img src="statics/img/mall/ali.png" alt="">
                        </el-tooltip>
                        <el-tooltip class="item" effect="dark" v-else-if="scope.row.platform == 'ttapp'" content="抖音/头条"
                                    placement="top">
                            <img src="statics/img/mall/toutiao.png" alt="">
                        </el-tooltip>
                        <el-tooltip class="item" effect="dark" v-else-if="scope.row.platform == 'bdapp'" content="百度"
                                    placement="top">
                            <img src="statics/img/mall/baidu.png" alt="">
                        </el-tooltip>
                        <el-tooltip class="item" effect="dark" v-else content="后台" placement="top">
                            <img src="statics/img/mall/site.png" alt="">
                        </el-tooltip>
                    </template>
                </el-table-column>
                <el-table-column prop="name" label="商品名称" width="200">
                    <template slot-scope="scope">
                        <div class="goods-info">
                            <app-image mode="aspectFill" style="margin-right: 10px;float: left"
                                       :src="scope.row.cover_pic"></app-image>
                            <div style="display: -webkit-box;height:50px;line-height: 25px;-webkit-box-orient: vertical;-webkit-line-clamp: 2;">
                                {{scope.row.name}}
                            </div>
<!--                            <app-ellipsis :line="2">{{scope.row.name}}</app-ellipsis>-->
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="score" align="center" label="评价" width="80">
                    <template slot-scope="scope">
                        <el-tooltip class="item" effect="dark" v-if="scope.row.score==3" content="好评" placement="top">
                            <img src="statics/img/mall/good.png" alt="">
                        </el-tooltip>
                        <el-tooltip class="item" effect="dark" v-else-if="scope.row.score==2" content="中评"
                                    placement="top">
                            <img src="statics/img/mall/normal.png" alt="">
                        </el-tooltip>
                        <el-tooltip class="item" effect="dark" v-else-if="scope.row.score==1" content="差评"
                                    placement="top">
                            <img src="statics/img/mall/bad.png" alt="">
                        </el-tooltip>
                    </template>
                </el-table-column>
                <el-table-column prop="content" label="详情" width="200">
                    <template slot-scope="scope">
                        <el-tooltip effect="dark" placement="top">
                            <template slot="content">
                                <div style="width: 320px;">{{scope.row.content}}</div>
                            </template>
                            <div style="display: -webkit-box;height:48px;line-height: 24px;overflow: hidden;-webkit-box-orient: vertical;-webkit-line-clamp: 2;">
                                {{scope.row.content}}
                            </div>
                        </el-tooltip>
                        <div>
                            <div v-for="item in scope.row.pic_url" @click="openImg(item)"
                                 style="margin: 10px;display: inline-block;cursor: pointer">
                                <app-image mode="aspectFill" :key="item.id" :src="item"></app-image>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="reply_content" label="评价回复"></el-table-column>
                <el-table-column prop="is_top" label="置顶" width="100">
                    <template slot-scope="scope">
                        <el-switch :active-value="1" :inactive-value="0" @change="switchTop(scope.row)"
                                   v-model="scope.row.is_top"></el-switch>
                    </template>
                </el-table-column>
                <el-table-column prop="is_show" label="状态" width="100">
                    <template slot-scope="scope">
                        <el-tag v-if="scope.row.is_show" size="small" type="success">显示</el-tag>
                        <el-tag v-else size="small" type="warning">隐藏</el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="操作" fixed="right" width="220">
                    <template slot-scope="scope">
                        <el-button size="small" type="text" circle @click="replyComment(scope.row)">
                            <el-tooltip class="item" effect="dark" content="评价回复" placement="top">
                                <img src="statics/img/mall/reply.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button size="small" type="text" v-if="scope.row.is_virtual == 1"
                                   @click="$navigate({r: 'mall/order-comments/edit', id:scope.row.id})" circle>
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button size="small" type="text" @click="destroy(scope.row)" circle>
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>

                        <el-button v-if="scope.row.is_show" size="small" type="text"
                                   @click="switchShow(scope.row)" circle>
                            <el-tooltip class="item" effect="dark" content="隐藏" placement="top">
                                <img src="statics/img/mall/icon-hidden.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button v-else size="small" type="text" @click="switchShow(scope.row)" circle>
                            <el-tooltip class="item" effect="dark" content="显示" placement="top">
                                <img src="statics/img/mall/icon-show.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div flex="dir:right" style="margin-top: 20px;">
                <el-pagination @current-change="pagination" hide-on-single-page background layout="prev, pager, next, jumper"
                               :page-count="pageCount"></el-pagination>
            </div>
            <el-dialog :visible.sync="dialogImg" class="open-img">
                <img :src="click_img" class="click-img" alt="">
            </el-dialog>
        </div>

        <el-dialog
                :visible.sync="dialogVisible"
                width="50%">
            <template slot="title">
                <div flex="dir:left cross:center">
                    <div style="margin-right: 10px">评价回复</div>
                </div>
            </template>
            <div class="select-type-box" flex="dir:left box:last cross:center">
                <div>
                    <el-radio v-model="reply_type" label="1">直接输入</el-radio>
                    <el-radio v-model="reply_type" label="2">选择模板</el-radio>
                </div>
                <div v-if="reply_type == 2">
                    <el-button @click="openDialog(null, templateVisible = true)" type="primary" size="small">
                        添加模板
                    </el-button>
                </div>
            </div>
            <template v-if="reply_type == 1">
                <el-input
                        type="textarea"
                        :rows="10"
                        placeholder="请输入回复内容"
                        v-model="reply_text">
                </el-input>
            </template>
            <template v-if="reply_type == 2">
                <div class="card-box">
                    <div>
                        <el-tabs v-model="activeName" @tab-click="handleClick">
                            <el-tab-pane label="好评回复" name="1"></el-tab-pane>
                            <el-tab-pane label="中评回复" name="2"></el-tab-pane>
                            <el-tab-pane label="差评回复" name="3"></el-tab-pane>
                        </el-tabs>
                    </div>
                    <div class="table-box" v-loading="tableLoading">
                        <div flex="dir:left">
                            <div flex="cross:center" class="table-item" style="width: 10%"></div>
                            <div flex="cross:center" class="table-item" style="width: 30%">模板名称</div>
                            <div flex="cross:center" class="table-item" style="width: 60%">模板内容</div>
                        </div>
                        <div flex="dir:left" v-for="(item, index) in dialogList" :key="index">
                            <div flex="main:center cross:center" class="table-item" style="width: 10%">
                                <el-radio @change="radioChange(item)" v-model="templateId" :label="item.id"></el-radio>
                            </div>
                            <div class="table-item" flex="cross:center" style="width: 30%">
                                <app-ellipsis :line="2">{{item.title}}</app-ellipsis>
                            </div>
                            <div class="table-item" flex="cross:center" style="width: 60%">
                                <app-ellipsis :line="2">{{item.content}}</app-ellipsis>
                            </div>
                        </div>
                    </div>
                    <div v-if="dialogList.length == 0" style="height: 100px" flex="cross:center main:center">
                        暂无模板
                    </div>
                    <div style="text-align: right;margin: 20px 0;">
                        <el-pagination @current-change="dialogPagination" background layout="prev, pager, next, jumper"
                                       :page-count="dialogPageCount"></el-pagination>
                    </div>
                </div>
            </template>
            <span slot="footer" class="dialog-footer">
                <el-button size="small" @click="dialogVisible = false">取 消</el-button>
                <el-button size="small" :loading="dialogBtnLoading" type="primary"
                           @click="batchReplySubmit">确 定</el-button>
            </span>
        </el-dialog>
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
                form: [],
                pageCount: 0,
                listLoading: false,
                type: '1',
                page: 1,
                comment_type: '0',
                keyword: '',
                btnLoading: false,
                dialogImg: false,
                click_img: null,
                platform: '',
                multipleSelection: [],
                dialogVisible: false,
                activeName: '1',
                tableLoading: false,
                dialogPageCount: 0,
                dialogPage: 1,
                dialogList: [],
                dialogBtnLoading: false,
                templateId: null,
                batchIds: [],
                template: null,
                templateVisible: false,
                reply_text: '',
                reply_type: '1',
            };
        },
        methods: {
            openImg(url) {
                this.click_img = url;
                this.dialogImg = true;
            },

            pagination(currentPage) {
                this.page = currentPage;
                this.search();
            },
            dialogPagination(currentPage) {
                this.dialogPage = currentPage;
                this.getTemplateList();
            },
            change() {
                this.page = 1;
                this.search();
            },

            search() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'mall/order-comments/index',
                        page: this.page,
                        type: this.type,
                        comment_type: this.comment_type,
                        keyword: this.keyword,
                        platform: this.platform,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.form = e.data.data.list;
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
                    this.listLoading = true;
                    request({
                        params: {
                            r: 'mall/order-comments/destroy'
                        },
                        data: {id: column.id},
                        method: 'post'
                    }).then(e => {
                        location.reload();
                        this.listLoading = false;
                    }).catch(e => {
                        this.listLoading = false;
                    });
                });
            },

            switchTop(row) {
                let self = this;
                request({
                    params: {
                        r: 'mall/order-comments/update-top',
                    },
                    method: 'post',
                    data: {
                        status: row.is_top,
                        id: row.id
                    }
                }).then(e => {
                    if (e.data.code === 0) {
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                });
            },

            switchShow(row) {
                let self = this;
                const is_show = row.is_show ? 0 : 1;
                request({
                    params: {
                        r: 'mall/order-comments/show',
                    },
                    method: 'post',
                    data: {
                        is_show,
                        id: row.id
                    }
                }).then(e => {
                    if (e.data.code === 0) {
                        row.is_show = is_show;
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                });
            },
            batchValidate() {
                this.batchIds = this.multipleSelection.map(item => {
                    return item.id
                });
                if (this.batchIds.length <= 0) {
                    throw new Error('请勾选数据');
                }
            },
            batchReplyDialog() {
                try {
                    this.reply_type = '1';
                    this.reply_text = '';
                    this.batchValidate();
                    this.dialogVisible = true;
                    this.getTemplateList();
                } catch (error) {
                    this.$message.error(error.message);
                }
            },
            batchHide() {
                try {
                    this.batchValidate();
                    const title = '已选中' + this.batchIds.length + '条评价，是否批量隐藏?';
                    this.batchSubmit(title, 'mall/order-comments/batch-update-status');
                } catch (error) {
                    this.$message.error(error.message);
                }
            },
            batchDestroy() {
                try {
                    this.batchValidate();
                    const title = '已选中' + this.batchIds.length + '条评价，是否批量删除?';
                    this.batchSubmit(title, 'mall/order-comments/batch-destroy');
                } catch (error) {
                    this.$message.error(error.message);
                }
            },
            batchSubmit(title, r) {
                const self = this;
                self.$confirm(title, '提示', {
                    type: 'warning'
                }).then(res => {
                    self.listLoading = true;
                    request({
                        params: {
                            r
                        },
                        data: {
                            batch_ids: self.batchIds,
                            status: 0
                        },
                        method: 'post'
                    }).then(e => {
                        self.listLoading = false;
                        if (e.data.code === 0) {
                            self.$message.success(e.data.msg);
                            location.reload();
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        self.listLoading = false;
                    })
                }).catch(res => {
                    console.log(res);
                })
            },
            batchReplySubmit() {
                if (this.reply_type == 2 && !this.templateId) {
                    this.$message.warning('请选择评价模板');
                    return;
                }
                if (this.reply_type == 1 && !this.reply_text) {
                    this.$message.warning('请输入回复评价内容');
                    return;
                }

                let self = this;
                self.dialogBtnLoading = true;
                request({
                    params: {
                        r: 'mall/order-comments/batch-reply',
                    },
                    method: 'post',
                    data: {
                        batch_ids: self.batchIds,
                        template_id: self.templateId,
                        reply_type: self.reply_type,
                        reply_text: self.reply_text,
                    }
                }).then(e => {
                    self.dialogBtnLoading = false;
                    if (e.data.code === 0) {
                        self.dialogVisible = false;
                        self.search();
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            handleSelectionChange(val) {
                this.multipleSelection = val;
            },
            handleClick(tab, event) {
                this.templateId = null;
                this.getTemplateList();
            },
            getTemplateList() {
                this.tableLoading = true;
                request({
                    params: {
                        r: 'mall/order-comment-templates/index',
                        page: this.dialogPage,
                        type: this.activeName,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.dialogList = e.data.data.list;
                        this.dialogPageCount = e.data.data.pagination.page_count;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.tableLoading = false;
                }).catch(e => {
                    this.tableLoading = false;
                });
            },
            radioChange(row) {
                this.templateId = row.id;
            },
            openDialog(template) {
                this.template = template;
            },
            dialogClose() {
                this.templateVisible = false;
            },
            dialogSubmit() {
                this.getTemplateList();
            },
            // 单个评价回复
            replyComment(row) {
                let self = this;
                self.reply_type = '1';
                self.reply_text = '';
                self.multipleSelection = [];
                self.batchIds = [];
                self.batchIds.push(row.id);
                self.dialogVisible = true;
                self.getTemplateList();
            }
        },
        mounted: function () {
            this.search();
        }
    });
</script>