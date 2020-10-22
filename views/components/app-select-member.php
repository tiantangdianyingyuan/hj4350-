<template id="app-select-member">
    <div class="app-select-member">
        <el-dialog title="选择会员等级" :visible.sync="memberDialog" top="10vh" width="750">
            <div class="input-item" style="margin-bottom: 12px">
                <el-input @keyup.enter.native="searchMember" size="small" placeholder="请输入会员等级名称"
                          v-model="search.keyword" clearable
                          @clear="searchMember">
                    <el-button slot="append" icon="el-icon-search" @click="searchMember"></el-button>
                </el-input>
            </div>
            <div flex="dir:top" v-loading="listLoading">
                <div v-for="member of memberList" style="padding: 2px 0">
                    <el-radio v-model="form.member_id" :label="member.id">
                        <span style="display:inline-block;max-width: 45vw;text-overflow: ellipsis;word-break: break-all;overflow:hidden;white-space: nowrap">{{member.name}}</span>
                    </el-radio>
                </div>
            </div>
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
                <el-button size="small" @click="memberDialog = false">取 消</el-button>
                <el-button size="small" type="primary" @click="memberConfirm">确 定</el-button>
            </div>
        </el-dialog>

        <span @click="openMemberDialog">
            <slot></slot>
        </span>
    </div>
</template>

<script>
    Vue.component('app-select-member', {
        template: '#app-select-member',
        props: {
            value: {
                type: String,
                default: () => {
                    return ""
                }
            },
        },
        data() {
            return {
                listLoading: false,
                search: {
                    keyword: ''
                },
                form: {
                    member_id: this.value,
                },
                memberList: null,
                memberDialog: false,
                page: 1,
                pagination: null,
            }
        },
        methods: {
            openMemberDialog() {
                if (!this.memberList || !this.memberList.length) {
                    this.getList();
                }
                this.memberDialog = true;
            },
            closeMemberDialog() {
                this.memberDialog = false;
            },
            memberConfirm() {
                let listItem = null;
                for (let i = 0; i < this.memberList.length; i++) {
                    if (this.memberList[i]['id'] == this.form.member_id) {
                        listItem = this.memberList[i];
                        break;
                    }
                }
                this.$emit('change', listItem);
                this.$emit('input', this.form.member_id);
                this.closeMemberDialog();
            },
            pageChange(page) {
                this.page = page;
                this.getList();
            },
            searchMember() {
                this.page = 1;
                this.getList();
            },
            getList() {
                const self = this;
                self.listLoading = true;

                request({
                    params: Object.assign({}, {
                        r: 'mall/mall-member/index',
                        keyword: self.search.keyword,
                        page: self.page,
                    }),
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    if (e.data.code === 0) {
                        self.pagination = e.data.data.pagination;
                        self.memberList = e.data.data.list;
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