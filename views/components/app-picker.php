<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/14 13:49
 */
?>
<style>
.app-picker > div {
    float: left;
}

.app-picker:after {
    clear: both;
    display: block;
    content: " ";
}
</style>
<template id="app-picker">
    <div class="app-picker">
        <div style="display: inline-block">
            <slot name="before"></slot>
        </div>
        <template v-for="(props, index) in reversedList">
            <div style="display: inline-block" @click="rowClick(props)">
                <slot :props="props"></slot>
            </div>
        </template>
        <slot name="after"></slot>
    </div>
</template>
<script>
Vue.component('app-picker', {
    template: '#app-picker',
    props: {
        list: Array,
        multiple: Boolean,
        max: Number,
    },
    data() {
        return {
            checkedList: [],
            reversedList: [],
        };
    },
    watch: {
        list: function (newList, oldList) {
            this.reversedList = [];
            for (let i in newList) {
                this.reversedList.push({
                    id: randomString(),
                    checked: false,
                    row: newList[i],
                });
            }
        }
    },
    created() {
    },
    methods: {
        rowClick(props) {
            if (this.multiple === true) {
                // 多选
                if (typeof this.max === 'number' && !props.checked && this.checkedList.length >= this.max) {
                    // 数量限定
                    return false;
                }
                props.checked = !(props.checked);
                for (let i in this.checkedList) {
                    if (this.checkedList[i].id === props.id) {
                        this.checkedList.splice(i, 1);
                        break;
                    }
                }
                if (props.checked) {
                    this.checkedList.push(props);
                }
            } else {
                // 单选
                for (let i in this.reversedList) {
                    this.reversedList[i].checked = false;
                }
                props.checked = !(props.checked);
                this.checkedList = [];
                if (props.checked === true) {
                    this.checkedList.push(props);
                }
            }
            let newList = [];
            for (let i in this.checkedList) {
                newList.push(this.checkedList[i].row);
            }
            this.$emit('change', newList);
        },
    },
});
</script>
