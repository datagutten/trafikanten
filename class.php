<?Php
class trafikanten
{
	public $soap;
	function __construct()
	{
		$this->soap=new SoapClient("http://reis.trafikanten.no/topp2009/Topp2009WS.asmx?WSDL",array('trace' => 1));
	}
	function requestdate($timestamp=false)
	{
		if($timestamp===false)
			$timestamp=time();
		return date('dmYHi',$timestamp);
	}
	function endeholdeplasser($lineid)
	{
		$stopinfo=$this->soap->GetStopsByLineID(array('lineid'=>$lineid))->GetStopsByLineIDResult;
		//print_r($stopinfo);
		$stopid=$stopinfo->Stop[0]->ID;
		$linenames=array('83');
		foreach ($this->soap->GetDepartures(array('stopid'=>$stopid,'departAfter'=>date('c')))->GetDeparturesResult->TravelStage as $departure) ///*,'linenames'=>$linenames*/
		{
			if($departure->LineName==$lineid)
				break;
		}
		return(array($departure->DepartureStop,$departure->ArrivalStop));
				
		//print_r($this->soap->GetDepartures(array('stopid'=>$stopid,'departAfter'=>date('c'))));
		
	}
	function journeysfromstop($stopid)
	{
		$departures=$this->soap->GetAlldepartures(array('stopid'=>$stopid,'departuretime'=>date('c')));
		foreach($departures->GetAllDeparturesResult->MonitoredStopVisit as $departure)
		{
			//echo "LineRef: {$departure->LineRef}\nVehicleJourneyName: {$departure->VehicleJourneyName}\n\n";
			if(!isset($jorneys[$departure->LineRef]))
				$journeys[$departure->LineRef]=$departure->VehicleJourneyName;
		}
		return $journeys;
	}
	function GetMatches($searchName)
	{
		$result=$this->soap->GetMatches(array('searchName'=>$searchName));
		$return=$result->GetMatchesResult->Place;
		return $return;
	}
	
	function stoplist($trip)
	{
		return $this->soap->GetTrip(array('tripid'=>$trip,'dt'=>date('c')))->GetTripResult->Stops->Stop;
	}
	function kmlstoplist($stoplist)
	{
		require_once 'UTMtoGeog.php';
		$xml="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$xml.="<kml xmlns=\"http://www.opengis.net/kml/2.2\">\n";
		$xml.="  <Document>\n";
		foreach($stoplist as $stop)
		{
			$latlon=UTMtoGeog($stop->X,$stop->Y,32);

			$latlon=implode(',',array_reverse($latlon));
			//print_r($stop);
			$xml.="  <Placemark>\n";
			$xml.="    <name>{$stop->Name}</name>\n";
			$xml.="    <description></description>\n";
			$xml.="    <Point>\n";
			$xml.="      <coordinates>$latlon,0.000000</coordinates>\n";
			$xml.="    </Point>\n";
			$xml.="  </Placemark>\n";
		}
		$xml.="  </Document>\n";
		$xml.="</kml>";
		return $xml;
	}
	function filnavn($file) //Tilpass tilnavn for windows
	{
		if(substr($file,-1,1)=='.')
			$file=substr($file,0,-1); //Windows liker ikke filnavn som slutter med punktum
		$file=str_replace(array(':','?','*','|','<','>','/','\\','"'),array('-','','','','','','','',''),$file); //Fjern tegn som ikke kan brukes i filnavn på windows
		if(PHP_OS=='WINNT')
			$file=utf8_decode($file);
		return $file;
	}
}