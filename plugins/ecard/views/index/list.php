<?php
/**
 * Created by PhpStorm.
 * User: fjt
 * Date: 2020/3/11
 * Time: 11:41
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
Yii::$app->loadViewComponent('app-rich-text');

?>
<style>
    .table-body {
        background-color: #fff;
        margin-bottom: 20px;
        padding: 20px;
    }

    .card-name.el-input {
        width: 350px;
    }
    .poster-form-title {
        padding: 24px 25% 24px 32px;
        border-bottom: 1px solid #ebeef5;
    }
    .explanation {
        background-color: rgba(255, 255, 204, 1);
        padding: 20px 20px 26px 20px;
        margin-bottom: 15px;
    }
    .el-popover.el-popper {
        padding: 0;
    }
    .input .el-input__inner {
        border-color: #ff4544;
    }
    .input-item {
        display: inline-block;
        width: 250px;
        margin: 0 0 20px;
    }
     .label {
        margin-right: 10px;
        line-height: 32px;
    }
    .el-form {
        border-bottom: 1px solid #e2e2e2;
        margin-bottom: 17px;
        margin-left: 20px;
        margin-right: 20px;
    }
    #app .export-dialog .el-dialog {
        min-width: 350px;
    }
    #app.export-dialog .el-dialog__body {
        padding: 20px 20px;
    }

    #app .export-dialog .el-button--submit {
        color: #FFF;
        background-color: #409EFF;
        border-color: #409EFF;
    }
    .edit .el-dialog {
        min-width: 450px;
    }
    .edit .el-dialog__body {

    }
    .export-dialog .el-dialog__body {
        padding: 30px 20px 0 20px;
    }
</style>

<div id="app" v-cloak>
    <el-dialog
            title="编辑卡密"
            :visible.sync="dialogVisible"
            width="20%"
            class="edit"
            >
        <div flex="wrap:nowrap cross:center" style="margin-bottom: 15px;">
            <div class="item-box"  style="width: 100%;max-height: 400px;overflow: auto" flex="dir:top cross:center">
                <div flex="main:justify" v-for="(item, index) in form.ecard.list" style="margin-bottom: 30px;width: 100%" flex="dir: left">
                    <div class="label" style="width: 90px;text-align: right;">
                        {{item.key}}
                    </div>
                    <div style="width: 300px;">
                        <el-input style="width: 280px" size="small" v-model="editItem['key' + (index + 1)]"
                                  maxlength="50"
                                  >
                        </el-input>
                    </div>
                </div>
            </div>
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button @click="dialogVisible = false">取 消</el-button>
            <el-button type="primary" @click="saveItem">确 定</el-button>
        </span>
    </el-dialog>
    <el-dialog
            title="批量导入卡密数据"
            :visible.sync="updateDialogVisible"
            width="20%"
    >
        <div>
            <div style="margin-bottom: 15px;">文件大小：{{sizeM}}</div>
            <div flex="">
                <div style="display: inline-block">
                    导入进度：
                </div>
                <div style="width: 80%">
                    <el-progress :stroke-width="10" :percentage="progress"></el-progress>
                </div>
            </div>
            <div v-if="updateData">
                <div flex="cross:center main:center" style="margin-top: 15px;">
                    <image style="width: 29px;height: 29px;margin-right:5px" src="statics/img/mall/pass.png"></image>
                    <span>成功导入{{updateData.success}}条数据</span>
                </div>
                <div flex="cross:center main:center" style="margin-top: 15px;" >
                    <span style="margin-right: 5px;">
                        未导入
                        <span style="color: #ff5d5d">{{updateData.fail}}</span>
                        条数据
                    </span>
                    <div>
                        <el-button @click="updateNo" style="color: #3398ff;cursor: pointer;border: none;padding: 0;margin-left: 5px;">
                            下载未导入数据
                        </el-button>
                    </div>
                </div>
            </div>
        </div>
        <div slot="footer" class="dialog-footer" flex="main:center">
            <el-button type="primary" @click="updateDialogVisible = false">我知道了</el-button>
        </div>
    </el-dialog>
    <el-dialog
            flex="cross:center"
            class="export-dialog"
            title="提示"
            :visible.sync="exportDialogVisible"
            width="20%">
        <template>
            <div>
                <div class="el-message-box__content">
                    <div class="el-message-box__status el-icon-warning"></div>
                    <div class="el-message-box__message">
                        <p>{{exportParams.export == 'export' ? '确认导出所有卡密数据' : '确认下载卡密库模板'}}</p>
                    </div>
                </div>
            </div>
            <span slot="footer" class="dialog-footer">
                <form target="_blank" :action="exportParams.action_url" method="post">
                    <div class="modal-body">

                        <input name="_csrf" type="hidden" id="_csrf"
                               value="<?= Yii::$app->request->csrfToken ?>">
                        <input name="ecard_id" :value="search.ecard_id" type="hidden">
                        <input name="status" :value="search.status" type="hidden">
                        <input name="date_satrt" :value="search.date_satrt" type="hidden">
                        <input name="date_end" :value="search.date_end" type="hidden">
                        <input name="keyword" :value="search.keyword" type="hidden">
                        <input name="export" :value="exportParams.export" type="hidden">
                    </div>
                    <div flex="dir:right" style="margin-top: 20px;">
                        <button type="submit"
                                class="el-button el-button--primary el-button--small">点击下载</button>
                    </div>
                </form>
            </span>
        </template>
    </el-dialog>
    <el-dialog
        title="提示"
        :visible.sync="uploadVisible"
        width="30%">
        <p flex="cross:center">
            <image src="statics/img/plugins/sigh.png" style="margin-right: 10px;width:30px;height: 30px;"></image>
            <span>请导入不大于2M的Excel文件</span>
        </p>
        <span slot="footer" class="dialog-footer">
             <el-upload
                 style="margin-right: 10px;"
                 action=""
                 :multiple="false"
                 :http-request="handleFile"
                 accept=".xls,.csv"
                 :on-change="excelChange"
                 :show-file-list="false">
                <el-button type="primary" >点击导入</el-button>
            </el-upload>
        </span>
    </el-dialog>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;" v-loading="loading">
        <div slot="header">
           <div>
               <span @click="route" style="cursor: pointer;color: #409eff;">卡密列表</span>
               <span>/卡密管理</span>
               <div style="float: right;margin-top: -5px" flex="">
                   <el-button style="margin-right: 10px;" type="primary" @click="newEdit" size="small">添加卡密数据</el-button>
                   <el-button type="primary" @click="uploadVisible = true" size="small">批量导入卡密数据</el-button>
                   <el-button type="primary" @click="exportEmpty" size="small">下载模板</el-button>
                   <el-button type="primary" @click="exportData" size="small">导出卡密数据</el-button>
               </div>
           </div>
        </div>
        <div style="background-color: #ffffff;">
           <div class="poster-form-title" style="margin-bottom: 24px;">卡密信息</div>
           <el-form>
               <div style="margin-left: 50px;padding: 20px;">
                   <el-form-item label="卡密名称" >
                       <el-input disabled class="card-name" v-model="form.ecard.name" placeholder="最多输入10个字" maxlength="10"></el-input>
                   </el-form-item>
               </div>
           </el-form>
           <div class="table-body">

               <div flex="wrap:nowrap cross:center" style="margin-bottom: 15px;">
                   <div class="item-box" style="margin-right: 15px;" flex="dir:left cross:center">
                       <div class="label">
                           {{form.ecard.key}}
                       </div>
                       <div>
                           <el-input style="width: 350px" size="small"
                                     v-model="search.keyword"
                                     :placeholder="'请输入'+ form.ecard.key"
                                     clearable
                                     @clear="toSearch"
                                     @keyup.enter.native="toSearch">
                               <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                           </el-input>
                       </div>
                   </div>
                   <div class="label">添加时间：</div>
                   <el-date-picker
                       class="item-box"
                       size="small"
                       @change="toSearch"
                       v-model="search.date"
                       type="datetimerange"
                       value-format="yyyy-MM-dd HH:mm:ss"
                       range-separator="至"
                       start-placeholder="开始日期"
                       end-placeholder="结束日期">
                   </el-date-picker>
                   <el-button class="item-box" style="margin-left: 20px;" @click="deleteList" size="small">批量删除</el-button>
               </div>
               <el-tabs v-model="search.status" @tab-click="toSearch">
                   <el-tab-pane label="全部" name="-1"></el-tab-pane>
                   <el-tab-pane label="已售出" name="1"></el-tab-pane>
                   <el-tab-pane label="未售出" name="0"></el-tab-pane>
               </el-tabs>
               <el-table v-loading="listLoading" :data="form.list" border
                         @selection-change="handleSelectionChange"
                         style="width: 100%">
                   <el-table-column
                           type="selection"
                           fixed="left"
                           :selectable="checkSelectable"
                           width="60">
                   </el-table-column>
                   <el-table-column
                           v-for="(item, index) in form.ecard.list"
                           :key="index"
                           :prop="'key' + (index + 1)"
                           :label="item.key"
                   >
                   </el-table-column>
                   <el-table-column
                           fixed="right"
                           width="230"
                           label="状态">
                       <template slot-scope="scope">
                           <el-tag type="success" v-if="scope.row.is_sales === 0">未售出</el-tag>
                           <el-tag type="warning" v-else-if="scope.row.is_sales === 1">已售出</el-tag>
                       </template>
                   </el-table-column>
                   <el-table-column
                           prop="created_at"
                           fixed="right"
                           width="265"
                           label="添加时间">
                   </el-table-column>
                   <el-table-column
                           prop="address"
                           fixed="right"
                           width="240"
                           label="操作">
                       <template slot-scope="scope">
                           <el-button type="text" v-if="scope.row.is_sales === 0" circle size="mini" @click="edit(scope.row)">
                               <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                   <img src="statics/img/mall/edit.png" alt="">
                               </el-tooltip>
                           </el-button>
                           <el-button  type="text" circle size="mini" v-if="scope.row.is_sales === 0" @click="deleteItem([{token: scope.row.token}])">
                               <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                   <img src="statics/img/mall/del.png" alt="">
                               </el-tooltip>
                           </el-button>
                       </template>
                   </el-table-column>
               </el-table>
               <div flex="main:right cross:center" style="margin-top: 20px;">
                   <div v-if="page_count > 0">
                       <el-pagination
                           @current-change="pagination"
                           background
                           :current-page="current_page"
                           layout="prev, pager, next, jumper"
                           :page-count="page_count">
                       </el-pagination>
                   </div>
               </div>
           </div>
       </div>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',

        data() {
            return {
                form: {
                    list: [],
                    ecard: {
                        list: [],
                        key: ''
                    },
                },
                loading: false,
                listLoading: false,
                search: {
                    ecard_id: 1,
                    status: '-1',
                    keyword: '',
                    page: 1,
                    date_start: '',
                    date_end: '',
                    date: null
                },
                page_count: 1,
                current_page: 1,
                dialogVisible: false,
                editItem: {
                },
                selectList: [],
                exportParams: {
                    action_url: `<?= Yii::$app->urlManager->createUrl('/plugin/ecard/mall/index/export') ?>`,
                    export: 'export'
                },
                exportDialogVisible: false,
                choose_list: [],
                file: '',
                uploadVisible: false,
                updateDialogVisible: false,
                progress: 0,
                updateData: null,
                sizeM: 0
            }
        },

        methods: {
            excelChange(file, fileList) {
                this.file = file.raw;
            },

            updateNo() {
                const elt = document.createElement('a');
                elt.setAttribute('href', this.updateData.url);
                elt.setAttribute('download', '未导入数据.csv');
                elt.style.display = 'none';
                document.body.appendChild(elt);
                elt.click();
            },
            async handleFile() {
                let formData = new FormData();
                let size = this.file.size;
                let sizeM = (size / Math.pow(1024, 2)).toFixed(2) + "M";
                this.sizeM = sizeM;
                formData.append('file', this.file);
                this.uploadVisible = false;
                this.updateDialogVisible = true;
                formData.append('ecard_id', this.search.ecard_id);
                let that = this;
                const e = await request({
                    params: {
                        r: `plugin/ecard/mall/index/import`,
                    },
                    method: 'post',
                    data: formData,
                    onUploadProgress: function(progressEvent) {
                        if (progressEvent.lengthComputable) {
                            that.progress = Math.round((progressEvent.loaded / progressEvent.total) * 100);
                        }
                    }
                });
                if (e.data.code === 0) {
                    this.updateData = e.data.data.data;
                    this.getInform();
                } else {
                    that.$message({
                        type: 'warning',
                        message: e.data.msg
                    });
                }
            },

            // 导出数据
            exportData() {
                this.exportDialogVisible = true;
                this.exportParams.export = 'export';
            },

            // 导出模板
            exportEmpty() {
                this.exportDialogVisible = true;
                this.exportParams.export = 'empty';
            },

            async getInform() {
                if (this.search.date) {
                    this.search.date_start = this.search.date[0];
                    this.search.date_end = this.search.date[1];
                } else {
                    this.search.date_start = '';
                    this.search.date_end = '';
                }
                let { ecard_id, status, keyword, page, date_start, date_end } = this.search;
                const e = await request({
                    params: {
                        r: `plugin/ecard/mall/index/list`,
                        ecard_id,
                        status,
                        keyword,
                        page,
                        date_start,
                        date_end
                    }
                });
                if (e.data.code === 0) {
                    let {pagination, list, ecard} = e.data.data;
                    this.form = {
                        list,
                        ecard
                    };
                    this.page_count = pagination.page_count;
                    this.current_page = pagination.current_page;
                }
            },

            pagination(e) {
                this.listLoading = true;
                this.search.page = e;
                this.getInform().then(() => {
                    this.listLoading = false;
                }).catch(() => {
                    this.listLoading = false;
                })
            },

            toSearch() {
                this.listLoading = true;
                this.getInform().then(() => {
                    this.listLoading = false;
                }).catch(() => {
                    this.listLoading = false;
                })
            },

            handleSelectionChange(e) {
                this.selectList = e;
            },

            deleteList() {
                let that = this;
                if(that.selectList.length === 0) {
                    that.$message({
                        type: 'warning',
                        message: '请先选择数据'
                    });
                    return;
                }
                this.$confirm('是否确认删除选中的数据?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    let list = [];
                    for (let i = 0; i < that.selectList.length; i++) {
                        list.push({
                            token: that.selectList[i].token
                        })
                    }
                    request({
                        params: {
                            r: `plugin/ecard/mall/index/destroy`,
                            token: JSON.stringify(list),
                            ecard_id: that.form.ecard.id
                        }
                    }).then(e => {
                        if (e.data.code === 0) {

                            that.$message({
                                type: 'success',
                                message: '批量删除成功!'
                            });
                            this.getInform();
                        } else {
                            that.$message({
                                type: 'warning',
                                message: e.data.msg
                            });
                        }
                    });

                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '已取消删除'
                    });
                });
            },
            edit(row) {
                this.dialogVisible = true;
                this.editItem = JSON.parse(JSON.stringify(row));
                console.log(this.editItem);
            },

            deleteItem(data) {
                let that = this;
                this.$confirm('删除该条数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    request({
                        params: {
                            r: `plugin/ecard/mall/index/destroy`,
                            token: JSON.stringify(data),
                            ecard_id: that.form.ecard.id
                        }
                    }).then(e => {
                        if (e.data.code === 0) {
                            that.$message({
                                type: 'success',
                                message: '删除成功!'
                            });
                            this.getInform();
                        } else {
                            that.$message({
                                type: 'warning',
                                message: e.data.msg
                            });
                        }
                    });

                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '已取消删除'
                    });
                });
            },

            // 保存卡密数据
            async saveItem() {
                this.loading = true;
                let list = [];
                console.log(this.editItem)
                for (let i = 0; i < Object.keys(this.editItem).length; i++) {
                    if (this.editItem.hasOwnProperty('key' + (i + 1))) {
                        list.push({
                            key: this.editItem['key' + (i + 1)]
                        })
                    }
                }
                this.dialogVisible = false;
                const e = await request({
                    params: {
                        r: 'plugin/ecard/mall/index/edit-data'
                    },
                    method: 'post',
                    data: {
                        token: this.editItem.token,
                        ecard_data_list: JSON.stringify(list),
                        ecard_id: this.form.ecard.id
                    }
                });

                this.loading = false;
                if (e.data.code === 0) {
                    this.$message({
                        type: 'success',
                        message: '编辑成功!'
                    });

                    this.getInform();
                } else {
                    this.$message({
                        type: 'warning',
                        message: e.data.msg
                    });
                }
            },

            // 添加卡密数据
            newEdit() {
                console.log(this.form.ecard.list.length);
                if (this.form.ecard.list.length > 0) {
                    this.$navigate({
                        r: 'plugin/ecard/mall/index/list-edit',
                        id: this.form.ecard.id
                    });
                } else {
                    this.$confirm('请先添加字段！', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        this.$navigate({
                            r: 'plugin/ecard/mall/index/edit',
                            id: this.form.ecard.id
                        });
                    }).catch(() => {
                    });
                }
            },

            exportGoods() {

            },

            route() {
                this.$navigate({
                    r: `plugin/ecard/mall/index/index`,
                });
            },

            checkSelectable(data) {
                if (data.is_sales === 1) {
                    return false;
                } else {
                    return true;
                }
            }

        },
        mounted: function () {
            this.loading = true;
            this.search.ecard_id = getQuery('id');
            this.getInform().then(() => {
                this.loading = false;
            }).catch(() => {
                this.loading = false;
            })
        }
    });
</script>
