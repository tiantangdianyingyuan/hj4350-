<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        display: inline-block;
        width: 250px;
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

    .app-goods-list .app-goods-cat-list {
        border: 1px solid #E8EAEE;
        border-radius: 5px;
        margin-top: -5px;
        padding: 10px;
    }

    .app-goods-list .video-play {
        position: absolute;
        top: 28%;
        cursor: pointer;
        left: 40%;
    }
</style>
<div id="app" v-cloak>
    <div class="app-goods-list">
        <el-card v-loading="listLoading" class="box-card" shadow="never" style="border:0"
                 body-style="background-color: #f3f3f3;padding: 10px 0 0;">
            <div slot="header">
                <span>发圈素材管理</span>
                <el-button style="float: right; margin: -5px 0" type="primary" size="small" @click="edit">添加发圈素材
                </el-button>
            </div>
            <div class="table-body">
                <el-form size="small" :inline="true" :model="search">
                    <el-form-item>
                        <span style="margin-right:10px">素材类型</span>
                        <el-select style="width:150px" @change="toSearch" v-model="search.type" placeholder="请选择">
                            <el-option
                                    v-for="item in options"
                                    :key="item.label"
                                    :label="item.label"
                                    :value="item.value">
                            </el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item>
                        <span style="margin-right:10px">添加时间</span>
                        <el-date-picker
                                @change="toSearch"
                                v-model="search.time"
                                type="datetimerange"
                                value-format="yyyy-MM-dd HH:mm:ss"
                                range-separator="至"
                                start-placeholder="开始日期"
                                end-placeholder="结束日期">
                        </el-date-picker>
                    </el-form-item>
                    <el-form-item>
                        <div class="input-item">
                            <el-input @keyup.enter.native="toSearch" size="small" placeholder="请输入文案关键词搜索"
                                      v-model="search.keyword" clearable @clear="toSearch">
                                <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                            </el-input>
                        </div>
                    </el-form-item>
                    <el-form-item v-if="choose_list.length" style="margin-bottom: 0">
                        <el-button @click="allDelete" type="primary">批量删除</el-button>
                    </el-form-item>
                </el-form>

                <el-table :data="list" border style="width: 100%;margin-bottom: 15px"
                          @selection-change="handleSelectionChange" @sort-change="sortChangeList">
                    <el-table-column type="selection" align="center" width="60"></el-table-column>
                    <el-table-column prop="goods_id" label="素材类型" width="100">
                        <template slot-scope="scope">
                            <span v-if="scope.row.goods_id">商品</span>
                            <span v-else>动态</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="share_text" label="发圈文案" width="300">
                        <template slot-scope="scope">
                            <span>{{scope.row.share_text}}</span>
                            <img @click="setShareText(scope.$index,scope.row)" style="cursor: pointer"
                                 src="statics/img/mall/order/edit.png" alt="">
                        </template>
                    </el-table-column>
                    <el-table-column prop="share_pic" label="图片视频" width="350">
                        <template slot-scope="scope">
                            <div v-if="scope.row.goods_id==0 && scope.row.material_video_url"
                                 style="position: relative;width:328px">
                                <video height="166px" width="328px" :src="scope.row.material_video_url">
                                    您的浏览器不支持 video 标签。
                                </video>
                                <div class="video-play" @click="previewShow(scope.row.material_video_url,'video')">
                                    <el-image
                                            src="<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl() ?>/img/video-play.png"
                                    ></el-image>
                                </div>
                            </div>
                            <div v-else flex="dir:left" style="flex-wrap: wrap">
                                <el-image v-for="(i,k) in scope.row.share_pic"
                                          :src="i.pic_url"
                                          :key="k"
                                          :preview-src-list="[i.pic_url]"
                                          style="cursor:pointer;height:90px;width:90px;margin:9px"
                                ></el-image>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="商品信息" width="220">
                        <template slot-scope="scope">
                            <div flex="box:first" v-if="scope.row.goods_id">
                                <div style="padding-right: 10px">
                                    <app-image mode="aspectFill" :src="scope.row.goodsWarehouse.cover_pic"></app-image>
                                </div>
                                <div flex="cross:center">
                                    <div v-line-clamp="1">{{scope.row.goodsWarehouse.name}}</div>
                                </div>
                            </div>
                        </template>
                    </el-table-column>

                    <el-table-column prop="material_sort" width="150" label="排序" sortable="false">
                        <template slot-scope="scope">
                            <div v-if="sort_goods_id != scope.row.id" flex="dir:left cross:center">
                                <span>{{scope.row.material_sort}}</span>
                                <el-button class="edit-sort" type="text" @click="editSort(scope.row)">
                                    <img src="statics/img/mall/order/edit.png" alt="">
                                </el-button>
                            </div>
                            <div style="display: flex;align-items: center" v-else>
                                <el-input style="min-width: 70px" type="number" size="mini" class="change"
                                          v-model="material_sort"
                                          autocomplete="off"></el-input>
                                <el-button class="change-quit" type="text" style="color: #F56C6C;padding: 0 5px"
                                           icon="el-icon-error"
                                           circle @click="quit"></el-button>
                                <el-button class="change-success" type="text"
                                           style="margin-left: 0;color: #67C23A;padding: 0 5px"
                                           icon="el-icon-success" circle
                                           @click="changeSortSubmit(scope.$index,scope.row)">
                                </el-button>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="is_top" label="是否置顶" width="80">
                        <template slot-scope="scope">
                            <el-switch
                                    @change="editAlone(scope.row)"
                                    v-model="scope.row.is_top"
                                    :active-value="1"
                                    :inactive-value="0">
                            </el-switch>
                        </template>
                    </el-table-column>
                    <el-table-column prop="status" label="状态" width="80">
                        <template slot-scope="scope">
                            <el-switch
                                    @change="editAlone(scope.row)"
                                    v-model="scope.row.status"
                                    :active-value="1"
                                    :inactive-value="0">
                            </el-switch>
                        </template>
                    </el-table-column>
                    <el-table-column prop="plugins.created_at" label="添加时间" width="180"></el-table-column>
                    <el-table-column label="操作" width="200" fixed="right">
                        <template slot-scope="scope">
                            <el-button @click="edit(scope.row)" type="text" circle size="mini">
                                <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                    <img src="statics/img/mall/edit.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button @click="destroy(scope.row, scope.$index)" type="text" circle size="mini">
                                <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                    <img src="statics/img/mall/del.png" alt="">
                                </el-tooltip>
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>
                <div flex="dir:right" style="margin-top: 20px;">
                    <el-pagination
                        @current-change="pagination"
                        hide-on-single-page
                        background
                        :current-page="current_page"
                        layout="prev, pager, next, jumper"
                        :page-count="pageCount">
                    </el-pagination>
                </div>
            </div>
        </el-card>

        <!-- model -->
        <el-dialog title="预览" :visible.sync="previewVisible" width="850px">
            <div v-if="previewData.video" style="text-align:center">
                <video height="360px" width="750px" :src="previewData.video" controls="controls" autoplay>
                    您的浏览器不支持 video 标签。
                </video>
            </div>
            <div v-else>
                <el-image :src="previewData.pic_list"></el-image>
            </div>
        </el-dialog>

        <el-dialog title="编辑发圈文案" :visible.sync="materialFormVisible" width="30%">
            <el-form :model="materialForm" label-width="80px" :rules="materialFormRules" ref="materialForm">
                <el-form-item label="文案内容" prop="share_text">
                    <el-input type="textarea" :autosize="{minRows:12,maxRows:12}" v-model="materialForm.share_text"
                              autocomplete="off"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button type="primary" :loading="btnLoading" @click="materialSubmit" size="medium">确 定</el-button>
            </div>
        </el-dialog>
        <!-- model -->
        <el-dialog title="编辑排序" :visible.sync="sortFormVisible" width="30%">
            <el-form :model="sortForm" label-width="80px" :rules="sortFormRules" ref="sortForm">
                <el-form-item label="排序" prop="material_sort">
                    <el-input v-model="sortForm.material_sort" autocomplete="off"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button type="primary" :loading="btnLoading" @click="sortSubmit" size="medium">确 定</el-button>
            </div>
        </el-dialog>
    </div>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                sort_goods_id: 0,
                id: 0,
                material_sort: '',

                options: [{
                    value: '',
                    label: '全部'
                }, {
                    value: 'goods',
                    label: '商品'
                }, {

                    value: 'dynamic',
                    label: '动态'
                }],
                search: {
                    keyword: '',
                    time: [],
                    type: '',
                    sort: 7,
                },
                list: [],
                listLoading: false,
                page: 1,
                pageCount: 0,
                current_page: 1,

                btnLoading: false,

                choose_list: [],

                /* MODLE */
                previewVisible: false,
                previewData: {},

                materialFormVisible: false,
                materialForm: {},
                materialFormRules: {
                    share_text: [
                        {required: true, message: '文案不能为空', trigger: 'blur'},
                    ],
                },
                sortFormVisible: false,
                sortForm: {},
                sortFormRules: {
                    material_sort: [
                        {required: true, message: '排序不能为空', trigger: 'blur'},
                    ],
                },
                /* url */
                goods_url: 'plugin/quick_share/mall/goods/index',
                edit_goods_url: 'plugin/quick_share/mall/goods/edit',
                destroy_goods_url: 'plugin/quick_share/mall/goods/destroy',
                batch_destroy_goods_url: 'plugin/quick_share/mall/goods/batch-destroy',
                edit_alone: 'plugin/quick_share/mall/goods/edit-alone',
            };
        },
        created() {
            if (getQuery('page') > 1) {
                this.page = getQuery('page');
            }
            this.getList();
        },
        methods: {
            changeSortSubmit(index, row) {
                this.sortForm = {
                    index: index,
                    material_sort: this.material_sort,
                    id: row.id
                };
                this.sortSubmit();
            },
            quit() {
                this.sort_goods_id = null;
            },
            editSort(row) {
                this.sort_goods_id = row.id;
                this.material_sort = row.material_sort;
            },

            sortChangeList(row) {
                if (row.prop === 'material_sort') {
                    switch (row.order) {
                        case 'ascending':
                            this.search.sort = 3;
                            break;
                        case 'descending':
                            this.search.sort = 4;
                            break;
                        default:
                            this.search.sort = 7;
                            break;
                    }
                    this.getList();
                }
            },
            previewShow(value, page) {
                this.previewData = {video: '', pic: ''};
                this.previewData[page] = value;
                this.previewVisible = true;
            },

            /* model */
            changeSort(index, column) {
                this.sortForm = {
                    index: index,
                    material_sort: column.material_sort,
                    id: column.id
                };
                this.sortFormVisible = true;
            },
            sortSubmit() {
                //this.$refs.sortForm.validate((valid) => {
                //    if (valid) {
                        const data = Object.assign({}, this.list[this.sortForm.index], {material_sort: this.sortForm.material_sort});
                        this.editAlone(data, () => {
                            this.sortFormVisible = false;
                            this.list[this.sortForm.index].material_sort = this.sortForm.material_sort;
                            this.quit();
                        });
                //   }
                //});
            },

            setShareText(index, column) {
                this.materialForm = {
                    index: index,
                    share_text: column.share_text,
                    id: column.id
                };
                this.materialFormVisible = true;
            },
            materialSubmit() {
                this.$refs.materialForm.validate((valid) => {
                    if (valid) {
                        const data = Object.assign({}, this.list[this.materialForm.index], {share_text: this.materialForm.share_text});
                        this.editAlone(data, () => {
                            this.materialFormVisible = false;
                            this.list[this.materialForm.index].share_text = this.materialForm.share_text;
                        });
                    }
                });
            },

            /* Search */
            toSearch() {
                this.page = 1;
                this.dialogVisible = false;
                this.getList();
            },

            pagination(currentPage) {
                this.page = currentPage;
                this.getList();
            },

            edit(row) {
                if (row.id) {
                    navigateTo({
                        r: this.edit_goods_url,
                        id: row.id,
                        page: this.page,
                    });

                } else {
                    navigateTo({
                        r: this.edit_goods_url,
                        page: this.page
                    });
                }
            },
            handleSelectionChange(val) {
                let self = this;
                self.choose_list = [];
                val.forEach(function (item) {
                    self.choose_list.push(item.id);
                })
            },

            getList() {
                const self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: self.goods_url,
                        page: self.page,
                        search: self.search,
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.list = e.data.data.list;
                    self.pageCount = e.data.data.pagination.page_count;
                    self.current_page = e.data.data.pagination.current_page;
                });
            },

            destroy(row, index) {
                const self = this;
                self.$confirm('删除该条数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: self.destroy_goods_url,
                        },
                        method: 'post',
                        data: {
                            id: row.id,
                        }
                    }).then(e => {
                        self.listLoading = false;
                        if (e.data.code === 0) {
                            self.list.splice(index, 1);
                            self.$message.success(e.data.msg);
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    })
                }).catch(() => {
                    self.$message.info('已取消删除')
                });
            },

            allDelete() {
                const self = this;
                self.$confirm('确认删除, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    request({
                        params: {
                            r: self.batch_destroy_goods_url
                        },
                        data: {
                            batch_ids: this.choose_list
                        },
                        method: 'post'
                    }).then(e => {
                        if (e.data.code === 0) {
                            self.$message.success(e.data.msg);
                            self.getList();
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    });
                }).catch(() => {
                    self.$message.info('已取消删除')
                });
            },

            editAlone(row, success = function () {
            }) {
                const self = this;
                self.btnLoading = true;
                request({
                    params: {
                        r: self.edit_alone
                    },
                    data: {
                        id: row.id,
                        material_sort: row.material_sort,
                        status: row.status,
                        is_top: row.is_top,
                        share_text: row.share_text
                    },
                    method: 'post'
                }).then(e => {
                    self.btnLoading = false;
                    if (e.data.code === 0) {
                        success();
                        self.$message.success(e.data.msg);
                        if (row.is_top) {
                            self.getList();
                        }
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(() => {
                    self.btnLoading = false;
                });
            },
        },
    });
</script>
