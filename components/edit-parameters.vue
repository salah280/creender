<template>
    <div class="modal fade" :id="id" tabindex="-1" role="dialog" aria-labelledby="editParametersFormLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            
            <div class="modal-content ">
                <div class="modal-header ">
                    <h5 class="modal-title" id="editParametersFormLabel" ><!--{{ title }}-->Modifica Task</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form class="form" role="form" @submit.prevent="$emit('passdata', info)">
                    
                    <div class="modal-body">
                     
                        <div class="form-group">
                            <label for="sent1" class="col-form-label"><!--{{ lang.name }}:--><b>Frase/Domanda(Principale)</b></label>
                            <textarea class="form-control" id="sent1" name="sent1" v-model="info.info.sent1" required="required"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="sent2" class="col-form-label"><!--{{ lang.name }}:--><b>Frase/Domanda(Motivo Risposta)</b></label>
                            <textarea class="form-control" id="sent2" name="sent2" v-model="info.info.sent2" required="required"></textarea>
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="use_yes"  v-model="info.info.use_yes" />
                                <label class="form-check-label" for="use_yes">
                                    L'utente salta la foto cliccando "SI"
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-check mt-4">
                                <input class="form-check-input " type="checkbox" id="other" v-model="info.info.other"  />
                                <label class="form-check-label" for="checkAltro">
                                    Permetti all'utente di scrivere una risposta
                                </label>
                            </div>
                        </div>
                      
                        <hr/>

                        <p>Opzioni di risposta</p>

                        <div class="input wrapper flex items-center"  >
                            <div class="row mt-2"  v-for="(input, index) in opzioni" :key="index">
                                 <div class="col-md-6">
                                    <input
                                        type="text"
                                        class="form-control"
                                        placeholder="Inserisci opzione"
                                        v-model="info.info.choices[index]"  
                                    />
                                </div>
                                
                                <div class="col-sm-4">
                                    <button
                                        type="button"
                                        class="btn btn-md btn-success mt-1"
                                        @click="addField(input, opzioni)"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
                                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"></path>
                                        </svg>
                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-md btn-danger mt-1"
                                        v-show="opzioni.length > 1"
                                        @click="removeField(index, opzioni), deleteChoices(index)"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-dash" viewBox="0 0 16 16">
                                            <path d="M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <br>

                        <div class="form-group ml-3">
                            <form method='post' action='' enctype="multipart/form-data"> 
                                <p><b>Carica immagini</b></p>
                                <input  type="file" id='files' name="files[]"  multiple ><br>
                                <input class="btn btn-success mt-2" type="button" id="submit" value='Carica '>
                            </form>
                        </div>
                    
                        <br>
                        <div id='preview'></div>


                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ lang.close }}</button>
                            <button type="submit" class="btn btn-success" >{{ lang.confirm }}</button>
                        </div> 
                          
                </form>   
                        
                    
            </div>
        </div>
    </div>
</template>

<script type="text/javascript">

    module.exports = {
       
        props: ['info', 'title', 'id'],
        mounted: function() {
             
            $("#" + this.id).on("shown.bs.modal", function() {
                $(this).find("input").get(0).focus();
            });
            
        },
 
        data() {
            return {
                opzioni: [{ 
                    opzione: "" 
                }],
                
            };
           
        },
    
    
        methods: {
           
       
            addField(value,fieldType) {
            fieldType.push({ value: "" });
            },
            removeField(index, fieldType) {
            fieldType.splice(index, 1);
           
            },
            deleteChoices(index){
                  this.info.info.choices.splice(this.info.info.choices.indexOf(index), 1)
            },  
           
          
        },
    };

        /* $(document).ready(function(){

            $('#submit').click(function(){

                var form_data = new FormData();

                // Read selected files
                var totalfiles = document.getElementById('files').files.length;
                for (var index = 0; index < totalfiles; index++) {
                    form_data.append("files[]", document.getElementById('files').files[index]);
                }

            
                // AJAX request
                $.ajax({
                    url: 'uploadImage.php',
                    type: 'post',
                    data: form_data,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    success: function (response) {
                    
                            for(var index = 0; index < response.length; index++) {
                                var src = response[index];

                                // Add img element in <div id='preview'>
                                $('#preview').append('<img src="'+src+'" width="100px;" height="auto">');
                            }
                        }
                });
            
                
            });
        });*/
    
       
</script>


<style>
.input{
    outline: none;
};

</style>

