<?php

/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/23
 * Time: 15:14
 */

/* @var $this \yii\web\View */
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
        padding-right: 50%;
        min-width: 1100px;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'plugin/diy/mall/page/index'})">自定义页面</span></el-breadcrumb-item>
                <el-breadcrumb-item v-if="form.id > 0">编辑</el-breadcrumb-item>
                <el-breadcrumb-item v-else>新增页面</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class='table-body'>
            <el-form @submit.native.prevent :model="form" :rules="rules" ref="form" v-loading="loading" label-width="150px">
                <el-form-item label="标题" prop="title">
                    <el-input size='small' v-model="form.title"></el-input>
                </el-form-item>
                <el-form-item label="启用/禁用">
                    <el-switch v-model="form.is_disable" inactive-value="1" active-value="0">
                    </el-switch>
                </el-form-item>
                <el-form-item label="显示导航条">
                    <el-switch v-model="form.show_navs" inactive-value="0" active-value="1">
                    </el-switch>
                </el-form-item>
                <el-form-item label="模板和导航" prop="navs">
                    <div style="max-width: 480px">
                        <template v-for="(nav,index) in form.navs">
                            <el-card shadow="never" style="margin-bottom: 10px;">
                                <div flex="box:last">
                                    <el-form label-width="80px" ref="navForm">
                                        <el-form-item label="导航名称" style="margin-bottom: 20px">
                                            <el-input size='small' v-model="nav.name"></el-input>
                                        </el-form-item>
                                        <el-form-item label="对应模板">
                                            <el-input
                                                    size='small'
                                                    readonly
                                                    :value="nav.template_id?('#'+nav.template_id+':'+nav.template_name):''">
                                                <template slot="append">
                                                    <el-button size='small' @click="selectTemplate(index)">选择模板</el-button>
                                                </template>
                                            </el-input>
                                        </el-form-item>
                                    </el-form>
                                    <div style="padding-left: 15px">
                                        <el-tooltip effect="dark" content="删除导航" placement="bottom">
                                            <el-button style='padding: 0' @click="delNav(index)" circle type="text">
                                                <img src="statics/img/mall/del.png" alt="">
                                            </el-button>
                                        </el-tooltip>
                                    </div>
                                </div>
                            </el-card>
                        </template>
                        <el-button v-if="form.navs.length < 10" @click="addNav" type='text'>
                            <i class="el-icon-plus"></i>
                            <span style='color: #353535'>新增模版和导航</span>
                        </el-button>
                    </div>
                </el-form-item>
            </el-form>
        </div>
        <el-button class='button-item' @click="submit('form')" :loading="submitLoading" type="primary">保存</el-button>
    </el-card>

    <el-dialog title="选择模板" :visible.sync="showTemplateDialog" @open="templateDialogOpen">
        <el-table v-loading="template.loading" :data="template.list" style="margin-bottom: 20px">
            <el-table-column label="ID" prop="id" width="100px"></el-table-column>
            <el-table-column label="模板名称" prop="name"></el-table-column>
            <el-table-column label="操作" width="100px">
                <template slot-scope="scope">
                    <el-button @click="selectTemplateItem(scope.row)" size="mini">选择</el-button>
                </template>
            </el-table-column>
        </el-table>
        <div style="text-align: center">
            <el-pagination
                    v-if="template.pagination"
                    style="display: inline-block"
                    background
                    @current-change="templatePageChange"
                    layout="prev, pager, next, jumper"
                    :page-size.sync="template.pagination.pageSize"
                    :total="template.pagination.totalCount">
            </el-pagination>
        </div>
    </el-dialog>
</div>
<script>
    new Vue({
        el: '#app',
        data() {
            const validateNavs = (rule, value, callback) => {
                for (let i in value) {
                    if (!value[i].name || !value[i].template_id) {
                        return callback(new Error('导航名称和对应模板不能为空。'));
                    }
                }
                return callback();
            };
            return {
                showTemplateDialog: false,
                template: {
                    page: 1,
                    loading: false,
                    pagination: null,
                    list: [],
                    resolve: null,
                },
                form: {
                    id: null,
                    title: '',
                    is_disable: '0',
                    show_navs: '0',
                    navs: [
                        {
                            name: '',
                            template_id: null,
                            template_name: '',
                        }
                    ],
                },
                rules: {
                    title: [
                        {required: true, message: '请输入标题'},
                    ],
                    navs: [
                        {required: true, message: '至少添加一个导航'},
                        {validator: validateNavs},
                    ],
                },
                loading: false,
                submitLoading: false,
            };
        },
        created() {
            this.form.id = getQuery('id');
            if (this.form.id) {
                this.loadData();
            }
        },
        methods: {
            loadData() {
                this.loading = true;
                this.$request({
                    params: {
                        r: 'plugin/diy/mall/page/edit',
                        id: this.form.id,
                    },
                }).then(response => {
                    this.loading = false;
                    if (response.data.code === 0) {
                        this.form = response.data.data;
                    } else {
                        this.$message.error(response.data.msg);
                    }
                }).catch(e => {
                });
            },
            addNav() {
                this.form.navs.push({
                    name: '',
                    template_id: 0,
                });
            },
            delNav(index) {
                this.form.navs.splice(index, 1);
            },
            selectTemplate(index) {
                this.getTemplate().then(template => {
                    this.form.navs[index].template_id = template.id;
                    this.form.navs[index].template_name = template.name;
                });
            },
            getTemplate() {
                return new Promise(resolve => {
                    this.showTemplateDialog = true;
                    this.template.resolve = resolve;
                });
            },
            templateDialogOpen() {
                if (!this.template.list || !this.template.list.length) {
                    this.loadTemplates();
                }
            },
            templatePageChange(page) {
                this.template.page = page;
                this.loadTemplates();
            },
            loadTemplates() {
                this.template.loading = true;
                this.$request({
                    params: {
                        r: 'plugin/diy/mall/template/index',
                        page: this.template.page,
                    },
                }).then(response => {
                    this.template.loading = false;
                    if (response.data.code === 0) {
                        this.template.pagination = response.data.data.pagination;
                        this.template.list = response.data.data.list;
                    } else {
                        this.$message.error(response.data.msg);
                    }
                }).catch(e => {
                });
            },
            selectTemplateItem(template) {
                if (this.template.resolve) {
                    this.template.resolve(template);
                    this.template.resolve = null;
                    this.showTemplateDialog = false;
                }
            },
            submit(formName) {
                this.$refs[formName].validate(valid => {
                    if (valid) {
                        this.submitLoading = true;
                        this.$request({
                            params: {
                                r: 'plugin/diy/mall/page/edit'
                            },
                            method: 'post',
                            data: this.form,
                        }).then(response => {
                            this.submitLoading = false;
                            if (response.data.code === 0) {
                                this.$message.success(response.data.msg);
                                this.$navigate({
                                    r: 'plugin/diy/mall/page/index',
                                });
                            } else {
                                this.$message.error(response.data.msg);
                            }
                        }).catch(e => {
                        });
                    }
                });
            },
        },
    });
</script>