<template>
    <div>
        <modal :id="modalID" :data="modalData"></modal>
        <form-institutions :id="instModalID" :info="institutionInfo" :title="instModalTitle" @passdata="addInstitution"></form-institutions>
        <generate-users ref="generateUsers" :id="generateUsersID" :instid="institutionInfo.id" @passdata="generateUsers"></generate-users>
        <admin-login :logininfo.sync="loginInfo" v-if="!admin" @login="login"></admin-login>

        <div v-else class="container-fluid">
            <nav class="navbar navbar-expand-md navbar-light bg-light">
                <router-link class="navbar-brand" to="/admin">
                    <img src="img/creender-top.png" height="30" alt="" class="d-inline-block align-top" />
                    {{ lang.adminpanel }}
                </router-link>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a href="#" class="nav-link" @click.prevent="logout">{{ lang.logout }}</a>
                        </li>
                    </ul>
                </div>
            </nav>

            <div id="content">
                <div v-if="!$route.params.id">
                    <h1 class="p-3">{{ lang.institutions }} <a class='ml-4 btn btn-success' href='#' @click.prevent='addInstitutionForm'><i class="fas fa-plus"></i></a></h1>
                    <p v-if="institutions.length == 0">{{ lang.no_institutions }}</p>
                    <table v-else class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Name</th>
                                <th scope="col">Language</th>
                                <th scope="col">Code</th>
                                <th scope="col">Users</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="institution in institutions" :key="institution">
                                <th class="py-4" scope="row">{{ institution.id }}</th>
                                <td class="py-4">{{ institution.name }}</td>
                                <td class="py-4">{{ institution.language }}</td>
                                <td class="py-4">{{ institution.id }}-{{ institution.code }}</td>
                                <td class="py-4">{{ institution.usercount }}</td>
                                <td class="py-3">
                                    <a class='btn btn-sm btn-warning' :title="lang.edit" href='#' @click.prevent='editInstitutionForm(institution.id)'><i class="fas fa-edit"></i></a>
                                    <a class='btn btn-sm btn-danger' :title="lang.delete" href='#' @click.prevent='deleteInstitution(institution.id)'><i class="fas fa-trash-alt"></i></a>
                                    |
                                    <a class='btn btn-sm btn-info' :title="lang.create_users" href='#' @click.prevent='generateUsersForm(institution.id)'><i class="fas fa-user-plus"></i></a>
                                    <a class='btn btn-sm btn-danger' :title="lang.reset_users" href='#' @click.prevent='resetUsers(institution.id)'><i class="fas fa-user-slash"></i></a>
                                    <!-- <a class='btn btn-sm btn-info' :title="lang.list_users" href='#' @click.prevent='listUsers(institution.id)'><i class="fas fa-users"></i></a> -->
                                    <router-link class='btn btn-sm btn-info' :title="lang.list_users" href='#' :to="{name: 'adminID', params: { id: institution.id}}"><i class="fas fa-users"></i></router-link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else>
                    {{ $route.params.id }}
                </div>

                <router-view></router-view>
            </div>
        </div>
    </div>
</template>

<script>
    module.exports = {
        data: function() {
            return {
                "modalID": "modalID",
                "instModalID": "institutionForm",
                "generateUsersID": "generateUsers",
                "modalData": {
                    title: "",
                    body: "",
                    button: ""
                },
                "loginInfo": {
                    "password": ""
                },
                "instModalTitle": "",
                "institutionInfo": {
                    "id": 0,
                    "name": ""
                },
                "institutions": []
            }
        },
        computed: {
            institutionInfoDB: function() {
                var ret = {};
                for (var key of Object.keys(this.institutionInfo)) {
                    if (typeof this.institutionInfo[key] === "boolean") {
                        ret[key] = this.institutionInfo[key] ? 1 : 0;
                    }
                    else {
                        ret[key] = this.institutionInfo[key];
                    }
                }
                return ret;
            }
        },
        props: ['admin'],
        components: {
            "admin-login": httpVueLoader('components/admin-login.vue'),
            "form-institutions": httpVueLoader('components/form-institutions.vue'),
            "generate-users": httpVueLoader('components/generate-users.vue'),
            "modal": httpVueLoader('components/modal.vue')
        },
        mounted: function() {
            this.updateInstitutions();
        },
        methods: {
            generateUsers: function(data, id) {
                data.id = id;
                var self = this;
                $.ajax("api/?action=populatePhoto", {
                    method: "POST",
                    data: data,
                    success: function(data) {
                        if (data.result == "OK") {
                            $("#" + self.generateUsersID).modal("hide");

                            self.modalData.body = self.lang.ok_create_users;
                            self.modalData.button = self.lang.ok;
                            self.modalData.title = self.lang.confirm;
                            $("#" + self.modalID).modal();

                            self.updateInstitutions();
                        }
                        else {
                            alert(data.error);
                        }
                    }
                });
            },
            generateUsersForm: function(id) {
                this.institutionInfo.id = id;
                this.$refs.generateUsers.resetWindow();
                $("#" + this.generateUsersID).modal();
            },
            resetUsers: function(id) {
                if (confirm(this.lang.reset_confirm)) {
                    $.ajax("api/?action=resetInstitution", {
                        method: "POST",
                        data: {
                            id: id
                        },
                        success: function(data) {
                            if (data.result == "OK") {
                                self.updateInstitutions();
                            }
                            else {
                                alert(data.error);
                            }
                        }
                    });
                }
            },
            updateInstitutions: function() {
                var self = this;
                $.ajax("api/?action=getInstitutions", {
                    success: function(data) {
                        if (data.result == "OK") {
                            self.institutions = data.values;
                        }
                    }
                });
            },
            addInstitutionForm: function() {
                this.instModalTitle = this.lang.add_institution;
                this.institutionInfo = { name: "", id: 0 };
                $("#" + this.instModalID).modal();
            },
            deleteInstitution: function(id) {
                if (confirm(this.lang.delete_confirm)) {
                    $.ajax("api/?action=deleteInstitution", {
                        method: "POST",
                        data: {
                            id: id
                        },
                        success: function(data) {
                            if (data.result == "OK") {
                                self.updateInstitutions();
                            }
                            else {
                                alert(data.error);
                            }
                        }
                    });
                }
            },
            editInstitutionForm: function(id) {
                this.instModalTitle = this.lang.edit_institution;
                var self = this;
                $.ajax("api/", {
                    method: "GET",
                    data: {
                        id: id,
                        action: "editInstitution"
                    },
                    success: function(data) {
                        if (data.result == "OK") {
                            self.institutionInfo = data.values;
                            $("#" + self.instModalID).modal();
                        }
                        else {
                            alert(data.error);
                        }
                    }
                });
            },
            addInstitution: function(data) {
                var self = this;
                $.ajax("api/?action=addInstitution", {
                    method: "POST",
                    data: this.institutionInfoDB,
                    success: function(data) {
                        if (data.result == "OK") {
                            $("#" + self.instModalID).modal("hide");
                            self.updateInstitutions();
                        }
                        else {
                            alert(data.error);
                        }
                    }
                });
            },
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
                            self.updateInstitutions();
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

