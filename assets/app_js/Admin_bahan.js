$('#jenis_bahan').selectize({create: false, sortField: ''});
$('#satuan').selectize({create: false, sortField: 'text'});

var namaBahan = $("#nama_bahan");
var jenisBahan = $("#jenis_bahan");
var dosisBahan = $("#dosis");
var tahunGiling = $("#tahun_giling");
var biaya_angkut = $("#biaya_angkut");
var biaya_muat = $("#biaya_muat");
var cbxRelasiAktivitas =$("#relasi_aktivitas");
var satuan = $("#satuan");
var tblBahan = $("#tblBahan");
var formAddBahan = $("#formAddBahan");
var dialogAddBahan = $("#dialogAddBahan");
var btnTambahBahan = $("#btnTambahBahan");
var edit = false;

/*
tahunGiling.on("change", function(){
  if($(this).val() != ""){
    console.log($(this).val());
    loadAktivitas($(this).val());
  }
});
*/

function loadAktivitas(tahun_giling){
  $.ajax({
    url: js_base_url + "Admin_aktivitas/getAktivitasByTahunGiling",
    type: "GET",
    dataType: "json",
    data: {
      tahun_giling: tahun_giling
    },
    success: function(response){
      $("#relasi_aktivitas").selectize();
      $("#relasi_aktivitas").selectize()[0].selectize.clear();
      $("#relasi_aktivitas").selectize()[0].selectize.clearOptions();
      $("#relasi_aktivitas").selectize()[0].selectize.load(function(callback){
        callback(response);
      });
    }
  });
}

btnTambahBahan.on("click", function(){
    tahunGiling[0].selectize.enable();
    tahunGiling[0].selectize.clear();
    dosisBahan[0].disabled = false;
})

dosisBahan.bind("keyup blur", function(){
  $(this).val($(this).val().replace(/[^0-9. ]/g,""));
});
dosisBahan.on("blur", function(){
  if ($(this).val() != ""){
    $(this).val(parseFloat($(this).val()));
  }
})

dialogAddBahan.on("hide.bs.modal", function(){
  namaBahan.val("");
  jenisBahan[0].selectize.clear();
  satuan[0].selectize.clear();
  tahunGiling[0].selectize.clear();
  dosisBahan.val("");
  tblBahan.DataTable().ajax.reload();
})

function hapusData(id){
  $.ajax({
    url: js_base_url + "Admin_bahan/getBahanById",
    type: "GET",
    data: "idBahan=" + id,
    dataType: "json",
    success: function(response){
      if (confirm("Anda yakin akan menghapus data bahan " + response.nama_bahan + "?")){
        $.ajax({
          url: js_base_url + "Admin_bahan/hapusBahan",
          type: "POST",
          dataType: "text",
          data: {id_bahan: id},
          success: function(result){
            console.log(result);
            alert(result);
            tblBahan.DataTable().ajax.reload();
          }
        });
      }
    }
  });
}

function editData(id){
  dialogAddBahan.modal("toggle");
  $.ajax({
    url: js_base_url + "Admin_bahan/getBahanById",
    type: "GET",
    data: "idBahan=" + id,
    dataType: "json",
    success: function(response){
      namaBahan.val(response.nama_bahan);
      jenisBahan[0].selectize.setValue(response.jenis_bahan, true);
      satuan[0].selectize.setValue(response.satuan, true);
      tahunGiling[0].selectize.setValue(response.tahun_giling,true);
      tahunGiling[0].selectize.disable();
      dosisBahan.val(response.dosis_per_ha);
      dosisBahan[0].disabled = true;
      $("#btnSimpanBahan").on("click", function(){simpanEditData(response.id_bahan)});
      edit = true;
    }
  });
}

function simpanEditData(id){
  console.log("Simpan edit = " + id);
  if (namaBahan.val() != "" && jenisBahan.val() != "" && satuan.val() != "" && edit){
    namaBahan.removeClass("is-invalid");
    jenisBahan.removeClass("is-invalid");
    satuan.removeClass("is-invalid");
    dosisBahan.removeClass("is-invalid");
    $.ajax({
      url: js_base_url + "Admin_bahan/editBahan",
      type: "POST",
      dataType: "text",
      data: {
        id_bahan: id,
        nama_bahan: namaBahan.val(),
        jenis_bahan: jenisBahan.val(),
        satuan: satuan.val(),
        dosis: dosisBahan.val(),
        tahun_giling: tahunGiling.val(),
        biaya_angkut: biaya_angkut.val(),
        biaya_muat: biaya_muat.val()
      },
      success: function(data){
        namaBahan.val("");
        jenisBahan[0].selectize.clear();
        satuan[0].selectize.clear();
        tahunGiling[0].selectize.clear();
        dosisBahan.val("");
        biaya_angkut.val("");
        biaya_muat.val("");
        tblBahan.DataTable().ajax.reload();
        edit = false;
        dialogAddBahan.modal("toggle");
      },
      error: function(textStatus){
        console.log(textStatus);
      }
    });
  } else {
    (namaBahan.val() == "") ? namaBahan.addClass("is-invalid") : "";
    (jenisBahan.val() == "") ? jenisBahan.addClass("is-invalid") : "";
    (satuan.val() == "") ? satuan.addClass("is-invalid") : "";
    (dosisBahan.val() == "") ? dosisBahan.addClass("is-invalid") : "";
  }
  edit = false;
}

$("#btnSimpanBahan").on("click", function(){
  console.log("klik");
  if (namaBahan.val() != "" && jenisBahan.val() != "" && satuan.val() != "" && dosisBahan.val() != "" && tahunGiling.val() != "" && !edit){
    namaBahan.removeClass("is-invalid");
    jenisBahan.removeClass("is-invalid");
    satuan.removeClass("is-invalid");
    dosisBahan.removeClass("is-invalid");
    tahunGiling.removeClass("is-invalid");
    $.ajax({
      url: js_base_url + "Admin_bahan/addBahan",
      type: "POST",
      dataType: "text",
      data: formAddBahan.serialize(),
      success: function(data){
        namaBahan.val("");
        jenisBahan[0].selectize.clear();
        satuan[0].selectize.clear();
        tahunGiling[0].selectize.clear();
        dosisBahan.val("");
        biaya_angkut.val("");
        biaya_muat.val("");
        tblBahan.DataTable().ajax.reload();
      },
      error: function(textStatus){
        console.log(textStatus);
      }
    });
  } else {
    (namaBahan.val() == "") ? namaBahan.addClass("is-invalid") : "";
    (jenisBahan.val() == "") ? jenisBahan.addClass("is-invalid") : "";
    (satuan.val() == "") ? satuan.addClass("is-invalid") : "";
    (dosisBahan.val() == "") ? dosisBahan.addClass("is-invalid") : "";
    (tahunGiling.val() == "") ? tahunGiling.addClass("is-invalid") : "";
  }
})

$("#tblBahan").DataTable({
  bFilter: true,
  bPaginate: true,
  bSort: false,
  bInfo: false,
  dom: '<"row"<"spacer"><"cbxTahunGilingList">f>tpl',
  ajax: {
    url: js_base_url + "Admin_bahan/getAllBahan",
    dataSrc: ""
  },
  columns : [
    {data: "no", render: function(data, type, row, meta){return meta.row + meta.settings._iDisplayStart + 1}},
    {data: "nama_bahan"},
    {data: "jenis_bahan"},
    {data: "satuan"},
    {
      data: "dosis_per_ha",
      className: "text-right",
      render: function(data, type, row, meta){
        return parseFloat(row.dosis_per_ha).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits:2});
      }
    },
    {
      data: "biaya_muat",
      className: "text-right",
      render: function(data, type, row, meta){
        if(row.biaya_muat != null){
          return "Rp " + parseInt(row.biaya_muat).toLocaleString({maximumFractionDigits: 0});
        } else {
          return "-";
        }
      }
    },
    {
      data: "biaya_angkut",
      className: "text-right",
      render: function(data, type, row, meta){
        if(row.biaya_angkut != null){
          return "Rp " + parseInt(row.biaya_angkut).toLocaleString({maximumFractionDigits: 0});
        } else {
          return "-";
        }
      }
    },
    {
      data: "tahun_giling",
      className: "text-center"
    },
    {data: "button",
      render: function(data, type, row, meta){
        return '<button type="button" onclick="editData('+row.id_bahan+')" class="btn btn-warning btn-sm" id="btnEditBahan" name="btnEditBahan" title="Ubah Data"><i class="fe fe-edit"></i></button>  ' +
        '<button type="button" onclick="hapusData('+row.id_bahan+')" class="btn btn-danger btn-sm" name="hapus_data" value="'+row.id_bahan+'" title="Hapus Data"><i class="fe fe-trash-2"></i></button>'
      },
      className: "text-center"
    }
  ],
  initComplete: function(){
    $(".dataTables_filter input[type=\"search\"]").css({
      "width": "250px",
      "display": "inline-block",
      "margin": "0px 0px 0px 10px"
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
      $("#tblBahan").DataTable().ajax.url(js_base_url + "Admin_bahan/getBahanByTahunGiling?tahun_giling=" + tahun_giling).load();
    });
  },
  language: {
    "search": ""
  }
});

$("#relasi_aktivitas").selectize({
  valueField: "id_aktivitas",
  labelField: "nama_aktivitas",
  sortField: "nama_aktivitas",
  searchField: "nama_aktivitas",
  maxItems: 1,
  create: false,
  placeholder: "Pilih jenis aktivitas",
  options: []
});

$("#tahun_giling").selectize({
  sortField: "text",
  maxItems: 1,
  create: false
});
