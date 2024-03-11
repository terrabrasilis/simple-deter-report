<html>
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="css/estilo.css" type="text/css">
</head>
<body>

<?php
    error_reporting(E_ALL ^ E_NOTICE);
	ini_set('display_erros', true);
	require("config/config.inc.php");

	$deter_table = "public.deter_auth";

	$connection_string = "host=$host port=$port dbname=$dbname user=$user password=$password";
	$bdcon = @pg_connect($connection_string);

	if ($bdcon === false) {
		echo '<p align="center"><font size="6" face="Verdana"><b>Falha de comunicação com o banco de dados.</b></font><br><br>';
		echo '<p align="center"><font size="3" face="Verdana">Não foi possível gerar o relatório. Tente mais tarde.</font>';
		echo '</body></html>';
		exit;
	}

	$body = "
	<p align=\"center\"><font size=\"6\" face=\"Verdana\"> <b>Resumo DETER Pantanal</B></font>
	<br><br>
	<div class=\"main-txt\">
	<p align=\"left\"><font>
	O Sistema de Detecção de Desmatamento em Tempo Real (DETER) tem como objetivo detectar e enviar avisos de supressão 
	e de degradação de vegetação primária, para dar suporte à fiscalização em biomas brasileiros. O sistema DETER teve 
	início na Amazônia em 2004 e foi essencial para o controle e redução do desmatamento, que atingia níveis recordes 
	naquele momento. Diante dos níveis crescentes de supressão da vegetação natural do Cerrado, em 2018 foi criado o 
	DETER Cerrado. <br><br>

	A supressão da vegetação nativa no Pantanal tem apresentado níveis elevados nos últimos anos e, por essa razão, 
	houve a necessidade de se criar também um sistema DETER para o monitoramento deste bioma, 
	que teve início em 1º de agosto de 2023. O DETER Pantanal utiliza imagens dos satélites Amazônia 1, 
	CBERS 4 e CBERS 4A (sensor Wide Field Imaging Camera - WFI), para detectar avisos de supressão e cicatrizes de queimadas, com 
	revisita completa no bioma, em média, a cada três dias (e revisitas parciais a cada um ou dois dias).
	<br><br>
	<b>Observação: Trata-se de uma versão do DETER em fase de consolidação e são esperadas alterações no dados em relação a versão final a ser disponibilizada no portal Terrabrasilis.</b>
	</div>
	<br><br>
	<div class=\"res-txt\">
	";

	echo "$body";

	$classe_cr1 = 'supressão com vegetação';
	$classe_cr2 = 'supressão com solo exposto';
	$classe_dg1 = 'cicatriz de queimada';

	// atualiza municipio/uf na tabela deter
	$query = "UPDATE public.deter_current  as dt";
	$query .= " SET municipio = mun.nome, uf = mun.uf";
	$query .= " FROM public.municipalities_pantanal_biome as mun ";
	$query .= " WHERE ST_INTERSECTS(dt.geom, mun.geom)";
	$query .= " AND dt.uf is NULL";
	$result = @pg_query($bdcon, $query);

	// sql para desmate CR no periodo
	$query = "SELECT sum(area_km) as area, ";
	$query .= " min(view_date) as mindate, max(view_date) as maxdate";
	$query .= " FROM $deter_table";
	$query .= " WHERE class_name in ('$classe_cr1', '$classe_cr2')";

	//$submitted = 0;
	$result = @pg_query($bdcon, $query);
	$numlinhas = @pg_num_rows($result);
	$row = @pg_fetch_array($result);
	$area_cr = number_format($row["area"], 2, '.', '');
	$mindate = $row["mindate"];
	$maxdate = $row["maxdate"];

	echo "<p><b>Alertas de Desmatamento: $area_cr km² entre $mindate e $maxdate<br><br>";
		
	// sql para Degrad no periodo
	$query = "SELECT sum(area_km) as area, ";
	$query .= " min(view_date) as mindate, max(view_date) as maxdate";
	$query .= " FROM $deter_table";
	$query .= " WHERE class_name in ('$classe_dg1')";

	//$submitted = 0;
	$result = @pg_query($bdcon, $query);
	$numlinhas = @pg_num_rows($result);
	$row = @pg_fetch_array($result);
	$area_deg = number_format($row["area"], 2, '.', '');
	$mindate = $row["mindate"];
	$maxdate = $row["maxdate"];
	//echo "Alertas de Degradação: $area_deg km² entre $mindate e $maxdate<br><br>";

	// sql para desmate CR desde 1 do mês
	$aux = sscanf ($maxdate, "%4s-%2s-%2s");
	$data1 = $aux[0]."-".$aux[1]."-01";
	$data2 = $maxdate;
	$query = 	"SELECT sum(area_km) as area FROM $deter_table";
	$query .= " where view_date >= '$data1' and view_date <= '$data2'";
	$query .= " and class_name in ('$classe_cr1', '$classe_cr2')";
	//echo "$query <br>";

	//$submitted = 0;
	$result = @pg_query($bdcon, $query);
	$numlinhas = @pg_num_rows($result);
	$row = @pg_fetch_array($result);
	$area_cr = number_format($row["area"], 2, '.', '');
	echo "Alertas de Desmatamento: $area_cr km² entre $data1 e $data2<br><br>";

	// sql para desmate Degrad desde 1 do mês
	$query = 	"SELECT sum(area_km) as area FROM $deter_table";
	$query .= " where view_date >= '$data1' and view_date <= '$data2'";
	$query .= " and class_name in ('$classe_dg1')";
	//echo "$query <br>";

	$result = @pg_query($bdcon, $query);
	$row = @pg_fetch_array($result);
	$numlinhas = @pg_num_rows($result);
	$area_deg = number_format($row["area"], 2, '.', '');
	//echo "Alertas de Degradação: $area_deg km² entre $data1 e $data2<br><br>";

	echo "</p>";
	echo "</div>";

	echo "<p align=\"center\"><b><font> Alertas de Desmatamento e Degradação agrupados por mês</font><br>";

	// sql para agrupar por mês
	$query = 	"SELECT extract(year from view_date) as ano,extract(month from view_date) as mes,";
	$query .= " class_name as classe, sum(area_km) as area FROM $deter_table WHERE class_name not in ('$classe_dg1')";
	$query .= " GROUP BY 1,2,3";
	$result = @pg_query($bdcon, $query);
	//echo "$query <br>";

	echo "<table align=\"center\">";
	echo "<tr><b>";
	echo " <td> Ano </td>";
	echo " <td> Mês </td>";
	echo " <td> Classe </td>";
	echo " <td> Área km² </td>";
	echo "</b></tr>";
	echo "<tr>";

	while ($row = @pg_fetch_array($result))
	{
		$ano = $row["ano"];
		$mes = $row["mes"];
		$classe = ucwords($row["classe"]);
		$area = number_format($row["area"], 2, '.', '');
		echo " <td nowrap>$ano  </td>";
		echo " <td nowrap>$mes  </td>";
		echo " <td nowrap>$classe </td>";
		echo " <td nowrap> $area  </td>";
		echo "</tr>";	
	}
		
	echo "<br></table>";

	echo "<p align=\"center\"><b><font>Municípios com maiores áreas detectadas de Desmatamento entre $data1 e $data2</font><br>";

	// sql para desmate CR no periodo por municipo
	$query = "select municipio as mun, uf as uf, sum(area_km) as area";
	$query .= " from $deter_table ";
	$query .= " WHERE class_name in ('$classe_cr1', '$classe_cr2')";
	$query .= " AND view_date >= '$data1' AND view_date <= '$data2'";
	$query .= " group by 1,2 order by area desc limit 15";
	// echo "$query <br>";

	echo "<table align=\"center\">";
	echo "<tr><b>";
	echo " <td> Nr. </td>";
	echo " <td> Município </td>";
	echo " <td> UF </td>";
	echo " <td> Área km² </td>";
	echo "</b></tr>";
	echo "<tr>";

	//$submitted = 0;
	$result = @pg_query($bdcon, $query);
	$numlinhas = @pg_num_rows($result);
	$conta = 1;

	while ($row = @pg_fetch_array($result))
	{
		$municipio = $row["mun"];
		$uf = $row["uf"];
		$area = number_format($row["area"], 2, '.', '');
		echo " <td nowrap> $conta  </td>";
		echo " <td nowrap> $municipio  </td>";
		echo " <td nowrap> $uf  </td>";
		echo " <td nowrap> $area </td>";
		echo "</tr>";	
		$conta++;
	}
	echo "<br></table>";
?>
</body>
</html>
