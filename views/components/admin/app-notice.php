<?php defined('YII_ENV') or exit('Access Denied');
Yii::$app->loadViewComponent('app-rich-text');
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .app-notice .detail {
        background: #ffffff;
    }

    .app-notice .detail img {
        max-width: 100%;
        max-height: 100%;
    }

    .app-notice .detail p {
        margin: 0;
    }

    .app-notice .detail table {
        width: 100% !important;
    }

    .app-notice .el-scrollbar__wrap {
        overflow-y: scroll;
        overflow-x: hidden;
    }

    .app-notice .history-list {
        padding: 10px 5px;
        border-bottom: 1px solid #EBEEF5;
    }

    .app-notice .notice-label {
        border-bottom: 1px solid #f5f5f5;
        padding: 25px 10px;
    }

    .app-notice .history-list .t-omit {
        display: block;
        white-space: nowrap;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

<template id="app-notice">
    <div class="app-notice">
        <el-dialog :title="dialogTitle" :visible.sync="dialogVisible" width="850px" top="20vh">
            <div style="height:100%">
                <div class="detail" v-html="form.content"></div>
            </div>
            <span slot="footer" class="dialog-footer">
                <el-button size="small" @click="dialogVisible = false">取消</el-button>
                <el-button :loading="btnLoading" size="small" type="primary" @click="submit('add')">发布</el-button>
            </span>
        </el-dialog>
        <!-- 查看公告 -->
        <el-dialog :visible.sync="dialogPreviewVisible" width="1240px" top="10vh">
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
                <el-button size="small" type="primary" @click="dialogPreviewVisible = false">知道了</el-button>
            </span>
        </el-dialog>
        <!-- 编辑公告 -->
        <el-dialog title="编辑公告" :visible.sync="dialogEditVisible" width="1358px" top="10vh" v-if="dialogEditVisible">
            <span slot="title">
                <div style="font-size: 16px;">
                    <span>{{editForm.title}}</span>
                    <span style="margin-left: 10px">{{editForm.created_at}}</span>
                </div>
            </span>

            <el-form ref="editForm" :model="editForm" label-width="100px" :rules="rules">
                <el-form-item label="公告类型" prop="type">
                    <el-radio-group v-model="editForm.type">
                        <el-radio v-for="(type,key) in options"
                                  :key="key"
                                  :label="type.value">{{type.label}}
                        </el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="公告内容" prop="content">
                    <app-rich-text v-model="editForm.content" label-icon="插入图片(宽度1200)" :simple-attachment="true"></app-rich-text>
                </el-form-item>
            </el-form>
            <span slot="footer" class="dialog-footer">
                <el-button size="small" @click="dialogEditVisible = false">取消</el-button>
                <el-button :loading="btnLoading" size="small" type="primary" @click="editSave">发布</el-button>
            </span>
        </el-dialog>

        <el-card shadow="never" style="border:0;width:1358px" body-style="background-color: #f3f3f3;padding:0;">
            <div class="table-body">
                <el-form ref="form" :model="form" label-width="100px" :rules="rules">
                    <el-form-item label="公告类型" prop="type">
                        <el-radio-group v-model="form.type">
                            <el-radio v-for="(type,key) in options"
                                      :key="key"
                                      :label="type.value">{{type.label}}
                            </el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="公告内容" prop="content">
                        <app-rich-text v-model="form.content" label-icon="插入图片(宽度1200)"  :simple-attachment="true"></app-rich-text>
                    </el-form-item>
                    <el-button type="primary" @click="clerkSubmit" style="margin-left:100px;float:left" size="small">
                        发布
                    </el-button>
                    <el-button v-if="false" @click="preview" style="margin-bottom:22px;float:right" size="small"
                               type="primary">
                        预览
                    </el-button>
                </el-form>
                <template v-if="historyList && historyList.length">
                    <div style="height: 1px;width: 100%;background-color: #D8D8D8;margin-top: 110px"></div>
                    <div>
                        <div class="notice-label">历史记录</div>
                        <div v-loading="hLoading">
                            <div v-for="(history, key) in historyList" :key="key" flex="dir:left cross:center"
                                 class="history-list">
                                <div style="flex-shrink: 0" v-text="formatterType(history.type)"></div>
                                <div style="max-width: 415px">
                                    <div class="t-omit" v-text="history.content_text"></div>
                                </div>
                                <el-button type="text" style="margin-left: 12px" @click="openDialog(history)">查看详情
                                </el-button>
                                <el-button style="margin-left: auto" type="text" @click="editDialog(history)">编辑
                                </el-button>
                                <el-button style="margin-left: 20px" type="text" @click="delData(history,key)">删除
                                </el-button>
                                <div style="margin-left: 50px;min-width:145px;color: #999999;margin-right: 10px;">{{history.created_at}}
                                </div>
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
                                :current-page.sync="pagination.current_page"
                                :total="pagination.total_count"
                                style="float:right;margin:15px 0"
                                v-if="pagination">
                        </el-pagination>
                    </el-col>
                </template>
            </div>
        </el-card>
    </div>
</template>
<script>
    Vue.component('app-notice', {
        template: '#app-notice',
        data() {
            return {
                editForm: {
                    title: '',
                    id: '',
                    type: 'update',
                    content: '',
                },
                form: {
                    type: 'update',
                    content: '',
                },
                rules: {
                    type: [
                        {required: true, message: '公告类型不能为空', trigger: 'blur'},
                    ],
                    content: [
                        {required: true, message: '公告内容不能为空', trigger: 'change'},
                    ],
                },
                options: [
                    {
                        'label': '更新公告',
                        'value': 'update',
                    }, {
                        'label': '紧急维护',
                        'value': 'urgent',
                    }, {
                        'label': '重要通知',
                        'value': 'important'
                    },
                ],
                btnLoading: false,
                dialogVisible: false,
                historyList: [],
                historyForm: {
                    title: '',
                    content: '',
                },
                hLoading: false,
                pagination: null,
                page: 1,
                dialogEditVisible: false,
                dialogPreviewVisible: false,
            };
        },
        computed: {
            dialogTitle() {
                let sentinel = '';
                this.options.forEach(item => {
                    if (item.value == this.form.type) {
                        sentinel = item.label;
                        return true;
                    }
                })
                return sentinel;
            }
        },
        mounted() {
            this.getList();
        },
        methods: {
            openDialog(column) {
                let sentinel = '';
                this.options.forEach(item => {
                    if (item.value == column.type) {
                        sentinel = item.label;
                        return true;
                    }
                })

                this.historyForm = Object.assign(column, {title: sentinel});
                this.dialogPreviewVisible = true;
            },
            editDialog(column) {
                this.dialogEditVisible = true;
                this.editForm = column;
            },
            delData(column, index) {
                this.$confirm('此操作将删除公告, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    request({
                        params: {
                            r: 'admin/notice/notice-del',
                            id: column.id
                        },
                    }).then(e => {
                        if (e.data.code === 0) {
                            this.$message.success(e.data.msg);
                            if (this.historyList && this.historyList.length > 1) {
                                this.getList();
                            } else {
                                let page = this.pagination.current_page - 1;
                                this.page = page === 0 ? 1: page;
                                this.getList();
                            }
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    });
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '已取消删除'
                    });
                });
            },
            pageChange(page) {
                this.page = page;
                this.getList();
            },
            formatterType(type) {
                let sentinel = '';
                this.options.forEach(item => {
                    if (item.value == type) {
                        sentinel = item.label;
                        return true;
                    }
                })
                return '【' + sentinel + '】 ';
            },
            getList() {
                this.hLoading = true;
                let param = Object.assign({r: 'admin/notice/list'}, {page: this.page}, {id: getQuery('id')});
                request({
                    params: param,
                }).then(e => {
                    this.hLoading = false;
                    if (e.data.code === 0) {
                        this.historyList = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    }
                }).catch(e => {
                    this.hLoading = false;
                });
            },
            clerkSubmit() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        this.$confirm('是否确认发布公告?', '提示', {
                            confirmButtonText: '确定',
                            cancelButtonText: '取消',
                            type: 'warning'
                        }).then(() => {
                            this.submit('add');
                        }).catch(() => {
                            this.$message({
                                type: 'info',
                                message: '已取消发布'
                            });
                        });
                    }
                });
            },
            editSave() {
                this.$refs.editForm.validate((valid) => {
                    if (valid) {
                        this.submit('edit');
                    }
                });
            },
            submit(type) {
                let para = Object.assign({}, type === 'add' ? this.form : this.editForm);
                this.btnLoading = true;
                request({
                    params: {
                        r: 'admin/notice/notice'
                    },
                    data: para,
                    method: 'post'
                }).then(e => {
                    this.btnLoading = false;
                    if (e.data.code === 0) {
                        setTimeout(()=> {
                            location.reload();
                        },500);
                        this.$message.success(e.data.msg);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.btnLoading = false;
                })
            },
            preview() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        this.dialogVisible = true;
                    }
                });
            },
        },
    });
</script>
