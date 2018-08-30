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
 *  @copyright 2015-2018 Splash Sync
 *  @license   MIT
 */

namespace Splash\Local\Objects\Order;

//use Splash\Core\SplashCore      as Splash;

//====================================================================//
// Prestashop Static Classes
use Translate;

/**
 * @abstract    Access to Orders Status Fields
 */
trait StatusTrait
{

    /**
    *   @abstract     Build Fields using FieldFactory
    */
    private function buildStatusFields()
    {
                
        //====================================================================//
        // ORDER STATUS
        //====================================================================//

        //====================================================================//
        // Order Current Status
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->Identifier("status")
                ->Name(Translate::getAdminTranslation("Order status", "AdminStatuses"))
                ->Description(Translate::getAdminTranslation("Status of the order", "AdminSupplyOrdersChangeState"))
                ->MicroData("http://schema.org/Order", "orderStatus")
                ->isReadOnly();

        //====================================================================//
        // ORDER STATUS FLAGS
        //====================================================================//
        
        $Prefix = Translate::getAdminTranslation("Order status", "AdminOrders") . " ";
        
        //====================================================================//
        // Is Canceled
        // => There is no Diffrence Between a Draft & Canceled Order on Prestashop.
        //      Any Non Validated Order is considered as Canceled
        $this->fieldsFactory()->create(SPL_T_BOOL)
                ->Identifier("isCanceled")
                ->Name($Prefix . $this->spl->l("Canceled"))
                ->MicroData("http://schema.org/OrderStatus", "OrderCancelled")
                ->Group(Translate::getAdminTranslation("Meta", "AdminThemes"))
                ->Association("isCanceled", "isValidated", "isClosed")
                ->isReadOnly();
        
        //====================================================================//
        // Is Validated
        $this->fieldsFactory()->create(SPL_T_BOOL)
                ->Identifier("isValidated")
                ->Name($Prefix . Translate::getAdminTranslation("Valid", "AdminCartRules"))
                ->MicroData("http://schema.org/OrderStatus", "OrderProcessing")
                ->Group(Translate::getAdminTranslation("Meta", "AdminThemes"))
                ->Association("isCanceled", "isValidated", "isClosed")
                ->isReadOnly();
        
        //====================================================================//
        // Is Closed
        $this->fieldsFactory()->create(SPL_T_BOOL)
                ->Identifier("isClosed")
                ->Name($Prefix . Translate::getAdminTranslation("Closed", "AdminCustomers"))
                ->MicroData("http://schema.org/OrderStatus", "OrderDelivered")
                ->Group(Translate::getAdminTranslation("Meta", "AdminThemes"))
                ->Association("isCanceled", "isValidated", "isClosed")
                ->isReadOnly();

        //====================================================================//
        // Is Paid
        $this->fieldsFactory()->create(SPL_T_BOOL)
                ->Identifier("isPaid")
                ->Name($Prefix . $this->spl->l("Paid"))
                ->Group(Translate::getAdminTranslation("Meta", "AdminThemes"))
                ->MicroData("http://schema.org/OrderStatus", "OrderPaid")
                ->isReadOnly();
    }
    
    /**
     *  @abstract     Read requested Field
     *
     *  @param        string    $Key                    Input List Key
     *  @param        string    $FieldName              Field Identifier / Name
     *
     *  @return         none
     */
    private function getStatusFields($Key, $FieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($FieldName) {
            //====================================================================//
            // ORDER STATUS
            //====================================================================//
            case 'status':
                $this->Out[$FieldName]  = $this->getSplashStatus();
                break;
            case 'isCanceled':
                $this->Out[$FieldName]  = !$this->Object->valid;
                break;
            case 'isValidated':
                $this->Out[$FieldName]  = (bool) $this->Object->valid;
                break;
            case 'isClosed':
                $this->Out[$FieldName]  = (bool) $this->Object->isPaidAndShipped();
                break;
            case 'isPaid':
                $this->Out[$FieldName]  = (bool) $this->Object->hasBeenPaid();
                break;
        
            default:
                return;
        }
        
        unset($this->In[$Key]);
    }
    
    /**
     *  @abstract     Read Order Status
     *  @return       string
     */
    private function getSplashStatus()
    {
        //====================================================================//
        // If order is in  Static Status => Use Static Status
        if ($this->Object->current_state == 1) {
            return "OrderPaymentDue";
        } elseif ($this->Object->current_state == 2) {
            return "OrderProcessing";
        } elseif ($this->Object->current_state == 3) {
            return "OrderProcessing";
        } elseif ($this->Object->current_state == 4) {
            return "OrderInTransit";
        } elseif ($this->Object->current_state == 5) {
            return "OrderDelivered";
        }
        //====================================================================//
        // If order is invalid => Canceled
        if (!$this->Object->valid) {
            return "OrderCanceled";
        }
        //====================================================================//
        // Other Status => Use Status Flag to Detect Current Order Status
        //====================================================================//
        if ($this->Object->isPaidAndShipped()) {
            return "OrderDelivered";
        } elseif ($this->Object->hasBeenPaid()) {
            return "OrderProcessing";
        }
        //====================================================================//
        // Default Status => Order is Closed & Delivered
        // Used for Orders imported to Prestashop that do not have Prestatsop Status
        return "OrderDelivered";
    }
}
