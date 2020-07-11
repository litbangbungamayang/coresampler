function formatTgl(dateObj){
  if(dateObj != null){
    return dateObj.getFullYear() + "-" + ("0" + (dateObj.getMonth()+1)) + "-" + ("0" + dateObj.getDate()).slice(-2);
  }
  return "";
}

function formatTglStr(dateObj){
  if(dateObj != null){
    return ("0" + dateObj.getDate()).slice(-2) + "-" + ("0" + (dateObj.getMonth()+1)) + "-" + dateObj.getFullYear();
  }
  return "";
}

function approve(id_dokumen){
  $.ajax({
    url: js_base_url + "List_bon_perawatan/validasiDokumen",
    dataType: "text",
    type: "POST",
    data: "id_dokumen=" + id_dokumen,
    success: function(response){
      if (response = "SUCCESS"){
        tahun_giling = parseInt($("#tahun_giling").val()) || 0;
        $("#tblListPpk").DataTable().ajax.url(js_base_url + "List_bon_perawatan/getAllPpk?tahun_giling=0").load();
        alert("Dokumen berhasil divalidasi!");
      }
    }
  });
}

function approveAskep(id_dokumen){
  $.ajax({
    url: js_base_url + "List_bon_perawatan/validasiDokumenAskep",
    dataType: "text",
    type: "POST",
    data: "id_dokumen=" + id_dokumen,
    success: function(response){
      if (response = "SUCCESS"){
        tahun_giling = parseInt($("#tahun_giling").val()) || 0;
        $("#tblListPpk").DataTable().ajax.url(js_base_url + "List_bon_perawatan/getAllPpk?tahun_giling=0").load();
        alert("Dokumen berhasil divalidasi!");
      }
    }
  });
}

$("#tblListPpk").DataTable({
  bFilter: true,
  bPaginate: false,
  bSort: false,
  bInfo: false,
  autoWidth: false,
  ajax: {
    url: js_base_url + "List_bon_perawatan/getAllPpk?tahun_giling=0&tgl_awal=" + "2010-01-01" + "&tgl_akhir=" + "2050-12-31" ,
    dataSrc: ""
  },
  dom: '<"row"<"labelTahunGiling"><"cbxTahunGiling"><"dtpTglAwal"><"dtpTglAkhir">f>tpl',
  columns : [
    {
      data: "no",
      render: function(data, type, row, meta){
        return meta.row + 1;
      }
    },
    {
      data: "no_dokumen"
    },
    {
      data: "tgl_buat",
      className: "text-center"
    },
    { data: "nama_kelompok" },
    { data: "no_kontrak" },
    {
      data: "jml_rupiah",
      render: function(data, type, row, meta){
        return "Rp " + parseInt(row.jml_rupiah).toLocaleString({maximumFractionDigits: 2});
      },
      className: "text-right"
    },
    {
      data: "",
      render: function(data, type, row, meta){
        /*
        if((row.tgl_validasi_bagian == null && row.priv_level == "Asisten Bagian") || (row.priv_level == "Kepala Sub Bagian" && row.tgl_validasi_kasubbag == null)){
          return "<span class='tag tag-red'>Belum Divalidasi</span>";
        } else {
          if(row.tgl_validasi_bagian == null && row.tgl_validasi_kasubbag == null){
            return "<span class='tag tag-red'>Belum Divalidasi</span>";
          } else {
            if(row.tgl_validasi_bagian == null || row.tgl_validasi_kasubbag == null){
              return "<span class='tag tag-orange'>Validasi Belum Lengkap</span>";
            }
          }
        }
        return "<span class='tag tag-green'>Sudah Divalidasi</span>";
        */
        if(row.priv_level == "Asisten Bagian"){
          if(row.tgl_validasi_bagian == null && row.tgl_validasi_kasubbag == null){
            return "<span class='tag tag-red'>Belum Divalidasi</span>";
          } else {
            if(row.tgl_validasi_bagian != null && row.tgl_validasi_kasubbag == null){
              return "<span class='tag tag-orange'>Validasi Belum Lengkap</span>";
            } else {
              return "<span class='tag tag-green'>Sudah Divalidasi</span>";
            }
          }
        } else {
          if(row.priv_level == "Kepala Sub Bagian"){
            if(row.tgl_validasi_bagian == null && row.tgl_validasi_kasubbag == null){
              return "<span class='tag tag-red'>Belum Divalidasi</span>";
            } else {
              if(row.tgl_validasi_bagian != null && row.tgl_validasi_kasubbag == null){
                return "<span class='tag tag-orange'>Validasi Belum Lengkap</span>";
              } else {
                return "<span class='tag tag-green'>Sudah Divalidasi</span>";
              }
            }
          }
        }
        if(row.tgl_validasi_bagian == null && row.tgl_validasi_kasubbag == null){
          return "<span class='tag tag-red'>Belum Divalidasi</span>";
        } else {
          if(row.tgl_validasi_bagian == null || row.tgl_validasi_kasubbag == null){
            return "<span class='tag tag-orange'>Validasi Belum Lengkap</span>";
          } else {
            return "<span class='tag tag-green'>Sudah Divalidasi</span>";
          }
        }
      },
      className: "text-center"
    },
    {
      render: function(data, type, row, meta){
        var buttonDetail = '<a class="btn btn-sm btn-cyan" href="Transaksi_aktivitas?no_transaksi=' + row.no_transaksi + '&id_kelompok=' + row.id_kelompok + '" title="Lihat Detail"><i class="fe fe-book-open"></i></a> ';
        if(row.priv_level == "Asisten Bagian"){
          var buttonApproval = '<button class="btn btn-sm btn-primary" onclick = approve(' + row.id_dokumen +') title="Setuju" ><i class="fe fe-check-circle"></i></button> ';
          if(row.tgl_validasi_bagian == null){
            return buttonDetail + buttonApproval;
          } else {
            return buttonDetail;
          }
        } else {
          if(row.priv_level == "Kepala Sub Bagian" && row.tgl_validasi_bagian != null){
            var buttonApproval = '<button class="btn btn-sm btn-primary" onclick = approveAskep(' + row.id_dokumen +') title="Setuju" ><i class="fe fe-check-circle"></i></button> ';
            if(row.tgl_validasi_kasubbag == null){
              return buttonDetail + buttonApproval;
            } else {
              return buttonDetail;
            }
          }
        }
        return buttonDetail;
      },
      className: "text-left"
    }
  ],
  initComplete: function(){
    $(".dataTables_filter input[type=\"search\"]").css({
      "width": "250px",
      "display": "",
      "margin-left": "10px"
    }).attr("placeholder", "Cari");
    $(".dataTables_filter").css({
      "margin": "0px"
    });
    var currYear = parseInt(new Date().getFullYear());
    var i;
    var optionTahun = '<option value="0">Pilih tahun giling</option>';
    for (i=0; i < 4; i++){
      optionTahun += '<option value="' + parseInt(currYear + i) + '">' + parseInt(currYear + i) + '</option>';
    }
    $("div.cbxTahunGiling").html('<select style="width: 150px; margin-bottom: 10px; margin-left: 10px" name="tahun_giling" id="tahun_giling" class="custom-control custom-select" placeholder="Pilih tahun giling">' + optionTahun + '</select>');
    $("div.labelTahunGiling").html('<label style="margin-left: 10px; height: 37px; margin-bottom: 10px; display: block; background: red"></label>');
    $('#tahun_giling').selectize({create: false, sortField: 'value'});
    function refreshTable(){
      var tgl_awal = $("#dtpAwal").datepicker("getDate");
      var tgl_akhir = $("#dtpAkhir").datepicker("getDate");
      if(tgl_awal != null && tgl_akhir != null){
        tgl_awal = formatTgl(tgl_awal);
        tgl_akhir = formatTgl(tgl_akhir);
        tahun_giling = parseInt($("#tahun_giling").val()) || 0;
        $("#tblListPpk").DataTable().ajax.url(js_base_url + "List_bon_perawatan/getAllPpk?tahun_giling=" + tahun_giling +
        "&tgl_awal=" + tgl_awal + "&tgl_akhir=" + tgl_akhir).load();
        console.log("triggered");
      }
    }
    $("#tahun_giling").on("change", function(){
      refreshTable();
    });
    $("div.dtpTglAwal").html("<input autocomplete='off' type='text' class='form-control text-center' placeholder='Tanggal Awal' id='dtpAwal' style='width: 120px; margin-left: 10px;'>");
    $("#dtpAwal").datepicker({
      format: "dd-mm-yyyy"
    });
    $("div.dtpTglAkhir").html("<input autocomplete='off' type='text' class='form-control text-center' placeholder='Tanggal Akhir' id='dtpAkhir' style='width: 120px; margin-left: 10px; margin-bottom: 10px'>");
    $("#dtpAkhir").datepicker({
      format: "dd-mm-yyyy"
    });
    $("#dtpAwal").on("change", function(){
      refreshTable();
    });
    $("#dtpAkhir").on("change", function(){
      refreshTable();
    })
  },
  footerCallback: function (row, data, start, end, display){
    var api = this.api(), data;
    var intRupiah = function ( i ) {
      return typeof i === 'string' ? i.replace(/[\Rp,]/g, '')*1 : typeof i === 'number' ? i : 0;
    };
    totalRupiah = api.column(5).data().reduce( function (a, b) {
        return intRupiah(a) + intRupiah(b);
    },0);
    $(api.column(5).footer()).html('<font color="white" size="3">' + "Rp " + totalRupiah.toLocaleString({maximumFractionDigits: 0}) + ' </font>');
  },
  language: {
    "search": ""
  }
});
