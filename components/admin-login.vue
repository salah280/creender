<template>
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