<?php chdir('/var/www/html/magento');define('PATH_TO_MAGENTO', '.');require_once PATH_TO_MAGENTO.'/app/Mage.php';Mage::app('');$clazz = '#M#A#M#CMage_Core_Model_Abstract.catalogProductNewAction.0.getProduct|?';$keys = array();
$types = array();

		$reflectionClass = new ReflectionClass($clazz);
		if( ! $reflectionClass->isInstantiable()){
			exit();
		}

		$object = new $clazz;
		if($object instanceof Mage_Core_Model_Abstract)
		{
			$intTypes = array('int', 'tinyint');
			$resource = $object->getResource();
			if($resource instanceof Mage_Eav_Model_Entity_Abstract){
				$resource->loadAllAttributes();
				foreach ($resource->getAttributesByCode() as $attrCode => $attribute) {
					$keys[] = $attrCode;
					$types[] = in_array($attribute->getBackendType(), $intTypes) ? 'int' : 'string';    // static, varchar, int, text, datetime 
				}
			}
			elseif($resource instanceof Mage_Core_Model_Resource_Db_Abstract) {
				$fields = $resource->getReadConnection()->describeTable($resource->getMainTable());
				$keys = array_keys($fields);
				foreach($fields as $fieldName => $fieldData){ 
					$types[] = isset($fieldData['DATA_TYPE']) && in_array($fieldData['DATA_TYPE'], $intTypes) ? 'int' : 'string'; 
				} 
			}
		}
		
	if($keys) 
		echo '0'.implode(',', $keys).':'.implode(',', $types);