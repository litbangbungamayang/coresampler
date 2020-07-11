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

$("#btnBuatPbp").on("click", function(){
  var objTglAwal = $("#dtpAwal").datepicker("getDate");
  var strTglAwal = formatTglStr(objTglAwal);
  var objTglAkhir = $("#dtpAkhir").datepicker("getDate");
  var strTglAkhir = formatTglStr(objTglAkhir);
  if($("#dtpAwal").datepicker("getDate") != null && $("#dtpAkhir").datepicker("getDate") != null &&
    $("#tblListPerawatan").DataTable().data().any()){
      console.log(strTglAwal);
    if (confirm("Buat pengajuan biaya perawatan untuk daftar tersebut?")){
      var url_string = $("#tblListPerawatan").DataTable().ajax.url();
      var url = new URL(url_string);
      var tgl_awal = url.searchParams.get("tgl_awal");
      var tgl_akhir = url.searchParams.get("tgl_akhir");
        $.ajax({
          url: js_base_url + "Biaya_perawatan/buatPbp",
          dataType: "text",
          type: "POST",
          data: "tipe_dokumen=PBP&tgl_awal=" + tgl_awal + "&tgl_akhir=" + tgl_akhir + "&catatan=" + strTglAwal + " s.d " + strTglAkhir,
          success: function(response){
            alert("Pengajuan biaya perawatan berhasil disimpan.");
            $("#tblListPerawatan").DataTable().ajax.reload();
          }
        });
        console.log("StartDate = " + tgl_awal + "; EndDate = " + tgl_akhir);
    }
  }
})

$("#tblListPerawatan").DataTable({
  bFilter: false,
  bPaginate: false,
  bSort: false,
  bInfo: false,
  autoWidth: false,
  dom: '<"row"<"labelTahunGiling"><"cbxTahunGiling"><"dtpTglAwal"><"dtpTglAkhir">f>tpl',
  ajax: {
    url: js_base_url + "Biaya_perawatan/getRekapBiayaPerawatan?tahun_giling=0&tgl_awal=2000-01-01&tgl_akhir=2000-12-31" ,
    dataSrc: ""
  },
  columns : [
    {
      data: "no",
      render: function(data, type, row, meta){
        return meta.row + 1;
      }
    },
    {
      width: "10%",
      data: "nama_kelompok",
      render: function(data, type, row, meta){
        return row.nama_kelompok;
      },
    },
    {
      data: "no_kontrak",
      render: function(data, type, row, meta){
        return row.no_kontrak;
      }
    },
    {
      data: "nama_wilayah",
      render: function(data, type, row, meta){
        return row.nama_wilayah;
      },
      className: "text-left"
    },
    {
      data: "tgl_transaksi",
      render: function(data, type, row, meta){
        return row.tgl_transaksi;
      },
      className: "text-center"
    },
    {data: "luas",
      render: function(data, type, row, meta){
        return parseFloat(row.luas).toLocaleString({minimumFractionDigits: 2, maximumFractionDigits: 2}) + " Ha";
      },
      className: "text-right"
    },
    {
      data: "jml_perawatan",
      render: function(data, type, row, meta){
        return "Rp " + parseInt(row.jml_perawatan).toLocaleString({maximumFractionDigits: 2});
      },
      className: "text-right"
    }
  ],
  initComplete: function(){
    var currYear = parseInt(new Date().getFullYear());
    var i;
    var optionTahun = '<option value="0">Pilih tahun giling</option>';
    for (i=0; i < 4; i++){
      optionTahun += '<option value="' + parseInt(currYear + i) + '">' + parseInt(currYear + i) + '</option>';
    }
    $("div.cbxTahunGiling").html('<select style="width: 150px;" name="tahun_giling" id="tahun_giling" class="custom-control custom-select" placeholder="Pilih tahun giling">' + optionTahun + '</select>');
    $("div.labelTahunGiling").html('<label class="form-label" style="margin: 0px 10px 0px 0px;"></label>');
    $('#tahun_giling').selectize({create: false, sortField: 'value'});
    function refreshTable(){
      var tgl_awal = $("#dtpAwal").datepicker("getDate");
      var tgl_akhir = $("#dtpAkhir").datepicker("getDate");
      if(tgl_awal != null && tgl_akhir != null){
        tgl_awal = formatTgl(tgl_awal);
        tgl_akhir = formatTgl(tgl_akhir);
        tahun_giling = parseInt($("#tahun_giling").val()) || 0;
        $("#tblListPerawatan").DataTable().ajax.url(js_base_url + "Biaya_perawatan/getRekapBiayaPerawatan?tahun_giling=" + tahun_giling +
        "&tgl_awal=" + tgl_awal + "&tgl_akhir=" + tgl_akhir).load();
      }
    }
    $("#tahun_giling").on("change", function(){
      refreshTable();
    })
    $("div.dtpTglAwal").html("<input autocomplete='off' type='text' class='form-control text-center' placeholder='Tanggal Awal' id='dtpAwal' style='width: 120px; margin-left: 10px;'>");
    $("#dtpAwal").datepicker({
      format: "dd-mm-yyyy"
    });
    $("div.dtpTglAkhir").html("<input autocomplete='off' type='text' class='form-control text-center' placeholder='Tanggal Akhir' id='dtpAkhir' style='width: 120px; margin-left: 10px; margin-bottom: 10px'>");
    $("#dtpAkhir").datepicker({
      format: "dd-mm-yyyy"
    });
    $("div.btnSearch").html("<button style='margin-left: 10px; width: 100px;' id='btnSearch' type='button' class='btn btn-outline-secondary'>Tampilkan</button>");
    $("#btnSearch").on("click", function(){
      refreshTable();
    });
    $("#dtpAwal").on("change", function(){
      refreshTable();
    });
    $("#dtpAkhir").on("change", function(){
      refreshTable();
    })
  },
  language: {
    "search": ""
  },
  footerCallback: function (row, data, start, end, display){
    var api = this.api(), data;
    var intRupiah = function ( i ) {
      return typeof i === 'string' ? i.replace(/[\Rp,]/g, '')*1 : typeof i === 'number' ? i : 0;
    };
    totalBiaya = api.column(6).data().reduce( function (a, b) {
        return intRupiah(a) + intRupiah(b);
    },0);
    $(api.column(6).footer()).html( '<font color="white" size="3">' + 'Rp '+ totalBiaya.toLocaleString({maximumFractionDigits: 0}) + '</font>');
  }
});
