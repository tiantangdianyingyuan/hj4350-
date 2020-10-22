<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/8
 * Time: 14:46
 */
?>
<style>
    .check-env-table .success-row {
        background: #e1f3d8;
    }

    .check-env-table .error-row {
        background: #fde2e2;
    }

    .step-item {
        padding: 20px;
        border-bottom: 2px solid #909399;
    }

    .convert-errors {
        margin-top: 20px;
        border: 1px solid #909399;
    }

    .convert-error {
        border-bottom: 1px dashed #909399;
    }

    .convert-error:last-child {
        border-bottom: none;
    }
</style>
<div id="app" v-cloak>
    <div class="step-item">
        <h2>1.检查服务器环境</h2>
        <p>请检查服务器环境是否符合新版本商城的要求，检查通过后可进入下一个步骤。</p>
        <el-button @click="checkEnv" :loading="checkEnvLoading" style="margin-bottom: 20px">开始检查</el-button>
        <el-table v-if="checkEnvResult"
                  :data="checkEnvResult.list"
                  :row-class-name="checkEnvRowClassName"
                  border
                  class="check-env-table">
            <el-table-column prop="name" label="检查项" width="200px"></el-table-column>
            <el-table-column prop="status" label="结果" width="100px">
                <template slot-scope="scope">
                    <template v-if="scope.row.pass === 0">未通过</template>
                    <template v-if="scope.row.pass === 1">通过</template>
                </template>
            </el-table-column>
            <el-table-column prop="desc" label="说明"></el-table-column>
        </el-table>
    </div>
    <div class="step-item">
        <h2>2.启动队列服务</h2>
        <?php $basePath = Yii::$app->basePath; ?>
        <h4>- Linux服务器: </h4>
        <p>登录服务器进入命令行，执行如下命令</p>
        <pre>cd <?= $basePath ?> && ./queue.sh</pre>
        <h4>- Windows服务器: </h4>
        <p>远程登录服务器，打开文件夹</p>
        <pre><?= $basePath ?></pre>
        <p>双击运行queue.vbs</p>
        <div style="border-top: 1px dashed #909399;padding-top: 20px">
            <el-button @click="createQueue" :loading="checkQueueLoading">检查队列服务</el-button>
            <div v-if="checkQueueSuccess" style="color: #67c23a">恭喜，队列服务已正常启动。</div>
            <div style="color: #909399">如果检查超过一分钟没有响应，请检查队列服务是否已经正常启动。</div>
        </div>
    </div>
    <div class="step-item">
        <h2>3.转移数据</h2>
        <el-button v-if="!storeList" @click="checkStore">检查数据</el-button>
        <template v-else>
            <p>一共有{{storeList.length}}个商城的数据需要转换，数据转换过程请勿关闭浏览器！</p>
            <el-button @click="convertClick" :loading="convertLoading">开始转换</el-button>
            <p v-if="convertText" style="color: #909399">{{convertText}}</p>
        </template>
        <div class="convert-errors" v-if="convertErrorList && convertErrorList.length">
            <div class="convert-error" v-for="convertError in convertErrorList">{{convertError}}</div>
        </div>
    </div>
</div>
<script>
new Vue({
    el: '#app',
    data() {
        return {
            checkEnvLoading: false,
            checkEnvResult: null,
            checkQueueLoading: false,
            checkQueueCount: 0,
            checkQueueSuccess: false,
            checkStoreLoading: false,
            storeList: null,
            convertLoading: false,
            convertText: null,
            convertErrorList: [],
        };
    },
    created() {
    },
    methods: {
        checkEnv() {
            this.checkEnvLoading = true;
            this.$request({
                params: {
                    r: 'common/convert/check-env',
                },
            }).then(response => {
                this.checkEnvLoading = false;
                if (response.data.code === 0) {
                    this.checkEnvResult = response.data.data;
                }
            }).catch(e => {
            });
        },
        checkEnvRowClassName({row, rowIndex}) {
            if (row.pass === 0) {
                return 'error-row';
            }
            if (row.pass === 1) {
                return 'success-row';
            }
            return '';
        },
        createQueue() {
            this.checkQueueLoading = true;
            this.$request({
                params: {
                    r: 'common/convert/check-queue',
                    action: 'create',
                },
            }).then(response => {
                if (response.data.code === 0) {
                    this.checkQueueCount = 0;
                    this.checkQueue();
                }
            });
        },
        checkQueue() {
            if (this.checkQueueCount >= 65) {
                this.$alert('队列似乎没有启动成功，请检查服务是否已经运行。', '提示');
                this.checkQueueLoading = false;
                return;
            }
            this.$request({
                params: {
                    r: 'common/convert/check-queue',
                    action: 'check',
                },
            }).then(response => {
                this.checkQueueCount++;
                if (response.data.code === 0) {
                    if (response.data.data.retry === 1) {
                        setTimeout(() => {
                            this.checkQueue();
                        }, 1000);
                    } else {
                        this.$alert('恭喜，队列服务已正常启动。', '提示');
                        this.checkQueueSuccess = true;
                        this.checkQueueLoading = false;
                    }
                }
            });
        },
        checkStore() {
            this.checkStoreLoading = true;
            this.$request({
                params: {
                    r: 'common/convert/check-store'
                },
            }).then(response => {
                this.checkStoreLoading = false;
                this.storeList = response.data.data.storeList;
            });
        },
        convertClick() {
            this.convertLoading = true;
            this.convertStore(0);
        },
        convertStore(index) {
            if (typeof index === 'undefined' || index === null) {
                index = 0;
            }
            if (!this.storeList[index]) {
                this.convertSystemData();
            }
            this.convertText = '正在转换第' + (index + 1) + '个商城数据';
            this.$request({
                method: 'post',
                params: {
                    r: 'common/convert/convert-store',
                },
                data: {
                    id: this.storeList[index].id,
                }
            }).then(response => {
                const nextIndex = index + 1;
                if (this.storeList[nextIndex]) {
                    this.convertStore(nextIndex);
                } else {
                    this.convertSystemData();
                }
                if (response.data.code === 1) {
                    this.convertErrorList.push(response.data.msg);
                }
            }).catch(e => {
            });
        },
        convertSystemData() {
            this.convertText = '正在处理系统数据';
            this.$request({
                method: 'post',
                params: {
                    r: 'common/convert/convert-system-data',
                },
                data: {},
            }).then(response => {
                this.convertLoading = false;
                this.convertText = null;
            }).catch(e => {
            });
        },
    },
});
</script>
