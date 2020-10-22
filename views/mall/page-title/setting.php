<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>

<style>
    .form-body {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 20px;
    }

    .form-button {
        margin: 0!important;
    }

    .form-button .el-form-item__content {
        margin-left: 0!important;
    }

    .button-item {
        padding: 9px 25px;
    } 
</style>

<div id="app" v-cloak>
    <el-card  class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;" v-loading="cardLoading">
        <div slot="header">
            <div>
                <span>页面标题设置</span>
            </div>
        </div>
        <el-row class="form-body">
            <el-col v-for="(item,index) in list" :key="index" :span="12" flex="dir:left" style="padding-bottom: 15px;">
                <el-tag style="min-width: 120px;padding-right: 50px">{{item.name}}</el-tag>
                <el-input style="margin: 0 20px;" size="small" v-model="item.new_name"></el-input>
            </el-col>
        </el-row>
        <el-button class="button-item" :loading="btnLoading" @click="store" type="primary" size="small">保存</el-button>
        <el-button class="button-item" :loading="btnLoading" @click="restoreDefault" size="small">恢复默认</el-button>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                list: [],
                btnLoading: false,
                cardLoading: false,
            };
        },
        methods: {
            store() {
                let self = this;
                self.btnLoading = true;
                request({
                    params: {
                        r: 'mall/page-title/setting'
                    },
                    method: 'post',
                    data: {
                        list: self.list,
                    }
                }).then(e => {
                    self.btnLoading = false;
                    if (e.data.code == 0) {
                        self.$message.success(e.data.msg);
                        self.getList();
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.$message.error(e.data.msg);
                    self.btnLoading = false;
                });

            },
            restoreDefault() {
                let self = this;
                self.$confirm('恢复默认, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.btnLoading = true;
                    request({
                        params: {
                            r: 'mall/page-title/restore-default',
                        },
                        method: 'post',
                        data: {}
                    }).then(e => {
                        self.btnLoading = false;
                        if (e.data.code === 0) {
                            self.$message.success(e.data.msg);
                            self.getList();
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        console.log(e);
                    });
                }).catch(() => {
                    self.$message.info('已取消')
                });
            },
            getList() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'mall/page-title/setting',
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        self.list = e.data.data.list;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
        },
        mounted: function () {
            this.getList();
        }
    });
</script>
