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

    .goods-images {
        width: 50px;
        height: 50px;
        margin-right: 10px;
    }

    .add-button {
        cursor: pointer;
        color: #409EFF;
    }

    .price {
        color: #ff4544
    }

    .table-box {
        margin-top: 10px;
        border: 1px solid #EBEEF5;
        max-height: 500px;
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
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'mall/live/index'})">
                        直播间管理
                    </span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>编辑直播商品</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="130px">
                <el-form-item label="添加直播商品" prop="goods_list">
                    <div><el-button @click="openDiloag" size="small" plain>选择商品</el-button></div>
                    <div v-if="!list.length" class="add-button" @click="$navigate({r: 'mall/live/goods-edit'})">直播商品还未添加商品?点击前往
                    </div>
                    <template v-if="ruleForm.goods_list.length">
                        <div flex="dir:left" class="table-box">
                        <el-table
                            max-height="500"
                            v-loading="listLoading"
                            :data="ruleForm.goods_list"
                            ref="multipleTable"
                            @selection-change="goodsListSelection">
                            <el-table-column :selectable="selectInit" type="selection" width="55"></el-table-column>
                            <el-table-column prop="name" label="名称" >
                                <template slot-scope="scope">
                                    <div flex="dir:left cross:center">
                                        <img :src="scope.row.coverImgUrl" class="goods-images">
                                        <div flex="dir:top">
                                            <div><app-ellipsis :line="1">{{scope.row.name}}</app-ellipsis></div>
                                            <template>
                                                <div v-if="scope.row.price_type == 1" class="price">￥{{scope.row.price}}</div>
                                                <div v-else-if="scope.row.price_type == 2" class="price">
                                                    <span>￥{{scope.row.price}} ——</span>
                                                    <span>{{scope.row.price2}}</span>
                                                </div>
                                                <div v-else-if="scope.row.price_type == 3" class="price">
                                                    <span><del>￥{{scope.row.price}}</del></span>
                                                    <span>{{scope.row.price2}}</span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </el-table-column>
                            <el-table-column
                                width="60"
                                label="操作">
                                <template slot-scope="scope">
                                        <el-button v-if="scope.row.goods_id" @click="deleteGoods(scope.$index)" type="text" circle size="mini">
                                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                                <img src="statics/img/mall/del.png" alt="">
                                            </el-tooltip>
                                        </el-button>
                                </template>
                            </el-table-column>
                        </el-table>
                    </div>
                    <div flex="dir:left box:last" style="margin-top: 10px;">
                        <div><el-button type="primary" size="small" @click="batchDelete()">批量删除</el-button></div>
                        <div v-if="multipleSelection2.length">已选 {{multipleSelection2.length}}</div>
                    </div>
                    </template>
                </el-form-item>
            </el-form>
        </div>
        <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">    保存
        </el-button>

        <el-dialog title="选择商品" :visible.sync="dialogTableVisible">
          <el-table
            v-loading="listLoading"
            :data="list"
            ref="multipleTable"
            @selection-change="handleSelectionChange">
            <el-table-column type="selection" width="55"></el-table-column>
            <el-table-column property="goodsId" label="ID" width="150"></el-table-column>
            <el-table-column property="name" label="名称" >
                <template slot-scope="scope">
                    <div flex="dir:left cross:center">
                        <img :src="scope.row.coverImgUrl" class="goods-images">
                        <div>{{scope.row.name}}</div>
                    </div>
                </template>
            </el-table-column>
          </el-table>
          <div slot="footer" class="dialog-footer">
            <div flex="dir:left box:last">
                <div style="text-align: left;">
                    <el-pagination
                    @current-change="pagination"
                    background
                    layout="prev, pager, next, jumper"
                    :page-count="pageCount">
                </el-pagination>
                </div>
                <el-button type="primary" @click="selectGoods()" size="small">选择</el-button>
            </div>
          </div>
        </el-dialog>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                ruleForm: {
                    room_id: getQuery('room_id'),
                    goods_list: []
                },
                multipleSelection2: [],
                rules: {

                },
                btnLoading: false,
                cardLoading: false,
                // 弹框参数
                dialogTableVisible: false,
                list: [],
                listLoading: false,
                page: 1,
                pageCount: 0,
                multipleSelection: [],
            };
        },
        methods: {
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        if (!self.ruleForm.goods_list.length) {
                            self.$message.warning('请先选择商品');
                            return false;
                        }

                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/live/add-goods'
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
                                    r: 'mall/live/index',
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
            getGoodsList(){
                let self = this;
                request({
                    params: {
                        r: 'mall/live/goods',
                        page: self.page,
                        status: 2,
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        self.list = e.data.data.list;
                        self.pageCount = e.data.data.pageCount;
                    } else {
                        self.$message.error(e.data.msg)
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            openDiloag() {
                this.dialogTableVisible = true;
            },
            selectGoods() {
                let self = this;
                self.dialogTableVisible = false;
                self.multipleSelection.forEach(function(item, index) {
                    let sign = true;
                    self.ruleForm.goods_list.forEach(function(item2, index2) {
                        if (item.goodsId == item2.goods_id) {
                            sign = false;
                        }
                    })

                    if (sign) {
                        self.ruleForm.goods_list.unshift({
                            name: item.name,
                            cover_img: item.coverImgUrl,
                            price: item.price,
                            price2: item.price2,
                            price_type: item.priceType,
                            url: item.url,
                            goods_id: item.goodsId,
                        })
                    }
                })
            },
            goodsListSelection(val) {
                this.multipleSelection2 = val;
            },
            handleSelectionChange(val) {
                this.multipleSelection = val;
            },
            pagination(currentPage) {
                let self = this;
                self.page = currentPage;
                self.listLoading = true;
                self.getGoodsList();
            },
            selectInit(row, index) {
                if (row.goods_id) {
                    return true;
                } else {
                    return false;
                }
            },
            deleteGoods(index) {
                this.ruleForm.goods_list.splice(index,1);
                this.multipleSelection2 = [];
            },
            batchDelete() {
                let self = this;
                if (self.multipleSelection2.length == 0) {
                    self.$message.warning('请先选择要删除的商品');
                    return false;
                }
                self.multipleSelection2.forEach(function(item, index) {
                    self.ruleForm.goods_list.forEach(function(item2, index2) {
                        if (item.goods_id && item.goods_id == item2.goods_id) {
                            self.deleteGoods(index2);
                        }
                    })
                })
            }
        },
        mounted() {
            let data = localStorage.getItem('LIVE_GOODS_LIST');
            let goodsList = data ? JSON.parse(data) : [];
            this.ruleForm.goods_list = goodsList;
            this.cardLoading = true;

            this.getGoodsList();
        }
    });
</script>
