<?php defined('YII_ENV') or exit('Access Denied'); ?>

<style>
    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 50%;
    }

    .form-button {
        margin: 0!important;
    }

    .form-button .el-form-item__content {
        margin-left: 0!important;
    }

    .button-item {
        padding: 9px 25px;
    } 
</style>
<section id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" class="clearfix">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer"
                                          @click="$navigate({r:'mall/topic-type/index'})">专题分类</span></el-breadcrumb-item>
                <el-breadcrumb-item>专题分类编辑</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body" >
            <el-form :model="form"  label-width="80px" v-loading="loading" :rules="FormRules" ref="form">
                <el-form-item label="名称" size="small" prop="name">
                    <el-input v-model="form.name" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="排序" size="small" prop="sort">
                    <el-input v-model="form.sort" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="状态">
                    <el-switch
                            v-model="form.status"
                            :active-value="1"
                            :inactive-value="0">
                    </el-switch>
                </el-form-item>
            </el-form>
        </div>
        <el-button class="button-item" type="primary" size="small" :loading=btnLoading @click="onSubmit">提交</el-button>
    </el-card>
</section>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                form: {},
                loading: false,
                btnLoading: false,
                FormRules: {
                    name: [
                        { required: true, message: '标题不能为空', trigger: 'blur' },
                        { min: 1, max: 30, message: "标题长度在1-30个字符内" },
                    ],
                    sort: [
                        { required: false, pattern: /^[0-9]\d{0,8}$/, message: '排序必须在9位整数内' }
                    ]
                },
            };
        },
        methods: {
            onSubmit() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        let para = Object.assign(this.form);
                        request({
                            params: {
                                r: 'mall/topic-type/edit',
                            },
                            data: para,
                            method: 'post'
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code == 0) {
                                const h = this.$createElement;
                                this.$message({
                                  message: e.data.msg,
                                  type: 'success'
                                });
                                setTimeout(function(){
                                    navigateTo({ r: 'mall/topic-type/index' });
                                },300);
                            }
                        }).catch(e => {
                            this.btnLoading = false;
                            this.$alert(e.data.msg, '提示', {
                              confirmButtonText: '确定'
                            })
                        });
                    }
                });
            },
            getTopicForm() {
                this.loading = true;
                let _this = this;
                request({
                    params: {
                        r: 'mall/topic-type/edit',
                        id: getQuery('id'),
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        _this.form = e.data.data;
                    }
                }).catch(e => {
                    this.loading = false;
                    this.$alert(e.data.msg, '提示', {
                      confirmButtonText: '确定'
                    })
                });
            },

        },
        mounted() {
            this.getTopicForm();
        }
    })
</script>
