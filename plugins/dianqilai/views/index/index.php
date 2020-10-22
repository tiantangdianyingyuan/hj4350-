<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/3
 * Time: 11:44
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
?>
<style>
    .form-body {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 20px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" v-loading="loading"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>客服回调链接</span>
        </div>
        <div class="form-body">
            <div style="color: rgb(2, 117, 216)">
                <div>温馨提示：</div>
                <div>该设置用于客服发送给离线访客消息后，可以通过小程序模版消息对该访客进行通知提醒</div>
            </div>
            <el-input id="url" v-model="url" :readonly="true" size="mini" style="max-width: 600px;"></el-input>
            <div style="margin-top: 24px">
                <el-button size="mini" class="copy-btn" data-clipboard-action="copy" type="primary"
                           data-clipboard-target="#url">复制链接
                </el-button>
                <el-button size="mini" @click="reset">重置链接</el-button>
            </div>
        </div>
    </el-card>
</div>
<script src="https://cdn.jsdelivr.net/clipboard.js/1.5.12/clipboard.min.js"></script>
<script>
    var clipboard = new Clipboard('.copy-btn');

    var self = this;
    clipboard.on('success', function (e) {
        self.ELEMENT.Message.success('复制成功');
        e.clearSelection();
    });
    clipboard.on('error', function (e) {
        self.ELEMENT.Message.success('复制失败，请手动复制');
    });
    const app = new Vue({
        el: '#app',
        data() {
            return {
                url: 'lllll',
                loading: false
            };
        },
        created() {
            this.load();
        },
        methods: {
            reset() {
                this.load(1);
            },
            load(isNew = 0) {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/dianqilai/mall/index/index',
                        is_new: isNew
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.url = e.data.data.url
                    } else {
                        this.$message.error(e.data.msg);
                    }
                });
            }
        }
    });
</script>
