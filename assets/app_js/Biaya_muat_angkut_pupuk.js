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

var nama_kelompok = null;
var no_kontrak = null;
var tahun_giling = null;
var luas = null;

$("#btnBuatPBMA").on("click", function(){
  var objTglAwal = $("#dtpAwal").datepicker("getDate");
  var strTglAwal = formatTglStr(objTglAwal);
  var objTglAkhir = $("#dtpAkhir").datepicker("getDate");
  var strTglAkhir = formatTglStr(objTglAkhir);
  if($("#dtpAwal").datepicker("getDate") != null && $("#dtpAkhir").datepicker("getDate") != null &&
    $("#tblListPupuk").DataTable().data().any()){
      console.log(strTglAwal);
    if (confirm("Buat pengajuan biaya untuk daftar tersebut?")){
      var url_string = $("#tblListPupuk").DataTable().ajax.url();
      var url = new URL(url_string);
      console.log(url_string);
      var tgl_awal = url.searchParams.get("tgl_awal");
      var tgl_akhir = url.searchParams.get("tgl_akhir");
        $.ajax({
          url: js_base_url + "Biaya_muat_angkut_pupuk/buatPbma",
          dataType: "text",
          type: "POST",
          data: "tipe_dokumen=PBMA&tgl_awal=" + tgl_awal + "&tgl_akhir=" + tgl_akhir + "&catatan=" + strTglAwal + " s.d " + strTglAkhir,
          success: function(response){
            alert("Pengajuan berhasil disimpan.");
            $("#tblListPupuk").DataTable().ajax.url(js_base_url + "Biaya_muat_angkut_pupuk/getRekapBiayaMuatAngkutPupuk?tahun_giling=0&tgl_awal=2000-01-01&tgl_akhir=2000-12-31").load();
          }
        });
        console.log("StartDate = " + tgl_awal + "; EndDate = " + tgl_akhir);
    }
  }
})

$("#tblListPupuk").DataTable({
  bFilter: false,
  bPaginate: false,
  bSort: false,
  bInfo: false,
  autoWidth: false,
  dom: '<"row"<"labelTahunGiling"><"cbxTahunGiling"><"dtpTglAwal"><"dtpTglAkhir">f>tpl',
  ajax: {
    url: js_base_url + "Biaya_muat_angkut_pupuk/getRekapBiayaMuatAngkutPupuk?tahun_giling=0&tgl_awal=2000-01-01&tgl_akhir=2000-12-31" ,
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
      data: "urea",
      render: function(data, type, row, meta){
        return parseInt((row.urea == null) ? 0 : row.urea).toLocaleString({maximumFractionDigits: 2}) + " KG";
      },
      className: "text-right"
    },
    {
      data: "tsp",
      render: function(data, type, row, meta){
        return parseInt((row.tsp == null) ? 0 : row.tsp).toLocaleString({maximumFractionDigits: 2}) + " KG";;
      },
      className: "text-right"
    },
    {
      data: "kcl",
      render: function(data, type, row, meta){
        return parseInt((row.kcl == null) ? 0 : row.kcl).toLocaleString({maximumFractionDigits: 2}) + " KG";;
      },
      className: "text-right"
    },
    {
      data: "jml",
      render: function(data, type, row, meta){
        return parseInt((row.jml == null) ? 0 : row.jml).toLocaleString({maximumFractionDigits: 2}) + " KG";;
      },
      className: "text-right"
    },
    {
      data: "biaya_muat",
      render: function(data, type, row, meta){
        return "Rp " + parseInt(row.biaya_muat).toLocaleString({maximumFractionDigits: 2});;
      },
      className: "text-right"
    },
    {
      data: "biaya_angkut",
      render: function(data, type, row, meta){
        return "Rp " + parseInt(row.biaya_angkut).toLocaleString({maximumFractionDigits: 2});;
      },
      className: "text-right"
    },
    {
      data: "total_biaya",
      render: function(data, type, row, meta){
        return "Rp " + parseInt(row.total_biaya).toLocaleString({maximumFractionDigits: 2});;
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
        $("#tblListPupuk").DataTable().ajax.url(js_base_url + "Biaya_muat_angkut_pupuk/getRekapBiayaMuatAngkutPupuk?tahun_giling=" + tahun_giling +
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
    var intKg = function(i){
      return typeof i === 'string' ? i.replace(/[\KG,]/g, '')*1 : typeof i === 'number' ? i : 0;
    }
    totalBiaya = api.column(12).data().reduce( function (a, b) {
        return intRupiah(a) + intRupiah(b);
    },0);
    totalPupuk = api.column(9).data().reduce(function (a,b){
      return intKg(a) + intKg(b);
    }, 0);
    $(api.column(9).footer()).html('<font color="white" size="3">' + totalPupuk.toLocaleString({maximumFractionDigits: 2}) + ' KG' + '</font>');
    $(api.column(12).footer()).html( '<font color="white" size="3">' + 'Rp '+ totalBiaya.toLocaleString({maximumFractionDigits: 0}) + '</font>');
  }
});
