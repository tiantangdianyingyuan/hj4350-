<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>

<style>
    .form-body {
        padding: 20px 20px;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 40%;
    }

    .form-button {
        margin: 0 !important;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .button-item {
        padding: 9px 25px;
    }

    .add-image-btn {
        width: 100px;
        height: 100px;
        color: #419EFB;
        border: 1px solid #e2e2e2;
        cursor: pointer;
    }

    .pic-url-remark {
        font-size: 13px;
        color: #c9c9c9;
        margin-bottom: 12px;
    }

    .del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px;
    }

    .price-item {
        margin-bottom: 15px;
    }
    .price-item .el-radio {
        margin-right: 15px;
    }
    .input-item {
        width: 100%;
    }
</style>
<div id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;"
             v-loading="cardLoading">
        <div slot="header">
            <div>
                <span></span>
            </div>
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer"
                                          @click="$navigate({r:'mall/live/goods'})">直播商品</span></el-breadcrumb-item>
                <el-breadcrumb-item>添加直播商品</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="130px">
                <el-form-item prop="number">
                    <template slot="label">
                        <span>商城商品编码</span>
                        <el-tooltip effect="dark" placement="top"
                                    content="只能从商城中获取商品信息，且基本信息与商城商品保持一致">
                            <i class="el-icon-info"></i>
                        </el-tooltip>
                    </template>
                    <el-input :disabled="isDisabled" v-model="copyGoods.goods_id" type="number" min="0"
                              oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                              placeholder="请输入商城商品id">
                        <template slot="append">
                            <el-button :disabled="isDisabled" @click="getMallGoods(copyGoods.goods_id)" :loading="copyGoods.loading">获取
                            </el-button>
                        </template>
                    </el-input>
                </el-form-item>
                <el-form-item label="商品名称" prop="goods_name">
                    <el-input :disabled="isDisabled" v-model="ruleForm.goods_name" placeholder="请输入商品名称,最多可输入14个字" max="250"></el-input>
                </el-form-item>
                <template>
                    <el-form-item label="商品图片" prop="pic_url" >
                        <app-attachment v-if="!isDisabled" :multiple="false" :max="1" v-model="ruleForm.pic_url">
                            <el-tooltip effect="dark" content="上传一张作为缩略图,图片尺寸最大 300*300" placement="top">
                                <el-button  style="margin-bottom: 10px;" size="mini">选择图片</el-button>
                            </el-tooltip>
                        </app-attachment>
                        <app-gallery :url="ruleForm.pic_url" :show-delete="isDisabled ? false : true" @deleted="ruleForm.pic_url = ''"
                                     width="80px" height="80px">
                        </app-gallery>
                    </el-form-item>
                </template>
                <el-form-item label="价格形式" prop="price_type">
                    <div flex="dir:left cross:center" class="price-item">
                        <el-radio v-model="ruleForm.price_type" label="1">一口价</el-radio>
                        <div class="input-item">
                            <el-input :disabled="ruleForm.price_type != 1" placeholder="请输入价格" v-model="ruleForm.price"
                            type="number" oninput="this.value = this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3');" :min="0">
                                <template slot="append">元</template>
                            </el-input>
                        </div>
                    </div>
                    <div flex="dir:left cross:center" class="price-item">
                        <el-radio v-model="ruleForm.price_type" label="2">区间价</el-radio>
                        <div class="input-item">
                            <el-input :disabled="ruleForm.price_type != 2" placeholder="请输入价格" v-model="ruleForm.price1" type="number" oninput="this.value = this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3');" :min="0">
                                <template slot="append">元</template>
                            </el-input>
                        </div>
                        <span style="margin: 0 5px;">—</span>
                        <div class="input-item">
                            <el-input :disabled="ruleForm.price_type != 2" placeholder="请输入价格" v-model="ruleForm.price2" type="number" oninput="this.value = this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3');" :min="0">
                                <template slot="append">元</template>
                            </el-input>
                        </div>
                    </div>
                    <div flex="dir:left cross:center" class="price-item">
                        <el-radio v-model="ruleForm.price_type" label="3">折扣价</el-radio>
                        <div style="width: 100px;">原价</div>
                        <div class="input-item">
                            <el-input :disabled="ruleForm.price_type != 3" placeholder="请输入价格" v-model="ruleForm.price3" type="number" oninput="this.value = this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3');" :min="0">
                                <template slot="append">元</template>
                            </el-input>
                        </div>
                        <div style="width: 100px;margin-left: 20px;">现价</div>
                        <div class="input-item">
                            <el-input :disabled="ruleForm.price_type != 3" placeholder="请输入价格" v-model="ruleForm.price4" type="number" oninput="this.value = this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3');" :min="0">
                                <template slot="append">元</template>
                            </el-input>
                        </div>
                    </div>
                </el-form-item>
                <el-form-item label="小程序路径" prop="page_url">
                    <el-input :disabled="isDisabled" v-model="ruleForm.page_url" placeholder="请输入小程序路径"></el-input>
                </el-form-item>
            </el-form>
        </div>
        <template v-if="auditStatus == 0 || auditStatus == 3">
            <el-button class="button-item" :loading="btnLoading" type="primary" @click="submitAudit" size="small">提交审核
            </el-button>
            <el-button class="button-item" :loading="btnLoading"  @click="store('ruleForm')" size="small">保存
            </el-button>
        </template>
        <template v-else>
            <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">保存
            </el-button>
        </template>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                ruleForm: {
                    goods_name: '',
                    pic_url: '',
                    page_url: '',
                    price: 0,
                    price1: 0,
                    price2: 0,
                    price3: 0,
                    price4: 0,
                    price_type: '1',
                    goods_id: 0,
                },
                isDisabled: true,
                auditStatus: -1,
                rules: {
                    goods_name: [
                        {required: true, message: '请输入商品名称', trigger: 'change'},
                    ],
                    pic_url: [
                        {required: true, message: '请添加商品图片', trigger: 'blur'},
                    ],
                    page_url: [
                        {required: true, message: '请输入小程序路径', trigger: 'change'},
                    ],
                    price_type: [
                        {required: true, message: '请输入价格', trigger: 'change'},
                    ],
                },
                btnLoading: false,
                cardLoading: false,
                copyGoods: {
                    goods_id: '',
                    loading: false,
                },
                newStatus: 1,
            };
        },
        methods: {
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/live/goods-edit'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                                navigateTo({
                                    r: 'mall/live/goods',
                                    status: self.newStatus
                                })
                            } else {
                                self.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            self.$message.error(e.data.msg);
                            self.btnLoading = false;
                        });
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
            getMallGoods(id) {
                let self = this;
                self.copyGoods.loading = true;
                request({
                    params: {
                        r: 'mall/goods/edit',
                        id: id,
                    },
                    method: 'get',
                }).then(e => {
                    self.copyGoods.loading = false;
                    if (e.data.code == 0) {
                        let goods = e.data.data.detail;
                        self.ruleForm.goods_name = goods.goods_warehouse.name;
                        self.ruleForm.pic_url = goods.cover_pic;

                        self.ruleForm.price = goods.min_price;
                        self.ruleForm.price1 = goods.min_price;
                        self.ruleForm.price2 = goods.max_price;
                        self.ruleForm.price3 = goods.original_price;
                        self.ruleForm.price4 = goods.min_price;
                        self.ruleForm.goods_id = goods.goods_id;
                        self.ruleForm.page_url = 'pages/goods/goods.html?id=' + id

                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.copyGoods.loading = false;
                });
            },
            getDetail(){
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'mall/live/detail',
                        goods_id: getQuery('goods_id'),
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        let goods = e.data.data.goods;
                        this.ruleForm.goods_name = goods.name;
                        this.ruleForm.pic_url = goods.cover_img_url;
                        this.ruleForm.page_url = goods.url;
                        this.ruleForm.price_type = goods.price_type.toString();
                        this.ruleForm.goods_id = goods.goods_id;
                        if (this.ruleForm.price_type == 1) {
                            this.ruleForm.price = goods.price;
                        } else if (this.ruleForm.price_type == 2) {
                            this.ruleForm.price1 = goods.price;
                            this.ruleForm.price2 = goods.price2;
                        } else if (this.ruleForm.price_type == 3) {
                            this.ruleForm.price3 = goods.price;
                            this.ruleForm.price4 = goods.price2;
                        }

                        this.auditStatus = goods.audit_status;
                        this.setDisabled();
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.cardLoading = false;
                });
            },
            setDisabled() {
                console.log(this.auditStatus == 0);
                if (!getQuery('goods_id') || this.auditStatus == 0) {
                    this.isDisabled = false;
                }
            },
            submitAudit() {
                let self = this;
                this.$confirm('提交审核, 是否继续?', '提示', {
                  confirmButtonText: '确定',
                  cancelButtonText: '取消',
                  type: 'warning'
                }).then(() => {
                      request({
                        params: {
                            r: 'mall/live/submit-audit',
                        },
                        data: {
                            goods_id: getQuery('goods_id'),
                        },
                        method: 'post',
                    }).then(e => {
                        if (e.data.code == 0) {
                            self.$message.success(e.data.msg);
                            navigateTo({
                                r: 'mall/live/goods',
                                status: 1,
                            })
                        } else {
                            self.$message.error(e.data.msg)
                        }
                    }).catch(e => {
                        console.log(e);
                    });
                }).catch(() => {

                });
            },
        },
        mounted() {
            if (getQuery('goods_id')) {
                this.getDetail();
                this.newStatus = 0;
            }
            this.setDisabled();
        }
    });
</script>
