<template >
    <div>
        <modal :id="modalID" :data="modalData"></modal>
        <form-institutions :id="instModalID" :info="institutionInfo" :title="instModalTitle" @passdata="addInstitution"></form-institutions>
        <edit-parameters  :id="editModalID" :info="institutionInfo" :title="editModalTitle" @passdata="editParameters"></edit-parameters>
        <generate-users ref="generateUsers" :id="generateUsersID" :instid="institutionInfo.id" @passdata="generateUsers"></generate-users>

        <div class="container-fluid text-center pa-5">
                <h1 class="mt-5">
                    {{ lang.institutions }} 
                </h1>
                <a class=' btn btn-success mb-3 ' href='#' @click.prevent='addInstitutionForm' ><i class="fas fa-plus"></i>  {{lang.add_institution}} </a>
                
        </div>   

        <div class="container-fluid text-center mt-5"  v-if="Object.keys(this.institutions).length == 0">
            <img src="img/null.png" height="100" alt="" class="mb-5"/>
            <br>
            <p>{{ lang.no_institutions }}</p>            
        </div>

        <div class="container-fluid" v-else>
            <div class="row justify-content-center ">
                <div class="col-lg-3 col-md-6 col-sm-8 col-xs-12 mt-1" v-for="institution in institutions" :key="institution.id" >
                    <div class="card bg-light mx-2 mb-2 rounded-lg mw-100">

                        <div class="card-header">
                            <h5 class="card-title text-center mt-2"><b>Name:  </b>{{ institution.name }}</h5>
                        </div>

                        <div class="card-body" >
                            <p class="card-text"><b>Id:</b>  {{ institution.id }}</p>
                            <p class="card-text"><b>Language:</b>  {{ institution.language }}</p>
                            <p class="card-text"><b>Code:</b>   {{ institution.id }}-{{ institution.code }}</p>
                            <p class="card-text"><b>Users:</b>   {{ institution.usercount }}</p>                            
                        </div>

                        <div class="card-footer text-center">
                            <a class='btn btn-sm btn-warning' :title="lang.edit" href='#' @click.prevent='editInstitution(institution.id)'><i class="fas fa-edit"></i></a>
                            <a class='btn btn-sm btn-danger' onclick="location.reload();" :title="lang.delete" href='#' @click.prevent='deleteInstitution(institution.id)'><i class="fas fa-trash-alt"></i></a>

                            <span v-if="institution.usercount <= 0">
                                <a class='btn btn-sm btn-info'  :title="lang.create_users" href='#'  @click.prevent='generateUsersForm(institution.id)'><i class="fas fa-user-plus"></i></a>
                            </span>
                            <span v-else>
                                <a class='btn btn-sm btn-danger' onclick="location.reload();" :title="lang.reset_users" href='#' @click.prevent='resetUsers(institution.id)'><i class="fas fa-user-slash"></i></a>
                                <router-link class='btn btn-sm btn-primary' :title="lang.list_users" href='#' :to="{name: 'adminID', params: { id: institution.id}}"><i class="fas fa-users"></i></router-link>
                                <a v-if="institution.confirmed_users == 0" title="Download CSV" class='btn btn-sm btn-primary' :href='"api/?action=exportCsv&id=" + institution.id'><i class="fas fa-file-csv"></i></a>
                                <a v-if="institution.confirmed_users == 0" onclick="location.reload();" class='btn btn-sm btn-success' :title="lang.lock_users" href='#' @click.prevent='lockUsers(institution.id)'><i class="fas fa-user-lock"></i></a>
                                <br>
                                <button type="button" class="btn btn-sm btn-success mt-2">
                                    <a class=' btn btn-sm btn-success' href='#' @click.prevent='editParametersForm(institution.id)'><i class="fas fa-cogs"></i> {{lang.edit_institution}} </a>
                                </button>
                            </span>
                        </div>
                                    
                    </div>
                </div>
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
                "editModalID": "editParametersForm",
                "generateUsersID": "generateUsers",
                "modalData": {
                    title: "",
                    body: "",
                    button: ""
                },
                "loginInfo": {
                    "password": ""
                },
                "editModalTitle": "",
                "instModalTitle": "",
                "institutionInfo": {
                    "id": 0,
                    "name": "",
                    "info":{
                        'sent1':"",
                        'sent2':"",
                        'other':false,
                        'use_yes':false,
                        'choices':[]
                    }
                },
                'institutions':{}
            };
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
            },
        },
        components: {
            "form-institutions": httpVueLoader('components/form-institutions.vue'),
            "generate-users": httpVueLoader('components/generate-users.vue'),
            "modal": httpVueLoader('components/modal.vue'),
            "edit-parameters": httpVueLoader('components/edit-parameters.vue') 
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

                            if (confirm(self.lang.ok_create_users)) {
                                    window.location.reload(true)
                                    self.updateInstitutions();
                                }
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
                var self = this;
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
            lockUsers: function(id) {
                var self = this;
                if (confirm(this.lang.lock_confirm)) {
                    $.ajax("api/?action=lockUsers", {
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
                            for (var i=0; i < data.values.length; i++) {
                                self.institutions[data.values[i].id] = data.values[i]
                            }
                        }
                    }
                });
            },

            addInstitutionForm: function() {
                this.instModalTitle = this.lang.add_institution;
                this.institutionInfo = { name: "", id: 0};
                $("#" + this.instModalID).modal();
            },
            editParametersForm: function(id) {
                this.editModalTitle = this.lang.edit_institution;
                if (this.institutions[id].info == "") {
                    this.institutions[id].info = this.institutionInfo.info
                }
                this.institutionInfo = this.institutions[id];
                $("#" + this.editModalID).modal();
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
                            window.location.reload;
                        }
                    });
                }
            },
            editInstitution: function(id) {
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
             editParameters: function() {
                this.editModalTitle = this.lang.edit_institution;
                var self = this;
                
                $.ajax("api/", {
                    method: "GET",
                    data: {                   
                        id: self.institutionInfo.id,
                        action: "institutionInfo",
                        info: JSON.stringify(self.institutionInfo.info),     
                    },
                    success: function(data) {
                        if (data.result == "OK") {
                            self.institutionInfo = data.values;  
                                     
                            if (confirm('Conferma modifiche?')) {
                                    $("#" + self.editModalID).modal('hide');
                                }
                                else {                                 
                                }
                        }
                        else {
                            alert(data.error);
                            
                        }
                        
                    }
                });
            }
        }
    }
</script>

<style>
    .link{
        color:white;
    }
    
</style>

