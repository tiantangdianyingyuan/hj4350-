<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .preview {
        background-color: #fff;
        border-radius: 30px;
        padding: 40px 12.5px;
        width: 400px;
        height: 740px;
        position: relative;
    }

    .preview-icon {
        position: absolute;
        height: 45%;
        top: 50%;
        left: 12%;
        right: 12%;
    }

    .el-tabs__nav-wrap .el-tabs__nav-scroll {
        height: 468px !important;
        background: #f3f3f3;
    }

    .is-left.is-active {
        background: #ffffff;
        color: #409EFF;
    }

    .el-tabs--card > .el-tabs__header .el-tabs__nav {
        border-left: none;
        border-top: none;
    }

    .setting-menu {
        margin-left: 1%;
        width: 100%;
        height: 100%;
        position: relative;
        overflow: visible;
    }

    .button-item {
        padding: 9px 25px;
        margin-top: 24px;
    }

    .el-form-item {
        margin-bottom: 1%;
    }

    .pond-left {
        height: 26.5%;
        padding: 4% 4% 7%;
        width: 33%;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>奖品列表</span>
        </div>
        <div style="display: flex;">
            <div class="preview" v-loading=loading>
                <app-image style="border:2px solid #F4F5F6" src="<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl() ?>/img/pond.png"
                           width='375px'
                           height='660px'></app-image>
                <div class="preview-icon">
                    <el-col :span=8 v-for="(item,index) in prizeImg" class="pond-left">
                        <el-image style="width:100%;height:100%;" :src="item" v-if="index != 4"
                                  fit="contain"></el-image>
                    </el-col>
                </div>
            </div>
            <div class="setting-menu">
                <el-card shadow="never" style="background: #FFFFFF" body-style="padding:2%;padding-right: 0">
                    <div slot="header">
                        <span>奖品设置</span>
                    </div>
                    <div class="text item" v-loading=loading>
                        <el-form :model="form" label-width="80px" :rules='FormRules' ref="form">
                            <el-tabs type="card" style="border:1px solid #e2e2e2;height:470px" v-model="activeName"
                                     @tab-click="handleClick" tab-Position="left" :before-leave='toleave'>
                                <el-tab-pane :label="item.name" :name="item.id" v-for="item in prize" :key="item.id">
                                    <div style="margin-top:25px">
                                        <el-form-item label="奖品选择" prop="type">
                                        <el-radio v-model="form.type" label="5">谢谢参与</el-radio>
                                        <el-radio v-model="form.type" label="1">余额红包</el-radio>
                                        <el-radio v-model="form.type" label="2">优惠券</el-radio>
                                        <el-radio v-model="form.type" label="3">积分</el-radio>
                                        <el-radio v-model="form.type" label="4">赠品</el-radio>
                                    </el-form-item>
                                    <el-form-item prop="image_url">
                                        <app-attachment title="缩略图" @selected="singleSelected">
                                            <el-tooltip class="item"
                                                        effect="dark"
                                                        content="建议尺寸:150 * 80"
                                                        placement="top">
                                                <el-button size="mini">选择图片</el-button>
                                            </el-tooltip>
                                        </app-attachment>
                                        <app-image :src='form.image_url' width="80px" height="80px"></app-image>
                                    </el-form-item>
                                    <el-form-item label="名称" prop="name" v-if="form.type == 1 || form.type == 3">
                                        <el-input style='max-width: 180px' size="small" placeholder="奖品别名" size="small" v-model="form.name"
                                                  autocomplete="off"></el-input>
                                    </el-form-item>
                                    <el-form-item label="红包金额" prop="price" v-if="form.type == 1">
                                        <el-input style='max-width: 180px' size="small" type="number" v-model="form.price"
                                                  autocomplete="off"></el-input>
                                    </el-form-item>
                                    <el-form-item label="优惠券" prop="coupon_id" v-if="form.type == 2">
                                        <el-select size="small" v-model="form.coupon_id" size="small" placeholder="请选择">
                                            <el-option v-for="item in coupon" :label="item.name" :value='item.id'>
                                            </el-option>
                                        </el-select>
                                    </el-form-item>
                                    <el-form-item label="积分数量" prop="num" v-if="form.type == 3">
                                        <el-input style='max-width: 180px' size="small" size="small" type="number" v-model="form.num"
                                                  autocomplete="off"></el-input>
                                    </el-form-item>
                                    <el-form-item label="商品搜索" prop="" v-if="form.type == 4">
                                        <el-autocomplete size="small" v-model="form.goods_name" value-key="goods_name"
                                                         :fetch-suggestions="querySearchAsync" placeholder="请输入内容"
                                                         @select="clerkClick"></el-autocomplete>
                                    </el-form-item>
                                    <el-form-item label="规格选择" prop="attr_id" v-if="form.type == 4">
                                        <el-select size="small" v-model="form.attr_id" placeholder="无">
                                            <el-option v-for="item in form.attr_list" :label="item.attr_str"
                                                       :value="item.id"></el-option>
                                        </el-select>
                                    </el-form-item>
                                        <el-form-item label="库存" prop="stock" v-if="form.type != 5">
                                            <el-input style='max-width: 180px' size="small" type="number"
                                                      v-model="form.stock"
                                                      autocomplete="off"></el-input>
                                        </el-form-item>
                                    </div>
                                </el-tab-pane>
                            </el-tabs>
                        </el-form>
                    </div>

                </el-card>
                <el-button class="button-item" type="primary" size="small" :loading="btnLoading" @click="onSubmit">保存
                </el-button>
            </div>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                form: {attr_id: null},
                keyword: null,
                coupon: [],
                activeName: '0',
                prizeImg: [],
                loading: false,
                attr: true,
                leave: false,
                goods: [],
                goodsName: null,
                timeout: null,
                btnLoading: false,
                prize: [
                    {name: '奖品一', id: '0'},
                    {name: '奖品二', id: '1'},
                    {name: '奖品三', id: '2'},
                    {name: '奖品四', id: '3'},
                    {name: '奖品五', id: '4'},
                    {name: '奖品六', id: '5'},
                    {name: '奖品七', id: '6'},
                    {name: '奖品八', id: '7'}
                ],
                FormRules: {
                    price: [
                        {required: true, message: '红包金额不能为空', trigger: 'blur'},
                        {pattern: /^(?!0$)([1-9][0-9]{0,8}|0)(\.[0-9]{1,2})?$/,message:'红包金额必须在0.01~999999999以内'}
                    ],
                    num: [
                        {required: true, message: '积分数量不能为空', trigger: 'blur'},
                        {required: true, pattern: /^[1-9]\d{0,8}$/,message:'积分数量必须在1~999999999以内'}
                    ],
                    stock: [
                        {required: true, message: '库存不能为空', trigger: 'blur'},
                    ],
                    coupon_id: [
                        {required: true, message: '请选择优惠券', trigger: 'change'},
                    ],
                    type: [
                        {required: true, message: '奖品类型不能为空', trigger: 'blur'},
                    ],
                    attr_id: [
                        {required: true, message: '请输入规格', trigger: 'change'},
                    ]
                },

                attr_list: [],
            };
        },

        methods: {
            //搜索
            querySearchAsync(queryString, callback) {
                var goods = [];
                request({
                    params: {
                        r: 'plugin/pond/mall/pond/search',
                        keyword: queryString
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        goods = e.data.data.list;
                        for (let i of goods) {
                            i.value = i.name
                        }
                        callback(goods);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },

            clerkClick(row) {
                this.form.attr_id = null;
                this.form.attr_list = row.attr;
                if(this.form.attr_list && this.form.attr_list.length)
                    this.form.attr_id = this.form.attr_list[0].id;
            },



            // 选择商品
            handleSelect(item) {
                let that = this;
                if (item == "") {
                    that.$message.error('请输入商品名称');
                } else {
                    that.form.attr = item;
                    that.form.attr_list = item.attr_list;
                    that.form.attr_id = item.attr_list[0].id;
                    setTimeout(function () {
                        that.attr = true;
                    }, 500)
                }
            },
            // 隐藏规格
            hideAttr() {
                this.attr = false;
            },
            // 提交
            onSubmit() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        let para = this.list.map(item => {
                            return Object.assign({},item,{goods: null,attr_list: null})
                        })
                        //let para = Object.assign(this.list);
                        request({
                            params: {
                                r: 'plugin/pond/mall/pond',
                            },
                            data: {
                                list: para
                            },
                            method: 'post'
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code === 0) {
                                this.$alert(e.data.msg, {
                                    confirmButtonText: '确定',
                                    callback: action => {
                                        location.reload();
                                    }
                                });
                            } else {
                                this.$alert(e.data.msg, {
                                    confirmButtonText: '确定',
                                });
                            }
                        }).catch(e => {
                            this.btnLoading = false;
                            this.$alert(e.data.msg, {
                                confirmButtonText: '确定',
                            });
                        });
                    }
                });
            },
            // 展示图片
            singleSelected(list) {
                this.form.image_url = list.length ? list[0].url : null;
                this.imgFormat(list);
            },
            imgFormat(list) {
                this.prizeImg = [
                    this.list[0].image_url,
                    this.list[1].image_url,
                    this.list[2].image_url,
                    this.list[7].image_url,
                    '',
                    this.list[3].image_url,
                    this.list[6].image_url,
                    this.list[5].image_url,
                    this.list[4].image_url,
                ];
            },
            // 表单验证没通过，不能切换
            toleave() {
                return this.$refs.form.validate().then(() => {
                    this.$refs.form.validate()
                }).catch(() => {
                    this.$message.error('填写完整后方能切换');
                    rej();
                });
            },
            // 切换左侧列表
            handleClick() {
                // this.$refs.form.validate((valid) => {
                //     if (valid) {
                //         this.loading = true;
                //     }
                // })
                let that = this;
                setTimeout(function () {
                    that.form = that.list[that.activeName]
                    console.log(that.form);
                    that.loading = false;
                }, 500)
            },

            getList() {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/pond/mall/pond',
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        this.loading = false;
                        this.coupon = e.data.data.coupon;
                        this.list = e.data.data.list;
                        this.form = e.data.data.list[0];
                        this.imgFormat(this.list);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.loading = false;
                }).catch(e => {
                    this.loading = false;
                });
            },
        },
        mounted: function () {
            this.getList();
        },
    })
</script>