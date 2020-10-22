<?php

/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/19
 * Time: 11:33
 */

/* @var $this \yii\web\View */
?>
<style>
    .el-step__description.is-finish {
        color: inherit;
        font-size: 16px;
        margin-bottom: 20px;
    }

    .form-body {
        background-color: #fff;
        padding: 20px;
    }
</style>
<div id="app" v-cloak>
    <el-card style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header"><?= Yii::$app->plugin->getCurrentPlugin()->getDisplayName() ?>发布</div>
        <div class="form-body">
            <el-steps direction="vertical" :active="4">
                <el-step>
                    <div slot="description">
                        <p>下载并安装百度开发者工具，如果已经安装可跳过这一步。</p>
                        <el-button @click="$navigate('https://smartprogram.baidu.com/docs/develop/devtools/history/', true)">下载百度开发者工具
                        </el-button>
                    </div>
                </el-step>
                <el-step>
                    <div slot="description">
                        <p>下载小程序代码包，并解压。</p>
                        <el-button @click="$navigate({r:'plugin/bdapp/index/package-download'},true)">下载小程序代码包
                        </el-button>
                    </div>
                </el-step>
                <el-step>
                    <div slot="description">
                        <p>运行百度开发者工具，选择打开项目，打开解压出来的小程序代码包，点击上传。</p>
                    </div>
                </el-step>
            </el-steps>
        </div>
    </el-card>
</div>
<script>
    new Vue({
        el: '#app',
        data() {
            return {
                downloadLoading: false,
            };
        },
        created() {
        },
        methods: {}

    });
</script>