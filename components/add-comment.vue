<template>
    <div class="modal fade" :id="id" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel" v-for="item in currentID" :key="item.id">{{ item.info.sent2 }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                   
                    <form  v-for="item in currentID" :key="item.id">
                        <p>Scegli una o più opzioni</p>
                        <div id="radio-value" v-for="choice in item.info.choices" :key="choice" >
                            <div class="form-check">
                                <input class="form-check-input" type="radio" v-model="data.value" name="s-value" id="radioChoice" :value="choice"   >
                                <label class="form-check-label" :for="choice">
                                    {{choice}}
                                </label>
                            </div>
                        </div>

            
                        <div class="form-group" v-if="item.info.other">
                            <label for="message-text" class="col-form-label">{{ lang.what }} ({{lang.insert}})</label>
                            <textarea class="form-control" id="message-text" v-model="data.comment"></textarea>
                        </div>    
                            
                    </form>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ lang.cancel }}</button>
                    <button type="button" @click="userClickedConfirm" class="btn" :class="{ 'btn-success': clickedConfirm, 'btn-info': !clickedConfirm }">{{ lang.confirm }}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    module.exports = {
        props: ['data', 'id'],
        data: function() {
            return {
                clickedConfirm: false,
                "institutions":[],
                "logged_user": {}
            }
        },
        computed: {
            currentID() {
                let tempInstitution = this.institutions
                
                tempInstitution = tempInstitution.filter((item) => {
                return (item.id == this.logged_user.institution)
                })
                
                return tempInstitution;
            }

        },
        mounted: function() {
            comp = this;
            $("#" + this.id).on("show.bs.modal", function() {
                $(this).find("input").get(0).focus();
                comp.clickedConfirm = false;
                comp.data.value = "0";
                comp.data.comment = "";
            });
            this.updateInstitutions();
            this.updateLoginInfo();
            
        },
        methods: {
            userClickedConfirm: function() {
                if (!this.clickedConfirm) {
                    this.clickedConfirm = true;
                }
                else {
                    this.clickedConfirm = false;
                    this.$emit('confirm' );
                }
            },
            updateLoginInfo() {
                var self = this;
                $.ajax("api/?action=loginInfo", {
                    success: function(data) {
                        self.logged_user = data.login;
                       
                    }
                });
            },
            updateInstitutions: function() {
                var self = this;
                $.ajax("api/?action=getInstitutions", {
                    success: function(data) {
                        if (data.result == "OK") {
                            self.institutions= data.values
                                
                        }
                    }
                });
            }
        }
    };
</script>
