<template>
    <div class="container">
        <div class="row main">
            <div class="col-12">
                <div class="users-table-wrap">
                    <table id="user_table" class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th class="id-col">Id</th>
                            <th>Name</th>
                            <th>Email</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="user in users">
                            <td>
                                <router-link :to="{path:'/user/'+user.id, params:{user,users}}">{{ user.id }}</router-link>
                            </td>
                            <td>
                                <router-link :to="{path:'/user/'+user.id, params:{user,users}}">{{ user.name }}</router-link>
                            </td>
                            <td>
                                <router-link :to="{path:'/user/'+user.id, params:{user,users}}">{{ user.email }}</router-link>
                            </td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr>
                            <th class="id-col">Id</th>
                            <th>Name</th>
                            <th>Email</th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import jQuery from 'jquery';
    import dt from 'datatables.net';
    import Vue from 'vue';
    export default {
        data: function () {
            return {
                users: null,
            }
        },
        mounted: function () {
            if(this.check_api_data()){
                this.users = Vue.prototype.$users;
                this.init_table();

            }else{
                this.get_users();
            }


        },
        methods: {
            get_users: function () {
                jQuery.get(wp_rest_api.inpsyde_user_api + 'users')
                    .fail(function () {
                        this.$emit('setError');
                    }).always((response) => {
                    if (response.success) {
                        this.users = response.data;
                        Vue.prototype.$users = this.users ;
                        this.init_table();


                    }
                });
            },
            check_api_data: function() {
                if(Vue.prototype.$users != undefined && Vue.prototype.$users != null && Vue.prototype.$users != ''){
                    return true;
                }
                return false;
            },
            init_table: function () {
                this.$nextTick(function () {
                    jQuery('#user_table').DataTable();
                });

            }
        },


    };
</script>
<style>
    .main {
        margin: 60px 0px;
    }
    #user_table{
        word-break: break-all
    }
    .id-col{
        min-width: 20px;
    }
</style>


