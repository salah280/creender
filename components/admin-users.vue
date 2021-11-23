<template>
    <div v-if="institutionInfo.data">
        <h1 class="p-3">
            {{ institutionInfo.data.name }}
            <!-- <span class="display-sm-block badge badge-success badge-pill">{{ institutionCode }}</span> -->
            <span class="display-sm-block">
                <a v-if="institutionInfo.data.confirmed_users == 0" class='ml-sm-4 ml-0 btn btn-primary' :href='"api/?action=exportCsv&id=" + institutionInfo.data.id'>
                    <i class="fas fa-file-csv"></i>&nbsp; Download CSV
                </a>
                <router-link class='ml-sm-4 ml-0 btn btn-primary' to="/admin">
                    <i class="fas fa-arrow-left"></i> Back
                </router-link>
            </span>
        </h1>
        <p v-if="!institutionInfo.users.length">
            Non ci sono utenti
        </p>
        <table class="table table-striped" v-else>
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Code</th>
                    <th scope="col">Username</th>
                    <th scope="col">Password</th>
                    <th scope="col">Group</th>
                    <th scope="col" v-if="institutionInfo.data.confirmed_users">Logged</th>
                    <th scope="col" v-if="institutionInfo.data.confirmed_users">Social</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in institutionInfo.users" v-bind:key="user">
                    <th scope="row">{{ user.id }}</th>
                    <td>{{ institutionCode }}</td>
                    <td>{{ user.username }}</td>
                    <td>{{ !institutionInfo.data.confirmed_users ? user.password : "***" }}</td>
                    <td>{{ user.usergroup }}</td>
                    <td v-if="institutionInfo.data.confirmed_users">{{ user.logged != 0 ? lang.yes : lang.no }}</td>
                    <td v-if="institutionInfo.data.confirmed_users">{{ user.social_email ? user.social_email : lang.no }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    module.exports = {
        data: function() {
            return {
                institutionInfo: {}
            };
        },
        computed: {
            institutionCode: function() {
                return this.institutionInfo.data.id + "-" + this.institutionInfo.data.code;
            }
        },
        mounted: function() {
            var self = this;
            $.ajax("api/", {
                method: "GET",
                data: {
                    id: self.$route.params.id,
                    action: "getInstitutionInfo"
                },
                success: function(data) {
                    self.institutionInfo = data;
                }
            });
        }
    }
</script>

