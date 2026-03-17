@php
    $itemsCount = collect($orderData->data->line_items ?? [])->sum('quantity');
@endphp

<x-filament::page>
    <div style="display:flex; flex-direction:column; gap:32px;">

        @if($orderData)
            <section style="position:relative; overflow:hidden; border-radius:24px; padding:32px; box-shadow:0 30px 50px rgba(15,23,42,0.45); background: linear-gradient(135deg, rgba(56,130,255,0.85), rgba(59,130,246,0.75)); color:white;">
                <div style="position:absolute; inset:0; opacity:0.3; pointer-events:none;">
                    <div style="width:100%; height:100%; background:radial-gradient(circle at top, rgba(255,255,255,0.25), transparent 45%);"></div>
                </div>
                <div style="position:relative; display:flex; flex-direction:column; gap:24px;">
                    <button type="button" wire:click="toggleEditMode" style="position:absolute; top:24px; right:24px; background:rgba(255,255,255,0.15); border-radius:999px; border:1px solid rgba(255,255,255,0.5); padding:6px 16px; font-size:0.8rem; color:white; backdrop-filter:blur(6px);">
                        {{ $editMode ? 'Cancelar edición' : 'Editar orden' }}
                    </button>
                    <div style="display:flex; flex-direction:column; gap:12px;">
                        <p style="font-size:12px; letter-spacing:0.4em; text-transform:uppercase; color:rgba(255,255,255,0.7); margin:0;">Order Snapshot</p>
                        <h1 style="font-size:2.4rem; font-weight:600; margin:0;">Orden #{{ $orderData->data->id }}</h1>
                        <p style="margin:0; font-size:0.9rem; color:rgba(255,255,255,0.8);">
                            Creada el {{ $orderData->data->date_created }} · {{ $orderData->data->customer_note ?? 'Sin nota del cliente' }}
                        </p>
                    </div>

                    <div style="display:flex; flex-wrap:wrap; justify-content:space-between; gap:12px;">
                        <div style="border-radius:20px; padding:16px; background:rgba(255,255,255,0.08); min-width:140px;">
                            <p style="margin:0 0 4px 0; font-size:10px; letter-spacing:0.2em; text-transform:uppercase; color:rgba(255,255,255,0.65);">Total</p>
                            <p style="margin:0; font-size:2rem; font-weight:600;">${{ $orderData->data->total }}</p>
                        </div>
                        <div style="border-radius:20px; padding:16px; background:rgba(255,255,255,0.08); min-width:140px;">
                            <p style="margin:0 0 4px 0; font-size:10px; letter-spacing:0.2em; text-transform:uppercase; color:rgba(255,255,255,0.65);">Impuestos</p>
                            <p style="margin:0; font-size:2rem; font-weight:600;">${{ $orderData->data->total_tax }}</p>
                        </div>
                        <div style="border-radius:20px; padding:16px; background:rgba(255,255,255,0.08); min-width:140px;">
                            <p style="margin:0 0 4px 0; font-size:10px; letter-spacing:0.2em; text-transform:uppercase; color:rgba(255,255,255,0.65);">Artículos</p>
                            <p style="margin:0; font-size:2rem; font-weight:600;">{{ $itemsCount }}</p>
                        </div>
                    </div>
                </div>
            </section>
        @endif
        @if($editMode)
            <form wire:submit.prevent="saveEditableFields" style="border-radius:28px; padding:28px; background:rgba(255,255,255,0.95); box-shadow:0 25px 40px rgba(15,23,42,0.15); display:flex; flex-direction:column; gap:20px;">
                <h3 style="margin:0; font-size:1.2rem; font-weight:600; color:#0f172a;">Editar datos clave</h3>
                <div style="display:flex; flex-wrap:wrap; gap:16px;">
                    <label style="flex:1 1 220px; display:flex; flex-direction:column; gap:6px; font-size:0.8rem; color:#475569;">
                        Cliente
                        <input type="text" wire:model.defer="editableFields.customer_name" style="border-radius:14px; padding:10px 14px; border:1px solid rgba(148,163,184,0.6); font-size:0.95rem; color:#0f172a;">
                        @error('editableFields.customer_name')<span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span>@enderror
                    </label>
                    <label style="flex:1 1 220px; display:flex; flex-direction:column; gap:6px; font-size:0.8rem; color:#475569;">
                        Email
                        <input type="email" wire:model.defer="editableFields.customer_email" style="border-radius:14px; padding:10px 14px; border:1px solid rgba(148,163,184,0.6); font-size:0.95rem; color:#0f172a;">
                        @error('editableFields.customer_email')<span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span>@enderror
                    </label>
                    <label style="flex:1 1 160px; display:flex; flex-direction:column; gap:6px; font-size:0.8rem; color:#475569;">
                        Total
                        <input type="number" step="0.01" wire:model.defer="editableFields.total" style="border-radius:14px; padding:10px 14px; border:1px solid rgba(148,163,184,0.6); font-size:0.95rem; color:#0f172a;">
                        @error('editableFields.total')<span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span>@enderror
                    </label>
                    <label style="flex:1 1 180px; display:flex; flex-direction:column; gap:6px; font-size:0.8rem; color:#475569;">
                        Estado
                        <select wire:model.defer="editableFields.status" style="border-radius:14px; padding:10px 14px; border:1px solid rgba(148,163,184,0.6); font-size:0.95rem; color:#0f172a; background:white;">
                            <option value="pending">Pendiente</option>
                            <option value="processing">Procesando</option>
                            <option value="completed">Completado</option>
                            <option value="cancelled">Cancelado</option>
                            <option value="refunded">Reembolsado</option>
                        </select>
                        @error('editableFields.status')<span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span>@enderror
                    </label>
                </div>
                <div style="display:flex; flex-wrap:wrap; gap:12px;">
                    <button type="submit" style="border:none; border-radius:16px; padding:10px 20px; background:#2563eb; color:white; font-weight:600;">Guardar cambios</button>
                    <button type="button" wire:click="toggleEditMode" style="border-radius:16px; padding:10px 20px; border:1px solid rgba(15,23,42,0.2); background:white; color:#0f172a; font-weight:600;">Cancelar</button>
                </div>
            </form>
        @endif

        <div style="display:grid; grid-template-columns:minmax(0,3fr) minmax(0,1.2fr); gap:24px;">

            <div style="display:flex; flex-direction:column; gap:24px;">

                <section style="border-radius:32px; padding:32px; background:linear-gradient(180deg, rgba(255,255,255,0.95), rgba(248,250,252,0.9)); box-shadow:0 25px 40px rgba(15,23,42,0.15);">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <h2 style="margin:0; font-size:1.1rem; font-weight:600; color:#0f172a;">🛍 Detalles de productos</h2>
                        <span style="font-size:0.65rem; letter-spacing:0.3em; text-transform:uppercase; color:#94a3b8;">{{ count($orderData->data->line_items) }} items</span>
                    </div>

                    <div style="margin-top:24px; display:flex; flex-direction:column; gap:16px;">
                        @foreach($orderData->data->line_items as $item)
                            <article style="border-radius:24px; padding:20px; border:1px solid rgba(148,163,184,0.4); box-shadow:0 10px 22px rgba(15,23,42,0.08); display:grid; grid-template-columns:auto 1fr auto; gap:16px; align-items:center;">
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

                    <div style="margin-top:24px; display:flex; flex-direction:column; gap:16px;">
                        @foreach($orderNotes as $note)
                            <div style="display:flex; gap:12px;">
                                <div style="display:flex; flex-direction:column; align-items:center;">
                                    <span style="width:8px; height:8px; border-radius:999px; background:#34d399;"></span>
                                    <span style="width:1px; flex-grow:1; background:rgba(255,255,255,0.4); margin-top:4px;"></span>
                                </div>
                                <div style="display:flex; flex-direction:column; gap:4px;">
                                    <p style="margin:0; font-size:0.9rem; color:rgba(255,255,255,0.8);">{{ $note->note }}</p>
                                    <p style="margin:0; font-size:0.75rem; color:rgba(255,255,255,0.4);">{{ $note->date_created }}</p>
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
