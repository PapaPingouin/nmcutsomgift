<?php
if (!defined('_PS_VERSION_'))
  exit;
 
class nmcustomgift extends Module
{
	// public $customgift_id = 19;
	
	
	public function __construct()
	{
		$this->name = 'nmcustomgift';
		$this->tab = 'front_office_features';
		$this->version = '1.0';
		$this->author = 'PapaPingouin';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.8');
		$this->dependencies = array('blockcart');

		parent::__construct();

		$this->displayName = $this->l('CustomGift');
		$this->description = $this->l('Custom gift');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

		if (!Configuration::get('nmcustomgift'))      
			$this->warning = $this->l('No name provided');
	}
  
	public function install()
	{
		if (Shop::isFeatureActive())
			Shop::setContext(Shop::CONTEXT_ALL);

		return parent::install() &&
		$this->registerHook('displayShoppingCartFooter') &&
		$this->registerHook('header') &&
		$this->registerHook('displayFooterProduct') &&
		Configuration::updateValue('CUSTOMGIFT_PRODUCTID', '0');
	}
	
	public function uninstall()
	{
		if (!parent::uninstall() || !Configuration::deleteByName('CUSTOMGIFT_PRODUCTID'))
			return false;
		return true;
	}
	
	public function getContent()
	{
		$html = '';
		// If we try to update the settings
		if (Tools::isSubmit('submitModule'))
		{				
			Configuration::updateValue('CUSTOMGIFT_PRODUCTID', Tools::getValue('customgift_productid'));
			$this->_clearCache('nmcustomgift.tpl');
			$this->_clearCache('nmcustomgiftcartmodify.tpl');
			$html .= $this->displayConfirmation($this->l('Configuration updated'));
		}

		$html .= $this->renderForm();

		return $html;
	}
	
	public function renderForm()
	{
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Settings'),
					'icon' => 'icon-cogs'
				),
				'description' => $this->l('Set the id number of the gift product.'),
				'input' => array(
					array(
						'type' => 'text',
						'label' => $this->l('Product ID'),
						'name' => 'customgift_productid',
					),
					
				),
				'submit' => array(
					'title' => $this->l('Save'),
				)
			),
		);
		
		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table =  $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();

		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitModule';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
	}
	
	public function getConfigFieldsValues()
	{
		return array(
			'customgift_productid' => Tools::getValue('customgift_productid', Configuration::get('CUSTOMGIFT_PRODUCTID')),
		);
	}
	
	
	public function hookDisplayFooterProduct($params)
	{
		//if( $params['product']->id == $this->customgift_id ) // remove all add to cart and quantity
		if( $params['product']->id == Configuration::get('CUSTOMGIFT_PRODUCTID') ) // remove all add to cart and quantity
		{
			//echo "<html><script type='text/javascript'>var data = ".json_encode( $params )."</script></html>"; die();
			return "<style type='text/css'>#pb-right-column1{ display: none; }</style>";
		}
	}
  
	public function hookDisplayShoppingCartFooter($params)
	{
		if( Configuration::get('CUSTOMGIFT_PRODUCTID') <1 )
			return;
		
		$produit = new Product( Configuration::get('CUSTOMGIFT_PRODUCTID') , true, (int)($this->context->cookie->id_lang));
		if( !$produit )
			return;
			
		$fields = $produit->getCustomizationFieldIds();
		$index = $fields[0]['id_customization_field'];
		
		$images = $produit->getImages( (int)($this->context->cookie->id_lang) );
		foreach( $images as $i )
			if( $i['cover'] )
				$image_id = $i['id_image'];
		$image = new Image( $image_id );
		$image_url = '/shop/img/p/'.$image->getImgPath().'.jpg';		
		//print_r( $image_url ); die();
		
		
		$products = $this->context->cart->getProducts() ;
		//print_r( $products ); die();
		foreach( $products as $p )
		{
			//echo $p['id_product'];
			if( $p['id_product'] == Configuration::get('CUSTOMGIFT_PRODUCTID') )
			{
				$custom = $this->context->cart->getProductCustomization( $p['id_product'] );
				
				
				if( empty($custom) )
				{
					$this->smarty->assign('nmcustomgift_title', $this->l('Gift customization') );
					$this->smarty->assign('nmcustomgift_info', $this->l('Write text to put on the back of the card.') );
					$this->smarty->assign('nmcustomgift_info2', $this->l('Package included !') );
					$this->smarty->assign('nmcustomgift_save', $this->l('Save') );
					$this->smarty->assign('nmcustomgift_image_url', $image_url );
					
					$this->context->controller->addCSS($this->_path.'css/nmcustomgift.css', 'all');
					
					return $this->display(__FILE__, 'nmcustomgift.tpl');
					
				}
				else
				{
					$this->smarty->assign('customgift_id', Configuration::get('CUSTOMGIFT_PRODUCTID') );
					$this->smarty->assign('customgist_customization', $custom[0]['id_customization'] );
					$this->smarty->assign('gift', $this->l('Gift !') );
					
					return $this->display(__FILE__,'nmcustomgiftcartmodify.tpl' );
				}
				//print_r( $this->context->cart->getProductCustomization( $p['product_id'] ) ); 
			}
		
		} 
		//die();
		
		
		//$products = $this->context->cart->getProducts() ;
		//print_r( $products ); die();
		
		
		//$context=Context::getContext();
		/*
		header( 'Content-type: text/html');
		echo "<html><script type='text/javascript'>var data = ".json_encode( $this->context->cart->getSummaryDetails() )."</script></html>"; die();
		//echo json_encode( $context ); die();
		return "<h3>GROS TEST</h3>";
		*/
		/*
		$this->context->smarty->assign(
		array(
			'my_module_name' => Configuration::get('MYMODULE_NAME'),
			'my_module_link' => $this->context->link->getModuleLink('mymodule', 'display')
			)
		);
		return $this->display(__FILE__, 'mymodule.tpl');
		*/
	}
  
	public function hookDisplayHeader()
	{
		//die('header');
		$this->context->controller->addCSS($this->_path.'css/nmcustomgift.css', 'all');
		
		//$this->context->cart->deleteProduct(19);
		//$this->context->cart->update();
		
		//$produit = new Product(19, true, (int)($this->context->cookie->id_lang));
			
		//print_r( Product::getDefaultAttribute( 19,0 ) ); die();	
		/*
		if( isset( $_GET['test'] ) )
		{
			$produit = new Product(19, true, (int)($this->context->cookie->id_lang));
			$fields = $produit->getCustomizationFieldIds();
			print_r( $fields );
			$index = $fields[0]['id_customization_field'];
			
			$this->context->cart->addTextFieldToProduct(19, $index, Product::CUSTOMIZE_TEXTFIELD, 'Wazzaaa');
			$customization = $this->context->cart->getProductCustomization(19);
			
			//print_r( $customization );
			
			Db::getInstance()->execute('UPDATE ps_customization SET in_cart = 1 WHERE id_customization = ' .$customization[0]['id_customization']);
			
			$this->context->cart->updateQty(1, 19, null, $customization[0]['id_customization']);                
			$this->context->cart->update();
			
		}*/
		
		if( isset( $_GET['saveCustomGift'] ) )
		{
			
			$produit = new Product( Configuration::get('CUSTOMGIFT_PRODUCTID') , true, (int)($this->context->cookie->id_lang));
			$fields = $produit->getCustomizationFieldIds();
			//print_r( $fields );
			$index = $fields[0]['id_customization_field'];
			
			$customization = $this->context->cart->getProductCustomization( Configuration::get('CUSTOMGIFT_PRODUCTID') );
			if( !empty( $customization ) )
			{
				$sql = 'UPDATE ps_customized_data SET value = "'.addslashes($_GET['saveCustomGift']).'" WHERE id_customization = ' .$customization[0]['id_customization'];
				//echo $sql; die();
				Db::getInstance()->execute($sql);
			}
			else
			{
			
				$this->context->cart->deleteProduct( Configuration::get('CUSTOMGIFT_PRODUCTID') );
				
				$this->context->cart->addTextFieldToProduct( Configuration::get('CUSTOMGIFT_PRODUCTID') , $index, Product::CUSTOMIZE_TEXTFIELD, $_GET['saveCustomGift']);
				$customization = $this->context->cart->getProductCustomization( Configuration::get('CUSTOMGIFT_PRODUCTID') );
				
				//print_r( $customization );
				
				Db::getInstance()->execute('UPDATE ps_customization SET in_cart = 1 WHERE id_customization = ' .$customization[0]['id_customization']);
				
				$this->context->cart->updateQty(1, Configuration::get('CUSTOMGIFT_PRODUCTID') , null, $customization[0]['id_customization']);                
				$this->context->cart->update();
			}
			
			
			
			/*
			$customization = $this->context->cart->getProductCustomization(19);
			$sql = 'UPDATE ps_customized_data SET value = "'.addslashes($_GET['saveCustomGift']).'" WHERE id_customization = ' .$customization[0]['id_customization'];
			//echo $sql; die();
			Db::getInstance()->execute($sql);
			*/
		}
		
		
		/*
		$produit = new Product(19, true, (int)($this->context->cookie->id_lang));
		$fields = $produit->getCustomizationFieldIds();
		$index = $fields[0]['id_customization_field'];
		
		$products = $this->context->cart->getProducts() ;
		//print_r( $products ); die();
		foreach( $products as $p )
		{
			//echo $p['id_product'];
			if( $p['reference'] == 'Voeux2015' )
			{
				$custom = $this->context->cart->getProductCustomization( $p['id_product'] );
				if( empty( $custom ) )
				{
					print_r( $custom ); die();
					
					$this->context->cart->addTextFieldToProduct( $p['id_product'],$index,Product::CUSTOMIZE_TEXTFIELD,'test8'  );  
					//$this->context->cart->updateQty(1, $p['id_product'],null,null,'down');
					$this->context->cart->updateQty(2, $p['id_product'], null, 15);
					
				}
				//print_r( $this->context->cart->getProductCustomization( $p['product_id'] ) ); 
			}
			print_r( $this->context->cart->getProductCustomization( $p['id_product'] ) ); 
		} 
		//die();
		
		$products = $this->context->cart->getProducts() ;
		
		*/
	}   
  
}
?>
