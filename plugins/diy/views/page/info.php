<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/23
 * Time: 15:09
 */
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>表单提交信息</span>
            <app-export-dialog action_url='index.php?r=plugin/diy/mall/page/info' style="float: right;margin-top: -5px"
                               :field_list='export_list' :params="search"></app-export-dialog>
        </div>
        <div style="padding: 20px;background-color: #fff">
            <el-date-picker size="small" style="margin-bottom: 20px;" @change="changeTime" v-model="time" type="datetimerange" value-format="yyyy-MM-dd HH:mm:ss" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期">
            </el-date-picker>
            <el-table v-loading="loading" border :data="list" style="margin-bottom: 15px;">
                <el-table-column prop="user_id" label="用户ID" width="150px"></el-table-column>
                <el-table-column prop="nikename" label="用户信息">
                    <template slot-scope="scope">
                        <app-image mode="aspectFill" style="float: left;margin-right: 8px" :src="scope.row.avatar"></app-image>
                        <div>{{scope.row.nickname}}</div>
                    </template>
                </el-table-column>
                <el-table-column prop="created_at" label="提交时间">
                </el-table-column>
                <el-table-column prop="form_data" label="表单信息">
                    <template slot-scope="scope">
                        <el-tooltip class="item" effect="dark" content="查看详情" placement="top">
                            <img style="cursor: pointer" @click="toDetail(scope.row.form_data)" src="statics/img/mall/order/detail.png"
                                 alt="">
                        </el-tooltip>
                        <el-tooltip class="item" effect="dark" content="删除" placement="top">
                            <img style="cursor: pointer;margin-left: 10px;" @click="toDelete(scope.row.id)" src="statics/img/mall/del.png"
                                 alt="">
                        </el-tooltip>
                    </template>
                </el-table-column>
            </el-table>
            <div  flex="dir:right">
                <div></div>
                <div>
                    <el-pagination
                            background
                            :page-size="pagination.pageSize"
                            @current-change="pageChange"
                            layout="prev, pager, next, jumper" :current-page="pagination.current_page"
                            :total="pagination.totalCount">
                    </el-pagination>
                </div>
            </div>
        </div>
    </el-card>
    <el-dialog title="表单信息" :visible.sync="dialogTableVisible">
        <el-table :data="detail">
            <el-table-column property="name" label="标签名称" width="240"></el-table-column>
            <el-table-column property="value" label="填写内容">
                <template slot-scope="scope">
                    <template v-if="scope.row.key=='img_upload' && scope.row.value && [`,`,``].indexOf(scope.row.value.toString()) === -1">
                        <img v-for="img in scope.row.value"
                             @click="toLook(img)"
                             style="height: 100px;width: 100px;cursor: pointer"
                             :src="img"
                             alt=""
                        >
                    </template>
                    <div v-else>{{scope.row.value}}</div>
                </template>
            </el-table-column>
        </el-table>
        <el-dialog width="50%" :visible.sync="innerVisible" append-to-body>
            <img style="width: 100%" :src="img" alt="">
        </el-dialog>
    </el-dialog>
</div>
<script>
    new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                innerVisible: false,
                list: [],
                img: '',
                detail: [],
                time: [],
                page: 1,
                pagination: [],
                dialogTableVisible: false,
                export_list: [],
                search: {
                    date_start: '',
                    date_end: ''
                }
            };
        },
        created() {
            this.loadData();
        },
        methods: {
            pageChange(e) {
                this.page = e;
                this.loadData();
            },

            changeTime(page) {
                this.page = 1;
                this.loadData();
            },

            toDetail(e) {
                this.dialogTableVisible = !this.dialogTableVisible;
                this.detail = e;
            },

            toLook(e) {
                this.innerVisible = !this.innerVisible;
                this.img = e;
            },

            toDelete(res) {
                let id = res;
                this.$confirm('是否删除该条记录？', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning',
                    center: true,
                    beforeClose: (action, instance, done) => {
                        if (action === 'confirm') {
                            instance.confirmButtonLoading = true;
                            instance.confirmButtonText = '执行中...';
                            request({
                                params: {
                                    r: 'plugin/diy/mall/page/info-del',
                                },
                                data:{
                                    id: id,
                                },
                                method: 'post'
                            }).then(e => {
                                done();
                                instance.confirmButtonLoading = false;
                                if (e.data.code == 0) {
                                    this.loadData();
                                } else {
                                    this.$message.error(e.data.msg);
                                }
                            }).catch(e => {
                                done();
                                instance.confirmButtonLoading = false;
                                this.$message.error(e.data.msg);
                            });
                        } else {
                            done();
                        }
                    }
                }).then(() => {
                }).catch(e => {
                    this.$message({
                        type: 'info',
                        message: '取消了操作'
                    });
                });
            },

            loadData() {
                this.loading = true;
                if(this.time) {
                    this.search.date_start = this.time[0];
                    this.search.date_end = this.time[1];
                }else {
                    this.search.date_start = '';
                    this.search.date_end = '';
                }
                this.$request({
                    params: {
                        r: 'plugin/diy/mall/page/info',
                        page: this.page,
                        date_start: this.search.date_start,
                        date_end: this.search.date_end,
                    }
                }).then(response => {
                    this.loading = false;
                    if (response.data.code === 0) {
                        this.list = response.data.data.list;
                        this.pagination = response.data.data.pagination;
                        this.export_list = response.data.data.export_list;
                    }
                }).catch(e => {
                });
            }
        },
    });
</script>