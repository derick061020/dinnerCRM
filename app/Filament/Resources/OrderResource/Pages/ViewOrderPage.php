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

    public $orderData = null;
    public $orderNotes = null;
    public $loading = false;
    public bool $editMode = false;
    public array $editableFields = [];

    protected function rules(): array
    {
        return [
            'editableFields.customer_name' => ['nullable', 'string', 'max:255'],
            'editableFields.customer_email' => ['nullable', 'email', 'max:255'],
            'editableFields.total' => ['nullable', 'numeric'],
            'editableFields.status' => ['nullable', 'in:pending,processing,completed,cancelled,refunded'],
        ];
    }

    public function mount(string|int $record): void
    {
        parent::mount($record);
        $this->loadWooCommerceData();
        $this->loadEditableFields();
    }

    protected function loadWooCommerceData(): void
    {
        $this->loading = true;
        
        try {
            // Verificar que tengamos un ID de WooCommerce válido
            if (!$this->record->woocommerce_order_id) {
                Notification::make()
                    ->warning()
                    ->title('ID de WooCommerce no encontrado')
                    ->body('Esta orden no tiene un ID de WooCommerce asociado.')
                    ->send();
                return;
            }

            // Debug: Verificar el ID
            logger('WooCommerce ID: ' . $this->record->woocommerce_order_id);

            // Usar directamente el controlador y mostrar el JSON crudo
            $controller = new WooOrderController();
            $response = $controller->getOrderDetails($this->record->woocommerce_order_id);
            
            // Debug: Verificar la respuesta
            logger('Response: ' . json_encode($response));
            
            // Obtener los datos crudos del response
            $responseData = $response->getData();
            
            // Debug: Verificar los datos
            logger('Response Data: ' . json_encode($responseData));
            
            // Asignar el JSON crudo para mostrarlo en la vista
            $this->orderData = $responseData;
            $this->orderNotes = $responseData->order_notes ?? [];
                
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
        ];
    }

    public function saveEditableFields(): void
    {
        $this->validate();

        $this->record->update([
            'customer_name' => $this->editableFields['customer_name'],
            'customer_email' => $this->editableFields['customer_email'],
            'total' => $this->editableFields['total'],
            'status' => $this->editableFields['status'],
        ]);

        $this->editMode = false;

        Notification::make()
            ->success()
            ->title('Orden actualizada')
            ->body('Los datos básicos se guardaron correctamente.')
            ->send();
    }
}
