import Vue from 'vue';
import Nav from './nav.vue';
import Body from './body.vue';
import Detail from './detail.vue';
import Error from './error-msg.vue';
import App from './App.vue';
import Test from './test.vue';
import VueRouter from 'vue-router';

Vue.prototype.$users =[];
Vue.use(VueRouter);

Vue.component('app-main-nav',Nav);
Vue.component('app-body',Body);
Vue.component('app-detail',Detail);
Vue.component('app-error',Error);

const  routes = [
    {
        path: '',
        component: Body,
        name:'allusers'
    },
    {
        path: '/user/:id',
        component: Detail,
        name:'singleuser',
        props: true
    },
    {
        path:'*',
        component:Error,
    }
];

const router  =  new VueRouter({
    routes:     routes,
    mode:       'history',
    base:       '/inpsyde/',

});


new Vue({
    el: '#app',
    router:router,
    render: h => h(App),
});
