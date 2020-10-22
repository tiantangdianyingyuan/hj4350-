<style>
    .app-market .box {
        width: calc(14px + 680px + 210px);
        height: calc(57px + 340px + 20px);
        flex-wrap: wrap;
        overflow-x: hidden;
    }

    .app-market .box .box-item {
        width: 210px;
        height: 340px;
        background: rgba(255, 255, 255, 1);
        border: 2px solid rgba(238, 238, 238, 1);
        border-radius: 4px;
        margin: 0 7px 21px;
        cursor: pointer;
        position: relative;
    }

    .app-market .show-img {
        width: 100%;
        height: 295px;
        overflow: hidden;
        background-size: cover;
        background-position: 0 0;
        background-repeat: no-repeat;
    }

    .app-market .dialog-img {
        background-color: rgba(0, 0, 0, .4);
        position: absolute;
        left: 0;
        top: 0;
        z-index: 10;
        width: 100%;
        height: 295px;
        display: none;
    }

    .app-market .dialog-img .choose-btn {
        cursor: pointer;
        border-radius: 6px;
        height: 40px;
        line-height: 38px;
        width: 120px;
        margin: 10px auto;
        text-align: center;
        border: 1px solid #fff;
        color: #fff;
        font-size: 16px;
    }

    .app-market .item-name {
        font-size: 16px;
        font-weight: 400;
        color: #666666;
        display: block;
        white-space: nowrap;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .box-item:hover .dialog-img {
        display: flex;
    }

    .app-market .choose-btn.use {
        border: 1px solid #3399ff;
        background-color: #3399ff;
    }
</style>
<template id="app-market">
    <div class="app-market">
        <el-dialog title="选择模板" :visible.sync="markDialog" width="1000px">
            <div v-loading="loading">
                <div class="box" flex="dir:left">
                    <div class="box-item" flex="dir:top cross:center main:center"
                         @click="$navigate({r:editUrl})">
                        <img src="<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl() ?>/images/icon_add.png"
                             height="56px"
                             width="57px">
                        <span style="margin-top: 15px;font-size:16px;font-weight:400; color:rgba(102,102,102,1);">使用空白模板</span>
                    </div>
                    <div class="box-item" v-for="item in list">
                        <div class="dialog-img" flex="dir:top cross:center main:center">
                            <div @click="toUse(item)" v-if="item.is_use" class="choose-btn use"
                                 :loading="useLoading">加载模板
                            </div>
                            <div @click="show(item)" class="choose-btn">预览模板</div>
                    </div>
                    <div class="show-img" :style="{backgroundImage: 'url('+item.pics[0]+')'}"
                         v-if="item.pics.length >= 1"></div>
                    <div style="padding: 10px">
                        <div class="item-name">{{item.name}}</div>
                    </div>
                </div>
            </div>
            <div flex="dir:left">
                <!--工具条 批量操作和分页-->
                <el-col :span="24" class="toolbar">
                    <el-pagination
                            hide-on-single-page
                            background
                            layout="prev, pager, next, jumper"
                            @current-change="pageChange"
                            :page-size="pagination.pageSize"
                            :total="pagination.total_count"
                            style="float:right;margin:15px 0"
                            v-if="pagination">
                    </el-pagination>
                </el-col>
            </div>
            </div>

        </el-dialog>

        <!-- 预览 -->
        <el-dialog title="手机端预览" :visible.sync="tplDialog" width="30%" v-if="template">
            <div style="height: 600px;overflow-y: auto;text-align: center">
                <img style="width: 375px;" :src="template.pics[0]" alt="">
            </div>
            <span slot="footer" class="dialog-footer" flex="dir:right cross:center">
            <el-button type="primary" @click="toUse(template)" class="set-el-button" v-if="template.is_use"
                       size="small">加载模板</el-button>
            <el-button size="small" @click="tplDialog = false;template=null"
                       style="margin-right: 10px;">取 消</el-button>
        </span>
        </el-dialog>

        <!-- 加载进行时 -->
        <el-dialog class="loading-dialog" flex="cross:center main:center" title="提示" width="350px"
                   :visible.sync="useLoading">
            <div style="text-align: center;font-size: 16px;"><i
                        style="font-size: 20px;margin-right: 10px;color: #3399ff"
                        class="el-icon-loading"></i>正在加载中，请稍后...
            </div>
        </el-dialog>

        <div @click="openDialog">
            <slot></slot>
        </div>

    </div>
</template>

<script>
    Vue.component('app-market', {
        template: '#app-market',
        props: {
            type: {
                type: String,
                default: '',
            },
            listUrl: '',
            editUrl: '',
        },
        data() {
            return {
                markDialog: false,
                loading: false,
                page: 1,
                pagination: null,
                list: [],
                tplDialog: false,
                template: null,
                useLoading: false,
            };
        },
        mounted() {
            this.getList();
        },
        methods: {
            load(item) {
                this.useLoading = true;
                request({
                    params: {
                        r: 'plugin/diy/mall/market/loading',
                        template_id: item.template_id,
                        type: this.type,
                    }
                }).then(response => {
                    this.useLoading = false;
                    if (response.data.code == 0) {
                        this.$confirm('模板加载成功，是否前往编辑？', '提示', {
                            confirmButtonText: '确认',
                            cancelButtonText: '取消',
                            type: 'success'
                        }).then(() => {
                            if (item.type == 'diy') {
                                navigateTo({
                                    r: this.editUrl,
                                    id: response.data.data.id
                                }, true);
                            } else {
                                navigateTo({
                                    r: 'mall/home-page/setting',
                                }, true);
                            }
                        })
                    } else {
                        this.$alert(response.data.msg, '提示', {
                            type: 'warning'
                        });
                    }
                }).catch(response => {
                    this.useLoading = false;
                });

            },
            toUse(item) {
                if (!item.is_use) {
                    this.$alert('没有该模板的使用权限，请联系管理员', '提示', {
                        type: 'warning'
                    });
                    return;
                } else {
                    if (item.type == 'home') {
                        this.$confirm('选择加载的是首页布局的模板，会覆盖当前的首页布局们是否确定加载？', '提示', {
                            confirmButtonText: '确认',
                            cancelButtonText: '取消',
                            type: 'warning'
                        }).then(() => {
                            this.load(item);
                        })
                    } else {
                        this.load(item);
                    }
                }
            },

            show(item) {
                this.tplDialog = true;
                this.template = item;
            },

            openDialog() {
                this.getList();
                this.markDialog = true;
            },
            pageChange(page) {
                this.page = page;
                this.getList();
            },
            getList() {
                this.loading = true;
                request({
                    params: {
                        r: this.listUrl,
                        page: this.page,
                    }
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            }
        },
    });
</script>
