<?php

ob_start();

if ($_POST) {
  $id = time();
  $valor = str_replace("R$ ", "",  $_REQUEST['txtValor2025']);
  $valor = str_replace(".", "",  $valor);
  $valor = str_replace(",", ".",  $valor);

  var_dump($valor);

  $lblRequerenteTaxaRegistro = 5500;
  $lblRequerenteTaxaAdministracao = 0;
  $lblRequerenteHonorarioArbitros = 0;

  if ($_REQUEST['calculator'] === 'default') {
    $mapAdm =  [
      ['max' => 4000000, 'base' => 144938.69, 'tax' => null],
      ['max' => 10000000, 'base' => 144938.69, 'tax' => 0.002898774],
      ['max' => 18000000, 'base' => 162331.34, 'tax' => 0.002608896],
      ['max' => 50000000, 'base' => 183202.51, 'tax' => 0.002319019],
      ['max' => 100000000, 'base' => 257411.12, 'tax' => 0.002029141],
      ['max' => 150000000, 'base' => 358868.20, 'tax' => 0.001449387],
      ['max' => 300000000, 'base' => 431337.55, 'tax' => 0.000144938],
      ['max' => 500000000, 'base' => 453078.36, 'tax' => 0.000072469],
      ['max' => 1000000000, 'base' => 467572.23, 'tax' => 0.000036235],
      ['max' => null, 'base' => 485689.57, 'tax' => 0.000018117]
    ];

    //Taxa de Administração por Parte
    foreach ($mapAdm as $idx => $item) {
      if ($item['max'] === null || $valor <= $item['max']) {
        $lblRequerenteTaxaAdministracao = $item['base'] + (($valor - ($mapAdm[$idx - 1]['max'] ?? 0)) * ($item['tax'] ?: 0));
        break;
      }
    }

    $mapArb = [
      ['max' => 2000000, 'base' => 326112.07, 'tax' => null],
      ['max' => 4000000, 'base' => 326112.07, 'tax' => 0.0543521072273],
      ['max' => 10000000, 'base' => 434816.08, 'tax' => 0.03913434772037],
      ['max' => 18000000, 'base' => 669616.77, 'tax' => 0.01304448257482],
      ['max' => 50000000, 'base' => 773972.63, 'tax' => 0.0043416085782],
      ['max' => 100000000, 'base' => 913113.78, 'tax' => 0.0036583340339],
      ['max' => 150000000, 'base' => 1108781.02, 'tax' => 0.00260889651469],
      ['max' => 300000000, 'base' => 1239285.85, 'tax' => 0.00130444825735],
      ['max' => 500000000, 'base' => 1500115.50, 'tax' => 0.00108704421445],
      ['max' => 1000000000, 'base' => 1761055.15, 'tax' => 0.0008693217156],
      ['max' => null, 'base' => 2304525.25, 'tax' => 0.0008693217156]
    ];

    ////////III. Honorários dos Árbitros
    foreach ($mapArb as $idx => $item) {
      if ($item['max'] === null || $valor <= $item['max']) {
        $lblRequerenteHonorarioArbitros += $item['base'];
        break;
      }
    }

    //$lblRequerenteTaxaAdministracao = floor($lblRequerenteTaxaAdministracao);
    $lblRequerenteTaxaAdministracao /= 2;
    $lblRequerenteHonorarioArbitros3 = $lblRequerenteHonorarioArbitros / 2;
    $lblRequerenteHonorarioArbitros = ($lblRequerenteHonorarioArbitros * 0.4) / 2;
  } else if ($valor <= 3000000) {
    ////////////Taxa de Administração por Parte
    if ($valor > 0 and $valor < 1000000) {
      $lblRequerenteTaxaAdministracao = 32900;
    } else if ($valor >= 1000000 and $valor <= 2000000) {
      $lblRequerenteTaxaAdministracao = 32900 + (($valor - 1000000) * 0.0099);
    } else if ($valor >= 2000000 and $valor <= 3000000) {
      $lblRequerenteTaxaAdministracao = 42800 + (($valor - 2000000) * 0.0099);
    }

    ////////III. Honorários dos Árbitros
    if ($valor > 0 and $valor < 1000000) {
      $lblRequerenteHonorarioArbitros = 49400;
    } else if ($valor >= 1000000 and $valor <= 2000000) {
      $lblRequerenteHonorarioArbitros = 49400 + (($valor - 1000000) * 0.0154);
    } else if ($valor >= 2000000 and $valor <= 3000000) {
      $lblRequerenteHonorarioArbitros = 64800 + (($valor - 2000000) * 0.0154);
    }

    $lblRequerenteTaxaRegistro = null;
    $lblRequerenteTaxaAdministracao /= 2;
    $lblRequerenteHonorarioArbitros3 = ($lblRequerenteHonorarioArbitros + ($lblRequerenteHonorarioArbitros * 2 * 0.83333)) / 2;
    $lblRequerenteHonorarioArbitros /= 2;
  }
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
  <script src="../Scripts/jCalculadora2025.js?v=_2_O2ISiGBe5X6-5gdMmonh8tFKYZaYX5PYQUMoNxH41"></script>
  <link rel='stylesheet' id='sb_dtm_custom_css-css' href='https://ccbc.org.br/cam-ccbc-centro-arbitragem-mediacao/wp-content/themes/Divi/style.css?ver=4.9.8' type='text/css' media='all' />
  <meta name="viewport" content="width=device-width" />
  <title>Calculadora 2025</title>
  <style type="text/css">
    * {
      font-family: Open Sans, Arial, sans-serif;
    }

    h3 {
      text-align: center;
    }

    .tabela,
    .grupo-custas {
      width: auto;
      border: 1px solid #90C445;
      border-radius: 10px;
      background-color: #fff !important;

    }

    .linha {
      width: 100%;
      display: flex;
      border-bottom: 1px dotted #ccc;
      color: #666;
    }

    .coluna {
      width: 33%;
      float: left;
      padding: 5px;
    }

    .coluna50 {
      width: 50%;
      float: left;
      padding: 5px;
    }

    .coluna100 {
      width: 100%;
      float: left;
      padding: 5px;
      text-align: center;
    }

    .titulo {
      background-color: #90C445 !important;
      border-radius: 10px 10px 0 0;
      color: #fff !important;
      border-bottom: none;

    }

    .total {
      font-weight: bold;
      border-bottom: none;
    }

    input {
      font-size: 16px;
      padding: 10px;
      border-radius: 50px;
      border: 2px solid #90c445;
      margin: 0 0 -10px
    }

    .botao-calculadora {
      border-radius: 28px;
      border: 1px solid #90c445;
      display: inline-block;
      cursor: pointer;
      color: #000000;
      font-size: 15px;
      padding: 10px 19px;
      text-decoration: none;
      //margin-left: 50%;
      //transform: translateX(-50%);
      //white-space: nowrap;
    }

    .botao {
      display: block;
      margin: 10px 0 0;
    }

    .grupo-custas {
      padding: 0 5px 5px;
      margin: 20px 0;
    }
  </style>


  <script type="text/javascript">
    function Calcular() {

      $.post('index.php', {
        valor: $("#txtValor2017").val()
      }, function() {

      });
    }
  </script>
</head>

<body>
  <div class="centro" style="display: none;">

    <div class="geral">
      <div class="linha">
        <div class="ferramenta">

        </div>
      </div>

      <div class="linha" style="display: none;">
        <div class="coluna1">Árbitro Único</div>
        <div class="coluna2"><input type="text" id="lblArbitroUnico" /></div>
      </div>
      <div class="linha" style="display: none;">
        <div class="coluna1">Presidente</div>
        <div class="coluna2"><input type="text" id="lblPresidente" /></div>
      </div>
      <div class="linha" style="display: none;">
        <div class="coluna1">Co-Árbitros</div>
        <div class="coluna2"><input type="text" id="lblCoArbitros" /></div>
      </div>
      <div class="linha" style="display: none;">
        <div class="coluna1">Total</div>
        <div class="coluna2"><input type="text" id="lblTotal" /></div>
      </div>
      <div class="linha" style="display: none;">
        <div class="coluna1">Despesas Administrativas</div>
        <div class="coluna2"><span id="lblDespesasAdministrativas"></span></div>
      </div>
      <div style="display: none">
        <input type="text" id="lblRequerenteTaxaRegistroX" />
        <input type="text" id="lblRequerenteTaxaAdministracaoX" />
        <input type="text" id="lblRequeridoTaxaAdministracaoX" />
        <input type="text" id="lblRequerenteHonorarioArbitrosX" />
        <input type="text" id="lblRequeridoHonorarioArbitrosX" />
        <input type="text" id="lblRequerenteTotalX" />
        <input type="text" id="lblRequeridoTotalX" />

        <input type="text" id="lblRequerenteTaxaRegistro2X" />
        <input type="text" id="lblRequerenteTaxaAdministracao2X" />
        <input type="text" id="lblRequeridoTaxaAdministracao2X" />
        <input type="text" id="lblRequerenteHonorarioArbitros2X" />
        <input type="text" id="lblRequeridoHonorarioArbitros2X" />
        <input type="text" id="lblRequerenteTotal2X" />
        <input type="text" id="lblRequeridoTotal2X" />

        <input type="text" id="lblCoArbitrosX" />
        <input type="text" id="lblRequerenteX" />
        <input type="text" id="lblRequeridoX" />

        <input type="text" id="lblSegregacao3ArbitrosTaxaAdmX" />
        <input type="text" id="lblSegregacao3ArbitrosHonorariosX" />
        <input type="text" id="lblSegregacao3ArbitrosTotalX" />
        <input type="text" id="lblSegregacao1ArbitroTaxaAdmX" />
        <input type="text" id="lblSegregacao1ArbitroHonorariosX" />
        <input type="text" id="lblSegregacao1ArbitroTotalX" />
      </div>
    </div>
  </div>
  <div>
    <div>

      <div class="">
        <h2>Simulação custo de arbitragem</h2>

        <form action="" method="POST">
          <p>Informe o valor da disputa:
            <input type="text" placeholder="R$ 0,00" id="txtValor2017" name="txtValor2025" class="campo" data-thousands="." data-decimal="," data-prefix="R$ " value="<?php echo $_REQUEST['txtValor2025']; ?>" required /><br>
          </p>
          <p>Honorarios Adicionais:
            <input type="text" placeholder="R$ 0,00" id="txtValor2017" name="txtHonorariosAdicionais" class="campo" data-thousands="." data-decimal="," data-prefix="R$ " value="<?php echo $_REQUEST['txtHonorariosAdicionais']; ?>" required /><br>
          </p>
          <p>Selecione a forma de administração do procedimento:<br><br>
            <label><input type="radio" name="calculator" value="default" required <?= $_REQUEST['calculator'] === 'default' ? 'checked' : '' ?> onchange="this.form.submit();" />&nbsp;Padrão</label>
            <label><input type="radio" name="calculator" value="expedita" required <?= $_REQUEST['calculator'] === 'expedita' ? 'checked' : '' ?> onchange="this.form.submit();" />&nbsp;Expedita</label>
          </p>
          <input type="submit" id="cmdCalcular20173" value="Calcular" class="botao" style="border-radius: 25px; border-color: #90c445; display: block; margin-top: 10px;" />
        </form>
      </div>
    </div> <!-- .et_pb_text -->
    <?php if (!$_POST || $_REQUEST['calculator'] === 'default' || ($_REQUEST['calculator'] === 'expedita' && $valor <= 3000000)): ?>

      <div class="grupo-custas">
        <div class="linhas">
          <h3>Árbitro Único</h3>
          <h4>Custas Principais</h4>
        </div>
        <div class="tabela">
          <div class="linha titulo">
            <div class="coluna">&nbsp;</div>
            <div class="coluna">Requerente(s)</div>
            <div class="coluna">Requerido(s)</div>
          </div>
          <div class="linha">
            <div class="coluna">Taxa de Registro</div>
            <div class="coluna"><span id="lblRequerenteTaxaRegistro2"><?php echo $lblRequerenteTaxaRegistro ? ("R$ " . number_format($lblRequerenteTaxaRegistro, 2, ",", ".")) : '-'; ?></span></div>
            <div class="coluna"><span>-</span></div>
          </div>
          <div class="linha">
            <div class="coluna">Taxa de Administração</div>
            <div class="coluna"><span id="lblRequerenteTaxaAdministracao2"><?php echo $lblRequerenteTaxaAdministracao ? ("R$ " . number_format($lblRequerenteTaxaAdministracao, 2, ",", ".")) : '-'; ?></span></div>
            <div class="coluna"><span id="lblRequeridoTaxaAdministracao2"><?php echo $lblRequerenteTaxaAdministracao ? ("R$ " . number_format($lblRequerenteTaxaAdministracao, 2, ",", ".")) : '-'; ?></span></div>
          </div>
          <div class="linha">
            <div class="coluna">Honorários do Árbitro Único</div>
            <div class="coluna"><span id="lblRequerenteHonorarioArbitros2"><?php echo $lblRequerenteHonorarioArbitros ? ("R$ " . number_format($lblRequerenteHonorarioArbitros, 2, ",", ".")) : '-'; ?></span></div>
            <div class="coluna"><span id="lblRequeridoHonorarioArbitros2"><?php echo $lblRequerenteHonorarioArbitros ? ("R$ " . number_format($lblRequerenteHonorarioArbitros, 2, ",", ".")) : '-'; ?></span></div>
          </div>
          <div class="linha total">
            <div class="coluna ">Total</div>
            <div class="coluna"><span id="lblRequerenteTotal2"><?php echo ("R$ " . number_format(($lblRequerenteHonorarioArbitros + $lblRequerenteTaxaRegistro + $lblRequerenteTaxaAdministracao), 2, ",", ".")); ?></span></div>
            <div class="coluna"><span id="lblRequeridoTotal2"><?php echo ("R$ " . number_format(($lblRequerenteHonorarioArbitros + $lblRequerenteTaxaAdministracao), 2, ",", ".")); ?></span></div>
          </div>
        </div>
        <?php if ($_REQUEST['calculator'] === 'default'):
          include "calc_esclarecimento-unico.php";
        endif; ?>
      </div>
      <!-- .et_pb_text -->
      <div>
        <div class="grupo-custas">
          <div class="linhas">
            <h3>Tribunal Arbitral (3 Árbitros)</h3>
            <h4>Custas Principais</h4>
          </div>
          <div class="tabela">
            <div class="linha titulo">
              <div class="coluna">&nbsp;</div>
              <div class="coluna">Requerente(s)</div>
              <div class="coluna">Requerido(s)</div>
            </div>

            <div class="linha linha">
              <div class="coluna">Taxa de Registro</div>
              <div class="coluna"><span id="lblRequerenteTaxaRegistro"><?php echo $lblRequerenteTaxaRegistro ? ("R$ " . number_format($lblRequerenteTaxaRegistro, 2, ",", ".")) : '-'; ?></span></div>
              <div class="coluna"><span>-</span></div>
            </div>
            <div class="linha">
              <div class="coluna">Taxa de Administração</div>
              <div class="coluna"><span id="lblRequerenteTaxaAdministracao"><?php echo $lblRequerenteTaxaAdministracao ? ("R$ " . number_format($lblRequerenteTaxaAdministracao, 2, ",", ".")) : '-'; ?> </span></div>
              <div class="coluna"><span id="lblRequeridoTaxaAdministracao"><?php echo $lblRequerenteTaxaAdministracao ? ("R$ " . number_format($lblRequerenteTaxaAdministracao, 2, ",", ".")) : '-'; ?></span></div>
            </div>
            <div class="linha">
              <?php if (!$_POST || $_REQUEST['calculator'] === 'default'): ?>
                <div class="coluna">Honorários do Tribunal Arbitral
                  <a href="#" onclick="window.open('https://ccbc.org.br/Conteudo/Calcular2025/composicao/?txtValor2025=<?php echo $_REQUEST['txtValor2025'] ?>', 'Calculadora 2022', 'STATUS=NO, TOOLBAR=NO, LOCATION=NO, DIRECTORIES=NO, RESISABLE=NO, SCROLLBARS=YES, TOP=10, LEFT=10, WIDTH=504, HEIGHT=270'); return false;" style="font-size: 12px; font-family: arial; text-decoration: none;"> 
                    (Composição) 
                  </a>
                </div>
              <?php endif; ?>
              <?php if ($_REQUEST['calculator'] === 'expedita'): ?>
                <div class="coluna">Honorários do Tribunal Arbitral
                  <a href="#" onclick="window.open('https://ccbc.org.br/Conteudo/Calcular2025/composicaoExpedita/?txtValor2025=<?php echo $_REQUEST['txtValor2025'] ?>', 'Calculadora 2021', 'STATUS=NO, TOOLBAR=NO, LOCATION=NO, DIRECTORIES=NO, RESISABLE=NO, SCROLLBARS=YES, TOP=10, LEFT=10, WIDTH=504, HEIGHT=270'); return false;" style="font-size: 12px; font-family: arial; text-decoration: none;">
                    (Composição) 
                  </a>
                </div>
              <?php endif; ?>
              <div class="coluna"><span id="lblRequerenteHonorarioArbitros"><?php echo $lblRequerenteHonorarioArbitros3 ? ("R$ " . number_format($lblRequerenteHonorarioArbitros3, 2, ",", ".")) : '-'; ?></span></div>
              <div class="coluna"><span id="lblRequeridoHonorarioArbitros"><?php echo $lblRequerenteHonorarioArbitros3 ? ("R$ " . number_format($lblRequerenteHonorarioArbitros3, 2, ",", ".")) : '-'; ?></span></div>
            </div>
            <div class="linha total">
              <div class="coluna">Total</div>
              <div class="coluna"><span id="lblRequerenteTotal"><?php echo ("R$ " . number_format(($lblRequerenteHonorarioArbitros3 + $lblRequerenteTaxaRegistro + $lblRequerenteTaxaAdministracao), 2, ",", ".")); ?></span></div>
              <div class="coluna"><span id="lblRequeridoTotal"><?php echo ("R$ " . number_format(($lblRequerenteHonorarioArbitros3 + $lblRequerenteTaxaAdministracao), 2, ",", ".")); ?></span></div>
            </div>
          </div>
          <?php if ($_REQUEST['calculator'] === 'default'):
            // include "calc_esclarecimento-tribunal.php";
          endif; ?>
        </div>
      </div> <!-- .et_pb_text -->
  </div>
<?php else: ?>
  <p>A arbitragem expedita é elegível para valor em disputa inferior a R$ 3.000.000,00. Ver parágrafo 52 do Regramento de Custas</p>
<?php endif; ?>
<br />
<?php if ($_REQUEST['calculator'] === 'default'): ?>
  <a href="#" onclick="window.open('https://ccbc.org.br/cam-ccbc-centro-arbitragem-mediacao/resolucao-de-disputas/arbitragem/tabela-despesas-calculadora-2022/calculadora-de-segregacao-2022/', 'Calculadora de Segregação 2022', 'STATUS=NO, TOOLBAR=NO, LOCATION=NO, DIRECTORIES=NO, RESISABLE=NO, SCROLLBARS=YES, TOP=10, LEFT=10, WIDTH=1110, HEIGHT=642'); return false;" class="botao-calculadora">Faça o cálculo comparativo de Segregação aqui</a>
  <br />
<?php endif; ?>

</body>

</html>