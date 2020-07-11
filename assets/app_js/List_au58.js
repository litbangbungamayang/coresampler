function approve(id_dokumen){
  $.ajax({
    url: js_base_url + "List_au58/validasiDokumen",
    dataType: "text",
    type: "POST",
    data: "id_dokumen=" + id_dokumen,
    success: function(response){
      if (response = "SUCCESS"){
        tahun_giling = parseInt($("#tahun_giling").val()) || 0;
        $("#tblListAu58").DataTable().ajax.url(js_base_url + "List_au58/getAllAu58?tahun_giling=" + tahun_giling).load();
        alert("Dokumen berhasil divalidasi!");
      }
    }
  });
}

$("#tblListAu58").DataTable({
  bFilter: true,
  bPaginate: true,
  bSort: false,
  bInfo: false,
  autoWidth: false,
  ajax: {
    url: js_base_url + "List_au58/getAllAu58?tahun_giling=0" ,
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
    { data: "nama_kelompok" },
    { data: "no_kontrak" },
    {
      data: "jml_kuanta",
      render: function(data, type, row, meta){
        return parseInt(row.jml_kuanta).toLocaleString({maximumFractionDigits: 0}) + " KG";
      },
      className: "text-right"
    },
    {
      data: "",
      render: function(data, type, row, meta){
        if(row.tgl_validasi_bagian == null){
          return "<span class='tag tag-red'>Belum Divalidasi</span>";
        }
        return "<span class='tag tag-green'>Sudah Divalidasi</span>";
      },
      className: "text-center"
    },
    {
      render: function(data, type, row, meta){
        var buttonDetail = '<a class="btn btn-sm btn-cyan" href="Transaksi_AU58?no_transaksi=' + row.no_transaksi + '&id_kelompok=' + row.id_kelompok + '"title="Lihat Detail"><i class="fe fe-book-open"></i></a> ';
        var buttonApproval = '<button class="btn btn-sm btn-primary" onclick = approve(' + row.id_dokumen +') title="Validasi" ><i class="fe fe-check-circle"></i></button> '
        if(row.priv_level == "Asisten Bagian"){
          if(row.tgl_validasi_bagian == null){
            return buttonDetail + buttonApproval;
          } else {
            return buttonDetail;
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
    $("#tahun_giling").on("change", function(){
      tahun_giling = parseInt($("#tahun_giling").val()) || 0;
      $("#tblListAu58").DataTable().ajax.url(js_base_url + "List_au58/getAllAu58?tahun_giling=" + tahun_giling).load();
    })
  },
  footerCallback: function (row, data, start, end, display){
    var api = this.api(), data;
    var intKuanta = function ( i ) {
      return typeof i === 'string' ? i.replace(/[\KG,]/g, '')*1 : typeof i === 'number' ? i : 0;
    };
    totalKuanta = api.column(5).data().reduce( function (a, b) {
        return intKuanta(a) + intKuanta(b);
    },0);
    $(api.column(5).footer()).html('<font color="white" size="3">' + totalKuanta.toLocaleString({maximumFractionDigits: 0}) + ' KG </font>');
  },
  language: {
    "search": ""
  }
});
