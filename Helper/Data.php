<?php


namespace OrviSoft\FeedCronScripts\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $_stockItemRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $_dir;

    /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory */
    protected $collectionFactory;

    /**
     * @var \Magento\Catalog\Helper\ImageFactory
     */
    protected $imageHelperFactory;


    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockItemRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     * @param \Magento\Catalog\Helper\Image $imageHelperFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockItemRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Catalog\Helper\Image $imageHelperFactory
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->collectionFactory = $collectionFactory;
        $this->_stockItemRepository = $stockItemRepository;
        $this->storeManager = $storeManager;
        $this->_dir = $dir;
        $this->imageHelperFactory = $imageHelperFactory;
    }

    /**
     * @return \Magento\CatalogInventory\Api\StockRegistryInterface::getStockItem($productId)
     */
    public function getStockItem($productId){
        return $this->_stockItemRepository->getStockItem($productId);
    }

    /**
     * @return bool
     */
    public function isEnabled($which = 'googlefeed'){
        return (boolean)$this->getConfig("feeds/$which/enabled");
    }

    /**
     * @return string
     */
    public function getConfig($config_path){
        return $this->scopeConfig->getValue(
                $config_path,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
    }

    /**
     * @return product collection
     */
    public function getProducts(){
        $products = $this->collectionFactory->create();
                                            //->setFlag('has_stock_status_filter', false)->load();
        $products->addAttributeToSelect('*');
        return $products;
    }

    /**
     * @return string
     */
    public function getBaseUrl(){
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB,true);
    }

    /**
     * @return void
     */
    public function generateGoogleFeeds($debug = false){
        if(!$this->isEnabled('googlefeed')){
            return false;
        }
        $webUrl = $this->getBaseUrl();
        $products = $this->getProducts()->addAttributeToFilter('googlemerchant', 1);
        if(!$debug):
            $saveFileName = $this->getConfig('feeds/googlefeed/file_name');
            $rootPath = $this->_dir->getRoot().DIRECTORY_SEPARATOR;
            $fopen = fopen($rootPath.$saveFileName, 'w');
            $csvHeader = array("Id", 'Title' ,'Descripiton' , 'Link', "Condition" , 'Availability' , 'Price' , 'Image Link', 'Audlt', 'Gtin', 'Brand', 'Google product category');// Add the fields you need to export
            fputcsv( $fopen , $csvHeader,",");
        else:
            $increamenet = 0;
        endif;
        if(!$products->count()){
            return;
        }
        foreach($products as $_product):
            $stock = $this->getStockItem($_product->getId());
            $stock_text = 'Out Of Stock';
            if($stock->getQty() > 0){
                $stock_text = "In Stock";
            }

            $id = $_product->getId();
            $name = $_product->getName();
            
            $description = $name;
            $condition = '';
            $condition_att = $_product->getResource()->getAttribute('conditionstatus');
            if ($condition_att){
                $condition = $condition_att->getFrontend()->getValue($_product);
            }
            $image = $this->imageHelperFactory->init($_product, 'product_thumbnail_image')
                                                ->setImageFile($_product->getFile())
                                                ->constrainOnly(true)
                                                ->keepAspectRatio(true)
                                                ->keepTransparency(true)
                                                ->keepFrame(false)
                                                ->resize(200, 300)->getUrl();
            $audit = 'No';
            $category_google = $_product->getGoogleproductcategory();
            $brand = $_product->getBrand();
            $price =  number_format($_product->getPrice(), '2', '.', ',');
            $url = $_product->getData('url_key');
            $attribute = $_product->getResource()->getAttribute('ean');
            if ($attribute){
                $mpnvalue = $attribute->getFrontend()->getValue($_product);
                $mpnvalue = $mpnvalue == 'No' ? '' : $mpnvalue;
            }
                
            $attribute_type = $_product->getResource()->getAttribute('networkingfiltertype');
            if ($attribute_type){
                $typevalue = $attribute_type->getFrontend()->getValue($_product);
            }
            if(!$debug):
                fputcsv($fopen, array($id, $name, $description, ($webUrl.$url.'.html'), $condition, $stock_text, $price.' GBP', $image, $audit, $mpnvalue, $brand, $category_google), ",");
            else:
                $increamenet++;
                if($increamenet > 10){
                    break 1;
                }
                echo '<pre>';
                print_r(array($id, $name, $description, ($webUrl.$url.'.html'), $condition, $stock_text, $price.' GBP', $image, $audit, $mpnvalue, $brand, $category_google));
                echo "</pre>";
            endif;
        endforeach;
    }

    /**
     * @return void
     */
    public function generatePixelFeeds($debug = false){
        if(!$this->isEnabled('pixelfeed')){
            return false;
        }
        $webUrl = $this->getBaseUrl();
        $products = $this->getProducts();
        if(!$debug):
            $saveFileName = $this->getConfig('feeds/pixelfeed/file_name');
            $rootPath = $this->_dir->getRoot().DIRECTORY_SEPARATOR;
            $fopen = fopen($rootPath.$saveFileName, 'w');
            $csvHeader = array("brand", 'MPN' ,'ean' , 'name', "price" , 'product_url' , 'qty' , 'Category', 'Vendor');
            fputcsv( $fopen , $csvHeader,",");
        else:
            $increamenet = 0;
        endif;
        if(!$products->count()){
            return;
        }
        foreach($products as $_product):
            $stock = $this->getStockItem($_product->getId());
            $mpnvalue = $brandvalue = $eanvalue = $typevalue = '';
            $name = $_product->getName();
            
            $attribute_brand = $_product->getResource()->getAttribute('brand');
            if ($attribute_brand){
                $brandvalue = $attribute_brand ->getFrontend()->getValue($_product);
            }
            $ean = $_product->getEan();

            $price =  number_format($_product->getPrice(), '2', '.', ',');
            $url = $_product->getData('url_key');
            $attribute = $_product->getResource()->getAttribute('mpn');

            if ($attribute){
                $mpnvalue = $attribute ->getFrontend()->getValue($_product);
                $mpnvalue = $mpnvalue == 'No' ? '' : $mpnvalue;
            }
            
            $attribute_type = $_product->getResource()->getAttribute('networkingfiltertype');
            if ($attribute_type){
                $typevalue = $attribute_type ->getFrontend()->getValue($_product);
            }
           
            if(!$debug):
                fputcsv($fopen, array($brandvalue, $mpnvalue, $ean, $name , $price , ($webUrl.$url.'.html') , $stock->getQty(), $typevalue , 'ASUS'), ",");
            else:
                $increamenet++;
                if($increamenet > 10){
                    break 1;
                }
                echo '<pre>';
                print_r(array($brandvalue, $mpnvalue, $ean, $name , $price , ($webUrl.$url.'.html') , $stock->getQty(), $typevalue , 'ASUS'));
                echo "</pre>";
            endif;
        endforeach;
    }

    /**
     * @return void
     */
    public function generatePriceSearcherFeeds($debug = false){
        if(!$this->isEnabled('pricesearcher')){
            return false;
        }
        $webUrl = $this->getBaseUrl();
        $products = $this->getProducts()->setFlag('has_stock_status_filter', true);
        if(!$debug):
            $saveFileName = $this->getConfig('feeds/pricesearcher/file_name');
            $rootPath = $this->_dir->getRoot().DIRECTORY_SEPARATOR;
            $fopen = fopen($rootPath.$saveFileName, 'w');
            $csvHeader = array("brand", 'MPN' ,'ean' , 'name', "price" , 'product_url' , 'qty' , 'Category', 'Image');
            fputcsv( $fopen , $csvHeader,",");
        else:
            $increamenet = 0;
        endif;
        if(!$products->count()){
            return;
        }
        foreach($products as $_product):
            $stock = $this->getStockItem($_product->getId());
            $mpnvalue = $brandvalue = $eanvalue = $typevalue = '';
            $name = $_product->getName();
            
            $attribute_brand = $_product->getResource()->getAttribute('brand');
            if ($attribute_brand){
                $brandvalue = $attribute_brand ->getFrontend()->getValue($_product);
            }
            $ean = $_product->getEan();

            $price =  number_format($_product->getPrice(), '2', '.', ',');
            $url = $_product->getData('url_key');
            $attribute = $_product->getResource()->getAttribute('mpn');

            if ($attribute){
                $mpnvalue = $attribute ->getFrontend()->getValue($_product);
                $mpnvalue = $mpnvalue == 'No' ? '' : $mpnvalue;
            }
            
            $attribute_type = $_product->getResource()->getAttribute('networkingfiltertype');
            if ($attribute_type){
                $typevalue = $attribute_type ->getFrontend()->getValue($_product);
            }
            $image = $this->imageHelperFactory->init($_product, 'product_thumbnail_image')
                                                ->setImageFile($_product->getFile())
                                                ->constrainOnly(true)
                                                ->keepAspectRatio(true)
                                                ->keepTransparency(true)
                                                ->keepFrame(false)
                                                ->resize(200, 300)->getUrl();
            if(!$debug):
                fputcsv($fopen, array($brandvalue, $mpnvalue, $ean, $name , $price , ($webUrl.$url.'.html') , $stock->getQty(), $typevalue , $image), ",");
            else:
                $increamenet++;
                if($increamenet > 10){
                    break 1;
                }
                echo '<pre>';
                print_r(array($brandvalue, $mpnvalue, $ean, $name , $price , ($webUrl.$url.'.html') , $stock->getQty(), $typevalue , $image));
                echo "</pre>";
            endif;
        endforeach;
    }

    /**
     * @return void
     */
    public function generateWebCollageFeeds($debug = false){
        if(!$this->isEnabled('webcollage')){
            return false;
        }
        $webUrl = $this->getBaseUrl();
        $products = $this->getProducts();
        if(!$debug):
            $saveFileName = $this->getConfig('feeds/webcollage/file_name');
            $rootPath = $this->_dir->getRoot().DIRECTORY_SEPARATOR;
            $fopen = fopen($rootPath.$saveFileName, 'w');
            $csvHeader = array("id","sku", "brand", 'MPN' ,'ean' , 'name', "price" , 'product_url');
            fputcsv( $fopen , $csvHeader,",");
        else:
            $increamenet = 0;
        endif;
        if(!$products->count()){
            return;
        }
        foreach($products as $_product):
            $stock = $this->getStockItem($_product->getId());
            $mpnvalue = $brandvalue = $eanvalue = $typevalue = '';
            $id = $_product->getId();
	        $sku = $_product->getSku();
            $name = $_product->getName();
            
            $attribute_brand = $_product->getResource()->getAttribute('brand');
            if ($attribute_brand){
                $brandvalue = $attribute_brand ->getFrontend()->getValue($_product);
            }
            $ean = $_product->getEan();

            $price =  number_format($_product->getPrice(), '2', '.', ',');
            $url = $_product->getData('url_key');
            $attribute = $_product->getResource()->getAttribute('mpn');

            if ($attribute){
                $mpnvalue = $attribute ->getFrontend()->getValue($_product);
                $mpnvalue = $mpnvalue == 'No' ? '' : $mpnvalue;
            }
            
            if(!$debug):
                fputcsv($fopen, array($id,$sku,$brandvalue, $mpnvalue, $ean, $name, $price , ($webUrl.$url.'.html')), ",");
            else:
                $increamenet++;
                if($increamenet > 10){
                    break 1;
                }
                echo '<pre>';
                print_r(array($id,$sku,$brandvalue, $mpnvalue, $ean, $name, $price , ($webUrl.$url.'.html')));
                echo "</pre>";
            endif;
        endforeach;
    }
}
