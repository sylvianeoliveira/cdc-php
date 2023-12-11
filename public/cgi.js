function priceTable(numParcelas, valorFinanciado, prestacao, table, jurosTotal){
    let tableTags = `
        <p>Tabela Price</p>
        <table>
        <thead>
            <tr>
                <th>Mês</th>
                <th>Prestação</th>
                <th>Juros</th>
                <th>Amortização</th>
                <th>Saldo Devedor</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>n</td>
                <td>R = pmt</td>
                <td>J = SD * t</td>
                <td>U = pmt - J</td>
                <td>SD = PV - U</td>
            </tr>`;

    tableTags += ("<tr><td>" + table[0][0] + "</td>" +
                  "<td>" + table[0][1].toFixed(2) + "</td>" +
                  "<td>(" + table[0][2].toFixed(4) + ")</td>" +
                  "<td>" + table[0][3].toFixed(2) + "</td>" +
                  "<td>" + table[0][4].toFixed(2) + "</td></tr>");
    for (let i = 1; i <= numParcelas; i++){
        tableTags += "<tr>";
        for(let j = 0; j < 5; j++){
            tableTags += "<td>" + (j==0 ? table[i][j] : table[i][j].toFixed(2)) + "</td>";
        }
        tableTags += "</tr>";
    }
    tableTags += `
            <tr>
            <td>Total</td>
            <td> ${($("#idp").is(":checked")? prestacao*(numParcelas+1) : prestacao*numParcelas).toFixed(2)} </td>
            <td> ${jurosTotal.toFixed(3)} </td>
            <td> ${valorFinanciado.toFixed(2)} </td>
            <td>0.00</td>
            </tr>
        </tbody>
    </table>`;
    return tableTags;
}

function infoTable(numParcelas, taxaJuros, valorFinanciado, valorFinal, valorVoltar, mesesVoltar, entrada){
    console.log("infos: --->>>", numParcelas, taxaJuros, valorFinanciado, valorFinal, valorVoltar,mesesVoltar, entrada);
    let infos = `
    <p>Parcelamento: ${entrada ? "1+" : ""}${numParcelas} meses</p>
    <p>Taxa: ${(taxaJuros * 100).toFixed(2)}% ao mês = ${(((1+taxaJuros)**12 -1)*100).toFixed(2)}% ao ano</p>
    <p>Valor Financiado: $${valorFinanciado.toFixed(2)}</p>
    <p>Valor Final: $${parseFloat(valorFinal).toFixed(2)}</p>
    <p>Valor a Voltar: $${parseFloat(valorVoltar).toFixed(2)}</p>
    <p>Meses a Voltar: ${mesesVoltar}</p>
    <p>Entrada: ${$("#idp").is(":checked")}</p>
    `;
    return infos;
}

function resultTable(numParcelas, taxaJuros, valorFinanciado, valorVoltar, mesesVoltar, cf, prestacao, numIte, taxaReal){    
    if (valorVoltar > 0){
        mesesVoltar = Math.ceil(valorVoltar / prestacao);
    }

    return `
    <p>Coefiente de Financiamento: ${cf.toFixed(6)}</p>
    <p>Prestação: ${cf.toFixed(6)} * \$${valorFinanciado.toFixed(2)} = \$${prestacao.toFixed(2)} ao mês</p>
    <p>Valor Pago com Juros: \$${($("#idp").is(":checked")? prestacao * (numParcelas+1) : prestacao * numParcelas).toFixed(2)}</p>
    <p>Taxa Real (${numIte} iterações): ${taxaReal.toFixed(4)}% ao mês</p>
    <p>Valor Corrigido: \$${ mesesVoltar > 0 ? presentValue(valorVoltar, mesesVoltar, taxaJuros, false)[1].toFixed(2) : 0}</p> `;
}

$("#back").click(function () {
    window.location.reload();
});