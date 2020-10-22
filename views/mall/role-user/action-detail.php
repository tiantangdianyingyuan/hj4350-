<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
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
        margin-right: 40px;
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

    .el-table .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }

    #app .copy .el-input-group__append {
        background-color: #409EFF;
        border-color: #409EFF;
        padding: 0 10px;
    }

    #app .table-body .copybtn {
        color: #fff;
        padding: 0 30px;
    }

    .el-alert {
        padding: 0;
        padding-left: 5px;
        padding-bottom: 5px;
    }

    .el-alert--info .el-alert__description {
        color: #606266;
    }

    .el-alert .el-button {
        margin-left: 20px;
    }

    .el-alert__content {
        display: flex;
        align-items: center;
    }

    .table-body .el-alert__title {
        margin-top: 5px;
        font-weight: 400;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>操作记录详情</span>
            </div>
        </div>
        <div class="table-body">
            <el-row>
                <el-col :span="12" flex="dir:top">
                    <div style="margin-bottom: 20px;">更新前数据</div>
                    <div>{{detail.before_update}}</div>
                </el-col>
                <el-col :span="12">
                    <div style="margin-bottom: 20px;">更新后数据</div>
                    <div>{{detail.after_update}}</div>
                </el-col>
            </el-row>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                detail: [],
            };
        },
        methods: {
            getDetail() {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'mall/role-user/action-detail',
                        id: getQuery('id')
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.detail = e.data.data.detail;
                }).catch(e => {
                    console.log(e);
                });
            },
        },
        mounted: function () {
            this.getDetail();
        }
    });
</script>
