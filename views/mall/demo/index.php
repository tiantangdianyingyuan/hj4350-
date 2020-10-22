<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/29 15:59
 */
?>
<div id="app" v-cloak>
    <el-card shadow="never">
        <div slot="header">
            <span>列表标题</span>
            <el-button style="float: right; margin: -5px 0" type="primary" size="small"
                       @click="$navigate({r:'mall/demo/edit'})">添加项目
            </el-button>
        </div>
        <el-form size="small" :inline="true" :model="search">
            <el-form-item>
                <el-date-picker
                        style="width: 240px"
                        v-model="search.start_time"
                        type="daterange"
                        range-separator="至"
                        start-placeholder="开始日期"
                        end-placeholder="结束日期">
                </el-date-picker>
            </el-form-item>
            <el-form-item>
                <el-select style="width: 100px" v-model="search.status1" placeholder="状态1">
                    <el-option label="区域一" value="0"></el-option>
                    <el-option label="区域二" value="1"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-select style="width: 100px" v-model="search.status2" placeholder="状态2">
                    <el-option label="区域一" value="0"></el-option>
                    <el-option label="区域二" value="1"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-input style="width: 150px" v-model="search.keyword" placeholder="ID/名称/数据项"></el-input>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" plain>搜索</el-button>
            </el-form-item>
        </el-form>
        <el-table v-loading="loading" border :data="list" style="width: 100%;margin-bottom: 15px">
            <el-table-column type="selection" width="35"></el-table-column>
            <el-table-column prop="id" label="ID" width="100"></el-table-column>
            <el-table-column prop="name" label="名称" width="180"></el-table-column>
            <el-table-column label="数据">
                <template slot-scope="scope">
                    <div flex="box:first">
                        <div style="padding-right: 10px">
                            <app-image mode="aspectFill" :src="scope.row.pic"></app-image>
                        </div>
                        <div>
                            <app-ellipsis :line="2">{{scope.row.data1}}</app-ellipsis>
                            <app-ellipsis :line="1">{{scope.row.data2}}</app-ellipsis>
                        </div>
                    </div>
                </template>
            </el-table-column>
            <el-table-column label="状态1" width="70">
                <template slot-scope="scope">
                    <el-switch v-model="scope.row.status1">
                    </el-switch>
                </template>
            </el-table-column>
            <el-table-column prop="status2" label="状态2" width="60"></el-table-column>
            <el-table-column label="操作" width="220">
                <template slot-scope="scope">
                    <el-button plain size="mini">其它</el-button>
                    <el-button plain size="mini" type="primary">编辑</el-button>
                    <el-button plain size="mini" type="danger">删除</el-button>
                </template>
            </el-table-column>
        </el-table>
        <div flex="box:last cross:center">
            <div>
                <el-button plain type="primary" size="small">批量操作1</el-button>
                <el-button plain type="primary" size="small">批量操作2</el-button>
            </div>
            <div>
                <el-pagination
                        v-if="pagination"
                        style="display: inline-block;float: right;"
                        background
                        @current-change="pageChange"
                        layout="prev, pager, next, jumper"
                        :total="pagination.totalCount">
                </el-pagination>
            </div>
        </div>
    </el-card>
    <el-card>
        <div slot="header">
            <span>省市区选择</span>
            <el-button type="text" @click="openDistrict()">选择省市区</el-button>
            <el-dialog title="选择地区" :visible.sync="dialogVisible" width="50%">
                <app-district :detail="detail" @selected="selectDistrict" :level="3"
                              :edit="editDistrict"></app-district>
                <el-button @click="districtConfirm">确定选择</el-button>
            </el-dialog>
        </div>
        <el-form>
            <el-form-item>
                <template v-for="(item, index) in detail">
                    <el-card>
                        <div flex="dir:left box:last">
                            <div>
                                <div flex="dir:left" style="flex-wrap: wrap">
                                    <div>区域：</div>
                                    <el-tag style="margin:5px" v-for="(item, index) in item.list" :key="item.id">
                                        {{item.name}}
                                    </el-tag>
                                </div>
                            </div>
                            <div style="text-align: right">
                                <el-button type="text" @click="deleteDistrict(index)">[-删除项目]</el-button>
                                <el-button type="text" @click="openDistrict(index)">[-编辑项目]</el-button>
                            </div>
                        </div>
                    </el-card>
                </template>
            </el-form-item>
        </el-form>
    </el-card>
</div>
<script>
    new Vue({
        el: '#app',
        data() {
            return {
                search: {
                    keyword: '',
                    status: 0,
                    start_time: '',
                    end_time: '',
                },
                loading: false,
                list: [],
                pagination: null,
                dialogVisible: false,
                detail: [],
                districtTempList: [],
                editDistrict: [],
                editIndex: -1
            };
        },
        created() {
            this.loadData(1);
        },
        methods: {
            loadData(page) {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/demo/index',
                        page: page,
                        keyword: this.search.keyword,
                        status: this.search.status,
                        start_time: this.search.start_time,
                        end_time: this.search.end_time,
                    },
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
            pageChange(page) {
                this.loadData(page);
            },
            selectDistrict(e) {
                let list = [];
                for (let i in e) {
                    let obj = {
                        id: e[i].id,
                        name: e[i].name
                    };
                    list.push(obj);
                }
                this.districtTempList = list;
            },
            districtConfirm() {
                let list = {
                    list: this.districtTempList
                };
                if (this.editDistrict.length > 0) {
                    this.detail[this.editIndex] = list;
                } else {
                    this.detail.push(list);
                }
                this.dialogVisible = false;
            },
            deleteDistrict(index) {
                this.detail.splice(index, 1);
            },
            openDistrict(index) {
                if (typeof index != 'undefined') {
                    this.editDistrict = this.detail[index].list;
                    this.editIndex = index;
                } else {
                    this.editDistrict = [];
                    this.editIndex = -1;
                }
                this.dialogVisible = true;
            }
        }
    });
</script>