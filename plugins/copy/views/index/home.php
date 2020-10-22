<?php
/**
 * link: 域名
 * copyright: Copyright (c) 2018 人人禾匠商城
 * author: wxf
 */

?>

<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        width: 250px;
        margin: 0 0 20px;
    }

    .input-item .el-input__inner {
        border-right: 0;
    }

    .input-item .el-input__inner:hover {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input__inner:focus {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input-group__append {
        background-color: #fff;
        border-left: 0;
        width: 10%;
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .table-body .el-table .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }

    .el-form-item {
        margin-bottom: 0;
    }

    .sort-input {
        width: 100%;
        background-color: #F3F5F6;
        height: 32px;
    }

    .sort-input span {
        height: 32px;
        width: 100%;
        line-height: 32px;
        display: inline-block;
        padding: 0 10px;
        font-size: 13px;
    }

    .sort-input .el-input__inner {
        height: 32px;
        line-height: 32px;
        background-color: #F3F5F6;
        float: left;
        padding: 0 10px;
        border: 0;
    }
    .store-item{
        padding-left: 20px;
        height:35px;
        line-height: 35px;
    }
    .store-item:hover{
        background-color: #e4f1ff;
        border-right:4px solid #409EFF;
    }
    .store-active{
        background-color: #e4f1ff;
        border-right:4px solid #409EFF;
    }
    .store-card .el-card__body{
        padding: 20px 0px;
    }
</style>
<div id="app" v-cloak>
    <el-row :gutter="6">
        <!--        门店列表-->
        <el-col :span="4" style="min-width:300px;" v-loading="storeListLoading">
            <el-card class="store-card">
                <div slot="header">
                    <span>门店列表</span>
                    <el-button style="float: right; padding: 3px 0" type="text" @click="dialogVisible = true">添加门店
                    </el-button>
                </div>
                <div class="store-content" :style="{height: height + 'px',overflow:'auto'}">

                    <div class="store-item"
                         :class="[ store_id == store.id ? 'store-active':'' ]"
                         v-for="store in storeList"
                         @click="hanldSelectStore(store.id)"
                         :key="store.id">

                        <el-row type="flex" justify="space-between">
                            <div>{{store.name}}</div>
                            <el-button  type="text" @click="hanldDelStore(store.id)" style="padding:7px 10px;">删除 </el-button>
                        </el-row>
                    </div>
                </div>


            </el-card>
        </el-col>

        <!--        门店分类列表-->
<!--        <el-col :span="4" style="min-width:300px;" v-loading="storeCatLoading">-->
<!--            <el-card class="store-card">-->
<!--                <div slot="header">-->
<!--                    <span>商品分类</span>-->
<!--                </div>-->
<!--                <div class="store-content" :style="{height: height + 'px',overflow:'auto'}">-->
<!---->
<!--                    <div class="store-item"-->
<!--                         :class="[ cat_id == cat.cat_id ? 'store-active':'' ]"-->
<!--                         v-for="cat in catList"-->
<!--                         @click="hanldSelectCat(cat.cat_id)"-->
<!--                         :key="cat.id">-->
<!---->
<!--                        <el-row type="flex" justify="space-between">-->
<!--                            <div>{{cat.name}}</div>-->
<!--                        </el-row>-->
<!--                    </div>-->
<!--                </div>-->
<!---->
<!---->
<!--            </el-card>-->
<!--        </el-col>-->

        <!--        商品列表-->
        <el-col :span="15" v-loading="storeGoodsLoading" >
            <el-card class="store-card" >
                <div slot="header">
                    <span>首页模板</span>
                </div>
                <el-row style="padding:20px 20px;">

                </el-row>

                <div class="store-content" :style="{height: height-80 + 'px',overflow:'auto'}" >
                    <el-table :data="goodsList" border style="width: 100%;margin-bottom: 15px"
                              @selection-change="handleSelectionChange"
                    >
                        <el-table-column prop="id" label="ID" width="80"></el-table-column>

                        <el-table-column label="模板">
                            <template slot-scope="scope">
                                <div flex="box:first">
                                    <div>
                                        <!-- <el-tag size="mini" type="success">{{scope.row.plugin_name}}</el-tag> -->
                                        <app-ellipsis :line="1">{{scope.row.title}}</app-ellipsis>
                                    </div>
                                </div>
                            </template>
                        </el-table-column>



                        <el-table-column label="操作">
                            <template slot-scope="scope">
                                <el-button @click="handlRowCopyGoods(scope.row.id)" type="text" circle size="mini">
                                    导入
                                </el-button>

                            </template>
                        </el-table-column>

                    </el-table>

                </div>

                <el-row style="height: 40px;line-height: 40px;text-align: center;">共{{goodsList.length ? goodsList.length:0}}个商品</el-row>
            </el-card>
        </el-col>
    </el-row>


    <el-dialog
            title="添加门店"
            :visible.sync="dialogVisible"
            width="30%"
            >
        <el-form ref="storeform" :model="storeForm" :rules="storeRules" label-width="80px">

            <el-form-item prop="name" label="门店名称" style="padding:20px 2px;">
                <el-input v-model="storeForm.name"></el-input>
            </el-form-item>

            <el-form-item prop="url" label="门店url" style="padding:20px 2px;">
                <el-input v-model="storeForm.url"></el-input>
                <span>例如独立版：https://api.xxxx.com</span></br>
                <span>v3微擎版：https://api.xxxx.com/addons/zjhj_mall/core</span></br>
                <span>v4微擎版：https://api.xxxx.com/addons/zjhj_bd</span>
            </el-form-item>
            <el-form-item  label="版本" style="padding:20px 2px;">
                <el-radio-group v-model="storeForm.ver">
<!--                    <el-radio :label="3">v3</el-radio>-->
                    <el-radio :label="4">v4</el-radio>
                </el-radio-group>
            </el-form-item>

            <el-form-item label="门店id" prop="store_id" style="padding:20px 2px;">
                <el-input type="number" v-model="storeForm.store_id"></el-input>
            </el-form-item>
            <el-form-item  style="padding:20px 2px;">
                <el-button type="primary" @click="onStoreSubmit('storeform')">添加</el-button>
                <el-button @click="dialogVisible = false">取消</el-button>
            </el-form-item>
        </el-form>

    </el-dialog>

</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                height: document.documentElement.clientHeight - 200, // 表的高度
                //门店
                dialogVisible: false,
                storeListLoading:false,
                storeList:[],
                storeForm:{
                    name:'',
                    store_id:'',
                    url:'',
                    ver:4,
                },
                store_id:2,
                storeRules: {
                    name: [
                        {required: true, message: '请输入门店名称', trigger: 'blur'}
                    ],
                    store_id: [
                        {required: true, message: '门店id不能为空', trigger: 'blur'}
                    ],
                    url: [
                        {required: true, message: 'url不能为空', trigger: 'blur'}
                    ],
                },
                // 分类
                storeCatLoading:false,
                catList:[],
                cat_id:'',
                //商品
                goodsList:[],
                store_cat_list:[],
                store_cat_id:'',//自己门店分类id
                selectVal:'',
                storeGoodsLoading:false,
                status:'0',
                statsList:[
                    {
                        value: '0',
                        label: '全部'
                    },
                    {
                        value: '1',
                        label: '未导入'
                    },
                    {
                        value: '2',
                        label: '已导入'
                    }
                ]

            };
        },
        created() {
            this.getList()
            this.getCatList()
        },
        methods: {
            onStoreSubmit(formName) {
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        this.submitAddStore();
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
            hanldSelectStore(store_id){
                this.store_id = store_id;
                this.getStoreGoodsList();
            },
            hanldStatusChange(e){
                console.log(e)
            },
            submitAddStore() {
                let self = this;
                request({
                    params: {
                        r: 'plugin/copy/mall/index/store-add',
                    },
                    method: 'post',
                    data: {
                        form: self.storeForm,
                    }
                }).then(e => {

                    self.getList();
                    self.dialogVisible = false
                    self.$message({
                        message:  e.data.msg,
                        type: 'success'
                    });
                }).catch(e => {
                    console.log(e);
                });
            },
            hanldDelStore(id){
                let self = this;
                request({
                    params: {
                        r: 'plugin/copy/mall/index/store-del',
                        id:id
                    },
                    method: 'get',
                }).then(e => {
                    self.getList();
                    self.$message({
                        message:  e.data.msg,
                        type: 'success'
                    });
                }).catch(e => {
                    console.log(e);
                });
            },
            getList() {
                let self = this;
                self.storeListLoading = true;
                request({
                    params: {
                        r: 'plugin/copy/mall/index/store-list',
                    },
                    method: 'get',
                }).then(e => {
                    self.storeListLoading = false;
                    self.storeList = e.data.data.list;
                }).catch(e => {
                    console.log(e);
                });
            },
            //分类
            hanldSelectCat(cat_id){
                this.cat_id = cat_id;
                this.getStoreGoodsList();
            },
            //获取复制小程序的分类
            getStoreCatList() {
                let self = this;
                self.storeCatLoading = true;
                request({
                    params: {
                        r: 'plugin/copy/mall/index/cat-list',
                        store_id:self.store_id
                    },
                    method: 'get',
                }).then(e => {
                    self.storeCatLoading = false;
                    self.catList = e.data.data.list;
                    self.$message({
                        message:  e.data.msg,
                        type: 'success'
                    });
                }).catch(e => {
                    console.log(e);
                });
            },
            //商品
            getStoreGoodsList() {
                let self = this;
                self.storeGoodsLoading = true;
                request({
                    params: {
                        r: 'plugin/copy/mall/index/home-template',
                        store_id:self.store_id,
                        cat_id:1,
                        status:self.status
                    },
                    method: 'get',
                }).then(e => {
                    self.storeGoodsLoading = false;
                    self.goodsList = e.data.data.list;
                    self.$message({
                        message:  e.data.msg,
                        type: 'success'
                    });
                }).catch(e => {
                    console.log(e);
                });
            },
            //获取自己小程序分类
            getCatList() {
                let self = this;
                request({
                    params: {
                        r: 'plugin/copy/mall/index/store-cat',
                    },
                    method: 'get',
                }).then(e => {
                    self.store_cat_list = e.data.data.list;

                }).catch(e => {
                    console.log(e);
                });
            },
            handleSelectionChange(val){
                this.selectVal = val
                console.log(val)
            },
            handlRowCopyGoods(id){
                this.copyGoods([id])
            },
            handlCopyGoods(){
                let list =this.selectVal,ids = [];

                for(j = 0,len=list.length; j < len; j++) {
                    ids.push(list[j]['goods_id'])
                }
                this.copyGoods(ids)
            },
            copyGoods(ids){
                let self = this;

                self.storeGoodsLoading = true;
                request({
                    params: {
                        r: 'plugin/copy/mall/index/copy-template',
                    },
                    method: 'post',
                    data: {
                        store_id:self.store_id,
                    }
                }).then(e => {
                    self.storeGoodsLoading = false
                    if(e.data.code ==1 ){

                        this.$alert('导入失败', '提示', {
                            confirmButtonText: '确定'
                        });

                    }else{
                        this.$alert('导入成功', '提示', {
                            confirmButtonText: '确定',
                            callback: action => {

                            }
                        });
                    }

                }).catch(e => {
                    console.log(e);
                });
            },
            filterTag(value, row) {
                return row.is_copy === value;
            },
        }
    });
</script>
