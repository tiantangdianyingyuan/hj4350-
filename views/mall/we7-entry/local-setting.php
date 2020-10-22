<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/6/14
 * Time: 11:22
 */
?>
<style>
    #app {
        padding: 40px 0 0;
    }

    .container {
        border: 1px solid #e2e2e2;
        max-width: 600px;
        margin: 0 auto 40px;
        color: #333;
    }

    .container .container-title {
        padding: 18px 20px;
        background: #F3F5F6;
    }

    .container .container-body {
        padding: 18px 20px;
        margin-bottom: 20px;
    }

    .code-block {
        background: #e8efee;
        border-left: 2px solid #d2d2d2;
        margin: 10px 0;
        padding: 10px 10px;
        white-space: pre-line;
    }
</style>
<div id="app">
    <div class="container">
        <div class="container-title">1. Redis配置</div>
        <div class="container-body">
            <el-form :model="form" :rules="rules" ref="form" label-width="120px">
                <el-form-item label="Redis服务器" prop="host">
                    <el-input v-model="form.host"></el-input>
                    <div style="font-size: 12px;color: #909399;">请填写Redis服务器的IP或域名</div>
                </el-form-item>
                <el-form-item label="Redis端口" prop="port">
                    <el-input v-model="form.port"></el-input>
                    <div style="font-size: 12px;color: #909399;">Redis的默认端口为6379，如果没改过就使用这里默认的配置</div>
                </el-form-item>
                <el-form-item label="Redis密码" prop="password">
                    <el-input v-model="form.password"></el-input>
                    <div style="font-size: 12px;color: #909399;">Redis默认没有密码，如果您没配置过Redis密码则密码不需要填写</div>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="saveConfig('form')" :loading="saveConfigLoading"
                               :disabled="step!=1">保存
                    </el-button>
                    <div v-if="step===2">Redis配置已保存，请转到第2步启动并检查队列服务。</div>
                </el-form-item>
            </el-form>
        </div>
        <div class="container-title">2. 启动队列服务</div>
        <div class="container-body">
            <ol>
                <li>
                    <?php
                    $queueFile = Yii::$app->basePath . '/queue.sh';
                    $command = 'chmod a+x ' . $queueFile . ' && ' . $queueFile;
                    ?>
                    <h4>启动队列服务</h4>
                    <div>Linux使用SSH远程登录服务器，运行命令：</div>
                    <pre class="code-block"><?= $command ?></pre>
                </li>
                <li>
                    <h4>测试队列服务</h4>
                    <el-button @click="createQueue" :loading="testQueueLoading" style="margin-bottom: 10px">点击测试
                    </el-button>
                    <div style="font-size: 12px;color: #909399;">检测过程最多可能需要两分钟。</div>
                </li>
            </ol>
        </div>
    </div>
</div>
<script>
    new Vue({
        el: '#app',
        data() {
            return {
                step: 1,
                saveConfigLoading: false,
                testQueueLoading: false,
                maxTestCount: 60,
                testCount: 0,
                form: {
                    host: '',
                    port: 6379,
                    password: '',
                },
                rules: {
                    host: [{required: true, message: '请填写Redis服务器'}],
                    port: [{required: true, message: '请填写Redis端口'}],
                },
            };
        },
        created() {
        },
        methods: {
            saveConfig(formName) {
                this.$refs[formName].validate(valid => {
                    if (valid) {
                        this.saveConfigLoading = true;
                        this.$request({
                            method: 'post',
                            params: {
                                r: 'mall/we7-entry/local-setting',
                                action: 'saveConfig',
                            },
                            data: this.form,
                        }).then(e => {
                            this.saveConfigLoading = false;
                            if (e.data.code === 0) {
                                this.step = 2;
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                        });
                    } else {
                        console.log('not ok');
                    }
                });
            },
            createQueue() {
                this.testQueueLoading = true;
                this.$request({
                    method: 'post',
                    params: {
                        r: 'mall/we7-entry/local-setting',
                        action: 'testQueue',
                        testQueueStep: 'create',
                    },
                    data: {},
                }).then(e => {
                    if (e.data.code === 0) {
                        this.testQueue(e.data.data.id);
                    } else {
                        this.testQueueLoading = false;
                        this.step = 1;
                        this.$alert(e.data.msg, '提示');
                    }
                }).catch(e => {
                    this.$alert(e.data.msg, '提示');
                    this.testQueueLoading = false;
                    this.step = 1;
                });
            },
            testQueue(id) {
                if (this.testCount >= this.maxTestCount) {
                    this.testCount = 0;
                    this.testQueueLoading = false;
                    this.$alert('队列服务检测失败，请检查队列服务是否运行。', '提示');
                    return;
                }
                this.testCount++;
                this.$request({
                    method: 'post',
                    params: {
                        r: 'mall/we7-entry/local-setting',
                        action: 'testQueue',
                        testQueueStep: 'test',
                    },
                    data: {
                        id: id,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        if (e.data.data.done) {
                            this.$alert('恭喜您队列服务已启动完成', '提示', {
                                confirmButtonText: '进入商城',
                                callback: action => {
                                    this.$navigate({r: 'mall/index/index'});
                                },
                            });
                        } else {
                            setTimeout(() => {
                                this.testQueue(id);
                            }, 1000);
                        }
                    }
                }).catch(e => {
                });
            },
        },
    });
</script>