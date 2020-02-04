<html>
<head>
	<meta charset="utf-8">
	<title>Mon Foutu programme en PHP</title>
</head>
<body>
	<table>
		<td><form action="" method="post"><input type="submit" name="RealtimeValue" value="Realtime Value"/></form></td>
		<td><form action="" method="post"><input type="submit" name="Download" value="Download File"/></form></td>
	</table>
	<table>
		<form action="" method="post">
			<label for="chkDebut"><input type="checkbox" name="chkDebut" id="chkDebut"/>Ajouter une date de Début</label>
			<tr id="debut" style="display: none">
				<td align="center">Date de Debut</td>
				<td>--> Jour : <input type="text" name="DayD" value="31" style='width:30px'/></td>
				<td> Mois : <input type="text" name="MonthD" value="01" style='width:30px'/></td>
				<td> Année : <input type="text" name="YearD" value="2020" style='width:50px'/></td>
				<td> Heure : <input type="text" name="HourD" value="13" style='width:30px'/></td>
				<td> Minute : <input type="text" name="MinuteD" value="07" style='width:30px'/></td>
			</tr>
			</br>
			<label for="chkFin"><input type="checkbox" name="chkFin" id="chkFin"/>Ajouter une date de Fin</label>
			<tr id="fin" style="display: none">
				<td align="center">Date de Fin</td>
				<td>--> Jour : <input type="text" name="DayF" value="02" style='width:30px'/></td>
				<td> Mois : <input type="text" name="MonthF" value="02" style='width:30px'/></td>
				<td> Année : <input type="text" name="YearF" value="2020" style='width:50px'/></td>
				<td> Heure : <input type="text" name="HourF" value="16" style='width:30px'/></td>
				<td> Minute : <input type="text" name="MinuteF" value="12" style='width:30px'/></td>
			</tr>
			<tr><td colspan=7><input type="submit" name="History" value="History"></td></tr>
			<?php echo "<script type='text/javascript'>
				$(function () {
					$('#chkDebut').click(function () {
						if ($(this).is(':checked')) { $('#debut').show(); }
						else { $('#debut').hide(); }
					});
					$('#chkFin').click(function () {
						if ($(this).is(':checked')) { $('#fin').show(); }
						else { $('#fin').hide(); }
					});
				});
			</script>"; ?>
		</form>
	</table></br>
	<table>
		<?php
		$readFile = fopen("/data/DataBase.csv", "r");
		$nbline = count(file("/data/DataBase.csv"));
		
		if(isset($_POST['RealtimeValue'])){
			echo "<tr>";
			echo "<th width=20%>Date</th>";
			echo "<th width=10%>Heure</th>";
			echo "<th width=30%>Température (°C)</th>";
			echo "<th width=20%>Pression (hPa)</th>";
                        echo "<th width=10%>MIN</th>";
                        echo "<th width=10%>MAX</th>";
			echo "</tr>";
			for($i = 0; $i < $nbline; $i++) {
				$element = fgetcsv($readFile, 0, ";", "\n");

				if($i == 0) {
					$min = $element[5];
					$max = $element[5];
				} else {
					if($element[5] < $min)
						$min = $element[5];
					if($element[5] > $max)
						$max = $element[5];
				}

				if($i == $nbline - 1) {
					echo "<tr>";
					echo '<td align="center">';
					for($j = 0; $j < 7; $j++) {
						if($j == 3 || $j == 5 || $j == 6) { echo '</td><td align="center">'; }
						echo $element[$j];
						if($j < 2) { echo "/"; }
						if($j == 3) { echo ":"; }
					}
					echo '</td><td align="center">' .$min. '</td><td align="center">' .$max. "</td>";
				}
			}
			echo "</tr>";
		}

		if(isset($_POST['History'])){
			$DAYD	= $_POST['DayD'];
			$DAYF	= $_POST['DayF'];
			$MONTHD	= $_POST['MonthD'];
			$MONTHF	= $_POST['MonthF'];
			$YEARD	= $_POST['YearD'];
			$YEARF	= $_POST['YearF'];
			$HOURD	= $_POST['HourD'];
			$HOURF	= $_POST['HourF'];
			$MIND	= $_POST['MinuteD'];
			$MINF	= $_POST['MinuteF'];
			
			echo "<tr>";
			echo "<th width=25%>Date</th>";
                        echo "<th width=10%>Heure</th>";
                        echo "<th width=40%>Température (°C)</th>";
                        echo "<th width=25%>Pression (Pa)</th>";
                        echo "</tr>";

                        for($i = 0; $i < $nbline; $i++) {
				$element = fgetcsv($readFile, 0, ";", "\n");

				if(isset($_POST['chkDebut'])) {
					if(isset($_POST['chkFin'])) {
						if($element[0] >= $YEARD && $element[1] >= $MONTHD && $element[2] >= $DAYD && $element[3] >= $HOURD &&
						   $element[4] >= $MIND && $element[0] <= $YEARF && $element[1] <= $MONTHF && $element[2] <= $DAYF &&
						   $element[3] <= $HOURF && $element[4] <= $MINF) {
							echo "<tr>";
							echo '<td align="center">';
							for($j = 0; $j < 7; $j++) {
								if($j == 3 || $j == 5 || $j == 6) { echo '</td><td align="center">'; }
								echo $element[$j];
								if($j < 2) { echo "/"; }
								if($j == 3) { echo ":"; }
							}
							echo "</td></tr>";
						}
					} else {
						if($element[0] >= $YEARD && $element[1] >= $MONTHD && $element[2] >= $DAYD && $element[3] >= $HOURD &&
						   $element[4] >= $MIND) {
							echo "<tr>";
							echo '<td align="center">';
                                                        for($j = 0; $j < 7; $j++) { 
                                                                if($j == 3 || $j == 5 || $j == 6) { echo '</td><td align="center">'; }
                                                                echo $element[$j];
                                                                if($j < 2) { echo "/"; }
                                                                if($j == 3) { echo ":"; }
                                                        }
                                                        echo "</td></tr>";
						}
					}
				} elseif(isset($_POST['chkFin'])) {
					if($element[0] <= $YEARF && $element[1] <= $MONTHF && $element[2] <= $DAYF && $element[3] <= $HOURF && $element[4] <= $MINF) {
						echo "<tr>";
						echo '<td align="center">';
                                                for($j = 0; $j < 7; $j++) {
                                                	if($j == 3 || $j == 5 || $j == 6) { echo '</td><td align="center">'; }
                                                        echo $element[$j];
                                                        if($j < 2) { echo "/"; }
                                                	if($j == 3) { echo ":"; }
						}
						echo "</td></tr>";
					}
				} else {
					echo "<tr>";
					echo '<td align="center">';
					for($j = 0; $j < 7; $j++) {
						if($j == 3 || $j == 5 || $j == 6) { echo '</td><td align="center">'; }
						echo $element[$j];
						if($j < 2) { echo "/"; }
						if($j == 3) { echo ":"; }
					}
					echo "</td></tr>";
				}
                        }
                }
		
		if(isset($_POST['Download'])){
			$new_csv = fopen('/data/DLDB.csv', 'w');
			for($i = 0; $i < $nbline; $i++) {                                                                             
                                $element = fgetcsv($readFile, 0, ";", "\n");
				
				for($j = 0; $j < 7; $j++) {                                                      
                                	$var .= $element[$j];
					$var .= ";";                                                       
                                }
				$var .= "\n";                                                                    
                       		fputcsv($new_csv, $var); 
			}
			fclose($new_csv);
			
			// output headers so that the file is downloaded rather than displayed
			header("Content-type: text/csv");
			header("Content-disposition: attachment; filename = DLDB.csv");
			readfile("/data/DLDB.csv");
         	}
		readFile.fclose()
		?>
	</table>
</body>
</html>
