<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!doctype html>
<html lang="en" dir="ltr" id="main_page">
  <head>
    <?
      $arrayPetani = array();
      $arrayPetani = $this->session->flashdata("arrayPetani");
    ?>
    <? $this->load->view("_partials/head.php")?>
  </head>
  <body class="">
    <div class="page">
      <div class="flex-fill">
        <? //$this->load->view("_partials/upper_banner.php") ?>
        <? $this->load->view("_partials/navbar.php") ?>
        <div class="my-3 my-md-3">
          <script>
            require(['jquery','datatables', 'selectize', 'datepicker'], function() {
              $(document).ready(function() {
                window.js_base_url = "<? echo base_url(); ?>" + "index.php/";
                <?
                  if (isset($before_script)){
                    echo $before_script;
                  }
                ?>
              });
            });
          </script>
          <div class="container">
            <? $this->load->view("_partials/page_title.php") ?>
            <?
              if (isset($content)){
                echo $content;
              }
            ?>
          </div>
          <script>
            require(['jquery','datatables', 'selectize', 'datepicker'], function () {

            	$(document).ready(function () {
                <?
                  if (isset($script)){
                    echo $script;
                  }
                ?>

            		function setCookie(name,value,days) {
            			var expires = "";
            			if (days) {
            				var date = new Date();
            				date.setTime(date.getTime() + (days*24*60*60*1000));
            				expires = "; expires=" + date.toUTCString();
            			}
            			document.cookie = name + "=" + (value || "")  + expires + "; path=/";
            		}

            		function getCookie(name) {
            			var nameEQ = name + "=";
            			var ca = document.cookie.split(';');
            			for(var i=0;i < ca.length;i++) {
            				var c = ca[i];
            				while (c.charAt(0)==' ') c = c.substring(1,c.length);
            				if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
            			}
            			return null;
            		}

            		if (!getCookie('bottombar-hidden')) {
            			$('.js-bottombar').show();
            		}

            		$('.js-bottombar-close').on('click', function (e) {
            			$('.js-bottombar').hide();
            			setCookie('bottombar-hidden', 1, 7);
            			e.preventDefault();
            			return false;
            		});

            	});
            });
          </script>
        </div>
      </div>
      <!-- <? $this->load->view("_partials/lower_banner.php") ?> -->
      <footer class="footer">
        <? $this->load->view("_partials/footer.php") ?>
      </footer>
    </div>
  </body>
</html>
