<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>

<style>
    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 45%;
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

    .list-box {
        width: 100%;
        border: 1px solid #e2e2e2;
    }
    .list-box .item-box {
        border: 1px solid #e2e2e2;
        padding: 10px;
        cursor: pointer;
    }
    .list-box .active {
        background-color: #e2e2e2;
    }
    .click-img {
        width: 100%;
    }
</style>
<div id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;"
             v-loading="loading">
        <div slot="header">
            <div>
                <span></span>
            </div>
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer"
                  @click="$navigate({r:'mall/city-service/index'})">即时配送商家</span>
              </el-breadcrumb-item>
                <el-breadcrumb-item>配送商家{{city_service_id ? '编辑' : '添加'}}</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="150px">
                <el-form-item label="配送名称" prop="name">
                    <el-input v-model="ruleForm.name" placeholder="请输入配送名称"></el-input>
                </el-form-item>
                <el-form-item label="选择配送公司" prop="distribution_corporation">
                    <div class="list-box" flex="box:mean">
                        <div class="item-box"
                            @click="distributionChange(item)"
                            :class="{'active': item.value == ruleForm.distribution_corporation}"
                            v-for="(item, index) in corporation_list"
                            :key="index"
                            flex="dir:top cross:center main:center">
                                <img :src="item.icon">
                                <div>{{item.name}}</div>
                        </div>
                    </div>
                </el-form-item>
                <!-- 达达 -->
                <el-form-item v-if="ruleForm.distribution_corporation == 4" label="商户ID" prop="shop_id">
                    <el-input v-model="ruleForm.shop_id" placeholder="请输入商户ID"></el-input>
                </el-form-item>
                <el-form-item v-if="ruleForm.distribution_corporation != 3 && ruleForm.service_type == '第三方'" label="物品类目" prop="product_type">
                    <el-select v-model="ruleForm.product_type" filterable placeholder="请选择">
                    <el-option
                      v-for="item in product_list"
                      :key="item.value"
                      :label="item.label"
                      :value="item.value">
                    </el-option>
                  </el-select>
                </el-form-item>
                <el-form-item v-if="ruleForm.service_type == '微信'" label="物品类目" prop="wx_product_type">
                    <el-cascader
                    :options="wx_product_list"
                    v-model="ruleForm.wx_product_type">
                  </el-cascader>
                </el-form-item>

                <el-form-item label="Appkey" prop="appkey">
                    <el-input v-model="ruleForm.appkey" placeholder="请输入appkey"></el-input>
                </el-form-item>
                <el-form-item label="AppSecret" prop="appsecret">
                    <el-input v-model="ruleForm.appsecret" placeholder="请输入appsecret"></el-input>
                </el-form-item>
                <el-form-item label="商家门店编号" prop="shop_no">
                    <el-input v-model="ruleForm.shop_no" placeholder="请输入商家门店编号"></el-input>
                </el-form-item>
                <el-form-item label="使用第三方平台接口">
                    <div flex="dir:left cross:center">
                        <div>
                            <el-radio v-model="ruleForm.service_type" label="第三方">配送公司自带接口</el-radio>
                        </div>
                        <div flex="dir:top" style="position: relative;margin-left: 30px;">
                            <el-radio v-model="ruleForm.service_type" label="微信">腾讯即时配送接口</el-radio>
                            <span style="color: #409EFF;cursor: pointer;position: absolute;top: 20px;left: 24px;" @click="dialogImg = true">查看图例</span>
                        </div>
                    </div>
                </el-form-item>
            </el-form>
        </div>
        <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">保存
        </el-button>
    </el-card>

    <!-- 查看图例 -->
        <el-dialog :visible.sync="dialogImg" width="65%" class="open-img">
            <div style="padding-bottom: 20px; font-size: 20px;">查看腾讯即时配送接口图例</div>
            <img src="statics/img/mall/city_service/example.png" class="click-img" alt="">
        </el-dialog>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                ruleForm: {
                    name: '',
                    distribution_corporation: 1,
                    appkey: '',
                    appsecret: '',
                    shop_no: '',
                    service_type: '第三方',
                    shop_id: '',
                    product_type: '',
                    wx_product_type: ''
                },
                rules: {
                    name: [
                        {required: true, message: '请输入配送名称', trigger: 'change'},
                    ],
                    distribution_corporation: [
                        {required: true, message: '请选择配送公司', trigger: 'change'},
                    ],
                    appkey: [
                        {required: true, message: '请输入Appkey', trigger: 'change'},
                    ],
                    appsecret: [
                        {required: true, message: '请输入AppSecret', trigger: 'change'},
                    ],
                    shop_no: [
                        {required: true, message: '请输入商家门店编号', trigger: 'change'},
                    ],
                    shop_id: [
                        {required: true, message: '请输入商户ID', trigger: 'change'},
                    ],
                    product_type: [
                        {required: true, message: '物品类目', trigger: 'change'},
                    ],
                    wx_product_type: [
                        {required: true, message: '物品类目', trigger: 'change'},
                    ],
                },
                btnLoading: false,
                loading: false,
                corporation_list: [],
                city_service_id: null,
                // 查看图例
                dialogImg: false,
                product_list:[],
                sf_product_list: [
                    {value: 1, label: '快餐'},
                    {value: 2, label: '送药'},
                    {value: 3, label: '百货'},
                    {value: 4, label: '脏衣服收'},
                    {value: 5, label: '干净衣服派'},
                    {value: 6, label: '生鲜'},
                    {value: 7, label: '保单'},
                    {value: 8, label: '高端饮品'},
                    {value: 9, label: '现场勘验'},
                    {value: 10, label: '快递'},
                    {value: 12, label: '文件'},
                    {value: 13, label: '蛋糕'},
                    {value: 14, label: '鲜花'},
                    {value: 15, label: '电子数码'},
                    {value: 16, label: '服装鞋帽'},
                    {value: 17, label: '汽车配件'},
                    {value: 18, label: '珠宝'},
                    {value: 20, label: '披萨'},
                    {value: 21, label: '中餐'},
                    {value: 22, label: '水产'},
                    {value: 27, label: '专人直送'},
                    {value: 32, label: '中端饮品'},
                    {value: 33, label: '便利店'},
                    {value: 34, label: '面包糕点'},
                    {value: 35, label: '火锅'},
                    {value: 36, label: '证照'},
                    {value: 99, label: '其他'},
                ],
                dada_product_list: [
                    {value: 1, label: '食品小吃'},
                    {value: 2, label: '饮料'},
                    {value: 3, label: '鲜花'},
                    {value: 8, label: '文印票务'},
                    {value: 9, label: '便利店'},
                    {value: 13, label: '水果生鲜'},
                    {value: 19, label: '同城电商'},
                    {value: 20, label: '医药'},
                    {value: 21, label: '蛋糕'},
                    {value: 24, label: '酒品'},
                    {value: 25, label: '小商品市场'},
                    {value: 26, label: '服装'},
                    {value: 27, label: '汽修零配'},
                    {value: 28, label: '数码'},
                    {value: 29, label: '小龙虾'},
                    {value: 51, label: '火锅'},
                    {value: 5, label: '其他'},
                ],
                ss_product_list: [
                    {value: 1, label: '文件广告'},
                    {value: 3, label: '电子产品'},
                    {value: 5, label: '蛋糕'},
                    {value: 6, label: '快餐水果'},
                    {value: 7, label: '鲜花绿植'},
                    {value: 8, label: '海鲜水产'},
                    {value: 9, label: '汽车配件'},
                    {value: 10, label: '其他'},
                    {value: 11, label: '宠物'},
                    {value: 12, label: '母婴'},
                    {value: 13, label: '医药健康'},
                    {value: 14, label: '教育'},
                ],
                wx_product_list: [
                    {
                      value: '美食夜宵',
                      label: '美食夜宵',
                      children: [
                          {
                            value: '零食小吃',
                            label: '零食小吃',
                          },{
                            value: '香锅/烤鱼',
                            label: '香锅/烤鱼',
                          },{
                            value: '西餐',
                            label: '西餐',
                          },{
                            value: '日韩料理',
                            label: '日韩料理',
                          },{
                            value: '海鲜/烧烤',
                            label: '海鲜/烧烤',
                          },{
                            value: '快餐/地方菜',
                            label: '快餐/地方菜',
                          },{
                            value: '小龙虾',
                            label: '小龙虾',
                          },{
                            value: '披萨',
                            label: '披萨',
                          },
                      ]
                    },
                    {
                      value: '甜品饮料',
                      label: '甜品饮料',
                      children: [
                          {
                            value: '甜品',
                            label: '甜品',
                          },{
                            value: '奶茶果汁',
                            label: '奶茶果汁',
                          },{
                            value: '咖啡',
                            label: '咖啡',
                          },{
                            value: '面包/糕点',
                            label: '面包/糕点',
                          },{
                            value: '冰淇淋',
                            label: '冰淇淋',
                          }
                      ]
                    },
                    {
                      value: '蛋糕',
                      label: '蛋糕',
                      children: [
                          {
                            value: '蛋糕',
                            label: '蛋糕',
                          },
                      ]
                    },
                    {
                      value: '日用百货',
                      label: '日用百货',
                      children: [
                          {
                            value: '便利店',
                            label: '便利店',
                          },
                          {
                            value: '水站/奶站',
                            label: '水站/奶站',
                          },
                          {
                            value: '零食/干果',
                            label: '零食/干果',
                          },
                          {
                            value: '五金日用',
                            label: '五金日用',
                          },
                          {
                            value: '粮油调味',
                            label: '粮油调味',
                          },
                          {
                            value: '文具店',
                            label: '文具店',
                          },
                          {
                            value: '酒水行',
                            label: '酒水行',
                          },
                          {
                            value: '地方特产',
                            label: '地方特产',
                          },
                          {
                            value: '进口食品',
                            label: '进口食品',
                          },
                          {
                            value: '宠物用品',
                            label: '宠物用品',
                          },
                          {
                            value: '超市',
                            label: '超市',
                          },
                          {
                            value: '书店',
                            label: '书店',
                          },
                          {
                            value: '宠物食品用品',
                            label: '宠物食品用品',
                          },
                          {
                            value: '办公家居用品',
                            label: '办公家居用品',
                          },
                      ]
                    },
                    {
                      value: '果蔬生鲜',
                      label: '果蔬生鲜',
                      children: [
                          {
                            value: '果蔬',
                            label: '果蔬',
                          },{
                            value: '海鲜水产',
                            label: '海鲜水产',
                          },{
                            value: '冷冻速食',
                            label: '冷冻速食',
                          },
                      ]
                    },
                    {
                      value: '鲜花',
                      label: '鲜花',
                      children: [
                          {
                            value: '鲜花',
                            label: '鲜花',
                          },
                      ]
                    },
                    {
                      value: '医药健康',
                      label: '医药健康',
                      children: [
                          {
                            value: '送药',
                            label: '送药',
                          },
                          {
                            value: '器材器具',
                            label: '器材器具',
                          },
                      ]
                    },
                    {
                      value: '美妆护肤',
                      label: '美妆护肤',
                      children: [
                          {
                            value: '日化美妆',
                            label: '日化美妆',
                          },
                      ]
                    },
                    {
                      value: '母婴',
                      label: '母婴',
                      children: [
                          {
                            value: '孕婴用品',
                            label: '孕婴用品',
                          },
                      ]
                    },
                    {
                      value: '文件或票务',
                      label: '文件或票务',
                      children: [
                          {
                            value: '保单',
                            label: '保单',
                          },
                          {
                            value: '票务文件',
                            label: '票务文件',
                          },
                          {
                            value: '政府文件',
                            label: '政府文件',
                          },
                          {
                            value: '证件',
                            label: '证件',
                          },
                      ]
                    },
                    {
                      value: '服饰鞋帽',
                      label: '服饰鞋帽',
                      children: [
                          {
                            value: '服饰鞋帽综合',
                            label: '服饰鞋帽综合',
                          },
                      ]
                    },
                    {
                      value: '洗涤',
                      label: '洗涤',
                      children: [
                          {
                            value: '脏衣服收',
                            label: '脏衣服收',
                          },
                          {
                            value: '干净衣服派',
                            label: '干净衣服派',
                          },
                      ]
                    },
                    {
                      value: '珠宝奢侈品',
                      label: '珠宝奢侈品',
                      children: [
                          {
                            value: '珠宝饰品',
                            label: '珠宝饰品',
                          },
                          {
                            value: '奢侈品',
                            label: '奢侈品',
                          },
                      ]
                    },
                    {
                      value: '家居家装',
                      label: '家居家装',
                      children: [
                          {
                            value: '家具',
                            label: '家具',
                          },
                          {
                            value: '装修建材',
                            label: '装修建材',
                          },
                          {
                            value: '厨房卫浴',
                            label: '厨房卫浴',
                          },
                      ]
                    },
                    {
                      value: '数码产品',
                      label: '数码产品',
                      children: [
                          {
                            value: '数码产品',
                            label: '数码产品',
                          },
                      ]
                    },
                    {
                      value: '配件器材',
                      label: '配件器材',
                      children: [
                          {
                            value: '配件器材',
                            label: '配件器材',
                          },
                      ]
                    },
                    {
                      value: '电商',
                      label: '电商',
                      children: [
                          {
                            value: '电视购物',
                            label: '电视购物',
                          },
                          {
                            value: '线上商城',
                            label: '线上商城',
                          },
                      ]
                    },
                    {
                      value: '现场勘查',
                      label: '现场勘查',
                      children: [
                          {
                            value: '现场勘查',
                            label: '现场勘查',
                          },
                      ]
                    },
                    {
                      value: '快递业务',
                      label: '快递业务',
                      children: [
                          {
                            value: '快递配送',
                            label: '快递配送',
                          },
                      ]
                    },
                    {
                      value: '其他',
                      label: '其他',
                      children: [
                          {
                            value: '其他',
                            label: '其他',
                          },
                      ]
                    },
                ]
            };
        },
        methods: {
            store(formName) {
                let self = this;
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/city-service/edit'
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
                                    r: 'mall/city-service/index'
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
            getDetail() {
                let self = this;
                self.loading = true;
                request({
                    params: {
                        r: 'mall/city-service/edit',
                        id: getQuery('id')
                    },
                    method: 'get',
                }).then(e => {
                    self.loading = false;
                    if (e.data.code == 0) {
                        self.ruleForm = e.data.data.city_service;
                        this.checkDistribution()
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            getOption() {
                let self = this;
                request({
                    params: {
                        r: 'mall/city-service/option',
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code == 0) {
                        self.corporation_list = e.data.data.corporation_list;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            distributionChange(item) {
                if (this.ruleForm.distribution_corporation != item.value) {
                    this.ruleForm.product_type = '';
                }

                this.ruleForm.distribution_corporation = item.value
                this.checkDistribution()
            },
            checkDistribution() {
                if (this.ruleForm.distribution_corporation == 1) {
                    this.product_list = this.sf_product_list;
                }
                if (this.ruleForm.distribution_corporation == 2) {
                    this.product_list = this.ss_product_list;
                }
                if (this.ruleForm.distribution_corporation == 4) {
                    this.product_list = this.dada_product_list;
                }
            }
        },
        mounted: function () {
            this.getOption();
            if (getQuery('id')) {
                this.city_service_id = getQuery('id');
                this.getDetail();
            }
        }
    });
</script>
