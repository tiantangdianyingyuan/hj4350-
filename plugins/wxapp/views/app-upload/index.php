<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/2/26
 * Time: 10:57
 */
?>
<script>const _branch = '<?=$branch?>';</script>
<style>
    .table-body {
        padding: 40px 20px 20px;
        background-color: #fff;
    }

    .outline {
        display: inline-block;
        vertical-align: middle;
        line-height: 32px;
        height: 32px;
        color: #F56E6E;
        cursor: pointer;
        font-size: 24px;
        margin: 0 5px;
    }

    .plugin-list {
        width: 200px;
        margin-bottom: 20px;
    }

    .plugin-list .plugin-item {
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 10px 0;
    }

    .plugin-list .plugin-item:last-child {
        border: none;
    }
</style>

<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" class="box-card" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>{{preTitle}}小程序发布</span>
                <div style="float: right;margin-top: -5px">
                    <el-button type="primary" size="small" @click="getAppQrcode" :loading="app_qrcode_loading">
                        获取小程序二维码
                    </el-button>
                </div>
            </div>
        </div>
        <div class="table-body">
            <el-steps :active="step" finish-status="success" align-center
                      style="border-bottom: 1px solid #ebeef5;padding-bottom: 20px">
                <el-step title="插件配置"></el-step>
                <el-step title="扫描二维码登录"></el-step>
                <el-step title="预览小程序"></el-step>
                <el-step title="上传成功"></el-step>
            </el-steps>
            <div v-if="step==0" style="padding: 20px 10px" v-loading="loadingAppPlugins">
                <div style="color: #ff4544; margin-bottom: 10px">
                    必须先去
                    <span style="color: #33cc33">【微信公众平台--设置--第三方服务--插件管理】</span>
                    中添加相应的插件
                </div>
                <div style="max-width: 620px; margin-bottom: 10px;">
                    目前小程序直播组件是在公测期间，是针对电商类目小程序活跃、经营方面表现较好邀请做公测，如果符合要求的在小程序后台会看到【直播】入口，可以点进去申请，可以勾选直播插件发布，没有资格的小程序的请不要勾选直播插件发布，会出现小程序无法使用的情况
                </div>
                <div class="plugin-list">
                    <div v-for="(item, index) in all_plugins"
                         :key="index"
                         class="plugin-item"
                         flex="box:last">
                        <div>
                            <div>{{item.name}}</div>
                            <div style="color: #707379">{{item.key}}</div>
                        </div>
                        <div>
                            <el-switch v-model="item.active"></el-switch>
                        </div>
                    </div>
                </div>
                <el-button type="primary" size="small" @click="jumpAppidDialogVisible=true">可跳转小程序设置</el-button>
                <div style="padding: 20px;text-align: center;">
                    <el-button type="primary" @click="saveAppPlugins" :loading="saveAppPluginsLoading">下一步</el-button>
                </div>
            </div>
            <div v-else style="text-align: center; padding: 20px 0">
                <el-button type="primary" @click="login" :loading="upload_loading" v-if="!login_qrcode">获取登录二维码
                </el-button>
                <div style="text-align: center" v-if="login_qrcode && !preview_qrcode">
                    <img :src="login_qrcode"
                         style="width: 150px;height: 150px; border: 1px solid #e2e2e2;margin-bottom: 12px">
                    <div style="margin-bottom: 12px;">请使用微信扫码登录</div>
                    <div style="color: #909399;">
                        <div>扫码登录后大约会有10秒左右延时，请您耐心等待。</div>
                        <div>您的微信号必须是该小程序的管理员或者开发者才可扫码登录。</div>
                    </div>
                </div>
                <div style="text-align: center" v-if="preview_qrcode">
                    <img :src="preview_qrcode"
                         style="width: 150px;height: 150px; border: 1px solid #e2e2e2;margin-bottom: 12px">
                    <div style="margin-bottom: 12px;">扫描二维码可以预览小程序</div>
                    <el-button type="primary" @click="upload" :loading="upload_loading" v-if="!upload_success">上传小程序
                    </el-button>
                    <div v-else>
                        <div style="margin-bottom: 12px">
                            <span>上传成功！</span>
                            <span>请登录微信小程序平台（</span>
                            <a href="https://mp.weixin.qq.com/" target="_blank">https://mp.weixin.qq.com/</a>
                            <span>）发布小程序</span>
                        </div>
                        <div style="margin-bottom: 12px">
                            <div>版本号：{{version}}</div>
                            <div>描述：{{desc}}</div>
                        </div>
                        <div>
                            <img style="max-width: 100%;height: auto;border: 1px dashed #35b635;"
                                 src="<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl() ?>/upload-tip.png">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </el-card>
    <el-dialog @open="loadJumpAppid" title="可跳转小程序设置" :visible.sync="jumpAppidDialogVisible"
               :close-on-click-modal="false">
        <div style="margin-bottom: 20px">最多可配置10个，超出无效</div>
        <div v-loading="loadJumpAppidLoading">
            <template v-for="(appid, index) in jumpAppIdList">
                <div flex="box:last" style="margin-bottom: 20px;width: 95%">
                    <el-input v-model="jumpAppIdList[index]" placeholder="请填写小程序APPID"
                              style="margin-right: 10px;"></el-input>
                    <div class="outline">
                        <el-tooltip class="item" effect="dark" content="删除" placement="top">
                            <i @click="jumpAppIdList.splice(index,1)" class="el-icon-remove-outline"></i>
                        </el-tooltip>
                    </div>
                </div>
            </template>
            <el-button type="text" v-if="jumpAppIdList.length<10" @click="jumpAppIdList.push('')"
                       style="margin-bottom: 20px">
                <i class="el-icon-plus" style="font-weight: bolder;margin-left: 5px;"></i>
                <span style="color: #353535;font-size: 14px">新增</span>
            </el-button>
            <div slot="footer" style="text-align: right">
                <el-button size="small" type="primary" @click="saveJumpAppid" :loading="saveJumpAppidLoading">保存
                </el-button>
            </div>
        </div>
    </el-dialog>

</div>
<script>
    new Vue({
        el: '#app',
        data() {
            return {
                jumpAppidDialogVisible: false,
                saveJumpAppidLoading: false,
                app_qrcode_loading: false,
                app_qrcode: false,
                step: 0,
                upload_loading: false,
                login_qrcode: false,
                preview_qrcode: false,
                upload_success: false,
                version: '',
                desc: '',
                loadJumpAppidLoading: false,
                jumpAppIdList: [],
                loadingAppPlugins: false,
                all_plugins: [
                    {
                        key: 'live-player-plugin',
                        name: '小程序直播组件',
                        active: false,
                    },
                ],
                saveAppPluginsLoading: false,
            };
        },
        computed: {
            preTitle() {
                if (_branch == 'nomch') {
                    return '单商户';
                }
                return '';
            },
        },
        created() {
            this.loadAppPlugins();
        },
        methods: {
            loadAppPlugins() {
                this.loadingAppPlugins = true;
                this.$request({
                    params: {
                        r: 'plugin/wxapp/app-upload/app-plugins',
                    },
                }).then(e => {
                    this.loadingAppPlugins = false;
                    for (let i in this.all_plugins) {
                        if (e.data.data.indexOf(this.all_plugins[i].key) > -1) {
                            this.all_plugins[i].active = true;
                        }
                    }
                }).catch(e => {
                    this.loadingAppPlugins = false;
                });
            },
            saveAppPlugins() {
                let plugins = [];
                for (let i in this.all_plugins) {
                    if (this.all_plugins[i].active) {
                        plugins.push(this.all_plugins[i].key);
                    }
                }
                this.saveAppPluginsLoading = true;
                this.$request({
                    method: 'post',
                    params: {
                        r: 'plugin/wxapp/app-upload/app-plugins',
                    },
                    data: {
                        plugins: plugins,
                    },
                }).then(e => {
                    this.step = 1;
                }).catch(e => {
                    this.saveAppPluginsLoading = false;
                });
            },
            getAppQrcode() {
                let html = '';
                if (this.app_qrcode) {
                    html = '<div style="text-align: center;"><img src='
                        + this.app_qrcode
                        + ' style="width: 200px;"></div>';
                    this.$alert(html, '小程序码', {
                        dangerouslyUseHTMLString: true
                    });
                    return;
                }
                this.app_qrcode_loading = true;
                this.$request({
                    params: {
                        r: 'plugin/wxapp/app-upload/app-qrcode',
                    },
                }).then(e => {
                    this.app_qrcode_loading = false;
                    if (e.data.code === 0) {
                        this.app_qrcode = e.data.data.qrcode;
                        html = '<div style="text-align: center;"><img src='
                            + this.app_qrcode
                            + ' style="width: 200px;"></div>';
                        this.$alert(html, '小程序码', {
                            dangerouslyUseHTMLString: true
                        });
                    } else {
                        this.$alert(e.data.msg, '提示');
                    }
                }).catch(e => {
                    this.app_qrcode_loading = false;
                });
            },
            login() {
                this.upload_loading = true;
                this.$request({
                    params: {
                        r: 'plugin/wxapp/app-upload/index',
                        action: 'login',
                        branch: _branch,
                    },
                }).then(e => {
                    this.upload_loading = false;
                    if (e.data.code === 0) {
                        this.login_qrcode = e.data.data.qrcode;
                        setTimeout(() => {
                            this.preview();
                        }, 2000);
                    } else {
                        this.$alert(e.data.msg, '提示', {
                            callback() {
                                location.reload();
                            },
                        });
                    }
                }).catch(e => {
                    this.upload_loading = false;
                });
            },
            preview() {
                this.$request({
                    params: {
                        r: 'plugin/wxapp/app-upload/index',
                        action: 'preview',
                        branch: _branch,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        if (e.data.data.qrcode) {
                            this.preview_qrcode = e.data.data.qrcode;
                            this.step = 1;
                        } else if (e.data.data.retry && e.data.data.retry === 1) {
                            setTimeout(() => {
                                this.preview();
                            }, 2000);
                        }
                    } else {
                        this.$alert(e.data.msg, '提示', {
                            callback() {
                                location.reload();
                            },
                        });
                    }
                }).catch(e => {
                });
            },
            upload() {
                this.upload_loading = true;
                this.$request({
                    params: {
                        r: 'plugin/wxapp/app-upload/index',
                        action: 'upload',
                        branch: _branch,
                    },
                }).then(e => {
                    this.upload_loading = false;
                    if (e.data.code === 0) {
                        this.step = 3;
                        this.upload_success = true;
                        this.version = e.data.data.version;
                        this.desc = e.data.data.desc;
                    } else {
                        this.$alert(e.data.msg, '提示');
                    }
                }).catch(e => {
                    this.upload_loading = false;
                });
            },
            loadJumpAppid() {
                this.loadJumpAppidLoading = true;
                this.$request({
                    params: {
                        r: 'plugin/wxapp/app-upload/jump-appid',
                    },
                }).then(e => {
                    this.loadJumpAppidLoading = false;
                    if (e.data.code === 0) {
                        this.jumpAppIdList = e.data.data.list;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
            saveJumpAppid() {
                this.saveJumpAppidLoading = true;
                this.$request({
                    params: {
                        r: 'plugin/wxapp/app-upload/jump-appid',
                    },
                    method: 'post',
                    data: {
                        appid_list: this.jumpAppIdList,
                    },
                }).then(e => {
                    this.saveJumpAppidLoading = false;
                    if (e.data.code === 0) {
                        this.jumpAppidDialogVisible = false;
                        this.$message.success(e.data.msg);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
        },
    });
</script>