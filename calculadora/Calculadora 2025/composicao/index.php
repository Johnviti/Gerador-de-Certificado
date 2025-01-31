<?php

$id = time();
$valor = str_replace("R$ ", "",  $_REQUEST['txtValor2021']);
$valor = str_replace(".", "",  $valor);
$valor = str_replace(",", ".",  $valor);

$lblRequerenteTaxaRegistro = 4000;
$lblRequerenteTaxaAdministracao = 0;
$lblRequerenteHonorarioArbitros = 0;

////////////Taxa de Administração por Parte
if ($valor > 0 and $valor < 1000000) {
    $lblRequerenteTaxaAdministracao = 30000;
} else if ($valor >= 1000000 and $valor <= 2000000) {
    $lblRequerenteTaxaAdministracao = 30000 + (($valor - 1000000) * 0.009);
} else if ($valor >= 2000000 and $valor <= 3000000) {
    $lblRequerenteTaxaAdministracao = 39000 + (($valor - 2000000) * 0.009);
}

////////III. Honorários dos Árbitros
if ($valor > 0 and $valor < 1000000) {
    $lblRequerenteHonorarioArbitros = 45000;
} else if ($valor >= 1000000 and $valor <= 2000000) {
    $lblRequerenteHonorarioArbitros = 45000 + (($valor - 1000000) * 0.014);
} else if ($valor >= 2000000 and $valor <= 3000000) {
    $lblRequerenteHonorarioArbitros = 59000 + (($valor - 2000000) * 0.014);
}

$lblRequerenteTaxaAdministracao /= 2;
$lblRequerenteHonorarioArbitros3 = ($lblRequerenteHonorarioArbitros + ($lblRequerenteHonorarioArbitros * 2 * 0.83333)) / 2;
$lblPresidente = $lblRequerenteHonorarioArbitros;
$lblCoArbitro = $lblRequerenteHonorarioArbitros * 0.83333;
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
                color: #015291;
                font-weight: bold;
            }

            .ferramenta .campo {
                float: left;
                width: 130px;
                text-align: right;
                border: 1px solid #90C445;
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
                border-bottom: 1px dotted #90C445;
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
                    <div style="width: 360px; text-align: left; float: left; border-bottom: 1px solid #90C445; padding: 4px 0 4px 6px; background-color: #90C445; color: white; border-top-left-radius: 6px; border-top-right-radius:6px;"><?php echo $textos[$_REQUEST['lang'] ?? 'br'][0] ?></div>
                    <div style="float: left">
                        <div style="text-align: center; float: left;">
                            <div style="width: 366px; text-align: center; float: left; background-color: #F2F2F2; border-bottom: 1px dotted #90C445; padding: 2px 0; height: 16px; "><?php echo $textos[$_REQUEST['lang'] ?? 'br'][1] ?></div>
                        </div>
                        <div style="text-align: center; float: left; padding: 2px 0; border-bottom: 1px dotted #90C445; margin-bottom: 5px;">
                            <div style="width: 366px; text-align: center; float: left;"><span id="lblSegregacao3ArbitrosTaxaAdm"></span><?php echo "R$ " . number_format(($lblPresidente), 2, ",", "."); ?></div>
                        </div>
                        <br>
                        <div style="text-align: center; float: left;">
                            <div style="width: 253px; text-align: left; float: left; background-color: #F2F2F2; border-bottom: 1px dotted #90C445; padding: 2px 0; height: 16px; "><?php echo $textos[$_REQUEST['lang'] ?? 'br'][2] ?></div>
                            <div style="width: 113px; text-align: left; float: left; background-color: #F2F2F2; border-bottom: 1px dotted #90C445; padding: 2px 0; height: 16px; color:#015291; ">Co-Árbitro<span></span></div>
                        </div>
                        <div style="text-align: center; float: left; padding: 2px 0; border-bottom: 1px dotted #90C445; margin-bottom: 5px;">
                            <div style="width: 247px; text-align: left; float: left; padding-left: 6px;"><?php echo "R$ " . number_format(($lblCoArbitro), 2, ",", "."); ?></div>
                            <div style="width: 113px; text-align: left; float: left;"><span id="lblSegregacao3ArbitrosTaxaAdm"></span><?php echo "R$ " . number_format(($lblCoArbitro), 2, ",", "."); ?></div>
                        </div>
                        <div style="text-align: center; float: left; padding: 2px 0; border-bottom: 1px dotted #90C445; margin-bottom: 5px; ">
                            <div style="width: 366px; text-align: center; float: left; padding-left: 6px;">Total</div>
                            <div style="width: 366px; text-align: center; float: left;"><span id="lblSegregacao3ArbitrosTaxaAdm"></span><?php echo "R$ " . number_format(($lblPresidente + ($lblCoArbitro * 2)), 2, ",", "."); ?></div>
                        </div>
                        <div style="text-align: center; float: left;">
                            <div style="width: 253px; text-align: left; float: left; background-color: #F2F2F2; border-bottom: 1px dotted #90C445; padding: 2px 0; height: 16px; "><?php echo $textos[$_REQUEST['lang'] ?? 'br'][3] ?></div>
                            <div style="width: 113px; text-align: left; float: left; background-color: #F2F2F2; border-bottom: 1px dotted #90C445; padding: 2px 0; height: 16px; color:#015291; "><?php echo $textos[$_REQUEST['lang'] ?? 'br'][4] ?><span></span></div>
                        </div>
                        <div style="text-align: center; float: left; padding: 2px 0; border-bottom: 1px dotted #90C445; ">
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