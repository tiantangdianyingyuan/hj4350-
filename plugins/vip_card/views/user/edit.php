<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/28
 * Time: 14:38
 */
defined('YII_ENV') or exit('Access Denied');
?>

<style>
    .form-body {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 20%;
        min-width: 900px;
    }

    .form-body .el-form-item {
        padding-right: 50%;
        min-width: 850px;
    }

    .form-button {
        margin: 0;
    }

    .form-button .el-form-item__content {
        margin-left: 0!important;
    }

    .button-item {
        padding: 9px 25px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'plugin/vip_card/mall/user/index'})">用户管理</span></el-breadcrumb-item>
                <el-breadcrumb-item>编辑用户</el-breadcrumb-item>
            </el-breadcrumb>
        </div>

        <div class="form-body">
            <el-form size="small" label-width="150px">
                <el-row>
                    <el-col :span="24">
                        <el-form-item label="用户" >

                        </el-form-item>
                        <el-form-item label="会员卡种类" >
                            <el-radio-group v-for="(item, index) in card.detail">
                                <el-radio :label="item.id" >{{item.name}}</el-radio>

                            </el-radio-group>
                        </el-form-item>

                    </el-col>
                </el-row>
            </el-form>
        </div>
        <el-button class="button-item"  type="primary" @click="store('ruleForm')" size="small">保存</el-button>
    </el-card>
</div>


<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                card: [],
                id: null,
            };
        },

        methods: {
            getCard() {
                let self = this;

                request({
                    params: {
                        r: 'plugin/vip_card/mall/card',
                    },
                    method: 'get',
                }).then(e => {
                    self.card = e.data.data.card;
                }).catch(e => {
                    console.log(e);
                });
            },

        },
        created() {
            this.getCard();
        }
    })
</script>