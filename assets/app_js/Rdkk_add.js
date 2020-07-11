$('#masaTanam').selectize({create: false, sortField: 'text'});
$('#varietas').selectize({create: false, sortField: 'text'});
$('#kategori').selectize({create: false, sortField: 'text'});
$('#tahun_giling').selectize({create: false, sortField: 'text'});

$("#errMsg").hide();
$("#iconLoading").hide();
var MAX_IMAGE_SIZE = 200;

$("#namaKelompok").bind("keyup blur", function(){
  $(this).val($(this).val().replace(/[^a-zA-Z ]/g,""));
});
$("#namaPetani").bind("keyup blur", function(){
  $(this).val($(this).val().replace(/[^a-zA-Z ]/g,""));
});
$("#noKtp").bind("keyup blur", function(){
  $(this).val($(this).val().replace(/[^0-9 ]/g,""));
});

$.ajax({
  url: js_base_url + "Rdkk_add/getAllKabupaten",
  type: "GET",
  dataType: "json",
  success: function(response){
    $namaDesa = $("#namaDesa").selectize();
    namaDesa = $namaDesa[0].selectize;
    $("#namaKab").selectize({
      valueField: "id_wilayah",
      labelField: "nama_wilayah",
      sortField: "nama_wilayah",
      searchField: "nama_wilayah",
      maxItems: 1,
      create: false,
      placeholder: "Pilih kabupaten",
      options: response,
      onChange: function(value){
        $("#iconLoading").show();
        console.log(value);
        namaDesa.disable();
        namaDesa.clear();
        namaDesa.clearOptions();
        $.ajax({
          url: js_base_url + "Rdkk_add/getDesaByKabupaten",
          type: "GET",
          dataType: "json",
          data: "idKab=" + value,
          success: function(response){
            //console.log(response);
            namaDesa.options = response;
            namaDesa.enable();
            namaDesa.clear();
            namaDesa.clearOptions();
            namaDesa.load(function (callback){
              callback(response);
              $("#iconLoading").hide();
            });
          }
        })
      }
    });
  }
});

$("#namaDesa").selectize({
  valueField: "id_wilayah",
  labelField: "nama_wilayah",
  sortField: "nama_wilayah",
  searchField: "nama_wilayah",
  maxItems: 1,
  create: false,
  placeholder: "Pilih desa",
  options: [],
  render: {
    option: function(item, escape){
      var namaKec = function (){
        var namaKec = "";
        $.ajax({
          async: true,
          url: js_base_url + "Rdkk_add/getKecByDesa",
          data: "idDesa=" + escape(item.id_wilayah),
          dataType: "json",
          type: "GET",
          success: function(response){
            namaKec = response[0].nama_wilayah;
          }
        });
        return namaKec;
      }();
      return "<option value = escape(item.id_wilayah)>" + "DESA " + escape(item.nama_wilayah) + namaKec + "</option>";
    },
    item: function(item, escape){
      var namaKec = function (){
        var namaKec = "";
        $.ajax({
          async: false,
          url: js_base_url + "Rdkk_add/getKecByDesa",
          data: "idDesa=" + escape(item.id_wilayah),
          dataType: "json",
          type: "GET",
          success: function(response){
            namaKec = response[0].nama_wilayah;
          }
        });
        return namaKec;
      }();
      return "<option value = escape(item.id_wilayah)>" + "DESA " + escape(item.nama_wilayah) + namaKec + "</option>";
    }
  },
  onChange: function(value){
    console.log(value);
  }
});

$("#namaDesa")[0].selectize.disable();

function readOpenLayers(gpxFile){
  var reader = new FileReader();
  reader.readAsText(gpxFile, "UTF-8");
  reader.onload = function (evt){
    var gpxFormat = new ol.format.GPX();
    var gpxFeatures = gpxFormat.readFeature(evt.target.result, {
      dataProjection: "EPSG:4326",
      featureProjection: "EPSG:4326"
    });
    var sourceProjection = gpxFormat.readProjection(evt.target.result);
    //console.log("Source proj. = " + sourceProjection.getCode());
    var geom = gpxFeatures.getGeometry();
    var poly = new ol.geom.Polygon(geom.getCoordinates());
    //console.log("Geom type = " + geom.getType());
    //console.log("Length = " + ol.sphere.getLength(geom));
    //console.log("Area = " + ol.sphere.getArea(geom));
    //console.log("Coordinates = " + geom.getCoordinates());
    //console.log("Poly Area = " + poly.getArea(poly)*1000000 + " Ha.");
    //console.log("Sphere Area = " + ol.sphere.getArea(poly, {projection: "EPSG:4326"})/10000 + " Ha.");
    //console.log("Poly Length = " + ol.sphere.getLength(poly, {projection: "EPSG:4326"}) + " m.");
    var luasLahan =  ol.sphere.getArea(poly, {
      projection: "EPSG:4326"
    });
    var indexScanPetani = arrayScanPetani.length - 1;
    var petani = objPetani(
      null,
      null,
      $("#namaPetani").val().toUpperCase(),
      luasLahan/10000,
      geom.getCoordinates(),
      arrayScanPetani[indexScanPetani].scanKtpPetani,
      arrayScanPetani[indexScanPetani].scanKkPetani
    );
    $("#lblFileGpxKebun").text("Pilih file");
    $("#lblScanKtpPetani").text("Pilih file");
    $("#lblScanKkPetani").text("Pilih file");
    $("#fileGpxKebun").val("");
    $("#fileScanKtpPetani").val("");
    $("#fileScanKkPetani").val("");
    arrayPetani.push(petani);
    refreshData();
    //console.log(petani);
    //console.log(arrayScanPetani);
    formAddPetani.reset();
  }
}

$("#dialogAddPetani").on("hide.bs.modal", function (e){
  $("#lblFileGpxKebun").text("Pilih file");
  $("#fileGpxKebun").val("");
  $("#fileGpxKebun").removeClass("is-invalid");
  $("#namaPetani").removeClass("is-invalid");
  $("#fbFileGpx").hide();
  $("#fbNamaPetani").hide();
})

var arrayPetani = [];
var arrayScanPetani = [];
var formAddPetani = $("#formAddPetani")[0];
var scanKtpPetaniContent = "";
var scanKkPetaniContent = "";
var objPetani = function(id_petani, id_kelompok, nama_petani, luas, arrayGPS, scanKtp, scanKk){
  var obj = {};
  obj.id_petani = id_petani;
  obj.id_kelompok = id_kelompok;
  obj.nama_petani = nama_petani;
  obj.luas = luas;
  obj.gps = arrayGPS;
  obj.scanKtp = scanKtp;
  obj.scanKk = scanKk;
  return obj;
}
var objScanPetani = function(scanKtpPetani, scanKkPetani){
  var obj = {};
  obj.scanKtpPetani = scanKtpPetani;
  obj.scanKkPetani = scanKkPetani;
  return obj;
}

function refreshData(){
  tabelPetani = $("#tblPetani").DataTable();
  tabelPetani.clear();
  tabelPetani.rows.add(arrayPetani);
  tabelPetani.draw();
  return false;
}

$("#fileGpxKebun").change(function(e){
  var selectedFile = $(this)[0].files[0];
  var lblGpxKebun = $("#lblFileGpxKebun");
  var fbFileGpx = $("#fbFileGpx");
  lblGpxKebun.text(selectedFile.name);
  if (selectedFile.type != "application/gpx+xml"){
    fbFileGpx.show();
    fbFileGpx.html("Format GPX tidak sesuai!");
    $(this).addClass("is-invalid");
    lblGpxKebun.text("Pilih file");
    $("#fileGpxKebun").val("");
  } else {
    fbFileGpx.hide();
    $(this).removeClass("is-invalid");
  }
})

function validasiFile($fileInput, $lblFileInput, $maxFileSize, $fileType, $feedBackLabel){
  var inputFile = $fileInput;
  var selectedFile = $fileInput[0].files[0];
  var labelInput = $lblFileInput;
  var maxSize = $maxFileSize; //in byte
  var allowedType = $fileType;
  var feedbackLabel = $feedBackLabel;
  if (inputFile.val() != ""){
    if (selectedFile.type == $fileType && selectedFile.size <= (MAX_IMAGE_SIZE*1024)){
      feedbackLabel.hide();
      inputFile.removeClass("is-invalid");
      labelInput.text(selectedFile.name);
    } else {
      if (selectedFile.type != $fileType){
        feedbackLabel.show();
        feedbackLabel.html("Format image tidak sesuai!");
        inputFile.addClass("is-invalid");
        inputFile.val("");
        labelInput.text("Pilih file");
      } else {
        if (selectedFile.size > maxSize){
          feedbackLabel.show();
          feedbackLabel.html("Ukuran file melebihi batas maksimal! (Maks. 200kB)");
          inputFile.addClass("is-invalid");
          inputFile.val("");
          labelInput.text("Pilih file");
        }
      }
    }
  }
};

$("#scanKtp").change(function (e){
  validasiFile($(this), $("#lblScanKtp"), (MAX_IMAGE_SIZE*1024), "image/jpeg", $("#fbScanKtp"));
});

$("#scanKtpPetani").change(function (e){
  validasiFile($(this), $("#lblScanKtpPetani"), (MAX_IMAGE_SIZE*1024), "image/jpeg", $("#fbScanKtpPetani"));
});

$("#scanKk").change(function (e){
  validasiFile($(this), $("#lblScanKk"), (MAX_IMAGE_SIZE*1024), "image/jpeg", $("#fbScanKk"));
});

$("#scanKkPetani").change(function (e){
  validasiFile($(this), $("#lblScanKkPetani"), (MAX_IMAGE_SIZE*1024), "image/jpeg", $("#fbScanKkPetani"));
});

$("#scanSurat").change(function (e){
  validasiFile($(this), $("#lblScanSurat"), (MAX_IMAGE_SIZE*1024), "image/jpeg", $("#fbScanSurat"));
});

$("#namaPetani").on("change", function(){
  var fbNamaPetani = $("#fbNamaPetani");
  fbNamaPetani.hide();
  $(this).removeClass("is-invalid");
});

function readFile(scanKtp, scanKk){
  var dokumen = objScanPetani();
  var readerKtp = new FileReader();
  readerKtp.onload = function (e){
    dokumen.scanKtpPetani = (readerKtp.result).split("data:image/jpeg;base64,")[1];
  }
  readerKtp.readAsDataURL(scanKtp);
  var readerKk = new FileReader();
  readerKk.onload = function (e){
    dokumen.scanKkPetani = (readerKtp.result).split("data:image/jpeg;base64,")[1];
  }
  readerKk.readAsText(scanKk);
  arrayScanPetani.push(dokumen);
}

$("#btnSimpan").on("click", function(){
  $.ajax({
    url: js_base_url + "Rdkk_add/getArrayPetani",
    type: "POST",
    dataType: "json",
    data: "petani=" + JSON.stringify(arrayPetani)
  });
});

$("#btnSimpanPetani").on("click", function(){
  var fileGpxKebun = $("#fileGpxKebun");
  var fileScanKtpPetani = $("#scanKtpPetani");
  var fileScanKkPetani = $("#scanKkPetani");
  var fbFileGpx = $("#fbFileGpx");
  var fbScanKtpPetani = $("#fbScanKtpPetani");
  var fbScanKkPetani = $("#fbScanKkPetani");
  var namaPetani = $("#namaPetani");
  var fbNamaPetani = $("#fbNamaPetani");
  var errMsg = $("#errMsg");
  if (fileGpxKebun.val() != "" && namaPetani.val() != "" && fileScanKtpPetani.val() != "" && fileScanKkPetani != ""){
    var gpxFile = $("#fileGpxKebun")[0].files[0];
    var scanKtpObj = fileScanKtpPetani[0].files[0];
    var scanKkObj = fileScanKtpPetani[0].files[0];
    readFile(scanKtpObj, scanKkObj);
    readOpenLayers(gpxFile);
    namaPetani.removeClass("is-invalid");
    fileGpxKebun.removeClass("is-invalid");
    fbFileGpx.hide();
    fbNamaPetani.hide();
  } else {
    if (namaPetani.val() == ""){
      fbNamaPetani.show();
      namaPetani.addClass("is-invalid");
    };
    if (fileGpxKebun.val() == ""){
      fbFileGpx.show();
      fbFileGpx.html("File gpx belum diinput!");
      fileGpxKebun.addClass("is-invalid");
    };
    if (fileScanKtpPetani.val() == ""){
      fbScanKtpPetani.show();
      fbScanKtpPetani.html("File scan KTP petani belum diinput!");
      fbScanKtpPetani.addClass("is-invalid");
    };
    if (fileScanKkPetani.val() == ""){
      fbScanKkPetani.show();
      fbScanKkPetani.html("File scan KK petani belum diinput!");
      fileScanKkPetani.addClass("is-invalid");
    };
  }
});

$("#tblPetani").DataTable({
  bFilter: false,
  bPaginate: false,
  bSort: false,
  bInfo: false,
  data: arrayPetani,
  columns : [
    {data: "no", render: function(data, type, row, meta){return meta.row + meta.settings._iDisplayStart + 1}},
    {data: "nama_petani"},
    {data: "luas",
      render: function(data, type, row, meta){
        return data.toLocaleString(undefined, {maximumFractionDigits:2}) + " Ha"
      },
      className: "text-right"
    },
    {data: "button", render: function(data, type, row, meta){return '<button type="button" class="btn btn-danger btn-sm" name="hapus" >Hapus</button>'}}
  ],
  "footerCallback": function (row, data, start, end, display){
      var api = this.api(), data;
      var getIntVal = function (i){
        return typeof i === 'string' ? i.replace(/Ha/g,'')*1 : typeof i === 'number' ? i : 0;
      };
      total = api.column(2).data().reduce(function (a,b){
        return getIntVal(a) + getIntVal(b);
      },0);
      $(api.column(2).footer()).html(total.toLocaleString(undefined, {maximumFractionDigits: 2}) + " Ha");
  }
});

$("#tblPetani").on("click", "button[name='hapus']", function(e){
  var currentRow = $(this).closest("tr");
  var currentRowData = currentRow.find("td").slice(1,2).text();
  var index = arrayPetani.findIndex(function (item) {return item.nama_petani == currentRowData});
  console.log(index);
  arrayPetani.splice(index,1);
  currentRow.remove();
  //console.log(arrayPetani);
  refreshData();
});
