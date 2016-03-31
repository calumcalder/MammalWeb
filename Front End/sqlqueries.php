<?php
include("sql.php");

$totalAnimalsQuery = "SELECT COUNT(*) FROM Options WHERE struc = 'mammal' OR struc = 'bird';";
$totalImagesQuery = "SELECT COUNT(*) FROM Photo";
$totalImagesAddedTodayQuery = "SELECT COUNT(*) FROM Photo WHERE DATE(taken) = CURRDATE();";
$newlyClassifiedImagesQuery = "SELECT COUNT(*) FROM Photo WHERE (DATE(taken) = CURRDATE()) AND (classification_id != -1);";
$mostCommonSpeciesQuery = "SELECT COUNT(*), option_name FROM Photo JOIN Options ON classification_id = option_id GROUP BY classification_id ORDER BY COUNT(*) DESC;";
$totalNeedingAttentionQuery = "SELECT COUNT(*);"; //Fix this!
$totalBlanksQuery = "SELECT COUNT(*) FROM Photo WHERE classification_id = 86;";

$totalAnimalsResult = $sqlConnection->query($totalAnimalsQuery);
$totalImagesResult = $sqlConnection->query($totalImagesQuery);
$newlyClassifiedImagesResult = $sqlConnection->query($newlyClassifiedImagesQuery);
$totalImagesAddedTodayResult = $sqlConnection->query($totalImagesAddedTodayQuery);
$mostCommonSpeciesResult = $sqlConnection->query($mostCommonSpeciesQuery);
$totalBlanksResult = $sqlConnection->query($totalBlanksQuery);



$algorithmStatus = 3; // = $sqlConnection->query($algorithmStatusQuery);
$totalNeedingAttentionResult = $sqlConnection->query($totalNeedingAttentionQuery);



//Total animals query
if ($totalAnimalsResult->num_rows > 0)
	{
	while ($row = $totalAnimalsResult->fetch_assoc())
		{
		$totalAnimals = $row["COUNT(*)"];
		}
	}
  else
	{
	$totalAnimals = "No animals found";
	}


//Total images query
if ($totalImagesResult->num_rows > 0)
	{
	while ($row = $totalImagesResult->fetch_assoc())
		{
		$totalImages = $row["COUNT(*)"];
		}
	}
  else
	{
	$totalImages = "No images found";
	}

//Newly classified images query
if ($newlyClassifiedImagesResult->num_rows > 0)
	{
	while ($row = $newlyClassifiedImagesResult->fetch_assoc())
		{
		$newlyClassifiedImages = $row["COUNT(*)"];
		}
	}
  else
	{
	$newlyClassifiedImages = "No images recently classified";
	}


//Total images added today query
if ($totalImagesAddedTodayResult->num_rows > 0)
	{
	while ($row = $totalImagesAddedTodayResult->fetch_assoc())
		{
		$totalImagesAddedToday = $row["COUNT(*)"];
		}
	}
  else
	{
	$totalImagesAddedToday = "No images added today";
	}


//Most common species query
if ($mostCommonSpeciesResult->num_rows > 0)
	{ 
        $row = $mostCommonSpeciesResult->fetch_assoc();
		
		$mostCommonSpecies = $row["option_name"];
		}
	
  else
	{
	$mostCommonSpecies = "No most common species";
	}


//Total images needing attention query
if ($totalNeedingAttentionResult->num_rows > 0)
	{
	while ($row = $totalNeedingAttentionResult->fetch_assoc())
		{
		$totalNeedingAttention = $row["COUNT(*)"];
		}
	}
  else
	{
	$totalNeedingAttention = "None";
	}


//Total blanks query
if ($totalBlanksResult->num_rows > 0)
	{
	while ($row = $totalBlanksResult->fetch_assoc())
		{
		$totalBlanks = $row["COUNT(*)"];
		}
	}
  else
	{
	$totalBlanks = "None";
	}
?>