<template>
    <!--<div class="container py-5">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-9 col-lg-6 mx-auto">
                      
                        <div class="card rounded-0">
                            <div class="card-header" id="login-header">
                                <h3 class="mb-0 text-center"><img src="img/creender.png" /></h3>
                            </div>
                            <div class="card-body">
                            <h1 class="my-3">{{ lang.adminpanel }}</h1>
                                <form class="form" role="form" id="formLogin" @submit.prevent="$emit('login', logininfo)">
                                    <div class="form-group">
                                        <label for="pwd1">{{ lang.password }}</label>
                                        <input v-model="logininfo.password" type="password" class="form-control form-control-lg rounded-0" id="pwd1" required="" name="pwd1">
                                    </div>
                                    <button type="submit" class="btn btn-success btn-lg float-right" id="btnLogin">Login</button>
                                </form>
                            </div>
                          
                        </div>

                        <p class="text-center p-3">
                            <router-link to="/">{{ lang.loginpanel }}</router-link>
                        </p>
                   

                    </div>


                </div>
              

            </div>
         
        </div>
       
    </div>-->
    <div class="container">
    <div class="row ">
      <div class="col-md-4 mx-auto">
        <h3 class="text-center logo-creender-home py-4">
          <img src="img/creender.png" class="logo-creender" />
        </h3>

        <h2 class="my-3 text-center">Admin</h2>

        <form
          class="form"
          role="form"
          id="formLogin"
          @submit.prevent="$emit('login', logininfo)"
        >
          <div class="form-group mx-auto">
            <input
              v-model="logininfo.password"
              type="password"
              class="form-control form-control-md form-rounded"
              id="pwd1"
              required=""
              name="pwd1"
              placeholder="Password"
            />
          </div>

          <div class="row py-3 text-center">
            <div class="col-md-12">
              <button
                type="submit"
                class="btn btn-light btn-md"
                id="btnLogin"
              >
                Login
              </button>
            </div>
          </div>
        </form>

        <p class="text-center p-3">
          <router-link to="/">{{lang.loginpanel}}</router-link
          >
        </p>
      </div>
    </div>
  </div>
   
</template>

<script>
    module.exports = {
        props: ['logininfo']
    }
</script>

<style>
    .logo-creender {
        width: 80%;
    }
    .form-rounded {
        border-radius: 1rem;
    }
    #formLogin:valid #btnLogin {
        background : #218838;
        color: white;
    }
</style>