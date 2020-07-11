<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<? $loggedUser = (object) $this->session->all_userdata();?>
<div class="header d-lg-flex p-0">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-9 order-lg-first">
        <ul class="nav nav-tabs border-0 flex-lg-row">
          <li class="nav-item" style="background: teal; color: white;">LABSYS</li>
          <li class="nav-item">
            <a href="<? echo base_url('') ?>" class="nav-link <? echo $this->uri->segment(1) == '' ? 'active' : '' ?>"><i class="fe fe-home"></i> Home</a>
          </li>
          <li class="nav-item dropdown" style="display: ">
            <a href="javascript:void(0)" class="nav-link <? echo ($this->uri->segment(1) == 'daily_ari') ? 'active' : '' ?>" data-toggle="dropdown"><i class="fe fe-book-open"></i> Analisa Rendemen Individu</a>
            <div class="dropdown-menu dropdown-menu-arrow">
              <a href="<? echo site_url('/daily_ari')?>" class="dropdown-item " style=""><i class="fe fe-search"></i> Rekapitulasi Analisa Harian</a>
            </div>
          </li>
        </ul>
      </div>
      <div class="col-3 text-right ">
        <div class="nav-item dropdown">
          <a href="#" class="nav-link pr-0" data-toggle="dropdown"><i class="fe fe-user mr-2"></i> <??></a>
          <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
            <div class="dropdown-header text-muted" style="margin-top: -10px;"><??></div>
            <div class="dropdown-header text-muted" style="margin-top: -10px;"><??></div>
            <a class="dropdown-item" href="<? echo site_url('/landing/logout')?>">
              <i class="dropdown-icon fe fe-log-out"></i> Log out
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
