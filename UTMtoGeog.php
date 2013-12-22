<?Php
//Original version: http://www.uwgb.edu/dutchs/usefuldata/ConvertUTMNoOZ.HTM
function UTMtoGeog($Easting,$Northing,$UtmZone,$SouthofEquator=false) //Convert UTM Coordinates to Geographic
{
	//Declarations
	//Symbols as used in USGS PP 1395: Map Projections - A Working Manual
	$k0 = 0.9996;//scale on central meridian
	$a = 6378137.0;//equatorial radius, meters. 
	$f = 1/298.2572236;//polar flattening.
	$b = $a*(1-$f);//polar axis.
	$e = sqrt(1 - $b*$b/$a*$a);//eccentricity
	$drad = pi()/180;//Convert degrees to radians)
	$phi = 0;//latitude (north +, south -), but uses phi in reference
	$e0 = $e/sqrt(1 - $e*$e);//e prime in reference

	$lng = 0;//Longitude (e = +, w = -)
	$lng0 = 0;//longitude of central meridian
	$lngd = 0;//longitude in degrees
	$M = 0;//M requires calculation
	$x = 0;//x coordinate
	$y = 0;//y coordinate
	$k = 1;//local scale
	$zcm = 0;//zone central meridian
	//End declarations


	//Convert UTM Coordinates to Geographic

	$k0 = 0.9996;//scale on central meridian
	$b = $a*(1-$f);//polar axis.
	$e = sqrt(1 - ($b/$a)*($b/$a));//eccentricity
	$e0 = $e/sqrt(1 - $e*$e);//Called e prime in reference
	$esq =(1 - ($b/$a)*($b/$a));//e squared for use in expansions
	$e0sq =$e*$e/(1-$e*$e);// e0 squared - always even powers
	$x = $Easting;
	if ($x<160000 || $x>840000)
		echo "Outside permissible range of easting values \n Results may be unreliable \n Use with caution\n";
	$y = $Northing;
	if ($y<0)
		echo "Negative values not allowed \n Results may be unreliable \n Use with caution\n";
	if ($y>10000000)
		echo "Northing may not exceed 10,000,000 \n Results may be unreliable \n Use with caution\n";

	$zcm =3 + 6*($UtmZone-1) - 180;//Central meridian of zone
	$e1 =(1 - sqrt(1 - $e*$e))/(1 + sqrt(1 - $e*$e));//Called e1 in USGS PP 1395 also
	$M0 =0;//In case origin other than zero lat - not needed for standard UTM
	$M =$M0 + $y/$k0;//Arc length along standard meridian. 
	if ($SouthofEquator === true)
		$M=$M0+($y-10000000)/$k;
	$mu =$M/($a*(1 - $esq*(1/4 + $esq*(3/64 + 5*$esq/256))));
	$phi1 =$mu + $e1*(3/2 - 27*$e1*$e1/32)*sin(2*$mu) + $e1*$e1*(21/16 -55*$e1*$e1/32)*sin(4*$mu);//Footprint Latitude
	$phi1 =$phi1 + $e1*$e1*$e1*(sin(6*$mu)*151/96 + $e1*sin(8*$mu)*1097/512);
	$C1 =$e0sq*pow(cos($phi1),2);
	$T1 =pow(tan($phi1),2);
	$N1 =$a/sqrt(1-pow($e*sin($phi1),2));
	$R1 =$N1*(1-$e*$e)/(1-pow($e*sin($phi1),2));
	$D =($x-500000)/($N1*$k0);
	$phi =($D*$D)*(1/2 - $D*$D*(5 + 3*$T1 + 10*$C1 - 4*$C1*$C1 - 9*$e0sq)/24);
		$phi =$phi + pow($D,6)*(61 + 90*$T1 + 298*$C1 + 45*$T1*$T1 -252*$e0sq - 3*$C1*$C1)/720;
		$phi =$phi1 - ($N1*tan($phi1)/$R1)*$phi;
				
	//Longitude
	$lng =$D*(1 + $D*$D*((-1 -2*$T1 -$C1)/6 + $D*$D*(5 - 2*$C1 + 28*$T1 - 3*$C1*$C1 +8*$e0sq + 24*$T1*$T1)/120))/cos($phi1);
	$lngd = $zcm+$lng/$drad;
	

	return array(floor(1000000*$phi/$drad)/1000000,floor(1000000*$lngd)/1000000); //Latitude,Longitude
}
?>