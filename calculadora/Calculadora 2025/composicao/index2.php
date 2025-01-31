<?php

$id = time();
$valor = str_replace("R$ ", "",  $_REQUEST['txtValor2022']);
$valor = str_replace(".", "",  $valor);
$valor = str_replace(",", ".",  $valor);

$lblRequerenteTaxaRegistro = 4000;
$lblRequerenteTaxaAdministracao = 0;
$lblRequerenteHonorarioArbitros = 0;

$mapAdm = [
  ['max' => 4000000, 'base' => 131994.46, 'tax' => null],
  ['max' => 10000000, 'base' => 131994.46, 'tax' => 0.002639889],
  ['max' => 18000000, 'base' => 147833.80, 'tax' => 0.002375900],
  ['max' => 50000000, 'base' => 166841.00, 'tax' => 0.002111911],
  ['max' => 100000000, 'base' => 234422.16, 'tax' => 0.001847922],
  ['max' => 150000000, 'base' => 326818.28, 'tax' => 0.001319945],
  ['max' => 300000000, 'base' => 392815.51, 'tax' => 0.000131994],
  ['max' => 500000000, 'base' => 412614.68, 'tax' => 0.000065997],
  ['max' => 1000000000, 'base' => 425814.13, 'tax' => 0.000032999],
  ['max' => null, 'base' => 442313.44, 'tax' => 0.000016499]
];

////////////Taxa de Administração por Parte
foreach ($mapAdm as $idx => $item) {
  if ($item['max'] === null || $valor <= $item['max']) {
    $lblRequerenteTaxaAdministracao = $item['base'] + (($valor - ($mapAdm[$idx - 1]['max'] ?? 0)) * ($item['tax'] ?: 0));
    break;
  }
}

$mapArb = [
  ['max' => 2000000, 'base' => 296987.54, 'tax' => null],
  ['max' => 4000000, 'base' => 296987.54, 'tax' => 0.04949792268750],
  ['max' => 10000000, 'base' => 395983.38, 'tax' => 0.03563850433500],
  ['max' => 18000000, 'base' => 609814.41, 'tax' => 0.01187950144500],
  ['max' => 50000000, 'base' => 704850.42, 'tax' => 0.00395983381500],
  ['max' => 100000000, 'base' => 831565.10, 'tax' => 0.00356385043350],
  ['max' => 150000000, 'base' => 1009757.62, 'tax' => 0.00237590028900],
  ['max' => 300000000, 'base' => 1128552.64, 'tax' => 0.00158393352600],
  ['max' => 500000000, 'base' => 1366142.67, 'tax' => 0.00118795014450],
  ['max' => 1000000000, 'base' => 1603732.70, 'tax' => 0.00098995845375],
  ['max' => null, 'base' => 2098711.92, 'tax' => 0.00079196676300]
];

////////III. Honorários dos Árbitros
foreach ($mapArb as $idx => $item) {
  if ($item['max'] === null || $valor <= $item['max']) {
    $lblRequerenteHonorarioArbitros = $item['base'] + (($valor - ($mapArb[$idx - 1]['max'] ?? 0)) * ($item['tax'] ?: 0));
    break;
  }
}

$lblRequerenteTaxaAdministracao = floor($lblRequerenteTaxaAdministracao);
$lblRequerenteTaxaAdministracao /= 2;
$lblRequerenteHonorarioArbitros3 = $lblRequerenteHonorarioArbitros / 2;
$lblPresidente = $lblRequerenteHonorarioArbitros * 0.4;
$lblCoArbitro = $lblRequerenteHonorarioArbitros * 0.3;
$lblRequerenteHonorarioArbitros /= 2;

$textos = [
  'en' => [
    'Arbitral Tribunal Fees',
    'President',
    'Co-Arbitrator',
    'Claimant(s)',
    'Respondent(s)'
  ],
  'es' => [
    'Tasa de Tribunal Arbitral',
    'Presidente',
    'Co-Árbitro',
    'Requerente(s)',
    'Requerido(s)'
  ],
  'br' => [
    'Honorários do Tribunal Arbitral',
    'Presidente',
    'Co-Árbitro',
    'Requerente(s)',
    'Requerido(s)'
  ]
]; {

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


    <script src="../Scripts/jCalculadora.js?v=_2_O2ISiGBe5X6-5gdMmonh8tFKYZaYX5PYQUMoNxH41"></script>


    <meta name="viewport" content="width=device-width" />
    <title>Calculadora 2017 - Composição</title>

    <style>
      .centro {
        left: 50%;
        margin-left: -240px;
        /* A metade de sua largura. */
        position: absolute;
        width: 480px;
        /* O valor que você desejar. */
        top: 50%;
        transform: translateY(-50%);
      }

      .geral {
        float: left;
        width: 220px;
        border: 1px solid silver;
        border-radius: 6px;
        padding-left: 8px;
        padding-bottom: 11px;
        margin-left: 126px;
      }

      .linha {
        float: left;
        margin-bottom: 8px;
      }

      .ferramenta {
        float: left;
        margin: 2px;
        margin-bottom: 20px;
        font-family: arial;
        font-size: 13px;
        color: #90C445 ;
        font-weight: bold;
      }

      .ferramenta .campo {
        float: left;
        width: 130px;
        text-align: right;
        border: 1px solid gray;
      }

      .ferramenta .botao {
        float: right;
        margin-left: 4px;
      }

      .coluna1 {
        float: left;
        width: 200px;
        background-color: #90C445;
        margin: 2px;
        color: white;
        font-family: arial;
        font-size: 13px;
        padding: 2px 2px 2px 6px;
        border-radius: 5px;
      }

      .coluna2 {
        float: left;
        width: 200px;
        margin: 2px;
        border-bottom: 1px dotted #015291;
        border-right: 1px dotted;
        padding-right: 7px;
      }

      .coluna2 span {
        float: left;
        width: 100%;
        text-align: right;
        font-family: arial;
        font-size: 13px;
        min-height: 15px;
      }
    </style>
  </head>

  <body>
    <div>


      <div style="float: left; width: 370px; font-family: arial; font-size: 12px;">

        <div style="float: left; width: 366px; border: 1px solid #90C445; border-radius: 6px; margin-top: 10px;">
          <div style="width: 360px; text-align: left; float: left; border-bottom: 1px solid gray; padding: 4px 0 4px 6px; background-color: #90C445; color: white; border-top-left-radius: 6px; border-top-right-radius:6px;"><?php echo $textos[$_REQUEST['lang'] ?? 'br'][0] ?></div>
          <div style="float: left">
            <div style="text-align: center; float: left;">
              <div style="width: 366px; text-align: center; float: left; background-color: #F2F2F2 ; border-bottom: 1px dotted gray; padding: 2px 0; height: 16px; "><?php echo $textos[$_REQUEST['lang'] ?? 'br'][1] ?></div>
            </div>

            <div style="text-align: center; float: left; padding: 2px 0; border-bottom: 1px dotted gray; margin-bottom: 5px;">
              <div style="width: 366px; text-align: center; float: left;"><span id="lblSegregacao3ArbitrosTaxaAdm"></span><?php echo "R$ " . number_format(($lblPresidente), 2, ",", "."); ?></div>
            </div>
            <br>


            <div style="text-align: center; float: left;">
              <div style="width: 253px; text-align: left; float: left; background-color: #F2F2F2 ; border-bottom: 1px dotted gray; padding: 2px 0; height: 16px; "><?php echo $textos[$_REQUEST['lang'] ?? 'br'][2] ?></div>
              <div style="width: 113px; text-align: left; float: left; background-color: #F2F2F2 ; border-bottom: 1px dotted gray; padding: 2px 0; height: 16px; color:#015291; ">Co-Árbitro<span></span></div>
            </div>

            <div style="text-align: center; float: left; padding: 2px 0; border-bottom: 1px dotted gray; margin-bottom: 5px;">
              <div style="width: 247px; text-align: left; float: left; padding-left: 6px;"><?php echo "R$ " . number_format(($lblCoArbitro), 2, ",", "."); ?></div>
              <div style="width: 113px; text-align: left; float: left;"><span id="lblSegregacao3ArbitrosTaxaAdm"></span><?php echo "R$ " . number_format(($lblCoArbitro), 2, ",", "."); ?></div>
            </div>

            <div style="text-align: center; float: left; padding: 2px 0; border-bottom: 1px dotted gray; margin-bottom: 5px; ">
              <div style="width: 366px; text-align: center; float: left; padding-left: 6px;">Total</div>
              <div style="width: 366px; text-align: center; float: left;"><span id="lblSegregacao3ArbitrosTaxaAdm"></span><?php echo "R$ " . number_format(($lblPresidente + ($lblCoArbitro * 2)), 2, ",", "."); ?></div>
            </div>

            <div style="text-align: center; float: left;">
              <div style="width: 253px; text-align: left; float: left; background-color: #F2F2F2 ; border-bottom: 1px dotted gray; padding: 2px 0; height: 16px; "><?php echo $textos[$_REQUEST['lang'] ?? 'br'][3] ?></div>
              <div style="width: 113px; text-align: left; float: left; background-color: #F2F2F2 ; border-bottom: 1px dotted gray; padding: 2px 0; height: 16px; color:#015291; "><?php echo $textos[$_REQUEST['lang'] ?? 'br'][4] ?><span></span></div>
            </div>

            <div style="text-align: center; float: left; padding: 2px 0; border-bottom: 1px dotted gray; ">
              <div style="width: 247px; text-align: left; float: left; padding-left: 6px;"><?php echo "R$ " . number_format((($lblPresidente + ($lblCoArbitro * 2)) / 2), 2, ",", "."); ?></div>
              <div style="width: 113px; text-align: left; float: left;"><span id="lblSegregacao3ArbitrosTaxaAdm"></span><?php echo "R$ " . number_format((($lblPresidente + ($lblCoArbitro * 2)) / 2), 2, ",", "."); ?></div>
            </div>


          </div>
        </div>

      </div>


    </div>




  </body>

  </html>

<?php



}

?>
