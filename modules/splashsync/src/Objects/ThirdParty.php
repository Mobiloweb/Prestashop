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

/**
 * @abstract    Splash Local Object Class - Customer Accounts Local Integration 
 * @author      B. Paquier <contact@splashsync.com>
 */
class ThirdParty extends AbstractObject
{
    
    // Splash Php Core Traits
    use IntelParserTrait;
    use SimpleFieldsTrait;
    use ObjectsTrait;

    // Prestashop Common Traits
    use \Splash\Local\Objects\Core\DatesTrait;
    use \Splash\Local\Objects\Core\SplashMetaTrait;
    
    // Prestashop ThirdParty Traits
    use \Splash\Local\Objects\ThirdParty\ObjectsListTrait;
    use \Splash\Local\Objects\ThirdParty\CRUDTrait;
    use \Splash\Local\Objects\ThirdParty\CoreTrait;
    use \Splash\Local\Objects\ThirdParty\MainTrait;
    use \Splash\Local\Objects\ThirdParty\AddressTrait;
    use \Splash\Local\Objects\ThirdParty\AddressesTrait;
    use \Splash\Local\Objects\ThirdParty\MetaTrait;
    
    //====================================================================//
    // Object Definition Parameters	
    //====================================================================//
    
    /**
     *  Object Disable Flag. Uncomment thius line to Override this flag and disable Object.
     */
//    protected static    $DISABLED        =  True;
    
    /**
     *  Object Name (Translated by Module)
     */
    protected static    $NAME            =  "ThirdParty";
    
    /**
     *  Object Description (Translated by Module) 
     */
    protected static    $DESCRIPTION     =  "Prestashop Customer Object";    
    
    /**
     *  Object Icon (FontAwesome or Glyph ico tag) 
     */
    protected static    $ICO     =  "fa fa-user";

    /**
     *  Object Synchronization Limitations 
     *  
     *  This Flags are Used by Splash Server to Prevent Unexpected Operations on Remote Server
     */
    protected static    $ALLOW_PUSH_CREATED         =  TRUE;        // Allow Creation Of New Local Objects
    protected static    $ALLOW_PUSH_UPDATED         =  TRUE;        // Allow Update Of Existing Local Objects
    protected static    $ALLOW_PUSH_DELETED         =  TRUE;        // Allow Delete Of Existing Local Objects
    
    /**
     *  Object Synchronization Recommended Configuration 
     */
    protected static    $ENABLE_PUSH_CREATED       =  FALSE;        // Enable Creation Of New Local Objects when Not Existing
    protected static    $ENABLE_PUSH_UPDATED       =  TRUE;         // Enable Update Of Existing Local Objects when Modified Remotly
    protected static    $ENABLE_PUSH_DELETED       =  TRUE;         // Enable Delete Of Existing Local Objects when Deleted Remotly

    protected static    $ENABLE_PULL_CREATED       =  TRUE;         // Enable Import Of New Local Objects 
    protected static    $ENABLE_PULL_UPDATED       =  TRUE;         // Enable Import of Updates of Local Objects when Modified Localy
    protected static    $ENABLE_PULL_DELETED       =  TRUE;         // Enable Delete Of Remotes Objects when Deleted Localy 
        
    //====================================================================//
    // Class Constructor
    //====================================================================//
    
    public function __construct()
    {
        //====================================================================//
        //  Load Local Translation File
        Splash::Translator()->Load("objects@local");          
       
        //====================================================================//
        // Load Splash Module
        $this->spl = Splash::Local()->getLocalModule();
        if ( $this->spl == False ) {
            return False;
        }
    }

}




