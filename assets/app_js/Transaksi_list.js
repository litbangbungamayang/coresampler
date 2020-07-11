$("#tblTransPupuk").DataTable({
  bFilter: false,
  bPaginate: true,
  bSort: false,
  bInfo: false,
  dom: 'tp',
  ajax: {
    url: js_base_url + "Transaksi_bahan/getTransaksiKeluarByIdKelompok?id_kelompok="+id_kelompok,
    dataSrc: ""
  },
  columns : [
    {
      data: "no",
      render: function(data, type, row, meta){
        return meta.row + meta.settings._iDisplayStart + 1;
      }
    },
    {data: "no_transaksi"},
    {data: "tgl_transaksi"},
    {data: "nama_bahan"},
    {
      data: "luas_aplikasi",
      render: function(data, type, row, meta){
        return parseFloat(data).toLocaleString(undefined, {maximumFractionDigits:2}) + " HA";
      },
      className: "text-right"
    },
    {
      data: "kuanta",
      render: function(data, type, row, meta){
        return parseInt(data).toLocaleString(undefined, {maximumFractionDigits:2}) + " " +row.satuan;
      },
      className: "text-right"
    },
    {
      data: "rupiah",
      render: function(data, type, row, meta){
        return "Rp " + parseInt(data).toLocaleString(undefined, {maximumFractionDigits:2});
      },
      className: "text-right"
    },
    {
      data: "biaya_muat",
      render: function(data, type, row, meta){
        return "Rp " + parseInt(row.kuanta*row.biaya_muat).toLocaleString(undefined, {maximumFractionDigits:2});
      },
      className: "text-right"
    },
    {
      data: "biaya_angkut",
      render: function(data, type, row, meta){
        return "Rp " + parseInt(row.kuanta*row.biaya_angkut).toLocaleString(undefined, {maximumFractionDigits:2});
      },
      className: "text-right"
    },
    {
      data: "button",
      render: function(data, type, row, meta){
        return '<a class="btn btn-outline-primary btn-sm" id="cetakAu58" href="Transaksi_AU58?no_transaksi='+row.no_transaksi+'&id_kelompok='+row.id_kelompoktani+'"><i class="fe fe-printer"></i></a>'
      },
      className: "text-center"
    }
  ]
});

$("#tblTransPerawatan").DataTable({
  bFilter: false,
  bPaginate: true,
  bSort: false,
  bInfo: false,
  dom: 'tp',
  ajax: {
    url: js_base_url + "Transaksi_aktivitas/getTransaksiAktivitasByIdKelompok?id_kelompok="+id_kelompok+"&jenis_aktivitas="+"PERAWATAN",
    dataSrc: ""
  },
  columns : [
    {
      data: "no",
      render: function(data, type, row, meta){
        return meta.row + meta.settings._iDisplayStart + 1;
      }
    },
    {data: "no_transaksi"},
    {data: "tgl_transaksi"},
    {data: "nama_aktivitas"},
    {
      data: "kuanta",
      render: function(data, type, row, meta){
        return parseFloat(data).toLocaleString({minimumFractionDigits: 2, maximumFractionDigits:2}) + " Ha";
      },
      className: "text-right"
    },
    {
      data: "rupiah",
      render: function(data, type, row, meta){
        return "Rp " + parseInt(data).toLocaleString({minimumFractionDigits: 2, maximumFractionDigits:2});
      },
      className: "text-right"
    },
    {
      data: "button",
      render: function(data, type, row, meta){
        return '<a class="btn btn-outline-primary btn-sm" name="hapus" id="" href="Transaksi_aktivitas?no_transaksi='+row.no_transaksi+'&id_kelompok='+row.id_kelompoktani+'"><i class="fe fe-printer"></i></a>'
      },
      className: "text-center"
    }
  ],
  footerCallback: function (row, data, start, end, display){
    var api = this.api(), data;
    var intRupiah = function ( i ) {
      return typeof i === 'string' ? i.replace(/[\Rp,]/g, '')*1 : typeof i === 'number' ? i : 0;
    };
    totalRupiah = api.column(5).data().reduce( function (a, b) {
        return intRupiah(a) + intRupiah(b);
    },0);
    $(api.column(5).footer()).html('<font size="3" color="white">' + "Rp " + totalRupiah.toLocaleString({maximumFractionDigits: 0}) + ' </font>');
  },
  initComplete: function(){

  }
});

$("#tblTransBibit").DataTable({
  bFilter: false,
  bPaginate: true,
  bSort: false,
  bInfo: false,
  dom: 'tp',
  ajax: {
    url: js_base_url + "Transaksi_aktivitas/getTransaksiAktivitasByIdKelompok?id_kelompok="+id_kelompok+"&jenis_aktivitas=" + "BIBIT",
    dataSrc: ""
  },
  columns : [
    {
      data: "no",
      render: function(data, type, row, meta){
        return meta.row + meta.settings._iDisplayStart + 1;
      }
    },
    {data: "no_transaksi"},
    {data: "tgl_transaksi"},
    {data: "nama_aktivitas"},
    {
      data: "kuanta",
      render: function(data, type, row, meta){
        return parseFloat(data).toLocaleString({minimumFractionDigits: 2, maximumFractionDigits:2}) + " Ha";
      },
      className: "text-right"
    },
    {
      data: "rupiah",
      render: function(data, type, row, meta){
        return "Rp " + parseInt(data).toLocaleString({minimumFractionDigits: 2, maximumFractionDigits:2});
      },
      className: "text-right"
    },
    {
      data: "button",
      render: function(data, type, row, meta){
        return '<a class="btn btn-outline-primary btn-sm" name="hapus" id="" href="Transaksi_aktivitas?no_transaksi='+row.no_transaksi+'&id_kelompok='+row.id_kelompoktani+'"><i class="fe fe-printer"></i></a>'
      },
      className: "text-center"
    }
  ],
  initComplete: function(){

  }
});
