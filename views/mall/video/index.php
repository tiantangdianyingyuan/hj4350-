<style>
    .list-video {
        display: inline-block;
        width: 250px;
        margin: 10px;
        height: 230px;
        border: 1px solid null;
    }

    .list-video > .active {
        border: 1px solid #3399ff;
    }

    .list-video .img {
        height: 134px;
        width: 250px;
        display: block;
    }

    .list-video .end {
        padding: 20px;
        color: #303133;
        font-size: 13px;
        cursor: pointer;
        border-bottom: 1px solid rgba(0, 0, 0, .125);
    }

    .list-video .end :first-child {
        font-size: 14px;
    }

    .set-el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }

    .sort-input {
        width: 100%;
        background-color: #F3F5F6;
        height: 32px;
    }

    .sort-input span {
        height: 32px;
        width: 100%;
        line-height: 32px;
        display: inline-block;
        padding: 0 10px;
        font-size: 13px;
    }

    .sort-input .el-input__inner {
        height: 32px;
        line-height: 32px;
        background-color: #F3F5F6;
        float: left;
        padding: 0 10px;
        border: 0;
    }

    .table-body {
        padding: 20px;
        background-color: #fff;
    }
</style>
<div id="app" v-cloak>
    <el-card class="box-card" v-loading="listLoading" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>视频</span>
                <div style="float: right;margin-top: -5px">
                    <el-button type="primary" @click="$navigate({r: 'mall/video/edit'})" size="small">添加视频</el-button>
                </div>
            </div>
        </div>
        <div class="table-body">
            <div v-for="item in list" class="list-video" @mouseleave="leave" @mouseenter.stop="editModel(item)">
                <el-card :body-style="{ padding: '0px'}" :class="item.id == edit_id ? 'active':''" shadow="never">
                    <div style="position:relative">
                        <a :href="item.url" target="_blank">
                            <image class="img" :src="item.pic_url"></image>
                        </a>
                        <div v-if="item.id == edit_id" style="position:absolute;bottom:20px;right:20px">
                            <el-button type="text" class="set-el-button" size="mini" circle @click="$navigate({r: 'mall/video/edit', id:item.id})">
                                <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                    <img src="statics/img/mall/edit.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button type="text" class="set-el-button" size="mini" circle @click="destroy(item)">
                                <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                    <img src="statics/img/mall/del.png" alt="">
                                </el-tooltip>
                            </el-button>
                        </div>
                    </div>
                    <div class="end">
                        <div flex="dir:left box:first cross:center" style="padding-bottom:16px">
                            <span>标题： </span>
                            <app-ellipsis :line="1">{{item.title}}</app-ellipsis>
                        </div>
                        <div flex="dir:left box:first cross:center">
                            <span>排序： </span>
<!--                             <el-input v-if="id == item.id" v-model="sort" v-focus @keyup.enter.native="change(item)" class="sort-input"></el-input>
                            <div class="sort-input" v-else>
                                <span @click="editSort(item)">{{item.sort}}</span>
                            </div> -->

                            <div v-if="id != item.id">
                                <el-tooltip class="item" effect="dark" content="排序" placement="top">
                                    <span>{{item.sort}}</span>
                                </el-tooltip>
                                <el-button class="edit-sort" type="text" @click="editSort(item)">
                                    <img src="statics/img/mall/order/edit.png" alt="">
                                </el-button>
                            </div>
                            <div style="display: flex;align-items: center" v-else>
                                <el-input style="min-width: 70px;height: 42px;line-height: 42px;" type="number" size="mini" class="change" v-model="sort"
                                              autocomplete="off"></el-input>
                                <el-button class="change-quit" type="text" style="color: #F56C6C;padding: 0 5px" icon="el-icon-error"
                                               circle @click="quit()"></el-button>
                                <el-button class="change-success" type="text" style="margin-left: 0;color: #67C23A;padding: 0 5px"
                                           icon="el-icon-success" circle @click="change(item)">
                                </el-button>
                            </div>
                        </div>
                    </div>
                </el-card>
            </div>
            <div style="text-align: right;margin: 20px 0;">
                <el-pagination @current-change="pagination" background layout="prev, pager, next, jumper" :page-count="pageCount">
                </el-pagination>
            </div>
        </div>
    </el-card>
</div>
<script>
const app = new Vue({
    el: '#app',
    data() {
        return {
            list: [],
            page: 1,
            listLoading: false,
            article_cat_id: 1,
            pageCount: 0,
            id: 0,
            sort: 0,
            edit_id: 0,
        };
    },
    directives: {
        // 注册一个局部的自定义指令 v-focus
        focus: {
            // 指令的定义
            inserted: function(el) {
                // 聚焦元素
                el.querySelector('input').focus()
            }
        }
    },
    methods: {
        leave(){
            this.edit_id = 0;
        },

        quit() {
            this.id = 0;
        },

        editModel(item) {
            console.log(item);
            this.edit_id = item.id;
        },

        editSort(row) {
            this.id = row.id;
            this.sort = row.sort;
        },

        change(row) {
            let self = this;
            row.sort = self.sort;
            request({
                params: {
                    r: 'mall/video/edit'
                },
                method: 'post',
                data: row
            }).then(e => {
                self.btnLoading = false;
                if (e.data.code == 0) {
                    self.$message.success(e.data.msg);
                    this.id = null;
                } else {
                    self.$message.error(e.data.msg);
                }
            }).catch(e => {
                self.$message.error(e.data.msg);
                self.btnLoading = false;
            });
        },

        pagination(currentPage) {
            let self = this;
            self.page = currentPage;
            self.getList();
        },

        //删除
        destroy: function(column) {
            this.$confirm('确认删除该记录吗?', '提示', {
                type: 'warning'
            }).then(() => {
                this.listLoading = true;
                request({
                    params: {
                        r: 'mall/video/destroy'
                    },
                    data: { id: column.id },
                    method: 'post'
                }).then(e => {
                    location.reload();

                }).catch(e => {
                    this.listLoading = false;
                });

            });
        },
        //获取列表
        getList() {
            this.listLoading = true;
            request({
                params: {
                    r: 'mall/video/index',
                    page: this.page,
                },
            }).then(e => {
                if (e.data.code === 0) {
                    this.list = e.data.data.list;
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
    created() {
        this.getList();
    }
})
</script>