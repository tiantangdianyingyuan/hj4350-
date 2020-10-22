<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>

<style>
</style>

<div id="app" v-cloak>
    <el-card class="box-card">
        <div slot="header">
            <div style="height: 32px;line-height: 32px">
                <span>页面转发设置</span>
            </div>
        </div>
        <el-table
                v-loading="tableLoading"
                :data="list"
                border
                style="width: 100%;margin-bottom: 15px">
<!--            <el-table-column type="selection" width="55"></el-table-column>-->
            <el-table-column prop="name" label="页面名称" width="150"></el-table-column>
            <el-table-column width="120" prop="pic_url" label="自定义转发图片">
                <template slot-scope="scope">
                    <app-image mode="aspectFill" :src="scope.row.pic_url"></app-image>
                </template>
            </el-table-column>
            <el-table-column prop="title" label="自定义转发标题"></el-table-column>
<!--            <el-table-column prop="url" label="转发链接"></el-table-column>-->
            <el-table-column
                    label="操作"
                    width="220">
                <template slot-scope="scope">
                    <el-button @click="edit(scope.$index)" type="primary" plain size="mini">编辑</el-button>
                </template>
            </el-table-column>
        </el-table>
        <div flex="box:last cross:center" style="margin-top: 20px;">
            <div>
<!--                <el-button plain type="primary" size="small">批量操作</el-button>-->
                <el-button :loading="btnLoading" @click="store" plain type="primary" size="small">保存</el-button>
            </div>
            <div>
<!--                <el-pagination-->
<!--                        v-if="pageCount > 0"-->
<!--                        @current-change="pagination"-->
<!--                        background-->
<!--                        layout="prev, pager, next"-->
<!--                        :page-count="pageCount">-->
<!--                </el-pagination>-->
            </div>
        </div>
    </el-card>
    <el-dialog title="编辑" :visible.sync="dialogFormVisible">
        <el-form :model="form" label-width="90px" size="small">
            <el-form-item label="转发标题">
                <el-input v-model="form.title" autocomplete="off"></el-input>
            </el-form-item>
<!--            <el-form-item label="跳转链接">-->
<!--                <el-input v-model="form.page_url" autocomplete="off"></el-input>-->
<!--            </el-form-item>-->
            <el-form-item label="转发图片">
                <div style="display: block">
                    <app-attachment :multiple="false" :max="1" v-model="form.pic_url">
                        <el-tooltip class="item"
                                    effect="dark"
                                    content="建议尺寸:325 * 325"
                                    placement="top">
                            <el-button size="mini">选择文件</el-button>
                        </el-tooltip>
                    </app-attachment>
                    <app-image mode="aspectFill" width='80px' height='80px' :src="form.pic_url">
                    </app-image>
                </div>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button @click="dialogFormVisible = false">取 消</el-button>
            <el-button type="primary" @click="dialogFormVisible = false">确定</el-button>
        </div>
    </el-dialog>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                list: [],
                btnLoading: false,
                tableLoading: false,
                dialogFormVisible: false,
                form: {
                    pic_url: '',
                    title: '',
                    page_url: '',
                }
            };
        },
        methods: {
            getList() {
                let self = this;
                self.tableLoading = true;
                request({
                    params: {
                        r: 'mall/page/share-setting',
                    },
                    method: 'get',
                }).then(e => {
                    self.tableLoading = false;
                    if (e.data.code == 0) {
                        self.list = e.data.data.list;
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            edit(index) {
                this.dialogFormVisible = true;
                this.form = this.list[index];
            },
            store() {
                let self = this;
                self.btnLoading = true;
                request({
                    params: {
                        r: 'mall/page/share-setting',
                    },
                    data: {
                        'list': self.list
                    },
                    method: 'post',
                }).then(e => {
                    self.btnLoading = false;
                    if (e.data.code == 0) {
                        self.getList();
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            }
        },
        mounted: function () {
            this.getList();
        }
    });
</script>
