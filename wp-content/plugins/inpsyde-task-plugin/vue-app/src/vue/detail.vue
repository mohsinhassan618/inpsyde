<template>
    <div class="container main-wrap">
        <div class="row">
            <div class="pagination">
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <li class="page-item"  :class="[hasPrev ? '' : 'disabled' ]">
                            <router-link class="page-link" :to="{path:'/user/'+( parseInt(query_id))}" >Previous</router-link>
                        </li>
                        <li class="page-item"  :class="[hasNext? '' : 'disabled' ]">
                            <router-link class="page-link" :to="{path:'/user/'+(parseInt(query_id)+2)}"  >Next</router-link>
                        </li>
                    </ul>
                </nav>
            </div>
            <table id="user-details" class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th colspan="2">General Information</th>
                </tr>
                </thead>
                <template v-if="is_object(user) && user.name!=null">
                    <tbody>
                    <tr v-if="user.name != null">
                        <td>Name</td>
                        <td>{{ user.name}}</td>
                    </tr>

                    <tr v-if="user.username != null">
                        <td>User Name</td>
                        <td>{{ user.username}}</td>
                    </tr>

                    <tr v-if="user.email != null">
                        <td>Email</td>
                        <td>{{ user.email}}</td>
                    </tr>

                    <tr v-if="user.phone != null">
                        <td>Phone</td>
                        <td>{{ user.phone}}</td>
                    </tr>

                    <tr v-if="user.website != null">
                        <td>Website</td>
                        <td>{{ user.website}}</td>
                    </tr>
                    </tbody>
                </template>
            </table>

            <table id="user-details-address" class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th colspan="2">Address</th>
                </tr>
                </thead>
                <tbody v-if="is_object(user)">
                <tr v-if="user.address.street != null">
                    <td>Street</td>
                    <td>{{ user.address.street }}</td>
                </tr>

                <tr v-if="user.address.suite != null">
                    <td>Suite</td>
                    <td>{{ user.address.suite }}</td>
                </tr>

                <tr v-if="user.address.city  != null">
                    <td>City</td>
                    <td>{{ user.address.city }}</td>
                </tr>

                <tr v-if="user.address.zipcode != null">
                    <td>Zipcode</td>
                    <td>{{ user.address.zipcode }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
<script>
    import jQuery from 'jquery';
    import Vue from 'vue';
    export default {
        beforeRouteUpdate (to, from, next) {
            // called when the route that renders this component has changed,
            // but this component is reused in the new route.
            // For example, for a route with dynamic params `/foo/:id`, when we
            // navigate between `/foo/1` and `/foo/2`, the same `Foo` component instance
            // will be reused, and this hook will be called when that happens.
            // has access to `this` component instance.

            next();
            this.inpsyde_init();
            this.get_current_user();
            this.find_next();
            this.find_prev();
        },
        data: function () {
            return {
                query_id: null,
                user: null,
                users: null,
                hasNext:null,
                hasPrev:null,
            }
        },
        mounted: function () {
            this.inpsyde_init();
        },
        methods: {
            inpsyde_init:function(){
                    this.query_id = this.$route.params.id -1;
                    if (this.check_api_data()) {
                        if(this.users == null){
                            this.users = Vue.prototype.$users;
                            this.find_prev();
                            this.find_next();
                        }
                    } else {
                        this.get_user();
                    }

                },
            get_user: function () {
                var self = this;
                jQuery.get(wp_rest_api.inpsyde_user_api + 'users')
                    .fail(function () {
                        self.$emit('setError');
                    }).always((response) => {
                    if (response.success) {
                        this.users = response.data;
                        Vue.prototype.$users = this.users;
                        this.find_next();
                        this.find_prev();
                    }
                });
            },
            check_api_data: function () {
                if (Vue.prototype.$users != undefined && Vue.prototype.$users != null && Vue.prototype.$users != '') {
                    return true;
                }
                return false;
            },
            get_current_user: function () {
                var self = this;
                if (this.users[this.query_id] != undefined) {
                    this.user = this.users[this.query_id];
                }else{
                    self.$emit('setError');
                }
            },
            find_next: function () {
                if (this.users[this.query_id + 1] != undefined && this.users[this.query_id + 1] != null ) {
                    this.hasNext = true;
                }else {
                    this.hasNext = false;
                }
            },
            find_prev: function () {

                if (this.users[this.query_id - 1] != undefined && this.users[this.query_id - 1] != null ) {
                    this.hasPrev = true;
                }else{
                    this.hasPrev = false;
                }
            },
            is_object: function (o) {
                if (o != null) {
                    return true;
                }
                return false;
            },

        },
        watch: {
            users: function (newUser, oldUser) {
                this.get_current_user();
            },

        },
    };
</script>
<style>
    #user-details {
        margin: 20px 0px 0px;
    }
    #user-details-address {
        margin-top: 30px;
    }
    .main-wrap {
        margin: 50px 0px 50px;
    }

    table th {
        text-align: center;
    }
</style>

