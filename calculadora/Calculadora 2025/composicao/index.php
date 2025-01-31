<?php

$id = time();
$valor = str_replace("R$ ", "",  $_REQUEST['txtValor2025']);
$valor = str_replace(".", "",  $valor);
$valor = str_replace(",", ".",  $valor);

$lblRequerenteTaxaRegistro = 5500;
$lblRequerenteTaxaAdministracao = 0;
$lblRequerenteHonorarioArbitros = 0;

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

////////////Taxa de Administração por Parte
foreach ($mapAdm as $idx => $item) {
  if ($item['max'] === null || $valor <= $item['max']) {
    $lblRequerenteTaxaAdministracao = $item['base'] + (($valor - ($mapAdm[$idx - 1]['max'] ?? 0)) * ($item['tax'] ?: 0));
    break;
  }
}

$mapArb = [
    ['max' => 2000000, 'base' => 326112.07, 'tax' => null],
    ['max' => 4000000, 'base' => 434816.09, 'tax' => 0.0543521072273],
    ['max' => 10000000, 'base' => 669616.77, 'tax' => 0.03913434772037],
    ['max' => 18000000, 'base' => 773972.64, 'tax' => 0.01304448257482],
    ['max' => 50000000, 'base' => 913113.78, 'tax' => 0.0043416085782],
    ['max' => 100000000, 'base' => 1108781.02, 'tax' => 0.0036583340339],
    ['max' => 150000000, 'base' => 1239225.84, 'tax' => 0.00260889651469],
    ['max' => 300000000, 'base' => 1500115.50, 'tax' => 0.00130444825735],
    ['max' => 500000000, 'base' => 1761005.15, 'tax' => 0.00108704421445],
    ['max' => 1000000000, 'base' => 2304525.26, 'tax' => 0.0008693217156],
    ['max' => null, 'base' => 2304525.25, 'tax' => 0.0008693217156]
];

////////III. Honorários dos Árbitros
foreach ($mapArb as $idx => $item) {
  if ($item['max'] === null || $valor <= $item['max']) {
    var_dump('valor:'. $valor . ' max:'. $item['max'] . ' base:'. $item['base'] . ' tax:'. $item['tax']);
    $lblRequerenteHonorarioArbitros = $item['base'];
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
    <title>Calculadora 2025 - Composição</title>

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
