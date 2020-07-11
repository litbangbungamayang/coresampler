var dtpAwal = $("#dtpAwal");
var btnProsesData = $("#btnProsesData");
var btnDownload = $("#btnDownload");
var lbl_tonTebu = $("#ton_tebu");
var lbl_hablurAnalisa = $("#hablur_analisa");
var lbl_rendAnalisa = $("#rend_analisa");

btnProsesData.on("click", function (){
  (dtpAwal.val() == "") ? dtpAwal.addClass("is-invalid") : dtpAwal.removeClass("is-invalid");
  if (dtpAwal.val() != ""){
    $tglTimbang = formatTgl(dtpAwal.datepicker("getDate")),
    $.ajax({
      url: js_base_url + "Daily_ari/getDataDaily",
      type: "GET",
      dataType: "json",
      data: "tglTimbang=" + $tglTimbang,
      success: function(data){
        lbl_tonTebu.html(data[0].ton_tebu);
        lbl_hablurAnalisa.html(data[0].hablur_analisa);
        lbl_rendAnalisa.html(data[0].rend);
      }
    })
  }
})

btnDownload.on("click", function(){
  var tgl_timbang = dtpAwal.datepicker("getDate");
  if (tgl_timbang != null){
    $.ajax({
      url: js_base_url + "Daily_ari/getLaporanAri",
      type: "GET",
      dataType: "json",
      data: "tglTimbang=" + formatTgl(tgl_timbang),
    }).done(function(data){
        var $a = $("<a>");
        $a.attr("href",data.file);
        $("body").append($a);
        $a.attr("download","file.xls");
        $a[0].click();
        $a.remove();
    });
  }
})

function formatTgl(dateObj){
  if(dateObj != null){
    return dateObj.getFullYear() + "-" + ("0" + (dateObj.getMonth()+1)) + "-" + ("0" + dateObj.getDate()).slice(-2);
  }
  return "";
}

dtpAwal.datepicker({
  format: "dd-MM-yyyy"
});
