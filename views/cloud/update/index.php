<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/8 9:41
 */
?>
<div id="app" v-cloak>
    <div v-loading="loading">
        <template v-if="host_version">
            <div flex>
                <div>当前版本:</div>
                <div>{{host_version}}</div>
            </div>
        </template>
        <template v-if="next_version">
            <div flex>
                <div>下一版本:</div>
                <div>{{next_version.version}}</div>
            </div>
        </template>
        <template v-if="list">
            <div flex>
                <div>更新记录:</div>
                <div>
                    <div v-for="item in list">
                        <div>{{item.version}}</div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
<script>
new Vue({
    el: '#app',
    data() {
        return {
            loading: false,
            host_version: null,
            next_version: null,
            list: null,
        };
    },
    created() {
        this.loadData();
    },
    methods: {
        loadData() {
            this.$request({
                params: {
                    r: 'cloud/update/index',
                }
            }).then(e => {
                if (e.data.code === 0) {
                    this.host_version = e.data.data.host_version;
                    this.next_version = e.data.data.next_version;
                    this.list = e.data.data.list;
                } else {

                }
            }).catch(e => {
            });
        },
    },
});
</script>