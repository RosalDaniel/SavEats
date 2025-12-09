<?php

namespace App\Services;

use App\Models\FoodListing;
use App\Models\Order;
use App\Models\StockLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockService
{
    /**
     * Confirm payment and deduct stock from food listing
     * Uses row-level locking to prevent overselling
     * 
     * @param Order $order
     * @return array ['success' => bool, 'message' => string, 'ledger_id' => int|null]
     */
    public function confirmPaymentAndDeductStock(Order $order): array
    {
        // Check if payment already confirmed
        if ($order->payment_status === 'confirmed' && $order->stock_deducted) {
            return [
                'success' => true,
                'message' => 'Payment already confirmed and stock deducted',
                'ledger_id' => null
            ];
        }

        return $this->deductStock($order, $order->quantity);
    }

    /**
     * Deduct stock from food listing when order is accepted
     * Uses row-level locking to prevent overselling
     * 
     * @param Order $order
     * @param int $quantity
     * @return array ['success' => bool, 'message' => string, 'ledger_id' => int|null]
     */
    public function deductStock(Order $order, int $quantity): array
    {
        // Check if stock already deducted (idempotency)
        if ($order->stock_deducted) {
            return [
                'success' => true,
                'message' => 'Stock already deducted for this order',
                'ledger_id' => null
            ];
        }

        return DB::transaction(function () use ($order, $quantity) {
            // Lock the food listing row to prevent concurrent modifications
            $foodListing = FoodListing::lockForUpdate()
                ->findOrFail($order->food_listing_id);

            // Capture current state
            $quantityBefore = $foodListing->quantity;
            $reservedStockBefore = $foodListing->reserved_stock ?? 0;
            $soldStockBefore = $foodListing->sold_stock ?? 0;

            // Validate available stock (available = quantity - reserved_stock)
            $availableStock = $foodListing->quantity - ($foodListing->reserved_stock ?? 0);
            if ($availableStock < $quantity) {
                throw new \Exception("Insufficient stock. Available: {$availableStock}, Requested: {$quantity}");
            }

            // Deduct stock: reduce quantity directly when order is accepted
            $newQuantity = $foodListing->quantity - $quantity;
            if ($newQuantity < 0) {
                throw new \Exception("Stock cannot go below zero. Current: {$foodListing->quantity}, Requested: {$quantity}");
            }

            // Update stock
            $foodListing->quantity = $newQuantity;
            $foodListing->sold_stock = ($foodListing->sold_stock ?? 0) + $quantity;
            $foodListing->save();

            // Create ledger entry
            $ledger = StockLedger::create([
                'food_listing_id' => $foodListing->id,
                'order_id' => $order->id,
                'transaction_type' => 'deduction',
                'quantity' => $quantity,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $newQuantity,
                'reserved_stock_before' => $reservedStockBefore,
                'reserved_stock_after' => $reservedStockBefore,
                'sold_stock_before' => $soldStockBefore,
                'sold_stock_after' => $foodListing->sold_stock,
                'reason' => 'Order accepted by establishment',
                'notes' => "Stock deducted when order #{$order->order_number} was accepted",
            ]);

            // Mark order as having stock deducted
            $order->stock_deducted = true;
            $order->stock_deducted_at = now();
            // Don't modify payment_status here - it should already be set
            $order->save();

            Log::info('Stock deducted on order acceptance', [
                'order_id' => $order->id,
                'food_listing_id' => $foodListing->id,
                'quantity' => $quantity,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $newQuantity,
            ]);

            return [
                'success' => true,
                'message' => 'Stock deducted successfully',
                'ledger_id' => $ledger->id
            ];
        });
    }

    /**
     * Restore stock when order is cancelled
     * Idempotent: can only restore once per order
     * 
     * @param Order $order
     * @param string|null $reason
     * @return array ['success' => bool, 'message' => string, 'ledger_id' => int|null]
     */
    public function restoreStock(Order $order, ?string $reason = null): array
    {
        // Check if stock already restored (idempotency)
        if ($order->stock_restored) {
            return [
                'success' => true,
                'message' => 'Stock already restored for this order',
                'ledger_id' => null
            ];
        }

        // Only restore if stock was deducted
        if (!$order->stock_deducted) {
            return [
                'success' => true,
                'message' => 'No stock to restore (stock was never deducted)',
                'ledger_id' => null
            ];
        }

        return DB::transaction(function () use ($order, $reason) {
            // Lock the food listing row
            $foodListing = FoodListing::lockForUpdate()
                ->findOrFail($order->food_listing_id);

            // Capture current state
            $quantityBefore = $foodListing->quantity;
            $reservedStockBefore = $foodListing->reserved_stock ?? 0;
            $soldStockBefore = $foodListing->sold_stock ?? 0;

            // Restore stock: add back to quantity
            $restoreQuantity = $order->quantity;
            $foodListing->quantity = $foodListing->quantity + $restoreQuantity;
            
            // Reduce sold_stock if it was increased
            if ($foodListing->sold_stock >= $restoreQuantity) {
                $foodListing->sold_stock = $foodListing->sold_stock - $restoreQuantity;
            } else {
                // Safety check: if sold_stock is less than restore quantity, set to 0
                $foodListing->sold_stock = 0;
            }

            $foodListing->save();

            // Create ledger entry
            $ledger = StockLedger::create([
                'food_listing_id' => $foodListing->id,
                'order_id' => $order->id,
                'transaction_type' => 'restoration',
                'quantity' => $restoreQuantity,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $foodListing->quantity,
                'reserved_stock_before' => $reservedStockBefore,
                'reserved_stock_after' => $reservedStockBefore,
                'sold_stock_before' => $soldStockBefore,
                'sold_stock_after' => $foodListing->sold_stock,
                'reason' => $reason ?? 'Order cancelled',
                'notes' => "Stock restored for cancelled order #{$order->order_number}",
            ]);

            // Mark order as having stock restored
            $order->stock_restored = true;
            $order->stock_restored_at = now();
            $order->save();

            Log::info('Stock restored', [
                'order_id' => $order->id,
                'food_listing_id' => $foodListing->id,
                'quantity' => $restoreQuantity,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $foodListing->quantity,
            ]);

            return [
                'success' => true,
                'message' => 'Stock restored successfully',
                'ledger_id' => $ledger->id
            ];
        });
    }

    /**
     * Reserve stock temporarily (before payment confirmation)
     * This is used when order is placed but payment not yet confirmed
     * 
     * @param FoodListing $foodListing
     * @param int $quantity
     * @return array ['success' => bool, 'message' => string]
     */
    public function reserveStock(FoodListing $foodListing, int $quantity): array
    {
        return DB::transaction(function () use ($foodListing, $quantity) {
            // Lock the food listing row
            $foodListing = FoodListing::lockForUpdate()
                ->findOrFail($foodListing->id);

            // Validate available stock
            $availableStock = $foodListing->quantity - ($foodListing->reserved_stock ?? 0);
            if ($availableStock < $quantity) {
                throw new \Exception("Insufficient stock. Available: {$availableStock}, Requested: {$quantity}");
            }

            // Reserve stock: increase reserved_stock
            $foodListing->reserved_stock = ($foodListing->reserved_stock ?? 0) + $quantity;
            $foodListing->save();

            return [
                'success' => true,
                'message' => 'Stock reserved successfully'
            ];
        });
    }

    /**
     * Release reserved stock (if payment fails or order cancelled before payment)
     * 
     * @param FoodListing $foodListing
     * @param int $quantity
     * @return array ['success' => bool, 'message' => string]
     */
    public function releaseReservedStock(FoodListing $foodListing, int $quantity): array
    {
        return DB::transaction(function () use ($foodListing, $quantity) {
            // Lock the food listing row
            $foodListing = FoodListing::lockForUpdate()
                ->findOrFail($foodListing->id);

            // Release reserved stock
            $currentReserved = $foodListing->reserved_stock ?? 0;
            $foodListing->reserved_stock = max(0, $currentReserved - $quantity);
            $foodListing->save();

            return [
                'success' => true,
                'message' => 'Reserved stock released successfully'
            ];
        });
    }
}

