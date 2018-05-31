<?php
/**
 * This file is part of SplashSync Project.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 *  @author    Splash Sync <www.splashsync.com>
 *  @copyright 2015-2017 Splash Sync
 *  @license   GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007
 *
 **/

namespace   Splash\Local\Objects;

use Splash\Core\SplashCore      as Splash;

use Splash\Models\AbstractObject;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\SimpleFieldsTrait;
use Splash\Models\Objects\ObjectsTrait;

//====================================================================//
// Prestashop Static Classes
use Shop;
use Configuration;
use Currency;
use Translate;
use DbQuery;
use Db;
use Tools;
use OrderInvoice;

/**
 * @abstract    Splash Local Object Class - Customer Invoices Local Integration
 * @author      B. Paquier <contact@splashsync.com>
 */
class Invoice extends AbstractObject
{

    // Splash Php Core Traits
    use IntelParserTrait;
    use SimpleFieldsTrait;
    use ObjectsTrait;

    // Prestashop Common Traits
    use \Splash\Local\Objects\Core\DatesTrait;
    use \Splash\Local\Objects\Core\SplashMetaTrait;
    
    // Prestashop Order Traits
    use \Splash\Local\Objects\Order\CoreTrait;
    use \Splash\Local\Objects\Order\MainTrait;
    use \Splash\Local\Objects\Order\AddressTrait;
    use \Splash\Local\Objects\Order\ItemsTrait;
    use \Splash\Local\Objects\Order\PaymentsTrait;

    // Prestashop Invoice Traits
    use \Splash\Local\Objects\Invoice\ObjectsListTrait;
    use \Splash\Local\Objects\Invoice\CRUDTrait;
    use \Splash\Local\Objects\Invoice\CoreTrait;
    use \Splash\Local\Objects\Invoice\StatusTrait;

    
    //====================================================================//
    // Object Definition Parameters
    //====================================================================//
    
    /**
     *  Object Disable Flag. Uncomment this line to Override this flag and disable Object.
     */
//    protected static    $DISABLED        =  True;
    
    /**
     *  Object Name (Translated by Module)
     */
    protected static $NAME            =  "Customer Invoice";
    
    /**
     *  Object Description (Translated by Module)
     */
    protected static $DESCRIPTION     =  "Prestashop Customers Invoice Object";
    
    /**
     *  Object Icon (FontAwesome or Glyph ico tag)
     */
    protected static $ICO     =  "fa fa-money";
    
    /**
     *  Object Synchronistion Limitations
     *
     *  This Flags are Used by Splash Server to Prevent Unexpected Operations on Remote Server
     */
    protected static $ALLOW_PUSH_CREATED         =  false;       // Allow Creation Of New Local Objects
    protected static $ALLOW_PUSH_UPDATED         =  false;       // Allow Update Of Existing Local Objects
    protected static $ALLOW_PUSH_DELETED         =  false;       // Allow Delete Of Existing Local Objects
    
    /**
     *  Object Synchronistion Recommended Configuration
     */
    protected static $ENABLE_PUSH_CREATED       =  false;         // Enable Creation Of New Local Objects when Not Existing
    protected static $ENABLE_PUSH_UPDATED       =  false;         // Enable Update Of Existing Local Objects when Modified Remotly
    protected static $ENABLE_PUSH_DELETED       =  false;         // Enable Delete Of Existing Local Objects when Deleted Remotly

    protected static $ENABLE_PULL_CREATED       =  true;         // Enable Import Of New Local Objects
    protected static $ENABLE_PULL_UPDATED       =  true;         // Enable Import of Updates of Local Objects when Modified Localy
    protected static $ENABLE_PULL_DELETED       =  true;         // Enable Delete Of Remotes Objects when Deleted Localy
    
    //====================================================================//
    // General Class Variables
    //====================================================================//

    protected $Order          = null;
    protected $Products       = null;
    protected $Payments       = null;

    //====================================================================//
    // Class Constructor
    //====================================================================//
        
    /**
     *      @abstract       Class Constructor (Used only if localy necessary)
     *      @return         int                     0 if KO, >0 if OK
     */
    public function __construct()
    {
        //====================================================================//
        // Set Module Context To All Shops
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        //====================================================================//
        //  Load Local Translation File
        Splash::Translator()->Load("objects@local");
        //====================================================================//
        // Load Splash Module
        $this->spl = Splash::local()->getLocalModule();
        //====================================================================//
        // Load Default Language
        $this->LangId   = Splash::local()->LoadDefaultLanguage();
        //====================================================================//
        // Load OsWs Currency
        $this->Currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        return true;
    }
    
//    //====================================================================//
//    // Class Main Functions
//    //====================================================================//
//
//    /**
//    *   @abstract     Return List Of available data for Customer
//    *   @return       array   $data             List of all customers available data
//    *                                           All data must match with OSWS Data Types
//    *                                           Use OsWs_Data::Define to create data instances
//    */
//    public function Fields()
//    {
//        //====================================================================//
//        // Stack Trace
//        Splash::log()->trace(__CLASS__,__FUNCTION__);
//        //====================================================================//
//        //  Load Local Translation File
//        Splash::Translator()->Load("objects@local");
//
//        //====================================================================//
//        // Load Splash Module
//        $this->spl = Splash::local()->getLocalModule();
//        if ( $this->spl == False ) {
//            return False;
//        }
//        //====================================================================//
//        // CORE INFORMATIONS
//        //====================================================================//
//        $this->buildCoreFields();
//        //====================================================================//
//        // MAIN INFORMATIONS
//        //====================================================================//
//        $this->buildMainFields();
//        //====================================================================//
//        // MAIN INVOICE LINE INFORMATIONS
//        //====================================================================//
//        $this->buildProductsLineFields();
//        //====================================================================//
//        //INVOICE PAYMENTS LIST INFORMATIONS
//        //====================================================================//
//        $this->buildPaymentLineFields();
//        //====================================================================//
//        // Publish Fields
//        return $this->fieldsFactory()->Publish();
//    }
//
//    /**
//    *   @abstract     Return List Of Customer with required filters
//    *   @param        array   $filter               Filters for Object List.
//    *   @param        array   $params               Search parameters for result List.
//    *                         $params["max"]        Maximum Number of results
//    *                         $params["offset"]     List Start Offset
//    *                         $params["sortfield"]  Field name for sort list (Available fields listed below)
//    *                         $params["sortorder"]  List Order Constraign (Default = ASC)
//    *   @return       array   $data             List of all Object main data
//    *                         $data["meta"]["total"]     ==> Total Number of results
//    *                         $data["meta"]["current"]   ==> Total Number of results
//    */
//    public function ObjectsList($filter=NULL,$params=NULL)
//    {
//        //====================================================================//
//        // Stack Trace
//        Splash::log()->trace(__CLASS__,__FUNCTION__);
//
//        //===============================customer=====================================//
//        // Build query
//        $sql = new DbQuery();
//        //====================================================================//
//        // Build SELECT
//        $sql->select("i.`id_order_invoice`  as id");            // Invoice Id
//        $sql->select("o.`id_order`          as id_order");      // Order Id
//        $sql->select("o.`id_customer`       as id_customer");   // Customer Id
//        $sql->select("i.`number`            as number");        // Invoice Internal Reference
//        $sql->select("o.`reference`         as reference");     // Order Internal Reference
//        $sql->select("c.`firstname`         as firstname");     // Customer Firstname
//        $sql->select("c.`lastname`          as lastname");      // Customer Lastname
//        $sql->select("i.`date_add`          as date_add");      // Invoice Date
//        $sql->select("o.`total_paid_tax_excl`");                // Invoice Total HT
//        $sql->select("o.`total_paid_tax_incl`");                // Invoice Total TTC
//        //====================================================================//
//        // Build FROM
//        $sql->from("order_invoice", 'i');
//        $sql->leftJoin("orders",     'o', 'i.id_order = o.id_order');
//        $sql->leftJoin("customer",  'c', 'c.id_customer = o.id_customer');
//        //====================================================================//
//        // Setup filters
//        if ( !empty($filter) ) {
//            $Where = " LOWER( i.number )        LIKE LOWER( '%" . pSQL($filter) ."%') ";
//            $Where.= " OR LOWER( o.reference )  LIKE LOWER( '%" . pSQL($filter) ."%') ";
//            $Where.= " OR LOWER( c.firstname )  LIKE LOWER( '%" . pSQL($filter) ."%') ";
//            $Where.= " OR LOWER( c.lastname )   LIKE LOWER( '%" . pSQL($filter) ."%') ";
//            $Where.= " OR LOWER( i.date_add )   LIKE LOWER( '%" . pSQL($filter) ."%') ";
//            $sql->where($Where);
//        }
//        //====================================================================//
//        // Setup sortorder
//        $SortField = empty($params["sortfield"])    ?   "date_add"  :   $params["sortfield"];
//        $SortOrder = empty($params["sortorder"])    ?   "DESC"      :   $params["sortorder"];
//        // Build ORDER BY
//        $sql->orderBy('`' . pSQL($SortField) . '` ' . pSQL($SortOrder) );
//
//        //====================================================================//
//        // Execute count request
//        Db::getInstance()->executeS($sql);
//        if (Db::getInstance()->getNumberError())
//        {
//            return Splash::log()->err("ErrLocalTpl",__CLASS__,__FUNCTION__, Db::getInstance()->getMsgError());
//        }
//        //====================================================================//
//        // Compute Total Number of Results
//        $total      = Db::getInstance()->NumRows();
//        //====================================================================//
//        // Build LIMIT
//        $sql->limit(pSQL($params["max"]),pSQL($params["offset"]));
//        //====================================================================//
//        // Execute final request
//        $result = Db::getInstance()->executeS($sql);
//        if (Db::getInstance()->getNumberError())
//        {
//            return Splash::log()->err("ErrLocalTpl",__CLASS__,__FUNCTION__, Db::getInstance()->getMsgError());
//        }
//        //====================================================================//
//        // Init Result Array
//        $Data       = array();
//        //====================================================================//
//        // For each result, read information and add to $Data
//        foreach ($result as $key => $Invoice)
//        {
//            $Object = new OrderInvoice($Invoice["id"]);
//            $Invoice["number"] = $Object->getInvoiceNumberFormatted($this->LangId);
//            $Data[$key] = $Invoice;
//        }
//        //====================================================================//
//        // Prepare List result meta infos
//        $Data["meta"]["current"]    =   count($Data);  // Store Current Number of results
//        $Data["meta"]["total"]      =   $total;  // Store Total Number of results
//        Splash::log()->deb("MsgLocalTpl",__CLASS__,__FUNCTION__,(count($Data)-1)." Invoices Found.");
//        return $Data;
//    }
//
//    /**
//    *   @abstract     Return requested Customer Data
//    *   @param        array   $id               Customers Id.
//    *   @param        array   $list             List of requested fields
//    */
//    public function Get($id=NULL,$list=0)
//    {
//        //====================================================================//
//        // Stack Trace
//        Splash::log()->trace(__CLASS__,__FUNCTION__);
//        //====================================================================//
//        // Init Reading
//        $this->In = $list;
//        //====================================================================//
//        // Load Splash Module
//        $this->spl = Splash::local()->getLocalModule();
//        if ( $this->spl == False ) {
//            return False;
//        }
//        //====================================================================//
//        // Init Object
//        $this->Object = new OrderInvoice($id);
//        if ( $this->Object->id != $id )   {
//            return Splash::log()->err("ErrLocalTpl",__CLASS__,__FUNCTION__," Unable to load Invoice (" . $id . ").");
//        }
//        $this->Order = new \Order($this->Object->id_order);
//        if ( $this->Order->id != $this->Object->id_order )   {
//            return Splash::log()->err("ErrLocalTpl",__CLASS__,__FUNCTION__," Unable to load Invoice Order (" . $this->Object->id_order . ").");
//        }
//        $this->Products     =   $this->Object->getProducts();
//        $this->Payments     =   $this->Object->getOrderPaymentCollection();
//        //====================================================================//
//        // Init Response Array
//        $this->Out  =   array( "id" => $id );
//        //====================================================================//
//        // Run Through All Requested Fields
//        //====================================================================//
//        $Fields = is_a($this->In, "ArrayObject") ? $this->In->getArrayCopy() : $this->In;
//        foreach ($Fields as $Key => $FieldName) {
//            //====================================================================//
//            // Read Requested Fields
//            $this->getCoreFields($Key,$FieldName);
//            $this->getMainFields($Key,$FieldName);
//            $this->getProductsLineFields($Key,$FieldName);
//            $this->getShippingLineFields($Key,$FieldName);
//            $this->getDiscountLineFields($Key,$FieldName);
//            $this->getPaymentLineFields($Key, $FieldName);
//        }
//        //====================================================================//
//        // Verify Requested Fields List is now Empty => All Fields Read Successfully
//        if ( count($this->In) ) {
//            foreach (clone $this->In as $FieldName) {
//                Splash::log()->war("ErrLocalWrongField",__CLASS__,__FUNCTION__, $FieldName);
//            }
//            return False;
//        }
//        //====================================================================//
//        // Return Data
//        //====================================================================//
//        Splash::log()->deb("MsgLocalTpl",__CLASS__,__FUNCTION__," DATA : " . print_r($this->Out,1));
//        return $this->Out;
//    }
//
//    /**
//    *   @abstract     Write or Create requested Customer Data
//    *   @param        array   $id               Customers Id.  If NULL, Customer needs t be created.
//    *   @param        array   $list             List of requested fields
//    *   @return       string  $id               Customers Id.  If NULL, Customer wasn't created.
//    */
//    public function Set($id=NULL,$list=NULL)
//    {
//        //====================================================================//
//        // Stack Trace
//        Splash::log()->trace(__CLASS__,__FUNCTION__);
//
//        //====================================================================//
//        // An Order Cannot Get deleted
//        Splash::log()->err("ErrLocalTpl",__CLASS__,__FUNCTION__,"You Cannot Update Prestashop Invoices");
//
//        return (int) $id;
//    }
//
//    /**
//    *   @abstract   Delete requested Object
//    *   @param      int         $id             Object Id.  If NULL, Object needs to be created.
//    *   @return     int                         0 if KO, >0 if OK
//    */
//    public function Delete($id=NULL)
//    {
//        //====================================================================//
//        // Stack Trace
//        Splash::log()->trace(__CLASS__,__FUNCTION__);
//
//        //====================================================================//
//        // An Order Cannot Get deleted
//        Splash::log()->err("ErrLocalTpl",__CLASS__,__FUNCTION__,"You Cannot Delete Prestashop Invoices");
//        return True;
//    }
//
//    //====================================================================//
//    // Fields Generation Functions
//    //====================================================================//
//
//    /**
//    *   @abstract     Build Core Fields using FieldFactory
//    */
//    private function buildCoreFields()   {
//
//        //====================================================================//
//        // Customer Object
//        $this->fieldsFactory()->Create(self::ObjectId_Encode( "ThirdParty" , SPL_T_ID))
//                ->Identifier("id_customer")
//                ->Name(Translate::getAdminTranslation("Customer ID", "AdminCustomerThreads"))
//                ->MicroData("http://schema.org/Invoice","customer")
//                ->isRequired();
//
//        //====================================================================//
//        // Order Object
//        $this->fieldsFactory()->Create(self::ObjectId_Encode( "Order" , SPL_T_ID))
//                ->Identifier("id_order")
//                ->Name($this->spl->l('Order'))
//                ->MicroData("http://schema.org/Invoice","referencesOrder")
//                ->isRequired();
//
//        //====================================================================//
//        // Invoice Reference
//        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
//                ->Identifier("number")
//                ->Name(Translate::getAdminTranslation("Invoice number", "AdminInvoices"))
//                ->MicroData("http://schema.org/Invoice","confirmationNumber")
//                ->isReadOnly()
//                ->isListed();
//
//        //====================================================================//
//        // Order Reference
//        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
//                ->Identifier("reference")
//                ->Name(Translate::getAdminTranslation("Reference", "AdminOrders"))
//                ->MicroData("http://schema.org/Order","orderNumber")
//                ->isReadOnly()
//                ->isListed();
//
//        //====================================================================//
//        // Order Date
//        $this->fieldsFactory()->Create(SPL_T_DATE)
//                ->Identifier("date_add")
//                ->Name(Translate::getAdminTranslation("Creation", "AdminSupplyOrders"))
//                ->MicroData("http://schema.org/Order","orderDate")
//                ->isReadOnly()
//                ->isRequired()
//                ->isListed();
//
//    }
//
//    /**
//    *   @abstract     Build Address Fields using FieldFactory
//    */
//    private function buildMainFields() {
//
////        //====================================================================//
////        // Delivery Date
////        $this->fieldsFactory()->Create(SPL_T_DATE)
////                ->Identifier("date_livraison")
////                ->Name($langs->trans("DeliveryDate"))
////                ->MicroData("http://schema.org/ParcelDelivery","expectedArrivalUntil");
//
//        //====================================================================//
//        // PRICES INFORMATIONS
//        //====================================================================//
//
//        $CurrencySuffix = " (" . $this->Currency->sign . ")";
//
//        //====================================================================//
//        // Order Total Price HT
//        $this->fieldsFactory()->Create(SPL_T_DOUBLE)
//                ->Identifier("total_paid_tax_incl")
//                ->Name(Translate::getAdminTranslation("Total (Tax excl.)", "AdminOrders") . $CurrencySuffix)
//                ->MicroData("http://schema.org/Invoice","totalPaymentDue")
//                ->isListed()
//                ->isReadOnly();
//
//        //====================================================================//
//        // Order Total Price TTC
//        $this->fieldsFactory()->Create(SPL_T_DOUBLE)
//                ->Identifier("total_paid_tax_excl")
//                ->Name(Translate::getAdminTranslation("Total (Tax incl.)", "AdminOrders") . $CurrencySuffix)
//                ->MicroData("http://schema.org/Invoice","totalPaymentDueTaxIncluded")
//                ->isListed()
//                ->isReadOnly();
//
//       //====================================================================//
//        // Order Current Status
//        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
//                ->Identifier("status")
//                ->Name(Translate::getAdminTranslation("Status", "AdminOrders"))
//                ->MicroData("http://schema.org/Invoice","paymentStatus")
//                ->isReadOnly()
//                ->isNotTested();
//
//        //====================================================================//
//        // INVOICE STATUS FLAGS
//        //====================================================================//
//
//        $Prefix = Translate::getAdminTranslation("Status", "AdminOrders") . " ";
//
//        //====================================================================//
//        // Is Canceled
//        // => There is no Diffrence Between a Draft & Canceled Order on Prestashop.
//        //      Any Non Validated Order is considered as Canceled
//        $this->fieldsFactory()->Create(SPL_T_BOOL)
//                ->Identifier("isCanceled")
//                ->Name($Prefix . $this->spl->l("Canceled"))
//                ->MicroData("http://schema.org/PaymentStatusType","PaymentDeclined")
//                ->Association( "isCanceled","isValidated")
//                ->Group(Translate::getAdminTranslation("Meta", "AdminThemes"))
//                ->isReadOnly();
//
//        //====================================================================//
//        // Is Validated
//        $this->fieldsFactory()->Create(SPL_T_BOOL)
//                ->Identifier("isValidated")
//                ->Name($Prefix . Translate::getAdminTranslation("Valid", "AdminCartRules"))
//                ->MicroData("http://schema.org/PaymentStatusType","PaymentDue")
//                ->Association( "isCanceled","isValidated")
//                ->Group(Translate::getAdminTranslation("Meta", "AdminThemes"))
//                ->isReadOnly();
//
//        //====================================================================//
//        // Is Paid
//        $this->fieldsFactory()->Create(SPL_T_BOOL)
//                ->Identifier("isPaid")
//                ->Name($Prefix . $this->spl->l("Paid"))
//                ->MicroData("http://schema.org/PaymentStatusType","PaymentComplete")
//                ->isReadOnly()
//                ->Group(Translate::getAdminTranslation("Meta", "AdminThemes"))
//                ->isNotTested();
//
//        return;
//    }
//
//    /**
//    *   @abstract     Build Address Fields using FieldFactory
//    */
//    private function buildProductsLineFields() {
//
//        //====================================================================//
//        // Order Line Description
//        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
//                ->Identifier("product_name")
//                ->InList("lines")
//                ->Name(Translate::getAdminTranslation("Short description", "AdminProducts"))
//                ->MicroData("http://schema.org/partOfInvoice","description")
//                ->Group(Translate::getAdminTranslation("Products", "AdminOrders"))
//                ->Association("product_name@lines","product_quantity@lines","unit_price@lines");
//
//        //====================================================================//
//        // Order Line Product Identifier
//        $this->fieldsFactory()->Create(self::ObjectId_Encode( "Product" , SPL_T_ID))
//                ->Identifier("product_id")
//                ->InList("lines")
//                ->Name(Translate::getAdminTranslation("Product ID", "AdminImport"))
//                ->MicroData("http://schema.org/Product","productID")
//                ->Group(Translate::getAdminTranslation("Products", "AdminOrders"))
//                ->Association("product_name@lines","product_quantity@lines","unit_price@lines");
//
//        //====================================================================//
//        // Order Line Quantity
//        $this->fieldsFactory()->Create(SPL_T_INT)
//                ->Identifier("product_quantity")
//                ->InList("lines")
//                ->Name(Translate::getAdminTranslation("Quantity", "AdminOrders"))
//                ->MicroData("http://schema.org/QuantitativeValue","value")
//                ->Group(Translate::getAdminTranslation("Products", "AdminOrders"))
//                ->Association("product_name@lines","product_quantity@lines","unit_price@lines");
//
//        //====================================================================//
//        // Order Line Discount
//        $this->fieldsFactory()->Create(SPL_T_DOUBLE)
//                ->Identifier("reduction_percent")
//                ->InList("lines")
//                ->Name(Translate::getAdminTranslation("Discount (%)", "AdminGroups"))
//                ->MicroData("http://schema.org/Order","discount")
//                ->Group(Translate::getAdminTranslation("Products", "AdminOrders"))
//                ->Association("product_name@lines","product_quantity@lines","unit_price@lines");
//
//        //====================================================================//
//        // Order Line Unit Price
//        $this->fieldsFactory()->Create(SPL_T_PRICE)
//                ->Identifier("unit_price")
//                ->InList("lines")
//                ->Name(Translate::getAdminTranslation("Price", "AdminOrders"))
//                ->MicroData("http://schema.org/PriceSpecification","price")
//                ->Group(Translate::getAdminTranslation("Products", "AdminOrders"))
//                ->Association("product_name@lines","product_quantity@lines","unit_price@lines");
//
//    }
//
//    /**
//    *   @abstract     Build Address Fields using FieldFactory
//    */
//    private function buildPaymentLineFields() {
//
//        //====================================================================//
//        // Payment Line Payment Method
//        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
//                ->Identifier("mode")
//                ->InList("payments")
//                ->Name(Translate::getAdminTranslation("Payment method", "AdminOrders"))
//                ->MicroData("http://schema.org/Invoice","PaymentMethod")
//                ->Group(Translate::getAdminTranslation("Payment", "AdminPayment"))
//                ->isNotTested();
//
//        //====================================================================//
//        // Payment Line Date
//        $this->fieldsFactory()->Create(SPL_T_DATE)
//                ->Identifier("date")
//                ->InList("payments")
//                ->Name(Translate::getAdminTranslation("Date", "AdminProducts"))
//                ->MicroData("http://schema.org/PaymentChargeSpecification","validFrom")
////                ->Association("date@payments","mode@payments","amount@payments");
//                ->Group(Translate::getAdminTranslation("Payment", "AdminPayment"))
//                ->isNotTested();
//
//        //====================================================================//
//        // Payment Line Payment Identifier
//        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
//                ->Identifier("number")
//                ->InList("payments")
//                ->Name(Translate::getAdminTranslation("Transaction ID", "AdminOrders"))
//                ->MicroData("http://schema.org/Invoice","paymentMethodId")
////                ->Association("date@payments","mode@payments","amount@payments");
//                ->Group(Translate::getAdminTranslation("Payment", "AdminPayment"))
//                ->isNotTested();
//
//        //====================================================================//
//        // Payment Line Amount
//        $this->fieldsFactory()->Create(SPL_T_DOUBLE)
//                ->Identifier("amount")
//                ->InList("payments")
//                ->Name(Translate::getAdminTranslation("Amount", "AdminOrders"))
//                ->MicroData("http://schema.org/PaymentChargeSpecification","price")
//                ->Group(Translate::getAdminTranslation("Payment", "AdminPayment"))
//                ->isNotTested();
//
//    }
//
//    //====================================================================//
//    // Fields Reading Functions
//    //====================================================================//
//
//    /**
//     *  @abstract     Read requested Field
//     *
//     *  @param        string    $Key                    Input List Key
//     *  @param        string    $FieldName              Field Identifier / Name
//     *
//     *  @return         none
//     */
//    private function getCoreFields($Key,$FieldName)
//    {
//        //====================================================================//
//        // READ Fields
//        switch ($FieldName)
//        {
//            //====================================================================//
//            // Direct Readings
//            case 'number':
//                $this->Out[$FieldName] = $this->Object->getInvoiceNumberFormatted($this->LangId);
//                break;
//            case 'reference':
//                $this->getSingleField($FieldName,"Order");
//                break;
//
//            //====================================================================//
//            // Customer Object Id Readings
//            case 'id_customer':
//                $this->Out[$FieldName] = self::ObjectId_Encode( "ThirdParty" , $this->Order->$FieldName );
//                break;
//
//            //====================================================================//
//            // Order Object Id Readings
//            case 'id_order':
//                $this->Out[$FieldName] = self::ObjectId_Encode( "Order" , $this->Object->$FieldName );
//                break;
//
//            //====================================================================//
//            // Order Official Date
//            case 'date_add':
//                $this->Out[$FieldName] = date(SPL_T_DATECAST, strtotime($this->Object->$FieldName));
//                break;
//
//            default:
//                return;
//        }
//
//        unset($this->In[$Key]);
//    }
//
//    /**
//     *  @abstract     Read requested Field
//     *
//     *  @param        string    $Key                    Input List Key
//     *  @param        string    $FieldName              Field Identifier / Name
//     *
//     *  @return         none
//     */
//    private function getMainFields($Key,$FieldName)
//    {
//        //====================================================================//
//        // READ Fields
//        switch ($FieldName)
//        {
//            //====================================================================//
//            // Order Delivery Date
////            case 'date_livraison':
////                $this->Out[$FieldName] = !empty($this->Object->date_livraison)?dol_print_date($this->Object->date_livraison, '%Y-%m-%d'):Null;
////                break;
//
//            //====================================================================//
//            // PRICE INFORMATIONS
//            //====================================================================//
//            case 'total_paid_tax_incl':
//            case 'total_paid_tax_excl':
//                $this->getSingleField($FieldName);
//                break;
//
//            //====================================================================//
//            // INVOICE STATUS
//            //====================================================================//
//            case 'status':
//                $delta = $this->Object->getTotalPaid() - $this->Object->total_paid_tax_incl;
//                if (!$this->Order->valid) {
//                    $this->Out[$FieldName]  = "PaymentCanceled";
//                } elseif (($delta < 1E-6 ) || ($delta > 0)) {
//                    $this->Out[$FieldName]  = "PaymentComplete";
//                } else {
//                    $this->Out[$FieldName]  = "PaymentDue";
//                }
//            break;
//
//            //====================================================================//
//            // INVOICE PAYMENT STATUS
//            //====================================================================//
//            case 'isCanceled':
//                $this->Out[$FieldName]  = (bool) !$this->Order->valid;
//                break;
//            case 'isValidated':
//                $this->Out[$FieldName]  = (bool) $this->Order->valid;
//                break;
//            case 'isPaid':
//                $delta = $this->Object->getTotalPaid() - $this->Object->total_paid_tax_incl;
//                $this->Out[$FieldName]  = (bool) ( ($delta < 1E-6 ) || ($delta > 0)  );
//                break;
//
//            default:
//                return;
//        }
//
//        unset($this->In[$Key]);
//    }
//
//    /**
//     *  @abstract     Read requested Field
//     *
//     *  @param        string    $Key                    Input List Key
//     *  @param        string    $FieldName              Field Identifier / Name
//     *
//     *  @return         none
//     */
//    private function getShippingLineFields($Key,$FieldName)
//    {
//        //====================================================================//
//        // Check List Name
//        if (self::ListField_DecodeListName($FieldName) !== "lines") {
//            return True;
//        }
//        //====================================================================//
//        // Decode Field Name
//        $ListFieldName = self::ListField_DecodeFieldName($FieldName);
//        //====================================================================//
//        // Create List Array If Needed
//        if (!array_key_exists("lines",$this->Out)) {
//            $this->Out["lines"] = array();
//        }
//
//        //====================================================================//
//        // READ Fields
//        switch ($ListFieldName)
//        {
//            //====================================================================//
//            // Order Line Direct Reading Data
//            case 'product_name':
//                $Value = $this->spl->l("Delivery");
//                break;
//            case 'product_quantity':
//                $Value = 1;
//                break;
//            case 'reduction_percent':
//                $Value = 0;
//                break;
//            //====================================================================//
//            // Order Line Product Id
//            case 'product_id':
//                $Value = Null;
//                break;
//            //====================================================================//
//            // Order Line Unit Price
//            case 'unit_price':
//                //====================================================================//
//                // Manually Compute Tax Rate
//                if ( $this->Object->total_shipping_tax_incl != $this->Object->total_shipping_tax_excl )  {
//                    $Tax    =   round(100 * ( ($this->Object->total_shipping_tax_incl - $this->Object->total_shipping_tax_excl) /  $this->Object->total_shipping_tax_excl ), 2);
//                } else {
//                    $Tax    =   0;
//                }
//                //====================================================================//
//                // Build Price Array
//                $Value = self::Price_Encode(
//                        (double)    Tools::convertPrice($this->Object->total_shipping_tax_excl,  $this->Currency),
//                        (double)    $Tax,
//                                    Null,
//                                    $this->Currency->iso_code,
//                                    $this->Currency->sign,
//                                    $this->Currency->name);
//                break;
//            default:
//                return;
//        }
//
//        //====================================================================//
//        // Create Line Array If Needed
//        $key = count($this->Products);
//        if (!array_key_exists($key,$this->Out["lines"])) {
//            $this->Out["lines"][$key] = array();
//        }
//        //====================================================================//
//        // Store Data in Array
//        $FieldIndex = explode("@",$FieldName);
//        $this->Out["lines"][$key][$FieldIndex[0]] = $Value;
//    }
//
//    /**
//     *  @abstract     Read requested Field
//     *
//     *  @param        string    $Key                    Input List Key
//     *  @param        string    $FieldName              Field Identifier / Name
//     *
//     *  @return         none
//     */
//    private function getDiscountLineFields($Key,$FieldName)
//    {
//        //====================================================================//
//        // Check List Name
//        if (self::ListField_DecodeListName($FieldName) !== "lines") {
//            return True;
//        }
//        //====================================================================//
//        // Decode Field Name
//        $ListFieldName = self::ListField_DecodeFieldName($FieldName);
//        //====================================================================//
//        // Create List Array If Needed
//        if (!array_key_exists("lines",$this->Out)) {
//            $this->Out["lines"] = array();
//        }
//        //====================================================================//
//        // Check If Order has Discounts
//        if ( $this->Object->total_discount_tax_incl == 0 )  {
//            return;
//        }
//
//        //====================================================================//
//        // READ Fields
//        switch ($ListFieldName)
//        {
//            //====================================================================//
//            // Order Line Direct Reading Data
//            case 'product_name':
//                $Value = $this->spl->l("Discount");
//                break;
//            case 'product_quantity':
//                $Value = 1;
//                break;
//            case 'reduction_percent':
//                $Value = 0;
//                break;
//            //====================================================================//
//            // Order Line Product Id
//            case 'product_id':
//                $Value = Null;
//                break;
//            //====================================================================//
//            // Order Line Unit Price
//            case 'unit_price':
//                //====================================================================//
//                // Manually Compute Tax Rate
//                if ( $this->Object->total_discount_tax_incl != $this->Object->total_discount_tax_excl )  {
//                    $Tax    =   round(100 * ( ($this->Object->total_discount_tax_incl - $this->Object->total_discount_tax_excl) /  $this->Object->total_discount_tax_excl ), 2);
//                } else {
//                    $Tax    =   0;
//                }
//                //====================================================================//
//                // Build Price Array
//                $Value = self::Price_Encode(
//                        (double)    (-1) * Tools::convertPrice($this->Object->total_discount_tax_excl,  $this->Currency),
//                        (double)    $Tax,
//                                    Null,
//                                    $this->Currency->iso_code,
//                                    $this->Currency->sign,
//                                    $this->Currency->name);
//                break;
//            default:
//                return;
//        }
//
//        //====================================================================//
//        // Create Line Array If Needed
//        $key = count($this->Products) + 1;
//        if (!array_key_exists($key,$this->Out["lines"])) {
//            $this->Out["lines"][$key] = array();
//        }
//        //====================================================================//
//        // Store Data in Array
//        $FieldIndex = explode("@",$FieldName);
//        $this->Out["lines"][$key][$FieldIndex[0]] = $Value;
//    }
//
//    /**
//     *  @abstract     Read requested Field
//     *
//     *  @param        string    $Key                    Input List Key
//     *  @param        string    $FieldName              Field Identifier / Name
//     *
//     *  @return         none
//     */
//    private function getProductsLineFields($Key,$FieldName)
//    {
//        //====================================================================//
//        // Check List Name
//        if (self::ListField_DecodeListName($FieldName) !== "lines") {
//            return True;
//        }
//        //====================================================================//
//        // Decode Field Name
//        $ListFieldName = self::ListField_DecodeFieldName($FieldName);
//        //====================================================================//
//        // Create List Array If Needed
//        if (!array_key_exists("lines",$this->Out)) {
//            $this->Out["lines"] = array();
//        }
//        //====================================================================//
//        // Verify List is Not Empty
//        if ( !is_array($this->Products) ) {
//            return True;
//        }
//
//        //====================================================================//
//        // Fill List with Data
//        foreach ($this->Products as $key => $Product) {
//
//            //====================================================================//
//            // READ Fields
//            switch ($ListFieldName)
//            {
//                //====================================================================//
//                // Order Line Direct Reading Data
//                case 'product_name':
//                case 'product_quantity':
//                    $Value = $Product[$ListFieldName];
//                    break;
//                case 'reduction_percent':
////                    if ( $Product["original_product_price"] <= 0 ) {
////                        $Value = 0;
////                    }
////                    $Value = round(100 * ($Product["original_product_price"] - $Product["unit_price_tax_excl"]) / $Product["original_product_price"] , 2) ;
//                    $Value = 0;
//                    break;
//                //====================================================================//
//                // Order Line Product Id
//                case 'product_id':
//                    $UnikId = Splash::Object('Product')->getUnikId($Product["product_id"], $Product["product_attribute_id"]);
//                    $Value = self::ObjectId_Encode( "Product" , $UnikId );
//                    break;
//                //====================================================================//
//                // Order Line Unit Price
//                case 'unit_price':
//
//$TaxCalc    =    \OrderDetail::getTaxCalculatorStatic($Product["id_order_detail"]);
//
//Splash::log()->www("Tax", $TaxCalc);
//                    //====================================================================//
//                    // Manually Compute Tax Rate
//                    if ( $Product["unit_price_tax_excl"] != $Product["unit_price_tax_incl"] )  {
//                        $RawTaxRate =   (100 * ( ($Product["unit_price_tax_incl"] - $Product["unit_price_tax_excl"]) /  $Product["unit_price_tax_excl"] ));
//                    }
//                    //====================================================================//
//                    // If Tax Rate Group is Defined => Search for Best Tax Rate
//                    if ( !empty($Product["id_tax_rules_group"]) ) {
//                        $Product["tax_rate"]    =       Splash::local()->getBestTaxRateInGroup($RawTaxRate, $Product["id_tax_rules_group"]);
//                    } else {
//                        $Product["tax_rate"]    =       round( $RawTaxRate , 2);
//                    }
//                    //====================================================================//
//                    // Build Price Array
//                    $Value = self::Price_Encode(
//                            (double)    Tools::convertPrice($Product["unit_price_tax_excl"],  $this->Currency),
////                            (double)    $Product["tax_rate"],
//                            (double)    $TaxCalc->getTotalRate(),
//                                        Null,
//                                        $this->Currency->iso_code,
//                                        $this->Currency->sign,
//                                        $this->Currency->name);
//                    break;
//                default:
//                    return;
//            }
//            //====================================================================//
//            // Create Line Array If Needed
//            if (!array_key_exists($key,$this->Out["lines"])) {
//                $this->Out["lines"][$key] = array();
//            }
//            //====================================================================//
//            // Store Data in Array
//            $FieldIndex = explode("@",$FieldName);
//            $this->Out["lines"][$key][$FieldIndex[0]] = $Value;
//        }
//        unset($this->In[$Key]);
//    }
//
//    /**
//     *  @abstract     Try To Detect Payment method Standardized Name
//     *
//     *  @param  OrderPayment    $OrderPayment
//     *
//     *  @return         none
//     */
//    private function getPaymentMethod($OrderPayment)
//    {
//        //====================================================================//
//        // Detect Payment Metyhod Type from Default Payment "known" methods
//        switch ($this->Order->module){
//            case "bankwire":
//                $Method = "ByBankTransferInAdvance";
//                break;
//            case "cheque":
//                $Method = "CheckInAdvance";
//                break;
//            case "paypal":
//                $Method = "PayPal";
//                break;
//            case "cashondelivery":
//                $Method = "COD";
//                break;
//            default:
//                //====================================================================//
//                // Detect Payment Method is Credit Card Like Method
//                if ( !empty($OrderPayment->card_brand) ) {
//                    $Method = "DirectDebit";
//                } else {
//                    $Method = "Unknown";
//                }
//                break;
//        }
//        return $Method;
//    }
//
//    /**
//     *  @abstract     Read requested Field
//     *
//     *  @param        string    $Key                    Input List Key
//     *  @param        string    $FieldName              Field Identifier / Name
//     *
//     *  @return         none
//     */
//    private function getPaymentLineFields($Key,$FieldName)
//    {
//        //====================================================================//
//        // Create List Array If Needed
//        if (!array_key_exists("payments",$this->Out)) {
//            $this->Out["payments"] = array();
//        }
//        //====================================================================//
//        // Verify List is Not Empty
//        if ( !is_a($this->Payments,"PrestaShopCollection") ) {
//            unset($this->In[$Key]);
//            return True;
//        }
//        //====================================================================//
//        // Fill List with Data
//        foreach ($this->Payments as $key => $OrderPayment) {
//            //====================================================================//
//            // READ Fields
//            switch ($FieldName)
//            {
//                //====================================================================//
//                // Payment Line - Payment Mode
//                case 'mode@payments':
//                    $Value  =   $this->getPaymentMethod($OrderPayment);
//                    break;
//                //====================================================================//
//                // Payment Line - Payment Date
//                case 'date@payments':
//                    $Value  =   date(SPL_T_DATECAST, strtotime($OrderPayment->date_add));
//                    break;
//                //====================================================================//
//                // Payment Line - Payment Identification Number
//                case 'number@payments':
//                    $Value  =   $OrderPayment->transaction_id;
//                    break;
//                //====================================================================//
//                // Payment Line - Payment Amount
//                case 'amount@payments':
//                    $Value  =   $OrderPayment->amount;
//                    break;
//                default:
//                    return;
//            }
//            //====================================================================//
//            // Create Address Array If Needed
//            if (!array_key_exists($key,$this->Out["payments"])) {
//                $this->Out["payments"][$key] = array();
//            }
//            //====================================================================//
//            // Store Data in Array
//            $FieldIndex = explode("@",$FieldName);
//            $this->Out["payments"][$key][$FieldIndex[0]] = $Value;
//        }
//        unset($this->In[$Key]);
//    }
//
//    //====================================================================//
//    // Fields Writting Functions
//    //====================================================================//
//
//    // NO SET OPERATIONS FOR INVOICES => ERROR
}
