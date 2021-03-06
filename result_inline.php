<form action="search.php">
<input type="submit" value="Back" /><br />
</form>

<?php

include 'database.php';

$tune = "./tune/";
$chordPro = "./chordpro/";

$con = connect();

if (!empty($_POST['searchTxt']))
{

	$searchTxtUpper = sanitize($_POST['searchTxt']);
	$noPunctuation = preg_replace("/[^A-Za-z0-9]/", " ", $searchTxtUpper);

	if ($_POST['filter'] == "contains")
		$sql=mysql_query("SELECT * FROM song WHERE UPPER(songTitle) LIKE \"%$noPunctuation%\" OR UPPER(songChorus) LIKE \"%$noPunctuation%\" ORDER BY songTitle") or die (mysql_error());
	else if ($_POST['filter'] == "exact")
		$sql=mysql_query("SELECT * FROM song WHERE UPPER(songTitle) = \"$searchTxtUpper\" OR UPPER(songChorus) = \"$searchTxtUpper\" ORDER BY songTitle") or die (mysql_error());
	else if ($_POST['filter'] == "begins")
		$sql=mysql_query("SELECT * FROM song WHERE UPPER(songTitle) LIKE \"$searchTxtUpper%\" OR UPPER(songChorus) LIKE \"$searchTxtUpper%\" ORDER BY songTitle") or die (mysql_error());
		
	displayResults($sql);
}
else if (!empty($_POST['searchLyricsInline']))
{
	$keyWords = sanitize($_POST['searchLyricsInline']);
	$results = array();
	
	//sql query for all songs
	$sql = "SELECT * FROM song ORDER BY songTitle";
	
	$temp = mysql_query($sql, $con) or die (mysql_error());
	
	//iterate through row['chordPro']
	while ($row = mysql_fetch_array($temp))
	{
		$line = $row['chordPro'];
		$dir = $chordPro . $line;
		//read .txt file into string
		$file = file_get_contents($dir) or die("Can't open $dir");
		//$fileNoChords = preg_replace("/\[[A-Za-z#]*[0-9]*\]/", "", $file);
		$fileNoChords = preg_replace("/\[(.*?)\]/", "", $file);
		
		$fileNoPunct = preg_replace("/[\",.!;:'-]/", "", $fileNoChords);
		$keyWordsNoPunct = preg_replace("/[\",.!;:'-]/", "", $keyWords);
		
		$fileNoDash = preg_replace("/�/", " ", $fileNoPunct);
		
		$fileNoBraces = preg_replace("/\{(.*?)\}/", "", $fileNoDash);
		$fileNoStanzaNum = preg_replace("/(\s)*[0-9](\s)+/", "", $fileNoBraces);
		$fileNoSpaces = preg_replace("/\s/", "", $fileNoStanzaNum);
		
		$keyWordsNoSpaces = preg_replace("/\s/", "", $keyWordsNoPunct);
		
		//do strpos to see if string contains keywords
		if (strpos(strtoupper($fileNoSpaces), strtoupper($keyWordsNoSpaces)) !== FALSE)
		{
			//add sid to results
			array_push($results, $row['sid']);
		}
	}
	
	//displayResults
	displayColumns();
	for ($i = 0; $i < sizeof($results); $i++)
	{
		$sid = $results[$i];
		$tempSQL = "SELECT * from song WHERE sid = $sid";
		$tempRow = mysql_query($tempSQL, $con);
		$row = mysql_fetch_array($tempRow);
		
		echo "<tr>";
		echo "<td>" . $row['songTitle'] . "</td>";
		echo "<td>" . $row['songChorus'] . "</td>";
		echo "<td><object height=\"50px\" width=\"100px\" data=\"./tune/" . $row['tune'] . "\" /><param name=\"autoplay\" value=\"false\"></object></td>";
		$tempLyrics = displayLyrics($row['chordPro']);
		echo $tempLyrics;
		echo "<td>" . $row['author'] . "</td>";
		echo "<td>" . $row['strum'] . "</td>";
		echo "</tr>";
	}
	echo "</table>";
}
else
{
	print "No search string specified";
}
close($con);
?>

<form action="search.php">
<input type="submit" value="Back" /><br />
</form>