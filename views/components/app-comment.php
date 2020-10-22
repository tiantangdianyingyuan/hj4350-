<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */
?>
<style>
    .app-comment-body {
        padding: 20px;
        background-color: #fff;
    }

    .app-comment-body .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }

    .app-comment-body .goods-info div{
        height: 50px;
        line-height: 50px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .app-comment-body .input-item {
        display: inline-block;
        width: 250px;
        margin: 0 0 20px;
    }
    
    .app-comment-body .input-item .el-input-group__prepend {
        background-color: #fff;
    }

    .app-comment-body .input-item .el-input__inner {
        border-right: 0;
    }

    .app-comment-body .input-item .el-input__inner:hover{
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .app-comment-body .input-item .el-input__inner:focus{
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }
    
    .app-comment-body .input-item .el-input-group__append {
        background-color: #fff;
        border-left: 0;
        width: 10%;
        padding: 0;
    }

    .app-comment-body .input-item .el-input-group__append .el-button {
        padding: 15px;
    }

    .app-comment-body .select {
        float: left;
        width: 100px;
        margin-right: 10px;
    }

    .app-comment-body .open-img .el-dialog {
        margin-top: 0 !important;
    }

    .app-comment-body .click-img {
        width: 100%;
    }
</style>
<template id="app-comment">
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>评价管理</span>
            <el-button style="float: right; margin: -5px 0" type="primary" size="small" @click="$navigate({r: edit_url})">添加客户评价</el-button>
        </div>
        <div class="app-comment-body">
            <el-select size="small" v-model="comment_type" class="select">
                <el-option key="1" label="全部评价" value="0"></el-option>
                <el-option key="2" label="好评" value="1"></el-option>
                <el-option key="3" label="中评" value="2"></el-option>
                <el-option key="4" label="差评" value="3"></el-option>
            </el-select>
            <div class="input-item">
                <el-input size="small" placeholder="请输入搜索内容" v-model="keyword">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table :data="form" border style="width: 100%" v-loading="listLoading">
                <el-table-column prop="id" label="ID" width="80"></el-table-column>
                <el-table-column prop="nickname" label="用户" width="180"></el-table-column>
                <el-table-column prop="platform" align="center" label="平台" width="80">
                    <template slot-scope="scope">
                        <el-tooltip class="item" effect="dark" v-if="scope.row.platform == 'wxapp'" content="微信" placement="top">
                            <img src="statics/img/mall/wx.png" alt="">
                        </el-tooltip>
                        <el-tooltip class="item" effect="dark" v-else-if="scope.row.platform == 'aliapp'" content="支付宝" placement="top">
                            <img src="statics/img/mall/ali.png" alt="">
                        </el-tooltip>
                        <el-tooltip class="item" effect="dark" v-else content="后台" placement="top">
                            <img src="statics/img/mall/site.png" alt="">
                        </el-tooltip>
                    </template>                    
                </el-table-column>
                <el-table-column prop="name" label="商品名称" width="300">
                    <template slot-scope="scope">
                        <div class="goods-info">
                            <app-image mode="aspectFill" style="margin-right: 10px;float: left" :src="scope.row.cover_pic"></app-image>
                            <div>
                                {{scope.row.goods ? scope.row.goods.name : scope.row.name}}
                            </div>   
                        </div>
                    </template>
                </el-table-column>
                <el-table-column align="center" prop="score" label="评分" width="80">
                    <template slot-scope="scope">
                        <el-tooltip class="item" effect="dark" v-if="scope.row.score==3" content="好评" placement="top">
                            <img src="statics/img/mall/good.png" alt="">
                        </el-tooltip>
                        <el-tooltip class="item" effect="dark" v-else-if="scope.row.score==2" content="中评" placement="top">
                            <img src="statics/img/mall/normal.png" alt="">
                        </el-tooltip>
                        <el-tooltip class="item" effect="dark" v-else-if="scope.row.score==1" content="差评" placement="top">
                            <img src="statics/img/mall/bad.png" alt="">
                        </el-tooltip>
                    </template>
                </el-table-column>
                <el-table-column prop="content" label="详情" width='300'>
                    <template slot-scope="scope">
                        <div v-text="scope.row.content"></div>
                        <div>
                            <div v-for="item in scope.row.pic_url" @click="openImg(item)" style="margin: 10px;display: inline-block;cursor: pointer" >
                                <app-image mode="aspectFill" :key="item.id" :src="item"></app-image>
                                
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="reply_content" label="评价回复"></el-table-column>
                <el-table-column prop="is_show" label="状态" width="80">
                    <template slot-scope="scope">
                        <el-switch active-value="1" inactive-value="0" @change="switchShow(scope.row)" v-model="scope.row.is_show"></el-switch>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="180">
                    <template slot-scope="scope">
                        <el-button size="small" type="text" circle @click="reply(scope.row.id)">
                            <el-tooltip class="item" effect="dark" content="评价回复" placement="top">
                                <img src="statics/img/mall/reply.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button size="small" type="text" v-if="scope.row.is_virtual == 1" @click="edit(scope.row.id)" circle>
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button size="small" type="text" @click="destroy(scope.row)" circle>
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div style="text-align: right;margin: 20px 0;">
                <el-pagination @current-change="pagination" background layout="prev, pager, next, jumper" :page-count="pageCount"></el-pagination>
            </div>
            <el-dialog :visible.sync="dialogImg" class="open-img">
                <img :src="click_img" class="click-img" alt="">
            </el-dialog>
        </div>
    </el-card>

</template>
<script>
Vue.component('app-comment', {
    template: '#app-comment',
    props: {
        sign: {
            type: String,
            default: ''
        },
        reply_url: {
            type: String,
            default: 'mall/order-comments/reply',
        },
        edit_url: {
            type: String,
            default: 'mall/order-comments/edit'
        }
    },
    data() {
        return {
            form: [],
            pageCount: 0,
            comment_type: '0',
            keyword: '',
            listLoading: false,
            btnLoading: false,
            dialogImg: false,
            click_img: null
        };
    },
    methods: {
        openImg(url) {
            console.log(url)
            this.click_img = url;
            this.dialogImg = true;
        },

        reply(id){
            navigateTo({r: this.reply_url, id:id});
        },
        edit(id) {
            navigateTo({r: this.edit_url, id:id});
        },

        pagination(currentPage) {
            this.page = currentPage;
            this.getList();
        },
        // 搜索
        search() {
            this.page = 1;
            this.getList();
        },

        //删除
        destroy: function(column) {
            this.$confirm('确认删除该记录吗?', '提示', {
                type: 'warning'
            }).then(() => {
                this.listLoading = true;
                request({
                    params: {
                        r: 'mall/order-comments/destroy'
                    },
                    data: { id: column.id },
                    method: 'post'
                }).then(e => {
                    location.reload();
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
            });
        },

        // 状态切换
        switchShow(row) {
            let self = this;
            request({
                params: {
                    r: 'mall/order-comments/show',
                },
                method: 'post',
                data: {
                    is_show: row.is_show,
                    id: row.id
                }
            }).then(e => {
                if (e.data.code === 0) {
                    self.$message.success(e.data.msg);
                } else {
                    self.$message.error(e.data.msg);
                }
            }).catch(e => {
                console.log(e);
            });
        },

        getList() {
            this.listLoading = true;
            this.form = [];
            request({
                params: {
                    r: 'mall/order-comments/index',
                    page: this.page,
                    sign: this.sign,
                },
            }).then(e => {
                if (e.data.code === 0) {
                    this.form = e.data.data.list;
                    this.pageCount = e.data.data.pagination.page_count;
                } else {
                    this.$message.error(e.data.msg);
                }
                this.listLoading = false;
            }).catch(e => {
                this.listLoading = false;
            });
        },
    },
    mounted: function() {
        this.getList();
    }
});
</script>
