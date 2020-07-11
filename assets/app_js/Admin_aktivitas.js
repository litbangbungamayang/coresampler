$('#tahun_giling').selectize({create: false, sortField: 'text'});
$('#kategori').selectize({create: false, sortField: 'text'});

var txtNamaAktivitas = $("#nama_aktivitas");
var cbxTahunGiling = $("#tahun_giling");
var cbxKategori = $("#kategori");
var txtBiaya = $("#biaya");
var btnSimpanAktivitas = $("#btnSimpanAktivitas");
var tblAktivitas = $("#tblAktivitas");
var dialogAddAktivitas = $("#dialogAddAktivitas");
var edit = false;
var edit_id = null;
cbxTahunGiling.selectize()[0].selectize.setValue("");

dialogAddAktivitas.on("hide.bs.modal", function(){
  resetForm();
  resetFeedback();
})

txtBiaya.bind("keyup blur", function(){
  $(this).val($(this).val().replace(/[^0-9]/g,""));
  ($(this).val() != "") ? $(this).val("Rp " + parseInt($(this).val()).toLocaleString()) : "";
});

function validasiForm(){
  var biayaValue = txtBiaya.val().replace(/[^0-9]/g,"");
  if (cbxTahunGiling.selectize()[0].selectize.getValue() != "" && txtNamaAktivitas.val() != "" && biayaValue != ""){
    var formValue = {nama_aktivitas: txtNamaAktivitas.val(), tahun_giling: cbxTahunGiling.selectize()[0].selectize.getValue(), biaya: biayaValue, kategori: cbxKategori.selectize()[0].selectize.getValue()};
    return formValue;
  } else {
    (txtNamaAktivitas.val() == "") ? txtNamaAktivitas.addClass("is-invalid") : "";
    (cbxTahunGiling.val() == "") ? cbxTahunGiling.addClass("is-invalid") : "";
    (txtBiaya.val() == "") ? txtBiaya.addClass("is-invalid") : "";
  }
}

btnSimpanAktivitas.on("click", function(){
  if (validasiForm() != null){
    var formValue = validasiForm();
    formValue.tstr = "TR";
    var controller = "";
    if (edit) formValue.id_aktivitas = edit_id;
    (edit) ? controller = "Admin_aktivitas/updateAktivitas" : controller = "Admin_aktivitas/addAktivitas";
    console.log("Edit = " + edit);
    console.log("Controller = " + controller);
    console.log("ID = " + edit_id);
    console.log("Form Value = " + formValue);
    $.ajax({
      url: js_base_url + controller,
      dataType: "text",
      type: "POST",
      data: formValue,
      success: function(data){
        if (data != ""){
          alert("Data telah tersimpan!");
          dialogAddAktivitas.modal("toggle");
          edit_id = null;
          edit = false;
          tblAktivitas.DataTable().ajax.reload();
        }
      }
    });
  }
})

function resetForm(){
  txtNamaAktivitas.val("");
  txtBiaya.val("");
  cbxTahunGiling.selectize()[0].selectize.setValue("");
}

function resetFeedback(){
  txtNamaAktivitas.removeClass("is-invalid");
  txtBiaya.removeClass("is-invalid");
  cbxTahunGiling.removeClass("is-invalid");
}

tblAktivitas.DataTable({
  bFilter: true,
  bPaginate: true,
  bSort: false,
  bInfo: false,
  dom: '<"row"<"spacer"><"cbxTahunGilingList">f>tpl',
  ajax: {
    url: js_base_url + "Admin_aktivitas/getAllAktivitas",
    dataSrc: ""
  },
  columns : [
    {data: "no", render: function(data, type, row, meta){return meta.row + meta.settings._iDisplayStart + 1}},
    {data: "nama_aktivitas"},
    {data: "tahun_giling"},
    {
      data: "biaya",
      render: function(data, type, row, meta){
        return "Rp " + parseInt(data).toLocaleString(undefined, {maximumFractionDigits:2})
      },
      className: "text-right"
    },
    {
      render: function(data, type, row, meta){
        return ""
      },
      width: "100px"
    },
    {
      data: "",
      render: function(data, type, row, meta){
        return actionButtonView(row.id_aktivitas);
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
      $("#tblAktivitas").DataTable().ajax.url(js_base_url + "Admin_aktivitas/getAktivitasByTahunGiling?tahun_giling=" + tahun_giling).load();
    });
  },
  language: {
    "search": ""
  }
})

function editData(id_aktivitas){
  dialogAddAktivitas.modal("toggle");
  edit = true;
  $.ajax({
    url: js_base_url + "Admin_aktivitas/getAktivitasById",
    dataType: "json",
    type: "GET",
    data: {id_aktivitas: id_aktivitas},
    success: function(response){
      txtNamaAktivitas.val(response.nama_aktivitas);
      cbxTahunGiling.selectize()[0].selectize.setValue(response.tahun_giling);
      txtBiaya.val("Rp " + parseInt(response.biaya).toLocaleString());
      edit_id = id_aktivitas;
    }
  });
}

function hapusData(id_aktivitas){
  $.ajax({
    url: js_base_url + "Admin_aktivitas/getAktivitasById",
    type: "GET",
    data: "id_aktivitas=" + id_aktivitas,
    dataType: "json",
    success: function(response){
      console.log("RESP = " + response);
      if (confirm("Anda yakin akan menghapus data aktivitas " + response.nama_aktivitas + "?")){
        $.ajax({
          url: js_base_url + "Admin_aktivitas/hapusAktivitas",
          type: "POST",
          dataType: "text",
          data: {id_aktivitas: id_aktivitas},
          success: function(result){
            alert(result);
            tblAktivitas.DataTable().ajax.reload();
          }
        });
      }
    }
  });
}

function actionButtonView(id_aktivitas){
  return  '<button type="button" onclick="editData('+id_aktivitas+')" class="btn btn-warning btn-sm" id="btnEditAktivitas" name="btnEditAktivitas" title="Ubah Data"><i class="fe fe-edit"></i></button>  ' +
  '<button type="button" onclick="hapusData('+id_aktivitas+')" class="btn btn-danger btn-sm" name="hapus_data" value="'+id_aktivitas+'" title="Hapus Data"><i class="fe fe-trash-2"></i></button>'
}
