<?php
if ($_POST) {
  $id = time();
  $valor = str_replace("R$ ", "",  $_REQUEST['txtValor2025']);
  $valor = str_replace(".", "",  $valor);
  $valor = str_replace(",", ".",  $valor);

  $lblRequerenteTaxaTotal = 0;
  $lblRequerenteHonorarioArbitros = 0;

  $mapAdm = [
    ['max' => 10000000, 'base' => 27500],
    ['max' => 50000000, 'base' => 38400],
    ['max' => 150000000, 'base' => 49400],
    ['max' => 500000000, 'base' => 60400],
    ['max' => null, 'base' => 71400]
  ];

  ////////////Taxa de Arbitro Único
  foreach ($mapAdm as $idx => $item) {
    if ($item['max'] === null || $valor <= $item['max']) {
      $lblRequerenteTaxaTotal = $item['base'];
      break;
    }
  }

  $lblPresidenteHonorarioArbitros3 = ($lblRequerenteTaxaTotal * 0.4);
  $lblCoAbitroHonorarioArbitros3 = ($lblRequerenteTaxaTotal * 0.3);
} {
?>
	<div class="linhas">
		<h4>Custas Incidentais</h4>
	</div>
    <div class="tabela">
      <div class="linha titulo">
		<div class="coluna100">Honorários adicionais para esclarecimentos à sentença</div>
	  </div>
	  <div class="linha">
          <div class="coluna50">Presidente</div>
          <div class="coluna50"><span id="lblSegregacao1ArbitroTaxaAdm"></span><?php echo "R$ " . number_format($lblPresidenteHonorarioArbitros3, 2, ",", "."); ?></div>
      </div>
	  <div class="linha">
          <div class="coluna50"><?php echo "Co-Árbitro R$ " . number_format($lblCoAbitroHonorarioArbitros3, 2, ",", "."); ?></div>
          <div class="coluna50"><span id="lblSegregacao1ArbitroTaxaAdm"></span><?php echo "Co-Árbitro R$ " . number_format($lblCoAbitroHonorarioArbitros3, 2, ",", "."); ?> </div>
      </div>
      <div class="linha total">
          <div class="coluna50">Total</div>
          <div class="coluna50"><span id="lblSegregacao1ArbitroTaxaAdm"></span><?php echo "R$ " . number_format($lblRequerenteTaxaTotal, 2, ",", "."); ?> </div>
      </div>
    </div>
	&nbsp;
	<div class="tabela">
      <div class="linha titulo">
		<div class="coluna100">Comitê Especial (por árbitros impugnados)</div>
	  </div>
	  <div class="linha">
          <div class="coluna50">Presidente</div>
          <div class="coluna50"><span id="lblSegregacao1ArbitroTaxaAdm"></span><?php echo "R$ " . number_format($mapAdm[0]['base']* 0.4, 2, ",", "."); ?></div>
      </div>
	  <div class="linha">
          <div class="coluna50"><?php echo "Membro do Comitê R$ " . number_format($mapAdm[0]['base']* 0.3, 2, ",", "."); ?></div>
          <div class="coluna50"><span id="lblSegregacao1ArbitroTaxaAdm"></span><?php echo "Membro do Comitê R$ " . number_format($mapAdm[0]['base']* 0.3, 2, ",", "."); ?></div>
      </div>
      <div class="linha total">
          <div class="coluna50">Total</div>
          <div class="coluna50"><span id="lblSegregacao1ArbitroTaxaAdm"></span><?php echo "R$ " . number_format($mapAdm[0]['base'], 2, ",", "."); ?> </div>
      </div>
    </div>

<?php
}
?>
