$(document).ready(function () {
  $("#priceForm").submit(function (event) {
    event.preventDefault();

    let imprime = $("#iip").is(":checked");
    let formData = $(this).serialize();

    $.ajax({
      type: "POST",
      url: "../api/index.php",
      data: formData,
      success: function (data) {
        $("#cdcfieldset").hide();
        $("#back").show();
        $("#infoTable").html(infoTable(data.numParcelas, data.taxaJuros, data.valorFinanciado, data.valorFinal, data.valorVoltar, data.mesesVoltar, data.entrada));
        $("#infoTable").show();
        $("#priceTable").html(priceTable(data.numParcelas, data.valorFinanciado, data.prestacao, data.tabelaPrice, data.jurosTotal));
        $("#priceTable").show();
        $("#resultTable").html(resultTable(data.numParcelas, data.taxaJuros, data.valorFinanciado, data.valorVoltar, data.mesesVoltar, data.cf, data.prestacao, data.numIte, data.taxaReal));
        $("#resultTable").show();
        if(imprime) window.print();
      },
      error: function (error) {
        console.log(error.responseText);
      },
    });
  });
});
