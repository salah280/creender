<template>

    <div>
        <admin-login :logininfo.sync="loginInfo" v-if="!admin" @login="login"></admin-login>

        <div v-else>
          
            <nav class="navbar navbar-expand-md navbar-light fixed-top">
                <a class="navbar-brand" >
                    <router-link class="navbar-brand" to="/admin">
                        <img src="img/creender-top.png" height="30" alt="" />
                    </router-link>
                     {{ lang.adminpanel }}
                     
                </a>
              
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                    <div class="collapse navbar-collapse"></div>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                             

                            <ul class="navbar-nav ml-auto">
                                <li class="nav-item ">
                                     <a class='btn btn-sm btn-danger' href="#" @click.prevent="logout">{{ lang.logout }}</a>
                                </li>
                            </ul>

                        </div>
                </nav> 
            
 
            <div class="container-fluid p-5" id="content">
                <router-view></router-view>
            </div>

        </div>
    </div>
</template>

<script>
    module.exports = {
        data: function() {
            return {
                "loginInfo": {
                    "password": ""
                }
            }
        },
        props: ['admin'],
        components: {
            "admin-login": httpVueLoader('components/admin-login.vue')
        },
        methods: {
            login: function(data) {
                var self = this;
                $.ajax("api/?action=adminLogin", {
                    method: "POST",
                    data: {
                        password: data.password
                    },
                    success: function(data) {
                        if (data.result === "OK") {
                            self.$emit("update:admin", true);
                        }
                        else {
                            self.modalData.body = data.error;
                            self.modalData.button = self.lang.ok;
                            self.modalData.title = self.lang.error;
                            $("#" + self.modalID).modal();
                        }
                        self.loginInfo.password = "";
                    }
                });
            },
            logout: function() {
                this.$emit("logout");
            }
        }
    }
</script>

<style>
.navbar{
    background:#fff;
}
</style>

