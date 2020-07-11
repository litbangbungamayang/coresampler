<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!doctype html>
<html lang="en" dir="ltr">
  <head>
    <? $this->load->view("_partials/head.php")?>
  </head>
  <body class="" style="background:url(<? echo base_url('/assets/images/sugarcane_blur.png')?>); background-repeat: no-repeat; background-size: cover; background-position: center; overflow: hidden;">
    <div class="page">
      <div class="page-single">
        <div class="container">
          <div class="row">
            <div class="col col-login mx-auto">
              <div class="card" style="opacity: 0.75">
                <? echo form_open('login_process'); ?>
                <div class="card-body p-6">
                  <img src="<? echo base_url('/assets/images/logo_only.png')?>" class="h-6" alt="" style="margin-bottom: 50px">
                  <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" class="<? echo (form_error('uname') != NULL ? "form-control is-invalid" : "form-control"); ?>" id="uname" name="uname" aria-describedby="emailHelp" value="<? echo set_value('uname'); ?>" placeholder="Enter username">
                    <div class="invalid-feedback"><? echo form_error('uname'); ?></div>
                  </div>
                  <div class="form-group">
                    <label class="form-label">
                      Password
                    </label>
                    <input type="password" class="<? echo (form_error('pwd') != NULL ? "form-control is-invalid" : "form-control"); ?>" id="pwd" name="pwd" value="<? echo set_value('pwd'); ?>" placeholder="Password">
                    <div class="invalid-feedback"><? echo form_error('pwd'); ?></div>
                  </div>
                  <div class="form-footer">
                    <button type="submit" class="btn btn-primary btn-block">Sign in</button>
                  </div>
                </div>
              </div>
              <!-- </form> -->
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
