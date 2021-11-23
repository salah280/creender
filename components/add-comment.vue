<template>
    <div class="modal fade" :id="id" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ lang.why }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <!--<p>{{ lang.why }}</p>-->
                        <div id="radio-value">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" v-model="data.value" name="s-value" id="radio-1" value="0" checked>
                                <label class="form-check-label" for="radio-1">
                                    [{{ lang.select }}]
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" v-model="data.value" name="s-value" id="radio1" value="1">
                                <label class="form-check-label" for="radio1">
                                    {{ lang.option1 }}
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" v-model="data.value" name="s-value" id="radio2" value="2">
                                <label class="form-check-label" for="radio2">
                                    {{ lang.option2 }}
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" v-model="data.value" name="s-value" id="radio3" value="3">
                                <label class="form-check-label" for="radio3">
                                    {{ lang.option3 }}
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" v-model="data.value" name="s-value" id="radio4" value="4">
                                <label class="form-check-label" for="radio4">
                                    {{ lang.option4 }}
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" v-model="data.value" name="s-value" id="radio5" value="5">
                                <label class="form-check-label" for="radio5">
                                    {{ lang.option5 }}
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" v-model="data.value" name="s-value" id="radio6" value="6">
                                <label class="form-check-label" for="radio6">
                                    {{ lang.option6 }}
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" v-model="data.value" name="s-value" id="radio100" value="100">
                                <label class="form-check-label" for="radio100">
                                    {{ lang.option100 }}
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
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
                clickedConfirm: false
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
            }
        }
    };
</script>
