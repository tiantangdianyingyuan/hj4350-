<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .form-body {
        padding: 20px;
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
<section id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" class="clearfix">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'plugin/scratch/mall/scratch/index'})">奖品列表</span></el-breadcrumb-item>
                <el-breadcrumb-item>添加奖品</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="text item form-body">
            <el-form :model="form" v-loading="loading" label-width="180px" :rules="FormRules" ref="form"
                     style="width: 50%;">
                <el-form-item label="奖品选择" size="small" prop="type">
                    <el-radio v-model="form.type" label="1">余额红包</el-radio>
                    <el-radio v-model="form.type" label="2">优惠券</el-radio>
                    <el-radio v-model="form.type" label="3">积分</el-radio>
                    <el-radio v-model="form.type" label="4">赠品</el-radio>
                </el-form-item>
                <el-form-item label="状态" size="small" prop="status">
                    <el-switch v-model="form.status" active-value="1" inactive-value="0"></el-switch>
                </el-form-item>
                <el-form-item label="红包金额" size="small" prop="price" v-if="form.type == 1">
                    <el-input v-model="form.price" auto-complete="off">
                        <template slot="append">元</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="优惠券" size="small" prop="coupon_id" v-if="form.type == 2">
                    <el-select style="width: 180px;" size="small" v-model="form.coupon_id">
                        <el-option
                          v-for="item in coupon"
                          :key="item.id"
                          :value="item.id"
                          :label="item.name">
                        </el-option>
                    </el-select>
                    <el-button type="primary" plain @click="$navigate({r:'mall/coupon/edit'})">新增优惠券</el-button>
                </el-form-item>
                <el-form-item label="积分数量" size="small" prop="num" v-if="form.type == 3">
                    <el-input v-model="form.num" type="num" auto-complete="off"></el-input>
                </el-form-item>

                <el-form-item label="商品搜索" prop="" v-if="form.type == 4">
                    <el-autocomplete v-model="form.nickname" value-key="goods_name" :fetch-suggestions="querySearchAsync"
                                     placeholder="请输入内容" @select="clerkClick"></el-autocomplete>
                </el-form-item>

                <el-form-item label="规格选择" prop="attr_id" v-if="form.type == 4">
                    <el-select v-model="form.attr_id" placeholder="无">
                        <el-option v-for="item in form.attr_list" :label="item.attr_str" :value="item.id"></el-option>
                    </el-select>
                </el-form-item>

                <el-form-item label="库存" size="small" prop="stock">
                    <el-input v-model="form.stock" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" size="small" @click="onSubmit">提交</el-button>
                    <el-button @click="handleCancel" size="small">取消</el-button>
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
                form: {type:'1',status:'0', attr_id: null, attr_list: []},
                coupon: [],
                loading:false,
                FormRules: {
                    stock: [
                        { required: true, message: '库存不能为空', trigger: 'blur' },
                    ],
                    num: [
                        { required: true, message: '积分数量不能为空', trigger: 'blur' },
                        { required: true, pattern: /^[1-9]\d{0,8}$/,message:'积分数量必须在1~999999999以内'}
                    ],
                    price: [
                        { required: true, message: '红包金额不能为空', trigger: 'blur' },
                        { pattern: /^(?!0$)([1-9][0-9]{0,8}|0)(\.(?![0]{1,2}$)[0-9]{1,2})?$/,message:'红包金额必须在0.01~999999999.99以内'}
                    ],
                    status: [
                        { required: true }
                    ],
                    type: [
                        { required: true }
                    ],
                    coupon_id: [
                        { required: true }
                    ],
                    attr_id: [
                        {required: true, message: '规格不能为空', trigger: 'blur'}
                    ]
                },
                keyword: null,
            };
        },
        methods: {
            //搜索
            querySearchAsync(queryString, cb) {
                this.keyword = queryString;
                this.clerkUser(cb);
            },

            clerkClick(row) {
                this.form.attr_id = null;
                this.form.attr_list = row.attr;
                if(this.form.attr_list && this.form.attr_list.length)
                    this.form.attr_id = this.form.attr_list[0].id;
            },

            clerkUser(cb) {
                request({
                    params: {
                        r: 'plugin/scratch/mall/scratch/search/index',
                        keyword: this.keyword,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        cb(e.data.data.list);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {});
            },

            handleCancel: function() {
                window.history.go(-1)
            },

            onSubmit() {
                let _this = this;
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        let para = Object.assign({},this.form,{attr_list: null});
                        request({
                            params: {
                                r: 'plugin/scratch/mall/scratch/edit',
                            },
                            data: para,
                            method: 'post'
                        }).then(e => {
                            if (e.data.code == 0) {
                                const h = this.$createElement;
                                this.$message({
                                  message: e.data.msg,
                                  type: 'success'
                                });
                                setTimeout(function(){
                                    navigateTo({ r: 'plugin/scratch/mall/scratch/index' });
                                },300);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            this.$alert(e.data.msg, '提示', {
                              confirmButtonText: '确定'
                            })
                        });
                    }
                });
            },
            getList() {
                this.loading = true;
                let _this = this;
                request({
                    params: {
                        r: 'plugin/scratch/mall/scratch/edit',
                        id: getQuery('id'),
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        _this.coupon = e.data.data.coupon;
                        if(e.data.data.list[0]){
                            _this.form = e.data.data.list[0];
                        }
                    }
                })
            },

        },
        mounted() {
            this.getList();
        }
    })
</script>
