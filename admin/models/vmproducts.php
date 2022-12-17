<?php

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class MigratorvmpsModelVmproducts extends ListModel
{
    /**
     * Method to get a JDatabaseQuery object for retrieving the data set from a database.
     *
     * @return  JDatabaseQuery  A JDatabaseQuery object to retrieve the data set.
     *
     * @since  2.0.0
     */
    protected function getListQuery()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('
                  pl.name as name,
                  cp.id_product,
                  cl.name as category,
                  pl.description as description,
                  pl.description_short as description_short,
                  pl.link_rewrite as link_rewrite,
                  p.price')
            ->from('m_category_product as cp')
            ->join('LEFT', 'm_product_lang AS pl ON cp.id_product = pl.id_product')
            ->join('LEFT', 'm_category_lang AS cl ON cp.id_category = cl.id_category')
            ->join('LEFT', 'm_product AS p ON cp.id_product = p.id_product')
        ;

        return $query;
    }

    public function copyProducts()
    {
        $items = $this->getItems();
        $application = Factory::getApplication();

        if ($items) {
            $application->enqueueMessage(Text::_('COM_MIGRATORVMPS_DATA_HAS_ALREADY_BEEN_COPIED'), 'notice');
            return true;
        }
        $db = $this->getDbo();

        $db->setQuery("INSERT INTO `m_category_map` (`id_category_old`, `id_category`, `id_parent_old`, `id_parent`)
            SELECT 
                `virtuemart_category_id` AS `id_category_old`,
                `virtuemart_category_id`+2 AS `id_category`,
                `category_parent_id` AS `id_parent_old`,
                `category_parent_id`+2 AS `id_parent`
            FROM `#__virtuemart_categories`                               
            ORDER BY `virtuemart_category_id` 
        ;")->execute();

        $db->setQuery("INSERT INTO `m_product_map` (`id_product_old`)
            SELECT 
                `virtuemart_product_id` AS `id_product_old`                
            FROM `#__virtuemart_products`                                         
            ORDER BY `virtuemart_product_id` 
        ;")->execute();

        $db->setQuery("REPLACE INTO `m_category` (`id_category`, `id_parent`, `date_add`, `date_upd`)
            SELECT 
            `cm`.`id_category` AS `id_category`,
            `cm`.`id_parent` AS `id_parent`,
            `c`.`created_on` AS `date_add`, 
            `c`.`modified_on` AS `date_upd`
            FROM `#__virtuemart_categories` AS `c`
            LEFT JOIN `m_category_map` AS `cm`
            ON `c`.`virtuemart_category_id` = `cm`.`id_category_old`
        ;")->execute();

        $db->setQuery("REPLACE INTO `m_category_group` (`id_category`, `id_group`)
            SELECT 
                `id_category` AS `id_category`,
                '1' AS `id_group`
            FROM `m_category_map`
            UNION    
            SELECT            
                `id_category` AS `id_category`,
                '2' AS `id_group`
            FROM `m_category_map`
            UNION    
            SELECT            
                `id_category` AS `id_category`,
                '3' AS `id_group`
            FROM `m_category_map`            
            ORDER BY `id_category`;           
        ;")->execute();

        $db->setQuery("REPLACE INTO `m_category_lang` (`id_category`, `name`, `description`, `link_rewrite`)
            SELECT 
                `cm`.`id_category` AS `id_category`,
                `vc`.`category_name` AS `name`,
                `vc`.`category_description` AS `description`, 
                `vc`.`slug` AS `link_rewrite`
            FROM `#__virtuemart_categories_ru_ru` AS `vc`
            LEFT JOIN `m_category_map` AS `cm`
            ON `vc`.`virtuemart_category_id` = `cm`.`id_category_old`
        ;")->execute();

        $db->setQuery("REPLACE INTO `m_category_product` (`id_category`, `id_product`)
            SELECT 
                `cm`.`id_category` AS `id_category`,
                `m`.`id_product` AS `id_product`            
            FROM `#__virtuemart_product_categories` AS `vpc`
            LEFT JOIN `m_product_map` AS `m`
            ON `vpc`.`virtuemart_product_id` = `m`.`id_product_old`
            LEFT JOIN `m_category_map` AS `cm`
            ON `vpc`.`virtuemart_category_id` = `cm`.`id_category_old`    

        ;")->execute();

        $db->setQuery("REPLACE INTO `m_category_shop` (`id_category`)
            SELECT 
                `id_category` AS `id_category`                        
            FROM `m_category_map`
        ;")->execute();

        $db->setQuery("REPLACE INTO `m_product` (`id_product`, `id_category_default`, `price`, `date_add`, `date_upd`)
            SELECT 
                `m`.`id_product` AS `id_product`,
                `cp`.`id_category` AS `id_category_default`,
                `vpp`.`product_price` AS `price`,
                `vp`.`created_on` AS `date_add`, 
                `vp`.`modified_on` AS `date_upd` 
            FROM `m_product_map` AS `m`                     
            LEFT JOIN `#__virtuemart_products` AS `vp`
            ON `vp`.`virtuemart_product_id` = `m`.`id_product_old`
            LEFT JOIN `m_category_product` AS `cp` 
            ON `cp`.`id_product` = `m`.`id_product`   
            LEFT JOIN `#__virtuemart_product_prices` AS `vpp` 
            ON `vpp`.`virtuemart_product_id` = `m`.`id_product_old`      
        ;")->execute();

        $db->setQuery("REPLACE INTO `m_product_lang` (`id_product`, `name`, `description`, `description_short`,
            `link_rewrite`)
            SELECT
                `m`.`id_product` as `id_product`,        	
                `p`.`product_name` AS `name`,
                `p`.`product_desc` AS `description`,
                `p`.`product_s_desc` AS `description_short`,
                `p`.`slug` AS `link_rewrite`                           
            FROM `#__virtuemart_products_ru_ru` AS `p`            
            LEFT JOIN `m_product_map` AS `m`
            ON `m`.`id_product_old`=`p`.`virtuemart_product_id`
            ORDER BY `p`.`virtuemart_product_id`             
        ;")->execute();

        $db->setQuery("REPLACE INTO `m_product_shop` (`id_product`, `id_shop`, 
        `id_category_default`, `id_tax_rules_group`, `price`, `active`, `date_add`, `date_upd`)
            SELECT 
            `m`.`id_product` AS `id_product`,
            1 AS `id_shop`,
            `cp`.`id_category`AS `id_category_default`,
            1 AS`id_tax_rules_group`,
            `vpp`.`product_price` AS `price`,
            1 AS `active`,
            `vp`.`created_on` AS `date_add`, 
            `vp`.`modified_on` AS `date_upd`  
            FROM `m_product_map` AS `m`
            LEFT JOIN `#__virtuemart_products` AS `vp`
            ON `vp`.`virtuemart_product_id` = `m`.`id_product_old`
            LEFT JOIN `m_category_product` AS `cp` 
            ON `cp`.`id_product` = `m`.`id_product`
            LEFT JOIN `#__virtuemart_product_prices` AS `vpp` 
            ON `vpp`.`virtuemart_product_id` = `m`.`id_product_old`               
        ;")->execute();

        $db->setQuery("REPLACE INTO `m_image_lang` (`id_image`)
            SELECT `m`.`virtuemart_media_id` as `id_image`                                         
            FROM `#__virtuemart_medias` AS `m`            
            ORDER BY `m`.`virtuemart_media_id`             
        ;")->execute();

        $db->setQuery("REPLACE INTO `m_image` (`id_image`, `id_product`)
            SELECT 
                `pm`.`virtuemart_media_id` as `id_image`,
                `m`.`id_product` as `id_product`                                         
            FROM `#__virtuemart_product_medias` AS `pm`
            LEFT JOIN `m_product_map` AS `m`
            ON `m`.`id_product_old` = `pm`.`virtuemart_product_id`                    
        ;")->execute();

        $db->setQuery("REPLACE INTO `m_image_shop` (`id_product`, `id_image`)
            SELECT
                `m`.`id_product` as `id_product`, 
                `pm`.`virtuemart_media_id` as `id_image`                                                   
            FROM `#__virtuemart_product_medias` AS `pm`
            LEFT JOIN `m_product_map` AS `m`
            ON `m`.`id_product_old` = `pm`.`virtuemart_product_id`                  
        ;")->execute();

        $application->enqueueMessage(Text::_('COM_MIGRATORVMPS_DATA_HAS_BEEN_COPIED_SUCCESSFULY'), 'Message');
    }

    public function resetData()
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true);
        $query->delete('m_product_map');
        $db->setQuery($query)->execute();

        $query = $db->getQuery(true);
        $db->setQuery("ALTER TABLE `m_product_map` AUTO_INCREMENT = 1")->execute();

        $query = $db->getQuery(true);
        $query->delete('m_category_map');
        $db->setQuery($query)->execute();

        $query = $db->getQuery(true);
        $query->delete('m_category');
        $db->setQuery($query)->execute();

        $query = $db->getQuery(true);
        $query->delete('m_category_group');
        $db->setQuery($query)->execute();

        $query = $db->getQuery(true);
        $query->delete('m_category_lang');
        $db->setQuery($query)->execute();

        $query = $db->getQuery(true);
        $query->delete('m_category_product');
        $db->setQuery($query)->execute();

        $query = $db->getQuery(true);
        $query->delete('m_category_shop');
        $db->setQuery($query)->execute();

        $query = $db->getQuery(true);
        $query->delete('m_image');
        $db->setQuery($query)->execute();

        $query = $db->getQuery(true);
        $query->delete('m_image_shop');
        $db->setQuery($query)->execute();

        $query = $db->getQuery(true);
        $query->delete('m_product');
        $db->setQuery($query)->execute();

        $query = $db->getQuery(true);
        $db->setQuery("ALTER TABLE `m_product` AUTO_INCREMENT = 1")->execute();

        $query = $db->getQuery(true);
        $query->delete('m_product_lang');
        $db->setQuery($query)->execute();

        $query = $db->getQuery(true);
        $query->delete('m_product_shop');
        $db->setQuery($query)->execute();
        $query = $db->getQuery(true);

        $query->delete('m_image_lang');
        $db->setQuery($query)->execute();
        $query = $db->getQuery(true);

        $path_images = JPATH_COMPONENT_ADMINISTRATOR . '/prestashop_module/migratorvmps/images';
        MigratorvmpsHelper::deleteDir($path_images);

        $path_images_categories = JPATH_COMPONENT_ADMINISTRATOR . '/prestashop_module/migratorvmps/images_categories';        
        array_map('unlink', glob($path_images_categories.'/*'));

        $application = Factory::getApplication();
        $application->enqueueMessage(Text::_('COM_MIGRATORVMPS_DATA_WAS_SUCCESSFULY_DELETED'), 'Message');
    }

    public function copyImagesProducts()
    {
        $application = Factory::getApplication();
        $db = $this->getDbo();

        $db->setQuery("
            SELECT 
                `virtuemart_media_id` AS `id_product`,
                `file_url` AS `image`,
                `file_title` AS `file`
            FROM `#__virtuemart_medias`; 
        ")->execute();
        $results = $db->loadObjectList();

        foreach ($results as $item) {
            $ar = str_split($item->id_product);
            $path = implode('/', $ar);

            mkdir(JPATH_COMPONENT_ADMINISTRATOR . '/prestashop_module/migratorvmps/images/' .$path);

            $image = JPATH_SITE . '/' . $item->image;

            if (is_file($image)) {
                copy($image, JPATH_COMPONENT_ADMINISTRATOR . '/prestashop_module/migratorvmps/images/' .$path. '/' . $item->id_product . '.jpg');
            }
        }

        $application->enqueueMessage(Text::_('COM_MIGRATORVMPS_IMAGES_HAS_BEEN_COPIED_SUCCESSFULY'), 'Message');
    }

    public function copyImagesCategories()
    {
        $application = Factory::getApplication();
        $db = $this->getDbo();

        $db->setQuery("
            SELECT 
                `cm`.`id_category` AS `id_category`,
                `vm`.`file_url` AS `url`
            FROM `#__virtuemart_category_medias` AS `vcm`
            LEFT JOIN `m_category_map` AS `cm`
            ON  `vcm`.`virtuemart_category_id`=`cm`.`id_category_old`
            LEFT JOIN `#__virtuemart_medias` AS `vm`
            ON  `vcm`.`virtuemart_media_id`=`vm`.`virtuemart_media_id`
            WHERE `cm`.`id_category` > 0; 
        ")->execute();
        $results = $db->loadObjectList();

        foreach ($results as $item) {
            $image = JPATH_SITE . '/' . $item->url;

            if (is_file($image)) {
                copy($image, JPATH_COMPONENT_ADMINISTRATOR . '/prestashop_module/migratorvmps/images_categories/' . $item->id_category . '.jpg');
            }
        }

        $application->enqueueMessage(Text::_('COM_MIGRATORVMPS_IMAGES_HAS_BEEN_COPIED_SUCCESSFULY'), 'Message');
    }

    public function createQueryList()
    {
        $application = Factory::getApplication();
        $db = $this->getDbo();

        $str = "INSERT INTO `ps_product_lang` (`id_product`, `id_shop`, `id_lang`, `description`, `description_short`,
            `link_rewrite`, `meta_description`, `meta_keywords`, `meta_title`, `name`, `available_now`, 
            `available_later`, `delivery_in_stock`, `delivery_out_stock`) VALUES ";

        $string_product_lang = $this->getStringFromQuery($db, 'm_product_lang');
        $str .= $string_product_lang;

        $str .= " INSERT INTO `ps_product` (`id_product`, `id_supplier`, `id_manufacturer`, `id_category_default`, `id_shop_default`,
            `id_tax_rules_group`, `on_sale`, `online_only`, `ean13`, `isbn`, `upc`, `mpn`, `ecotax`,
            `quantity`, `minimal_quantity`, `low_stock_threshold`, `low_stock_alert`, `price`,
            `wholesale_price`, `unity`, `unit_price_ratio`, `additional_shipping_cost`, `reference`,
            `supplier_reference`, `location`, `width`, `height`, `depth`, `weight`, `out_of_stock`,
            `additional_delivery_times`, `quantity_discount`, `customizable`, `uploadable_files`,
            `text_fields`, `active`, `redirect_type`,`id_type_redirected`, `available_for_order`,
            `available_date`, `show_condition`, `condition`, `show_price`, `indexed`, `visibility`,
            `cache_is_pack`, `cache_has_attachments`, `is_virtual`, `cache_default_attribute`, `date_add`,
            `date_upd`, `advanced_stock_management`, `pack_stock_type`, `state`, `product_type`) VALUES ";

        $string_product = $this->getStringFromQuery($db, 'm_product');
        $str .= $string_product;

        $str .= " INSERT INTO `ps_product_shop` (
            `id_product`, `id_shop`, `id_category_default`, `id_tax_rules_group`, `on_sale`, `online_only`,
            `ecotax`, `minimal_quantity`, `low_stock_threshold`, `low_stock_alert`, `price`, `wholesale_price`,
            `unity`, `unit_price_ratio`, `additional_shipping_cost`, `customizable`,`uploadable_files`,
            `text_fields`, `active`, `redirect_type`, `id_type_redirected`, `available_for_order`, 
            `available_date`, `show_condition`, `condition`, `show_price`, `indexed`, `visibility`,
            `cache_default_attribute`, `advanced_stock_management`, `date_add`, `date_upd`, `pack_stock_type`            
            ) VALUES ";

        $string_product_shop = $this->getStringFromQuery($db, 'm_product_shop');
        $str .= $string_product_shop;

        $str .= " INSERT INTO `ps_image_shop` (`id_product`, `id_image`, `id_shop`, `cover`) VALUES ";

        $string_image_shop = $this->getStringFromQuery($db, 'm_image_shop');
        $str .= $string_image_shop;

        $str .= " INSERT INTO `ps_image` (`id_image`, `id_product`, `position`, `cover`) VALUES ";

        $string_image =  $this->getStringFromQuery($db, 'm_image');
        $str .= $string_image;

        $str .= " INSERT INTO `ps_image_lang` (`id_image`, `id_lang`, `legend`) VALUES ";

        $string_image_lang = $this->getStringFromQuery($db, 'm_image_lang');
        $str .= $string_image_lang;

        $str .= "REPLACE INTO `ps_category` (`id_category`, `id_parent`, `id_shop_default`,
            `level_depth`, `nleft`, `nright`, `active`, `date_add`, `date_upd`, `position`,
            `is_root_category`) VALUES ('1', '0', '1','0','0','0','1','2022-11-14 21:15:24','2022-11-14 21:15:24','0','0'),
            ('2', '1', '1','1','0','0','1','2022-11-14 21:15:24','2022-11-14 21:15:24','0','1'), ";

        $string_category = $this->getStringFromQuery($db, 'm_category');
        $str .= $string_category;

        $str .= " REPLACE INTO `ps_category_lang` (`id_category`, `id_shop`, `id_lang`,
            `name`, `description`, `link_rewrite`, `meta_title`, `meta_keywords`, 
            `meta_description`) VALUES ('1', '1', '1','Корневая','','kornevaya','','',''),
            ('2', '1', '1','Главная','','glavnaya','','',''), ";

        $string_category_lang = $this->getStringFromQuery($db, 'm_category_lang');
        $str .= $string_category_lang;

        $str .= " REPLACE INTO `ps_category_product` (`id_category`, `id_product`,
            `position`) VALUES ";

        $string_category_product = $this->getStringFromQuery($db, 'm_category_product');
        $str .= $string_category_product;

        $str .= " REPLACE INTO `ps_category_shop` (`id_category`, `id_shop`,
            `position`) VALUES ('1', '1', '0'), ('2', '1', '0'),";

        $string_category_shop = $this->getStringFromQuery($db, 'm_category_shop');
        $str .= $string_category_shop;

        $str .= " REPLACE INTO `ps_category_group` (`id_category`, `id_group`) 
            VALUES ('2', '1'), ('2', '2'), ('2', '3'),";

        $string_category_group = $this->getStringFromQuery($db, 'm_category_group');
        $str .= $string_category_group;

        file_put_contents(JPATH_COMPONENT_ADMINISTRATOR .'/prestashop_module/migratorvmps/sql/dump.sql', print_r($str, true));

        $application->enqueueMessage(Text::_('COM_MIGRATORVMPS_PRESTASHOP_MODULE_IS_READY_FOR_DOWNLOAD'), 'Message');
    }

    public function archiveProducts()
    {
        $path = JPATH_COMPONENT_ADMINISTRATOR . '/prestashop_module/';
        $archive = JPATH_COMPONENT_ADMINISTRATOR . '/prestashop_module/migratorvmps.zip';

        MigratorvmpsHelper::ZipFull($path, $archive);
    }

    private function getStringFromQuery($db, $name_table)
    {
        $db->setQuery("SELECT * FROM `" .$name_table . "`");
        $result = $db->loadAssocList();

        $data_array = [];
        foreach ($result as $item) {
            foreach ($item as $key=>&$value) {
                if (empty($value)) {
                    if (is_null($value)) {
                        $value = "NULL";
                    }
                    if ($value === 0) {
                        $value = '0';
                    }
                    if ($value === '') {
                        $value = "''";
                    }
                } else {
                    $value = html_entity_decode($value);
                    $pattern  =  '/\r\n|\r|\n/u';
                    $value = preg_replace($pattern, ' ', $value);
                    $value = "'" . addslashes($value) . "'";
                }
            }
            $data_array[] = implode(", ", $item);
        }

        $str = "(" . implode("), (", $data_array) . ");";

        return $str;
    }

    public function getItems()
    {
        $items = parent::getItems();
        if ($items) {
            $arr = [];
            $p_id = null;

            for ($i = 0; $i< count($items); $i++) {
                /*    $description = $items[$i]->description;
                    if (!empty($description)) {
                        $description =  mb_substr($description, 0, 100, 'UTF-8') . '...';
                        $items[$i]->description = $description;
                    }  */

                if ($items[$i]->id_product == $p_id) {
                    end($arr)->category .= ' || ' .$items[$i]->category;
                } else {
                    $arr[] = $items[$i];
                    $p_id = $items[$i]->id_product;
                }
            }

            return $arr;
        }

        return false;
    }
}
