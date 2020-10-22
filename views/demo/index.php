<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/7 16:57
 */
?>
<div id="app" v-cloak>
    <el-button type="primary" @click="testBtn">{{btnText}}</el-button>
    <el-button :loading="btnLoading" type="primary" @click="testRequest('post')">请求网络POST</el-button>
    <el-button :loading="btnLoading" type="primary" @click="testRequest('get')">请求网络GET</el-button>
    <el-button :loading="btnLoading" type="primary" @click="testCode(400)">测试400</el-button>
    <el-button :loading="btnLoading" type="primary" @click="testCode(403)">测试403</el-button>
    <el-button :loading="btnLoading" type="primary" @click="testCode(404)">测试404</el-button>
    <el-button :loading="btnLoading" type="primary" @click="testCode(500)">测试500</el-button>
    <el-button :loading="btnLoading" type="primary" @click="testCode(502)">测试502</el-button>
    <hr>
    <el-button type="text" @click="openLink(false)">打开链接（当前窗口）</el-button>
    <el-button type="text" @click="openLink(true)">打开链接（新窗口）</el-button>

    <app-attachment @selected="attachmentSelected">选择文件</app-attachment>
    <el-button>
        <app-attachment v-model="myUrl">选择文件(v-model绑定)</app-attachment>
    </el-button>
    <div>文件选择结果: {{myUrl}}</div>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                btnText: '点击我！',
                btnLoading: false,
                myUrl: '',
            };
        },
        methods: {
            testBtn() {
                this.$alert('您点击了按钮。');
            },
            testRequest(method) {
                let self = this;
                self.btnLoading = true;
                request({
                    params: {
                        r: 'demo/index'
                    },
                    method: method,
                    data: {
                        name: 'aaa',
                        pwd: '123456',
                    }
                }).then(e => {
                    self.btnLoading = false;
                    self.$message.success(e.data.msg);
                }).catch(e => {
                    console.log(e);
                });
            },
            testCode(code) {
                this.btnLoading = true;
                request({
                    params: {
                        r: 'demo/index',
                        testCode: code,
                    },
                    method: 'get'
                }).then(e => {
                    this.btnLoading = false;
                    this.$message.success(e.data.msg);
                }).catch(e => {
                    this.btnLoading = false;
                });
            },
            openLink(newWindow) {
                navigateTo({
                    r: 'demo/index',
                    id: 1,
                }, newWindow);
            },
            attachmentSelected(list) {
                console.log(list);
            },
        }
    });
</script>