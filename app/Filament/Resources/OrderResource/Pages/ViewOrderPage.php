<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Models\Order;
use App\Filament\Resources\OrderResource;
use App\Http\Controllers\WooOrderController;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewOrderPage extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected static string $view = 'filament.resources.order-resource.pages.view-order-page';

    public $orderData;
    public $orderNotes = null;
    public $loading = false;
    public bool $editMode = false;
    public array $editableFields = [];
    public string $newOrderNote = '';
    public array $editableProducts = [];

    protected function rules(): array
    {
        return [
            'editableFields.customer_name' => ['nullable', 'string', 'max:255'],
            'editableFields.customer_email' => ['nullable', 'email', 'max:255'],
            'editableFields.total' => ['nullable', 'numeric'],
            'editableFields.status' => ['nullable', 'in:pending,processing,completed,cancelled,refunded'],
            'editableFields.billing_first_name' => ['nullable', 'string', 'max:255'],
            'editableFields.billing_last_name' => ['nullable', 'string', 'max:255'],
            'editableFields.billing_phone' => ['nullable', 'string', 'max:255'],
            'editableFields.billing_address_1' => ['nullable', 'string', 'max:255'],
            'editableFields.billing_city' => ['nullable', 'string', 'max:255'],
            'editableFields.billing_state' => ['nullable', 'string', 'max:255'],
            'editableFields.billing_postcode' => ['nullable', 'string', 'max:255'],
            'editableFields.billing_country' => ['nullable', 'string', 'max:255'],
            'editableFields.payment_method_title' => ['nullable', 'string', 'max:255'],
            'editableFields.transaction_id' => ['nullable', 'string', 'max:255'],
            'newOrderNote' => ['nullable', 'string', 'max:1000'],
            'editableProducts.*.name' => ['nullable', 'string', 'max:255'],
            'editableProducts.*.quantity' => ['nullable', 'integer', 'min:1'],
            'editableProducts.*.price' => ['nullable', 'numeric', 'min:0'],
            'editableProducts.*.total' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function mount(string|int $record): void
    {
        parent::mount($record);
        $this->loadWooCommerceData();
        $this->loadEditableFields();
        $this->loadEditableProducts();
    }

    protected function loadWooCommerceData(): void
    {
        $this->loading = true;
        
        try {
            // Verificar que tengamos datos guardados
            if (!$this->record->data) {
                Notification::make()
                    ->warning()
                    ->title('Datos no encontrados')
                    ->body('Esta orden no tiene datos guardados.')
                    ->send();
                return;
            }

            // Obtener datos guardados en la base de datos
            $orderData = $this->record->data;
            
            // Extraer datos de la orden y notas
            $actualOrderData = $orderData->get('data');
            $orderNotes = $orderData->get('order_notes', []);
            
            // Convertir a objetos para acceso fácil
            $this->orderData = (object) [
                'data' => json_decode(json_encode($actualOrderData))
            ];
            $this->orderNotes = json_decode(json_encode($orderNotes));
                
        } catch (\Exception $e) {
            // Debug: Mostrar error
            logger('Error: ' . $e->getMessage());
        } finally {
            $this->loading = false;
        }
    }

    public function toggleEditMode(): void
    {
        $this->editMode = !$this->editMode;
        if ($this->editMode) {
            $this->loadEditableFields();
        }
    }


    protected function loadEditableFields(): void
    {
        $this->editableFields = [
            'customer_name' => $this->record->customer_name,
            'customer_email' => $this->record->customer_email,
            'total' => $this->record->total,
            'status' => $this->record->status,
            'billing_first_name' => $this->orderData->data->billing->first_name ?? '',
            'billing_last_name' => $this->orderData->data->billing->last_name ?? '',
            'billing_phone' => $this->orderData->data->billing->phone ?? '',
            'billing_address_1' => $this->orderData->data->billing->address_1 ?? '',
            'billing_city' => $this->orderData->data->billing->city ?? '',
            'billing_state' => $this->orderData->data->billing->state ?? '',
            'billing_postcode' => $this->orderData->data->billing->postcode ?? '',
            'billing_country' => $this->orderData->data->billing->country ?? '',
            'payment_method_title' => $this->orderData->data->payment_method_title ?? '',
            'transaction_id' => $this->orderData->data->transaction_id ?? '',
            'booking_start' => $this->orderData->data->booking_start ?? '',
            'booking_end' => $this->orderData->data->booking_end ?? '',
        ];
    }

    protected function loadEditableProducts(): void
    {
        $this->editableProducts = [];
        
        if ($this->orderData && isset($this->orderData->data->line_items)) {
            foreach ($this->orderData->data->line_items as $index => $item) {
                $this->editableProducts[$index] = [
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->total,
                ];
            }
        }
    }

    public function saveEditableFields(): void
    {
        $this->validate();

        // Update basic order fields
        $this->record->update([
            'customer_name' => $this->editableFields['customer_name'],
            'customer_email' => $this->editableFields['customer_email'],
            'total' => $this->editableFields['total'],
            'status' => $this->editableFields['status'],
        ]);

        // Update WooCommerce data in JSON
        if ($this->orderData && isset($this->orderData->data)) {
            $data = $this->record->data;
            $orderData = $data->get('data', []);
            
            // Update billing information
            if (isset($orderData['billing'])) {
                $orderData['billing']['first_name'] = $this->editableFields['billing_first_name'];
                $orderData['billing']['last_name'] = $this->editableFields['billing_last_name'];
                $orderData['billing']['phone'] = $this->editableFields['billing_phone'];
                $orderData['billing']['address_1'] = $this->editableFields['billing_address_1'];
                $orderData['billing']['city'] = $this->editableFields['billing_city'];
                $orderData['billing']['state'] = $this->editableFields['billing_state'];
                $orderData['billing']['postcode'] = $this->editableFields['billing_postcode'];
                $orderData['billing']['country'] = $this->editableFields['billing_country'];
            }
            
            // Update payment information
            $orderData['payment_method_title'] = $this->editableFields['payment_method_title'];
            $orderData['transaction_id'] = $this->editableFields['transaction_id'];
            
            // Update booking dates
            $orderData['booking_start'] = $this->editableFields['booking_start'];
            $orderData['booking_end'] = $this->editableFields['booking_end'];
            
            // Update line items (products)
            if (!empty($this->editableProducts)) {
                $orderData['line_items'] = [];
                foreach ($this->editableProducts as $index => $product) {
                    // Get original item data to preserve other fields
                    $originalItem = null;
                    if (isset($this->orderData->data->line_items[$index])) {
                        $originalItem = $this->orderData->data->line_items[$index];
                    }
                    
                    $item = [
                        'name' => $product['name'],
                        'quantity' => $product['quantity'],
                        'price' => $product['price'],
                        'total' => $product['total'],
                    ];
                    
                    // Preserve original data if exists
                    if ($originalItem) {
                        $item['id'] = $originalItem->id ?? null;
                        $item['sku'] = $originalItem->sku ?? null;
                        $item['image'] = $originalItem->image ?? null;
                        $item['meta_data'] = $originalItem->meta_data ?? [];
                        $item['status'] = $originalItem->status ?? 'active';
                    }
                    
                    $orderData['line_items'][] = $item;
                }
            }
            
            $data->put('data', $orderData);
            $this->record->data = $data;
            $this->record->save();
            
            // Reload order data to reflect changes
            $this->loadWooCommerceData();
        }

        $this->editMode = false;

        Notification::make()
            ->success()
            ->title('Orden actualizada')
            ->body('Todos los datos se guardaron correctamente.')
            ->send();
    }

    public function addOrderNote(): void
    {
        $this->validate(['newOrderNote' => ['required', 'string', 'max:1000']]);

        if (empty(trim($this->newOrderNote))) {
            return;
        }

        // Create new order note
        $newNote = [
            'note' => $this->newOrderNote,
            'date_created' => now()->format('Y-m-d H:i:s'),
            'customer_note' => false,
            'added_by' => auth()->user()->name ?? 'System',
        ];

        // Add to order notes
        $data = $this->record->data;
        $orderNotes = $data->get('order_notes', []);
        $orderNotes[] = $newNote;
        $data->put('order_notes', $orderNotes);
        $this->record->data = $data;
        $this->record->save();

        // Reload order notes
        $this->orderNotes = json_decode(json_encode($orderNotes));

        // Clear the input
        $this->newOrderNote = '';

        Notification::make()
            ->success()
            ->title('Nota agregada')
            ->body('La nota de orden se agregó correctamente.')
            ->send();
    }
}
