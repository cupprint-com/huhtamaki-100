<?php
if(!defined('CP_HUHTAMAKI_RUNNING')){
    exit(1);
}

require_once   getcwd() . '/config/includes.php';

class UPS{
    
    public function calculateFreight($data=[]){
        
        $result=[];
        # build the request element
        
        $request=$this->prepare($data);
        #return $request; #json_decode($request,true);
        $result=$this->post($request);
       
        # response needs to be parsed to work out the correct charges to apply
        $result=$this->parseResponse($data,$result);
        
        return $result;
    }
    
    
    
    
    private function parseResponse($data=[],$response=[]){
        $result=[];
        #echo "<br/>";
        #print_r($response);
        #echo "<br/>";
        #default is that an error of some sort occurred and this notifies the calling function that we should not flag this quotation as 'ok' nor should we add freight costs
        $result['errors']=1;
        $result['message']=_('Unable to parse UPS api response');
       # check that we have a RateResponse 
        if (!array_key_exists('RateResponse', $response)){
            $result['message']=_('No RateResponse Key');
           # if there is no 'RateResponse' key there should be a 'response' key with errors
            if (!array_key_exists('response', $response)){
                $result['message']=_('No response Key');
                # however if there is no 'response' key then something very weird happening and we need to notify support@cupprint.com 
                # TODO write a function that will send an email to support@cupprint.com containing details of $data in html format
                return $result;
            }
            # check if there is an 'error's key 
            if (!array_key_exists('errors', $response['response'])){
                $result['message']=_('No errors Key');
                #  if there is no 'errors' key then we need to notify support@cupprint.com
                # TODO write a function that will send an email to support@cupprint.com containing details of $data in html format
                return $result;
            }
            $error=$response['response']['errors'][0]; # take first error
            #TODO send alert to support@cupprint.com with details of the error as per UPS api
            $code=$error['code'];
            $message=$error['message'];
            $result['ups']=$error;
            return $result;
            
        }
        # if we get to here then rate response should be usable
        $status=$response['RateResponse']['Response']['ResponseStatus']['Code'];
        if ($status!='1'){
            #TODO notify support if the result status is not 1
            return $result;
        }
        
        # RatedShipment will contain one or more 'rates'
        $services=$response['RateResponse']['RatedShipment'];
        
        $preferredService=[]; # empty container for the preferred RatedShipment element
        
        $cheapestRate=999999; # set 'cheapest' rate to very high
        foreach($services as $option){
            #echo "<br/>";
            #print_r($option);
            #echo "<br/>";
            
            # if we have valid NegotiatedRateCharges 
            if (array_key_exists('NegotiatedRateCharges', $option)){
                # check for the value of the NegotiatedRateCharges
                if (array_key_exists('TotalCharge', $option['NegotiatedRateCharges'])){
                    $charge=$option['NegotiatedRateCharges']['TotalCharge']['MonetaryValue'];
                    if ($cheapestRate  > $charge){
                        $result['errors']=0;
                        $result['message']='';
                        $preferredService=[];
                        $preferredService['service']=$option['Service']['Code'];
                        $preferredService['fee']=$charge;
                        $preferredService['price']=$charge * UPS_FREIGHT_MARKUP;
                        $cheapestRate=$charge;
                    }
                }
            }
        }
        $result['service']=$preferredService;
        return $result;
        
    }
    
    /**
     * Prepares the JSON body that is used to invoke the UPS api
     * G:\Digital\automation\huhtamaki 100\ups\Developers Guide\Rating Package RESTful Developer Guide.pdf
     * @param array $data
     * @return string
     */
    private function prepare($data=[]){
        $result=[];
        $rateRequest=[];
        $rateRequest['Request']=$this->buildRequestElement($data);
        $rateRequest['Shipment']=$this->buildShipmentElement($data);
        
        $result["RateRequest"]=$rateRequest;
        return json_encode($result);
        
    }
    
    /**
     * Creates the 'shipment' elements used in request to UPS api
     * @param array $data
     * @return string[]
     */
    private function buildShipmentElement($data=[]){
        $result=[];
        $result['ShipmentRatingOptions']=$this->shipmentRatingOptions($data);
        $result['Shipper']=$this->shipperElement(1);
        $result['ShipTo']=$this->shipToElement($data);
        $result['ShipFrom']=$this->shipperElement(0);
        $result['Service']=$this->serviceElement($data);
        $result['ShipmentTotalWeight']=$this->shipmentTotalWeight($data);
        $result['Package']=$this->packageElement($data);
        return $result;
        
    }
    
    
    private function shipmentTotalWeight($data=[]){
        $result=[];
        
        $settings=$this->calculateWeightsAndPackages($data);
        
        $result['UnitOfMeasurement']=[];
        $result['UnitOfMeasurement']['Code']='KGS';
        $result['UnitOfMeasurement']['Description']='Kilograms';
        $result['Weight']='' . $settings['weight'];
        return $result;
        
    }
    /**
     * Calculates the number of packages, dimensional weight & total weight
     * @param array $data
     * @return number[]
     */
    private function calculateWeightsAndPackages($data=[]){
        $result=[];
        # calculate the 'weight' by working out how many 8oz packages are required and how many 12oz packages are required
        $cpc8 = $data['cpc8dwQuantity'];
        $cpc12 = $data['cpc12dwQuantity'];
        $cpc8packages=0;
        $cpc12packages=0;
        $cpc8Weight=0;
        $cpc12Weight=0;
        if ($cpc8 > 0){
            $cpc8packages=$cpc8/500;
            $cpc8Weight=($cpc8packages * CPC8DW_WEIGHT);
        }
        if ($cpc12 > 0){
            $cpc12packages=$cpc12/500;
            $cpc12Weight=($cpc12packages * CPC12DW_WEIGHT);
        }
        $weight=($cpc8packages * CPC8DW_WEIGHT) + ($cpc12packages * CPC12DW_WEIGHT);
        $weight=round($weight);
        
        $result['cpc8packages']=$cpc8packages;
        $result['cpc8Weight']=$cpc8Weight;
        
        $result['cpc12packages']=$cpc12packages;
        $result['cpc12Weight']=$cpc12Weight;
        
        $result['weight']=$weight;
        return $result;
        
    }
    
    
    private function packageElement($data=[]){
        $result=[];
        $settings=$this->calculateWeightsAndPackages($data);
        $length=CPC8DW_PACKAGE_LENGTH;
        $width=CPC8DW_PACKAGE_WIDTH;
        $height=CPC8DW_PACKAGE_HEIGHT;
        if ($data['cpc8dwQuantity'] > 0){
          $type=[];
          $type=$this->packagingTypeElement($length, $width, $height, $settings['cpc8Weight']);
          $result[]=$type;
        }
        
        $length=CPC12DW_PACKAGE_LENGTH;
        $width=CPC12DW_PACKAGE_WIDTH;
        $height=CPC12DW_PACKAGE_HEIGHT;
        if ($data['cpc12dwQuantity'] > 0){
            $type=[];
            $type=$this->packagingTypeElement($length, $width, $height, $settings['cpc12Weight']);
            $result[]=$type;
        }
       
        return $result;
    }
    
    private function packagingTypeElement($length='',$width='',$height='',$weight=''){
        $result=[];
        $type=[];
        
        $type['Code']='02';
        $type['Description']='Package';
        $result['PackagingType']=$type;
        $result['Dimensions']=[];
        $result['Dimensions']['UnitOfMeasurement']=[];
        $result['Dimensions']['UnitOfMeasurement']['Code']='CM';
        $result['Dimensions']['Length']=$length;
        $result['Dimensions']['Width']=$width;
        $result['Dimensions']['Height']=$height;
        $result['PackageWeight']=[];
        $result['PackageWeight']['UnitOfMeasurement']=[];
        $result['PackageWeight']['UnitOfMeasurement']['Code']='KGS';
        $result['PackageWeight']['Weight']='' .round($weight);
        return $result;
    }
    
    private function serviceElement($data=[]){
        $result=[];
        $code='03'; # ups service code for ground shipments
        
        $result['Code']=$code;
        return $result;
    }
    
    /**
     * Creates the ShipmentRatingOptions object, note that we require an empty node for NegotiatedRatesIndicator, this ensures that we get back our own negotiated rates rather than public rates
     * @param array $data
     * @return string[]|stdClass[]
     */
    private function shipmentRatingOptions($data=[]){
        $result=[];
        $result['UserLevelDiscountIndicator']="TRUE";
        $result['NegotiatedRatesIndicator']=new stdClass;
        return $result;
        
    }
    
    
    private function shipToElement($data=[]){
        $result=[];
        
        $result['Name']=$data['location'];
        
        $result['Address']=$this->addressElement($data['address1'],$data['address2'],$data['address3'],$data['zip'],$data['code']);
        return $result;
        
    }
    
    
    private function shipperElement($includeAccount=0){
        $result=[];
        
        $result['Name']='Cup Print';
        if ($includeAccount){
            $result['ShipperNumber']=UPS_SHIPPER_NUMBER;
        }
        $result['Address']=$this->addressElement(UPS_CUPPRINT_ADDRESS,UPS_CUPPRINT_CITY,UPS_CUPPRINT_STATE,UPS_CUPPRINT_ZIP,UPS_CUPPRINT_COUNTRY);
        return $result;
        
    }
    
    
    /**
     * Returns the address element that is used for shipper, shipto and shipfrom address information
     * @param string $street
     * @param string $city
     * @param string $state
     * @param string $zip
     * @param string $country
     * @return string[]
     */
    private function addressElement($street='',$city='',$state='',$zip='',$country=''){
        $result=[];
        
        $result['AddressLine']=$street;
        $result['City']=$city;
        $result['StateProvinceCode']=$state;
        $result['PostalCode']=$zip;
        $result['CountryCode']=$country;
        
        return $result;
    }
    
    /**
     * build the 'request' part of the UPS payload
     * @param unknown $reference
     * @return string
     */
    private function buildRequestElement($data=[]){
        $result=[];
       # $result['Request']=[];
        $result['SubVersion']='1234';
        $result['TransactionReference']=[];
        $result['TransactionReference']['CustomerContext']=$data['quoteReference'];
        
        return $result;
        
    }
    
    private function post($request){
        $result=false;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://onlinetools.ups.com/ship/v1801/rating/shop",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS =>$request,
            CURLOPT_HTTPHEADER => array(
                "AccessLicenseNumber: " . UPS_LICENSE,
                "Username: " . UPS_USERNAME,
                "Password: " . UPS_PASSWORD,
                "transId: 8be6dba2-c56f-4723-938e-797f86a68544",
                "transactionSrc: " . UPS_SOURCE,
                "Content-Type: application/json"
            ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            
            $message = print_r( $err, true );
            
            error_log($message);
        } else {
            $result=json_decode($response,true);
        }
        
        
        return $result;
        
    }
    
    
    
}