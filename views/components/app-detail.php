<?php
?>
<style>
    .detail-card {
        border: none;
    }
    .detail-card .form-body {
        padding: 10px 20px 20px;
        background-color: #fff;
        min-height: calc(100vh - 200px);
    }
    .detail-type {
        width: 208px;
        height: 74px;
        cursor: pointer;
        position: relative;
        margin-right: 15px;
    }
    .detail-type.active {
        position: absolute;
    }
    .detail-card .mt-24 {
        margin-top: 24px;
    }
</style>

<template id="app-detail">
    <el-card body-style="background-color: #f3f3f3;padding: 10px 0 0;"  class="detail-card">
        <el-row >
            <el-col :xs="24" :sm="24" :md="24">
                <el-form size="small"   :rules="cRule" class="form-body">
                    <el-tabs v-model="tableName" @tab-click="tabClick">
                        <el-tab-pane label="商品设置" name="first">
                            <!--选择商品类型-->
                            <el-card shadow="never">
                                <el-row slot="header">
                                    <el-col>选择商品类型</el-col>
                                </el-row>
                                <el-row>
                                    <el-col>
                                        <image class="detail-type" src="statics/img/mall/goods/good-types.png"></image>
                                        <image class="detail-type" src="statics/img/mall/goods/ecard-types.png"></image>
                                        <image class="detail-type active" :style="{left: type === 'goods' ? '0px' : '223px'}" src="statics/img/mall/goods/active-types.png"></image>
                                    </el-col>
                                </el-row>
                            </el-card>

                            <!--选择分类-->
                            <el-card class="mt-24" shadow="never">
                                <div slot="header">
                                    <span>选择分类</span>
                                </div>
                                <el-row>
                                    <el-col :xl="12" :lg="16">
                                        <el-form-item label="商品分类" prop="cats">
                                            <el-tag style="margin-right: 5px;margin-bottom:5px" v-for="(item,index) in cats"
                                                    :key="index" type="warning" closable disable-transitions
                                                    @close="destroyCat(item,index)"
                                            >{{item.label}}
                                            </el-tag>
                                            <el-button type="primary" @click="$refs.cats.openDialog()">选择分类</el-button>
                                            <el-button type="text" @click="$navigate({r:'mall/cat/edit'}, true)">添加分类
                                            </el-button>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-card>
                        </el-tab-pane>

                        <el-tab-pane label="分销价设置" name="two">

                        </el-tab-pane>

                        <el-tab-pane label="会员价设置" name="third">

                        </el-tab-pane>
                    </el-tabs>
                </el-form>
            </el-col>
        </el-row>
    </el-card>
</template>

<script>
    Vue.component('app-detail', {
        template: '#app-detail',
        props: {
        },
        data() {
            return {
                tableName: 'first',
                cats: []
            }
        },
        created() {},
        watch: {},
        computed: {
            cForm() {
            },
            cRule() {
            },
            type() {
                return 'goods';
            }
        },
        methods: {
            tabClick(tab, event) {},
            selectCat() {}
        }
    });
</script>
