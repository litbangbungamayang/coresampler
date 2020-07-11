loadNamaVendor();
$('#kode_transaksi').selectize({create: false, sortField: 'text'});

var txtKuantaBahan = $("#kuanta_bahan");
var txtRupiahBahan = $("#rupiah_bahan");
var lblSatuanBahan = $("#satuan_bahan");
var cbxNamaBahan = $("#nama_bahan");
var cbxNamaVendor = $("#nama_vendor");
var cbxTahunGiling = $("#tahun_giling");
var txtCatatan = $("#catatan");
var btnSimpanTransaksi = $("#btnSimpanTransaksi");
var dialogAddTransaksi = $("#dialogAddTransaksi");
var tblTransaksi = $("#tblTransaksi");

txtKuantaBahan.bind("keyup blur", function(){
  $(this).val($(this).val().replace(/[^0-9]/g,""));
  ($(this).val() != "") ? $(this).val(parseInt($(this).val()).toLocaleString()) : "";
});

txtRupiahBahan.bind("keyup blur", function(){
  $(this).val($(this).val().replace(/[^0-9]/g,""));
  ($(this).val() != "") ? $(this).val("Rp " + parseInt($(this).val()).toLocaleString()) : "";
});

cbxTahunGiling.selectize({
  onChange: function(value){
    if (value != ""){
      $.ajax({
        url: js_base_url + "Admin_bahan/getBahanByTahunGiling",
        type: "GET",
        dataType: "json",
        data: {tahun_giling: value},
        success: function(response){
          $listNamaBahan = $("#nama_bahan").selectize();
          listNamaBahan = $listNamaBahan[0].selectize;
          listNamaBahan.clear();
          listNamaBahan.clearOptions();
          listNamaBahan.load(function (callback){
            callback(response);
          })
        }
      });
    }
  }
})

$("#nama_bahan").selectize({
  valueField: "id_bahan",
  labelField: "nama_bahan",
  sortField: "nama_bahan",
  searchField: "nama_bahan",
  maxItems: 1,
  create: false,
  placeholder: "Pilih bahan",
  options: [],
  onChange: function(value){
    if (value != ""){
      var selCbxNamaBahan = cbxNamaBahan.selectize()[0].selectize;
      lblSatuanBahan.val(selCbxNamaBahan.options[value]["satuan"]);
    } else {
      lblSatuanBahan.val("");
    }
  }
});

btnSimpanTransaksi.on("click", function(){
  if (validasiForm() != null){
    var formValue = validasiForm();
    formValue.kode_transaksi = 1;
    formValue.id_kelompoktani = null;
    $.ajax({
      url: js_base_url + "Transaksi_bahan/addTransaksi",
      dataType: "text",
      type: "POST",
      data: formValue,
      success: function(data){
        if (data != ""){
          alert("Data telah tersimpan!");
          dialogAddTransaksi.modal("toggle");
          tblTransaksi.DataTable().ajax.reload();
        }
      }
    });
  }
})

function resetForm(){
  cbxNamaBahan.selectize()[0].selectize.setValue("");
  cbxNamaVendor.selectize()[0].selectize.setValue("");
  txtKuantaBahan.val("");
  txtRupiahBahan.val("");
  txtCatatan.val("");
}

function resetFeedback(){
  cbxNamaBahan.removeClass("is-invalid");
  cbxNamaVendor.removeClass("is-invalid");
  txtKuantaBahan.removeClass("is-invalid");
  txtRupiahBahan.removeClass("is-invalid");
  cbxTahunGiling.removeClass("is-invalid");
  txtCatatan.removeClass("is-invalid");
}

dialogAddTransaksi.on("hide.bs.modal", function(){
  resetForm();
  resetFeedback();
})

function validasiForm(){
  var idBahan = cbxNamaBahan.selectize()[0].selectize.getValue();
  var idVendor = cbxNamaVendor.selectize()[0].selectize.getValue();
  var kuanta = txtKuantaBahan.val().replace(/[^0-9]/g,"");
  var rupiah = txtRupiahBahan.val().replace(/[^0-9]/g,"");
  var tahunGiling = cbxTahunGiling.val();
  var catatan = txtCatatan.val();
  if (idBahan != "" && idVendor != "" && kuanta != "" && rupiah != ""
    && tahunGiling != "" && catatan != ""){
    var formValue = {id_bahan : idBahan, id_vendor : idVendor, kuanta_bahan : kuanta,
      rupiah_bahan : rupiah, tahun_giling : tahunGiling, catatan : catatan};
    return formValue;
  } else {
    (idBahan == "") ? cbxNamaBahan.addClass("is-invalid") : "";
    (idVendor == "") ? cbxNamaVendor.addClass("is-invalid") : "";
    (kuanta == "") ? txtKuantaBahan.addClass("is-invalid") : "";
    (rupiah == "") ? txtRupiahBahan.addClass("is-invalid") : "";
    (tahunGiling == "") ? cbxTahunGiling.addClass("is-invalid") : "";
    (catatan == "") ? txtCatatan.addClass("is-invalid") : "";
    return null;
  }
}

function loadNamaVendor(){
  $.ajax({
    url: js_base_url + "Admin_vendor/getAllVendor",
    type: "GET",
    dataType: "json",
    success: function(response){
      $("#nama_vendor").selectize({
        valueField: "id_vendor",
        labelField: "nama_vendor",
        sortField: "nama_vendor",
        searchField: "nama_vendor",
        maxItems: 1,
        create: false,
        placeholder: "Pilih vendor",
        options: response,
        onChange: function(value){

        }
      });
    }
  })
}

tblTransaksi.DataTable({
  bFilter: true,
  bPaginate: true,
  bSort: false,
  bInfo: false,
  order: {7: "asc"},
  dom: '<"row"<"spacer"><"cbxTahunGilingList">f>tpl',
  ajax: {
    url: js_base_url + "Transaksi_bahan/getTransaksiByKode?kode_transaksi=1",
    dataSrc: ""
  },
  columns : [
    {data: "no", render: function(data, type, row, meta){return meta.row + meta.settings._iDisplayStart + 1}},
    {data: "nama_bahan"},
    {data: "nama_vendor"},
    {data: "catatan"},
    {
      data: "kode_transaksi",
      render: function(data, type, row, meta){
        switch(row.kode_transaksi){
          case "1" :
            return "MASUK";
            break;
          case "2" :
            return "KELUAR";
            break;
        }
      },
      className: "text-center"
    },
    {
      data: "kuanta",
      render: function(data, type, row, meta){
        return parseInt(data).toLocaleString(undefined, {maximumFractionDigits:2}) + " " + row.satuan
      },
      className: "text-right"
    },
    {
      data: "rupiah",
      render: function(data, type, row, meta){
        return "Rp " + parseInt(data).toLocaleString(undefined, {maximumFractionDigits:2})
      },
      className: "text-right"
    },
    {
      data: "tgl_transaksi",
      className: "text-center"
    },
    {
      data: "tahun_giling",
      className: "text-center"
    }
  ],
  initComplete: function(){
    $(".dataTables_filter input[type=\"search\"]").css({
      "width": "250px",
      "display": "inline-block",
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
    $("div.cbxTahunGilingList").html('<select style="width: 150px;" name="tahun_giling" id="tahun_giling" class="custom-control custom-select" placeholder="Pilih tahun giling">' + optionTahun + '</select>');
    $("div.spacer").html('<label class="form-label" style="margin: 0px 10px 0px 0px;"></label>');
    //console.log($("#tahun_giling").selectize()[0].selectize.getValue());
    $('#tahun_giling').selectize({create: false, sortField: 'value'});
    $("#tahun_giling").on("change", function(){
      tahun_giling = parseInt($("#tahun_giling").val()) || 0;
      $("#tblTransaksi").DataTable().ajax.url(js_base_url + "Transaksi_bahan/getTransaksiMasukByTahunGiling?tahun_giling=" + tahun_giling).load();
    });
  },
  language: {
    "search": ""
  }
})
