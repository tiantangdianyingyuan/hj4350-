<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/3/25
 * Time: 10:19
 */

$mchId = Yii::$app->user->identity->mch_id;
?>
<style>
    .mall-header {
        margin-left: 30px;
    }

    .mall-header .mall-name {
        max-width: 120px;
        display: inline-block;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .mall-header .el-menu.el-menu--horizontal {
        border-bottom: 0;
    }

    .mall-header-menu .el-menu--popup {
        min-width: 1px;
    }

    .mall-header-menu .el-menu--popup .is-disabled {
        opacity: .65;
        cursor: default;
    }
</style>
<template id="mall-header">
    <div>
        <header class="mall-header" flex="box:last">
            <el-menu class="el-menu-demo" mode="horizontal" menu-trigger="click">
                <el-menu-item v-for="(nav, index) in navs"
                              :key="index"
                              :index="'' + index"
                              @click="$navigate(nav.url, nav.new_window?true:false)">{{nav.name}}
                </el-menu-item>
            </el-menu>
            <el-menu mode="horizontal" menu-trigger="click">
                <el-menu-item v-if="mch_id == 0" @click="navigateClick({r: 'admin/cache/clean', '_layout': 'mall'})" index="3">缓存
                </el-menu-item>
                <el-submenu v-if="mch_id == 0 && courseMenu.children && courseMenu.children.length > 0" index="3" v-if="courseMenu" popper-class="mall-header-menu">
                    <template slot="title">
                        <span :title="courseMenu.name" class="mall-name">{{courseMenu.name}}</span>
                    </template>
                    <el-menu-item @click="navigateClick({r: item.route})" v-for="(item, index) in courseMenu.children"
                                  :key="index" :index="'1-' + index">
                        {{item.name}}
                    </el-menu-item>
                </el-submenu>
                <!--                <el-menu-item index="1">消息中心</el-menu-item>-->
                <el-submenu index="2" v-if="mall && user" popper-class="mall-header-menu">
                    <template slot="title">
                        <span :title="mall.name" class="mall-name">{{mall.name}}</span>
                    </template>
                    <el-menu-item index="1-1" :disabled="true">{{mall.name}}</el-menu-item>
                    <el-menu-item index="1-2" :disabled="true">{{user.nickname}}({{user.username}})</el-menu-item>
                    <el-menu-item v-if="user.identity && user.identity.is_operator == 0 && user.mch_id == 0"
                                  index="2-1" @click="goBackToSystem">返回系统
                    </el-menu-item>
                    <el-menu-item
                            v-if="user.identity && user.identity.is_operator == 1 && user.mch_id == 0 && updatePasswordStatus"
                            index="2-3" @click="updatePassword">修改密码
                    </el-menu-item>
                    <el-menu-item v-if="user.identity && user.identity.is_operator == 1 || user.mch_id"
                                  index="2-2" @click="logout">注销
                    </el-menu-item>
                </el-submenu>
            </el-menu>
        </header>
        <el-dialog title="修改密码" :visible.sync="dialogFormVisible" width="30%">
            <el-form :model="ruleForm" status-icon :rules="rules" ref="ruleForm" label-width="100px">
                <el-form-item label="密码" prop="pass">
                    <el-input type="password" v-model="ruleForm.pass" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="确认密码" prop="checkPass">
                    <el-input type="password" v-model="ruleForm.checkPass" auto-complete="off"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogFormVisible = false">取 消</el-button>
                <el-button :loading="btnLoading" type="primary" @click="updatePasswordSubmit('ruleForm')">确 定
                </el-button>
            </div>
        </el-dialog>
    </div>
</template>
<script>
    Vue.component('mall-header', {
        template: '#mall-header',
        data() {
            var validatePass = (rule, value, callback) => {
                if (value === '') {
                    callback(new Error('请输入密码'));
                } else {
                    if (this.ruleForm.checkPass !== '') {
                        this.$refs.ruleForm.validateField('checkPass');
                    }
                    callback();
                }
            };
            var validatePass2 = (rule, value, callback) => {
                if (value === '') {
                    callback(new Error('请再次输入密码'));
                } else if (value !== this.ruleForm.pass) {
                    callback(new Error('两次输入密码不一致!'));
                } else {
                    callback();
                }
            };
            return {
                user: null,
                navs: [],
                mall: null,
                dialogFormVisible: false,
                ruleForm: {
                    pass: '',
                    checkPass: '',
                },
                btnLoading: false,
                rules: {
                    pass: [
                        {required: true, message: '请输入密码', trigger: 'blur'},
                        {validator: validatePass, trigger: 'blur'},
                    ],
                    checkPass: [
                        {required: true, message: '请输入确认密码', trigger: 'blur'},
                        {validator: validatePass2, trigger: 'blur'}
                    ]
                },
                updatePasswordStatus: 0,
                courseMenu: {
                    children: [],
                },
                mch_id: '<?=$mchId ?>'
            };
        },
        created() {
            const cacheKey = '_MALL_MENUS';
            let data = localStorage.getItem(cacheKey);
            if (data) {
                try {
                    data = JSON.parse(data);
                    this.courseMenu = data.courseMenu;
                } catch (e) {
                    data = false;
                }
            }
            console.log(_aside)
            this.loadData();
            let headerData = localStorage.getItem('_MALL_HEADER_DATA');
            if (headerData) {
                try {
                    headerData = JSON.parse(headerData);
                    this.user = headerData.user;
                    this.navs = headerData.navs;
                    this.mall = headerData.mall;
                    this.updatePasswordStatus = headerData.update_password_status;
                    _aside.mall = headerData.mall;
                } catch (e) {
                    headerData = false;
                }
            }
        },
        methods: {
            handleOpen() {
            },
            handleClose() {
            },
            loadData() {
                this.$request({
                    params: {
                        r: 'mall/index/header-bar',
                    },
                    method: 'get',
                }).then(e => {
                    localStorage.setItem('_MALL_HEADER_DATA', JSON.stringify(e.data.data));
                    this.user = e.data.data.user;
                    this.navs = e.data.data.navs;
                    this.mall = e.data.data.mall;
                    this.updatePasswordStatus = e.data.data.update_password_status;
                    _aside.mall = e.data.data.mall;
                }).catch(e => {
                    console.log(e);
                });
            },
            logout() {
                let self = this;
                this.$request({
                    params: {
                        r: 'mall/user/logout'
                    },
                    method: 'get',
                    data: {}
                }).then(e => {
                    // 在当前页面打开
                    self.$navigate({
                        r: 'admin/user/me',
                    });
                }).catch(e => {
                    console.log(e);
                });
            },
            goBackToSystem() {
                if (_isWe7) {
                    this.$navigate({r: 'mall/we7-entry/logout'});
                } else {
                    this.$navigate({r: 'admin/index/back-index'});
                }
            },
            updatePassword() {
                this.dialogFormVisible = true;
            },
            updatePasswordSubmit(formName) {
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        this.$request({
                            params: {
                                r: 'mall/role-user/update-password'
                            },
                            method: 'post',
                            data: {
                                password: this.ruleForm.checkPass
                            }
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code == 0) {
                                this.dialogFormVisible = false;
                                this.$message.success(e.data.msg);
                                window.location.reload();
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            console.log(e);
                        });
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
            navigateClick(params) {
                this.clearMenuStorage();
                this.$navigate(params);
            },
            clearMenuStorage() {
                localStorage.removeItem('_OPENED_MENU_1_ID');
                localStorage.removeItem('_OPENED_MENU_2_ID');
                localStorage.removeItem('_OPENED_MENU_3_ID');
                localStorage.removeItem('_UNFOLD_ID_1');
                localStorage.removeItem('_UNFOLD_ID_2');
            }
        },
    });
</script>