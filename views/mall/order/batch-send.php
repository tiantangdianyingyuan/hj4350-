<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .form-body {
        padding: 20px 50% 20px 20px;
        background-color: #fff;
        margin-bottom: 20px;
    }

    .form-button {
        margin: 0;
    }

    .form-button .el-form-item__content {
        margin-left: 0!important;
    }

    .button-item {
        padding: 9px 25px;
    }

    .el-input-group__append {
        background-color: #fff;
    }

    .el-form-item__content {
        line-height: 1;
    }
</style>
<section id="app" v-cloak>
    <el-card class="box-card" v-loading="listLoading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" class="clearfix">
            <span>批量发货</span>
        </div>
        <div class="form-body">
            <el-form :model="form" label-width="150px" :rules="FormRules" ref="form">
                <el-form-item label="导入模板" prop="file_name" style="min-width: 600px">
                    <div flex="dir:left cross:center">
                        <div style="margin-right: 25px" @click="handleClick">
                            <el-input size="small" :disabled="true" v-model="form.file_name"
                                      class="input-with-select">
                                <el-button slot="append">选择文件</el-button>
                            </el-input>
                            <input ref="input" type="file" accept=".csv, .xlsx" multiple="false" style="display: none"
                                   @change="handleChange">
                        </div>
                        <el-button @click="$navigate({r:'mall/order/batch-send-model'})" size="small">默认模板下载</el-button>
                    </div>
                </el-form-item>
                <el-form-item label="选择快递公司" prop="express">
                    <el-select size="small" v-model="form.express" filterable placeholder="请选择">
                        <el-option v-for="(item,index) in express" :key="index" :label="item.name"
                                   :value="item.name"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-button v-if="list && list.length>0" plain @click="listVisible = true">上次发送日志</el-button>
                </el-form-item>
            </el-form>
        </div>
        <el-button type="primary" :loading="btnLoading" @click="onSubmit" class="button-item">提交</el-button>
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
                listVisible: false,
                list: [],

                form: {
                    express: '',
                    file_name: '',
                },
                express: [],
                listLoading: false,
                btnLoading: false,
                FormRules: {
                    file_name: [
                        {required: true, message: '模板不能为空', trigger: 'blur'},
                    ],
                    express: [
                        {required: true, message: '快递公司不能为空', trigger: 'blur'},
                    ],
                },

                max: 1,
                formData: new FormData(),
            };
        },
        methods: {
            handleClick() {
                this.$refs.input.value = null;
                this.$refs.input.click();
            },
            handleChange(e) {
                if (!e.target.files) return;
                this.uploadFiles(e.target.files);
            },

            uploadFiles(rawFiles) {
                if (this.max && rawFiles.length > this.max) {
                    this.$message.error('最多一次只能上传' + this.max + '个文件。')
                    return;
                }
                let files = [];
                for (let i = 0; i < rawFiles.length; i++) {
                    const file = {
                        _complete: false,
                        response: null,
                        rawFile: rawFiles[i],
                    };
                    files.push(file);
                }
                for (let i in files) {
                    let file = files[i];
                    let formData = new FormData();
                    for (let i in this.fields) {
                        formData.append(i, this.fields[i]);
                    }
                    formData.append('file', file.rawFile, file.rawFile.name);
                    this.formData = formData;
                    this.form.file_name = file.rawFile.name;
                }
            },
            onSubmit() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        let formData = this.formData;
                        formData.append('express', this.form.express);
                        request({
                            headers: {'Content-Type': 'multipart/form-data'},
                            params: {
                                r: 'mall/order/batch-send',
                            },
                            data: this.formData,
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