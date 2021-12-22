<template>
    <div class="modal fade" :id="id" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel" v-for="info in institution" :key="info.index">{{ info.sent2 }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                   
                    
                        <p>Scegli una o più opzioni</p>
                        <form  v-for="info in institution" :key="info.index">
                        <div id="radio-value" v-for="choice in info.choices" :key="choice" >
                            <div class="form-check">
                                <input class="form-check-input" type="radio" v-model="data.value" name="s-value" id="radioChoice" :value="choice"   >
                                <label class="form-check-label" :for="choice">
                                    {{choice}}
                                </label>
                            </div>
                        </div>

                        
                        <div class="form-group" v-if="info.other">
                            <hr/>
                            <label for="message-text" class="col-form-label">{{ lang.what }} ({{lang.insert}})</label>
                            <textarea class="form-control" id="message-text"  v-model="data.comment" ></textarea>
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
                "institution": [],
                "logged_user": {}
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
                        self.institution = data.institution;
                    }
                });
            }
        }
    };
</script>
