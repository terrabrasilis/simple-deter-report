<html>
<head>
<meta charset="utf-8" />
<body>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="css/estilo.css" type="text/css">
<!-- <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css"> -->

<style>
th, td {
  padding: 5px;
  text-align: center;
  border-bottom: 1px solid #ddd;
  font-size: 12px;
}

tr:hover {background-color:#ffffff;}

table {
  border-collapse: collapse;
}

.p1 {
  font-family: "Times New Roman", Times, serif;
  font-size: 20px;
}

body {
  background-color: white;
}
.main-txt{
    
}
</style>
</head>

<?php
require("config.inc.php");
ini_set('display_erros', true);

$body = "
<p align=\"center\"><font size=\"6\" face=\"Verdana\"> <b>Resumo DETER Pantanal</B></font>
<br><br>
<div class=\"main-txt\">
<p align=\"left\"><font size=\"2\" face=\"Verdana\">
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
";

echo "$body";

$mesarray = array("0","Janeiro","Fevereiro","Marco","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro");

$classe_cr1 = 'supressão com vegetação';
$classe_cr2 = 'supressão com solo exposto';
$classe_dg1 = 'cicatriz de queimada';

// sql para desmate CR no periodo
	$query = "SELECT sum(area_km) as area, ";
	$query .= " min(view_date) as mindate, max(view_date) as maxdate";
	$query .= " FROM $deter_table";
	$query .= " WHERE class_name in ('$classe_cr1', '$classe_cr2')";
	
		//$submitted = 0;
		$result = pg_query($bdcon, $query);
		$numlinhas = pg_num_rows($result);
		$row = pg_fetch_array($result);
		$area_cr = number_format($row["area"], 2, '.', '');
		$mindate = $row["mindate"];
		$maxdate = $row["maxdate"];
		
		echo "<p align=\"center\"><b><font size=\"2\" face=\"Verdana\"> Alertas de Desmatamento: $area_cr km2 entre $mindate e $maxdate<br><br>";

			 
// sql para Degrad no periodo
	$query = "SELECT sum(area_km) as area, ";
	$query .= " min(view_date) as mindate, max(view_date) as maxdate";
	$query .= " FROM $deter_table";
	$query .= " WHERE class_name in ('$classe_dg1')";
	
	//$submitted = 0;
	$result = pg_query($bdcon, $query);
	$numlinhas = pg_num_rows($result);
	$row = pg_fetch_array($result);
	$area_deg = number_format($row["area"], 2, '.', '');
	$mindate = $row["mindate"];
	$maxdate = $row["maxdate"];
	echo "<font size=\"2\" face=\"Verdana\"> Alertas de Degradacao: $area_deg km2 entre $mindate e $maxdate<br><br>";

// sql para desmate CR desde 1 do mes
	$aux = sscanf ($maxdate, "%4s-%2s-%2s");
    $data1 = $aux[0]."-".$aux[1]."-01";
    $data2 = $maxdate;
	$query = 	"SELECT sum(area_km) as area FROM $deter_table";
	$query .= " where view_date >= '$data1' and view_date <= '$data2'";
	$query .= " and class_name in ('$classe_cr1', '$classe_cr2')";
    //echo "$query <br>";
	
	//$submitted = 0;
	$result = pg_query($bdcon, $query);
	$numlinhas = pg_num_rows($result);
	$row = pg_fetch_array($result);
	$area_cr = number_format($row["area"], 2, '.', '');
	echo "<font size=\"2\" face=\"Verdana\"> Alertas de Desmatamento: $area_cr km2 entre $data1 e $data2<br><br>";

// sql para desmate Degrad desde 1 do mes
	$query = 	"SELECT sum(area_km) as area FROM $deter_table";
	$query .= " where view_date >= '$data1' and view_date <= '$data2'";
	$query .= " and class_name in ('$classe_dg1')";
    //echo "$query <br>";
    
	$result = pg_query($bdcon, $query);
	$row = pg_fetch_array($result);
	$numlinhas = pg_num_rows($result);
	$area_deg = number_format($row["area"], 2, '.', '');
	echo "<font size=\"2\" face=\"Verdana\"> Alertas de Degradacao: $area_deg km2 entre $data1 e $data2<br><br>";
	
	echo "<p align=\"center\"><b><font size=\"2\" face=\"Verdana\"> Alertas de Desmatamento e Degradação agrupados por mes <br>";
	
// sql para agrupar por mes
	$query = 	"SELECT extract(year from view_date) as ano,extract(month from view_date) as mes,";
	$query .= " class_name as classe, sum(area_km) as area FROM $deter_table";
	$query .= " GROUP BY 1,2,3";
	$result = pg_query($bdcon, $query);
    //echo "$query <br>";
    
    echo "<table align=\"center\">";
	echo "<tr><b>";
	echo " <td> Ano</font> </td>";
	echo " <td> Mes</font> </td>";
	echo " <td> Classe</font> </td>";
	echo " <td> Area km2</font> </td>";
	echo "</b></tr>";
	echo "<tr>";
	
	   	while ($row = pg_fetch_array($result))
		{
		$ano = $row["ano"];
		$mes = $row["mes"];
		$classe = ucwords($row["classe"]);
		$area = number_format($row["area"], 2, '.', '');
		echo " <td nowrap>$ano </font> </td>";
		echo " <td nowrap>$mes </font> </td>";
		echo " <td nowrap>$classe</font></td>";
		echo " <td nowrap> $area </font> </td>";
  		echo "</tr>";	
		}
		
	echo "<br></table>";
	
	echo "<p align=\"center\"><b><font size=\"2\" face=\"Verdana\"> 15 municipios com maiores areas detectadas de Desmatamento entre $data1 e $data2<br>";

// sql para desmate CR no periodo por municipo
	$query = 	"select municipio as mun, uf as uf, sum(area_km) as area";
	$query .= " from $deter_table ";
	$query .= " WHERE class_name in ('$classe_cr1', '$classe_cr2')";
	$query .= " group by 1,2 order by area desc limit 15";
   // echo "$query <br>";
  
    echo "<table align=\"center\">";
	echo "<tr><b>";
	echo " <td> Nr.</font> </td>";
	echo " <td> Municipio</font> </td>";
	echo " <td> UF</font> </td>";
	echo " <td> Area km2</font> </td>";
	echo "</b></tr>";
	echo "<tr>";
	
	//$submitted = 0;
	$result = pg_query($bdcon, $query);
	$numlinhas = pg_num_rows($result);
	$conta = 1;

		while ($row = pg_fetch_array($result))
		{
		$municipio = $row["mun"];
		$uf = $row["uf"];
		$classe = $row["classe"];
		$area = number_format($row["area"], 2, '.', '');
		echo " <td nowrap> $conta </font> </td>";
		echo " <td nowrap> $municipio </font></td>";
		echo " <td nowrap> $uf </font> </td>";
		echo " <td nowrap> $area</font> </td>";
  		echo "</tr>";	
		$conta++;
		}
	  echo "<br></table>";
?>
</body>
</html>
