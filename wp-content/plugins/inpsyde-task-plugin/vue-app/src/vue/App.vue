<template>
    <div>
        <app-main-nav></app-main-nav>
        <router-view v-if="notError" @setError="setError"></router-view>
        <app-error v-if="!notError"></app-error>
    </div>
</template>
<script>
    export default {
        data: function () {
            return {
                notError: true
            }
        },
        methods: {
            setError:function () {
                this.notError = false
            }
        },
    };
</script>

