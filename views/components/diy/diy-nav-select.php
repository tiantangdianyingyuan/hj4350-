<template id="diy-nav-select">
    <div class="diy-nav-select">
        <el-dialog title="" :visible.sync="navDialog" top="10vh" width="45%">
            <div class="input-item">
                <el-input @keyup.enter.native="searchNav" size="small" placeholder="根据名称搜索"
                          v-model="search.keyword" clearable
                          @clear="searchNav">
                    <el-button slot="append" icon="el-icon-search" @click="searchNav"></el-button>
                </el-input>
            </div>
            <el-table ref="singleTable" v-loading="listLoading" :data="navList" height="544"
                      @selection-change="handleSelectionChange">
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="id" label="ID" width="100"></el-table-column>
                <el-table-column prop="name" label="名称" width="120"></el-table-column>
                <el-table-column prop="icon_url" label="导航图标" width="80">
                    <template slot-scope="scope">
                        <app-image width="35" height="35" mode="aspectFill" :src="scope.row.icon_url"></app-image>
                    </template>
                </el-table-column>
                <el-table-column prop="url" label="导航链接"></el-table-column>
            </el-table>
            <!--工具条 批量操作和分页-->
            <el-col :span="24" class="toolbar">
                <el-pagination
                        background
                        layout="prev, pager, next"
                        @current-change="pageChange"
                        :page-size="pagination.pageSize"
                        :total="pagination.total_count"
                        style="text-align:center;margin: 15px 0"
                        v-if="pagination">
                </el-pagination>
            </el-col>
            <div slot="footer" class="dialog-footer">
                <el-button size="small" @click="navDialog = false">取 消</el-button>
                <el-button size="small" type="primary" @click="navConfirm">确 定</el-button>
            </div>
        </el-dialog>

        <span @click="openNavDialog">
            <slot></slot>
        </span>
    </div>
</template>

<script>
    Vue.component('diy-nav-select', {
        template: '#diy-nav-select',
        props: {
            value: {
                type: Array,
                default: () => {
                    return []
                }
            },
        },
        data() {
            return {
                navList: null,
                navDialog: false,
                page: 1,
                pagination: null,
                listLoading: false,
                navSelect: [],
                search: {
                    keyword: '',
                }
            }
        },
        methods: {
            searchNav() {
                this.page = 1;
                this.getList();
            },
            handleSelectionChange(row) {
                this.navSelect = row;
            },

            navConfirm() {
                this.$emit('change', this.navSelect);
                this.closeNavDialog();
            },

            closeNavDialog() {
                this.navDialog = false;
            },
            openNavDialog() {
                if (!this.navList) {
                    this.getList();
                }
                this.navDialog = true;
                setTimeout(() => {
                    this.navSelect = [];
                    this.$refs.singleTable.clearSelection();
                });
            },
            pageChange(currentPage) {
                this.page = currentPage;
                this.getList();
            },
            getList() {
                const self = this;
                self.listLoading = true;
                request({
                    params: Object.assign({}, {
                        r: 'mall/home-nav/index',
                        keyword: self.search.keyword,
                        page: self.page,
                    }),
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    if (e.data.code === 0) {
                        self.pagination = e.data.data.pagination;
                        self.navList = e.data.data.list;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                });
            },
        },
        mounted() {
        }
    })
</script>