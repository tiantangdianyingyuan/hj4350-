<?php defined('YII_ENV') or exit('Access Denied'); ?>
<?php Yii::$app->loadViewComponent('app-rich-text') ?>
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
                                          @click="$navigate({r:'mall/article/index'})">文章</span></el-breadcrumb-item>
                <el-breadcrumb-item>文章编辑</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form :model="form" label-width="100px" :rules="FormRules" ref="form">
                <el-form-item label="标题" prop="title">
                    <el-input size="small" v-model="form.title" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="排序" prop="sort">
                    <el-input size="small" v-model="form.sort" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="状态">
                    <el-switch
                            v-model="form.status"
                            active-value="1"
                            inactive-value="0">
                    </el-switch>
                </el-form-item>
                <el-form-item label="内容" prop="discount">
                    <app-rich-text v-model="form.content"></app-rich-text>
                </el-form-item>
            </el-form>
        </div>
        <el-button type="primary" class="button-item" @click="onSubmit">提交</el-button>
    </el-card>
</section>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                form: {
                    title: '',
                    sort: '',
                    textarea: '',
                    content: '',
                    status: 0,
                },
                listLoading: true,
                FormRules: {
                    title: [
                        {required: true, message: '标题不能为空', trigger: 'blur'},
                        {min: 1, max: 30, message: "标题长度在1-30个字符内"},
                    ],
                    sort: [
                        {required: false, pattern: /^[0-9]\d{0,8}$/, message: '排序必须在9位整数内'}
                    ]
                },
            };
        },
        methods: {
            onSubmit() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        let para = Object.assign({article_cat_id: getQuery('article_cat_id')}, this.form);
                        request({
                            params: {
                                r: 'mall/article/edit',
                            },
                            data: para,
                            method: 'post'
                        }).then(e => {
                            if (e.data.code === 0) {
                                navigateTo({r: 'mall/article/index'});
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                        });
                    }
                });
            },

            getList() {
                request({
                    params: {
                        r: 'mall/article/edit',
                        id: getQuery('id'),
                        article_cat_id: getQuery('article_cat_id'),
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        if (e.data.data) {
                            this.form = e.data.data;
                        }
                    }
                }).catch(e => {
                });
            },
        },

        mounted() {
            this.getList();
        }
    })
</script>
