<style>
a {
    color: #0275d8;
    text-decoration: none;
}

.table-body {
    padding: 20px;
    background-color: #fff;
}
</style>
<div id="app" v-cloak>
    <el-card class="box-card" v-loading="tutorialLoading" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>教程设置</span>
            </div>
        </div>
        <div class="table-body">
            <span>点击查看</span><a target="_blank" :href="form.url">操作教程文档链接</a>
        </div>
    </el-card>
</div>
<script>
const app = new Vue({
    el: '#app',
    data() {
        return {
            form: {},
            tutorialLoading: false,
        };
    },
    methods: {
        getList() {
            let self = this;
            self.tutorialLoading = true;
            request({
                params: {
                    r: 'mall/tutorial/index',
                },
            }).then(e => {
                self.tutorialLoading = false;
                if (e.data.code == 0) {
                    self.form = e.data.data;
                } else {
                    self.$message.error(e.data.msg);
                }
            }).catch(e => {
                console.log(e);
            });
        },
    },
    mounted: function() {
        this.getList();
    }
});
</script>