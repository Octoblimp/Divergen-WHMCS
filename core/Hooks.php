<?php
namespace OpenWHM\Core;

/**
 * Hook System for Extensions
 */
class Hooks
{
    private $hooks = [];
    private $priorities = [];
    
    /**
     * Register a hook listener
     */
    public function add($hookName, $callback, $priority = 10)
    {
        if (!isset($this->hooks[$hookName])) {
            $this->hooks[$hookName] = [];
            $this->priorities[$hookName] = [];
        }
        
        $this->hooks[$hookName][] = $callback;
        $this->priorities[$hookName][] = $priority;
    }
    
    /**
     * Execute a hook
     */
    public function execute($hookName, $args = [])
    {
        if (!isset($this->hooks[$hookName])) {
            return $args;
        }
        
        // Sort callbacks by priority
        array_multisort($this->priorities[$hookName], SORT_ASC, $this->hooks[$hookName]);
        
        $result = $args;
        
        foreach ($this->hooks[$hookName] as $callback) {
            $callbackResult = call_user_func_array($callback, is_array($result) ? $result : [$result]);
            
            if ($callbackResult !== null) {
                $result = $callbackResult;
            }
        }
        
        return $result;
    }
    
    /**
     * Execute a hook and collect all results
     */
    public function collect($hookName, $args = [])
    {
        if (!isset($this->hooks[$hookName])) {
            return [];
        }
        
        $results = [];
        
        foreach ($this->hooks[$hookName] as $callback) {
            $result = call_user_func_array($callback, $args);
            if ($result !== null) {
                $results[] = $result;
            }
        }
        
        return $results;
    }
    
    /**
     * Remove a hook listener
     */
    public function remove($hookName, $callback = null)
    {
        if ($callback === null) {
            unset($this->hooks[$hookName]);
            unset($this->priorities[$hookName]);
        } else {
            $key = array_search($callback, $this->hooks[$hookName], true);
            if ($key !== false) {
                unset($this->hooks[$hookName][$key]);
                unset($this->priorities[$hookName][$key]);
                $this->hooks[$hookName] = array_values($this->hooks[$hookName]);
                $this->priorities[$hookName] = array_values($this->priorities[$hookName]);
            }
        }
    }
    
    /**
     * Check if hook has listeners
     */
    public function has($hookName)
    {
        return !empty($this->hooks[$hookName]);
    }
    
    /**
     * Get all registered hooks
     */
    public function getRegisteredHooks()
    {
        return array_keys($this->hooks);
    }
}

/**
 * Available Hooks:
 * 
 * System Hooks:
 * - PreDispatch: Before routing
 * - PostDispatch: After routing
 * - RegisterRoutes: Register custom routes
 * 
 * Client Hooks:
 * - ClientAdd: When a client is created
 * - ClientEdit: When a client is edited
 * - ClientDelete: When a client is deleted
 * - ClientLogin: When a client logs in
 * - ClientLogout: When a client logs out
 * 
 * Order Hooks:
 * - OrderCreated: When an order is placed
 * - OrderPaid: When an order is paid
 * - OrderAccepted: When an order is accepted
 * - OrderCancelled: When an order is cancelled
 * 
 * Invoice Hooks:
 * - InvoiceCreated: When an invoice is created
 * - InvoicePaid: When an invoice is paid
 * - InvoiceRefunded: When an invoice is refunded
 * - InvoiceCancelled: When an invoice is cancelled
 * 
 * Service Hooks:
 * - ServiceCreate: When a service is about to be created
 * - ServiceCreated: After a service is created
 * - ServiceSuspend: When a service is suspended
 * - ServiceUnsuspend: When a service is unsuspended
 * - ServiceTerminate: When a service is terminated
 * - ServiceRenew: When a service is renewed
 * 
 * Domain Hooks:
 * - DomainRegister: When a domain is registered
 * - DomainRenew: When a domain is renewed
 * - DomainTransfer: When a domain is transferred
 * 
 * Ticket Hooks:
 * - TicketOpen: When a ticket is opened
 * - TicketReply: When a ticket reply is added
 * - TicketClose: When a ticket is closed
 * 
 * Payment Hooks:
 * - PaymentReceived: When a payment is received
 * - PaymentRefunded: When a payment is refunded
 * 
 * Cron Hooks:
 * - DailyCron: Daily cron execution
 * - HourlyCron: Hourly cron execution
 * 
 * Email Hooks:
 * - EmailPreSend: Before an email is sent
 * - EmailPostSend: After an email is sent
 * 
 * Cart Hooks:
 * - CartItemAdd: When an item is added to cart
 * - CartItemRemove: When an item is removed from cart
 * - CartCheckout: During checkout process
 * 
 * Admin Hooks:
 * - AdminLogin: When an admin logs in
 * - AdminLogout: When an admin logs out
 * - AdminAreaPage: When rendering admin pages
 * 
 * Frontend Hooks:
 * - ClientAreaPage: When rendering client area pages
 * - FrontendPage: When rendering frontend pages
 */
