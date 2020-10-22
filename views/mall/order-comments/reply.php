<?php defined('YII_ENV') or exit('Access Denied'); ?>

<section id="app" v-cloak>
    <el-card class="box-card" v-loading="listLoading">
        <div slot="header" class="clearfix">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'mall/order-comments/index'})">评价管理</span></el-breadcrumb-item>
                <el-breadcrumb-item>评价回复</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="text item" style="width:50%">
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
                    <div v-text="form.content"></div>
                </el-form-item>
                <el-form-item label="回复评价" prop="reply_content">
                    <el-input type="textarea" v-model="form.reply_content" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" :loading="btnLoading" @click="onSubmit">提交</el-button>
                </el-form-item>
            </el-form>
        </div>
    </el-card>
</section>
<script>
const app = new Vue({
    el: '#app',
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
                            navigateTo({ r: 'mall/order-comments/index' });
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
                    r: 'mall/order-comments/reply',
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
