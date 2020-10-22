<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

$mchId = Yii::$app->user->identity->mch_id;
Yii::$app->loadViewComponent('goods/app-add-cat');
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .tag-item {
        margin-right: 5px;
    }

    .btn {
        margin: 0;
        padding: 0;
        border: 1px solid transparent;
        outline: none;
        color: #409EFF;
        font-size: 14px;
        cursor: pointer;
    }

    .el-step__title.is-finish {
        position: absolute;
        left: -18px;
    }

    .el-step__title.is-process {
        position: absolute;
        left: -18px;
    }

    .el-step__title.is-wait {
        position: absolute;
        left: -18px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>批量导入</span>
                <div style="float: right;margin-top: -5px">
                    <el-button v-if="activeName === 'first'" type="primary" size="small"
                               @click="$navigate({r:'mall/goods/import-goods-log'})">
                        <span>商品导入历史</span>
                    </el-button>
                    <el-button v-if="activeName === 'second'" type="primary" size="small"
                               @click="$navigate({r:'mall/cat/import-cat-log'})">
                        <span>分类导入历史</span>
                    </el-button>
                </div>
            </div>
        </div>
        <div class="table-body">
            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-tab-pane label="商品导入" name="first"></el-tab-pane>
                <el-tab-pane label="分类导入" name="second"></el-tab-pane>
            </el-tabs>

            <el-steps style="margin-left: 20px; margin-top: 20px;" :space="300" :active="active">
                <el-step title="选择文件">
                    <template slot="icon">
                        <img v-if="active > 0" src="statics/img/mall/goods/finish-2.png">
                    </template>
                </el-step>
                <el-step title="导入数据">
                    <template slot="icon">
                        <img v-if="active > 1" src="statics/img/mall/goods/finish-2.png">
                    </template>
                </el-step>
                <el-step title="导入完成">
                    <template class="is-success" slot="icon">
                        <img v-if="active > 2" src="statics/img/mall/goods/finish-2.png">
                    </template>
                </el-step>
            </el-steps>

            <template v-if="active <= 2">
                <!-- 商品导入 -->
                <el-form v-show="activeName == 'first'" :model="ruleForm" :rules="rules" ref="ruleForm"
                         label-width="100px"
                         style="margin-top: 30px;width: 600px">
                    <el-form-item></el-form-item>
                    <el-form-item></el-form-item>
                    <el-form-item label="状态" prop="goods_status">
                        <el-radio v-model="ruleForm.goods_status" :label="0">暂不上架</el-radio>
                        <el-radio v-model="ruleForm.goods_status" :label="1">立即上架</el-radio>
                    </el-form-item>
                    <el-form-item label="上传数据" prop="file">
                        <template slot="label">
                            <span>上传数据</span>
                            <el-tooltip effect="dark" content="仅支持上传csv格式"
                                        placement="top">
                                <i class="el-icon-info"></i>
                            </el-tooltip>
                        </template>
                        <label>{{ruleForm.file ? ruleForm.file.name : ''}}</label>
                        <el-upload
                                action=""
                                :multiple="false"
                                :http-request="handleFile"
                                :on-change="excelChange"
                                accept=".csv"
                                :show-file-list="false">
                            <span style="color: #409EFF;">选择本地文件</span>
                        </el-upload>
                    </el-form-item>
                    <el-form-item v-if="mch_id > 0" label="系统分类" prop="system_cat_ids">
                        <el-tag v-if="systemCats.length > 0" type="warning" class="tag-item"
                                @close="deleteSystemCat(item, index)" closable
                                v-for="(item, index) in systemCats"
                                :key="item.value">
                            {{item.label}}
                        </el-tag>
                        <el-button type="primary" @click="$refs.systemCats.openDialog()" size="small">选择分类
                        </el-button>
                    </el-form-item>
                    <el-form-item label="商品分类" prop="cat_ids">
                        <el-tag v-if="cats.length > 0" type="warning" class="tag-item"
                                @close="deleteCat(item, index)"
                                closable
                                v-for="(item, index) in cats"
                                :key="item.value">
                            {{item.label}}
                        </el-tag>
                        <el-button type="primary" @click="$refs.cats.openDialog()" size="small">选择分类</el-button>
                    </el-form-item>

                    <el-form-item>
                        <el-button :loading="btnLoading" type="primary" @click="submit('ruleForm')"
                                   size="small">一键导入
                        </el-button>
                    </el-form-item>
                </el-form>
                <!-- 分类导入 -->
                <el-form v-show="activeName == 'second'" :model="ruleCatForm" :rules="catRules" ref="ruleCatForm"
                         label-width="100px"
                         style="margin-top: 30px;width: 600px">
                    <el-form-item></el-form-item>
                    <el-form-item></el-form-item>
                    <el-form-item label="上传数据" prop="file">
                        <template slot="label">
                            <span>上传数据</span>
                            <el-tooltip effect="dark" content="仅支持上传csv格式"
                                        placement="top">
                                <i class="el-icon-info"></i>
                            </el-tooltip>
                        </template>
                        <label>{{ruleCatForm.file ? ruleCatForm.file.name : ''}}</label>
                        <el-upload
                                action=""
                                :multiple="false"
                                :http-request="handleFile2"
                                :on-change="excelChange2"
                                accept=".csv"
                                :show-file-list="false">
                            <span style="color: #409EFF;">选择本地文件</span>
                        </el-upload>
                    </el-form-item>
                    <el-form-item label="是否启用" prop="status">
                        <el-switch
                                v-model="ruleCatForm.status"
                                :active-value="1"
                                :inactive-value="0">
                        </el-switch>
                    </el-form-item>
                    <el-form-item label="是否显示" prop="is_show">
                        <el-switch
                                v-model="ruleCatForm.is_show"
                                :active-value="1"
                                :inactive-value="0">
                        </el-switch>
                    </el-form-item>

                    <el-form-item>
                        <el-button :loading="btnLoading" type="primary" @click="submit('ruleCatForm')"
                                   size="small">一键导入
                        </el-button>
                    </el-form-item>
                </el-form>
            </template>
            <template v-else>
                <div style="width: 600px;height: 200px" flex="dir:top cross:center main:center">
                    <div flex="dir:left cross:center">
                        <img src="statics/img/mall/goods/finish-1.png">
                        <span style="margin-left: 10px">成功导入{{successCount}}条数据</span>
                    </div>
                    <div v-if="errorCount > 0" style="margin-top: 20px;">
                        <span>未导入<span style="color: red;">{{errorCount}}</span>条数据</span>
                        <span style="color: #409EFF;margin-left: 10px;"></span>
                        <form style="display: inline" target="_blank"
                              :action_url="'<?= Yii::$app->request->baseUrl . '/index.php?r=' ?>' + logUrl"
                              method="post">
                            <input name="_csrf" type="hidden" id="_csrf"
                                   value="<?= Yii::$app->request->csrfToken ?>">
                            <input name="flag" value="EXPORT" type="hidden">
                            <input name="type" v-model="activeName" type="hidden">
                            <button class="btn" type="submit">下载未导入数据</button>
                        </form>
                    </div>
                </div>
            </template>

            <el-dialog
                    :before-close="beforeClose"
                    :close-on-click-modal="false"
                    title="上传"
                    :visible.sync="importDialogVisible"
                    width="20%">
                <template>
                    <el-progress :text-inside="true" :stroke-width="18"
                                 :percentage="importParams.percentage"></el-progress>
                </template>
            </el-dialog>

            <app-add-cat ref="cats" :new-cats="ruleForm.cat_ids" @select="selectCat" :mch_id="mch_id"></app-add-cat>
            <app-add-cat ref="systemCats" :new-cats="ruleForm.system_cat_ids" @select="selectSystemCat"></app-add-cat>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                mch_id: <?= $mchId ?>,
                cats: [],
                systemCats: [],
                ruleForm: {
                    goods_status: 0,
                    file: '',
                    cat_ids: [],
                    system_cat_ids: [],
                },
                active: 0,
                rules: {
                    goods_status: [
                        {required: true, message: '请选择商品状态', trigger: 'change'},
                    ],
                    file: [
                        {required: true, message: '请上传文件', trigger: 'change'},
                    ],
                    cat_ids: [
                        {required: true, message: '请选择商品分类', trigger: 'change'},
                    ],
                    system_cat_ids: [
                        {required: true, message: '请选择系统分类', trigger: 'change'},
                    ],
                },
                btnLoading: false,
                errorCount: 0,
                successCount: 0,
                importDialogVisible: false,
                importParams: {
                    percentage: 0,
                    current_num: 1,
                },
                activeName: 'first',

                // 分类
                ruleCatForm: {
                    status: 0,
                    file: '',
                    is_show: 0,
                },
                catRules: {
                    file: [
                        {required: true, message: '请上传文件', trigger: 'change'},
                    ],
//                    status: [
//                        {required: true, message: '请选择分类状态', trigger: 'change'},
//                    ],
//                    is_show: [
//                        {required: true, message: '请选择分类显示', trigger: 'change'},
//                    ],
                },
                logUrl: 'mall/goods/import-goods-log',
            };
        },
        methods: {
            selectCat(cats) {
                this.cats = cats;
                let arr = [];
                cats.map(v => {
                    arr.push(v.value);
                });
                this.ruleForm.cat_ids = arr;
            },
            selectSystemCat(cats) {
                this.systemCats = cats;
                let arr = [];
                cats.map(v => {
                    arr.push(v.value);
                });
                this.ruleForm.system_cat_ids = arr;
            },
            deleteCat(option, index) {
                let self = this;
                self.ruleForm.cat_ids.forEach(function (item, index) {
                    if (item == option.value) {
                        self.ruleForm.cat_ids.splice(index, 1)
                    }
                })
                self.cats.splice(index, 1);
            },
            deleteSystemCat(option, index) {
                let self = this;
                self.ruleForm.system_cat_ids.forEach(function (item, index) {
                    if (item == option.value) {
                        self.ruleForm.system_cat_ids.splice(index, 1)
                    }
                })
                self.systemCats.splice(index, 1);
            },
            handleFile() {
            },
            handleFile2() {
            },
            excelChange(file, fileList) {
                this.setActive();
                this.ruleForm.file = file.raw;
            },
            excelChange2(file, fileList) {
                this.setActive();
                this.ruleCatForm.file = file.raw;
            },
            setActive() {
                if (this.active == 0) {
                    this.active += 1;
                }

                this.importParams = {
                    percentage: 0,
                    current_num: 1,
                };
            },
            submit(formName) {
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        this.$confirm('是否确认导入？', '提示', {
                            confirmButtonText: '确定',
                            cancelButtonText: '取消',
                            type: 'warning'
                        }).then(() => {
                            this.btnLoading = true;
                            this.active += 1;
                            this.importDialogVisible = true;
                            if (this.activeName === 'first') {
                                this.submitAction();
                            } else {
                                this.submitAction2();
                            }
                        }).catch(() => {
                        });
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
            submitAction() {
                let self = this;
                let formData = new FormData();
                formData.append('file', this.ruleForm.file);
                formData.append('goods_status', this.ruleForm.goods_status);
                this.ruleForm.cat_ids.forEach(function (item, index) {
                    formData.append('cat_ids[]', item);
                });
                this.ruleForm.system_cat_ids.forEach(function (item, index) {
                    formData.append('system_cat_ids[]', item);
                });
                formData.append('current_num', this.importParams.current_num);
                formData.append('file_path', this.importParams.file_path ? this.importParams.file_path : '');
                formData.append('import_data_id', this.importParams.import_data_id ? this.importParams.import_data_id : 0);

                request({
                    header: {
                        'Content-Type': 'multipart/form-data'
                    },
                    params: {
                        r: 'mall/goods/import-data'
                    },
                    data: formData,
                    method: 'post'
                }).then(e => {
                    this.btnLoading = false;
                    if (e.data.code === 0) {
                        let data = e.data.data;
                        self.importParams.increase = 100 / data.import_params.num_count;
                        self.importParams.percentage += self.importParams.increase;
                        self.importParams.percentage = parseFloat(self.importParams.percentage.toFixed(2));
                        if (data.import_params.current_num == data.import_params.num_count) {
                            self.importParams.percentage = 100;
                            self.active += 1;
                            self.errorCount = data.error_count;
                            self.successCount = data.success_count;
                            self.importDialogVisible = false;
                        }
                        if (data.import_params.current_num < data.import_params.num_count) {
                            self.importParams.current_num += 1;
                            self.importParams.file_path = data.import_params.file_path;
                            self.importParams.import_data_id = data.import_params.import_data_id;
                            self.submitAction();
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.btnLoading = false;
                });
            },
            beforeClose() {
                if (this.importParams.percentage < 100) {
                    this.$confirm('导入尚未完成, 是否确认关闭?', '警告', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        this.importDialogVisible = false;
                        navigateTo({r: 'mall/goods/import-data'});
                    }).catch(() => {
                    });
                }
            },
            handleClick(tab, event) {
                if (tab.name === 'second') {
                    this.logUrl = 'mall/cat/import-cat-log'
                    if (this.ruleCatForm.file === '') {
                        this.active = 0;
                    }
                } else {
                    this.logUrl = 'mall/goods/import-goods-log';
                    if (this.ruleForm.file === '') {
                        this.active = 0;
                    }
                }
            },
            // 分类导入
            submitAction2() {
                let self = this;
                let formData = new FormData();
                formData.append('file', this.ruleCatForm.file);
                formData.append('status', this.ruleCatForm.status);
                formData.append('is_show', this.ruleCatForm.is_show);
                formData.append('current_num', this.importParams.current_num);
                formData.append('file_path', this.importParams.file_path ? this.importParams.file_path : '');
                formData.append('import_data_id', this.importParams.import_data_id ? this.importParams.import_data_id : 0);

                request({
                    header: {
                        'Content-Type': 'multipart/form-data'
                    },
                    params: {
                        r: 'mall/cat/import-cat'
                    },
                    data: formData,
                    method: 'post'
                }).then(e => {
                    this.btnLoading = false;
                    if (e.data.code === 0) {
                        let data = e.data.data;
                        self.importParams.increase = 100 / data.import_params.num_count;
                        self.importParams.percentage += self.importParams.increase;
                        self.importParams.percentage = parseFloat(self.importParams.percentage.toFixed(2));
                        if (data.import_params.current_num == data.import_params.num_count) {
                            self.importParams.percentage = 100;
                            self.active += 1;
                            self.errorCount = data.error_count;
                            self.successCount = data.success_count;
                            self.importDialogVisible = false;
                        }
                        if (data.import_params.current_num < data.import_params.num_count) {
                            self.importParams.current_num += 1;
                            self.importParams.file_path = data.import_params.file_path;
                            self.importParams.import_data_id = data.import_params.import_data_id;
                            self.submitAction2();
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.btnLoading = false;
                });
            },
        },
        mounted: function () {

        }
    });
</script>
