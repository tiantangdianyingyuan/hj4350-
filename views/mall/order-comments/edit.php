<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .form-body {
        padding: 20px 50% 20px 20px;
        background-color: #fff;
        margin-bottom: 20px;
        min-width: 1000px;
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
<section id="app" v-cloak>
    <el-card class="box-card" v-loading="listLoading" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" class="clearfix">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'mall/order-comments/index'})">评价管理</span></el-breadcrumb-item>
                <el-breadcrumb-item>客户评价编辑</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form :model="form" label-width="150px" :rules="FormRules" ref="form">
                <el-form-item label="用户名" prop="virtual_user">
                    <el-input size="small" v-model="form.virtual_user" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="评价时间" prop="virtual_time">
                    <el-date-picker style="width: 100%" size="small" v-model="form.virtual_time" type="datetime" value-format="yyyy-MM-dd HH:mm:ss" placeholder="选择日期时间"></el-date-picker>
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
                    <el-autocomplete style="width: 100%"
                                     size="small"
                                     :validate-event="false"
                                     v-model="form.goods_name"
                                     value-key="name"
                                     :fetch-suggestions="querySearchAsync"
                                     :trigger-on-focus="false"
                                     placeholder="请输入内容"
                                     @select="clerkClick"
                    ></el-autocomplete>
                </el-form-item>
                <el-form-item label="规格选择" prop="attr_id">
                    <el-select v-model="form.attr_id" placeholder="无">
                        <el-option v-for="item in attr_list" :label="item.attr_str" :value="item.id"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="评价图片" prop="pic_url">
                    <app-attachment :multiple="true" :max="6" @selected="picUrl">
                        <el-tooltip class="item" effect="dark" content="建议尺寸:750 * 750" placement="top">
                            <el-button size="mini">选择文件</el-button>
                        </el-tooltip>
                    </app-attachment>
                    <div flex="dif:left">
                        <template v-if="form.pic_url.length">
                            <app-image v-for="item in form.pic_url" :key="item.id" mode="aspectFill" width="80px"
                                       height='80px' :src="item"></app-image>
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
                            v-model="form.is_anonymous"
                            active-value="1"
                            inactive-value="0">
                    </el-switch>
                </el-form-item>
                <el-form-item label="是否显示" prop="is_show">
                    <el-switch
                            v-model="form.is_show"
                            active-value="1"
                            inactive-value="0">
                    </el-switch>
                </el-form-item>
            </el-form>
        </div>
        <el-button class="button-item" type="primary" :loading="btnLoading" @click="onSubmit">提交</el-button>
    </el-card>
</section>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                form: {
                    pic_url: [],
                    virtual_avatar: '',
                    is_show: 0,
                    goods_id: '',
                    attr_id: '',
                    goods_name: '',
                    virtual_user: '',
                    virtual_time: '',
                    score: '',
                    is_anonymous: '',
                },
                keyword: '',
                listLoading: false,
                btnLoading: false,
                FormRules: {
                    virtual_user: [
                        {required: false, message: '用户名不能为空', trigger: 'blur'},
                    ],
                    virtual_avatar: [
                        {required: false, message: '用户头像不能为空', trigger: 'blur'},
                    ],
                    virtual_time: [
                        {required: true, message: '评价时间不能为空', trigger: 'blur'},
                    ],
                    goods_id: [
                        {required: true, message: '商品不能为空', trigger: 'change'},
                    ],
                    attr_id: [
                        {required: true, message: '规格不能为空', trigger: 'change'},
                    ],
                    score: [
                        {required: true, message: '评分不能为空', trigger: 'blur'},
                    ],
                    is_show: [
                        {required: true, message: '是否显示不能为空', trigger: 'blur'},
                    ],
                    is_anonymous: [
                        {required: true, message: '是否匿名不能为空', trigger: 'blur'},
                    ],
                },
                hasSwitch: false,
                attr_list: null,
            };
        },
        watch: {
            'form.goods_name': {
                handler(newData, oldData) {
                    if (this.hasSwitch && newData !== oldData) {
                        this.hasSwitch = false;
                        this.form.goods_id = '';
                        this.attr_list = [];
                        this.form.attr_id = null;
                    }
                },
                deep: true,
            }
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
                    e.forEach(function (item, index) {
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
                Object.assign(this.form, {goods_id: row.id});
                this.attr_list = row.attr;
                let self = this;
                setTimeout(() => {
                    self.hasSwitch = true;
                })
            },

            clerkGoods(cb) {
                request({
                    params: {
                        r: 'mall/order-comments/goods-search',
                        keyword: this.keyword,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        cb(e.data.data.list);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },

            onSubmit() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        let para = Object.assign({id: getQuery('id')}, this.form);
                        request({
                            params: {
                                r: 'mall/order-comments/edit',
                            },
                            data: para,
                            method: 'post'
                        }).then(e => {
                            if (e.data.code === 0) {
                                navigateTo({r: 'mall/order-comments/index'});
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
                            this.attr_list = e.data.data.attr;
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