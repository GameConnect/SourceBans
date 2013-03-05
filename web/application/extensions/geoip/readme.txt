Installation of the component:
* Extract the release file under `protected/extensions`
* Change main.php configuration file
  'components' => array(
      ...
      'geoip' => array(
          'class' => 'application.extensions.geoip.CGeoIP',
          // specify filename location for the corresponding database
          'filename' => 'C:\path\to\GeoIP\GeoLiteCity.dat',
          // Choose MEMORY_CACHE or STANDARD mode
          'mode' => 'STANDARD',
      ),
      ...
  ),

-----------------------------------------------
Usage instructions:
All methods accept an IP address as an argument.
If no argument is supplied CHttpRequest::getUserHostAddress() is used.

  $location = Yii::app()->geoip->lookupLocation();
  $countryCode = Yii::app()->geoip->lookupCountryCode();
  $countryName = Yii::app()->geoip->lookupCountryName();
  $org = Yii::app()->geoip->lookupOrg();
  $regionCode = Yii::app()->geoip->lookupRegion();

Location attributes:
  $location->countryCode
  $location->countryCode3
  $location->countryName
  $location->region
  $location->regionName
  $location->city
  $location->postalCode
  $location->latitude
  $location->longitude
  $location->areaCode
  $location->dmaCode

-----------------------------------------------
How to update Maxmind Free DBs example:
#updateGeoIP.sh
#this script will only download if there is a new version of the database
  cd /usr/local/share/GeoIP
  wget -N -q http://geolite.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz
  wget -N -q http://geolite.maxmind.com/download/geoip/database/GeoLiteCountry/GeoIP.dat.gz
  wget -N -q http://geolite.maxmind.com/download/geoip/database/GeoIPv6.dat.gz

  gunzip -c GeoLiteCity.dat.gz > GeoLiteCity.dat
  gunzip -c GeoIP.dat.gz > GeoIP.dat
  gunzip -c GeoIPv6.dat.gz > GeoIPv6.dat

#Setup a cron job to run this script monthly.