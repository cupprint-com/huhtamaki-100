<?php 
if(!defined('CP_HUHTAMAKI_RUNNING')){
    exit(1);
}

require_once   getcwd() . '/config/includes.php';
require_once   getcwd() . '/inc/estimate.php';
class HuhtamakiCupprint{
    
    
    
    
    public function renderFormHtml(){
        ?>
        <!-- begin default view (html) of preamble and form -->
  			<h1>Welcome to Huhtamaki 100</h1>
  			<div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur sollicitudin tortor eget mattis pellentesque. Donec euismod egestas luctus. Suspendisse in tincidunt tellus, a elementum justo. Donec quis pharetra nisl. Sed pharetra imperdiet nulla non pretium. Curabitur at justo eget tellus lobortis dictum. Quisque elementum, arcu sit amet maximus pharetra, libero justo rhoncus elit, non mollis velit tellus vel tortor. Morbi lacinia pellentesque tortor, sed dictum orci auctor ac. Aenean quis metus sed est molestie ornare. Donec vel vulputate nunc. Quisque dictum, dui at mattis dapibus, mi lectus accumsan orci, a cursus ex sapien fringilla tortor. Integer ex erat, accumsan eu vulputate at, lobortis vitae libero. Mauris rhoncus sed elit ut elementum. Integer elementum commodo nibh. Nulla sed felis odio. Phasellus rhoncus egestas ante, eleifend accumsan leo vestibulum nec. Pellentesque id congue eros. Sed efficitur malesuada imperdiet. Donec vulputate turpis vulputate tincidunt tincidunt. Etiam et purus magna. Sed finibus maximus augue, ut consectetur dolor finibus ut. Suspendisse ut augue neque.</div>
  			<div>
  				<form id="requestForm" action="#" method="post">
  				<input type="hidden" name="key" value="<?php echo KEY_CALCULATE_ESTIMATE;?>">
  				<p>
  					<label for="emailAddress" id="emailLabel">Your email address:</label>
	  				<input type="email" name="emailAddress" id="emailAddress" data-warning="Please enter a valid email address"/>
	  			</p>
	  			<p>
  					<label for="businessUnit" id="businessUnitLabel">Business unit:</label>
	  				<select name="businessUnit" id="businessUnit" data-warning="Please select your business unit">
	  						<option value="">please select your business unit</option>
  							<option value="placeholder1">placeholder 1</option>
  							<option value="placeholder2">placeholder 2</option>
  							<option value="placeholder3">placeholder 3</option>
  							<option value="placeholder4">placeholder 4</option>
  					</select>
	  			</p>
  				<p>
  					<label for="cpc8dwQuantity" id="CPC8DWLabel">8oz Double Wall:</label>
	  				<select name="cpc8dwQuantity" id="cpc8dwQuantity" data-warning="Please select at least one quantity">
  							<option value="0">how many 8 oz cups</option>
  							<option value="500">500</option>
  							<option value="1000">1000</option>
  							<option value="1500">1500</option>
  					</select>
	  			</p>
	  			<p>
  					<label for="cpc12dwQuantity" id="CPC12DWLabel">12oz Double Wall:</label>
	  				<select name="cpc12dwQuantity" id="cpc12dwQuantity" data-warning="Please select at least one quantity">
  							<option value="0">how many 12 oz cups</option>
  							<option value="500">500</option>
  							<option value="1000">1000</option>
  							<option value="1500">1500</option>
  					</select>
	  			</p>
	  			<p id="calculate">
  					<input type="submit" value="Calculate">
	  				
	  			</p>
  				</form>
  				<div class="estimateResult"></div>
  			</div>
        <?php         
        
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
        $data=$_REQUEST;
        
        if (array_key_exists('reference', $_REQUEST)){
            $estimate=new Estimate();
            $result=$estimate->get($_REQUEST['reference']);
            
            $nameRowHeader= _('Item');
            $quantityRowHeader= _('Quantity');
            $priceRowHeader= _('Cost');
            $freightRowHeader= _('Estimated Shipping');
            $subtotalRowHeader= _('Price');
            $cpc8dwName= _('Huhtamaki 100 8oz Double Wall');
            
            $subTotalPriceHeader=_('Price');
            $subTotalShippingHeader=_('Shipping');
            $subTotalHeader=_('Total');
            ?>
            <div class="wrapEstimate" id="estimate">
            	<table>
            		<tr>
            			<th class="estimateItemName"><?php echo($nameRowHeader);?></th>
            			<th class="estimateItemQuantity"><?php echo($quantityRowHeader);?></th>
            			<th class="estimateItemPrice"><?php echo($priceRowHeader);?></th>
            			<th class="estimateItemFreight"><?php echo($freightRowHeader);?></th>
            			<th class="estimateItemTotal"><?php echo($subtotalRowHeader);?></th>
            			
            		</tr>
            		<tr>
            			<td><?php  echo($cpc8dwName); ?></td>
            			<td><?php  echo($result['cpc8dwQuantity']); ?></td>
            			<td><?php  echo($result['cpc8dwPrice']); ?></td>
            			<td><?php  echo($result['cpc8dwFreight']); ?></td>
            			<td><?php  echo($result['cpc8dwTotal']); ?></td>
            		</tr>
            		<tr>
            			<td colspan="5">
            				<table>
            					<tr>
            						<th><?php echo($subTotalPriceHeader);?></th>
            						<td><?php  echo($result['estimatedPrice']); ?></td>
            					</tr>
            					<tr>
            						<th><?php echo($subTotalShippingHeader);?></th>
            						<td><?php  echo($result['estimatedFreight']); ?></td>
            					</tr>
            					<tr>
            						<th><?php echo($subTotalHeader);?></th>
            						<td><?php  echo($result['estimatedTotal']); ?></td>
            					</tr>
            				</table>
            			</td>
            			
            		</tr>
            		
            	</table>
            
            </div>
            
            
            <?php 
        
        
        }
        
    }
    
    private function validateFormSubmission($form=[]){
        $result=[];
        $result['errors']=1;
        $result['message']='Something went wrong !';
        $cpc8dwQuantity=0;
        $cpc12dwQuantity=0;
        # verify that email address posted
        if (!array_key_exists('emailAddress', $form)){
            $result['message']='Please enter a valid email address';
            return $result;
        }
        # verify the email address format
        
        # TODO we will need to limit email address domains (@cupprint.com / @huhtamaki.com / @other ?)
        
        # business unit
        if (!array_key_exists('businessUnit', $form)){
            $result['message']='Please select your business unit';
            return $result;
        }
        
        # 8ozdw / 12ozdw
        if ((!array_key_exists('cpc8dwQuantity', $form)) && (!array_key_exists('cpc12dwQuantity', $form))){
            $result['message']='Please select at least one quantity';
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
        $result['businessUnit']=$form['businessUnit'];
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
        return $result;
        
        
    }
    
}

