<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/10/19
 * Time: 9:29
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        display: inline-block;
        width: 250px;
        margin: 0 0 20px;
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

    .table-body .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>分销商等级</span>
                <div style="float: right; margin: -5px 0">
                    <el-button type="primary" @click="$navigate({r: 'mall/share/level-edit'})" size="small">添加分销商等级</el-button>
                </div>
            </div>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small"
                          placeholder="请输入分销商等级名称搜索"
                          v-model="keyword"
                          clearable
                          @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table
                v-loading="listLoading"
                :data="list"
                border
                style="width: 100%">
                <el-table-column
                    prop="id"
                    label="ID"
                    width="80">
                </el-table-column>
                <el-table-column prop="level" label="分销商等级" width="80">
                    <template slot-scope="scope">
                        <app-ellipsis :line="1">等级{{scope.row.level}}</app-ellipsis>
                    </template>
                </el-table-column>
                <el-table-column label="分销商等级名称">
                    <template slot-scope="scope">
                        <app-ellipsis :line="1">{{scope.row.name}}</app-ellipsis>
                    </template>
                </el-table-column>
                <el-table-column label="升级条件"
                                 width="240">
                    <template slot-scope="scope">
                        <template v-if="scope.row.is_auto_level == 0">
                            <el-tag type="warning">不可自动升级</el-tag>
                        </template>
                        <template v-else>
                            <div flex="dir:left cross:center" v-if="scope.row.condition_type == 1">
                                <el-tag>{{condition_type_list[scope.row.condition_type]}}</el-tag>
                                <div style="margin-left: 5px;">
                                    <app-ellipsis :line="1">达到{{scope.row.condition}}人</app-ellipsis>
                                </div>
                            </div>
                            <div  flex="dir:left cross:center" v-if="scope.row.condition_type == 2">
                                <el-tag>{{condition_type_list[scope.row.condition_type]}}</el-tag>
                                <div style="margin-left: 5px;">
                                    <app-ellipsis :line="1">达到￥{{scope.row.condition}}</app-ellipsis>
                                </div>
                            </div>
                            <div fl flex="dir:left cross:center"ex v-if="scope.row.condition_type == 3">
                                <el-tag>{{condition_type_list[scope.row.condition_type]}}</el-tag>
                                <div style="margin-left: 5px;">
                                    <app-ellipsis :line="1">达到￥{{scope.row.condition}}</app-ellipsis>
                                </div>
                            </div>
                        </template>
                    </template>
                </el-table-column>
                <el-table-column label="一级佣金">
                    <template slot-scope="scope">
                        <app-ellipsis :line="1" v-if="scope.row.first >= 0">{{scope.row.first}}{{price_type_list[scope.row.price_type]}}</app-ellipsis>
                        <template v-else>-</template>
                    </template>
                </el-table-column>
                <el-table-column label="二级佣金">
                    <template slot-scope="scope">
                        <app-ellipsis :line="1" v-if="scope.row.second >= 0">{{scope.row.second}}{{price_type_list[scope.row.price_type]}}</app-ellipsis>
                        <template v-else>-</template>
                    </template>
                </el-table-column>
                <el-table-column label="三级佣金">
                    <template slot-scope="scope">
                        <app-ellipsis :line="1" v-if="scope.row.third >= 0">{{scope.row.third}}{{price_type_list[scope.row.price_type]}}</app-ellipsis>
                        <template v-else>-</template>
                    </template>
                </el-table-column>
                <el-table-column
                    label="启用状态"
                    width="120">
                    <template slot-scope="scope">
                        <el-switch
                            :active-value="1"
                            :inactive-value="0"
                            @change="switchStatus(scope.row)"
                            v-model="scope.row.status">
                        </el-switch>
                    </template>
                </el-table-column>
                <el-table-column
                    label="操作"
                    fixed="right"
                    width="180">
                    <template slot-scope="scope">
                        <el-button circle size="mini" type="text" @click="edit(scope.row.id)">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button circle size="mini" type="text" @click="destroy(scope.row, scope.$index)">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>

            <div  flex="dir:right" style="margin-top: 20px;">
                <el-pagination
                    hide-on-single-page
                    @current-change="pagination"
                    background
                    layout="prev, pager, next, jumper"
                    :page-count="pageCount">
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
                keyword: '',
                listLoading: false,
                page: 1,
                pageCount: 0,
                condition_type_list: {
                    1: '下线用户数',
                    2: '累计佣金',
                    3: '已提现佣金'
                },
                price_type_list: {
                    1: '%',
                    2: '元'
                }
            };
        },
        mounted: function () {
            this.getList();
        },
        methods: {
            search() {
                this.page = 1;
                this.getList();
            },

            pagination(currentPage) {
                let self = this;
                self.page = currentPage;
                self.getList();
            },
            getList() {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'mall/share/level',
                        page: self.page,
                        keyword: this.keyword
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.list = e.data.data.list;
                    self.pageCount = e.data.data.pagination.page_count;
                }).catch(e => {
                    console.log(e);
                });
            },
            edit(id) {
                if (id) {
                    navigateTo({
                        r: 'mall/share/level-edit',
                        id: id,
                    });
                } else {
                    navigateTo({
                        r: 'mall/share/level-edit',
                    });
                }
            },
            switchStatus(row) {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'mall/share/switch-status',
                    },
                    method: 'post',
                    data: {
                        id: row.id,
                    }
                }).then(e => {
                    self.listLoading = false;
                    if (e.data.code === 0) {
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                    self.getList();
                }).catch(e => {
                    console.log(e);
                });
            },
            destroy(row, index) {
                let self = this;
                self.$confirm('删除该分销商等级, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'mall/share/level-destroy',
                        },
                        method: 'post',
                        data: {
                            id: row.id,
                        }
                    }).then(e => {
                        self.listLoading = false;
                        if (e.data.code === 0) {
                            self.$message.success(e.data.msg);
                            self.list.splice(index, 1);
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        console.log(e);
                    });
                }).catch(() => {
                    self.$message.info('已取消删除')
                });
            },
        }
    });
</script>
