<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
    }

    .form-button {
        margin: 0;
    }

    .form-button .el-form-item__content {
        margin-left: 0!important;
    }

    .button-item {
        padding: 9px 25px;
    }
</style>
<template id="app-comment-edit">
    <el-card class="box-card" v-loading="listLoading" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" class="clearfix">
            <span>客户评价编辑</span>
        </div>
        <div class="form-body">
            <el-form :model="form" label-width="150px" :rules="FormRules" ref="form">
                <el-form-item label="用户名" prop="virtual_user">
                    <el-input v-model="form.virtual_user" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="评价时间" prop="virtual_time">
                    <el-date-picker v-model="form.virtual_time" type="datetime" value-format="yyyy-MM-dd HH:mm:ss" placeholder="选择日期时间"></el-date-picker>
                </el-form-item>
                <el-form-item label="用户头像" prop="virtual_avatar">
                    <app-attachment :multiple="false" :max="1" @selected="virtualAvatar">
                        <el-tooltip class="item" effect="dark" content="建议尺寸:100 * 100" placement="top">
                            <el-button size="mini">选择文件</el-button>
                        </el-tooltip>
                    </app-attachment>
                    <app-image mode="aspectFill" width='80px' height='80px' :src="form.virtual_avatar"></app-image>
                </el-form-item>
                <el-form-item label="商品" prop="goods_id">
                    <el-autocomplete v-model="form.goods_name" value-key="name" :fetch-suggestions="querySearchAsync" placeholder="请输入内容" @select="clerkClick"></el-autocomplete>
                </el-form-item>
                <el-form-item label="评价图片" prop="pic_url">
                    <app-attachment :multiple="true" :max="6" @selected="picUrl">
                        <el-tooltip class="item" effect="dark" content="建议尺寸:750 * 750" placement="top">
                            <el-button size="mini">选择文件</el-button>
                        </el-tooltip>
                    </app-attachment>
                    <div flex="dif:left">
                        <template v-if="form.pic_url.length">
                            <app-image v-for="item in form.pic_url" :key="item.id" mode="aspectFill" width="80px" height='80px' :src="item"></app-image>
                        </template>
                        <template v-else>
                            <app-image mode="aspectFill" width="80px" height='80px'></app-image>
                        </template>
                    </div>
                </el-form-item>
                <el-form-item label="评价" prop="content">
                    <el-input type="textarea" v-model="form.content" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="评分" prop="score">
                    <el-radio-group v-model="form.score">
                        <el-radio label="1">差评</el-radio>
                        <el-radio label="2">中评</el-radio>
                        <el-radio label="3">好评</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="是否匿名" prop="is_anonymous">
                    <el-switch
                            style="margin-left: 20px;"
                            v-model="form.is_anonymous"
                            active-value="1"
                            inactive-value="0">
                    </el-switch>
                </el-form-item>
                <el-form-item label="是否显示" prop="is_show">
                    <el-switch
                            style="margin-left: 20px;"
                            v-model="form.is_show"
                            active-value="1"
                            inactive-value="0">
                    </el-switch>
                </el-form-item>
            </el-form>
        </div>
        <el-button class="button-item" type="primary" :loading="btnLoading" @click="onSubmit">提交</el-button>
    </el-card>
</template>
<script>
Vue.component('app-comment-edit', {
    template: '#app-comment-edit',
    props: {
        sign: {
            type: String,
            default: '',
        },
        navigate_url: {
            type: String,
            default: 'mall/order-comments/edit',
        },
    },
    data() {
        return {
            form: {
                pic_url: [],
                virtual_avatar: '',
            },
            keyword: '',
            listLoading: false,
            btnLoading: false,
            FormRules: {
                virtual_user: [
                    { required: false, message: '用户名不能为空', trigger: 'blur' },
                ],
                virtual_time: [
                    { required: true, message: '评价时间不能为空', trigger: 'blur' },
                ],
                virtual_avatar: [
                    { required: false, message: '用户头像不能为空', trigger: 'blur' },
                ],
                goods_id: [
                    { required: true, message: '商品不能为空', trigger: 'change' },
                ],
                score: [
                    { required: true, message: '评分不能为空', trigger: 'blur' },
                ],
                is_show: [
                    { required: true, message: '是否显示不能为空', trigger: 'blur' },
                ],
                is_anonymous: [
                    { required: true, message: '是否匿名不能为空', trigger: 'blur' },
                ],
            },
        };
    },
    methods: {
        // 用户头像
        virtualAvatar(e) {
            if (e.length) {
                this.form.virtual_avatar = e[0].url;
                this.$refs.form.validateField('virtual_avatar');
            }
        },

        // 评价图片
        picUrl(e) {
            if (e.length) {
                let self = this;
                self.form.pic_url = [];
                e.forEach(function(item, index) {
                    self.form.pic_url.push(item.url);
                });
                this.$refs.form.validateField('pic_url');
            }
        },

        //商品搜索
        querySearchAsync(queryString, cb) {
            this.keyword = queryString;
            this.clerkGoods(cb);
        },

        clerkClick(row) {
            //this.form = Object.assign(this.form, { goods_id: row.id });
            this.form.goods_id = row.id;
            //this.$refs.form.validateField('goods_id');
        },

        clerkGoods(cb) {
            request({
                params: {
                    r: 'mall/order-comments/goods-search',
                    keyword: this.keyword,
                    sign: this.sign,
                },
            }).then(e => {
                if (e.data.code === 0) {
                    cb(e.data.data.list);
                } else {
                    this.$message.error(e.data.msg);
                }
            }).catch(e => {});
        },

        onSubmit() {
            this.$refs.form.validate((valid) => {
                if (valid) {
                    this.btnLoading = true;
                    let para = Object.assign({ sign: this.sign, id: getQuery('id') }, this.form);
                    request({
                        params: {
                            r: 'mall/order-comments/edit',
                        },
                        data: para,
                        method: 'post'
                    }).then(e => {
                        if (e.data.code === 0) {
                            navigateTo({ r: this.navigate_url});
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
                    r: 'mall/order-comments/edit',
                    id: getQuery('id'),
                },
            }).then(e => {
                if (e.data.code == 0) {
                    if (e.data.data.list) {
                        this.form = e.data.data.list;
                        console.log(this.form, 123);
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
