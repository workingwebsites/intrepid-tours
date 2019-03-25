<?php

$ptTourFeed = 'http://api.peakadventuretravel.com/Feed/FeedService.svc/product/Intrepid/FD85E08B-68D1-497A-8F36-8CA80552107B';
$localTourFeed = 'data/tour_feed.xml';
$jsonFile = 'data/tour_feed.json';
$iNumRecords = 50;

ini_set('max_execution_time', 0);

function getTourFeed(){
  //What data are we using?
  global $ptTourFeed;
  global $localTourFeed;

  //So things don't get weird about ssl
  $contextOptions = array(
    "ssl" => array(
      "verify_peer"      => false,
      "verify_peer_name" => false,
    ),
  );
  $context = stream_context_create($contextOptions);

  //Get the data and stream it to the local machine
  $src = fopen($ptTourFeed, 'r', FALSE, $context);
  $dest1 = fopen($localTourFeed, 'w');

  //How much was copied over
  $numBytes = stream_copy_to_stream($src, $dest1);

  //Close it down?
  fclose($src);
  fclose($dest1);

  //Return
  return $numBytes;
} // end getTourFeed()


function getTourSummary(){
  //Returns array of summary of xml tour

  global $localTourFeed;

  //Set up the product (trip) reader
  $xmlProduct = new XMLReader;
  $xmlProduct->open($localTourFeed);
  $arProduct = array();

  //Find the products
  while ($xmlProduct->read() && $xmlProduct->name !== 'product');

  //Parse the products
  while ($xmlProduct->name === 'product'){
    //Get the node in SimpleXML
    $node = new SimpleXMLElement($xmlProduct->readOuterXML());

    //Build Array
    $tmpArray['summary'] = $node->summary;
    $tmpArray['countrylist'] = $node->countryList;

    $arProduct[] = $tmpArray;

    //On to the next one
    $xmlProduct->next('product');
  }

  //Return results
  return $arProduct;
} // end getTourSummary()


function dateFull($date){
//Returns date in MMM d YYYY
  if(empty($date)){
    return NULL;
  }
  return date ('F j Y', strtotime($date));
}


function getTourArray(){
//Returns array of valid tours from $ptTourFeed
  $arTourList = getTourSummary();

  //Build tour list
  //$todayDate = mktime();
  $todayDate = time();
  $arValidTrips = array();

  foreach ($arTourList as $objTour) {
    //Check tour is currently valid
    $validFrom = strtotime($objTour['summary']->validFrom);
    $validTo = strtotime($objTour['summary']->validTo);

    if($todayDate > $validFrom && $todayDate < $validTo){
      $arValidTrips[] = (string) $objTour['summary']->productCode;
    }
  }

  //Return
  return $arValidTrips;
} // end getTourArray()


function writeJson($iStart = 0){
  //Writes JSON file to display
  global $jsonFile;
  global $iNumRecords;

  $xmlProductDetails = new XMLReader;
  $arTours = getTourSummary();
  $arValidTours = getTourArray();

  //If it's a new run, clear out file
  if($iStart == 0){
    $clearFile = true;
  }else{
    $clearFile = false;
  }


  //Where to stop
  $lengthValidTours = count($arValidTours);
  $iEnd = min($iStart+$iNumRecords, $lengthValidTours);

  $recRemain = $lengthValidTours - $iEnd;

  //Go through list
  for ($i = $iStart; $i < $iEnd; $i++){
    $tour = $arValidTours[$i];

    //Make sure there's no security issues with data
    $contextOptions = array(
      "ssl" => array(
        "verify_peer"      => false,
        "verify_peer_name" => false,
      ),
    );

    $context = stream_context_create($contextOptions);
    libxml_set_streams_context($context);

    //Get product departures etc.
    $xmlDetailFeed = 'https://api.peakadventuretravel.com/Feed/FeedService.svc/availability/Individual/Intrepid/FD85E08B-68D1-497A-8F36-8CA80552107B/'.$tour;
    $xmlProductDetails->open($xmlDetailFeed);

    //Find the departurs for this one
    while ($xmlProductDetails->read() && $xmlProductDetails->name !== 'departure');
    while ($xmlProductDetails->name === 'departure'){
      $nodeDetails = new SimpleXMLElement($xmlProductDetails->readOuterXML());

      //Weed out the bad ones
      if($nodeDetails->statuses->bookingClosed === 'true'){
        break;
      }

      if(empty($nodeDetails->statuses->availability)){
        break;
      }

      if(strtotime($nodeDetails->summary->startDate) > strtotime("+2 month")){
        break;
      }

      if(!$nodeDetails->pricesList->prices[1]->discount){
        break;
      }


      //Get the tourinfo and build the array
      $arProduct = array();
      foreach ($arTours as $key => $tour) {

        if((string)$nodeDetails->summary->productCode === (string)$tour['summary']->productCode){
          //Build Array
          $arProduct['tour'] = $tour;
          $arProduct['tourdetails'] = $nodeDetails;

          $strJson = json_encode($arProduct);

          //Add / Append  to the file
          if($clearFile == true){
            file_put_contents($jsonFile, $strJson);
            $clearFile = false;
          }else{
            file_put_contents($jsonFile, ', '.$strJson, FILE_APPEND);
          }

          break;
        }
      }
      //only need the first entry
      break;
    }

    //Close this
    $xmlProductDetails->close();
  }  // end foreach $arValidTours

  //Return results
  $arResults['lastrec'] = $i;
  $arResults['remain'] = $recRemain;
  return $arResults;
} // end writeJson()



//===== GET DATA  =====//

//Get start record passed
$startRec = isset($_GET["startrec"])? $_GET["startrec"] : 0;

//If new feed, get latest XML file
if($startRec == 0){
  getTourFeed();

}

//Build JSON data
$results  = writeJson($startRec);

//Do next batch
if($results['remain'] > 0){

  if($_SERVER['HTTPS'] == 'off'){
    $strHTTP = 'http';
  }else{
    $strHTTP = 'https';
  }
  $strURL = $strHTTP.'://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'];

  header('Location: '.$strURL.'?startrec='.$results['lastrec']);
  exit;
}else{
  //DEBUG //
  echo "<pre>";
  print_r($results);
  echo "</pre>";
}

?>
