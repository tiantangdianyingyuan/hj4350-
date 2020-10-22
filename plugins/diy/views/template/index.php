<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/15
 * Time: 18:55
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
?>
<?php
Yii::$app->loadViewComponent("app-market", __DIR__);
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .table-body .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" flex="dir:left" style="justify-content:space-between;">
            <span>微页面</span>
            <app-market list-url="plugin/diy/mall/template/market-search"
                        edit-url="plugin/diy/mall/template/edit" type="page">
                <el-button style="margin: -5px 0" type="primary" size="small">新建微页面
                </el-button>
            </app-market>
        </div>
        <div id="NewsToolBox"></div>
        <div class="table-body">
            <el-table v-loading="listLoading" :data="list" border>
                <el-table-column label="ID" prop="id"></el-table-column>
                <el-table-column prop="name" label="名称">
                    <template slot-scope="scope">
                        <span>{{scope.row.name}}</span>
                        <el-tag v-if="scope.row.is_home_page == 1" effect="dark" size="mini">店铺首页</el-tag>
                    </template>
                </el-table-column>
                <!--<el-table-column prop="goodsCount" label="商品数"></el-table-column>-->
                <el-table-column :formatter="formatterCount" label="浏览人数/浏览人次"></el-table-column>
                <el-table-column prop="created_at" label="创建时间"></el-table-column>
                <el-table-column fixed="right" width="250" label="操作">
                    <template slot-scope="scope">
                        <el-button type="text" @click="edit(scope.row)"
                                   size="small" circle>
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button type="text" @click="noticePoster(scope.row)"
                                   size="small" circle>
                            <el-tooltip class="item" effect="dark" content="推广" placement="top">
                                <img src="statics/img/mall/notice.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button v-if="scope.row.is_home_page == 1" type="text" @click="changeHome(scope.row, 0)"
                                   size="small" circle>
                            <el-tooltip class="item" effect="dark" content="取消设为首页" placement="top">
                                <img src="statics/img/mall/form_icon_home_grey.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button v-else type="text" @click="changeHome(scope.row, 1)" size="small" circle>
                            <el-tooltip class="item" effect="dark" content="设为首页" placement="top">
                                <img src="statics/img/mall/list_icon_home.png" alt="">
                            </el-tooltip>
                        </el-button>

                        <el-button v-if="scope.row.is_home_page != 1" type="text"
                                   @click="destroy(scope.row,scope.$index)"
                                   size="small" circle>
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>

                    </template>
                </el-table-column>
            </el-table>
        </div>
        <!-- 推广 -->
        <el-dialog title="推广" :visible.sync="bgVisible" width="956px">
            <div flex="dir:left" v-loading="bgLoading">
                <div style="height: 480px;width: 270px">
                    <img style="height: 100%;width: 100%;display: block" :src="bgPosterForm.picUrl" alt="">
                </div>
                <el-form label-position="top" label-width="80px" style="margin-left: 50px">
                    <el-form-item label="复制小程序路径">
                        <el-input size="small" style="width: 500px" v-model="bgPosterForm.text" disabled>
                            <template slot="append">
                                <el-button type="primary" @click="copyInput">复制</el-button>
                            </template>
                        </el-input>
                        <div>
                            <a style="color:#409EFF;text-decoration:none"
                               :href="bgPosterForm.picUrl"
                               :download="downloadText">下载海报</a>
                        </div>
                    </el-form-item>
                </el-form>
            </div>
        </el-dialog>

        <!--工具条 批量操作和分页-->
        <el-col  flex="dir:right" style="margin: 20px;">
            <el-pagination
                    background
                    hide-on-single-page
                    layout="prev, pager, next, jumper"
                    @current-change="pageChange"
                    :page-size="pagination.pageSize"
                    :total="pagination.total_count"
                    >
            </el-pagination>
        </el-col>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                listLoading: false,
                list: [],
                page: 1,
                pagination: {},

                bgVisible: false,
                bgLoading: false,
                bgPosterForm: {
                    id: '',
                    text: '',
                    picUrl: '',
                    name: '',
                },
            };
        },
        created() {
            this.syncTemplateType();
        },
        mounted() {
            this.getList();
        },
        computed: {
            downloadText() {
                return this.bgPosterForm.name + '-' + this.bgPosterForm.id;
            }
        },

        methods: {
            formatterCount(column) {
                return column['userCount'] + `/` + column['accessCount'];
            },
            copyText(text) {
                var textarea = document.createElement("textarea"); //创建input对象
                var toolBoxwrap = document.getElementById('NewsToolBox'); //将文本框插入到NewsToolBox这个之后
                toolBoxwrap.appendChild(textarea); //添加元素
                textarea.value = text;
                textarea.focus();
                if (textarea.setSelectionRange) {
                    textarea.setSelectionRange(0, textarea.value.length); //获取光标起始位置到结束位置
                } else {
                    textarea.select();
                }
                try {
                    var flag = document.execCommand("copy"); //执行复制
                } catch (eo) {
                    var flag = false;
                }
                toolBoxwrap.removeChild(textarea); //删除元素
                return flag;
            },

            noticePoster(column) {
                let params = '';
                if (column.is_home_page == 0) {
                    params = '?page_id=' + column.id;
                }
                this.bgPosterForm = {
                    id: column.id,
                    text: '/pages/index/index' + params,
                    picUrl: '',
                    name: column.name,
                }
                this.bgVisible = true;
                this.bgLoading = true;
                request({
                    params: {
                        r: 'plugin/diy/mall/template/poster',
                        page_id: column.is_home_page == 0 ? this.bgPosterForm.id : 0,
                    },
                    method: 'get'
                }).then(e => {
                    this.bgLoading = false;
                    if (e.data.code === 0) {
                        this.bgPosterForm.picUrl = e.data.data.pic_url;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.bgLoading = false;
                });
            },

            copyInput() {
                if (this.copyText(this.bgPosterForm.text)) {
                    this.$message.success('复制成功');
                } else {
                    this.$message.error('复制失败');
                }
            },

            edit(column) {
                navigateTo({
                    r: 'plugin/diy/mall/template/edit',
                    id: column.id
                });
            },
            changeHome(column, is_home_page) {
                request({
                    params: {
                        r: 'plugin/diy/mall/template/change-home-status',
                        id: column.id
                    },
                    method: 'post',
                    data: {
                        id: column.id,
                        is_home_page
                    }
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code === 0) {
                        this.list.forEach(item => {
                            item.is_home_page = 0;
                        });
                        column.is_home_page = is_home_page;
                        this.$message.success(e.data.msg);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.listLoading = false;
                });
            },
            destroy(column, index) {
                this.$confirm('此操作将删除该微页面, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/diy/mall/template/destroy',
                            id: column.id
                        },
                        method: 'get'
                    }).then(e => {
                        this.listLoading = false;
                        if (e.data.code === 0) {
                            this.$message.success(e.data.msg);
                            this.list.splice(index, 1);
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        this.listLoading = false;
                    });
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '已取消删除'
                    });
                });
            },
            pageChange(page) {
                this.page = page;
                this.getList();
            },
            getList() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'plugin/diy/mall/template/index',
                        page: this.page,
                    }
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    }
                }).catch(e => {
                    this.listLoading = false;
                });
            },
            syncTemplateType() {
                this.$request({
                    params: {
                        r: 'plugin/diy/mall/template/sync-template-type',
                    },
                }).then(e => {
                }).catch(e => {
                });
            }
        }
    });
</script>
