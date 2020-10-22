<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/12/15 16:54
 */
?>
<div id="app" v-cloak>
    <el-card shadow="never">
        <div slot="header">
            <span>插件管理</span>
        </div>
        <el-table v-loading="loading" border :data="list" style="width: 100%;margin-bottom: 15px">
            <el-table-column label="插件">
                <template slot-scope="scope">
                    <div>{{scope.row.display_name}}</div>
                    <div style="color: #909399">{{scope.row.name}}</div>
                </template>
            </el-table-column>
            <el-table-column label="安装" width="100">
                <template slot-scope="scope">
                    <div v-if="scope.row.installed" style="color: #909399">已安装</div>
                    <div v-else>
                        <el-button :loading="scope.row.installLoading ? true : false"
                                   type="primary" plain size="mini"
                                   @click="installPlugin(scope.row)">安装
                        </el-button>
                    </div>
                </template>
            </el-table-column>
        </el-table>
    </el-card>
</div>
<script>
new Vue({
    el: '#app',
    data() {
        return {
            loading: false,
            list: [],
        };
    },
    created() {
        this.loadData();
    },
    methods: {
        loadData() {
            this.loading = true;
            request({
                params: {
                    r: 'cloud/plugin/index',
                }
            }).then(e => {
                this.loading = false;
                if (e.data.code === 0) {
                    this.list = e.data.data.list;
                } else {
                    this.$message.error(e.data.msg);
                }
            }).catch(e => {
            });
        },
        installPlugin(plugin) {
            request({
                params: {
                    r: 'cloud/plugin/install',
                },
                data: {
                    name: plugin.name,
                },
                method: 'post',
            }).then(e => {
                if (e.data.code === 0) {
                    this.$message.success(e.data.msg);
                } else {
                    this.$message.error(e.data.msg);
                }
            }).catch(e => {
            });
        }
    },
});
</script>