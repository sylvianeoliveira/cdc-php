$(document).ready(function () {
    $("#priceForm").submit(function (event) {
      // Impedir o envio do formulário padrão
      event.preventDefault();

      // Obter os dados do formulário
      let imprime = $("#iip").is(":checked");
      let formData = $(this).serialize();

      // Enviar os dados para o servidor
      $.ajax({
        type: "POST",
        url: "../api/server.php",
        data: formData,
        success: function (data) {
            console.log(data);
          $("#cdcfieldset").hide();
          $("#back").show();
          $("#infoTable").html(infoTable(data.numParcelas, data.taxaJuros, data.valorFinanciado, data.valorFinal, data.valorVoltar, data.mesesVoltar, data.entrada));
          $("#infoTable").show();
          $("#priceTable").html(priceTable(data.numParcelas, data.valorFinanciado, data.prestacao, data.tabelaPrice));
          $("#priceTable").show();
          $("#resultTable").html(resultTable(data.numParcelas, data.taxaJuros, data.valorFinanciado, data.valorVoltar, data.mesesVoltar, data.cf, data.prestacao, data.numIte, data.taxaReal));
          $("#resultTable").show();
          if(imprime) window.print();
        },
        error: function (error) {
          // Lidar com erros de requisição
          console.log(error.responseText);
        },
      });
    });
});
