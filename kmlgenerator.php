<?php
require 'class.php';
$trafikanten=new trafikanten;

if(!is_numeric($argv[1]))
{
	$info=$trafikanten->GetMatches($argv[1]);
	if(is_array($info))
	{
		echo "Multiple stops found for \"{$argv[1]}\". Please be more specific or use stop id.\n";
		echo "Stops found:\n";
		foreach($info as $stop)
		{
			if($stop->Type!='Stop')
				continue;
			echo "$stop->Name ($stop->ID) ($stop->District)\n";
		}
		die();
	}
	else
	{
		$stopid=$info->ID;
		$stop=$info;
	}
}
else
{
	$stopid=$argv[1];
	$stop=$trafikanten->soap->GetStopByID(array('stopID'=>$stopid))->GetStopByIDResult;
	//print_r($stop);
}

	 $output="Departures from $stop->Name:\n";
		$departures=$trafikanten->soap->GetAlldepartures(array('stopid'=>$stopid,'departuretime'=>date('c')));
		foreach($departures->GetAllDeparturesResult->MonitoredStopVisit as $departure)
		{
			//print_r($departure);
			$output.="$departure->PublishedLineName $departure->DestinationName ($departure->VehicleJourneyName)\n";
			if(isset($argv[2]) && strlen($argv[2])>=4 && $argv[2]==$departure->VehicleJourneyName)
			{
				$filename=$trafikanten->filnavn("$departure->PublishedLineName $departure->DestinationName.kml");
				$kml=$trafikanten->kmlstoplist($trafikanten->stoplist($argv[2]));
				file_put_contents($filename,$kml);
				echo "Stops written to $filename\n";
				break;
			}
			/*if(!isset($jorneys[$departure->LineRef]))
				$journeys[$departure->LineRef]=$departure->VehicleJourneyName;*/
		}
	
	//print_r($trafikanten->journeysfromstop($stopid));

if(!isset($argv[2]))
	echo $output;