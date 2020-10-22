<?php defined('YII_ENV') or exit('Access Denied'); ?>
<section id="app" v-cloak>
    <el-card class="box-card" v-loading="listLoading">
        <div slot="header" class="clearfix">
            <span>批量发货</span>
        </div>
        <div class="text item" style="width:50%">
            <el-form :model="form" label-width="150px" :rules="FormRules" ref="form">
                <el-form-item label="导入模板" prop="url">
                    <div class="app-attachment-item app-attachment-upload">
                        <app-upload v-loading="uploading" :disabled="uploading" @start="handleStart" @success="handleSuccess" @complete="handleComplete" :max="1" accept=".csv" flex="main:center cross:center" style="width: 100px;height: 100px">
                            <div v-if="uploading"></div>
                            <i v-else class="el-icon-upload"></i>
                        </app-upload>
                    </div>
                    <div v-if="form.url" flex="cross:center" style="height: 100px">{{attachments[0].name}}</div>
                </el-form-item>
                <el-form-item label="选择快递公司" prop="express">
                    <el-select v-model="form.express" filterable placeholder="请选择">
                        <el-option v-for="item in express" :key="item.name" :label="item.name" :value="item.name"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" :loading="btnLoading" @click="onSubmit" plain>提交</el-button>
                    <el-button @click="$navigate({r:'mall/order/batch-send-model'})" type="info" plain>默认模板下载</el-button>
                    <el-button v-if="list && list.length>0" plain @click="listVisible = true">上次发送日志</el-button>
                </el-form-item>
            </el-form>
        </div>
        <!--指定商品分类-->
        <el-dialog title="处理记录" :visible.sync="listVisible" width="50%">
            <el-table :data="list" max-height="800">
                <el-table-column property="empty" label="订单不存在"></el-table-column>
                <el-table-column property="cancel" label="订单取消"></el-table-column>
                <el-table-column property="send" label="已发货商品"></el-table-column>
                <el-table-column property="offline" label="自提订单"></el-table-column>
                <el-table-column property="pay" label="未支付"></el-table-column>
                <el-table-column property="error" label="处理失败"></el-table-column>
                <el-table-column property="success" label="处理成功"></el-table-column>
            </el-table>
        </el-dialog>
    </el-card>
</section>
<script>
const app = new Vue({
    el: '#app',
    data() {
        return {
            uploading: false,
            attachments: [],
            listVisible: false,
            list: [],

            form: {
                express: '',
                url: '',
            },
            express: [],
            listLoading: false,
            btnLoading: false,
            FormRules: {
                url: [
                    { required: true, message: '模板不能为空', trigger: 'blur' },
                ],
                express: [
                    { required: true, message: '快递公司不能为空', trigger: 'blur' },
                ],
            },
        };
    },
    methods: {
        //上传
        handleStart(files) {
            this.uploading = true;
        },
        handleSuccess(file) {
            if (file.response && file.response.data && file.response.data.code === 0) {
                const newItem = {
                    url: file.response.data.data.url,
                    name: file.response.data.data.name,
                };
                this.attachments.unshift(newItem);
            }
        },
        handleComplete(files) {
            this.form.url = this.attachments[0].url;
            this.uploading = false;
        },

        onSubmit() {
            this.$refs.form.validate((valid) => {
                if (valid) {
                    this.btnLoading = true;
                    let para = Object.assign({}, this.form);
                    request({
                        params: {
                            r: 'mall/order/batch-send',
                        },
                        data: para,
                        method: 'post'
                    }).then(e => {
                        if (e.data.code === 0) {
                            this.$message.success(e.data.msg);
                            this.list = e.data.data.list;
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

        getList() {
            this.listLoading = true;
            request({
                params: {
                    r: 'mall/order/batch-send'
                },
            }).then(e => {
                if (e.data.code == 0) {
                    if (e.data.data) {
                        this.express = e.data.data.express_list;
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
    }
})
</script>