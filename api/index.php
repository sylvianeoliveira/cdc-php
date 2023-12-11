<?php

function CF($t, $p) {
    return $t / (1 - pow(1 + $t, -$p));
}

function metodoNewton($f, $df, $x, $y, $p) {
    $t = $x / $y;
    $t0 = 0;
    $n = 0;

    while (true) {
        $t0 = $t;
        $n++;
        $t = $t0 - $f($t, $x, $y, $p) / $df($t, $x, $y, $p);
        
        if (abs($t - $t0) < 1e-4) {
            return [$t, $n];
        }
    }
}

function fSemEntrada($t, $x, $y, $p) {
    // $t -> taxa de juros
    // $p -> numero de parcelas
    // $x -> total a prazo

    $a = pow(1 + $t, -$p);
    return $y * $t - $x / $p * (1 - $a);
}

function dfSemEntrada($t, $x, $y, $p) {
    // $t -> taxa de juros
    // $p -> numero de parcelas
    // $x -> total a prazo

    $b = pow(1 + $t, -($p + 1));
    return $y - $x * $b;
}

function getInterest($x, $y, $p) {
    $R = $x / $p;

    if ((isset($_POST["dp"]) ? 1 : 0)) {
        // Com entrada
        $resultado = metodoNewton('fSemEntrada', 'dfSemEntrada', $x - $R, $y - $R, $p - 1);
    } else {
        // Sem entrada
        $resultado = metodoNewton('fSemEntrada', 'dfSemEntrada', $x, $y, $p);
    }

    return $resultado;
}

function presentValue($x, $p, $t, $fix = true) {
    $factor = 1.0 / ($p * CF($t, $p));

    if ($fix && (isset($_POST["dp"]) ? 1 : 0)) {
        $factor *= 1.0 + $t;
    }

    return [$factor, $x * $factor];
}

function priceTable($numParcelas, $valorFinanciado, $taxaJuros, $prestacao) {
    $table = [];
    $jurosTotal = 0;

    for ($i = 0; $i <= $numParcelas; $i++) {
        $table[$i] = [0, 0, 0, 0, 0];
    }

    $table[0][0] = 0;

    
    if (isset($_POST["dp"]) ? 1 : 0) {
        $table[0][1] = $prestacao;
    } else {
        $table[0][1] = 0.0;
    }
    
    $table[0][2] = $taxaJuros;
    $table[0][3] = 0.0;
    $table[0][4] = $valorFinanciado;
    
    for ($i = 1; $i <= $numParcelas; $i++) {
        $table[$i][0] = $i;
        $table[$i][1] = $prestacao;
        $table[$i][2] = $table[$i - 1][4] * $taxaJuros;
        $table[$i][3] = $prestacao - $table[$i][2];
        $saldo = $table[$i - 1][4] - $table[$i][3];
        $table[$i][4] = ($saldo <= 1e-4 ? 0 : $saldo);
        $jurosTotal += $table[$i][2];
    }
    
    
    return [$table, $jurosTotal];
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $n = isset($_POST["np"]) ? intval($_POST["np"]) - (isset($_POST["dp"]) ? 1 : 0) : 0;
    
    $numParcelas = intval($_POST['np']);
    $taxaJuros = floatval($_POST['tax']) / 100;
    $valorFinanciado = floatval($_POST['pv']) / 1;
    $valorFinal = isset($_POST['pp']) ? floatval($_POST['pp']) : 0;
    $valorVoltar = isset($_POST['pb']) ? floatval($_POST['pb']) : 0;
    $mesesVoltar = isset($_POST['mesesVoltar']) ? intval($_POST['mesesVoltar']) : 0;
    $entrada = isset($_POST["dp"]) ? 1 : 0;

    if ($taxaJuros == 0 && $valorFinal == 0) {
        echo json_encode(["error" => "Taxa de juros e valor final não podem ser ambos nulos."]);
        exit;
    }

    if ($taxaJuros == 0 && $valorFinanciado == 0) {
        echo json_encode(["error" => "Taxa de juros e valor financiado não podem ser ambos nulos."]);
        exit;
    }

    if (!$valorFinanciado && !$valorFinal) {
        echo json_encode(["error" => "Valor financiado e valor final não podem ser ambos nulos."]);
        exit;
    }

    if ($numParcelas < 0 || $taxaJuros < 0 || $valorFinanciado < 0 )  {
        echo json_encode(["error" => "Nenhum valor de entrada pode ser negativo."]);
        exit;
    }

    if ($valorFinal < 0 || $valorVoltar < 0 || $mesesVoltar < 0 )  {
        echo json_encode(["error" => "Nenhum valor de entrada pode ser negativo."]);
        exit;
    }

    if ($mesesVoltar == 0 && $numParcelas == 0) {
        echo json_encode(["error" => "Meses a voltar não pode ser maior do que o número de parcelas."]);
        exit;
    }

    if ($valorFinanciado == 0) {
        $valorFinanciado = presentValue($valorFinal, $numParcelas, $taxaJuros)[1];
    }

    $prestacao = $valorFinal / $numParcelas;
    $cf = 0;
    $numIte = 0;
    $taxaReal = 0;

    try {
        if ($taxaJuros == 0) {
            if ($prestacao >= $valorFinanciado) {
                echo json_encode(["error" => "Prestação (\$$prestacao) é maior do que o empréstimo."]);
                exit;
            }
            list($taxaReal, $numIte) = getInterest($valorFinal, $valorFinanciado, $numParcelas);
            $taxaJuros = 0.01 * $taxaReal;
        }
    
        $cf = CF($taxaJuros, $numParcelas);
        $prestacao = $valorFinanciado * $cf;
    
        if ($prestacao >= $valorFinanciado) {
            echo json_encode(["error" => "Prestação (\$$prestacao) é maior do que o empréstimo."]);
            exit;
        }
    
        if ($entrada) {
            $prestacao /= 1 + $taxaJuros;
            $numParcelas -= 1;
            $valorFinanciado -= $prestacao;
            $cf = $prestacao / $valorFinanciado;
        }
    } catch (Exception $e) {
        echo json_encode(["error" => $e.getMessage()]);
    }

    if ($valorVoltar == 0 && $mesesVoltar > 0) {
        $valorVoltar = $prestacao * $mesesVoltar;
        
    }

    header('Content-Type: application/json');

    echo json_encode([
        "numParcelas" => $numParcelas,
        "taxaJuros" => $taxaJuros,
        "valorFinanciado" => $valorFinanciado,
        "valorFinal" => $valorFinal,
        "valorVoltar" => $valorVoltar,
        "mesesVoltar" => $mesesVoltar,
        "entrada" => $entrada,
        "prestacao" => $prestacao,
        "cf" => $cf,
        "taxaReal" => $taxaReal,
        "numIte" => $numIte,
        "tabelaPrice" => priceTable($numParcelas, $valorFinanciado, $taxaJuros, $prestacao)[0],
        "jurosTotal" => priceTable($numParcelas, $valorFinanciado, $taxaJuros, $prestacao)[1]
    ]);

    exit;
}
?>
