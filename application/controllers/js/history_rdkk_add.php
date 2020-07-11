<?
$scriptContent =
'
  $("#errMsg").hide();
  $("#iconLoading").hide();

  $("#namaKelompok").bind("keyup blur", function(){
    $(this).val($(this).val().replace(/[^a-zA-Z ]/g,""));
  });
  $("#namaPetani").bind("keyup blur", function(){
    $(this).val($(this).val().replace(/[^a-zA-Z ]/g,""));
  });

  $.ajax({
    url: "Rdkk_add/getAllKabupaten",
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
            url: "Rdkk_add/getDesaByKabupaten",
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
            async: false,
            url: "Rdkk_add/getKecByDesa",
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
            url: "Rdkk_add/getKecByDesa",
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
      var petani = objPetani(
        null,
        null,
        $("#namaPetani").val(),
        luasLahan/10000,
        geom.getCoordinates()
      );
      $("#lblFileGpxKebun").text("Pilih file");
      $("#fileGpxKebun").val("");
      arrayPetani.push(petani);
      refreshData();
      console.log(arrayPetani);
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
  var formAddPetani = $("#formAddPetani")[0];
  var objPetani = function(id_petani, id_kelompok, nama_petani, luas, arrayGPS){
    var obj = {};
    obj.id_petani = id_petani;
    obj.id_kelompok = id_kelompok;
    obj.nama_petani = nama_petani;
    obj.luas = luas;
    obj.gps = arrayGPS;
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

  $("#scanKtp").change(function (e){
    var selectedFile = $(this)[0].files[0];
    var lblScanKtp = $("#lblScanKtp");
    function resetForm(){
      lblScanKtp.text("Pilih file");
      $(this).val("");
    };
    lblScanKtp.text(selectedFile.name);
    if (selectedFile.type != "image/jpeg"){
      alert("Invalid format!");
      resetForm();
    } else {
      if (selectedFile.size/1000 > 500){
        alert("Ukuran file Scan KTP melebihi batas maksimum (500kB)");
        resetForm();
      }
    }
  })

  $("#namaPetani").on("change", function(){
    var fbNamaPetani = $("#fbNamaPetani");
    fbNamaPetani.hide();
    $(this).removeClass("is-invalid");
  });

  $("#btnSimpanPetani").on("click", function(){
    var fileGpxKebun = $("#fileGpxKebun");
    var fbFileGpx = $("#fbFileGpx");
    var namaPetani = $("#namaPetani");
    var fbNamaPetani = $("#fbNamaPetani");
    var errMsg = $("#errMsg");
    if (fileGpxKebun.val() != "" && namaPetani.val() != ""){
      var selectedFile = $("#fileGpxKebun")[0].files[0];
      readOpenLayers(selectedFile);
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
      {data: "button", render: function(data, type, row, meta){return \'<button type="button" class="btn btn-danger btn-sm" name="hapus" >Hapus</button>\'}}
    ],
    "footerCallback": function (row, data, start, end, display){
        var api = this.api(), data;
        var getIntVal = function (i){
          return typeof i === \'string\' ? i.replace(/Ha/g,\'\')*1 : typeof i === \'number\' ? i : 0;
        };
        total = api.column(2).data().reduce(function (a,b){
          return getIntVal(a) + getIntVal(b);
        },0);
        $(api.column(2).footer()).html(total.toLocaleString(undefined, {maximumFractionDigits: 2}) + " Ha");
    }
  });

  $("#tblPetani").on("click", "button[name=\"hapus\"]", function(e){
    var currentRow = $(this).closest("tr");
    var currentRowData = currentRow.find("td").slice(1,2).text();
    var index = arrayPetani.findIndex(function (item) {return item.nama_petani == currentRowData});
    console.log(index);
    arrayPetani.splice(index,1);
    currentRow.remove();
    //console.log(arrayPetani);
    refreshData();
  });

';
?>
