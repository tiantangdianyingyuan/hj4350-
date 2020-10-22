<?php defined('YII_ENV') or exit('Access Denied');
$_currentPluginBaseUrl = \app\helpers\PluginHelper::getPluginBaseAssetsUrl(Yii::$app->plugin->currentPlugin->getName());
Yii::$app->loadViewComponent('goods/app-add-cat');


?>

<style>


     .el-card__header {
        height: 60px;
        font-size: 15px;
        padding-left: 15px;
        border-radius: 4px;
        border: 1px solid #ebeef5;
        background-color: white;
    }

    .el-card {
        background-color: #f3f3f3;
    }

    .card-body {
        background-color: white;
        width: 100%;
        margin-top: 10px;
        border-radius: 4px;
        border: 1px solid #ebeef5;
        padding: 30px;
        overflow: auto;
    }

     .table-body {
         padding: 20px;
         background-color: #fff;
         margin-bottom: 20px;
     }

     .table-body .el-button {
         padding: 0!important;
         border: 0;
         margin: 0 5px;
     }

     .input-item {
         width: 250px;
         margin: 0 0 20px;
     }

     .input-item .el-input__inner {
         border-right: 0;
     }

     .input-item .el-input__inner:hover{
         border: 1px solid #dcdfe6;
         border-right: 0;
         outline: 0;
     }

     .input-item .el-input__inner:focus{
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

     .el-input-group__append .el-button {
         margin: 0;
     }

</style>

<div id="app">
    <el-card style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 20px 0 0 0;"
             v-loading="listLoading">
        <app-add-cat ref="mchCats" :new-cats="ruleForm.mchCats" :mch_id="mch_id"
                     @select="selectMchCat"></app-add-cat>
        <div slot="header" >
            <span>
                分类列表
            </span>
            <div style="float: right;margin-top: -5px">
                <el-button  size="small" style="display: inline-block;" type="primary" @click="$refs.mchCats.openDialog()">添加分类</el-button>
            </div>
        </div>
        <div class="table-body">
            <div style="justify-content:space-between;display: flex">
                <div class="input-item">
                    <el-input @keyup.enter.native="search" size="small" placeholder="请输入搜索内容" v-model="keyword" clearable @clear="search">
                        <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                    </el-input>
                </div>
            </div>

            <el-table v-loading="loading" border :data="list" style="width: 100%;margin-bottom: 15px">
                <el-table-column prop="id" label="ID">

                </el-table-column>
                <el-table-column prop="name" label="分类名称"></el-table-column>
                <el-table-column label="图标">
                    <template slot-scope="scope">
                        <app-image mode="aspectFill" :src="scope.row.cats.pic_url"></app-image>
                    </template>
                </el-table-column>
                <el-table-column label="排序">
                    <template slot-scope="scope">
                        <div v-if="id != scope.row.id">
                            <el-tooltip class="item" effect="dark" content="排序" placement="top">
                                <span>{{scope.row.sort}}</span>
                            </el-tooltip>
                            <el-button class="edit-sort" type="text" @click="editSort(scope.row)">
                                <img src="statics/img/mall/order/edit.png" alt="">
                            </el-button>
                        </div>
                        <div style="display: flex;align-items: center" v-else>
                            <el-input style="min-width: 70px" type="number" size="mini" class="change" v-model="sort"
                                      autocomplete="off"></el-input>
                            <el-button class="change-quit" type="text" style="color: #F56C6C;padding: 0 5px" icon="el-icon-error"
                                       circle @click="quit()"></el-button>
                            <el-button class="change-success" type="text" style="margin-left: 0;color: #67C23A;padding: 0 5px"
                                       icon="el-icon-success" circle @click="sortSubmit(scope.row)">
                            </el-button>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="name" label="添加时间"></el-table-column>
                <el-table-column label="操作" width="200" fixed="right">
                    <template slot-scope="scope">
                        <el-button size="small" type="text" @click="destroy(scope.row.id)" circle>
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div flex="dir:right">
                <el-pagination
                        background
                        @current-change="pageChange"
                        layout="prev, pager, next"
                        :page-size="pagination.pageSize"
                        :current-page.sync="pagination.page"
                        :total="pagination.totalCount">
                </el-pagination>
            </div>
        </div>
    </el-card>
</div>

<script>
    const _currentPluginBaseUrl = `<?=$_currentPluginBaseUrl?>`;

    const app = new Vue({
        el: '#app',
        data() {
            return {
                pic_url: _currentPluginBaseUrl + `/img/gift-left.png`,

                listLoading: false,
                form: {},
                search: {
                    keyword: 'name',
                    keyword_1: '',
                    time: [],
                    start_date: '',
                    end_date: '',
                    page: 1,
                    gift_id: ''
                },
                tableData: [],
                pagination: {},
                keyword: '',
                ruleForm: {},
                mch_id: 0,
                list: [],
                loading: false
            }
        },

        created() {

        },

        methods: {
            pageChange() {},

            selectMchCat() {}
        }
    })
</script>
