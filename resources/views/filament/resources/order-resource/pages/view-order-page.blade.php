@php
    $itemsCount = collect($orderData->data->line_items ?? [])->sum('quantity');
@endphp

<x-filament::page>
    <style>
        /* Responsive Mobile Styles */
        @media (max-width: 768px) {
            .main-container {
                gap: 20px !important;
                padding: 0 16px !important;
            }
            
            .order-header {
                padding: 24px 20px !important;
                border-radius: 20px !important;
            }
            
            .order-header h1 {
                font-size: 1.8rem !important;
            }
            
            .order-header p {
                font-size: 0.8rem !important;
            }
            
            .edit-button {
                top: 16px !important;
                right: 16px !important;
                padding: 4px 12px !important;
                font-size: 0.7rem !important;
            }
            
            .stats-container {
                flex-direction: column !important;
                gap: 12px !important;
            }
            
            .stat-card {
                min-width: 100% !important;
                padding: 12px 16px !important;
            }
            
            .stat-card p {
                font-size: 0.8rem !important;
            }
            
            .stat-card p:nth-child(2) {
                font-size: 1.5rem !important;
            }
            
            .content-grid {
                grid-template-columns: 1fr !important;
                gap: 16px !important;
            }
            
            .products-section {
                padding: 24px 20px !important;
                border-radius: 20px !important;
            }
            
            .products-section h2 {
                font-size: 0.9rem !important;
            }
            
            .products-section span {
                font-size: 0.6rem !important;
            }
            
            .product-item {
                padding: 16px !important;
                border-radius: 16px !important;
                grid-template-columns: 1fr !important;
                gap: 12px !important;
            }
            
            .product-item img {
                width: 80px !important;
                height: 80px !important;
                border-radius: 12px !important;
            }
            
            .product-item h3 {
                font-size: 0.9rem !important;
            }
            
            .product-item p {
                font-size: 0.75rem !important;
            }
            
            .edit-form {
                padding: 20px !important;
                border-radius: 20px !important;
            }
            
            .edit-form h3 {
                font-size: 1rem !important;
            }
            
            .form-label {
                flex: 1 1 100% !important;
            }
            
            .form-input {
                padding: 8px 12px !important;
                font-size: 0.9rem !important;
            }
            
            .form-button {
                padding: 8px 16px !important;
                font-size: 0.9rem !important;
            }
        }
        
        @media (max-width: 480px) {
            .main-container {
                gap: 16px !important;
                padding: 0 12px !important;
            }
            
            .order-header {
                padding: 20px 16px !important;
                border-radius: 16px !important;
            }
            
            .order-header h1 {
                font-size: 1.6rem !important;
            }
            
            .order-header p {
                font-size: 0.75rem !important;
            }
            
            .edit-button {
                top: 12px !important;
                right: 12px !important;
                padding: 3px 10px !important;
                font-size: 0.65rem !important;
            }
            
            .stats-container {
                gap: 10px !important;
            }
            
            .stat-card {
                padding: 10px 12px !important;
            }
            
            .stat-card p {
                font-size: 0.75rem !important;
            }
            
            .stat-card p:nth-child(2) {
                font-size: 1.3rem !important;
            }
            
            .products-section {
                padding: 20px 16px !important;
                border-radius: 16px !important;
            }
            
            .products-section h2 {
                font-size: 0.85rem !important;
            }
            
            .products-section span {
                font-size: 0.55rem !important;
            }
            
            .product-item {
                padding: 12px !important;
                border-radius: 12px !important;
                gap: 10px !important;
            }
            
            .product-item img {
                width: 60px !important;
                height: 60px !important;
                border-radius: 8px !important;
            }
            
            .product-item h3 {
                font-size: 0.85rem !important;
            }
            
            .product-item p {
                font-size: 0.7rem !important;
            }
            
            .edit-form {
                padding: 16px !important;
                border-radius: 16px !important;
            }
            
            .edit-form h3 {
                font-size: 0.9rem !important;
            }
            
            .form-row {
                flex-direction: column !important;
                gap: 12px !important;
            }
            
            .form-input {
                padding: 6px 10px !important;
                font-size: 0.85rem !important;
            }
            
            .form-button {
                padding: 6px 14px !important;
                font-size: 0.85rem !important;
                width: 100% !important;
            }
        }
    </style>
    
    <div class="main-container" style="display:flex; flex-direction:column; gap:32px;">

        @if($orderData)
            <section class="order-header" style="position:relative; overflow:hidden; border-radius:24px; padding:32px; box-shadow:0 30px 50px rgba(15,23,42,0.45); background: linear-gradient(135deg, rgba(56,130,255,0.85), rgba(59,130,246,0.75)); color:white;">
                <div style="position:absolute; inset:0; opacity:0.3; pointer-events:none;">
                    <div style="width:100%; height:100%; background:radial-gradient(circle at top, rgba(255,255,255,0.25), transparent 45%);"></div>
                </div>
                <div style="position:relative; display:flex; flex-direction:column; gap:24px;">
                    <button class="edit-button" type="button" wire:click="toggleEditMode" style="position:absolute; top:24px; right:24px; background:rgba(255,255,255,0.15); border-radius:999px; border:1px solid rgba(255,255,255,0.5); padding:6px 16px; font-size:0.8rem; color:white; backdrop-filter:blur(6px);">
                        {{ $editMode ? 'Cancelar edición' : 'Editar orden' }}
                    </button>
                    <div style="display:flex; flex-direction:column; gap:12px;">
                        <p style="font-size:12px; letter-spacing:0.4em; text-transform:uppercase; color:rgba(255,255,255,0.7); margin:0;">Order Snapshot</p>
                        <h1 style="font-size:2.4rem; font-weight:600; margin:0;">Orden #{{ $orderData->data->id }}</h1>
                        <p style="margin:0; font-size:0.9rem; color:rgba(255,255,255,0.8);">
                            Creada el {{ $orderData->data->date_created }} · {{ $orderData->data->customer_note ?? 'Sin nota del cliente' }}
                        </p>
                    </div>

                    <div class="stats-container" style="display:flex; flex-wrap:wrap; justify-content:space-between; gap:12px;">
                        <div class="stat-card" style="border-radius:20px; padding:16px; background:rgba(255,255,255,0.08); min-width:140px;">
                            <p style="margin:0 0 4px 0; font-size:10px; letter-spacing:0.2em; text-transform:uppercase; color:rgba(255,255,255,0.65);">Total</p>
                            <p style="margin:0; font-size:2rem; font-weight:600;">${{ $orderData->data->total }}</p>
                        </div>
                        <div class="stat-card" style="border-radius:20px; padding:16px; background:rgba(255,255,255,0.08); min-width:140px;">
                            <p style="margin:0 0 4px 0; font-size:10px; letter-spacing:0.2em; text-transform:uppercase; color:rgba(255,255,255,0.65);">Impuestos</p>
                            <p style="margin:0; font-size:2rem; font-weight:600;">${{ $orderData->data->total_tax }}</p>
                        </div>
                        <div class="stat-card" style="border-radius:20px; padding:16px; background:rgba(255,255,255,0.08); min-width:140px;">
                            <p style="margin:0 0 4px 0; font-size:10px; letter-spacing:0.2em; text-transform:uppercase; color:rgba(255,255,255,0.65);">Artículos</p>
                            <p style="margin:0; font-size:2rem; font-weight:600;">{{ $itemsCount }}</p>
                        </div>
                    </div>
                </div>
            </section>
        @endif
        @if($editMode)
            <form class="edit-form" wire:submit.prevent="saveEditableFields" style="border-radius:28px; padding:28px; background:rgba(255,255,255,0.95); box-shadow:0 25px 40px rgba(15,23,42,0.15); display:flex; flex-direction:column; gap:20px;">
                <h3 style="margin:0; font-size:1.2rem; font-weight:600; color:#0f172a;">Editar datos clave</h3>
                <div class="form-row" style="display:flex; flex-wrap:wrap; gap:16px;">
                    <label class="form-label" style="flex:1 1 220px; display:flex; flex-direction:column; gap:6px; font-size:0.8rem; color:#475569;">
                        Cliente
                        <input class="form-input" type="text" wire:model.defer="editableFields.customer_name" style="border-radius:14px; padding:10px 14px; border:1px solid rgba(148,163,184,0.6); font-size:0.95rem; color:#0f172a;">
                        @error('editableFields.customer_name')<span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span>@enderror
                    </label>
                    <label class="form-label" style="flex:1 1 220px; display:flex; flex-direction:column; gap:6px; font-size:0.8rem; color:#475569;">
                        Email
                        <input class="form-input" type="email" wire:model.defer="editableFields.customer_email" style="border-radius:14px; padding:10px 14px; border:1px solid rgba(148,163,184,0.6); font-size:0.95rem; color:#0f172a;">
                        @error('editableFields.customer_email')<span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span>@enderror
                    </label>
                    <label class="form-label" style="flex:1 1 160px; display:flex; flex-direction:column; gap:6px; font-size:0.8rem; color:#475569;">
                        Total
                        <input class="form-input" type="number" step="0.01" wire:model.defer="editableFields.total" style="border-radius:14px; padding:10px 14px; border:1px solid rgba(148,163,184,0.6); font-size:0.95rem; color:#0f172a;">
                        @error('editableFields.total')<span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span>@enderror
                    </label>
                    <label class="form-label" style="flex:1 1 180px; display:flex; flex-direction:column; gap:6px; font-size:0.8rem; color:#475569;">
                        Estado
                        <select class="form-input" wire:model.defer="editableFields.status" style="border-radius:14px; padding:10px 14px; border:1px solid rgba(148,163,184,0.6); font-size:0.95rem; color:#0f172a; background:white;">
                            <option value="pending">Pendiente</option>
                            <option value="processing">Procesando</option>
                            <option value="completed">Completado</option>
                            <option value="cancelled">Cancelado</option>
                            <option value="refunded">Reembolsado</option>
                        </select>
                        @error('editableFields.status')<span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span>@enderror
                    </label>
                </div>
                
                <!-- Billing Information -->
                <h4 style="margin:20px 0 12px 0; font-size:1rem; font-weight:600; color:#0f172a;">📍 Información de facturación</h4>
                <div class="form-row" style="display:flex; flex-wrap:wrap; gap:16px;">
                    <label class="form-label" style="flex:1 1 200px; display:flex; flex-direction:column; gap:6px; font-size:0.8rem; color:#475569;">
                        Nombre
                        <input class="form-input" type="text" wire:model.defer="editableFields.billing_first_name" style="border-radius:14px; padding:10px 14px; border:1px solid rgba(148,163,184,0.6); font-size:0.95rem; color:#0f172a;">
                        @error('editableFields.billing_first_name')<span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span>@enderror
                    </label>
                    <label class="form-label" style="flex:1 1 200px; display:flex; flex-direction:column; gap:6px; font-size:0.8rem; color:#475569;">
                        Apellido
                        <input class="form-input" type="text" wire:model.defer="editableFields.billing_last_name" style="border-radius:14px; padding:10px 14px; border:1px solid rgba(148,163,184,0.6); font-size:0.95rem; color:#0f172a;">
                        @error('editableFields.billing_last_name')<span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span>@enderror
                    </label>
                    <label class="form-label" style="flex:1 1 200px; display:flex; flex-direction:column; gap:6px; font-size:0.8rem; color:#475569;">
                        Teléfono
                        <input class="form-input" type="text" wire:model.defer="editableFields.billing_phone" style="border-radius:14px; padding:10px 14px; border:1px solid rgba(148,163,184,0.6); font-size:0.95rem; color:#0f172a;">
                        @error('editableFields.billing_phone')<span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span>@enderror
                    </label>
                </div>
                
                <div class="form-row" style="display:flex; flex-wrap:wrap; gap:16px;">
                    <label class="form-label" style="flex:1 1 100%; display:flex; flex-direction:column; gap:6px; font-size:0.8rem; color:#475569;">
                        Dirección
                        <input class="form-input" type="text" wire:model.defer="editableFields.billing_address_1" style="border-radius:14px; padding:10px 14px; border:1px solid rgba(148,163,184,0.6); font-size:0.95rem; color:#0f172a;">
                        @error('editableFields.billing_address_1')<span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span>@enderror
                    </label>
                </div>
                
                <div class="form-row" style="display:flex; flex-wrap:wrap; gap:16px;">
                    <label class="form-label" style="flex:1 1 150px; display:flex; flex-direction:column; gap:6px; font-size:0.8rem; color:#475569;">
                        Ciudad
                        <input class="form-input" type="text" wire:model.defer="editableFields.billing_city" style="border-radius:14px; padding:10px 14px; border:1px solid rgba(148,163,184,0.6); font-size:0.95rem; color:#0f172a;">
                        @error('editableFields.billing_city')<span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span>@enderror
                    </label>
                    <label class="form-label" style="flex:1 1 150px; display:flex; flex-direction:column; gap:6px; font-size:0.8rem; color:#475569;">
                        Estado
                        <input class="form-input" type="text" wire:model.defer="editableFields.billing_state" style="border-radius:14px; padding:10px 14px; border:1px solid rgba(148,163,184,0.6); font-size:0.95rem; color:#0f172a;">
                        @error('editableFields.billing_state')<span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span>@enderror
                    </label>
                    <label class="form-label" style="flex:1 1 120px; display:flex; flex-direction:column; gap:6px; font-size:0.8rem; color:#475569;">
                        Código Postal
                        <input class="form-input" type="text" wire:model.defer="editableFields.billing_postcode" style="border-radius:14px; padding:10px 14px; border:1px solid rgba(148,163,184,0.6); font-size:0.95rem; color:#0f172a;">
                        @error('editableFields.billing_postcode')<span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span>@enderror
                    </label>
                    <label class="form-label" style="flex:1 1 120px; display:flex; flex-direction:column; gap:6px; font-size:0.8rem; color:#475569;">
                        País
                        <input class="form-input" type="text" wire:model.defer="editableFields.billing_country" style="border-radius:14px; padding:10px 14px; border:1px solid rgba(148,163,184,0.6); font-size:0.95rem; color:#0f172a;">
                        @error('editableFields.billing_country')<span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span>@enderror
                    </label>
                </div>
                
                <!-- Payment Information -->
                <h4 style="margin:20px 0 12px 0; font-size:1rem; font-weight:600; color:#0f172a;">💳 Información de pago</h4>
                <div class="form-row" style="display:flex; flex-wrap:wrap; gap:16px;">
                    <label class="form-label" style="flex:1 1 200px; display:flex; flex-direction:column; gap:6px; font-size:0.8rem; color:#475569;">
                        Método de pago
                        <input class="form-input" type="text" wire:model.defer="editableFields.payment_method_title" style="border-radius:14px; padding:10px 14px; border:1px solid rgba(148,163,184,0.6); font-size:0.95rem; color:#0f172a;">
                        @error('editableFields.payment_method_title')<span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span>@enderror
                    </label>
                    <label class="form-label" style="flex:1 1 200px; display:flex; flex-direction:column; gap:6px; font-size:0.8rem; color:#475569;">
                        ID Transacción
                        <input class="form-input" type="text" wire:model.defer="editableFields.transaction_id" style="border-radius:14px; padding:10px 14px; border:1px solid rgba(148,163,184,0.6); font-size:0.95rem; color:#0f172a;">
                        @error('editableFields.transaction_id')<span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span>@enderror
                    </label>
                </div>
                
                <!-- Booking Information -->
                <h4 style="margin:20px 0 12px 0; font-size:1rem; font-weight:600; color:#0f172a;">📅 Fechas de reserva</h4>
                <div class="form-row" style="display:flex; flex-wrap:wrap; gap:16px;">
                    <label class="form-label" style="flex:1 1 200px; display:flex; flex-direction:column; gap:6px; font-size:0.8rem; color:#475569;">
                        Fecha y hora de inicio
                        <input class="form-input" type="datetime-local" wire:model.defer="editableFields.booking_start" style="border-radius:14px; padding:10px 14px; border:1px solid rgba(148,163,184,0.6); font-size:0.95rem; color:#0f172a;">
                        @error('editableFields.booking_start')<span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span>@enderror
                    </label>
                    <label class="form-label" style="flex:1 1 200px; display:flex; flex-direction:column; gap:6px; font-size:0.8rem; color:#475569;">
                        Fecha y hora de fin
                        <input class="form-input" type="datetime-local" wire:model.defer="editableFields.booking_end" style="border-radius:14px; padding:10px 14px; border:1px solid rgba(148,163,184,0.6); font-size:0.95rem; color:#0f172a;">
                        @error('editableFields.booking_end')<span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span>@enderror
                    </label>
                </div>
                
                <!-- Editable Products -->
                <h4 style="margin:20px 0 12px 0; font-size:1rem; font-weight:600; color:#0f172a;">🛍 Productos</h4>
                <div style="display:flex; flex-direction:column; gap:16px;">
                    @foreach($editableProducts as $index => $product)
                    <div style="border:1px solid rgba(148,163,184,0.3); border-radius:12px; padding:16px; background:rgba(248,250,252,0.5);">
                        <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:end;">
                            <label class="form-label" style="flex:2 1 200px; display:flex; flex-direction:column; gap:6px; font-size:0.8rem; color:#475569;">
                                Producto
                                <input class="form-input" type="text" wire:model.defer="editableProducts.{{ $index }}.name" style="border-radius:14px; padding:10px 14px; border:1px solid rgba(148,163,184,0.6); font-size:0.95rem; color:#0f172a;">
                                @error('editableProducts.'.$index.'.name')<span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span>@enderror
                            </label>
                            <label class="form-label" style="flex:1 1 100px; display:flex; flex-direction:column; gap:6px; font-size:0.8rem; color:#475569;">
                                Cantidad
                                <input class="form-input" type="number" wire:model.defer="editableProducts.{{ $index }}.quantity" min="1" style="border-radius:14px; padding:10px 14px; border:1px solid rgba(148,163,184,0.6); font-size:0.95rem; color:#0f172a;">
                                @error('editableProducts.'.$index.'.quantity')<span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span>@enderror
                            </label>
                            <label class="form-label" style="flex:1 1 100px; display:flex; flex-direction:column; gap:6px; font-size:0.8rem; color:#475569;">
                                Precio Unit.
                                <input class="form-input" type="number" wire:model.defer="editableProducts.{{ $index }}.price" step="0.01" min="0" style="border-radius:14px; padding:10px 14px; border:1px solid rgba(148,163,184,0.6); font-size:0.95rem; color:#0f172a;">
                                @error('editableProducts.'.$index.'.price')<span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span>@enderror
                            </label>
                            <label class="form-label" style="flex:1 1 100px; display:flex; flex-direction:column; gap:6px; font-size:0.8rem; color:#475569;">
                                Total
                                <input class="form-input" type="number" wire:model.defer="editableProducts.{{ $index }}.total" step="0.01" min="0" style="border-radius:14px; padding:10px 14px; border:1px solid rgba(148,163,184,0.6); font-size:0.95rem; color:#0f172a;">
                                @error('editableProducts.'.$index.'.total')<span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span>@enderror
                            </label>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div style="display:flex; flex-wrap:wrap; gap:12px;">
                    <button class="form-button" type="submit" style="border:none; border-radius:16px; padding:10px 20px; background:#2563eb; color:white; font-weight:600;">Guardar cambios</button>
                    <button class="form-button" type="button" wire:click="toggleEditMode" style="border-radius:16px; padding:10px 20px; border:1px solid rgba(15,23,42,0.2); background:white; color:#0f172a; font-weight:600;">Cancelar</button>
                </div>
            </form>
        @endif

        <div class="content-grid" style="display:grid; grid-template-columns:minmax(0,3fr) minmax(0,1.2fr); gap:24px;">

            <div style="display:flex; flex-direction:column; gap:24px;">

                <section class="products-section" style="border-radius:32px; padding:32px; background:linear-gradient(180deg, rgba(255,255,255,0.95), rgba(248,250,252,0.9)); box-shadow:0 25px 40px rgba(15,23,42,0.15);">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <h2 style="margin:0; font-size:1.1rem; font-weight:600; color:#0f172a;">🛍 Detalles de productos</h2>
                        <span style="font-size:0.65rem; letter-spacing:0.3em; text-transform:uppercase; color:#94a3b8;">{{ count($orderData->data->line_items) }} items</span>
                    </div>

                    <div style="margin-top:24px; display:flex; flex-direction:column; gap:16px;">
                        @foreach($orderData->data->line_items as $item)
                            <article class="product-item" style="border-radius:24px; padding:20px; border:1px solid rgba(148,163,184,0.4); box-shadow:0 10px 22px rgba(15,23,42,0.08); display:grid; grid-template-columns:auto 1fr auto; gap:16px; align-items:center;">
                                <img src="{{ $item->image->src }}" alt="{{ $item->name }}" style="width:96px; height:96px; border-radius:18px; object-fit:cover;">

                                <div style="display:flex; flex-direction:column; gap:6px;">
                                    <h3 style="margin:0; font-size:1rem; font-weight:600; color:#0f172a;">{{ $item->name }}</h3>
                                    <p style="margin:0; font-size:0.85rem; color:#475569;">Cantidad: {{ $item->quantity }} · Precio unitario: ${{ $item->price }}</p>
                                    <p style="margin:0; font-size:0.85rem; color:#475569;">Total: ${{ $item->total }}</p>
                                    <div style="margin-top:6px; display:flex; flex-wrap:wrap; gap:8px;">
                                        @foreach($item->meta_data as $meta)
                                            @if(!str_starts_with($meta->key, '_') && is_scalar($meta->value))
                                                <span style="border-radius:999px; border:1px solid rgba(148,163,184,0.4); background:#f8fafc; padding:4px 12px; font-size:0.7rem; font-weight:600; color:#475569;">{{ $meta->key }}: {{ $meta->value }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>

                                <div style="display:flex; flex-direction:column; align-items:flex-end; gap:8px;">
                                    <span style="border-radius:999px; background:rgba(16,185,129,0.1); padding:4px 12px; font-size:0.7rem; font-weight:600; color:#047857;">{{ strtoupper($item->status ?? 'Activo') }}</span>
                                    <p style="margin:0; font-size:0.85rem; font-weight:600; color:#0f172a;">SKU: {{ $item->sku ?? 'N/A' }}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section style="border-radius:32px; padding:32px; background:#020617; box-shadow:0 30px 45px rgba(2,6,23,0.65); color:white;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <h2 style="margin:0; font-size:1.1rem; font-weight:600;">🕒 Actividad reciente</h2>
                        <span style="font-size:0.65rem; letter-spacing:0.3em; text-transform:uppercase; color:rgba(255,255,255,0.6);">{{ count($orderNotes) }} eventos</span>
                    </div>

                    <!-- Add New Order Note Form -->
                    <div style="margin-top:20px; padding:16px; background:rgba(255,255,255,0.1); border-radius:12px; border:1px solid rgba(255,255,255,0.2);">
                        <h4 style="margin:0 0 12px 0; font-size:0.9rem; font-weight:600; color:rgba(255,255,255,0.9);">📝 Agregar nueva nota</h4>
                        <form wire:submit.prevent="addOrderNote" style="display:flex; flex-direction:column; gap:12px;">
                            <textarea 
                                wire:model.defer="newOrderNote"
                                placeholder="Escribe una nota sobre esta orden..."
                                style="width:100%; min-height:80px; padding:12px; border-radius:8px; border:1px solid rgba(255,255,255,0.3); background:rgba(255,255,255,0.9); color:#0f172a; font-size:0.9rem; resize:vertical;"
                            ></textarea>
                            @error('newOrderNote')<span style="color:#fca5a5; font-size:0.75rem;">{{ $message }}</span>@enderror
                            <button 
                                type="submit"
                                style="align-self:flex-start; padding:8px 16px; background:#3b82f6; color:white; border:none; border-radius:8px; font-size:0.85rem; font-weight:600; cursor:pointer; transition:background 0.2s;"
                                onmouseover="this.style.background='#2563eb'"
                                onmouseout="this.style.background='#3b82f6'"
                            >
                                Agregar Nota
                            </button>
                        </form>
                    </div>

                    <div style="margin-top:24px; display:flex; flex-direction:column; gap:16px;">
                        @foreach($orderNotes as $note)
                            <div style="display:flex; gap:12px;">
                                <div style="display:flex; flex-direction:column; align-items:center;">
                                    <span style="width:8px; height:8px; border-radius:999px; background:#34d399;"></span>
                                    <span style="width:1px; flex-grow:1; background:rgba(255,255,255,0.4); margin-top:4px;"></span>
                                </div>
                                <div style="display:flex; flex-direction:column; gap:4px;">
                                    <p style="margin:0; font-size:0.9rem; color:rgba(255,255,255,0.8);">{{ $note->note }}</p>
                                    <p style="margin:0; font-size:0.75rem; color:rgba(255,255,255,0.4);">{{ $note->date_created }} @if(isset($note->added_by)) · por {{ $note->added_by }} @endif</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>

            <div style="display:flex; flex-direction:column; gap:24px;">
                <section style="border-radius:30px; padding:28px; background:rgba(255,255,255,0.95); box-shadow:0 20px 35px rgba(15,23,42,0.15);">
                    <h3 style="margin:0; font-size:1.1rem; font-weight:600; color:#0f172a;">👤 Cliente & Envío</h3>
                    <div style="margin-top:16px; display:flex; flex-direction:column; gap:6px; font-size:0.9rem; color:#475569;">
                        <p style="margin:0; font-weight:600; color:#0f172a;">{{ $orderData->data->billing->first_name }} {{ $orderData->data->billing->last_name }}</p>
                        <p style="margin:0;">{{ $orderData->data->billing->email }}</p>
                        <p style="margin:0;">{{ $orderData->data->billing->phone }}</p>
                        <p style="margin:0;">{{ $orderData->data->billing->address_1 }}, {{ $orderData->data->billing->city }}, {{ $orderData->data->billing->state }} {{ $orderData->data->billing->postcode }} · {{ $orderData->data->billing->country }}</p>
                    </div>
                </section>

                <section style="border-radius:30px; padding:28px; background:rgba(255,255,255,0.96); box-shadow:0 20px 35px rgba(15,23,42,0.15);">
                    <h3 style="margin:0; font-size:1.1rem; font-weight:600; color:#0f172a;">💳 Pago & financiero</h3>
                    <div style="margin-top:16px; display:flex; flex-direction:column; gap:8px; font-size:0.9rem; color:#475569;">
                        <p style="margin:0;"><span style="font-weight:600; color:#0f172a;">Método:</span> {{ $orderData->data->payment_method_title }}</p>
                        <p style="margin:0;"><span style="font-weight:600; color:#0f172a;">Transacción:</span> {{ $orderData->data->transaction_id ?? 'Pendiente' }}</p>
                        <p style="margin:0;"><span style="font-weight:600; color:#0f172a;">Pagado:</span> {{ $orderData->data->date_paid ?? 'No' }}</p>
                        <div style="border-radius:24px; background:#f1f5f9; padding:12px; font-size:0.8rem; color:#475569;">
                            <p style="margin:0; letter-spacing:0.2em; text-transform:uppercase; color:#94a3b8;">Resumen financiero</p>
                            <p style="margin:4px 0 0 0; font-weight:600; color:#0f172a;">Subtotal ${{ $orderData->data->total - $orderData->data->total_tax }}</p>
                            <p style="margin:2px 0 0 0; font-size:0.75rem; letter-spacing:0.2em; text-transform:uppercase; color:#94a3b8;">Impuestos ${{ $orderData->data->total_tax }}</p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-filament::page>
