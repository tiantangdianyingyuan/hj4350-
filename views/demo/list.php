<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/16 12:05
 */
?>
<div id="app" v-cloak>
    <div flex="box:last" style="margin-bottom: 20px">
        <div>
            <el-input style="width: 200px" suffix-icon="el-icon-search" placeholder="搜索"></el-input>
        </div>
        <div>
            <el-button icon="el-icon-circle-plus-outline">新建</el-button>
        </div>
    </div>
    <el-table v-loading="loading" :data="list">
        <el-table-column prop="id" label="ID"></el-table-column>
        <el-table-column prop="name" label="名称"></el-table-column>
    </el-table>
    <el-pagination
            v-if="pagination"
            style="padding: 20px 0"
            @current-change="pageChange"
            layout="prev, pager, next"
            :total="pagination.totalCount">
    </el-pagination>
</div>
<script>
new Vue({
    el: '#app',
    data() {
        return {
            loading: false,
            list: [],
            pagination: null,
        };
    },
    created() {
        this.loadList({});
    },
    methods: {
        loadList(params) {
            params['r'] = 'demo/list';
            this.loading = true;
            request({
                params: params,
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
            this.loadList({
                page: page,
            })
        },
    }
});
</script>