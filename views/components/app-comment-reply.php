<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .app-comment-reply .form-body {
        padding: 20px 50% 20px 20px;
        background-color: #fff;
        margin-bottom: 20px;
    }

    .app-comment-reply .form-button {
        margin: 0;
    }

    .app-comment-reply .form-button .el-form-item__content {
        margin-left: 0!important;
    }

    .app-comment-reply .button-item {
        padding: 9px 25px;
    }
</style>
<template id="app-comment-reply">
    <el-card class="app-comment-reply" v-loading="listLoading" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" class="clearfix">
            <span>评价回复</span>
        </div>
        <div class="form-body">
            <el-form :model="form" label-width="150px" :rules="FormRules" ref="form">
               <el-form-item label="用户">
                    <div v-text="form.nickname"></div>
                </el-form-item>
               <el-form-item label="商品名称">
                    <div v-text="form.goods_name"></div>
                </el-form-item>
               <el-form-item label="评分">
                    <el-tag type="success" v-if="form.score==3">好评</el-tag>
                    <el-tag type="warning" v-if="form.score==2">中评</el-tag>
                    <el-tag type="danger" v-if="form.score==1">差评</el-tag>
                </el-form-item>
               <el-form-item label="详情">
                    <app-image style="display:inline-block" v-for="item in form.pic_url" :key="item.id" mode="aspectFill" :src="item"></app-image>
                    <div style="text-align:center" v-text="form.content"></div>
                </el-form-item>
                <el-form-item label="回复评价" prop="reply_content">
                    <el-input type="textarea" v-model="form.reply_content" autocomplete="off"></el-input>
                </el-form-item>
            </el-form>
        </div>
        <el-button class="button-item" type="primary" :loading="btnLoading" @click="onSubmit">提交</el-button>
    </el-card>
</template>
<script>
Vue.component('app-comment-reply', {
    template: '#app-comment-reply',
    props: {
        navigate_url: {
            type: String,
            default: 'mall/order-comments',
        },
    },
    data() {
        return {
            form: {},
            listLoading: false,
            btnLoading: false,
            FormRules: {
                reply_content: [
                    { required: true, message: '回复评价不能为空', trigger: 'blur' },
                ],
            },
        };
    },
    methods: {
        onSubmit() {
            console.log(this.url);
            this.$refs.form.validate((valid) => {
                if (valid) {
                    this.btnLoading = true;
                    let para = Object.assign({}, this.form);
                    request({
                        params: {
                            r: 'mall/order-comments/reply',
                        },
                        data: para,
                        method: 'post'
                    }).then(e => {
                        if (e.data.code === 0) {
                            navigateTo({ r: this.navigate_url });
                        } else {
                            this.$message.error(e.data.msg);
                        }
                        this.btnLoading = false;
                    }).catch(e => {
                        this.btnLoading = false;
                    });
                }
            });
        },

        getList() {
            this.listLoading = true;
            request({
                params: {
                    r:  'mall/order-comments/reply',
                    id: getQuery('id'),
                },
            }).then(e => {

                if (e.data.code == 0) {
                    if (e.data.data) {
                        this.form = e.data.data.list;
                    }
                }
                this.listLoading = false;
            }).catch(e => {
                this.listLoading = false;
            });
        },
    },

    mounted() {
        this.getList();
    }
})
</script>
