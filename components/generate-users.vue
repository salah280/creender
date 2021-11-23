<template>
    <div class="modal fade" :id="id" tabindex="-1" role="dialog" aria-labelledby="institutionFormLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="institutionFormLabel">{{ lang.create_users }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="form" role="form" @submit.prevent="$emit('passdata', info, instid)">
                    <div class="modal-body">
                        <input type="hidden" class="form-control" name="instid" v-model="instid">
                        <div class="form-group">
                            <label for="usersPerPhoto" class="col-form-label">{{ lang.users_per_photo }}:
                            <input type="number" min="1" step="1" class="form-control" id="usersPerPhoto" name="users_per_photo" v-model="info.users_per_photo" required="required">
                        </div>
                        <div class="form-group">
                            <label for="userGroups" class="col-form-label">{{ lang.user_groups }}</label>:
                            <input type="number" min="1" step="1" class="form-control" id="userGroups" name="user_groups" v-model="info.user_groups" required="required">
                        </div>
                        <div class="form-group">
                            <label for="debugPhotos" class="col-form-label">{{ lang.debug_photos }}</label>:
                            <input type="number" min="0" step="1" class="form-control" id="debugPhotos" name="debug_photos" v-model="info.debug_photos" required="required">
                        </div>
                        <div class="form-group">
                            <label for="filesFolder" class="col-form-label">{{ lang.files_folder }}</label>:
                            <input type="text" class="form-control" id="filesFolder" name="files_folder" v-model="info.files_folder" required="required">
                        </div>
                        <div class="alert alert-danger">
                            {{ lang.reset_confirm }}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ lang.close }}</button>
                        <button type="submit" class="btn btn-primary">{{ lang.confirm }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
    module.exports = {
        props: ['id', 'instid'],
        data: function() {
            return this.initialState();
        },
        mounted: function() {
            $("#" + this.id).on("shown.bs.modal", function() {
                $(this).find("input").get(0).focus();
            });
        },
        methods: {
            initialState: function() {
                return {
                    info: {
                        users_per_photo: 3,
                        user_groups: 10,
                        debug_photos: 10,
                        files_folder: ""
                    }
                };
            },
            resetWindow: function () {
                Object.assign(this.$data, this.initialState());
            }

        }
    };
</script>
