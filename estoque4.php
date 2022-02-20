<?php
	session_start();
	$user = @$_SESSION['user'];
	if (!(isset($_SESSION['user']) and ($user<>''))){
		header("Location: index.php");
		exit;
	}
	
	require("../../conf_plos.php");
	require("../conf_utils.php");

/*
	https://jsfiddle.net/gh/get/library/pure/highcharts/highcharts/tree/master/samples/highcharts/demo/line-time-series
	https://www.highcharts.com/demo/line-time-series
	SELECT year(data_peticao),count(*) FROM `despachos_pag` WHERE tipo_peticao='200' group by year(data_peticao)
	valores corretos de 2007 em diante
	segundo AECON em 2021 foram um total de 26921 depósitos de patentes, o despachos pag indica 26880

	https://www.gov.br/inpi/pt-br/acesso-a-informacao/dados-abertos/arquivos/documentos/boletim-mensal-de-propriedade-industrial
	https://www.gov.br/inpi/pt-br/central-de-conteudo/estatisticas-e-estudos-economicos/estatisticas-1/estatisticas_aecon
	https://antigo.mctic.gov.br/mctic/opencms/indicadores/detalhe/Patentes/INPI/6.1.3.html concessoes
	https://antigo.mctic.gov.br/mctic/opencms/indicadores/detalhe/Patentes/INPI/6.1.1.html depósitos
						
*/


	if (empty($_REQUEST["tipo"])) {$tipo='depositos';} else {$tipo=$_REQUEST["tipo"];}
	if ($tipo=='depositos') 
	{
		$titulo = 'Depósitos de patentes por ano (PI, MU, CA)';
		$subtitulo = 'Depósitos';
	}
	if ($tipo=='concessoes')
	{
		$titulo = 'Patentes concedidas por ano (PI, MU, CA)';
		$subtitulo = 'Patentes';
	}
?>
<!doctype html>
<html>
  <head>
	<meta charset="utf-8">
	<link rel="stylesheet" type="text/css" href="css/estoque4d.css">
	<link rel="icon" href="imagens/favicon2.png">
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
		
	<script src="https://code.highcharts.com/highcharts.js"></script>
	<script src="https://code.highcharts.com/modules/exporting.js"></script>
	<script src="https://code.highcharts.com/modules/export-data.js"></script>
	<script src="https://code.highcharts.com/modules/accessibility.js"></script>
	
	<figure class="highcharts-figure">
		<div id="container"></div>
		<p class="highcharts-description">
		<?php 
		if ($tipo=='depositos') 
			echo "Fonte: https://antigo.mctic.gov.br/mctic/opencms/indicadores/detalhe/Patentes/INPI/6.1.1.html"; 
		if ($tipo=='concessoes') 
			echo "Fonte: https://antigo.mctic.gov.br/mctic/opencms/indicadores/detalhe/Patentes/INPI/6.1.3.html"; 
		?>
		</p>
	</figure>

	<?php
		$i = 0;$eixox='';$eixoy='';
		$cmd = "select * from estoque_ano where ano>=2000 order by ano asc";
		$res = mysqli_query($link,$cmd);
		while ($line=@mysqli_fetch_assoc($res))
		{
			$ano = $line['ano'];
			$depositos = $line['depositos'];
			$concessoes = $line['concessoes'];
			if ($tipo=='depositos') $valor=$depositos;
			if ($tipo=='concessoes') $valor=$concessoes;
			if ($i==0)
			{
				$eixox = "'$ano'";
				$eixoy = "$valor";
			}
			else
			{
				$eixox = $eixox.",'$ano'";
				$eixoy = $eixoy.",$valor";
			}
			$i++;
		}
	?>
	
	<script>
Highcharts.chart('container', {
    chart: {
        type: 'column'
    },
    title: {
        text: '<?php echo $titulo; ?>'
    },
    subtitle: {
        text: 'Fonte: AECON/INPI e MCTI'
    },
    xAxis: {
        categories: [
            <?php echo $eixox; ?>
        ],
        crosshair: true
    },
    yAxis: {
        min: 0,
        title: {
            text: 'Qtde'
        }
    },
    tooltip: {
        headerFormat: '<span style="font-size:8px">{point.key}</span><table>',
        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y:.0f}</b></td></tr>',
        footerFormat: '</table>',
        shared: true,
        useHTML: true
    },
    plotOptions: {
        column: {
            pointPadding: 0.2,
            borderWidth: 2
        }
    },
    series: [{
        name: '<?php echo $subtitulo; ?>',
        data: [<?php echo $eixoy; ?>]

    }]
});
	</script>


  </body>
</html>
