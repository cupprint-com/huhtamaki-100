<?php 

if(!defined('CP_HUHTAMAKI_RUNNING')){
    exit(1);
}

require_once   getcwd() . '/config/includes.php';

class Estimate{
    
    
    public function prepare($data){
        $result=$data;
        
        
        
        # quantity of 8oz requested
        $cpc8dwQuantity=$data['cpc8dwQuantity'];
        # quantity of 12oz requested
        $cpc12dwQuantity=$data['cpc12dwQuantity'];
        
        $cpc8dwPrice = $cpc8dwQuantity * CPC8DW_PRICE;
        # freight calculation (per line)
        $cpc8dwFreight = 0;
        $cpc8dwTotal= $cpc8dwPrice + $cpc8dwFreight;
        
        $cpc12dwPrice = $cpc12dwQuantity * CPC12DW_PRICE;
        # freight calculation (per line)
        $cpc12dwFreight = 0;
        $cpc12dwTotal= $cpc12dwPrice + $cpc12dwFreight;
        
        $estimatedPrice = $cpc8dwPrice + $cpc12dwPrice;
        $estimatedFreight = $cpc8dwFreight + $cpc12dwFreight;
        $estimatedTotal = $cpc8dwTotal + $cpc12dwTotal;
       
        
        $result['cpc8dwPrice']=$cpc8dwPrice;
        $result['cpc8dwFreight']=$cpc8dwFreight;
        $result['cpc8dwTotal']=$cpc8dwTotal;
        
        $result['cpc12dwPrice']=$cpc12dwPrice;
        $result['cpc12dwFreight']=$cpc12dwFreight;
        $result['cpc12dwTotal']=$cpc12dwTotal;
        
        $result['estimatedPrice']=$estimatedPrice;
        $result['estimatedFreight']=$estimatedFreight;
        $result['estimatedTotal']=$estimatedTotal;
        
        
        $result=$this->updateWipEstimate($result);
        
        
        
        
        
        return $result;
    }
    
    public function get($reference){
        $result=$this->getWipEstimate($reference);
        return $result;
    }
    
    public function save($reference){
        $db=new Database();
        $conn=$db->getConnection();
        
        
        $sql="call spSaveHuhtamaki100(:quoteReference);";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':quoteReference',$reference,PDO::PARAM_STR);
        $stmt->execute();
        $stmt->closeCursor();
        return $this->get($reference);
    }
    
    
    
    public function getBusinessUnits(){
        $results=[];
        $db=new Database();
        $conn=$db->getConnection();
        $sql="select id, location from buLocations order by location asc";
        $stmt = $conn->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        if ($stmt->execute()) {
            $rows=$stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach( $rows as $row ) {
                
                $field=[];
                $field['id']=$row['id'];
                $field['location']=$row['location'];
                
                $results[]=$field;
            }
        }
        $stmt->closeCursor();
        return $results;
    }
    /**
     * Performs an update to the wipHuh100Quotes record that stores current state of the options selected by end user
     * @param unknown $data
     * @return unknown
     */
    private function updateWipEstimate($data){
        $db=new Database();
        $conn=$db->getConnection();
        $quoteReference=bin2hex(openssl_random_pseudo_bytes(16));
        
        if (!array_key_exists('quoteReference', $data)){
            
            $sql="INSERT INTO wipHuh100Quotes(`quoteReference`) select :quoteReference ";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':quoteReference',$quoteReference,PDO::PARAM_STR);
            $stmt->execute();
            $stmt->closeCursor();
            $data['quoteReference']=$quoteReference;
        }
        
        $sql="UPDATE wipHuh100Quotes SET ";
        $sql.= 'businessUnitID=:businessUnitID, ' ;
        $sql.= 'email=:email, ' ;
        $sql.= 'cpc8dwName=:cpc8dwName,' ;
        $sql.= 'cpc8dwQuantity=:cpc8dwQuantity,' ;
        $sql.= 'cpc8dwPrice=:cpc8dwPrice,' ;
        $sql.= 'cpc8dwFreight=:cpc8dwFreight,' ;
        $sql.= 'cpc8dwTotal=:cpc8dwTotal,' ;
        $sql.= 'cpc12dwName=:cpc12dwName,' ;
        $sql.= 'cpc12dwQuantity=:cpc12dwQuantity,' ;
        $sql.= 'cpc12dwPrice=:cpc12dwPrice,' ;
        $sql.= 'cpc12dwFreight=:cpc12dwFreight,' ;
        $sql.= 'cpc12dwTotal=:cpc12dwTotal,' ;
        $sql.= 'estimatedPrice=:estimatedPrice,' ;
        $sql.= 'estimatedFreight=:estimatedFreight,' ;
        $sql.= 'estimatedTotal=:estimatedTotal ' ;
        /*
        $sql+= ',address1=:address1' ;
        $sql+= ',address2=:address2' ;
        $sql+= ',address3=:address3' ;
        $sql+= ',zip=:zip' ;
        $sql+= ',country=:country ' ;
        */
        
        $sql.=' WHERE quoteReference=:quoteReference ';
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':businessUnitID',$data['businessUnitID'],PDO::PARAM_INT);
        $stmt->bindValue(':email',$data['emailAddress'],PDO::PARAM_STR);
        $stmt->bindValue(':cpc8dwName',$data['cpc8dwName'],PDO::PARAM_STR);
        $stmt->bindValue(':cpc8dwQuantity',$data['cpc8dwQuantity'],PDO::PARAM_STR);
        $stmt->bindValue(':cpc8dwPrice',$data['cpc8dwPrice'],PDO::PARAM_STR);
        $stmt->bindValue(':cpc8dwFreight',$data['cpc8dwFreight'],PDO::PARAM_STR);
        $stmt->bindValue(':cpc8dwTotal',$data['cpc8dwTotal'],PDO::PARAM_STR);
        $stmt->bindValue(':cpc12dwName',$data['cpc12dwName'],PDO::PARAM_STR);
        $stmt->bindValue(':cpc12dwQuantity',$data['cpc12dwQuantity'],PDO::PARAM_STR);
        $stmt->bindValue(':cpc12dwPrice',$data['cpc12dwPrice'],PDO::PARAM_STR);
        $stmt->bindValue(':cpc12dwFreight',$data['cpc12dwFreight'],PDO::PARAM_STR);
        $stmt->bindValue(':cpc12dwTotal',$data['cpc12dwTotal'],PDO::PARAM_STR);
        $stmt->bindValue(':estimatedPrice',$data['estimatedPrice'],PDO::PARAM_STR);
        $stmt->bindValue(':estimatedFreight',$data['estimatedFreight'],PDO::PARAM_STR);
        $stmt->bindValue(':estimatedTotal',$data['estimatedTotal'],PDO::PARAM_STR);
        $stmt->bindValue(':quoteReference',$data['quoteReference'],PDO::PARAM_STR);
        
        $stmt->execute();
        $stmt->closeCursor();
        #update address details
        $sql='UPDATE wipHuh100Quotes INNER JOIN buLocations on wipHuh100Quotes.businessUnitID=buLocations.id  SET ';
        $sql.= 'wipHuh100Quotes.address1 = buLocations.address1, ';
        $sql.= 'wipHuh100Quotes.address2 = buLocations.address2, ';
        $sql.= 'wipHuh100Quotes.address3 = buLocations.address3, ';
        $sql.= 'wipHuh100Quotes.zip = buLocations.zip, ';
        $sql.= 'wipHuh100Quotes.country = buLocations.country ';
        $sql.= ' WHERE wipHuh100Quotes.quoteReference=:quoteReference '; 
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':quoteReference',$data['quoteReference'],PDO::PARAM_STR);
        $stmt->execute();
        $stmt->closeCursor();
        return $this->getWipEstimate($data['quoteReference']);
    }
    
    private function getWipEstimate($quoteReference='000'){
        $db=new Database();
        $conn=$db->getConnection();
        
        $sql="select * from wipHuh100Quotes where quoteReference=:quoteReference ";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':quoteReference',$quoteReference,PDO::PARAM_STR);
        if ($stmt->execute()) {
            $rows=$stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach( $rows as $row ) {
                $result=$row;
            }
        }
        $stmt->closeCursor();
        return $result;
        
    }
}