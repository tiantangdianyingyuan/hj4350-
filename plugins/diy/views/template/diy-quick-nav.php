<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/5/6
 * Time: 16:03
 */
?>
<style>
    .diy-quick-nav .pic-select {
        width: 72px;
        height: 72px;
        color: #00a0e9;
        border: 1px solid #ccc;
        line-height: normal;
        text-align: center;
        cursor: pointer;
        font-size: 12px;
    }

    .diy-quick-nav .pic-preview {
        width: 72px;
        height: 72px;
        border: 1px solid #ccc;
        cursor: pointer;
        background-position: center;
        background-repeat: no-repeat;
        background-size: contain;
    }

    .diy-quick-nav .edit-item {
        border: 1px solid #e2e2e2;
        padding: 15px;
        margin-bottom: 20px;
    }
</style>
<template id="diy-quick-nav">
    <div class="diy-quick-nav">
        <div class="diy-component-preview">
            <div style="padding: 20px 0;text-align: center;">
                <div>快捷导航设置</div>
                <div style="font-size: 22px;color: #909399">本条内容不占高度</div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form @submit.native.prevent label-width="100px">
                <el-form-item label="快捷导航开关">
                    <el-switch v-model="data.navSwitch" :inactive-value="0" :active-value="1"></el-switch>
                </el-form-item>
                <el-form-item v-if="data.navSwitch == 1" label="使用商城配置">
                    <el-switch v-model="data.useMallConfig"></el-switch>
                </el-form-item>

                <template v-if="!data.useMallConfig && data.navSwitch == 1">
                    <el-form-item label="获取商城配置">
                        <el-button size="small" @click="getMallNav">获取</el-button>
                    </el-form-item>
                    <el-form-item label="导航样式">
                        <app-radio v-model="data.navStyle" :label="1">样式1(点击收起)</app-radio>
                        <app-radio v-model="data.navStyle" :label="2">样式2(全部展示)</app-radio>
                    </el-form-item>
                    <el-form-item label="收起图标">
                        <app-image-upload width="100" height="100" v-model="data.closedPicUrl"></app-image-upload>
                    </el-form-item>
                    <el-form-item label="展开图标">
                        <app-image-upload width="100" height="100" v-model="data.openedPicUrl"></app-image-upload>
                    </el-form-item>
                    <div class="edit-item">
                        <div style="margin-bottom: 10px;">返回首页</div>
                        <el-form-item label="是否开启">
                            <el-switch v-model="data.home.opened"></el-switch>
                        </el-form-item>
                        <el-form-item v-if="data.home.opened" label="图标">
                            <app-image-upload width="100" height="100" v-model="data.home.picUrl"></app-image-upload>
                        </el-form-item>
                    </div>
                    <div class="edit-item">
                        <div style="margin-bottom: 10px;">小程序客服</div>
                        <el-form-item label="是否开启">
                            <el-switch v-model="data.customerService.opened"></el-switch>
                        </el-form-item>
                        <el-form-item v-if="data.customerService.opened" label="图标">
                            <app-image-upload width="100" height="100"
                                              v-model="data.customerService.picUrl"></app-image-upload>
                        </el-form-item>
                    </div>
                    <div class="edit-item">
                        <div style="margin-bottom: 10px;">一键拨号</div>
                        <el-form-item label="是否开启">
                            <el-switch v-model="data.tel.opened"></el-switch>
                        </el-form-item>
                        <el-form-item v-if="data.tel.opened" label="图标">
                            <app-image-upload width="100" height="100" v-model="data.tel.picUrl"></app-image-upload>
                        </el-form-item>
                        <el-form-item v-if="data.tel.opened" label="电话号码">
                            <el-input v-model="data.tel.number"></el-input>
                        </el-form-item>
                    </div>
                    <div class="edit-item">
                        <div style="margin-bottom: 10px;">网页链接</div>
                        <el-form-item label="是否开启">
                            <el-switch v-model="data.web.opened"></el-switch>
                        </el-form-item>
                        <el-form-item v-if="data.web.opened" label="图标">
                            <app-image-upload width="100" height="100" v-model="data.web.picUrl"></app-image-upload>
                        </el-form-item>
                        <el-form-item v-if="data.web.opened" label="网址">
                            <el-input v-model="data.web.url"></el-input>
                        </el-form-item>
                    </div>
                    <div class="edit-item">
                        <div style="margin-bottom: 10px;">跳转小程序</div>
                        <el-form-item label="是否开启">
                            <el-switch v-model="data.mApp.opened"></el-switch>
                        </el-form-item>
                        <el-form-item v-if="data.mApp.opened" label="图标">
                            <app-image-upload width="100" height="100" v-model="data.mApp.picUrl"></app-image-upload>
                        </el-form-item>
                        <el-form-item v-if="data.mApp.opened" label="appId">
                            <el-input v-model="data.mApp.appId"></el-input>
                        </el-form-item>
                        <el-form-item v-if="data.mApp.opened" label="页面路径">
                            <el-input v-model="data.mApp.page"></el-input>
                        </el-form-item>
                    </div>
                    <div class="edit-item">
                        <div style="margin-bottom: 10px;">地图导航</div>
                        <el-form-item label="是否开启">
                            <el-switch v-model="data.mapNav.opened"></el-switch>
                        </el-form-item>
                        <el-form-item v-if="data.mapNav.opened" label="图标">
                            <app-image-upload width="100" height="100" v-model="data.mapNav.picUrl"></app-image-upload>
                        </el-form-item>
                        <el-form-item v-if="data.mapNav.opened" label="详细地址">
                            <el-input v-model="data.mapNav.address"></el-input>
                        </el-form-item>
                        <el-form-item v-if="data.mapNav.opened" label="经纬度">
                            <app-map @map-submit="mapEvent">
                                <el-input v-model="data.mapNav.location" placeholder="点击进入地图选择" readonly></el-input>
                            </app-map>
                        </el-form-item>
                    </div>
                    <div class="edit-item">
                        <div style="margin-bottom: 10px;">自定义按钮</div>
                        <el-form-item label="是否开启">
                            <el-switch v-model="data.customize.opened"></el-switch>
                        </el-form-item>
                        <el-form-item v-if="data.customize.opened" label="图标">
                            <app-image-upload width="100" height="100"
                                              v-model="data.customize.picUrl"></app-image-upload>
                        </el-form-item>
                        <el-form-item v-if="data.customize.opened" label="跳转链接">
                            <el-input :disabled="true" size="small"
                                      v-model="data.customize.link_url" autocomplete="off">
                                <app-pick-link slot="append" @selected="selectQuickCustomize">
                                    <el-button size="mini">选择链接</el-button>
                                </app-pick-link>
                            </el-input>
                        </el-form-item>
                    </div>
                </template>
            </el-form>
        </div>
    </div>
</template>
<script>
    Vue.component('diy-quick-nav', {
        template: '#diy-quick-nav',
        props: {
            value: Object,
        },
        data() {
            return {
                data: {
                    navSwitch: 0,
                    useMallConfig: true,
                    navStyle: 1,
                    closedPicUrl: '',
                    openedPicUrl: '',
                    home: {
                        opened: false,
                        picUrl: '',
                    },
                    customerService: {
                        opened: false,
                        picUrl: '',
                    },
                    tel: {
                        opened: false,
                        picUrl: '',
                        number: '',
                    },
                    web: {
                        opened: false,
                        picUrl: '',
                        url: '',
                    },
                    mApp: {
                        opened: false,
                        picUrl: '',
                        appId: '',
                        page: '',
                    },
                    mapNav: {
                        opened: false,
                        picUrl: '',
                        address: '',
                        location: '',
                    },
                    customize: {
                        opened: false,
                        picUrl: '',
                        open_type: '',
                        params: '',
                        link_url: '',
                        key: '',
                    }
                }
            };
        },
        created() {
            if (!this.value) {
                this.$emit('input', JSON.parse(JSON.stringify(this.data)))
            } else {
                this.data = Object.assign({}, this.data, this.value);
                //this.data = JSON.parse(JSON.stringify(this.value));
            }
        },
        computed: {},
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal)
                },
            }
        },
        methods: {
            getMallNav() {
                console.log(this.value);
                request({
                    params: {
                        r: 'plugin/diy/mall/tpl-func/quick-nav-get-mall-config'
                    }
                }).then(response => {
                    if (response.data.code === 0) {
                        let data = response.data.data;
                        Object.assign(this.value, data);
                    }
                    this.$message.success(response.data.msg);
                });
            },
            selectQuickCustomize(e) {
                e.map(item => {
                    this.data.customize.link_url = item.new_link_url;
                    this.data.customize.open_type = item.open_type;
                    this.data.customize.params = item.params;
                    this.data.customize.key = item.key ? item.key : '';
                });
            },
            mapEvent(e) {
                this.data.mapNav.location = e.lat + ',' + e.long;
                this.data.mapNav.address = e.address;
            },
        }
    });
</script>
