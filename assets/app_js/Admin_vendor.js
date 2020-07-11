var dialogAddVendor = $("#dialogAddVendor");
var txtNamaVendor = $("#nama_vendor");
var txtNpwp = $("#npwp_vendor");
var txtAlamatVendor1 = $("#alamat_vendor");
var txtAlamatVendor2 = $("#alamat_2_vendor");
var txtNamaKontak = $("#nama_kontak");
var txtTelpKontak = $("#telp_kontak");
var btnSimpanVendor = $("#btnSimpanVendor");
var tblVendor = $("#tblVendor");
var formAddVendor = $("#formAddVendor");
var edit = false;
var edit_id = null;

function resetForm(){
  txtNamaVendor.val("");
  txtNpwp.val("");
  txtAlamatVendor1.val("");
  txtAlamatVendor2.val("");
  txtNamaKontak.val("");
  txtTelpKontak.val("");
}

function resetFeedback(){
  txtNamaVendor.removeClass("is-invalid");
  txtNpwp.removeClass("is-invalid");
  txtAlamatVendor1.removeClass("is-invalid");
  txtNamaKontak.removeClass("is-invalid");
  txtTelpKontak.removeClass("is-invalid");
}

dialogAddVendor.on("hide.bs.modal", function(){
  resetForm();
  resetFeedback();
  edit = false;
})
dialogAddVendor.on("show.bs.modal", function(){
  btnSimpanVendor.show();
})

btnSimpanVendor.on("click", function(){
  if (txtNamaVendor.val() != "" && txtNpwp.val() != "" && txtAlamatVendor1.val() != ""
    && txtNamaKontak.val() != "" && txtTelpKontak.val != ""){
      resetFeedback();
      var controller = "";
      (edit) ? controller = "Admin_vendor/editVendor" : controller = "Admin_vendor/addVendor";
      var formData = null;
      (edit) ? formData = formAddVendor.serialize()  + "&id_vendor=" + edit_id : formData = formAddVendor.serialize();
      //console.log("Status edit = " + edit + ";" + formData + ";" + controller);
      $.ajax({
        url: js_base_url + controller,
        data:formData,
        type: "POST",
        dataType: "text",
        success: function(data){
          //console.log("Response = " + data);
          tblVendor.DataTable().ajax.reload();
          edit = false;
        }
      });
      dialogAddVendor.modal("toggle");
      resetForm();
  } else {
    (txtNamaVendor.val() == "") ? txtNamaVendor.addClass("is-invalid") : "";
    (txtNpwp.val() == "") ? txtNpwp.addClass("is-invalid") : "";
    (txtAlamatVendor1.val() == "") ? txtAlamatVendor1.addClass("is-invalid") : "";
    (txtNamaKontak.val() == "") ? txtNamaKontak.addClass("is-invalid") : "";
    (txtTelpKontak.val() == "") ? txtTelpKontak.addClass("is-invalid") : "";
  }
})

txtNamaVendor.bind("keyup blur", function(){
  $(this).val($(this).val().replace(/[^a-zA-Z0-9 ]/g,""));
});

txtNamaKontak.bind("keyup blur", function(){
  $(this).val($(this).val().replace(/[^a-zA-Z0-9 ]/g,""));
});

txtNpwp.bind("keyup blur", function(){
  $(this).val($(this).val().replace(/[^0-9 ]/g,""));
});

function editData(id_vendor){
  edit = true;
  lihatData(id_vendor);
}

function hapusData(id_vendor){
  $.ajax({
    url: js_base_url + "Admin_vendor/getVendorById",
    type: "GET",
    data: "id_vendor=" + id_vendor,
    dataType: "json",
    success: function(response){
      if (confirm("Anda yakin akan menghapus data vendor " + response.nama_vendor + "?")){
        $.ajax({
          url: js_base_url + "Admin_vendor/hapusVendor",
          type: "POST",
          dataType: "text",
          data: {id_vendor: id_vendor},
          success: function(result){
            console.log(result);
            alert(result);
            tblVendor.DataTable().ajax.reload();
          }
        });
      }
    }
  });
}

function lihatData(id_vendor){
  dialogAddVendor.modal("toggle");
  $.ajax({
    url: js_base_url + "Admin_vendor/getVendorById",
    type: "GET",
    dataType: "json",
    data: "id_vendor=" + id_vendor,
    success: function(response){
      btnSimpanVendor.hide();
      txtNamaVendor.val(response.nama_vendor);
      txtNpwp.val(response.npwp_vendor);
      txtAlamatVendor1.val(response.alamat_vendor);
      txtAlamatVendor2.val(response.alamat_2_vendor);
      txtNamaKontak.val(response.nama_kontak);
      txtTelpKontak.val(response.telp_kontak);
      txtNamaVendor.prop("disabled", !edit);
      txtNpwp.prop("disabled", !edit);
      txtAlamatVendor1.prop("disabled", !edit);
      txtAlamatVendor2.prop("disabled", !edit);
      txtNamaKontak.prop("disabled", !edit);
      txtTelpKontak.prop("disabled", !edit);
      (edit) ? btnSimpanVendor.show() : btnSimpanVendor.hide();
      (edit) ? edit_id = id_vendor : null;
    }
  })
}

tblVendor.DataTable({
  bFilter: true,
  bPaginate: false,
  bSort: false,
  bInfo: false,
  ajax: {
    url: js_base_url + "Admin_vendor/getAllVendor",
    dataSrc: ""
  },
  columns : [
    //{data: "no", render: function(data, type, row, meta){return meta.row + meta.settings._iDisplayStart + 1}},
    {
      class: "",
      orderable: false,
      data: null,
      defaultContent: "",
      render: function(data, type, row, meta){return meta.row + meta.settings._iDisplayStart + 1}
    },
    {data: "nama_vendor"},
    {data: "nama_kontak"},
    {data: "telp_kontak"},
    {data: "button",
      render: function(data, type, row, meta){
        return '<button type="button" onclick="editData('+row.id_vendor+')" class="btn btn-warning btn-sm" id="btnEditVendor" name="edit_data" title="Ubah Data"><i class="fe fe-edit"></i></button>  ' +
        '<button type="button" onclick="hapusData('+row.id_vendor+')" class="btn btn-danger btn-sm" name="hapus_data" title="Hapus Data"><i class="fe fe-trash-2"></i></button> ' +
        '<button type="button" onclick="lihatData('+row.id_vendor+')" class="btn btn-primary btn-sm" name="lihat_data" title="Lihat Data"><i class="fe fe-external-link"></i></button>'
      },
      className: "text-center"
    }
  ],
  initComplete: function(){
    $(".dataTables_filter input[type=\"search\"]").css({
      "width": "250px",
      "display": "inline-block",
      "margin": "10px"
    }).attr("placeholder", "Cari");
    $(".dataTables_filter").css({
      "margin": "0px"
    });
  },
  language: {
    "search": ""
  }
})
