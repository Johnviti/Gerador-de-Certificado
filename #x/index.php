<?php

add_action('wp_enqueue_scripts', 'my_theme_enqueue_styles');
function my_theme_enqueue_styles()
{
	wp_enqueue_style(
		'child-style',
		get_stylesheet_uri(),
		array('parenthandle'),
		wp_get_theme()->get('Version') // This only works if you have Version defined in the style header.
	);
}

add_shortcode('shortcode_cupom', 'funcao_cupom');

function cupom_redirecionamento() {
	$erro_cupom = '';
    $cupons = array(
        "eslen" => "https://3xaction.com/promocao/",
        "vinha" => "https://3xaction.com/flavia-palheiros/",
        "rodrigopia" => "https://3xaction.com/rodrigopia/",
        "gabioliveira" => "https://3xaction.com/gabi-oliveira/",
        "fermento" => "https://3xaction.com/sem-groselha/",
        "raquel" => "https://3xaction.com/raquel-castanharo/",
        "anafarias" => "https://3xaction.com/ana-farias/",
        "rafa" => "https://3xaction.com/nadiara/",
        "mmovimento" => "https://3xaction.com/mmovimento/",
        "andressa" => "https://3xaction.com/andressa/",
        "fabiana" => "https://3xaction.com/fabiana/",
        "dratamires" => "https://3xaction.com/dratamires/",
        "cesarmahamudra" => "https://3xaction.com/gabriela/",
        "runnersbrasil" => "https://3xaction.com/laercio/",
        "bibi" => "https://3xaction.com/bianca/",
        "giovana" => "https://3xaction.com/giovana-damazio/",
        "flamino" => "https://3xaction.com/flamino/",
        "mend" => "https://3xaction.com/luisa-cormellato/",
        "cavallaro" => "https://3xaction.com/gabriela-cavallaro/",
        "laura" => "https://3xaction.com/laura/",
        "spaceorbit" => "https://3xaction.com/spaceorbit/",
        "doslivros" => "https://3xaction.com/doslivros/",
		"testing" => "https://3xaction.com/testing-promocao/"
    );

    // Verifica se o cupom foi enviado via POST
    if (isset($_POST['cupom'])) {
        $cupom = strtolower(sanitize_text_field($_POST['cupom']));

        // Verifica se o cupom existe na lista
        if (array_key_exists($cupom, $cupons)) {
            // Define um cookie para marcar o cupom como válido
            setcookie('cupom_aprovado', $cupom, time() + 86400, '/'); // O cookie expira em 1 hora

            // Redireciona para o URL correspondente
            wp_redirect($cupons[$cupom]);
            exit();
        } else {
            echo '<div class="erro-msg"><p>Cupom inválido. Tente novamente.</p></div>';
        }
 		
    }
}
add_action('template_redirect', 'cupom_redirecionamento');

function bloquear_paginas_protegidas() {
    $paginas_protegidas = array(
        "eslen" => "https://3xaction.com/promocao/",
        "vinha" => "https://3xaction.com/flavia-palheiros/",
        "rodrigopia" => "https://3xaction.com/rodrigopia/",
        "gabioliveira" => "https://3xaction.com/gabi-oliveira/",
        "fermento" => "https://3xaction.com/sem-groselha/",
        "raquel" => "https://3xaction.com/raquel-castanharo/",
        "anafarias" => "https://3xaction.com/ana-farias/",
        "rafa" => "https://3xaction.com/nadiara/",
        "mmovimento" => "https://3xaction.com/mmovimento/",
        "andressa" => "https://3xaction.com/andressa/",
        "fabiana" => "https://3xaction.com/fabiana/",
        "dratamires" => "https://3xaction.com/dratamires/",
        "cesarmahamudra" => "https://3xaction.com/gabriela/",
        "runnersbrasil" => "https://3xaction.com/laercio/",
        "bibi" => "https://3xaction.com/bianca/",
        "giovana" => "https://3xaction.com/giovana-damazio/",
        "flamino" => "https://3xaction.com/flamino/",
        "mend" => "https://3xaction.com/luisa-cormellato/",
        "cavallaro" => "https://3xaction.com/gabriela-cavallaro/",
        "laura" => "https://3xaction.com/laura/",
        "spaceorbit" => "https://3xaction.com/spaceorbit/",
        "doslivros" => "https://3xaction.com/doslivros/",
        "testing" => "https://3xaction.com/testing-promocao/"
    );

    // Verifique se a URL da página atual está na lista de páginas protegidas
    $current_url = get_permalink();
    
    // Verifica se a URL da página está na lista
    if (in_array($current_url, $paginas_protegidas)) {
        $cupom_atual = array_search($current_url, $paginas_protegidas); // Pegue o cupom que corresponde à URL
        
        // Verifique se o cupom aprovado está no cookie e corresponde ao cupom da página
        if (!isset($_COOKIE['cupom_aprovado']) || $_COOKIE['cupom_aprovado'] != $cupom_atual) {
            // Redireciona se o cookie não for válido
            wp_redirect(home_url());
            exit();
        }
    }
}
add_action('template_redirect', 'bloquear_paginas_protegidas');




// Fim 


function obter_dados_mercado_pago() {
	
		$logger = wc_get_logger();

        // Endpoint para obter o token
		$url1 = 'https://api.mercadolibre.com/oauth/token';

		// Configurar os parâmetros do corpo para obter o token
		$bodyParams = [
			'grant_type'    => 'client_credentials',
			'client_id'     => '1189337608555854',
			'client_secret' => 'E3TRaAGhk4LaYjqoSLjLiMqXou4TbSxu'
		];

		// Requisição para obter o token de acesso
		$response = wp_remote_post($url1, [
			'body' => $bodyParams,
			'headers' => [
				'Content-Type' => 'application/x-www-form-urlencoded'
			]
		]);

		if (is_wp_error($response)) {
			error_log('Erro na requisição do token: ' . $response->get_error_message());
			return 'Erro ao obter token';
		}

		$responseDecoded = json_decode(wp_remote_retrieve_body($response), true);

		if (!isset($responseDecoded['access_token'])) {
			error_log('Falha ao obter token de acesso');
			return 'Erro ao obter token de acesso';
		}

		$access_token = $responseDecoded['access_token'];
	
		$logger->info('Token: ' . print_r($access_token, true), ['source' => 'mercadolivre']);


		// 1. Obter o seller_id
		$userResponse = wp_remote_get('https://api.mercadolibre.com/users/me', [
			'headers' => [
				'Authorization' => 'Bearer ' . $access_token
			]
		]);

		if (is_wp_error($userResponse)) {
			error_log('Erro ao obter seller_id: ' . $userResponse->get_error_message());
			return 'Erro ao obter seller_id';
		}

		$userData = json_decode(wp_remote_retrieve_body($userResponse), true);
		$seller_id = $userData['id'] ?? null;
	
		$logger->info('Orders: ' . print_r($seller_id, true), ['source' => 'mercadolivre']);


		if (!$seller_id) {
			error_log('Seller ID não encontrado');
			return 'Seller ID não encontrado';
		}
	
		// Adicionar os filtros de data, se fornecidos
		$start_date = $_GET['start_date'] ?? null;
		$end_date = $_GET['end_date'] ?? null;
		$data_cupom = "start_date=2024-12-01&end_date=2025-01-10";

		// Verifique se as variáveis $start_date e $end_date estão definidas antes de usá-las
		$date_filters = '';
		if (!empty($start_date) && !empty($end_date)) {
			$start_date_formatted = date('Y-m-d\TH:i:s.000\Z', strtotime($start_date . ' 00:00:00'));
			$end_date_formatted = date('Y-m-d\TH:i:s.999\Z', strtotime($end_date . ' 23:59:59'));
			$date_filters = "&order.date_created.from=$start_date_formatted&order.date_created.to=$end_date_formatted";
			$start_date_cupom = date('Y-m-d', strtotime($start_date));
			$end_date_cupom = date('Y-m-d', strtotime($end_date));
			$data_cupom = "start_date=$start_date_cupom&end_date=$end_date_cupom";
		}

		$urlCupom = "https://api.mercadolibre.com/seller-promotions/users/417395953/coupons?$data_cupom&channel=mshops&app_version=v2";

		$cupomResponse = wp_remote_get($urlCupom, [
			'headers' => [
				'Authorization' => 'Bearer ' . $access_token
			],
			'timeout' => 20 // Aumenta o tempo limite para 20 segundos
		]);

		if (is_wp_error($cupomResponse)) {
			$logger->info('Erro ao buscar cupons: ' .  print_r($cupomResponse->get_error_message(), true), ['source' => 'mercadolivre']);
			return 'Erro ao buscar cupons';
		}

		$orderDataCumpom = json_decode(wp_remote_retrieve_body($cupomResponse), true);

		$logger->info('CUPOM: ' . print_r($orderDataCumpom, true), ['source' => 'mercadolivre']);
		$logger->info('URL CUPOM: ' . print_r($urlCupom, true), ['source' => 'mercadolivre']);
	
		// Agrupar os cupons por coupon_code, somando apenas os used_coupon quando os ids forem diferentes
		$cuponsAgrupados = [];

		// Agrupar os cupons
		foreach ($orderDataCumpom['coupons'] as $coupon) {
			$couponCode = $coupon['coupon_code'];
			$couponId = $coupon['id'];

			// Verifica se o coupon_code já foi agrupado
			if (!isset($cuponsAgrupados[$couponCode])) {
				$cuponsAgrupados[$couponCode] = [
					'coupon_code' => $coupon['coupon_code'],
					'campaign_name' => $coupon['campaign_name'],
					'used_coupon' => $coupon['used_coupon'],
					'status' => $coupon['status'],
					'start_date' => $coupon['start_date'],
					'end_date' => $coupon['end_date'],
					'total_sales' => $coupon['total_sales'],
					'total_discount' => $coupon['total_discount'],
					'ids' => [$couponId] // Armazenando os IDs já processados
				];
			} else {
				// Verifica se o ID do cupom já foi agrupado
				if (!in_array($couponId, $cuponsAgrupados[$couponCode]['ids'])) {
					// Se o ID não foi agrupado, soma o used_coupon e adiciona o ID
					$cuponsAgrupados[$couponCode]['used_coupon'] += $coupon['used_coupon'];
					$cuponsAgrupados[$couponCode]['ids'][] = $couponId; // Armazenando o ID do cupom
				}
			}
		}

		// Buscar ordens com os filtros
		$urlOrders = "https://api.mercadolibre.com/orders/search?seller=$seller_id&tags=mshops&order.status=paid&sort=date_asc$date_filters";
		
		
		$logger->info('URL: ' . print_r($urlOrders, true), ['source' => 'mercadolivre']);

		$orderResponse = wp_remote_get($urlOrders, [
			'headers' => [
				'Authorization' => 'Bearer ' . $access_token
			]
		]);

		if (is_wp_error($orderResponse)) {
			error_log('Erro ao buscar ordens: ' . $orderResponse->get_error_message());
			return 'Erro ao buscar ordens';
		}

		$orderData = json_decode(wp_remote_retrieve_body($orderResponse), true);

		if (!isset($orderData['results'])) {
			error_log('Nenhum pedido encontrado.');
			return 'Nenhum pedido encontrado.';
		}

		$orders = $orderData['results']; // Lista de pedidos

		// Loop através das ordens
		foreach ($orders as &$order) {
			$buyer_id = $order['buyer']['id']; // Pega o buyer_id
			$order_id = $order['id']; // Pega o ID do pedido

			// Obter informações do comprador usando o buyer_id
			$buyerResponse = wp_remote_get("https://api.mercadolibre.com/users/$buyer_id", [
				'headers' => [
					'Authorization' => 'Bearer ' . $access_token
				]
			]);

			if (is_wp_error($buyerResponse)) {
				error_log('Erro ao buscar informações do comprador: ' . $buyerResponse->get_error_message());
				continue; // Se der erro ao pegar dados do comprador, continua com o próximo pedido
			}

			$buyerData = json_decode(wp_remote_retrieve_body($buyerResponse), true);

			// Adicionar as informações do comprador ao pedido
			if ($buyerData) {
				$order['buyer_info'] = [
					'nickname' => $buyerData['nickname'],
					'country_id' => $buyerData['country_id'],
					'state' => $buyerData['address']['state'],
					'user_type' => $buyerData['user_type'],
					'permalink' => $buyerData['permalink'],
					'seller_reputation' => $buyerData['seller_reputation']['level_id'] ?? 'N/A',
					'transactions' => $buyerData['seller_reputation']['transactions']['total'] ?? 0,
					'status' => $buyerData['status']['site_status'] ?? 'N/A'
				];
			}

			// Obter descontos usando o ID do pedido
			$discountResponse = wp_remote_get("https://api.mercadolibre.com/orders/$order_id/discounts", [
				'headers' => [
					'Authorization' => 'Bearer ' . $access_token
				]
			]);

			if (is_wp_error($discountResponse)) {
				error_log('Erro ao buscar descontos do pedido: ' . $discountResponse->get_error_message());
				continue;
			}

			$discountData = json_decode(wp_remote_retrieve_body($discountResponse), true);
			
			// Adicionar os descontos ao pedido
			if ($discountData && isset($discountData['details'])) {
				foreach ($discountData['details'] as $discountDetail) {
					if (isset($discountDetail['coupon']['id'])) {
						$coupon_id = $discountDetail['coupon']['id'];

						// Obter informações do cupom
						$couponResponse = wp_remote_get("https://api.mercadolibre.com/coupons/$coupon_id", [
							'headers' => [
								'Authorization' => 'Bearer ' . $access_token
							]
						]);

						if (!is_wp_error($couponResponse)) {
							$couponData = json_decode(wp_remote_retrieve_body($couponResponse), true);

							// Adicionar o nome do cupom, se existir
							$discountDetail['coupon']['name'] = $couponData['name'] ?? 'N/A';
						} else {
							error_log('Erro ao buscar informações do cupom: ' . $couponResponse->get_error_message());
						}
					}
				}

				$order['discounts'] = $discountData;
				
				
				
			}
			
			 if (isset($order['shipping']['id'])) {
					$shipment_id = $order['shipping']['id'];

					$shipmentResponse = wp_remote_get("https://api.mercadolibre.com/shipments/$shipment_id", [
						'headers' => [
							'Authorization' => 'Bearer ' . $access_token
						]
					]);

					if (!is_wp_error($shipmentResponse)) {
						$shipmentData = json_decode(wp_remote_retrieve_body($shipmentResponse), true);

						// Adicionar os dados do envio ao pedido
						if ($shipmentData) {
							$order['shipment_info'] = [
								'receiver_name' => $shipmentData['receiver_address']['receiver_name'] ?? 'N/A',
								'country' => $shipmentData['receiver_address']['country']['name'] ?? 'N/A',
								'state' => $shipmentData['receiver_address']['state']['name'] ?? 'N/A',
								'city' => $shipmentData['receiver_address']['city']['name'] ?? 'N/A',
								'street_name' => $shipmentData['receiver_address']['street_name'] ?? 'N/A',
								'street_number' => $shipmentData['receiver_address']['street_number'] ?? 'N/A',
								'zip_code' => $shipmentData['receiver_address']['zip_code'] ?? 'N/A',
								'comment' => $shipmentData['receiver_address']['comment'] ?? 'N/A',
								'address_line' => $shipmentData['receiver_address']['address_line'] ?? 'N/A',
								'neighborhood' => $shipmentData['receiver_address']['neighborhood']['name'] ?? 'N/A',
							];
						}
					} else {
						error_log('Erro ao buscar informações de envio: ' . $shipmentResponse->get_error_message());
					}
				} else {
					error_log("Shipment ID não encontrado para a ordem $order_id.");
				}
			}

	
        // Formatação dos dados em tabela HTML
        ob_start();
      ?>
	<form method="GET" action="">
		<div class="date-filter">
			<input type="date" id="start_date" placeholder="Data Início:" name="start_date" class="datepicker" value="<?= esc_attr($_GET['start_date'] ?? '') ?>">
			<input type="date" id="end_date" placeholder="Data Fim:"  name="end_date" class="datepicker" value="<?= esc_attr($_GET['end_date'] ?? '') ?>">
			<button type="submit" id='filter_dates' class="btn btn-primary">Filtrar</button>
		</div>
	</form>


    <div class="table-responsive">
		<table id="example" class="display table-custom" style="width:100%">
			<thead>
				<tr>
					<th>ID do Pedido</th>
<!-- 					<th>Cupom</th> -->
					<th>Data</th>
					<th>Título do Item</th>
					<th>Categoria</th>
					<th>Quantidade</th>
					<th>Preço Unitário (BRL)</th>
					<th>Total Pago (BRL)</th>
					<th>Apelido do Comprador</th>
					<th>Email</th>
					<th>Telefone</th>
					
					<th>Nome</th>
					<th>Endereço Completo</th>
					<th>CEP</th>
					<th>Cidade</th>
					<th>Bairro</th>
					<th>Comentários</th>
					
					<th>Método de Pagamento</th>
					<th>Status do Pagamento</th>
					<th>Status</th>
					<th>Descrição</th>
					<th>Valor da Transação (BRL)</th>
					<th>Parcelas</th>
					<th>Valor da Parcela (BRL)</th>
					<th>ID do Frete</th>
					<th>Custo do Frete (BRL)</th>
					<th>Apelido do Vendedor</th>
					<th>Garantia</th>
					<th>Condição</th>
					<th>Data de Expiração</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($orders as $order) : ?>
					<?php foreach ($order['order_items'] as $item) : ?>
						<tr>
							<td><?= esc_html($order['id']) ?></td>
<!-- 							<td><?= esc_html($order['coupon']['id']) ?></td> -->
							<td><?= esc_html(date('d/m/Y', strtotime($order['date_created']))) ?></td>
							<td><?= esc_html($item['item']['title']) ?></td>
							<td><?= esc_html($item['item']['category_id']) ?></td>
							<td><?= esc_html($item['quantity']) ?></td>
							<td><?= esc_html($item['unit_price']) ?></td>
							<td><?= esc_html($order['paid_amount']) ?></td>
							<td><?= esc_html($order['buyer']['nickname']) ?></td>
							<td><?= esc_html($order['buyer']['email'] ?? 'Não tem registrado') ?></td>
							<td><?= esc_html($order['buyer']['phone']['number'] ?? 'Não tem registrado') ?></td>


							<td><?= esc_html($order['shipment_info']['receiver_name']) ?></td>
							<td>
								<?= esc_html($order['shipment_info']['street_name'] ?? '') ?>
								<?= esc_html($order['shipment_info']['street_number'] ?? '') ?>,
								<?= esc_html($order['shipment_info']['neighborhood'] ?? '') ?>
							</td>
							<td><?= esc_html($order['shipment_info']['zip_code'] ?? 'N/A') ?></td>
							<td><?= esc_html($order['shipment_info']['city'] ?? 'N/A') ?></td>
							<td><?= esc_html($order['shipment_info']['neighborhood'] ?? 'N/A') ?></td>
							<td><?= esc_html($order['shipment_info']['comment'] ?? 'N/A') ?></td>

							<td><?= esc_html($order['payments'][0]['payment_method_id']) ?></td>
							<td><?= esc_html($order['payments'][0]['status']) ?></td>
							<td><?= esc_html($order['status']) ?></td>
							<td><?= esc_html($order['payments'][0]['reason']) ?></td>
							<td><?= esc_html($order['payments'][0]['transaction_amount']) ?></td>
							<td><?= esc_html($order['payments'][0]['installments']) ?></td>
							<td><?= esc_html($order['payments'][0]['installment_amount']) ?></td>
							<td><?= esc_html($order['shipping']['id']) ?></td>
							<td><?= esc_html($order['payments'][0]['shipping_cost']) ?></td>
							<td><?= esc_html($order['seller']['nickname']) ?></td>
							<td><?= esc_html($item['item']['warranty']) ?></td>
							<td><?= esc_html($item['item']['condition']) ?></td>
							<td><?= esc_html(date('d/m/Y', strtotime($order['expiration_date']))) ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>


   <div class="table-responsive">
		<table id="example-2" class="display table-custom" style="width:100%">
			<thead>
				<tr>
					<th>Código do Cupom</th>
					<th>Campanha</th>
					<th>Quantidade de Uso</th>
					<th>Status</th>
					<th>Data de Início</th>
					<th>Data de Fim</th>
<!-- 					<th>Vendas Totais</th> -->
<!-- 					<th>Desconto Total</th> -->
				</tr>
			</thead>
			<tbody>
				<?php foreach ($cuponsAgrupados as $coupon) : ?>
					<tr>
                        <td><?= esc_html($coupon['coupon_code']) ?></td>
                        <td><?= esc_html($coupon['campaign_name']) ?></td>
						<td><?= esc_html($coupon['used_coupon']) ?></td>
						<td><?= esc_html($coupon['status']) ?></td>
                        <td><?= esc_html($coupon['start_date']) ?></td>
                        <td><?= esc_html($coupon['end_date']) ?></td>
<!--                         <td><?= esc_html($coupon['total_sales']) ?></td> -->
<!--                         <td><?= esc_html($coupon['total_discount'])?></td> -->
                    </tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

        <?php
        return ob_get_clean();
    }

// Registrando o shortcode para exibir a tabela
add_shortcode('tabela_mercado_pago', 'obter_dados_mercado_pago');
// Incluindo os scripts e estilos do DataTables
function incluir_datatables_assets() {
    wp_enqueue_style('datatables-css', 'https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css');
    wp_enqueue_style('datatables-buttons-css', 'https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css');

    // Carregar jQuery (WordPress já carrega automaticamente, mas para garantir)
    wp_enqueue_script('jquery');

    // Carregar scripts do DataTables e plugins
    wp_enqueue_script('datatables-js', 'https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js', array('jquery'), null, true);
    wp_enqueue_script('datatables-buttons-js', 'https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js', array('jquery', 'datatables-js'), null, true);
    wp_enqueue_script('jszip-js', 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js', array('jquery', 'datatables-js'), null, true);
    wp_enqueue_script('datatables-buttons-html5-js', 'https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js', array('jquery', 'datatables-js', 'jszip-js'), null, true);

    // Adicionar CSS customizado para estilização da tabela
    wp_add_inline_style('datatables-css', '
        .table-responsive {
            overflow-x: auto; /* Adiciona rolagem horizontal quando necessário */
            margin: 20px 0; /* Espaçamento em volta da tabela */
        }
        .date-filter{
			display: flex;
			gap: 8px;
			width: 100%;
		}
		
		.date-filter #filter_dates{
			padding: 1px 40px;
			height: 45px;
		}

        .table-custom {
            width: 100%;
            border-collapse: collapse; /* Remove espaçamentos entre células */
            background-color: #f9f9f9; /* Fundo da tabela */
            color: #333; /* Cor do texto */
        }

        .table-custom th,
        .table-custom td {
            border: 1px solid #ddd; /* Borda das células */
            padding: 12px; /* Espaçamento interno das células */
            text-align: left; /* Alinhamento do texto */
        }

        .table-custom th {
            background-color: #007bff; /* Cor do fundo do cabeçalho */
            color: white; /* Cor do texto do cabeçalho */
            font-weight: bold; /* Negrito no cabeçalho */
        }

        .table-custom tr:nth-child(even) {
            background-color: #f2f2f2; /* Cor de fundo das linhas pares */
        }

        .table-custom tr:hover {
            background-color: #ddd; /* Cor ao passar o mouse sobre as linhas */
        }

        /* Ajusta o tamanho da fonte e estilo */
        .table-custom td, .table-custom th {
            font-size: 14px; /* Tamanho da fonte */
            font-family: Arial, sans-serif; /* Fonte */
        }
    ');

    // Inicializar DataTables com opções de exportação para Excel
    wp_add_inline_script('datatables-js', '
        jQuery(document).ready(function($) {
            var table = $("#example").DataTable({
                dom: "Bfrtip",
                buttons: ["excel"],
                language: { url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Portuguese-Brasil.json" }
            });
        });
    ');
	
	wp_add_inline_script('datatables-js', '
        jQuery(document).ready(function($) {
            var table = $("#example2").DataTable({
                dom: "Bfrtip",
                buttons: ["excel"],
                language: { url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Portuguese-Brasil.json" }
            });
        });
    ');
}
add_action('wp_enqueue_scripts', 'incluir_datatables_assets');



function verificar_cupom_vendas() {
    // Verifica se o cupom foi submetido
    if (isset($_POST['password'])) {
        wp_redirect('https://3xaction.com/admin/');
        exit;
    }else{
		wp_redirect('https://3xaction.com/admin/');
        exit;
	}

}

function obter_dados_mercado_pago_vendedores($atts) {
   
    $user_id = get_current_user_id();

    $seller_cupom = get_user_meta($user_id, 'cupom_user', true);

    if (empty($seller_cupom)) {
        return 'Nenhum cupom de afiliado encontrado para o usuário.';
    }

    $url1 = 'https://api.mercadolibre.com/oauth/token';

    // Definindo o corpo da requisição
    $bodyParams = [
        'grant_type'    => 'client_credentials',
        'client_id'     => '4283005590584822',
        'client_secret' => 'JZGQGj6ezZLM5Afw2lUHsUn2bIUXwdMd'
    ];

    // Fazendo a requisição POST para o primeiro endpoint usando wp_remote_post
    $response = wp_remote_post($url1, [
        'body' => $bodyParams,
        'headers' => [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ]
    ]);

    // Verificando se a requisição foi bem-sucedida
    if (is_wp_error($response)) {
        error_log('Erro na requisição: ' . $response->get_error_message());
        return 'Erro ao obter token';
    }

    $responseDecoded = json_decode(wp_remote_retrieve_body($response), true);

    if (!isset($responseDecoded['access_token'])) {
        error_log('Falha ao obter token de acesso');
        return 'Erro ao obter token de acesso';
    }

    $access_token = $responseDecoded['access_token'];

    // Fazendo a requisição GET para o segundo endpoint (pagamentos)
    $url2 = 'https://api.mercadopago.com/v1/payments/search';
    $response2 = wp_remote_get($url2, [
        'headers' => [
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type'  => 'application/json'
        ]
    ]);

    // Verificando se a requisição foi bem-sucedida
    if (is_wp_error($response2)) {
        error_log('Erro na requisição de pagamentos: ' . $response2->get_error_message());
        return 'Erro ao buscar pagamentos';
    }

    $responseDecoded2 = json_decode(wp_remote_retrieve_body($response2), true);

    if (!isset($responseDecoded2['results'])) {
        error_log('Erro ao decodificar resposta de pagamentos');
        return 'Erro ao obter pagamentos';
    }

    // Filtrando os pagamentos com base no cupom informado
    $paymentsFiltered = array_filter($responseDecoded2['results'], function($payment) use ($seller_cupom) {
        return isset($payment['cupom']) && $payment['cupom'] === $seller_cupom;
    });

    // Verificando se há pagamentos para o cupom
    if (empty($paymentsFiltered)) {
		return '<p class="msg-not-found">Nenhum pedido encontrado para o seu cupom de afiliado. Verifique novamente mais tarde!</p>';
	}

    // Continuar o processamento dos pagamentos filtrados
    $finalPayments = array_map(function($payment) {
        // Mapeia os dados dos pagamentos filtrados (mesma lógica que o exemplo anterior)
        $payer = $payment['additional_info']['payer'] ?? [
            'first_name' => 'Não especificado',
            'last_name' => '',
            'phone' => ['number' => 'Não especificado'],
            'email' => 'Não especificado'
        ];

        return [
            'id'                         => $payment['order']['id'] ?? 'Sem ID',
            'status'                     => $payment['status'] ?? 'Sem status',
            'status_description'         => $payment['status_detail'] ?? 'Sem descrição de status',
            'sale_date'                  => $payment['date_created'] ?? 'Sem data de venda',
            'cupom'                      => $payment['cupom'] ?? 'Sem cupom',
            'units'                      => $payment['installments'] ?? 0,
            'product_revenue_brl'        => $payment['transaction_amount'] ?? 0,
            'shipping_revenue_brl'       => $payment['shipping_cost'] ?? 0,
            'sales_fee_and_taxes'        => $payment['taxes_amount'] ?? 'Não especificado',
            'total_brl'                  => $payment['transaction_details']['total_paid_amount'] ?? 0,
            'sku'                        => $payment['additional_info']['items'][0]['id'] ?? 'Não especificado',
            'listing_title'              => $payment['description'] ?? 'Não especificado',
            'buyer'                      => $payer['first_name'] . ' ' . $payer['last_name'] ?? 'Não especificado',
            'phone_buyer'                => $payer['phone']['number'] ?? 'Não especificado',
            'email_buyer'                => $payer['email'] ?? 'Não especificado',
            'city'                       => $payment['city'] ?? 'Não especificado',
            'state'                      => $payment['state'] ?? 'Não especificado',
            'postal_code'                => $payment['zip_code'] ?? 'Não especificado',
            'country'                    => $payment['country'] ?? 'Não especificado'
        ];
    }, $paymentsFiltered);

    // Formatação dos dados em tabela HTML
    ob_start();
    ?>
    <div class="table-responsive">
        <table id="example" class="display table-custom" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Status</th>
                    <th>Status Descrição</th>
                    <th>Data da Venda</th>
                    <th>Cupom</th>
                    <th>Unidades</th>
                    <th>Receita do Produto (BRL)</th>
                    <th>Frete (BRL)</th>
                    <th>Taxas e Impostos (BRL)</th>
                    <th>Total (BRL)</th>
                    <th>SKU</th>
                    <th>Título do Produto</th>
                    <th>Comprador</th>
                    <th>Email do Comprador</th>
                    <th>Telefone do Comprador</th>
                    <th>Cidade</th>
                    <th>Estado</th>
                    <th>Código Postal</th>
                    <th>País</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($finalPayments as $payment): ?>
                <tr>
                    <td><?php echo esc_html($payment['id']); ?></td>
                    <td><?php echo esc_html($payment['status']); ?></td>
                    <td><?php echo esc_html($payment['status_description']); ?></td>
                    <td><?php echo esc_html(date('d/m/Y H:i:s', strtotime($payment['sale_date']))); ?></td>
                    <td><?php echo esc_html($payment['cupom']); ?></td>
                    <td><?php echo esc_html($payment['units']); ?></td>
                    <td><?php echo esc_html(number_format($payment['product_revenue_brl'], 2, ',', '.')); ?></td>
                    <td><?php echo esc_html(number_format($payment['shipping_revenue_brl'], 2, ',', '.')); ?></td>
                    <td><?php echo esc_html(number_format($payment['sales_fee_and_taxes'], 2, ',', '.')); ?></td>
                    <td><?php echo esc_html(number_format($payment['total_brl'], 2, ',', '.')); ?></td>
                    <td><?php echo esc_html($payment['sku']); ?></td>
                    <td><?php echo esc_html($payment['listing_title']); ?></td>
                    <td><?php echo esc_html($payment['buyer']); ?></td>
                    <td><?php echo esc_html($payment['email_buyer']); ?></td>
                    <td><?php echo esc_html($payment['phone_buyer']); ?></td>
                    <td><?php echo esc_html($payment['city']); ?></td>
                    <td><?php echo esc_html($payment['state']); ?></td>
                    <td><?php echo esc_html($payment['postal_code']); ?></td>
                    <td><?php echo esc_html($payment['country']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

// Registrando o shortcode para exibir a tabela filtrada pelos vendedores
add_shortcode('tabela_mercado_pago_vendedores', 'obter_dados_mercado_pago_vendedores');



function wcfm_custom_report_page( $query_vars ) {
    // Adicione a chave do seu novo relatório
    $query_vars['custom-report'] = 'custom-report';
    return $query_vars;
}
add_filter( 'wcfm_query_vars', 'wcfm_custom_report_page', 50 );


function wcfm_custom_report_menu( $menus ) {
    $menus['custom-report'] = array(
        'label' => __( 'MercadoShops', 'wcfm' ),
        'url'   => wcfm_get_endpoint_url( 'custom-report' ), // A URL da página do relatório
        'icon'  => 'wcfmfa fa-chart-pie', // Ícone da página
        'priority' => 8 // Prioridade de exibição no menu
    );
    return $menus;
}
add_filter( 'wcfm_menus', 'wcfm_custom_report_menu', 50 );


function wcfm_load_custom_report_view( $end_point ) {
    if( $end_point === 'custom-report' ) {
        echo '
		<div class="collapse wcfm-collapse" id="wcfm_orders_listing">
			<div class="wcfm-page-headig">
			<a href="https://3xaction.com/painel-administracao/custom-report/" class="wcfm_header_panel_custom_report text_tip" data-tip="Relatório Mercado Shops">
						<i class="wcfmfa fa-chart-pie"></i> Relatório Mercado Shops
					</a>
				<div class="wcfm_header_panel">
					<a href="https://3xaction.com/painel-administracao/profile/" class="wcfm_header_panel_profile">
						<img decoding="async" class="wcfm_header_panel_profile_img text_tip" src="https://3xaction.com/wp-content/plugins/wc-frontend-manager/assets/images/user.png" data-tip="Perfil" data-hasqtip="67">
					</a>

					<a href="https://3xaction.com/painel-administracao/messages/" class="wcfm_header_panel_messages text_tip" data-tip="Painel de Notificações" data-hasqtip="68">
						<i class="wcfmfa fa-bell"></i>
						<span class="unread_notification_count message_count">0</span>
						<div class="notification-ring"></div>
					</a>

					<a href="https://3xaction.com/painel-administracao/notices/" class="wcfm_header_panel_notice text_tip" data-tip="Announcement" data-hasqtip="69">
						<i class="wcfmfa fa-bullhorn"></i>
						<div class="notification-ring"></div>
					</a>

					<a href="https://3xaction.com/painel-administracao/knowledgebase/" class="wcfm_header_panel_knowledgebase text_tip" data-tip="Base de Conhecimento" data-hasqtip="70">
						<i class="wcfmfa fa-book"></i>
					</a>

					<!-- Novo item de menu para Relatório Mercado Shops -->
					<a href="https://3xaction.com/painel-administracao/custom-report/" class="wcfm_header_panel_custom_report text_tip" data-tip="Relatório Mercado Shops">
						<i class="wcfmfa fa-chart-pie"></i> Relatório Mercado Shops
					</a>
				</div>
			</div>
			<div class="wcfm-collapse-content">
				' . do_shortcode('[tabela_mercado_pago]') . '
				' . do_shortcode('[exibir_cupons]') . '
			</div>
		</div>';
    }
}
add_action( 'wcfm_load_views', 'wcfm_load_custom_report_view', 50 );


function wcfm_custom_report_page_title( $title ) {
    global $wp;
    switch ( $wp->query_vars['wcfm-endpoint'] ) {
        case 'custom-report':
            $title = __( 'MercadoShops', 'wcfm' );
            break;
    }
    return $title;
}
add_filter( 'wcfm_endpoint_title', 'wcfm_custom_report_page_title' );


function criar_tabela_cupons() {
    global $wpdb;
    $tabela = $wpdb->prefix . 'cupons_aplicados';
    
    // Definindo o charset correto para a tabela
    $charset_collate = $wpdb->get_charset_collate();

    // SQL para criar a tabela
    $sql = "CREATE TABLE $tabela (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        cupom varchar(255) NOT NULL,
        quantidade mediumint(9) DEFAULT 1 NOT NULL,
        PRIMARY KEY (id),
        UNIQUE (cupom)
    ) $charset_collate;";

    // Inclui o arquivo necessário para executar a função dbDelta
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Verifica se houve erro na criação da tabela
    if (!empty($wpdb->last_error)) {
        error_log('Erro na criação da tabela de cupons: ' . $wpdb->last_error);
    }
}

// Hook para criar a tabela quando o plugin ou tema for ativado
register_activation_hook(__FILE__, 'criar_tabela_cupons');


function validar_cupom_formulario() {
    global $wpdb;
    $cuponsValidos = [
        "eslen", "vinha", "rodrigopia", "gabioliveira", "fermento", "raquel",
        "anafarias", "rafa", "mmovimento", "correjacque", "fabiana", "dratamires",
        "agabi", "laercio", "bibi", "giovana", "juliasette", "luisa", 
        "cavallaro", "laura", "spaceorbit", "doslivros"
    ];

    if (isset($_POST['cupom-desconto'])) {
        $cupom = sanitize_text_field($_POST['cupom-desconto']);
		setcookie('cupom_aplicado', $cupom, time() + 86400, "/");
        
        // Ignora maiúsculas/minúsculas
        $cupom = strtolower($cupom);
        $cuponsValidos = array_map('strtolower', $cuponsValidos);

        if (in_array($cupom, $cuponsValidos)) {
            setcookie('cupom_aplicado', $cupom, time() + 86400, "/");

            // Verifica se o cupom já existe na tabela
            $tabela = $wpdb->prefix . 'cupons_aplicados';
            $cupom_existe = $wpdb->get_var($wpdb->prepare("SELECT quantidade FROM $tabela WHERE cupom = %s", $cupom));

            if ($cupom_existe !== null) {
                // Se o cupom existir, atualize a quantidade
                $wpdb->query($wpdb->prepare("UPDATE $tabela SET quantidade = quantidade + 1 WHERE cupom = %s", $cupom));
            } else {
                // Se não existir, insira um novo registro
                $wpdb->insert($tabela, [
                    'cupom' => $cupom,
                    'quantidade' => 1
                ]);
            }

            // Verifica erros do banco de dados
            if (!empty($wpdb->last_error)) {
                error_log('Erro ao aplicar o cupom: ' . $wpdb->last_error);
            }

            $mensagem = "<p style='color: green;'>Cupom aplicado com sucesso!</p>";
        } else {
            $mensagem = "<p style='color: red;'>Cupom inválido. Tente novamente.</p>";
        }
    }

    ob_start(); ?>
    <form method="post" action="">
		<p style="color:#fff; font-weight: 500; font-size: 16px">
			Cupom <span style="color:red;">*</span>
		</p>
        <input type="text" placeholder="XXX-XXX" name="cupom-desconto" required>
        <input type="submit" value="Aplicar Cupom">
    </form>
    <?php if (isset($mensagem)) echo $mensagem; ?>
    <?php
    return ob_get_clean();
}

add_shortcode('validar_cupom', 'validar_cupom_formulario');


function exibir_cupons_aplicados() {
    global $wpdb;
    $tabela = $wpdb->prefix . 'cupons_aplicados';

    // Consulta os cupons no banco de dados
    $resultados = $wpdb->get_results("SELECT * FROM $tabela");

    // Gerar a tabela HTML
    ob_start(); ?>
    <table>
        <thead>
            <tr>
                <th>Cupom</th>
                <th>Quantidade Aplicada</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultados): ?>
                <?php foreach ($resultados as $cupom): ?>
                    <tr>
                        <td><?php echo esc_html($cupom->cupom); ?></td>
                        <td><?php echo esc_html($cupom->quantidade); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2">Nenhum cupom foi aplicado ainda.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}

// Registra o shortcode [exibir_cupons]
add_shortcode('exibir_cupons', 'exibir_cupons_aplicados');


// Formulário de Login de Influenciador
function login_influenciador_form() {
    if (isset($_POST['login'])) {
        $username = sanitize_text_field($_POST['username']);
        $password = sanitize_text_field($_POST['password']);

        // Autenticação do usuário
        $creds = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => true
        );
        $user = wp_signon($creds, false);

        if (is_wp_error($user)) {
            wp_redirect('https://3xaction.com/dashboard-influenciador/');
			exit();
        } else {
            wp_redirect('https://3xaction.com/dashboard-influenciador/');
			exit();
        }
    }

    ob_start(); ?>
    <form method="POST">
        <label for="username">Nome de Usuário:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Senha:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" name="login">Entrar</button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('login_influenciador', 'login_influenciador_form');


function dashboard_influenciador() {
    if (!is_user_logged_in()) {
        wp_redirect(site_url('/influencer'));
        exit;
    }

    $user_id = get_current_user_id();
    $cupom = get_user_meta($user_id, 'cupom_user', true);

    if (empty($cupom)) {
        return 'Nenhum cupom encontrado para este influenciador.';
    }

    wp_redirect('https://3xaction.com/dashboard-influenciador/');
	exit();
}
add_shortcode('dashboard_influenciador', 'dashboard_influenciador');

// Exibir o cupom do afiliado
function test() {
    // Verificar se o usuário está logado
    if (!is_user_logged_in()) {
        return '<p>Por favor, faça login para ver o seu cupom.</p>';
    }

    // Obtém o ID do usuário logado
    $user_id = get_current_user_id();

    // Obtém o cupom do afiliado associado ao usuário logado
    $seller_cupom = get_user_meta($user_id, 'cupom_user', true);

    // Se o usuário não tem um cupom associado, exiba uma mensagem
    if (empty($seller_cupom)) {
        return '<p class="msg-not-found">Nenhum cupom de afiliado associado à sua conta. Por favor, entre em contato com o administrador.</p>';
    }

    // Retorna o cupom
    return '<p>Seu cupom de afiliado é: ' . esc_html($seller_cupom) . '</p>';
}
add_shortcode('test', 'test');

// Formulário de Cadastro de Influenciador
function cadastro_influenciador_form() {
    if (isset($_POST['submit'])) {
        // Sanitizar e processar dados do formulário
        $username = sanitize_text_field($_POST['username']);
        $password = sanitize_text_field($_POST['password']);
        $cupom = sanitize_text_field($_POST['cupom_user']);

        // Verificar se o nome de usuário já existe
        if (username_exists($username)) {
            return "Nome de usuário já está em uso.";
        }

        // Criar um novo usuário
        $user_id = wp_create_user($username, $password);
        if (!is_wp_error($user_id)) {
            // Salvar o cupom como meta do usuário
            update_user_meta($user_id, 'cupom_user', $cupom); // Garante que 'cupom_user' seja salvo corretamente
            return "Cadastro bem-sucedido!";
        } else {
            return "Erro ao criar usuário.";
        }
    }

    ob_start(); ?>
    <form method="POST">
        <label for="username">Nome de Usuário:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Senha:</label>
        <input type="password" id="password" name="password" required>

        <label for="cupom_user">Cupom:</label>
        <input type="text" id="cupom_user" name="cupom_user" required>

        <button type="submit" name="submit">Cadastrar</button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('cadastro_influenciador', 'cadastro_influenciador_form');


// Adicionar o campo "Cupom de Afiliado" ao perfil do usuário no painel
function adicionar_campo_cupom_no_perfil($user) { 
    $cupom = get_user_meta($user->ID, 'cupom_user', true); // Obter o cupom do usuário
    ?>
    <h3>Informações de Afiliado</h3>
    <table class="form-table">
        <tr>
            <th><label for="cupom_user">Cupom de Afiliado</label></th>
            <td>
                <input type="text" name="cupom_user" id="cupom_user" value="<?php echo esc_attr($cupom); ?>" class="regular-text" /><br />
                <span class="description">Digite o cupom de afiliado para este usuário.</span>
            </td>
        </tr>
    </table>
    <?php 
}
add_action('show_user_profile', 'adicionar_campo_cupom_no_perfil'); // Exibir no perfil do próprio usuário
add_action('edit_user_profile', 'adicionar_campo_cupom_no_perfil'); // Exibir no perfil de outros usuários

// Salvar o campo "Cupom de Afiliado" quando o perfil for atualizado
function salvar_campo_cupom_no_perfil($user_id) {
    // Verifica se o usuário pode editar o perfil
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    // Sanitizar e salvar o cupom de afiliado
    if (isset($_POST['cupom_user'])) {
        update_user_meta($user_id, 'cupom_user', sanitize_text_field($_POST['cupom_user']));
    }
}
add_action('personal_options_update', 'salvar_campo_cupom_no_perfil'); // Salvamento no perfil próprio
add_action('edit_user_profile_update', 'salvar_campo_cupom_no_perfil'); // Salvamento no perfil de outros usuários


add_filter('woocommerce_add_to_cart_redirect', 'redirecionar_direto_para_checkout');

function redirecionar_direto_para_checkout($url) {
    return wc_get_checkout_url();
}

function sistema_etapas_por_produto($atts) {
    global $product;

    if (!$product || !$product instanceof WC_Product) {
        return ''; // Garante que só funcione no loop de produtos
    }

    $preco_original = $product->get_regular_price();
    $preco_desconto = $product->get_sale_price();
    $id_produto = $product->get_id();

    ob_start();
    ?>
    <div id="etapas-cupom-<?php echo $id_produto; ?>" class="etapas-cupom">
        <div class="etapa" id="etapa-1-<?php echo $id_produto; ?>" data-preco-original="<?php echo floatval($preco_original); ?>">
            <h3 class="titulo-padrao" >Insira o seu cupom</h3>
            <input type="text" id="input-cupom-<?php echo $id_produto; ?>" placeholder="Digite o cupom">
            <button type="button" class="validar-cupom" data-produto-id="<?php echo $id_produto; ?>">Validar Cupom</button>
            <p id="erro-cupom-<?php echo $id_produto; ?>" style="color: red; display: none;">Cupom inválido. Tente novamente.</p>
        </div>

        <!-- Etapa 2: Exibir formas de pagamento -->
        <div class="etapa" id="etapa-2-<?php echo $id_produto; ?>" style="display: none;">
            <h3 class="titulo-padrao">Descontos de Pagamento</h3>
            <p>Preço Original: <strong class="valor" >R$ <?php echo number_format($preco_original, 2, ',', '.'); ?></strong></p>
            <p>Desconto à Vista: <strong class="valor">R$ <span  id="preco-desconto-<?php echo $id_produto; ?>">-</span></strong></p>
            <p>Desconto em 12x: <strong class="valor">R$ <span  id="preco-12x-<?php echo $id_produto; ?>">-</span></strong></p>
            <p>À Vista no Cartão: <strong class="valor">R$ <span  id="preco-1x-<?php echo $id_produto; ?>">-</span></strong></p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const cuponsValidos = ['DESCONTO10', 'PROMO20', 'AVISTA30']; // Cupons válidos

            document.querySelectorAll('.validar-cupom').forEach(botao => {
                botao.addEventListener('click', function () {
                    const produtoId = this.getAttribute('data-produto-id');
                    const inputCupom = document.getElementById('input-cupom-' + produtoId);
                    const erroCupom = document.getElementById('erro-cupom-' + produtoId);
                    const etapa1 = document.getElementById('etapa-1-' + produtoId);
                    const etapa2 = document.getElementById('etapa-2-' + produtoId);

                    // Recupera o preço original do atributo data-preco-original
                    const precoOriginal = parseFloat(etapa1.dataset.precoOriginal);

                    const cupom = inputCupom.value.trim();
                    if (cuponsValidos.includes(cupom)) {
                        erroCupom.style.display = 'none';
                        etapa1.style.display = 'none';
                        etapa2.style.display = 'block';

                        const desconto = cupom === 'DESCONTO10' ? 0.10 :
                                         cupom === 'PROMO20' ? 0.20 :
                                         cupom === 'AVISTA30' ? 0.30 : 0;

                        const precoComDesconto = precoOriginal - (precoOriginal * desconto);
                        const preco12x = precoOriginal - 50;
                        const preco1x = precoOriginal;

                        document.getElementById('preco-desconto-' + produtoId).textContent = precoComDesconto.toFixed(2).replace('.', ',');
                        document.getElementById('preco-12x-' + produtoId).textContent = preco12x.toFixed(2).replace('.', ',');
                        document.getElementById('preco-1x-' + produtoId).textContent = preco1x.toFixed(2).replace('.', ',');
                    } else {
                        erroCupom.style.display = 'block';
                    }
                });
            });
        });
    </script>

    <style>
		.titulo-padrao{
			color: #000000;
			font-family: "Poppins", Sans-serif;
			font-size: 14px;
			font-weight: 700;
			line-height: normal;
			text-transform: uppercase;
			margin-bottom: 10px;
/* 			text-align: center; */
		}
/*         .etapas-cupom {
            margin: 4px 0;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #fff;
			border-radius: 20px;
        } */
        .etapas-cupom input {
            width: 100%;
            padding: 8px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
		
		.etapas-cupom p {
           font-size: 12px;
margin-bottom: 4px;
        }
		.etapas-cupom .valor {
           font-size: 14px;
		   color: #DC0000;
        }
        .etapas-cupom button {
            color: #fff;
            background-color: #DC0000;
            border: none;
            border-radius: 10px;
            cursor: pointer;
			font-size: 13px;
    		padding: 10px 20px;
			text-transform: uppercase;
			width: 100%
			
         }
        .etapas-cupom button:hover {
            background-color: #005a9c;
        }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('sistema_etapas_produto', 'sistema_etapas_por_produto');


function custom_calculator_shortcode() {
    ob_start();
    ?>
    <style>
        /* Estilo do botão de calculadora */
        #calculator-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #cf3033;
            color: white;
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: center;
            align-items: center;
			animation: breathe 2s ease-in-out infinite;
            cursor: pointer;
            z-index: 9999;
        }
		
		#calculator-button i {
            font-size: 24px;
			animation: beat 2s ease-in-out infinite;
			padding-right: 25px;
        }

		@keyframes breathe {
			0% {
				box-shadow: 0 0 0 0 rgba(207, 48, 51, 0.432);
			}

			70% {
				box-shadow: 0 0 0 15px rgba(207, 48, 51, 0);
			}

			100% {
				box-shadow: 0 0 0 0 rgba(0, 0, 0, 0);
			}
		}

		@keyframes beat {
			0% {
				transform: scale(1);
			}

			50% {
				transform: scale(1.2);
			}

			100% {
				transform: scale(1);
			}
		}
		#calculator-text {
			position: fixed;
			bottom: 30px;
			right: 100px;
			background-color: #cf3033;
			color: white;
			padding: 10px 15px 10px 15px;
			border-radius: 10px 0px 0px 10px;
			font-size: 14px;
			box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
			opacity: 0;
			transform: translateX(20px);
			transition: opacity 0.5s ease, transform 0.5s ease;
		}

		#calculator-text::after {
			content: '';
			position: absolute;
			top: 50%;
			right: -10px; /* Ajusta a posição da seta */
			transform: translateY(-50%);
			width: 0;
			height: 0;
			border-style: solid;
			border-width: 10px 0 10px 10px; /* Cria o formato da seta */
			border-color: transparent transparent transparent #cf3033;
		}
        #calculator-text.show {
            opacity: 1;
            transform: translateX(0);
        }

        /* Estilo do modal */
        #calculator-modal {
            display: none;
            position: fixed;
            bottom: 90px;
            right: 20px;
            background: #fff;
            border-radius: 8px;
            width: 300px; /* Tamanho fixo para o modal */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            z-index: 10000;
        }

        #calculator-modal .modal-header {
            display: flex;
			justify-content: space-between;
			align-items: center;
			background-color: #cf3033;
			color: #fff;
			border-radius: 8px 8px 0 0;
			padding: 4px 16px;
        }

        #calculator-modal .modal-header h2 {
            font-size: 17px;
            margin: 0;
			color: #fff;
        }

        #calculator-modal .modal-close {
            background: none;
            border: none;
			height: 20px;
			width: 10px;
            font-size: 14px;
			color: #fff;
			background: none;
            cursor: pointer;
			box-shadow: none;
			padding: 0;
        }
		
		.body-modal{
			padding: 20px;
		}
		
		.body-modal select, .body-modal input{
			width: 100%;
			border-radius: 8px;
		}
		
		.body-modal label{
			text-align: start;
		}
		
		.btn-calcular{
			width: 100%
		}

        /* Esconder preços inicialmente */
        #discounts {
            display: none;
        }
    </style>

    <button id="calculator-button">
        <i class="dashicons dashicons-calculator"></i>
    </button>
    <span id="calculator-text">Calculadora de Descontos!</span>

    <div id="calculator-modal">
        <div class="modal-header">
            <h2>Calculadora de Descontos</h2>
            <button class="modal-close" onclick="toggleModal()">×</button>
        </div>
		<div class="body-modal">
			<div>
<!-- 				<label for="product-select">Selecione um produto:</label> -->
				<select id="product-select">
					<option value="">Escolha um produto</option>
					<?php
					$products = wc_get_products(['limit' => -1]);
					foreach ($products as $product) {
						echo '<option value="' . $product->get_id() . '" data-name="' . $product->get_name() . '" data-price="' . $product->get_price() . '">' . $product->get_name() . '</option>';
					}
					?>
				</select>
			</div>
			<div>
<!-- 				<label for="coupon-code">Digite o cupom de desconto:</label> -->
				<input type="text" id="coupon-code" placeholder="Insira o cupom">
			</div>
			<div id="discounts">
				<p>Preço original: <span id="original-price">R$ 0,00</span></p>
				<p>Com desconto PIX: <span id="pix-price">R$ 0,00</span></p>
				<p>Com desconto à vista: <span id="avista-price">R$ 0,00</span></p>
				<p>Parcelado em 12x: <span id="parcelado-price">R$ 0,00</span></p>
			</div>
			<button class="btn-calcular" onclick="calculateDiscounts()">Calcular</button>
		</div>
    </div>

    <script>
		document.addEventListener('DOMContentLoaded', function () {
            const text = document.getElementById('calculator-text');

            function toggleText() {
                text.classList.add('show');
                setTimeout(() => {
                    text.classList.remove('show');
                }, 8000); // Manter o texto visível por 4 segundos
            }

            // Exibir o texto a cada 5 segundos
            toggleText(); // Exibe a primeira vez
            setInterval(toggleText, 5000); // Repetir a cada 5 segundos
        }); 
		
        const productDiscounts = {
            "Venu 3s Grafite" : { default: 18.28, eslen: 20.43 },
            "Venu 3s Cinza Com Prata": { default: 18.28, eslen: 20.43 },
            "Garmin Venum 3 45mm": { default: 18.28, eslen: 20.43 },
            "Forerunner 165 Preto": { default: 14.82, eslen: 18.53 },
            "Forerunner 265 Branco": { default: 18.28, eslen: 20.43 },
            "Garmin Forerunner 965 Branco Amoled": { default: 20.00, eslen: 21.67 },
            "Venu 3s Marfim com Dourado": { default: 18.28, eslen: 20.43 },
            "Venu 3s Cinza com Dourado": { default: 18.28, eslen: 20.43 },
            "Garmin Venu 3 Branco": { default: 18.28, eslen: 20.43 },
            "Garmin Forerunner 965 Branco AS": { default: 20.00, eslen: 21.67 },
            "Garmin Forerunner 955 Preto Solar": { default: 18.28, eslen: 20.43 },
            "Garmin Forerunner 265 Music Preto": { default: 18.28, eslen: 20.43 },
            "Garmin Forerunner 265S Music Preto": { default: 18.28, eslen: 20.43 },
            "Forerunner 265s Branco/verde 42mm": { default: 18.28, eslen: 20.43 },
            "Garmin Forerunner 255 Music": { default: 21.43, eslen: 23.82 },
            "Forerunner 165 Branco Amoled": { default: 15.63, eslen: 18.76 },
            "Forerunner 165 Music Preto": { default: 15.63, eslen: 18.76 },
            "Garmin Forerunner 55": { default: 13.64, eslen: 18.19 }
        };

        const validCoupons = ["bibi", "rodrigopia", "laercio", "fabiana", "spaceorbit", "raquel", "vinha", "anafarias", "cavallaro", "laura", "gabioli", "doslivros"];
        const eslenCoupon = "eslen";

        const modal = document.getElementById('calculator-modal');
        const button = document.getElementById('calculator-button');
        const discountsDiv = document.getElementById('discounts');

        function toggleModal() {
            const isVisible = modal.style.display === 'block';
            modal.style.display = isVisible ? 'none' : 'block';
        }

        button.addEventListener('click', toggleModal);

        async function calculateDiscounts() {
            const productSelect = document.getElementById('product-select');
            const couponCode = document.getElementById('coupon-code').value.trim();
            const originalPriceElement = document.getElementById('original-price');
            const pixPriceElement = document.getElementById('pix-price');
            const avistaPriceElement = document.getElementById('avista-price');
            const parceladoPriceElement = document.getElementById('parcelado-price');

            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const productName = selectedOption.getAttribute('data-name');
            const originalPrice = parseFloat(selectedOption.getAttribute('data-price')) || 0;

            if (!originalPrice) {
                alert('Selecione um produto válido!');
                return;
            }

            if (!couponCode) {
                alert('Digite um código de cupom!');
                return;
            }

            let discountPercentage = 0;

           if (!productDiscounts[productName]) {
				alert('Produto inválido ou sem desconto disponível!');
				return;
			}

			const lowerCaseCoupon = couponCode.toLowerCase();

			if (lowerCaseCoupon === eslenCoupon.toLowerCase()) {
				discountPercentage = productDiscounts[productName].eslen;
			} else if (validCoupons.map(coupon => coupon.toLowerCase()).includes(lowerCaseCoupon)) {
				discountPercentage = productDiscounts[productName].default;
			} else {
				alert('Cupom inválido ou não aplicável para este produto!');
				return;
			}

            const discountedPrice = originalPrice * (1 - discountPercentage / 100);
            originalPriceElement.textContent = `R$ ${originalPrice.toFixed(2)}`;
            pixPriceElement.textContent = `R$ ${(discountedPrice * 0.95).toFixed(2)}`;
            avistaPriceElement.textContent = `R$ ${discountedPrice.toFixed(2)}`;
            parceladoPriceElement.textContent = `R$ ${(discountedPrice / 12).toFixed(2)} (por parcela)`;

            discountsDiv.style.display = 'block';
        }
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('custom_calculator', 'custom_calculator_shortcode');



//  ================ PARTE DO DESCONTO ===================================


// Preencher automaticamente o campo do cupom no checkout se já foi definido
function autofill_coupon_checkout_field() {
    $coupon_code = WC()->session->get('custom_coupon', '');
    $influencer_name = WC()->session->get('influencer_name', '');

    if (!empty($coupon_code)) {
        ?>
        <script>
            jQuery(document).ready(function($) {
                $('#custom_coupon_code').val('<?php echo esc_js($coupon_code); ?>');
                $('#influencer_name').val('<?php echo esc_js($influencer_name); ?>');
            });
        </script>
        <?php
    }
}
add_action('woocommerce_after_order_notes', 'autofill_coupon_checkout_field');

//  Inicio Cupoms 



$valid_coupons = [
    "eslen" => "Eslen", 
    "vinha" => "Vinha", 
    "rodrigopia" => "Rodrigo Pia", 
    "gabioliveira" => "Gabi Oliveira", 
    "fermento" => "Fermento", 
    "raquel" => "Raquel",
    "anafarias" => "Ana Farias", 
    "rafa" => "Rafa", 
    "mmovimento" => "M Movimento", 
    "andressa" => "Andressa", 
    "fabiana" => "Fabiana", 
    "dratamires" => "Dra. Tamires", 
    "cesarmahamamudra" => "César Mahama Mudra", 
    "runnersbrasil" => "Runners Brasil", 
    "bibi" => "Bibi", 
    "giovana" => "Giovana", 
    "flamino" => "Flamino", 
    "mend" => "Mend", 
    "cavallaro" => "Cavallaro", 
    "laura" => "Laura", 
    "spaceorbit" => "Space Orbit", 
    "doslivros" => "Dos Livros", 
    "testing" => "Testing"
];

// Mapeamento de descontos por produto
$discounts = [
	3353 => 350,    
	4207 => 500,    
	3355 => 500,    
	3354 => 750,    
	3357 => 1050,   
	3360 => 1100,   
	3361 => 1100,   
	3362 => 1100,   
	4200 => 1100,   
	4193 => 1550,   
	3364 => 1550,   
	3363 => 1550,   
	4218 => 1100,   
	4215 => 1100,   
	4212 => 1100,   
	3368 => 1100,   
	3367 => 1100,   
	3366 => 1100,
	6076 => 5
];

// Adicionar campo de cupom personalizado no carrinho
function add_custom_coupon_field_to_cart() {
    echo '<div class="custom-coupon-container">
        <h3>Inserir Código de Desconto</h3>
        <input type="text" id="custom_coupon_code" name="custom_coupon_code" placeholder="Digite seu código">
        <button type="button" id="apply_custom_coupon">Aplicar</button>
        <p id="custom_coupon_message"></p>
    </div>';

    ?>
    <script>
        jQuery(document).ready(function($) {
            $('#apply_custom_coupon').click(function() {
                var coupon_code = $('#custom_coupon_code').val();
                $.ajax({
                    type: 'POST',
                    url: '<?php echo admin_url("admin-ajax.php"); ?>',
                    data: {
                        action: 'apply_custom_coupon',
                        coupon_code: coupon_code
                    },
                    success: function(response) {
                        $('#custom_coupon_message').html(response);
                        setTimeout(() => location.reload(), 1500);
                    }
                });
            });
        });
    </script>
    <style>
        .custom-coupon-container {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            background: #f9f9f9;
            text-align: center;
        }
        .custom-coupon-container input {
            padding: 5px;
            width: 60%;
        }
        .custom-coupon-container button {
            padding: 5px 15px;
            background: #054c84;
            color: white;
            border: none;
            cursor: pointer;
        }
    </style>
    <?php
}
add_action('woocommerce_before_cart', 'add_custom_coupon_field_to_cart');

// Aplicar o desconto baseado no cupom personalizado
function apply_custom_coupon() {
    if (isset($_POST['coupon_code'])) {
        $coupon_code = sanitize_text_field($_POST['coupon_code']);

        // Verificar se o cupom é válido
        global $valid_coupons;
        if (isset($valid_coupons[$coupon_code])) {
            // Armazenar o nome do influenciador na sessão
            $influencer_name = $valid_coupons[$coupon_code];
            WC()->session->set('custom_coupon', $coupon_code);
            WC()->session->set('influencer_name', $influencer_name);
            echo "<span style='color: green;'>Código aplicado com sucesso! Influenciador: {$influencer_name}</span>";
        } else {
            WC()->session->set('custom_coupon', '');
            WC()->session->set('influencer_name', '');
            echo "<span style='color: red;'>Código inválido!</span>";
        }
    }
    wp_die();
}
add_action('wp_ajax_apply_custom_coupon', 'apply_custom_coupon');
add_action('wp_ajax_nopriv_apply_custom_coupon', 'apply_custom_coupon');

// Aplicar desconto no carrinho
function apply_discount_on_cart_totals($cart) {
    if (is_admin() && !defined('DOING_AJAX')) return;

    $coupon_code = WC()->session->get('custom_coupon');

    if ($coupon_code) {
        global $discounts;
        $discount_total = 0;

        foreach ($cart->get_cart() as $cart_item) {
            $product_id = $cart_item['product_id'];

            if (isset($discounts[$product_id])) {
                $discount_total += $discounts[$product_id] * $cart_item['quantity'];
            }
        }

        if ($discount_total > 0) {
            $cart->add_fee(__('Desconto Aplicado', 'woocommerce'), -$discount_total);
        }
    }
}
add_action('woocommerce_cart_calculate_fees', 'apply_discount_on_cart_totals');

// Salvar o nome do influenciador e o desconto aplicado no pedido
function save_discount_info_in_order($order_id) {
    if (!$order_id) return;

    $order = wc_get_order($order_id);
    $coupon_code = WC()->session->get('custom_coupon');
    $influencer_name = WC()->session->get('influencer_name');

    if ($coupon_code) {
        global $discounts;
        $discount_total = 0;

        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            if (isset($discounts[$product_id])) {
                $discount_total += $discounts[$product_id] * $item->get_quantity();
            }
        }

        if ($discount_total > 0) {
            // Salvar como meta do pedido
            $order->update_meta_data('_custom_discount_code', $coupon_code);
            $order->update_meta_data('_custom_discount_value', $discount_total);
            $order->update_meta_data('_influencer_name', $influencer_name);

            // Adicionar nota ao pedido
            $order->add_order_note("Cupom aplicado: {$coupon_code}. Influenciador: {$influencer_name}. Desconto total: R$ {$discount_total}");

            // Salvar as alterações no pedido
            $order->save();
        }
    }
}
add_action('woocommerce_checkout_update_order_meta', 'save_discount_info_in_order');


// Adicionar campo de cupom personalizado no checkout
function add_custom_coupon_field_to_checkout($checkout) {
    echo '<div class="custom-coupon-container">
        <h3>Inserir Código de Desconto</h3>
        <input type="text" id="custom_coupon_code" name="custom_coupon_code" placeholder="Digite seu código">
        <button type="button" id="apply_custom_coupon">Aplicar</button>
        <p id="custom_coupon_message"></p>
    </div>';

    ?>
    <script>
        jQuery(document).ready(function($) {
            $('#apply_custom_coupon').click(function() {
                var coupon_code = $('#custom_coupon_code').val();
                $.ajax({
                    type: 'POST',
                    url: '<?php echo admin_url("admin-ajax.php"); ?>',
                    data: {
                        action: 'apply_custom_coupon',
                        coupon_code: coupon_code
                    },
                    success: function(response) {
                        $('#custom_coupon_message').html(response);
                        setTimeout(() => location.reload(), 1500); // Recarregar a página após aplicação do cupom
                    }
                });
            });
        });
    </script>
    <style>
        .custom-coupon-container {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            background: #f9f9f9;
            text-align: center;
        }
        .custom-coupon-container input {
            padding: 5px;
            width: 60%;
        }
        .custom-coupon-container button {
            padding: 5px 15px;
            background: #054c84;
            color: white;
            border: none;
            cursor: pointer;
        }
    </style>
    <?php
}
add_action('woocommerce_after_order_notes', 'add_custom_coupon_field_to_checkout');




// ============== Criar um shortcode para exibir o relatório de cupons =======================



// Adicionar coluna para nome do influenciador no relatório
function coupon_report_shortcode() {
    ob_start();
    ?>
    <div class="coupon-report-container">
        <h2>Relatório de Cupons</h2>
        <form method="GET">
            <label for="coupon_code">Código do Cupom:</label>
            <input type="text" id="coupon_code" name="coupon_code" value="<?php echo isset($_GET['coupon_code']) ? esc_attr($_GET['coupon_code']) : ''; ?>">
            
            <label for="start_date">Data Inicial:</label>
            <input type="date" id="start_date" name="start_date" value="<?php echo isset($_GET['start_date']) ? esc_attr($_GET['start_date']) : ''; ?>">
            
            <label for="end_date">Data Final:</label>
            <input type="date" id="end_date" name="end_date" value="<?php echo isset($_GET['end_date']) ? esc_attr($_GET['end_date']) : ''; ?>">
            
            <button type="submit">Filtrar</button>
        </form>

        <?php
        if (isset($_GET['coupon_code']) || isset($_GET['start_date']) || isset($_GET['end_date'])) {
            global $wpdb;
            $query = "SELECT ID, post_date FROM {$wpdb->prefix}posts WHERE post_type = 'shop_order' AND post_status IN ('wc-completed', 'wc-processing')";
            
            if (!empty($_GET['start_date'])) {
                $query .= $wpdb->prepare(" AND post_date >= %s", $_GET['start_date']);
            }
            if (!empty($_GET['end_date'])) {
                $query .= $wpdb->prepare(" AND post_date <= %s", $_GET['end_date']);
            }
            
            $orders = $wpdb->get_results($query);
            ?>
            <table border="1">
                <tr>
                    <th>ID do Pedido</th>
                    <th>Data</th>
                    <th>Cupom Aplicado</th>
                    <th>Valor do Desconto</th>
                    <th>Nome do Influenciador</th>
                </tr>
                <?php
                foreach ($orders as $order) {
                    $order_obj = wc_get_order($order->ID);
                    $coupon = $order_obj->get_meta('_custom_discount_code');
                    $discount = $order_obj->get_meta('_custom_discount_value');
                    $influencer_name = $order_obj->get_meta('_influencer_name'); // Obter nome do influenciador

                    if (!$coupon || (!$discount && $discount !== '0')) continue;

                    if (!empty($_GET['coupon_code']) && $_GET['coupon_code'] !== $coupon) continue;
                    ?>
                    <tr>
                        <td><?php echo $order->ID; ?></td>
                        <td><?php echo $order->post_date; ?></td>
                        <td><?php echo esc_html($coupon); ?></td>
                        <td>R$ <?php echo number_format($discount, 2, ',', '.'); ?></td>
                        <td><?php echo esc_html($influencer_name); ?></td> <!-- Exibir nome do influenciador -->
                    </tr>
                    <?php
                }
                ?>
            </table>
            <?php
        }
        ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('coupon_report', 'coupon_report_shortcode');



// Adicionar o campo de influenciador no checkout
function add_influencer_field_to_checkout($checkout) {
    woocommerce_form_field('influencer_name', array(
        'type'          => 'text',
        'class'         => array('form-row-wide'),
        'label'         => __('Nome do Influenciador'),
        'placeholder'   => __('Informe o nome do influenciador'),
        'required'      => true,
    ), $checkout->get_value('influencer_name'));
}
add_action('woocommerce_after_order_notes', 'add_influencer_field_to_checkout');


// Salvar o nome do influenciador no pedido
function save_influencer_name_on_order($order_id) {
    if (!empty($_POST['influencer_name'])) {
        update_post_meta($order_id, '_influencer_name', sanitize_text_field($_POST['influencer_name']));
    }
}
add_action('woocommerce_checkout_update_order_meta', 'save_influencer_name_on_order');

function complete_order_report_shortcode() {
    ob_start();
    
    // Inicializa o logger do WooCommerce
    $logger = wc_get_logger();
    $log_context = ['source' => 'complete_order_report'];
    
    ?>
    <div class="order-report-container">
        <h2>Relatório Completo de Pedidos</h2>
        <form method="GET">
            <label for="start_date">Data Inicial:</label>
            <input type="date" id="start_date" name="start_date" value="<?php echo isset($_GET['start_date']) ? esc_attr($_GET['start_date']) : ''; ?>">
            
            <label for="end_date">Data Final:</label>
            <input type="date" id="end_date" name="end_date" value="<?php echo isset($_GET['end_date']) ? esc_attr($_GET['end_date']) : ''; ?>">
            
            <label for="order_status">Status do Pedido:</label>
            <select id="order_status" name="order_status">
                <option value="">Todos</option>
                <option value="wc-pending" <?php selected($_GET['order_status'] ?? '', 'wc-pending'); ?>>Pendente de Pagamento</option>
                <option value="wc-processing" <?php selected($_GET['order_status'] ?? '', 'wc-processing'); ?>>Processando</option>
                <option value="wc-completed" <?php selected($_GET['order_status'] ?? '', 'wc-completed'); ?>>Concluído</option>
                <option value="wc-on-hold" <?php selected($_GET['order_status'] ?? '', 'wc-on-hold'); ?>>Em Espera</option>
                <option value="wc-cancelled" <?php selected($_GET['order_status'] ?? '', 'wc-cancelled'); ?>>Cancelado</option>
                <option value="wc-refunded" <?php selected($_GET['order_status'] ?? '', 'wc-refunded'); ?>>Reembolsado</option>
                <option value="wc-failed" <?php selected($_GET['order_status'] ?? '', 'wc-failed'); ?>>Falha</option>
            </select>
            
            <button type="submit">Filtrar</button>
        </form>

        <?php
        if (isset($_GET['start_date']) || isset($_GET['end_date']) || isset($_GET['order_status'])) {
            global $wpdb;
            $query = "SELECT ID, post_date, post_status FROM {$wpdb->prefix}posts WHERE post_type = 'shop_order'";
            
            $conditions = [];
            $params = [];
            
            if (!empty($_GET['start_date'])) {
                $conditions[] = "post_date >= %s";
                $params[] = $_GET['start_date'];
            }
            if (!empty($_GET['end_date'])) {
                $conditions[] = "post_date <= %s";
                $params[] = $_GET['end_date'];
            }
            if (!empty($_GET['order_status'])) {
                $conditions[] = "post_status = %s";
                $params[] = $_GET['order_status'];
            }
            
            if (!empty($conditions)) {
                $query .= " AND " . implode(" AND ", $conditions);
            }
            
            $logger->info("Consulta SQL: $query", $log_context);
            
            $orders = $wpdb->get_results($wpdb->prepare($query, ...$params));
            
            $logger->info("Total de pedidos encontrados: " . count($orders), $log_context);
			
$logger->info("pedidos encontrados: " . json_encode($orders), $log_context);
            
            ?>
            <table border="1">
                <thead>
                    <tr>
                        <th>ID do Pedido</th>
                        <th>Data do Pedido</th>
                        <th>Status</th>
                        <th>Cupom Aplicado</th>
                        <th>Valor do Desconto</th>
                        <th>Nome do Influenciador</th>
                        <th>Nome do Cliente</th>
                        <th>Itens do Pedido</th>
                        <th>Valor Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($orders as $order) {
                        $order_obj = wc_get_order($order->ID);
                        $coupon = $order_obj->get_meta('_custom_discount_code');
                        $discount = $order_obj->get_meta('_custom_discount_value');
                        $influencer_name = $order_obj->get_meta('_influencer_name');
                        $customer_name = $order_obj->get_billing_first_name() . ' ' . $order_obj->get_billing_last_name();
                        $items = '';
                        
                        foreach ($order_obj->get_items() as $item) {
                            $product_name = $item->get_name();
                            $items .= $product_name . ' (x' . $item->get_quantity() . ')<br>';
                        }
                        
                        $total = $order_obj->get_total();
                        
                        $logger->info("Pedido {$order->ID} - Cliente: $customer_name, Total: R$ $total", $log_context);
                        ?>
                        <tr>
                            <td><?php echo $order->ID; ?></td>
                            <td><?php echo $order->post_date; ?></td>
                           <td><?php echo wc_get_order_status_name($order->post_status); ?></td>
                             <td><?php echo esc_html($coupon); ?></td>
                            <td>R$ <?php echo number_format($discount, 2, ',', '.'); ?></td>
                            <td><?php echo esc_html($influencer_name); ?></td>
                            <td><?php echo esc_html($customer_name); ?></td>
                            <td><?php echo $items; ?></td>
                            <td>R$ <?php echo number_format($total, 2, ',', '.'); ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
            <?php
        }
        ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('complete_order_report', 'complete_order_report_shortcode');


function complete_order_report_shortcode_jquery() {
    ob_start();
    
    // Inicializa o logger do WooCommerce
    $logger = wc_get_logger();
    $log_context = ['source' => 'complete_order_report'];
    
    ?>
    <div class="app-container">
        <aside class="sidebar open" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <span class="logo-text">3x Action</span>
                </div>
                <button class="toggle-button" id="toggleSidebar">
                    &#9776;
                </button>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="active"><a href="#dashboard">Dashboard</a></li>
                    <li><a href="/">Loja</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content sidebar-open" id="mainContent">
            <div class="dashboard-header">
                <h1>Dashboard de Pedidos</h1>
                <p>Gerencie todos os pedidos em um só lugar</p>
            </div>
           <div class="filters">
			  <form method="GET">
				<div class="filter-group">
				  <label for="start_date">Data Inicial:</label>
				  <input type="date" id="start_date" name="start_date" value="<?php echo isset($_GET['start_date']) ? esc_attr($_GET['start_date']) : ''; ?>">
				</div>

				<div class="filter-group">
				  <label for="end_date">Data Final:</label>
				  <input type="date" id="end_date" name="end_date" value="<?php echo isset($_GET['end_date']) ? esc_attr($_GET['end_date']) : ''; ?>">
				</div>

				<div class="filter-group">
				  <label for="order_status">Status do Pedido:</label>
				  <select id="order_status" name="order_status">
					<option value="">Todos</option>
					<option value="wc-pending" <?php selected($_GET['order_status'] ?? '', 'wc-pending'); ?>>Pendente</option>
					<option value="wc-processing" <?php selected($_GET['order_status'] ?? '', 'wc-processing'); ?>>Processando</option>
					<option value="wc-completed" <?php selected($_GET['order_status'] ?? '', 'wc-completed'); ?>>Concluído</option>
					<option value="wc-on-hold" <?php selected($_GET['order_status'] ?? '', 'wc-on-hold'); ?>>Em Espera</option>
					<option value="wc-cancelled" <?php selected($_GET['order_status'] ?? '', 'wc-cancelled'); ?>>Cancelado</option>
					<option value="wc-refunded" <?php selected($_GET['order_status'] ?? '', 'wc-refunded'); ?>>Reembolsado</option>
					<option value="wc-failed" <?php selected($_GET['order_status'] ?? '', 'wc-failed'); ?>>Falha</option>
				  </select>
				</div>

				<button type="submit" style="margin-bottom: 20px;">Filtrar</button>
			  </form>
			</div>

        <?php
        if (isset($_GET['start_date']) || isset($_GET['end_date']) || isset($_GET['order_status'])) {
            global $wpdb;
            $query = "SELECT ID, post_date, post_status FROM {$wpdb->prefix}posts WHERE post_type = 'shop_order'";
            
            $conditions = [];
            $params = [];
            
            if (!empty($_GET['start_date'])) {
                $conditions[] = "post_date >= %s";
                $params[] = $_GET['start_date'];
            }
            if (!empty($_GET['end_date'])) {
                $conditions[] = "post_date <= %s";
                $params[] = $_GET['end_date'];
            }
            if (!empty($_GET['order_status'])) {
                $conditions[] = "post_status = %s";
                $params[] = $_GET['order_status'];
            }
            
            if (!empty($conditions)) {
                $query .= " AND " . implode(" AND ", $conditions);
            }
            
            $logger->info("Consulta SQL: $query", $log_context);
            
            $orders = $wpdb->get_results($wpdb->prepare($query, ...$params));
            
            $logger->info("Total de pedidos encontrados: " . count($orders), $log_context);
            
            ?>
		
           	<table id="ordersTable" style="width:100%">
    <thead>
        <tr>
            <th>ID do Pedido</th>
            <th>Data do Pedido</th>
            <th>Status</th>
            <th>Cupom Aplicado</th>
            <th>Valor do Desconto</th>
            <th>Nome do Cliente</th>
            <th>E-mail do Cliente</th>
            <th>Telefone do Cliente</th>
            <th>Endereço de Cobrança</th>
            <th>Endereço de Entrega</th>
            <th>Itens do Pedido</th>
            <th>Método de Pagamento</th>
            <th>Método de Envio</th>
            <th>Valor Total</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($orders as $order) {
            $order_obj = wc_get_order($order->ID);
            
            // Cupom e desconto
            $coupon = $order_obj->get_meta('_custom_discount_code') ?: "--";
            $discount = is_numeric($order_obj->get_meta('_custom_discount_value')) ? number_format($order_obj->get_meta('_custom_discount_value'), 2, ',', '.') : "--";
            
            // Dados do cliente
            $customer_name = trim($order_obj->get_billing_first_name() . ' ' . $order_obj->get_billing_last_name()) ?: "--";
            $customer_email = $order_obj->get_billing_email() ?: "--";
            $customer_phone = $order_obj->get_billing_phone() ?: "--";
            $billing_address = $order_obj->get_formatted_billing_address() ?: "--";
            $shipping_address = $order_obj->get_formatted_shipping_address() ?: "--";
            
            // Itens do pedido
            $items = "";
            if (!empty($order_obj->get_items())) {
                foreach ($order_obj->get_items() as $item) {
                    $product_name = $item->get_name();
                    $quantity = $item->get_quantity();
                    $item_total = $item->get_total();
                    $items .= "{$product_name} (x{$quantity}) - R$ " . number_format($item_total, 2, ',', '.') . "<br>";
                }
            } else {
                $items = "--";
            }

            // Métodos de pagamento e envio
            $payment_method = $order_obj->get_payment_method_title() ?: "--";
            
            $shipping_methods = $order_obj->get_shipping_methods();
            $shipping_method = !empty($shipping_methods) ? reset($shipping_methods)->get_name() : "--";

            // Total do pedido
            $total = is_numeric($order_obj->get_total()) ? number_format($order_obj->get_total(), 2, ',', '.') : "--";

            // Log para debug
            $logger->info("Pedido {$order->ID} - Cliente: $customer_name, Total: R$ $total", $log_context);
            ?>
            <tr>
                <td><?php echo esc_html($order->ID); ?></td>
                <td><?php echo esc_html($order->post_date ?: "--"); ?></td>
                <td><?php echo esc_html(wc_get_order_status_name($order->post_status) ?: "--"); ?></td>
                <td><?php echo esc_html($coupon); ?></td>
                <td>R$ <?php echo $discount; ?></td>
                <td><?php echo esc_html($customer_name); ?></td>
                <td><?php echo esc_html($customer_email); ?></td>
                <td><?php echo esc_html($customer_phone); ?></td>
                <td><?php echo wp_kses_post($billing_address); ?></td>
                <td><?php echo wp_kses_post($shipping_address); ?></td>
                <td><?php echo $items; ?></td>
                <td><?php echo esc_html($payment_method); ?></td>
                <td><?php echo esc_html($shipping_method); ?></td>
                <td>R$ <?php echo $total; ?></td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>

            <?php
        }
        ?>
        </main>
    </div>

    <!-- Inclua as bibliotecas necessárias via CDN -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.6.2/css/buttons.dataTables.min.css">
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.2/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.2/js/buttons.html5.min.js"></script>

    <script type="text/javascript">
        jQuery(document).ready(function ($) {
			var table = $('#ordersTable').DataTable({
				dom: 'Bfrtip',
				buttons: ['excel'],
				language: {
					url: "//cdn.datatables.net/plug-ins/1.10.21/i18n/Portuguese-Brasil.json"
				}
			});

			var cupomFilter = $('#cupomFilter');
			if (cupomFilter.length) {
				cupomFilter.on('keyup', function () {
					table.column(3).search(this.value).draw();
				});
			}
			
			

			var toggleButton = document.getElementById("toggleSidebar");
			var sidebar = document.getElementById("sidebar");
			var mainContent = document.getElementById("mainContent");

			if (toggleButton && sidebar && mainContent) {
				toggleButton.addEventListener("click", function () {
					sidebar.classList.toggle("open");
					mainContent.classList.toggle("sidebar-open");
				});
			}
		});


    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('complete_order_report_test', 'complete_order_report_shortcode_jquery');




function order_influencer() {
    ob_start();
    
    // Inicializa o logger do WooCommerce
    $logger = wc_get_logger();
    $log_context = ['source' => 'complete_order_report'];
    
    ?>
    <div class="app-container">
        <aside class="sidebar open" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <span class="logo-text">3x Action</span>
                </div>
                <button class="toggle-button" id="toggleSidebar">
                    &#9776;
                </button>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="active"><a href="#dashboard">Dashboard</a></li>
                    <li><a href="/">Loja</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content sidebar-open" id="mainContent">
            <div class="dashboard-header">
                <h1>Dashboard de Pedidos</h1>
                <p>Gerencie todos os pedidos em um só lugar</p>
            </div>
           <div class="filters">
			  <form method="GET">
				<div class="filter-group">
				  <label for="start_date">Data Inicial:</label>
				  <input type="date" id="start_date" name="start_date" value="<?php echo isset($_GET['start_date']) ? esc_attr($_GET['start_date']) : ''; ?>">
				</div>

				<div class="filter-group">
				  <label for="end_date">Data Final:</label>
				  <input type="date" id="end_date" name="end_date" value="<?php echo isset($_GET['end_date']) ? esc_attr($_GET['end_date']) : ''; ?>">
				</div>

				<div class="filter-group">
				  <label for="order_status">Status do Pedido:</label>
				  <select id="order_status" name="order_status">
					<option value="">Todos</option>
					<option value="wc-pending" <?php selected($_GET['order_status'] ?? '', 'wc-pending'); ?>>Pendente</option>
					<option value="wc-processing" <?php selected($_GET['order_status'] ?? '', 'wc-processing'); ?>>Processando</option>
					<option value="wc-completed" <?php selected($_GET['order_status'] ?? '', 'wc-completed'); ?>>Concluído</option>
					<option value="wc-on-hold" <?php selected($_GET['order_status'] ?? '', 'wc-on-hold'); ?>>Em Espera</option>
					<option value="wc-cancelled" <?php selected($_GET['order_status'] ?? '', 'wc-cancelled'); ?>>Cancelado</option>
					<option value="wc-refunded" <?php selected($_GET['order_status'] ?? '', 'wc-refunded'); ?>>Reembolsado</option>
					<option value="wc-failed" <?php selected($_GET['order_status'] ?? '', 'wc-failed'); ?>>Falha</option>
				  </select>
				</div>

				<div class="filter-group">
				  <label for="coupon_code">Cupom Aplicado:</label>
				  <input type="text" id="coupon_code" name="coupon_code" value="<?php echo isset($_GET['coupon_code']) ? esc_attr($_GET['coupon_code']) : ''; ?>">
				</div>

				<button type="submit" style="margin-bottom: 20px;">Filtrar</button>
			  </form>
			</div>

        <?php
        if (isset($_GET['start_date']) || isset($_GET['end_date']) || isset($_GET['order_status']) || isset($_GET['coupon_code'])) {
            global $wpdb;
            $query = "SELECT p.ID, p.post_date, p.post_status 
              FROM {$wpdb->prefix}posts p
              LEFT JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id
              WHERE p.post_type = 'shop_order'";
            
            $conditions = [];
            $params = [];
            
           if (!empty($_GET['start_date'])) {
        $conditions[] = "p.post_date >= %s";
        $params[] = $_GET['start_date'];
    }
    if (!empty($_GET['end_date'])) {
        $conditions[] = "p.post_date <= %s";
        $params[] = $_GET['end_date'];
    }
    if (!empty($_GET['order_status'])) {
        $conditions[] = "p.post_status = %s";
        $params[] = $_GET['order_status'];
    }
    if (!empty($_GET['coupon_code'])) {
        $conditions[] = "pm.meta_key = '_custom_discount_code' 
                         AND pm.meta_value LIKE %s";
        $params[] = '%' . $wpdb->esc_like($_GET['coupon_code']) . '%';
    }

            
            if (!empty($conditions)) {
                $query .= " AND " . implode(" AND ", $conditions);
            }
            
            $logger->info("Consulta SQL: $query", $log_context);
            
            $orders = $wpdb->get_results($wpdb->prepare($query, ...$params));
            
            $logger->info("Total de pedidos encontrados: " . count($orders), $log_context);
            
            ?>
		
           	<table id="ordersTable" style="width:100%">
    <thead>
        <tr>
            <th>ID do Pedido</th>
            <th>Data do Pedido</th>
            <th>Status</th>
            <th>Cupom Aplicado</th>
            <th>Valor do Desconto</th>
            <th>Nome do Cliente</th>
            <th>E-mail do Cliente</th>
            <th>Telefone do Cliente</th>
            <th>Endereço de Cobrança</th>
            <th>Endereço de Entrega</th>
            <th>Itens do Pedido</th>
            <th>Método de Pagamento</th>
            <th>Método de Envio</th>
            <th>Valor Total</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($orders as $order) {
            $order_obj = wc_get_order($order->ID);
            
            // Cupom e desconto
            $coupon = $order_obj->get_meta('_custom_discount_code') ?: "--";
            $discount = is_numeric($order_obj->get_meta('_custom_discount_value')) ? number_format($order_obj->get_meta('_custom_discount_value'), 2, ',', '.') : "--";
            
            // Dados do cliente
            $customer_name = trim($order_obj->get_billing_first_name() . ' ' . $order_obj->get_billing_last_name()) ?: "--";
            $customer_email = $order_obj->get_billing_email() ?: "--";
            $customer_phone = $order_obj->get_billing_phone() ?: "--";
            $billing_address = $order_obj->get_formatted_billing_address() ?: "--";
            $shipping_address = $order_obj->get_formatted_shipping_address() ?: "--";
            
            // Itens do pedido
            $items = "";
            if (!empty($order_obj->get_items())) {
                foreach ($order_obj->get_items() as $item) {
                    $product_name = $item->get_name();
                    $quantity = $item->get_quantity();
                    $item_total = $item->get_total();
                    $items .= "{$product_name} (x{$quantity}) - R$ " . number_format($item_total, 2, ',', '.') . "<br>";
                }
            } else {
                $items = "--";
            }

            // Métodos de pagamento e envio
            $payment_method = $order_obj->get_payment_method_title() ?: "--";
            
            $shipping_methods = $order_obj->get_shipping_methods();
            $shipping_method = !empty($shipping_methods) ? reset($shipping_methods)->get_name() : "--";

            // Total do pedido
            $total = is_numeric($order_obj->get_total()) ? number_format($order_obj->get_total(), 2, ',', '.') : "--";

            // Log para debug
            $logger->info("Pedido {$order->ID} - Cliente: $customer_name, Total: R$ $total", $log_context);
            ?>
            <tr>
                <td><?php echo esc_html($order->ID); ?></td>
                <td><?php echo esc_html($order->post_date ?: "--"); ?></td>
                <td><?php echo esc_html(wc_get_order_status_name($order->post_status) ?: "--"); ?></td>
                <td><?php echo esc_html($coupon); ?></td>
                <td>R$ <?php echo $discount; ?></td>
                <td><?php echo esc_html($customer_name); ?></td>
                <td><?php echo esc_html($customer_email); ?></td>
                <td><?php echo esc_html($customer_phone); ?></td>
                <td><?php echo wp_kses_post($billing_address); ?></td>
                <td><?php echo wp_kses_post($shipping_address); ?></td>
                <td><?php echo $items; ?></td>
                <td><?php echo esc_html($payment_method); ?></td>
                <td><?php echo esc_html($shipping_method); ?></td>
                <td>R$ <?php echo $total; ?></td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>

            <?php
        }
        ?>
        </main>
    </div>

    <!-- Inclua as bibliotecas necessárias via CDN -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.6.2/css/buttons.dataTables.min.css">
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.2/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.2/js/buttons.html5.min.js"></script>

    <script type="text/javascript">
        jQuery(document).ready(function ($) {
			var table = $('#ordersTable').DataTable({
				dom: 'Bfrtip',
				buttons: ['excel'],
				language: {
					url: "//cdn.datatables.net/plug-ins/1.10.21/i18n/Portuguese-Brasil.json"
				}
			});

		});
    </script>
    <?php
    return ob_get_clean();
}

add_shortcode('order_influencer', 'order_influencer');


