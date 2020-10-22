<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/6/15 15:00
 */

?>
<style>
    .code-block {
        background: #e8efee;
        border-left: 2px solid #d2d2d2;
        margin: 10px 0;
        padding: 10px 10px;
        white-space: pre-line;
    }

    .not-exists-f {
        margin-right: 20px;
        display: inline-block;
    }

    .not-exists-f:last-child {
        margin-right: 0;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never">
        <div>
            <div style="padding-left: 40px;">
                <el-alert
                        v-if="env.not_exists_fs && env.not_exists_fs.length"
                        title="检测到您服务器的PHP有以下函数被禁用了，请从PHP禁用函数列表中移除掉它们，否则队列服务将无法运行。"
                        type="error">
                    <code v-for="(f, i) in env.not_exists_fs" :key="i" class="not-exists-f">{{f}}</code>
                </el-alert>
            </div>
            <ol>
                <li>
                    <?php
                    $queueFile = Yii::$app->basePath . '/queue.sh';
                    $command = 'chmod a+x ' . $queueFile . ' && ' . $queueFile;
                    ?>
                    <h4>启动服务</h4>
                    <div>Linux使用SSH远程登录服务器，运行命令：</div>
                    <pre class="code-block"><?= $command ?></pre>
                </li>
                <li>
                    <h4>测试服务</h4>
                    <el-button style="margin-bottom: 10px" @click="createTestQueue" :loading="testLoading">开始测试
                    </el-button>
                    <div style="color: #909399">测试过程最多可能需要两分钟的时间。</div>
                </li>
                <li>
                    <h4>第三队列开启</h4>
                    <el-button style="margin-bottom: 10px" @click="createNewQueue" :loading="testLoading">开启队列
                    </el-button>
                    <div style="color: #909399">为加快队列的执行，现新增一条队列用于处理一些次要的任务。</div>
                </li>
            </ol>
        </div>
    </el-card>
</div>
<script>
    new Vue({
        el: '#app',
        data() {
            return {
                testLoading: false,
                testCount: 0,
                maxTestCount: 60,
                env: {
                    not_exists_fs: [],
                },
            };
        },
        created() {
            this.checkEnv();
        },
        methods: {
            createTestQueue() {
                this.testLoading = true;
                this.$request({
                    params: {
                        r: 'admin/setting/queue-service',
                        action: 'create',
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.testQueue(e.data.data.id, e.data.data.time, 'test');
                    } else {
                        this.$alert(e.data.msg);
                    }
                });
            },
            testQueue(id, time, action) {
                if (this.testCount >= this.maxTestCount) {
                    this.testLoading = false;
                    this.testCount = 0;
                    this.$alert('队列服务测试失败，请检查服务是否正常运行。');
                    return;
                }
                this.testCount++;
                this.$request({
                    params: {
                        r: 'admin/setting/queue-service',
                        action: action,
                        id: id,
                        time: time,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        if (e.data.data.done) {
                            this.testLoading = false;
                            this.testCount = 0;
                            this.$alert('队列服务测试通过，服务已正常运行。');
                        } else {
                            setTimeout(() => {
                                this.testQueue(id, time, action);
                            }, 1000);
                        }
                    } else {
                        this.$alert(e.data.msg);
                    }
                });
            },
            checkEnv() {
                this.$request({
                    params: {
                        r: 'admin/setting/queue-service',
                        action: 'env',
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.env.not_exists_fs = e.data.data.not_exists_fs;
                    }
                });
            },
            createNewQueue() {
                this.testLoading = true;
                this.$request({
                    params: {
                        r: 'admin/setting/queue-service',
                        action: 'create-queue',
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.testQueue(e.data.data.id, e.data.data.time, 'test3');
                    } else {
                        this.$alert(e.data.msg);
                    }
                });
            },
        },
    });
</script>
