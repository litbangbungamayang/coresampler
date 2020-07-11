<div class="header py-0" style="">
  <div class="container">
    <div class="d-flex">
      <!-- COMPANY LOGO -->
      <a class="header-brand" href="<? echo base_url('') ?>">
        <img src="<? echo base_url('/assets/images/Logo BCN - SIMTR.png')?>" class="header-brand-img" alt="tabler logo" style="display: none">
        <div class="page-title">Core Sampler</div>
      </a>
      <!-------------------->
      <div class="d-flex order-lg-2 ml-auto">
        <!-- USER INFO -->
        <? $loggedUser = (object) $this->session->all_userdata(); ?>
        <div class="dropdown">
          <a href="#" class="nav-link pr-0" data-toggle="dropdown">
            <span class="text-default"><i class="fe fe-user"></i> <? echo $loggedUser -> nama_user; ?> <small class="text-muted mt-1" style="margin-left: 10px;"><? echo $loggedUser -> jabatan; echo (($loggedUser->afd) !== NULL ? ' Afdeling '.$loggedUser -> afd : ''); ?></small></span>
          </a>
          <div class="dropdown-menu dropdown-menu-left dropdown-menu-arrow">
            <a class="dropdown-item" href="<? echo site_url('/landing/logout')?>">
              <i class="dropdown-icon fe fe-log-out"></i> Log out
            </a>
          </div>
        </div>
        <!----------------------------------------------------------->
      </div>
    </div>
  </div>
</div>
