<?php
Class Webkul_Marketplace_Model_Observer
{
	public function CustomerRegister($observer)
	{
		$data=Mage::getSingleton('core/app')->getRequest();
		if($data->getParam('wantpartner')==1){
			$customer = $observer->getCustomer();
			Mage::getModel('marketplace/userprofile')->getRegisterDetail($customer);
			$emailTemp = Mage::getModel('core/email_template')->loadDefault('partnerrequest');
			
			$emailTempVariables = array();
			$admin_storemail = Mage::getStoreConfig('marketplace/marketplace_options/adminemail');
			$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
			$adminUsername = Mage::helper('marketplace')->__('Admin');
			$emailTempVariables['myvar1'] = $customer->getName();
			$emailTempVariables['myvar2'] = Mage::getUrl('adminhtml/customer/edit', array('id' => $customer->getId()));
			
			$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
			
			$emailTemp->setSenderName($customer->getName());
			$emailTemp->setSenderEmail($customer->getEmail());
			$emailTemp->send($adminEmail,$customer->getName(),$emailTempVariables);
		}
	}
	
	public function afterSaveCustomer($observer)
	{
		$customer=$observer->getCustomer();
		$customerid=$customer->getId();
		$isPartner= Mage::getModel('marketplace/userprofile')->isPartner();
		if($isPartner==1){
			$data=$observer->getRequest();
			$sid = $data->getParam('sellerassignproid');
			$unassignproid = $data->getParam('sellerunassignproid');
			$partner_type = $data->getParam('partnertype');
			if($partner_type==2)
			{
				$collectionselectdelete = Mage::getModel('marketplace/userprofile')->getCollection();
				$collectionselectdelete->addFieldToFilter('mageuserid',array($customerid));
				foreach($collectionselectdelete as $delete){
					$autoid=$delete->getautoid();
				}
				$collectiondelete = Mage::getModel('marketplace/userprofile')->load($autoid);
				$collectiondelete->delete();
				$customer = Mage::getModel('customer/customer')->load($customerid);	
				$emailTemp = Mage::getModel('core/email_template')->loadDefault('partnerdisapprove');
			
				$emailTempVariables = array();		
				$admin_storemail = Mage::getStoreConfig('marketplace/marketplace_options/adminemail');
				$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');	
				$adminUsername = Mage::helper('marketplace')->__('Admin');
				$emailTempVariables['myvar1'] = $customer->getName();
				$emailTempVariables['myvar2'] = Mage::helper('customer')->getLoginUrl();
				
				$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
				
				$emailTemp->setSenderName($adminUsername);
				$emailTemp->setSenderEmail($adminEmail);
				$emailTemp->send($customer->getEmail(),$Username,$emailTempVariables);	
			}
			if($sid !=''||$sid!= 0){
				Mage::getModel('marketplace/userprofile')->assignProduct($customer,$sid);
			}
			if($unassignproid !=''||$unassignproid!= 0){
				Mage::getModel('marketplace/userprofile')->unassignProduct($customer,$unassignproid);
			}
			$wholedata=$data->getParams();
			$collectionselect = Mage::getModel('marketplace/saleperpartner')->getCollection();
			$collectionselect->addFieldToFilter('mageuserid',array('eq'=>$customer->getId()));
			if(count($collectionselect)==1){
			    foreach($collectionselect as $verifyrow){
				$autoid=$verifyrow->getautoid();
				}
				
				$collectionupdate = Mage::getModel('marketplace/saleperpartner')->load($autoid);
				$collectionupdate->setcommision($wholedata['commision']);
				$collectionupdate->save();
				}
			else{
				$collectioninsert=Mage::getModel('marketplace/saleperpartner');
				$collectioninsert->setmageuserid($customer->getId());
				$collectioninsert->setcommision($wholedata['commision']);
				$collectioninsert->save();
			}

			/*Save seller info*/
			if(!isset($wholedata['contactnumber'])){
				$wholedata['contactnumber'] = '';
			}
			if(!isset($wholedata['tw_active'])){
				$wholedata['tw_active']=0;
			}
			if(!isset($wholedata['fb_active'])){
				$wholedata['fb_active']=0;
			}
			if(!isset($wholedata['gplus_active'])){
				$wholedata['gplus_active']=0;
			}
			if(!isset($wholedata['youtube_active'])){
				$wholedata['youtube_active']=0;
			}
			if(!isset($wholedata['vimeo_active'])){
				$wholedata['vimeo_active']=0;
			}
			if(!isset($wholedata['instagram_active'])){
				$wholedata['instagram_active']=0;
			}
			if(!isset($wholedata['pinterest_active'])){
				$wholedata['pinterest_active']=0;
			}
			if(!isset($wholedata['moleskine_active'])){
				$wholedata['moleskine_active']=0;
			}
			$collection = Mage::getModel('marketplace/userprofile')->getCollection();
			$collection->addFieldToFilter('mageuserid',array('eq'=>$customer->getId()));
			foreach($collection as  $value){ 
				$data = $value; 
				$value->addData($wholedata);
				$value->setTwitterid($wholedata['twitterid']);
				$value->setFacebookid($wholedata['facebookid']);
				$value->setContactnumber($wholedata['contactnumber']);
				$value->setShoptitle($wholedata['shoptitle']);
				$value->setComplocality($wholedata['complocality']);
				$value->setMetaKeyword($wholedata['meta_keyword']);

				if($wholedata['compdesi']){
					$wholedata['compdesi'] = str_replace('script', '', $wholedata['compdesi']);
				}
				$value->setCompdesi($wholedata['compdesi']);

				if($wholedata['returnpolicy']){
					$wholedata['returnpolicy'] = str_replace('script', '', $wholedata['returnpolicy']);
				}
				$value->setReturnpolicy($wholedata['returnpolicy']);

				if($wholedata['shippingpolicy']){
					$wholedata['shippingpolicy'] = str_replace('script', '', $wholedata['shippingpolicy']);
				}
				$value->setShippingpolicy($wholedata['shippingpolicy']);
				
				$value->setMetaDescription($wholedata['meta_description']);
				$target =Mage::getBaseDir().'/media/avatar/';
				if(strlen($_FILES['bannerpic']['name'])>0){
					$extension = pathinfo($_FILES["bannerpic"]["name"], PATHINFO_EXTENSION);
					$temp = explode(".",$_FILES["bannerpic"]["name"]);
                    $img1 = $temp[0].rand(1,99999).$loid.'.'.$extension;
					$value->setbannerpic($img1);
					$targetb = $target.$img1; 
					move_uploaded_file($_FILES['bannerpic']['tmp_name'],$targetb);
				}
				if(strlen($_FILES['logopic']['name'])>0){
					$extension = pathinfo($_FILES["logopic"]["name"], PATHINFO_EXTENSION);
					$temp1 = explode(".",$_FILES["logopic"]["name"]);
                    $img2 = $temp1[0].rand(1,99999).$loid.'.'.$extension;
					$value->setlogopic($img2);					
					$targetl = $target.$img2; 
					move_uploaded_file($_FILES['logopic']['tmp_name'],$targetl);
				}
				if (array_key_exists('countrypic', $wholedata)) {
					$value->setcountrypic($wholedata['countrypic']);
				}
				$value->save();
			}
		}
        else{
			$data=$observer->getRequest();
			$partner_type = $data->getParam('partnertype');
			$profileurl = $data->getParam('profileurl');
			$wholedata=$data->getParams();
			if($partner_type==1)
			{
				if($profileurl!=''){
					$profileurlcount = Mage::getModel('marketplace/userprofile')->getCollection();
					$profileurlcount->addFieldToFilter('profileurl',$profileurl);
					if(count($profileurlcount)==0){
						$collectionselect = Mage::getModel('marketplace/userprofile')->getCollection();
						$collectionselect->addFieldToFilter('mageuserid',array($customer->getId()));
						if(count($collectionselect)>=1){
							foreach($collectionselect as $coll){
									$coll->setWantpartner('1');
									$coll->setpartnerstatus('Seller');
									$coll->setProfileurl($data->getParam('profileurl'));
									$coll->save();
							}
						}	
							else{
								$collection=Mage::getModel('marketplace/userprofile');
								$collection->setwantpartner(1);
								$collection->setpartnerstatus('Seller');
								$collection->setProfileurl($data->getParam('profileurl'));
								$collection->setmageuserid($customer->getId());
								$collection->save();
						}
						$customer = Mage::getModel('customer/customer')->load($customerid);

						$emailTemp = Mage::getModel('core/email_template')->loadDefault('partnerapprove');
		
						$emailTempVariables = array();				
						$admin_storemail = Mage::getStoreConfig('marketplace/marketplace_options/adminemail');
						$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
						$adminUsername = Mage::helper('marketplace')->__('Admin');
						$emailTempVariables['myvar1'] = $customer->getName();
						$emailTempVariables['myvar2'] = Mage::helper('customer')->getLoginUrl();
						
						$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
						
						$emailTemp->setSenderName($adminUsername);
						$emailTemp->setSenderEmail($adminEmail);
						$emailTemp->send($customer->getEmail(),$Username,$emailTempVariables);

					} else {
						Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__('This Shop Name alreasy Exists.'));
					}	
				}
				else{
					Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__('Enter Shop Name of Customer.'));
				}
			}
		}
	}

	public function deleteCustomer($observer)
	{
		$sellerid=$observer->getCustomer()->getId();
		$sellers=Mage::getModel('marketplace/userprofile')->getCollection()
												->addFieldToFilter('mageuserid',array('eq'=>$sellerid));
		foreach($sellers as $seller){ $seller->delete(); }
		
		$sellerpro= Mage::getModel('marketplace/product')->getCollection()
							->addFieldToFilter('userid',array('eq'=>$sellerid));
		foreach($sellerpro as $pro){
			$allStores = Mage::app()->getStores();
			foreach ($allStores as $_eachStoreId => $val){
				Mage::getModel('catalog/product_status')->updateProductStatus($pro->getMageproductid(),Mage::app()->getStore($_eachStoreId)->getId(), Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
			}
			$pro->delete();
		}
	}

	public function DeleteProduct($observer) 
	{ 
		$collection = Mage::getModel('marketplace/product')->getCollection()
														   ->addFieldToFilter('mageproductid ',$observer->getProduct()->getId());
		foreach($collection as $data){			
			Mage::getModel('marketplace/product')->load($data['index_id'])->delete();			
		}		
	}
	
	public function afterPlaceOrder($observer) 
	{ 
		$helper = Mage::helper('marketplace');
		$lastOrderId=$observer->getOrder()->getId();
		$order = Mage::getModel('sales/order')->load($lastOrderId);
		Mage::getModel('marketplace/saleslist')->getProductSalesCalculation($order);
		/*
		*send mail notification to seller for placed order
		*/
		$paymentCode = '';
	    if($order->getPayment()){
			$paymentCode = $order->getPayment()->getMethod();
		}
		$style='style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc";';

		$shipping_info = '';
		$shipping_des = '';

		$billingId = $order->getBillingAddress()->getId();
		$billaddress = Mage::getModel('sales/order_address')->load($billingId);
		$billinginfo = $billaddress['firstname'].'<br/>'.$billaddress['street'].'<br/>'.$billaddress['city'].' '.$billaddress['region'].' '.$billaddress['postcode'].'<br/>'.Mage::getModel('directory/country')->load($billaddress['country_id'])->getName().'<br/>T:'.$billaddress['telephone'];	
				
		$payment = $order->getPayment()->getMethodInstance()->getTitle();

		if($order->getShippingAddress()){
			$shippingId = $order->getShippingAddress()->getId();
			$address = Mage::getModel('sales/order_address')->load($shippingId);				
			$shipping_info = $address['firstname'].'<br/>'.$address['street'].'<br/>'.$address['city'].' '.$address['region'].' '.$address['postcode'].'<br/>'.Mage::getModel('directory/country')->load($address['country_id'])->getName().'<br/>T:'.$address['telephone'];	
			$shipping_des = $order->getShippingDescription();
		}

		$admin_storemail = Mage::getStoreConfig('marketplace/marketplace_options/adminemail');
		$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
		$adminUsername = Mage::helper('marketplace')->__('Admin');
				
		$seller_order = Mage::getModel('marketplace/order')->getCollection()
							->addFieldToFilter('order_id',$lastOrderId);						
		foreach($seller_order as $info){
			$tax="<tr><td ".$style."><h3>".$helper->__('Tax')."</h3></td><td ".$style."></td><td ".$style."></td><td ".$style."></td></tr><tr>";
			$totalprice ='';
			$totaltax_amount= 0;
			$cod_charges= 0;
			$shipping_charges= 0;
			$orderinfo = '';
			$product_ids=explode(',', $info['item_ids']);
			$fetchsale = Mage::getModel('marketplace/saleslist')->getCollection()
							->addFieldToFilter('mageorderid',$lastOrderId)
							->addFieldToFilter('mageproid',array('in'=>$product_ids));
			foreach ($fetchsale as $res) {
				$orderinfo = $orderinfo."<tr>
								<td valign='top' align='left' ".$style." >".$res['mageproname']."</td>
								<td valign='top' align='left' ".$style.">".Mage::getModel('catalog/product')->load($res['mageproid'])->getSku()."</td>
								<td valign='top' align='left' ".$style." >".$res['magequantity']."</td>
								<td valign='top' align='left' ".$style.">".Mage::app()->getStore()->formatPrice($res['mageproprice'])."</td>
							 </tr>";
				$totaltax_amount=$totaltax_amount + $res['totaltax'];
				$totalprice = $totalprice+$res['mageproprice'];
			}
			$total_cod = $info->getCodCharges();
			$shipping_charges = $info->getShippingCharges();
			$userdata = Mage::getModel('customer/customer')->load($info['seller_id']);				
			$Username = $userdata['firstname'];
			$useremail = $userdata['email'];

			if($paymentCode == 'mpcashondelivery'){
	        	$cod_row = "<tr style='font-size:11px;'>
									<td align='right' style='padding:3px 9px' colspan='3'>".$helper->__('Tax Amount')."</td>
									<td align='right' style='padding:3px 9px' colspan='3'><span>".Mage::app()->getStore()->formatPrice($total_cod)."</span></td>
								</tr>";
	       	}else{
	       		$cod_row = '';
	       	}

			$orderinfo = $orderinfo."</tbody><tbody><tr style='font-size:11px;'>
									<td align='right' style='padding:3px 9px' colspan='3'>".$helper->__('Shipping & Handling Charges')."</td>
									<td align='right' style='padding:3px 9px' colspan='3'><span>".Mage::app()->getStore()->formatPrice($shipping_charges)."</span></td>
								</tr><tr style='font-size:11px;'>
									<td align='right' style='padding:3px 9px' colspan='3'>".$helper->__('Tax Amount')."</td>
									<td align='right' style='padding:3px 9px' colspan='3'><span>".Mage::app()->getStore()->formatPrice($totaltax_amount)."</span></td>
								</tr>".$cod_row."<tr>
									<td align='right' style='padding:3px 9px' colspan='3'>".$helper->__('Grandtotal')."</td>
									<td align='right' style='padding:3px 9px' colspan='3'><span>".Mage::app()->getStore()->formatPrice($totalprice+$totaltax_amount+$shipping_charges+$total_cod)."</span></td>
								</tr>";
			/* load email template */
			$emailTemp = Mage::getModel('core/email_template')->loadDefault('webkulorderinvoice');
			
			$emailTempVariables = array();				
			
			$emailTempVariables['myvar1'] = $order->getRealOrderId();
			$emailTempVariables['myvar2'] = $order['created_at'];
			$emailTempVariables['myvar4'] = $billinginfo;
			$emailTempVariables['myvar5'] = $payment;
			$emailTempVariables['myvar6'] = $shipping_info;
			$emailTempVariables['myvar9'] = $shipping_des;
			$emailTempVariables['myvar8'] = $orderinfo;
			$emailTempVariables['myvar3'] =$Username;
			
			$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
			
			$emailTemp->setSenderName($adminUsername);
			$emailTemp->setSenderEmail($adminEmail);
			$emailTemp->send($useremail,$Username,$emailTempVariables);		
		}
	}
	
	
	public function commissionCalculationOnComplete($observer)
	{
	    $order = $observer->getOrder();
	    if($order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE){
	    	Mage::getModel('marketplace/saleslist')->getCommsionCalculation($order);
	    }
	}
	
	public function checkInvoiceSubmit($observer) 
	{ 
		$helper = Mage::helper('marketplace');
		$seller_items_array = array();
		$invoice_seller_ids = array();
		$event = $observer->getEvent()->getInvoice();
		foreach ($event->getAllItems() as $value) {
			$invoiceproduct = $value->getData();
			$pro_seller_id = 0;
			$product_seller	= Mage::getModel('marketplace/product')->getCollection()
					->addFieldToFilter('mageproductid',$invoiceproduct['product_id']);
			foreach ($product_seller as $sellervalue) {
				if($sellervalue->getUserid()){
					array_push($invoice_seller_ids, $sellervalue->getUserid());
					$invoice_seller_ids[$sellervalue->getUserid()] = $sellervalue->getUserid();
					$pro_seller_id = $sellervalue->getUserid();			
				}
			}
			$order_collection=Mage::getModel('marketplace/order')->getCollection()
								->addFieldToFilter('order_id',array('eq',$event->getOrderId()))
								->addFieldToFilter('item_ids',array('in',$invoiceproduct['product_id']));
			foreach ($order_collection as $order_coll) {
				$order_coll->setInvoiceId($event->getId());
				$order_coll->save();
			}
			if($pro_seller_id){
				$seller_items_array[$pro_seller_id][] = $invoiceproduct;
			}
		}
		$order = Mage::getModel('sales/order')->load($event->getOrderId());
		$paymentCode = '';
	    if($order->getPayment()){
			$paymentCode = $order->getPayment()->getMethod();
		}
		$lastOrderId = $event->getOrderId();		
		$admin_storemail = Mage::getStoreConfig('marketplace/marketplace_options/adminemail');
		$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
		$adminUsername = Mage::helper('marketplace')->__('Admin');
		
		$style='style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc";';
		$shipping_info = '';
		$shipping_des = '';

		$billingId = $order->getBillingAddress()->getId();
		$billaddress = Mage::getModel('sales/order_address')->load($billingId);
		$billinginfo = $billaddress['firstname'].'<br/>'.$billaddress['street'].'<br/>'.$billaddress['city'].' '.$billaddress['region'].' '.$billaddress['postcode'].'<br/>'.Mage::getModel('directory/country')->load($billaddress['country_id'])->getName().'<br/>T:'.$billaddress['telephone'];	
				
		$payment = $order->getPayment()->getMethodInstance()->getTitle();

		if($order->getShippingAddress()){
			$shippingId = $order->getShippingAddress()->getId();
			$address = Mage::getModel('sales/order_address')->load($shippingId);				
			$shipping_info = $address['firstname'].'<br/>'.$address['street'].'<br/>'.$address['city'].' '.$address['region'].' '.$address['postcode'].'<br/>'.Mage::getModel('directory/country')->load($address['country_id'])->getName().'<br/>T:'.$address['telephone'];	
			$shipping_des = $order->getShippingDescription();
		}
				
		$seller_order = Mage::getModel('marketplace/order')->getCollection()
							->addFieldToFilter('seller_id',array('in'=>$invoice_seller_ids))
							->addFieldToFilter('order_id',$lastOrderId);						
		foreach($seller_order as $info){
			$tax="<tr><td ".$style."><h3>".$helper->__('Tax')."</h3></td><td ".$style."></td><td ".$style."></td><td ".$style."></td></tr><tr>";
			$totalprice ='';
			$totaltax_amount= 0;
			$cod_charges= 0;
			$shipping_charges= 0;
			$orderinfo = '';
			$product_ids=explode(',', $info['item_ids']);
			$fetchsale = Mage::getModel('marketplace/saleslist')->getCollection()
							->addFieldToFilter('mageorderid',$lastOrderId)
							->addFieldToFilter('mageproid',array('in'=>$seller_items_array[$info['seller_id']]));
			foreach ($fetchsale as $res) {
				$orderinfo = $orderinfo."<tr>
								<td valign='top' align='left' ".$style." >".$res['mageproname']."</td>
								<td valign='top' align='left' ".$style.">".Mage::getModel('catalog/product')->load($res['mageproid'])->getSku()."</td>
								<td valign='top' align='left' ".$style." >".$res['magequantity']."</td>
								<td valign='top' align='left' ".$style.">".Mage::app()->getStore()->formatPrice($res['mageproprice'])."</td>
							 </tr>";
				$totaltax_amount=$totaltax_amount + $res['totaltax'];
				$totalprice = $totalprice+$res['mageproprice'];
			}
			$total_cod = $info->getCodCharges();
			$shipping_charges = $info->getShippingCharges();
			$userdata = Mage::getModel('customer/customer')->load($info['seller_id']);				
			$Username = $userdata['firstname'];
			$useremail = $userdata['email'];

			if($paymentCode == 'mpcashondelivery'){
	        	$cod_row = "<tr style='font-size:11px;'>
									<td align='right' style='padding:3px 9px' colspan='3'>".$helper->__('Tax Amount')."</td>
									<td align='right' style='padding:3px 9px' colspan='3'><span>".Mage::app()->getStore()->formatPrice($total_cod)."</span></td>
								</tr>";
	       	}else{
	       		$cod_row = '';
	       	}

			$orderinfo = $orderinfo."</tbody><tbody><tr style='font-size:11px;'>
									<td align='right' style='padding:3px 9px' colspan='3'>".$helper->__('Shipping & Handling Charges')."</td>
									<td align='right' style='padding:3px 9px' colspan='3'><span>".Mage::app()->getStore()->formatPrice($shipping_charges)."</span></td>
								</tr><tr style='font-size:11px;'>
									<td align='right' style='padding:3px 9px' colspan='3'>".$helper->__('Tax Amount')."</td>
									<td align='right' style='padding:3px 9px' colspan='3'><span>".Mage::app()->getStore()->formatPrice($totaltax_amount)."</span></td>
								</tr>".$cod_row."<tr>
									<td align='right' style='padding:3px 9px' colspan='3'>".$helper->__('Grandtotal')."</td>
									<td align='right' style='padding:3px 9px' colspan='3'><span>".Mage::app()->getStore()->formatPrice($totalprice+$totaltax_amount+$shipping_charges+$total_cod)."</span></td>
								</tr>";
			/* load email template */
			$emailTemp = Mage::getModel('core/email_template')->loadDefault('webkulorderinvoice');
			
			$emailTempVariables = array();				
			
			$emailTempVariables['myvar1'] = $order->getRealOrderId();
			$emailTempVariables['myvar2'] = $order['created_at'];
			$emailTempVariables['myvar4'] = $billinginfo;
			$emailTempVariables['myvar5'] = $payment;
			$emailTempVariables['myvar6'] = $shipping_info;
			$emailTempVariables['myvar9'] = $shipping_des;
			$emailTempVariables['myvar8'] = $orderinfo;
			$emailTempVariables['myvar3'] = $Username;
			
			$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
			
			$emailTemp->setSenderName($adminUsername);
			$emailTemp->setSenderEmail($adminEmail);
			$emailTemp->send($useremail,$Username,$emailTempVariables);		
		}
		
		Mage::dispatchEvent('mp_product_sold',array('itemwithseller'=>$seller_items_array));
	}	

	public function updateCreditMemoQty($observer)
	{
		$creditmemo = $observer->getEvent()->getCreditmemo();
		$order_id=$observer->getEvent()->getCreditmemo()->getOrderId();
		$mpcod_seller_coll=array();
	    foreach ($creditmemo->getAllItems() as $item) {
			$refunded_qty=$item->getQty();
			$refunded_price=$refunded_qty*$item->getPrice();
			$product_id=$item->getProductId();
			$seller_products = Mage::getModel('marketplace/saleslist')->getCollection()
						    	->addFieldToFilter('order_item_id',array('eq'=>$item->getOrderItemId()))
						    	->addFieldToFilter('mageproid',array('eq'=>$item->getProductId()))
						    	->addFieldToFilter('mageorderid',$order_id);
			foreach ($seller_products as $seller_product) {
				$updated_qty = $seller_product['magequantity']-$refunded_qty;
				$updated_price = $seller_product['totalamount']-$refunded_price;
				if(!$seller_product['totalamount']){
					$seller_product['totalamount'] = 1;
				}		
				$commission_percentage = ($seller_product['totalcommision']*100)/$seller_product['totalamount'];
				$updated_commission = ($updated_price*$commission_percentage)/100;
				$updated_seller_amount = $updated_price-$updated_commission;
				$partner_remain_seller = $seller_product->getActualparterprocost()-$updated_seller_amount;

				$seller_product->setMagequantity($updated_qty);
				$seller_product->setTotalamount($updated_price);
				$seller_product->setTotalcommision($updated_commission);
				$seller_product->setActualparterprocost($updated_seller_amount);
				$seller_product->setTotaltax(0);
				$seller_product->save();
				
				$seller_arr[$seller_product['mageproownerid']]['updated_commission'] = $updated_commission;
				if(!isset($seller_arr[$seller_product['mageproownerid']])){
		            $mpcod_seller_coll[$seller_product['mageproownerid']]=array();
		        }	        
		        if($seller_product['cpprostatus']==1){
		        	if(!isset($seller_arr[$seller_product['mageproownerid']]['totalsale'])){
		        		$seller_arr[$seller_product['mageproownerid']]['totalsale'] = 0;
		        	}
		        	if(!isset($seller_arr[$seller_product['mageproownerid']]['totalremain'])){
		        		$seller_arr[$seller_product['mageproownerid']]['totalremain'] = 0;
		        	}
		        	$seller_arr[$seller_product['mageproownerid']]['totalsale'] = $seller_arr[$seller_product['mageproownerid']]['totalsale']+$refunded_price;
		        	$seller_arr[$seller_product['mageproownerid']]['totalremain'] = $seller_arr[$seller_product['mageproownerid']]['totalremain']+$partner_remain_seller;
		        }
			}
	    }
	    foreach ($seller_arr as $seller_id => $value) {
    		$collectionverifyread = Mage::getModel('marketplace/saleperpartner')->getCollection();
			$collectionverifyread->addFieldToFilter('mageuserid',array('eq'=>$seller_id));
			foreach($collectionverifyread as $verifyrow){
				if(!isset($seller_arr[$seller_id]['totalsale'])){
					$verifyrow->setTotalsale($verifyrow->getTotalsale()-$seller_arr[$seller_id]['totalsale']);
				}
				if(!isset($seller_arr[$seller_id]['totalremain'])){
					$verifyrow->setAmountremain($verifyrow->getAmountremain()-$seller_arr[$seller_id]['totalremain']);
				}
				$verifyrow->save();
			}
	    }		
	}	
}
