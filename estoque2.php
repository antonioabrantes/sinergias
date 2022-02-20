<?php
	session_start();
	$user = @$_SESSION['user'];
	if (!(isset($_SESSION['user']) and ($user<>''))){
		header("Location: index.php");
		exit;
	}
	
	require("../../conf_plos.php");
	require("../conf_utils.php");
	// https://jsfiddle.net/gh/get/library/pure/highcharts/highcharts/tree/master/samples/highcharts/demo/line-time-series
	// https://www.highcharts.com/demo/line-time-series
	// SELECT year(data_peticao),count(*) FROM `despachos_pag` WHERE tipo_peticao='200' group by year(data_peticao)
	// valores corretos de 2007 em diante
	// 
	// segundo AECON em 2021 foram um total de 26921 depósitos de patentes, o despachos pag indica 26880
	
/*
https://www.gov.br/inpi/pt-br/central-de-conteudo/estatisticas-e-estudos-economicos/estatisticas-1/estatisticas_aecon
Tabelas Completas dos Indicadores de Propriedade Industrial 2020
Ano		PI 		MU 		CA 	Total									
2000	17444	3332	78	20854									
2001	17907	3558	90	21555									
2002	16685	3546	103	20334									
2003	16410	3640	126	20176									
2004	16707	3602	122	20431									
2005	18486	3243	123	21852									
2006	19851	3181	120	23152									
2007	21656	3044	140	24840									
2008	23120	3392	129	26641									
2009	22383	3378	124	25885									
2010	24986	3005	108	28099									
2011	28658	3134	89	31881									
2012	30435	3010	124	33569									
2013	30877	3035	134	34046									
2014	30341	2734	106	33181									
2015	30217	2719	106	33042									
2016	28009	2937	74	31020									
2017	25658	2918	91	28667									
2018	24857	2587	107	27551									
2019	25397	2823	98	28318									
*/


	if (empty($_REQUEST["op"])) {$op=0;} else {$op=$_REQUEST["op"];}
	if (empty($_REQUEST["tipo"])) {$tipo="estoque";} else {$tipo=$_REQUEST["tipo"];}
	$filename = "data/".$tipo.".json";
	if ($tipo=='estoque') 
	{
		$titulo = 'Estoque de pedidos não decididos (2006-2021)';
		$titulo_eixoy = 'Qtde de pedidos';
		$filename = "data/estoque2008.json";
	}
	if ($tipo=='depositos') 
	{
		$titulo = 'Depósitos mensais de pedidos de patente';
		$titulo_eixoy = 'Qtde de pedidos';
	}
	if ($tipo=='tempo_concessoes') 
	{
		$titulo = 'Tempo de concessão de cartas patentes (PI)';
		$titulo_eixoy = 'Anos';
	}

?>
<!doctype html>
<html>
  <head>
	<meta charset="utf-8">
	<script src="https://code.highcharts.com/highcharts.js"></script>
	<script src="https://code.highcharts.com/modules/data.js"></script>
	<script src="https://code.highcharts.com/modules/exporting.js"></script>
	<script src="https://code.highcharts.com/modules/export-data.js"></script>
	<script src="https://code.highcharts.com/modules/accessibility.js"></script>
	<link rel="stylesheet" type="text/css" href="css/estoque4d.css">
	<link rel="icon" href="imagens/favicon2.png">
	<script>
		Highcharts.getJSON(
			'<?php echo $filename; ?>',
			function (data) {

				Highcharts.chart('container', {
					chart: {
						zoomType: 'x'
					},
					title: {
						text: '<?php echo $titulo; ?>'
					},
					subtitle: {
						text: document.ontouchstart === undefined ?
							'Clique e arraste na figura para dar zoom' : 'Clique e arraste na figura para dar zoom'
					},
					xAxis: {
						type: 'datetime'
					},
					yAxis: {
						title: {
							text: '<?php echo $titulo_eixoy; ?>'
						}
					},
					legend: {
						enabled: false
					},
					plotOptions: {
						area: {
							fillColor: {
								linearGradient: {
									x1: 0,
									y1: 0,
									x2: 0,
									y2: 1
								},
								stops: [
									[0, Highcharts.getOptions().colors[0]],
									[1, Highcharts.color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
								]
							},
							marker: {
								radius: 2
							},
							lineWidth: 1,
							states: {
								hover: {
									lineWidth: 1
								}
							},
							threshold: null
						}
					},

					series: [{
						type: 'area',
						name: 'Qtde',
						data: data
					}]
				});
			}
		);
	</script>
  </head>
  
  <body>
  
		<center>
		<ul id="navegacao">
			<li>
				<a href="menu.php">Início</a>
			</li>
			<li>
				<a href="sobre.html">Sobre</a>
			</li>
			<li id="atual">
				<a href="estatistica.php">Estatísticas</a>
			</li>
			<li>
				<a href="../plos/plos.php">Cientistas</a>
			</li>
			<li>
				<a href="justica.php">Justiça</a>
			</li>
		</ul>
		</center>
		<BR><BR>
		
<?php
  
		/* data no formato json: usdeur.json
		[
		[
			1167609600000,
			0.7537
		],
		[
			1167696000000,
			0.7537
		]
		]
		
		dados originais: https://cdn.jsdelivr.net/gh/highcharts/highcharts@v7.0.0/samples/data/usdeur.json
		https://cientistaspatentes.com.br/central/data/estoque.json
		converter timestamp em data: https://www.epochconverter.com/
		echo '1167609600000='.strtotime('2007-01-01'); exit(); // 1167609600000=1167606000
		*/
	
		if ($op=='2') // converte dados da tabela estoque (atualizada por central/control.php?action=123) em um arquivo JSON
		{			  // https://www.epochconverter.com/ converter timestamp em data por ex: 473382000000 (milisec) => 31/12/1984
			@ $fp = fopen($filename,"w"); // 208-01-01 => 1199155245000
			if (!$fp)
				echo "Não foi identificado o arquivo texto $fname";
			else
			{
				$i = 0;$str='';
				if ($tipo=='depositos')
					$cmd = "select * from estoque where year(data)>=2006 order by data asc";
				else
					$cmd = "select * from estoque where 1 order by data asc";

				$res = mysqli_query($link,$cmd);
				while ($line=@mysqli_fetch_assoc($res))
				{
					$data = $line['data'];
					$estoque = $line['estoque']; ///184000;
					$depositos = $line['depositos'];
					$tempo_concessoes = $line['tempo_concessoes'];
					if ($tipo=='estoque') $valor=$estoque;
					if ($tipo=='depositos') $valor=$depositos;
					if ($tipo=='tempo_concessoes') $valor=$tempo_concessoes;
					$timestamp = 1000*strtotime($data); // JSON deve gravar os valores em milisegundos
					if ($i==0)
						$str = $str."[\n\t[\n\t\t$timestamp,\n\t\t$valor\n\t]";
					else
						$str = $str.",\n\t[\n\t\t$timestamp,\n\t\t$valor\n\t]";

					$i++;
				}
				$str = $str."\n]";
				fputs($fp,$str);
				fclose($fp);
				echo "Fim do processamento";
				exit();
			}
		}
		
  ?>
  
	<figure class="highcharts-figure">
		<div id="container"></div>
		<p class="highcharts-description">
			Fonte: Dados publicados na RPI
		</p>
	</figure>

  </body>
</html>
