<?php
if ($_POST) {
  $id = time();
  $valor = str_replace("R$ ", "",  $_REQUEST['txtValor2022']);
  $valor = str_replace(".", "",  $valor);
  $valor = str_replace(",", ".",  $valor);

  $lblRequerenteTaxaTotal = 0;
  $lblRequerenteHonorarioArbitros = 0;

  $mapAdm = [
    ['max' => 10000000, 'base' => 25000],
    ['max' => 50000000, 'base' => 35000],
    ['max' => 150000000, 'base' => 45000],
    ['max' => 500000000, 'base' => 55000],
    ['max' => null, 'base' => 65000]
  ];

  ////////////Taxa de Arbitro Único
  foreach ($mapAdm as $idx => $item) {
    if ($item['max'] === null || $valor <= $item['max']) {
      $lblRequerenteTaxaTotal = $item['base'];
      break;
    }
  }
} {
?>


    
	<div class="linhas">
		<h4>Custas Incidentais</h4>
	</div>
    <div class="tabela">
      <div class="linha titulo">
		<div class="coluna100">Honorários adicionais para esclarecimentos à sentença</div>
	  </div>
      <div class="linha total">
          <div class="coluna50">Total</div>
          <div class="coluna50"><span id="lblSegregacao1ArbitroTaxaAdm"></span><?php echo "R$ " . number_format($lblRequerenteTaxaTotal, 2, ",", "."); ?> </div>
      </div>
    </div>
	&nbsp;
	<div class="tabela">
      <div class="linha titulo">
		<div class="coluna100">Comitê Especial (por árbitro impugnado)</div>
	  </div>
      <div class="linha total">
          <div class="coluna50">Total</div>
          <div class="coluna50"><span id="lblSegregacao1ArbitroTaxaAdm"></span><?php echo "R$ " . number_format($mapAdm[0]['base'], 2, ",", "."); ?> </div>
      </div>
    </div>
<?php
}
?>
