<?php

ob_start();

if ($_POST) {

    $valorMediacao = isset($_POST['txtValor']) ? (float) $_POST['txtValor'] : 0.0;
    $taxaRegistro = isset($_POST['taxa']) ? (float) $_POST['taxa'] : 0.0;
    $qtdHoras = isset($_POST['horas']) ? (int) $_POST['horas'] : 0;
    
    // Condição para horas excedentes
    $horasExcedentesvalor = 0;
    $msgHorasExcedentes = "Quantidade de horas abaixo do contratado";
    if ($qtdHoras > 20) {
        $horasExcedentesvalor = $qtdHoras - 20;
        if ($horasExcedentesvalor > 0) {
            $msgHorasExcedentes = "Horas Adicionais";
        }
    }

    $taxas = [
        [
            "faixa_inicial" => 0,
            "faixa_final" => 5000000,
            "tx_adm" => 10000,
            "valor_tx" => function($valorMediação) {
                return ($valorMediação > 0 && $valorMediação <= 5000000) ? 10000 : 0;
            }
        ],
        [
            "faixa_inicial" => 5000000.01,
            "faixa_final" => 10000000,
            "tx_adm" => 20000,
            "valor_tx" => function($valorMediação) {
                return ($valorMediação > 5000000 && $valorMediação <= 10000000) ? 20000 : 0;
            }
        ],
        [
            "faixa_inicial" => 10000000.01,
            "faixa_final" => 50000000,
            "tx_adm" => 30000,
            "valor_tx" => function($valorMediação) {
                return ($valorMediação > 10000000 && $valorMediação <= 50000000) ? 30000 : 0;
            }
        ],
        [
            "faixa_inicial" => 50000000.01,
            "faixa_final" => 100000000,
            "tx_adm" => 40000,
            "valor_tx" => function($valorMediação) {
                return ($valorMediação > 50000000 && $valorMediação <= 100000000) ? 40000 : 0;
            }
        ],
        [
            "faixa_inicial" => 100000000.01,
            "faixa_final" => 300000000,
            "tx_adm" => 60000,
            "valor_tx" => function($valorMediação) {
                return ($valorMediação > 100000000 && $valorMediação <= 300000000) ? 60000 : 0;
            }
        ],
        [
            "faixa_inicial" => 300000000.01,
            "faixa_final" => 500000000,
            "tx_adm" => 80000,
            "valor_tx" => function($valorMediação) {
                return ($valorMediação > 300000000 && $valorMediação <= 500000000) ? 80000 : 0;
            }
        ],
        [
            "faixa_inicial" => 500000000.01,
            "faixa_final" => PHP_FLOAT_MAX,
            "tx_adm" => 100000,
            "valor_tx" => function($valorMediação) {
                return ($valorMediação > 500000000 && $valorMediação <= PHP_FLOAT_MAX) ? 100000 : 0;
            }
        ],
    ];


    $honorarios = [
        [
            "faixa_inicial" => 0,
            "faixa_final" => 1000000,
            "tx_honorarios" => 25000,
            "horas_adicionais" => 1000,
            "valor_honorarios" => '($valorMediacao >= 0 && $valorMediacao <= 1000000) ? 25000 : 0',
        ],
        [
            "faixa_inicial" => 1000000.01,
            "faixa_final" => 5000000,
            "tx_honorarios" => 30000,
            "horas_adicionais" => 1500,
            "valor_honorarios" => '($valorMediacao > 1000000 && $valorMediacao <= 5000000) ? 30000 : 0',
        ],
        [
            "faixa_inicial" => 5000000.01,
            "faixa_final" => 10000000,
            "tx_honorarios" => 40000,
            "horas_adicionais" => 2000,
            "valor_honorarios" => '($valorMediacao > 5000000 && $valorMediacao <= 10000000) ? 40000 : 0',
        ],
        [
            "faixa_inicial" => 10000000.01,
            "faixa_final" => 50000000,
            "tx_honorarios" => 50000,
            "horas_adicionais" => 2000,
            "valor_honorarios" => '($valorMediacao > 10000000 && $valorMediacao <= 50000000) ? 50000 : 0',
        ],
        [
            "faixa_inicial" => 50000000.01,
            "faixa_final" => 100000000,
            "tx_honorarios" => 60000,
            "horas_adicionais" => 2000,
            "valor_honorarios" => '($valorMediacao > 50000000 && $valorMediacao <= 100000000) ? 60000 : 0',
        ],
        [
            "faixa_inicial" => 100000000.01,
            "faixa_final" => 300000000,
            "tx_honorarios" => 70000,
            "horas_adicionais" => 2000,
            "valor_honorarios" => '($valorMediacao > 100000000 && $valorMediacao <= 300000000) ? 70000 : 0',
        ],
        [
            "faixa_inicial" => 300000000.01,
            "faixa_final" => 500000000,
            "tx_honorarios" => 75000,
            "horas_adicionais" => 2500,
            "valor_honorarios" => '($valorMediacao > 300000000 && $valorMediacao <= 500000000) ? 75000 : 0',
        ],
        [
            "faixa_inicial" => 500000000.01,
            "faixa_final" => PHP_FLOAT_MAX,
            "tx_honorarios" => 80000,
            "horas_adicionais" => 2500,
            "valor_honorarios" => '($valorMediacao > 500000000) ? 80000 : 0',
        ],
    ];


    // Cálculo da Taxa de Administração
    $taxaAdm = 2000;
    //foreach ($taxas as $faixa) {
       // if ($valorMediacao > $faixa['faixa_inicial'] && $valorMediacao <= $faixa['faixa_final']) {
         //   $taxaAdm += $faixa['tx_adm'] * 0.5; // Aplica 50%
          //  break;
       // }
    //}

    // Cálculo dos Honorários
    $honorario = 0;
    $valorHorasAdicionais = 0;
    foreach ($honorarios as $faixa) {
        if ($valorMediacao > $faixa['faixa_inicial'] && $valorMediacao <= $faixa['faixa_final']) {
            $honorario += $faixa['tx_honorarios'] * 0.5; // Aplica 50%
            if ($qtdHoras > 20) {
                $horasExcedentes = $qtdHoras - 20;
                $valorHorasAdicionais = $faixa['horas_adicionais'] * $horasExcedentes * 0.5; // 50% das horas adicionais
            }
            break;
        }
    }

    // Cálculo dos Totais
    //$totalSolicitada = $taxaAdm + $honorario + $valorHorasAdicionais;
    $totalSolicitantes = $taxaRegistro + $taxaAdm + $honorario + $valorHorasAdicionais;
    $totalSolicitada = $honorario + $valorHorasAdicionais;
} 
function formatarMoeda($valor) {
    return number_format((float) $valor, 2, ',', '.'); // Garante formato correto
}
?>
<!DOCTYPE html>
<html>
  <head>
    <script type="text/javascript">
      var AppPath = '/';
    </script>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <script src="../Scripts/Libs/jquery-1.11.0.min.js"></script>
    <script src="../Scripts/Libs/jquery.maskMoney.min.js"></script>
    
    <link rel='stylesheet' href='https://ccbc.org.br/cam-ccbc-centro-arbitragem-mediacao/wp-content/themes/Divi/style.css?ver=4.9.8' type='text/css' media='all' />
    <meta name="viewport" content="width=device-width" />
    <title>Calculadora Mediação</title>
    <style type="text/css">
      * { font-family: Open Sans, Arial, sans-serif; }
      h3 { text-align:center; }
      .tabela, .grupo-custas { width:auto; border: 1px solid #90C445; border-radius: 10px; background-color:#fff !important; }
      .linha { width:100%; display: flex; border-bottom: 1px dotted #ccc; color: #666; }
      .coluna { width:33%; padding: 5px; }
      .coluna50 { width:50%; padding: 5px; }
      .coluna100 { width:100%; padding: 5px; text-align:center; }
      .titulo { background-color:#90C445 !important; border-radius: 10px 10px 0 0; color: #fff !important; }
      .total { font-weight:bold; }
      input { font-size: 16px; padding: 10px; border-radius: 50px; border: 2px solid #90c445; margin: 0 0 -10px }
      .botao-calculadora { border-radius: 28px; border: 1px solid #90c445; cursor: pointer; color: #000000; font-size: 15px; padding: 10px 19px; }
      .botao { display: block; }
      .grupo-custas { padding: 0 5px 5px; margin: 20px 0; }
      
      form {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      gap: 20px;
      max-width: 1100px;
      margin: 0 auto;
    }

form div {
  display: flex;
  flex-direction: column;
  flex: 1;
}

form div label {
  margin-bottom: 8px;
}

form div input {
  width: 90%;
}


      
      @media (max-width: 768px) {
        form div {
          width: calc(50% - 10px); /* Dois campos lado a lado em telas menores */
        }
      }

      @media (max-width: 480px) {
        form div {
          width: 100%; /* Campos empilhados em telas muito pequenas */
        }
      }
    </style>
  </head>
  <body>
    <div>
      <!-- Formulário -->
      <div class="container-form-mediacao">
        <div class="">
          <h2>Calculadora de Mediação</h2>
            <form action="" method="POST" style="display: flex; align-content: flex-end; align-items: center;">
    <div>
        <label for="txtValor">Valor da Mediação:</label>
        <input type="text" 
               placeholder="R$ 0,00" 
               id="txtValor" 
               name="txtValor" 
               class="campo-monetario" 
               value="<?php echo isset($_POST['txtValor']) ? 'R$ ' . formatarMoeda($_POST['txtValor']) : ''; ?>" 
               required /><br>
    </div>
    <div>
        <label for="taxa">Taxa de Registro:</label>
        <input type="text" 
               placeholder="R$ 0,00" 
               id="taxa" 
               name="taxa" 
               class="campo-monetario" 
               value="<?php echo isset($_POST['taxa']) ? 'R$ ' . formatarMoeda($_POST['taxa']) : ''; ?>" 
               required /><br>
    </div>
    <div>
        <label for="horas">Quantidade de horas:</label>
        <input type="text" 
               placeholder="0" 
               id="horas" 
               name="horas" 
               class="campo" 
               value="<?php echo isset($_POST['horas']) ? (int) $_POST['horas'] : ''; ?>" 
               /><br>
    </div>
    <p>
        <input class="botao botao-calculadora" type="submit" value="Calcular">
    </p>
</form>
        </div>

        <div class="grupo-custas">
          <div class="linhas">
            <h3>Custos Detalhados</h3>
          </div>
          <div class="linhas">
                <h4>Custas Principais</h4>
          </div>
          <div class="tabela">
            <div class="linha titulo">
              <div class="coluna">&nbsp;</div>
              <div class="coluna">Solicitante(s)</div>
              <div class="coluna">Solicitadas(s)</div>
            </div>
            <div class="linha">
              <div class="coluna">Taxa de administração</div>
              <div class="coluna">
                <span id="lblRequerenteTaxaRegistro2">
                  <?php echo isset($taxaAdm) ? "R$" . number_format($taxaAdm, 2, ',', '.') : 'R$ 0,00'; ?>
                </span>
              </div>
              <div class="coluna">
                <span id="lblRequerenteTaxaRegistro2">
                   ---
                </span>
              </div>
            </div>
            <div class="linha">
              <div class="coluna">Honorários</div>
              <div class="coluna">
                <span id="lblRequerenteTaxaAdministracao2">
                  <?php echo isset($honorario) ? "R$" . number_format($honorario, 2, ',', '.') : 'R$ 0,00'; ?>
                </span>
              </div>
              <div class="coluna">
                <span id="lblRequeridoTaxaAdministracao2">
                  <?php echo isset($honorario) ? "R$" . number_format($honorario, 2, ',', '.') : 'R$ 0,00'; ?>
                </span>
              </div>
            </div>
            <?php if($horasExcedentesvalor > 0): ?>
                <div class="linha">
                    <div class="coluna">Horas Excedentes</div>
                    <div class="coluna">
                        <span id="lblRequerenteHonorarioArbitros2">
                            <?php echo isset($valorHorasAdicionais) ? "R$" . number_format($valorHorasAdicionais, 2, ',', '.') : 'R$ 0,00'; ?>
                        </span>
                    </div>
                    <div class="coluna">
                        <span id="lblRequeridoHonorarioArbitros2">
                            <?php echo isset($valorHorasAdicionais) ? "R$" . number_format($valorHorasAdicionais, 2, ',', '.') : 'R$ 0,00'; ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>
              <div class="linha total">
                <div class="coluna">Totais</div>
                <div class="coluna">
                  <span id="lblRequerenteTotal2">
                    <?php echo isset($totalSolicitantes) ? "R$" . number_format($totalSolicitantes, 2, ',', '.') : 'R$ 0,00'; ?>
                  </span>
                </div>
                <div class="coluna">
                  <span id="lblRequeridoTotal2">
                    <?php echo isset($totalSolicitada) ? "R$" . number_format($totalSolicitada, 2, ',', '.') : 'R$ 0,00'; ?>
                  </span>
                </div>
              </div>
          </div>
           <?php if($horasExcedentesvalor > 0): ?>
          
			  &nbsp;
              <div class="tabela">
                  <div class="linha titulo">
                    <div class="coluna100">Estimativa de horas adicionais</div>
                  </div>
                  <div class="linha">
 					<div class="coluna50">Horas Estimadas</div>
                    <div class="coluna50">
                      <span id="lblRequerenteTaxaRegistro2">
                        <?php echo isset($qtdHoras) ? $qtdHoras : ''; ?>
                      </span>
                    </div>
                  </div> 
                	
                  <div class="linha">
                    <div class="coluna50">Valor horas estimadas</div>
                    <div class="coluna50">
                        <span id="lblRequerenteTaxaRegistro2">
                         <?php echo isset($valorHorasAdicionais) ? "R$" . number_format($valorHorasAdicionais, 2, ',', '.') : 'R$ 0,00'; ?>
                        </span>
                      </div>
                </div>
              </div>
    		<?php endif; ?> 
      </div>
     </div>
    <script>
    $(document).ready(function() {
        // Aplica a máscara nos campos monetários
        $('.campo-monetario').maskMoney({
            prefix: 'R$ ',          // Prefixo para reais
            allowNegative: false,  // Não permite valores negativos
            thousands: '.',        // Separador de milhar
            decimal: ',',          // Separador decimal
            affixesStay: true      // Mantém o prefixo ao editar
        });

        // Formata automaticamente os valores carregados pelo PHP no frontend
        $('.campo-monetario').each(function() {
            let valor = $(this).val();
            if (valor) {
                $(this).maskMoney('mask', valor.replace('R$ ', '')); // Reaplica máscara
            }
        });

        // Remove a máscara ao enviar o formulário
        $('form').on('submit', function() {
            $('.campo-monetario').each(function() {
                let valorNumerico = $(this).maskMoney('unmasked')[0]; // Valor puro
                $(this).val(valorNumerico); // Remove máscara antes de enviar
            });
        });
    });
</script>
  </body>
</html>
