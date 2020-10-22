<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
$mchId = Yii::$app->user->identity->mch_id;
?>

<style>
    .app-style .table-body {
        padding: 0;
        background-color: #fff;
    }

    .app-style .el-card__body {
        padding: 0;
    }

    .app-style .cat-style .text {
        cursor: pointer;
        background: #409eff;
        width: 90px;
        color: #fff;
    }

    .new-table-body {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 0;
    }

    .app-style .cat-style-model {
        background-color: rgba(0, 0, 0, 0.5);
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 100%;
        -webkit-border-radius: 25px;
        -moz-border-radius: 25px;
        border-radius: 25px;
    }

    .app-style .cat-style-nmodel {
        background-color: transparent;
    }

    .app-style .button-item {
        padding: 9px 25px;
        margin-top: 20px;
    }

    .app-style .image-box {
        width: 340px;
        border: 1px solid #f2f4f5;
        cursor: pointer;
        border-radius: 25px;
        margin-right: 5px;
        margin-bottom: 5px;
        padding: 30px 0;
        position: relative;
    }

    .app-style .image-box .active {
        position: absolute;
        top: 0;
        left: 0;
        z-index: 99;
    }

    .app-style .image-box .text {
        cursor: pointer;
        background: #409eff;
        text-align: center;
        position: absolute;
        top: 245px;
        left: auto;
        line-height: 30px;
        width: 90px;
        color: #fff;
    }

    .app-style .tab-box {
        margin-bottom: 20px;
    }

    .app-style .tab-box .btn {
        cursor: pointer;
        padding: 7px 15px;
        margin-right: 10px;
        border: 1px solid #E3E3E3;
        -webkit-border-radius: 4px;
        -moz-border-radius: 4px;
        border-radius: 4px;
    }
    .app-style .tab-box .active {
        background: #409eff;
        color: #FFFFFF;
    }
</style>

<template id="app-style">
    <div class="app-style">
        <el-card v-loading="listLoading" shadow="never" style="border:0"
                 body-style="background-color: #f3f3f3;padding: 0 0;">
            <el-form :model="form" :rules="rules" ref="form" label-width="200px" size="small">
                <el-card shadow="never" style="border:0;">
                    <el-row class="table-body">
                        <div class="tab-box" flex="dir:left">
                            <div :class="{'active': activeName == 'one' ? true : false}" class="btn" @click="handleClick('one')">一级分类样式</div>
                            <div :class="{'active': activeName == 'two' ? true : false}" class="btn" @click="handleClick('two')">二级分类样式</div>
                            <div :class="{'active': activeName == 'three' ? true : false}" class="btn" @click="handleClick('three')">三级分类样式</div>
                        </div>
                        <div v-if="activeName == 'one'">
                            <div flex="wrap:wrap">
                                <div v-for="item in catList[0]"
                                     @mouseenter="enter(item.value)"
                                     @mouseleave="leave(item.value)">
                                    <div class="image-box" flex="main:center"
                                         :style="{'border': item.value == form.cat_style ? '1px solid #409eff' : '1px solid #F2F4F5'}">
                                        <div>
                                            <div :class="item.value == selectModel ? 'cat-style-model': 'cat-style-nmodel'"></div>
                                            <img :src="item.pic_url">
                                        </div>
                                        <app-image class="active"
                                                   v-if="item.value == form.cat_style"
                                                   width="80px"
                                                   height="80px"
                                                   :src="cat_select">
                                        </app-image>
                                        <div v-if="item.value == selectModel" @click="setStyle(item.value)"
                                             class="text">
                                            启用该样式
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="activeName == 'two'">
                            <div flex="wrap:wrap">
                                <div v-for="item in catList[1]"
                                     flex="main:center"
                                     @mouseenter="enter(item.value)"
                                     @mouseleave="leave(item.value)">
                                    <div class="image-box" flex="main:center"
                                         :style="{'border': item.value == form.cat_style ? '1px solid #409eff' : '1px solid #F2F4F5'}">
                                        <div>
                                            <div :class="item.value == selectModel ? 'cat-style-model': 'cat-style-nmodel'"></div>
                                            <img :src="item.pic_url">
                                        </div>
                                        <app-image class="active"
                                                   v-if="item.value == form.cat_style"
                                                   width="80px"
                                                   height="80px"
                                                   :src="cat_select">
                                        </app-image>
                                        <div v-if="item.value == selectModel" @click="setStyle(item.value)"
                                             class="text">
                                            启用该样式
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="activeName == 'three'">
                            <div flex="wrap:wrap">
                                <div v-for="item in catList[2]"
                                     flex="main:center"
                                     @mouseenter="enter(item.value)"
                                     @mouseleave="leave(item.value)">
                                    <div class="image-box" flex="main:center"
                                         :style="{'border': item.value == form.cat_style ? '1px solid #409eff' : '1px solid #F2F4F5'}">
                                        <div>
                                            <div :class="item.value == selectModel ? 'cat-style-model': 'cat-style-nmodel'"></div>
                                            <img :src="item.pic_url">
                                        </div>
                                        <app-image class="active"
                                                   v-if="item.value == form.cat_style"
                                                   width="80px"
                                                   height="80px"
                                                   :src="cat_select">
                                        </app-image>
                                        <div v-if="item.value == selectModel" @click="setStyle(item.value)"
                                             class="text">
                                            启用该样式
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </el-row>
                </el-card>
                <el-card shadow="never" style="border:0;">
                    <template v-if="!is_mch">
                        <div slot="header">
                            <span>显示设置</span>
                        </div>
                        <el-row class="new-table-body">
                            <el-form-item label="" prop="cat_goods_count">
                                <template slot='label'>
                                    <span>每个分类商品显示总数</span>
                                    <el-tooltip effect="dark" content="每个分类板块显示的商品最大数(0~100)"
                                                placement="top">
                                        <i class="el-icon-info"></i>
                                    </el-tooltip>
                                </template>
                                <el-input v-model="form.cat_goods_count" style="width:30%" type="number">
                                    <template slot="append">个</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item prop="cat_goods_cols">
                                <template slot='label'>
                                    <span>商品每行显示数量</span>
                                    <el-tooltip effect="dark" content="默认首页显示"
                                                placement="top">
                                        <i class="el-icon-info"></i>
                                    </el-tooltip>
                                </template>
                                <el-select v-model="form.cat_goods_cols" placeholder="请选择">
                                    <el-option v-for="item in catGoodsCols" :key="item.value" :label="item.label"
                                               :value="item.value">
                                    </el-option>
                                </el-select>
                            </el-form-item>
                        </el-row>
                    </template>
                    <el-button class="button-item" :loading="btnLoading" size="small" type="primary" @click="onSubmit">
                        保存
                    </el-button>
                </el-card>
            </el-form>
        </el-card>
    </div>
</template>

<script>
    Vue.component('app-style', {
        template: '#app-style',
        data() {
            return {
                catList: [
                    [
                        {
                            value: 3,
                            pic_url: _baseUrl + '/statics/img/mall/cat/one-a.png',
                        },
                        {
                            value: 1,
                            pic_url: _baseUrl + '/statics/img/mall/cat/one-b.png',
                        },
                        {
                            value: 8,
                            pic_url: _baseUrl + '/statics/img/mall/cat/one-c.png',
                        },
                        {
                            value: 9,
                            pic_url: _baseUrl + '/statics/img/mall/cat/one-d.png',
                        }
                    ],
                    [
                        {
                            value: 2,
                            pic_url: _baseUrl + '/statics/img/mall/cat/two-a.png',
                        },
                        {
                            value: 4,
                            pic_url: _baseUrl + '/statics/img/mall/cat/two-c.png',
                        },
                        {
                            value: 5,
                            pic_url: _baseUrl + '/statics/img/mall/cat/two-b.png',
                        },
                        {
                            value: 10,
                            pic_url: _baseUrl + '/statics/img/mall/cat/two-d.png',
                        }
                    ],
                    [
                        {
                            value: 6,
                            pic_url: _baseUrl + '/statics/img/mall/cat/three-a.png',
                        },
                        {
                            value: 7,
                            pic_url: _baseUrl + '/statics/img/mall/cat/three-b.png',
                        },
                        {
                            value: 11,
                            pic_url: _baseUrl + '/statics/img/mall/cat/three-c.png',
                        }
                    ],
                ],
                mchCatList: [
                    [
                        {
                            value: 3,
                            pic_url: _baseUrl + '/statics/img/mall/cat/mch-one-a.png',
                        },
                        {
                            value: 1,
                            pic_url: _baseUrl + '/statics/img/mall/cat/mch-one-b.png',
                        },
                    ],
                    [
                        {
                            value: 2,
                            pic_url: _baseUrl + '/statics/img/mall/cat/mch-two-a.png',
                        },
                        {
                            value: 4,
                            pic_url: _baseUrl + '/statics/img/mall/cat/mch-two-c.png',
                        }
                    ],
                    [
                        {
                            value: 6,
                            pic_url: _baseUrl + '/statics/img/mall/cat/mch-three-a.png',
                        },
                        {
                            value: 7,
                            pic_url: _baseUrl + '/statics/img/mall/cat/mch-three-b.png',
                        },
                    ],
                ],
                cat_select: _baseUrl + '/statics/img/mall/cat/select.png',
                selectModel: 0,
                catGoodsCols: [{
                    label: '1',
                    value: 1
                },
                    {
                        label: '2',
                        value: 2
                    },
                    {
                        label: '3',
                        value: 3
                    },
                ],

                form: {},
                btnLoading: false,
                listLoading: false,
                activeName: 'one',
                selectSet: 0,

                rules: {
                    cat_goods_count: [
                        {required: true, message: '每个分类商品显示总数不能为空', trigger: 'change'},
                    ],
                    cat_goods_cols: [
                        {required: true, message: '商品每行显示数量不能为空', trigger: 'change'},
                    ]
                },
                is_mch: <?= $mchId > 0 ? 1 : 0 ?>,
            }
        },
        methods: {
            //样式切换
            setStyle(value) {
                this.form.cat_style = value;
                this.selectModel = 0;
            },
            enter(value) {
                if (this.form.cat_style == value) {
                    return;
                }
                this.selectModel = value;
            },
            leave(value) {
                if (this.form.cat_style == value) {
                    return;
                }
                if (this.selectModel == value) {
                }
            },

            handleClick(name) {
                this.activeName = name;
            },

            onSubmit() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        let para = Object.assign({}, this.form);
                        request({
                            params: {
                                r: 'mall/cat/style'
                            },
                            data: para,
                            method: 'post'
                        }).then(e => {
                            if (e.data.code === 0) {
                                this.$message.success(e.data.msg);
                                // navigateTo({r: 'mall/cat/index'});
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
            //获取列表
            getList() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'mall/cat/style'
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        if (e.data.data) {
                            this.form = e.data.data.setting;
                        }
                        let catStyle = this.form.cat_style;
                        if (catStyle == 2 || catStyle == 4 || catStyle == 5) {
                            this.activeName = 'two'
                        } else if (catStyle == 6 || catStyle == 7) {
                            this.activeName = 'three'
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
            if (this.is_mch) {
                this.catList = this.mchCatList;
            }
        }
    })
</script>