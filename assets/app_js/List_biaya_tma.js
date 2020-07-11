
function cetak(){
  window.print();
  window.onafterprint = function(){
    alert("Printing finished");
  }
}

function approve(id_dokumen){
  if(confirm("Anda akan menyetujui pengajuan biaya TMA ini?")){
    $.ajax({
      url: js_base_url + "List_biaya_tma/validasiDokumen",
      dataType: "text",
      type: "POST",
      data: "id_dokumen=" + id_dokumen,
      success: function(response){
        if (response = "SUCCESS"){
          tahun_giling = parseInt($("#tahun_giling").val()) || 0;
          $("#tblListBiayaTma").DataTable().ajax.url(js_base_url + "List_biaya_tma/getAllPbtma?tahun_giling=" + tahun_giling).load();
          alert("Dokumen berhasil divalidasi!");
        }
      }
    });
  }
}

function approveAskep(id_dokumen){
  if(confirm("Anda akan menyetujui pengajuan biaya TMA ini?")){
    $.ajax({
      url: js_base_url + "List_biaya_tma/validasiAskep",
      dataType: "text",
      type: "POST",
      data: "id_dokumen=" + id_dokumen,
      success: function(response){
        if (response = "SUCCESS"){
          tahun_giling = parseInt($("#tahun_giling").val()) || 0;
          $("#tblListBiayaTma").DataTable().ajax.url(js_base_url + "List_biaya_tma/getAllPbtma?tahun_giling=" + tahun_giling).load();
          alert("Dokumen berhasil divalidasi!");
        }
      }
    });
  }
}

function cancel(id_dokumen){
  if(confirm("Anda yakin akan membatalkan dokumen ini?")){
    $.ajax({
      url: js_base_url + "List_biaya_perawatan/batalkanDokumen",
      dataType: "text",
      type: "POST",
      data: "id_dokumen=" + id_dokumen,
      success: function(response){
        if (response = "SUCCESS"){
          tahun_giling = parseInt($("#tahun_giling").val()) || 0;
          $("#tblListPerawatan").DataTable().ajax.url(js_base_url + "List_biaya_perawatan/getAllPbp?tahun_giling=0").load();
          alert("Dokumen berhasil dibatalkan!");
        }
      }
    });
  }
}


$("#tblListBiayaTma").DataTable({
  bFilter: false,
  bPaginate: false,
  bSort: false,
  bInfo: false,
  autoWidth: false,
  ajax: {
    url: js_base_url + "List_biaya_tma/getAllPbtma?tahun_giling=0" ,
    dataSrc: ""
  },
  dom: '<"row"<"labelTahunGiling"><"cbxTahunGiling">f>tpl',
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
    {
      data: "netto",
      render: function(data, type, row, meta){
        return parseFloat(row.netto/1000).toLocaleString({maximumFractionDigits: 0}) + " TON";
      },
      className: "text-right"
    },
    {
      data: "total",
      render: function(data, type, row, meta){
        return "Rp " + parseInt(row.total).toLocaleString({maximumFractionDigits: 0});
      },
      className: "text-right"
    },
    {
      data: "catatan",
      className: "text-center"
    },
    {
      data: "",
      render: function(data, type, row, meta){
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
        var buttonDetail = '<a class="btn btn-sm btn-cyan" href="Cetak_pbtma?id_pbtma=' + row.id_dokumen + '" title="Lihat Detail"><i class="fe fe-book-open"></i></a> ';
        if(row.priv_level == "Asisten Bagian"){
          var buttonApproval = '<button class="btn btn-sm btn-primary" onclick = approve(' + row.id_dokumen +') title="Setuju" ><i class="fe fe-check-circle"></i></button> ' +
          '<button type="button" class="btn btn-sm btn-red" onclick = cancel(' + row.id_dokumen + ') title="Batalkan"><i class="fe fe-trash-2"></i></button>';
          if(row.tgl_validasi_bagian == null){
            return buttonDetail + buttonApproval;
          } else {
            return buttonDetail;
          }
        } else {
          if(row.priv_level == "Kepala Sub Bagian" && row.tgl_validasi_bagian != null){
            var buttonApproval = '<button class="btn btn-sm btn-primary" onclick = approveAskep(' + row.id_dokumen +') title="Setuju"><i class="fe fe-check-circle"></i></button> ' +
            '<button type="button" class="btn btn-sm btn-red" onclick = cancel(' + row.id_dokumen + ') title="Batalkan"><i class="fe fe-trash-2"></i></button>';
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
      "width": "1px",
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
    $("#tahun_giling").on("change", function(){
      tahun_giling = parseInt($("#tahun_giling").val()) || 0;
      $("#tblListBiayaTma").DataTable().ajax.url(js_base_url + "List_biaya_tma/getAllPbtma?tahun_giling=" + tahun_giling).load();
    })
  },
  footerCallback: function (row, data, start, end, display){
    var api = this.api(), data;
    var intRupiah = function ( i ) {
      return typeof i === 'string' ? i.replace(/[\Rp,]/g, '')*1 : typeof i === 'number' ? i : 0;
    };
    var intNetto = function ( i ) {
      return typeof i === 'string' ? i.replace(/[\TON,]/g, '')*1 : typeof i === 'number' ? i : 0;
    };
    totalBiaya = api.column(4).data().reduce( function (a, b) {
        return intRupiah(a) + intRupiah(b);
    },0);
    totalNetto = api.column(3).data().reduce( function (a, b) {
        return intNetto(a) + intNetto(b);
    },0);
    $(api.column(3).footer()).html('<font color="white" size="3">' + parseFloat(totalNetto/1000).toLocaleString({maximumFractionDigits: 0}) + ' TON </font>');
    $(api.column(4).footer()).html('<font color="white" size="3">' + 'Rp ' + totalBiaya.toLocaleString({maximumFractionDigits: 0}) + '</font>');
  },
  language: {
    "search": ""
  }
});
