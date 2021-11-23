const routes = [
    { path: "/admin", component: httpVueLoader('components/admin.vue'),
        children: [
            {
                path: ":id/users",
                component: httpVueLoader('components/admin-users.vue'),
                name: "adminID"
            },
            {
                path: "",
                component: httpVueLoader('components/admin-home.vue'),
                
            }
        ]
    },
    // { path: "/admin/:id", component: httpVueLoader('components/admin.vue'), name: "admin"},
    { path: "/", component: httpVueLoader('components/annotate.vue') },
    { path: "/statistics", component: httpVueLoader('components/annotate.vue') }
];



const router = new VueRouter({
    routes: routes
});

const app = new Vue({
    
    router: router,
    data: {
        logged: false,
        admin: false,
        showAdminLogin: false
    },
    mounted: function() {
        Vue.prototype.lang = {};
        $.ajax("api/", {
            method: "GET",
            data: {
                action: "lang"
            },
            success: function(data) {
                Vue.prototype.lang = data;
            }
        });
        this.updateLoginInfo();
    },
    methods: {
        logout: function() {
            var self = this;
            $.ajax("api/", {
                method: "GET",
                data: {
                    action: "logout"
                },
                success: function(data) {
                    self.logged = false;
                    self.admin = false;
                }
            });
        },
        updateLoginInfo: function() {
            var self = this;
            $.ajax("api/", {
                method: "GET",
                data: {
                    action: "loginInfo"
                },
                success: function(data) {
                    self.logged = data.logged;
                    self.admin = data.admin;
                }
            });
        }
    }
}).$mount("#app");
