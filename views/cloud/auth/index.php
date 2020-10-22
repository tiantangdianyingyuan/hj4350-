<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/5 17:52
 */
?>
<style>
    body {
        background: #f7f7f7;
        padding-top: 24px;
    }

    .container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px 40px;
        background: #fff;
        border: 1px solid #e2e2e2;
        border-radius: 2px;
    }

    .row {
        margin-bottom: 10px;
    }

    .label {
        min-width: 120px;
    }

    .ip + .ip {
        margin-left: 20px;
    }
</style>
<div id="app" v-cloak>
    <div class="container" v-loading="loading">
        <template v-if="data">
            <template v-if="data.status===1">
                <el-form>
                    <el-form-item>
                        <el-button type="text" icon="el-icon-menu" @click="$navigate({r:'mall/index/index'})">返回商城
                        </el-button>
                    </el-form-item>
                    <template v-if="data.is_super_admin">
                        <el-form-item label="授权站点">{{data.host.name}}</el-form-item>
                        <el-form-item label="授权域名">{{data.host.domain}}</el-form-item>
                        <el-form-item label="授权网址">{{data.host.protocol}}{{data.host.domain}}</el-form-item>
                        <el-form-item label="本站域名">
                            <span>{{data.local_auth_domain}}</span>
                            <el-button v-if="!showEdit"
                                       type="text"
                                       style="margin: -13px 0"
                                       @click="showEdit = true">设置
                            </el-button>
                            <div :style="'display:' + (showEdit ? 'block' : 'none')">
                                <el-input v-model="localAuthDomain"
                                          style="width: 200px"
                                          placeholder="输入本站点的授权域名"
                                          size="mini"></el-input>
                                <el-button style="margin-left: 10px" size="mini" type="primary"
                                           @click="saveLocalAuthDomain">保存
                                </el-button>
                                <el-button size="mini" @click="showEdit = false">取消</el-button>
                                <div style="color: #E6A23C">提示：当本站域名和授权的域名不一致时需要设置本站域名。
                                    <br>注意：如果您的服务器上安装了多套本系统，则本站域名请配置成本套系统的域名。
                                </div>
                            </div>
                        </el-form-item>
                        <el-alert v-if="data.domain_error" title="授权错误"
                                  type="error"
                                  description="授权的域名与本站点域名不一致！"
                                  :closable="false"
                                  show-icon>
                        </el-alert>
                    </template>

                    <template v-else>
                        <el-form-item label="授权域名">{{data.host.domain}}</el-form-item>
                        <el-form-item label="本站域名">{{data.local_auth_domain}}</el-form-item>
                        <el-alert v-if="data.domain_error" title="授权错误"
                                  type="error"
                                  description="授权的域名与本站点域名不一致！"
                                  :closable="false"
                                  show-icon>
                        </el-alert>
                    </template>
                </el-form>
            </template>


            <template v-if="data.status===0">
                <el-alert title="授权错误"
                          type="error"
                          :description="data.detail"
                          :closable="false"
                          show-icon>
                </el-alert>
            </template>

        </template>
    </div>
</div>
<script>
    new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                showEdit: false,
                localAuthDomain: '',
                data: null,
            };
        },
        created() {
            this.loadData();
        },
        methods: {
            loadData() {
                this.loading = true;
                this.$request({
                    params: {
                        r: 'cloud/auth/index'
                    }
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.data = e.data.data;
                    } else {
                        this.$alert(e.data.msg);
                    }
                }).catch(e => {
                });
            },
            saveLocalAuthDomain() {
                this.$request({
                    params: {
                        r: 'cloud/auth/set-local-auth-domain',
                    },
                    data: {
                        domain: this.localAuthDomain,
                    },
                    method: 'post',
                }).then(e => {
                    if (e.data.code === 0) {
                        this.$message.success(e.data.msg);
                        location.reload();
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
        },
    });
</script>