<?php 
if(!defined('CP_HUHTAMAKI_RUNNING')){
    exit(1);
}

require_once   getcwd() . '/config/includes.php';
require_once   getcwd() . '/inc/estimate.php';
require_once   getcwd() . '/inc/ups.php';
class HuhtamakiCupprint{
    
    
    
    
    public function renderFormHtml(){
        ?>
        <!-- begin default view (html) of preamble and form -->
  			<h1><?php echo _('Welcome - Huhtamaki 100 Celebration Cup Orders');?></h1>
  			<div><?php echo $this->getMessageTemplateHtml('en','home-page-narrative.txt');?></div>
  			<div>
  				<form id="requestForm" action="#" method="post">

                  <div id="formWarnings"></div>

                    <input type="hidden" name="key" value="<?php echo KEY_CALCULATE_ESTIMATE;?>">
                    <div class="form-group">
                        <label for="emailAddress" id="emailLabel">Your email address:</label>
                        <input type="email" name="emailAddress" id="emailAddress" data-warning="<?php echo _('Please enter a valid email address');?>"/>
                    </div>
                    <div class="form-group">
                        <label for="businessUnitID" id="businessUnitLabel"><?php echo _('Business Unit');?>:</label>
                        <select name="businessUnitID" id="businessUnitID" data-warning="<?php echo _('Please select your business unit');?>">
                                <option value="">Please select your business unit</option>
                                <?php $this->renderBusinessUnitOptions();?>
                                
                                
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cpc8dwQuantity" id="CPC8DWLabel">8oz Double Wall:</label>
                        <select name="cpc8dwQuantity" id="cpc8dwQuantity" data-warning="<?php echo _('Please select at least one quantity');?>">
                            <option value="0">How many 8 oz cups</option>
                            <option value="500">500</option>
  							<option value="1000">1000</option>
  							<option value="1500">1500</option>
  							<option value="2000">2000</option>
  							<option value="2500">2500</option>
  							<option value="3000">3000</option>
  							<option value="3500">3500</option>
  							<option value="4000">4000</option>
  							<option value="4500">4500</option>
  							<option value="5000">5000</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cpc12dwQuantity" id="CPC12DWLabel">12oz Double Wall:</label>
                        <select name="cpc12dwQuantity" id="cpc12dwQuantity" data-warning="<?php echo _('Please select at least one quantity');?>">
                            <option value="0">How many 12 oz cups</option>
                            <option value="500">500</option>
  							<option value="1000">1000</option>
  							<option value="1500">1500</option>
  							<option value="2000">2000</option>
  							<option value="2500">2500</option>
  							<option value="3000">3000</option>
  							<option value="3500">3500</option>
  							<option value="4000">4000</option>
  							<option value="4500">4500</option>
  							<option value="5000">5000</option>
                        </select>
                    </div>

                    <div id="calculate" class="form-group">
                        <input type="submit" value="Calculate">
                    </div>
  				</form>
  				<div class="estimateResult"></div>
  				<div><?php echo $this->getMessageTemplateHtml('en','below-form-narrative.txt');?></div>
  			</div>
        <?php         
        
    }
    
    private function renderBusinessUnitOptions(){
        $estimate=new Estimate();
        $results=$estimate->getBusinessUnits();
        foreach($results as $row ) {
            ?>
            <option value="<?php echo $row['id']; ?>"><?php echo $row['location']; ?></option>
            <?php 
            
        }
        
        
    }
    
    public function processFormSubmission(){
        $result=[];
        $data=$_REQUEST;
        $result=$this->validateFormSubmission($data);
        
        if ($result['errors']){
            #should be an email notification to support@cupprint.com when there's an error because JS already screened the post
            return $result;
        }
        
        # prepare quotation for the target
        $estimate=new Estimate();
        $result=$estimate->prepare($result);
        $result['key']=KEY_GET_ESTIMATE;
        $result['errors']=0;
        return $result;
        
        
    }
    
    public function renderEstimate(){
        
        
        if (array_key_exists('reference', $_REQUEST)){
            $estimate=new Estimate();
            $result=$estimate->get($_REQUEST['reference']);
             ?>
            <div class="wrapEstimate" id="estimate">
 				<?php $this->renderEstimateTable($result); ?>
                <div class="form-group"><button type="button" id="sendRequest" class="submit" data-reference="<?php echo($result['quoteReference'])?>" data-key="<?php  echo KEY_SAVE_ESTIMATE?>"><?php echo _('send request');?></button></div>
            </div>
            
            
            <?php 
        }
        
    }
    
    private function renderEstimateTable($result){
        $nameRowHeader= _('Item');
        $quantityRowHeader= _('Quantity');
        $priceRowHeader= _('Cost');
        $freightRowHeader= _('Estimated Shipping');
        $subtotalRowHeader= _('Price');
        $cpc8dwName= _('Huhtamaki 100 8oz Double Wall');
        $cpc12dwName= _('Huhtamaki 100 12oz Double Wall');
        
        $subTotalPriceHeader=_('Price');
        $subTotalShippingHeader=_('Shipping');
        $subTotalHeader=_('Estimated Total');
        ?>
            <h2><?php echo _("Your Request:"); ?></h2>
            <table cellspacing="0" cellpadding="0" class="textright">
                <tr>
                    <th class="estimateItemName textleft"><?php echo($nameRowHeader);?></th>
                    <th class="estimateItemQuantity"><?php echo($quantityRowHeader);?></th>
                    <th class="estimateItemTotal"><?php echo($subtotalRowHeader);?></th>
                </tr>
                <tr>
                    <td class="textleft"><?php  echo($cpc8dwName); ?></td>
                    <td><?php  echo($result['cpc8dwQuantity']); ?></td>
                    <td><?php  echo number_format($result['cpc8dwTotal'],2); ?></td>
                </tr>
                <tr>
                    <td class="textleft"><?php echo($cpc12dwName); ?></td>
                    <td><?php  echo($result['cpc12dwQuantity']); ?></td>
                    <td><?php  echo number_format($result['cpc12dwTotal'],2); ?></td>
                </tr>
                <tr>
                    <td class="priceblockspacer">&nbsp;</td>
                    <td colspan="4" class="priceblock textright">
                        <table cellspacing="0" cellpadding="0">
                            
                            <?php 
                            if ($result['estimatedFreight'] > 0){
                            ?>
                            <tr>
                                <th><?php echo($subTotalPriceHeader);?></th>
                                <td><?php  echo number_format($result['estimatedPrice'],2); ?></td>
                            </tr>
                            <tr>
                                <th><?php echo($subTotalShippingHeader);?></th>
                                <td><?php  echo number_format($result['estimatedFreight'],2); ?></td>
                            </tr>
                            <tr>
                                <th><?php echo($subTotalHeader);?></th>
                                <td><b>&euro; <?php  echo number_format($result['estimatedTotal'],2); ?></b></td>
                            </tr>
                            <?php 
                            }
                            else{?>
                            <tr>
                                <th><?php echo($subTotalHeader);?></th>
                                <td><b>&euro; <?php  echo number_format($result['estimatedPrice'],2); ?></b></td>
                            </tr>
                            <?php }?>
                        </table>
                    </td>
                </tr>
                
            </table>
            <p>&nbsp;</p>
        <?php 
    }
    
    
    public function renderThankYouPage(){
        if (array_key_exists('reference', $_REQUEST)){
            # save the estimate (adds new row to 'huhtamaki100' table using stored procedure)
            $estimate=new Estimate();
            $result=$estimate->save($_REQUEST['reference']);
            ?>
            <!-- thank you page -->
            <div class="thank-you-page">
                <h1><?php echo _('Thank you for Your Request!');?></h1>
                <div><?php echo $this->getMessageTemplateHtml('en','thank-you-page.txt');?></div>
                <div>
                    <?php $this->renderEstimateTable($result); ?>
                    <?php $this->renderContactDetails($result); ?>
                </div>
                <div><?php echo $this->getMessageTemplateHtml('en','thank-you-bottom.txt');?></div>
            </div>
            <input type="hidden" id="sendRequest" class="submit" data-reference="<?php echo($_REQUEST['reference'])?>" data-key="<?php echo KEY_SAVE_ESTIMATE; ?>"></button>
            <?php 
            
            
        }
        
    }
    
    public function testCalculateFreight(){
       # $ref='0d1612cf56e3ef0b9cc79b30e4315e36';
        $ref='a5b89edd22416aa8af5e09787705ed4c';
        if (array_key_exists('reference', $_REQUEST)){
            $ref=$_REQUEST['reference'];
        }
        $estimate=new Estimate();
        $data=$estimate->get($ref);
        #return $data;
        $ups=new UPS();
        $result=$ups->calculateFreight($data);
        return $result;
        
    }
    
    private function renderContactDetails($result){
        ?>
        <div class="wrapBuContact">
        	<table>
        		<tr>
        			<th><?php echo _('Quotation Reference');?></th>
        			<td><?php echo $result['quoteReference'];?></td>
        		</tr>
        		<tr>
        			<th><?php echo _('Business Unit');?></th>
        			<td><?php echo $result['location'];?></td>
        		</tr>
        		<tr>
        			<th><?php echo _('Email Address');?></th>
        			<td><?php echo $result['email'];?></td>
        		</tr>
        		<tr>
        			<th><?php echo _('Address');?></th>
        			<td><?php echo $this->normalizeAddress($result);   ?></td>
        		</tr>
        		
        	</table>
        
        </div>
        <?php 
    }
    
    /**
     * Retrieve the html content of the correct auto quote template for target language & territory
     * @param string $language
     * @param string $territory
     * @return string
     */
    private function getMessageTemplateHtml($language='en', $filename='home-page-narrative.txt'){
        
        $path=getcwd() . '/templates/' . $language . '/';
        #echo $path . $filename;
        $result = file_get_contents($path . $filename);
        return $result;
        
    }
    
    
    private function normalizeAddress($result){
        $address='';
        if ($result['address1']!=''){
            $address.=$result['address1'] . '<br/>';
        }
        if ($result['address2']!=''){
            $address.=$result['address2'] . '<br/>';
        }
        if ($result['address3']!=''){
            $address.=$result['address3'] . '<br/>';
        }
        if ($result['zip']!=''){
            $address.=$result['zip'] . '<br/>';
        }
        if ($result['country']!=''){
            $address.=$result['country'] . '<br/>';
        }
        return $address;
    }
    
    private function validateFormSubmission($form=[]){
        $result=[];
        $result['errors']=1;
        $result['message']=_('Something went wrong !');
        $cpc8dwQuantity=0;
        $cpc12dwQuantity=0;
        # verify that email address posted
        if (!array_key_exists('emailAddress', $form)){
            $result['message']=_('Please enter a valid email address');
            return $result;
        }
        # verify the email address format
        
        # verify that email domain posted
        # we allow only cupprint.com & huhtamakic.com addresses in production
        /**
        if (!H100_DEBUG){
                if( !preg_match("/@(cupprint\.com|huhtamaki\.com)$/i", "PHP ist die Web-Scripting-Sprache der Wahl.")) {
                    $result['message']=_('Please enter a valid email domain');
                    return $result;
                }
        }
        **/
        
        # business unit
        if (!array_key_exists('businessUnitID', $form)){
            $result['message']=_('Please select your business unit');
            return $result;
        }
        
        # 8ozdw / 12ozdw
        if ((!array_key_exists('cpc8dwQuantity', $form)) && (!array_key_exists('cpc12dwQuantity', $form))){
            $result['message']=_('Please select at least one quantity');
            return $result;
        }
        if (array_key_exists('cpc8dwQuantity', $form)){
            
            $cpc8dwQuantity=$form['cpc8dwQuantity'];
        }
        if (array_key_exists('cpc12dwQuantity', $form)){
            
            $cpc12dwQuantity=$form['cpc12dwQuantity'];
        }
        # get to here then all ok
        $result['errors']=0;
        $result['message']='';
        
        $result['emailAddress']=$form['emailAddress'];
        $result['businessUnitID']=$form['businessUnitID'];
        $result['cpc8dwQuantity']=$cpc8dwQuantity;
        $result['cpc12dwQuantity']=$cpc12dwQuantity;
        $result['cpc8dwName']='8oz Double Wall';
        $result['cpc12dwName']='12oz Double Wall';
        
        return $result;
    }
    
    /**
     * Returns an array of sanctioned operations
     * @return string[]
     */
    public function sanctioned(){
        $result=[];
        $result[KEY_RENDER_FORM]=RENDER_FORM;
        $result[KEY_CALCULATE_ESTIMATE]=CALCULATE_ESTIMATE;
        $result[KEY_GET_ESTIMATE]=GET_ESTIMATE;
        $result[KEY_SAVE_ESTIMATE]=SAVE_ESTIMATE;
        $result[KEY_CALCULATE_FREIGHT]=CALCULATE_FREIGHT;
        return $result;
        
        
    }
    
}

