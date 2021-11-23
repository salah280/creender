<template>
    <div class="modal fade" :id="id" tabindex="-1" role="dialog" aria-labelledby="institutionFormLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="institutionFormLabel">{{ title }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="form" role="form" @submit.prevent="$emit('passdata', info)">
                    <div class="modal-body">
                        <input type="hidden" class="form-control" name="id">
                        <div class="form-group">
                            <label for="institutionName" class="col-form-label">{{ lang.name }}:</label>
                            <input type="text" class="form-control" id="institutionName" name="name" v-model="info.name" required="required">
                        </div>
                        <div class="form-group">
                            <label for="lang" class="col-form-label">{{ lang.language }}:</label>
                            <select class="form-control" id="lang" name="language" v-model="info.language" required="required">
                            <option v-for="option in lang._langs" v-bind:key="option">
                                {{ option }}
                            </option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="codeValue" class="col-form-label">{{ lang.institution_code }}:</label>
                            <input pattern="[0-9]{5}" class="form-control" id="codeValue" name="code" v-model="info.code" required="required">
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="socialCheck" v-model="info.allow_social_login" />
                                <label class="form-check-label" for="socialCheck">
                                Permetti login social
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ lang.close }}</button>
                        <button onclick="location.reload();" type="submit" class="btn btn-primary" >{{ lang.confirm }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
    module.exports = {
        props: ['info', 'title', 'id'],
        mounted: function() {
            $("#" + this.id).on("shown.bs.modal", function() {
                $(this).find("input").get(0).focus();
            });
        }
    };
</script>
