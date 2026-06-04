@php
    use Illuminate\Support\Str;

    \Carbon\Carbon::setLocale('es');

    // --- Datos base para la vista ---
    $ordersForDate   = $this->getOrdersForDate();
    $ordersByHour    = $this->getOrdersGroupedByHour();
    $daySummary      = $this->getProductSummary($ordersForDate);
    $totalReservas   = $ordersForDate->count();
    $totalEnPrep     = $ordersForDate->where('status', 'processing')->count();
    $totalListas     = $ordersForDate->where('status', 'completed')->count();
    $totalPlatos     = array_sum($daySummary);
    $maxDishDay      = $daySummary ? max($daySummary) : 0;

    // Carga (platos) por hora para la línea de tiempo
    $hourLoads = [];
    foreach ($ordersByHour as $hour => $orders) {
        $hourLoads[$hour] = [
            'reservas' => $orders->count(),
            'platos'   => array_sum($this->getProductSummary($orders)),
        ];
    }
    $maxHourLoad = $hourLoads ? max(array_column($hourLoads, 'platos')) : 0;
@endphp

<x-filament-panels::page>
    <style>
        .ck {
            --bg: #ffffff;
            --bg-soft: #f8fafc;
            --line: #e8edf3;
            --ink: #0f172a;
            --muted: #64748b;
            --blue: #3b82f6;
            --amber: #f59e0b;
            --green: #10b981;
            --purple: #8b5cf6;
            --red: #ef4444;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--ink);
        }
        .ck * { box-sizing: border-box; }

        /* ---------- Header ---------- */
        .ck-header {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            border-radius: 24px;
            padding: 32px 36px;
            color: #fff;
            margin-bottom: 24px;
        }
        .ck-header::after {
            content: '🔥';
            position: absolute;
            right: -10px; top: -20px;
            font-size: 160px;
            opacity: 0.08;
            transform: rotate(-10deg);
        }
        .ck-eyebrow {
            font-size: 12px; letter-spacing: 0.25em; text-transform: uppercase;
            color: rgba(255,255,255,0.6); margin: 0 0 8px;
        }
        .ck-title { font-size: 34px; font-weight: 800; margin: 0; letter-spacing: -1px; }
        .ck-sub { font-size: 15px; color: rgba(255,255,255,0.75); margin: 6px 0 0; }
        .ck-datebar {
            position: relative; z-index: 2;
            display: flex; flex-wrap: wrap; align-items: flex-end; gap: 20px;
            margin-top: 22px;
        }
        .ck-datebar label {
            display: block; font-size: 12px; font-weight: 600;
            color: rgba(255,255,255,0.7); margin-bottom: 6px;
        }
        .ck-date-input {
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.25);
            color: #fff; border-radius: 12px; padding: 12px 16px;
            font-size: 15px; font-weight: 600; min-width: 220px;
            backdrop-filter: blur(8px);
        }
        .ck-date-input::-webkit-calendar-picker-indicator { filter: invert(1); cursor: pointer; }
        .ck-daychip {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2);
            border-radius: 999px; padding: 8px 16px; font-size: 14px; font-weight: 600;
        }

        /* ---------- Stats ---------- */
        .ck-stats {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px; margin-bottom: 24px;
        }
        .ck-stat {
            background: var(--bg); border: 1px solid var(--line); border-radius: 18px;
            padding: 20px; display: flex; align-items: center; gap: 16px;
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .ck-stat:hover { transform: translateY(-3px); box-shadow: 0 12px 28px rgba(15,23,42,.08); }
        .ck-stat-ic {
            width: 52px; height: 52px; border-radius: 14px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: 24px;
        }
        .ck-ic-blue   { background: #eff6ff; }
        .ck-ic-amber  { background: #fffbeb; }
        .ck-ic-green  { background: #ecfdf5; }
        .ck-ic-purple { background: #f5f3ff; }
        .ck-stat-num { font-size: 30px; font-weight: 800; line-height: 1; }
        .ck-stat-lbl { font-size: 13px; color: var(--muted); margin-top: 4px; font-weight: 500; }

        /* ---------- Card genérica ---------- */
        .ck-card {
            background: var(--bg); border: 1px solid var(--line);
            border-radius: 22px; margin-bottom: 24px; overflow: hidden;
        }
        .ck-card-head { padding: 22px 26px; border-bottom: 1px solid var(--line); }
        .ck-card-title { font-size: 18px; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 10px; }
        .ck-card-desc { font-size: 14px; color: var(--muted); margin: 4px 0 0; }
        .ck-card-body { padding: 26px; }

        /* ---------- Timeline ---------- */
        .ck-timeline {
            display: flex; align-items: flex-end; gap: 14px;
            min-height: 180px; padding-top: 10px; overflow-x: auto;
        }
        .ck-tl-col {
            flex: 1; min-width: 78px; display: flex; flex-direction: column; align-items: center;
            gap: 10px; cursor: pointer;
        }
        .ck-tl-track {
            width: 100%; height: 130px; background: var(--bg-soft);
            border-radius: 12px; display: flex; align-items: flex-end;
            border: 1px solid var(--line); overflow: hidden; position: relative;
        }
        .ck-tl-fill {
            width: 100%; border-radius: 0 0 11px 11px;
            transition: height .4s cubic-bezier(.2,.8,.2,1);
            display: flex; align-items: flex-start; justify-content: center;
            padding-top: 8px; color: #fff; font-weight: 800; font-size: 15px;
        }
        .ck-load-low    { background: linear-gradient(180deg, #34d399, #10b981); }
        .ck-load-mid    { background: linear-gradient(180deg, #fbbf24, #f59e0b); }
        .ck-load-high   { background: linear-gradient(180deg, #f87171, #ef4444); }
        .ck-tl-hour { font-size: 15px; font-weight: 700; }
        .ck-tl-meta { font-size: 11px; color: var(--muted); text-align: center; line-height: 1.3; }
        .ck-tl-col.active .ck-tl-track { outline: 3px solid var(--blue); outline-offset: 2px; }
        .ck-tl-col:hover .ck-tl-track { border-color: var(--blue); }

        .ck-legend { display: flex; flex-wrap: wrap; gap: 18px; margin-top: 18px; }
        .ck-legend-item { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--muted); }
        .ck-dot { width: 14px; height: 14px; border-radius: 4px; }

        /* ---------- Mise en place (platos del día) ---------- */
        .ck-dish-row {
            display: grid; grid-template-columns: 44px 1fr auto; align-items: center;
            gap: 14px; padding: 12px 0; border-bottom: 1px dashed var(--line);
        }
        .ck-dish-row:last-child { border-bottom: none; }
        .ck-dish-emoji {
            width: 44px; height: 44px; border-radius: 12px; background: var(--bg-soft);
            display: flex; align-items: center; justify-content: center; font-size: 22px;
            border: 1px solid var(--line);
        }
        .ck-dish-name { font-size: 15px; font-weight: 600; margin-bottom: 8px; }
        .ck-bar { height: 12px; border-radius: 999px; background: var(--bg-soft); overflow: hidden; }
        .ck-bar-fill {
            height: 100%; border-radius: 999px;
            background: linear-gradient(90deg, #60a5fa, #3b82f6);
            transition: width .5s cubic-bezier(.2,.8,.2,1);
        }
        .ck-dish-qty {
            font-size: 22px; font-weight: 800; min-width: 64px; text-align: right;
        }
        .ck-dish-qty small { font-size: 12px; font-weight: 600; color: var(--muted); }

        /* ---------- Hours grid ---------- */
        .ck-hours { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 18px; }
        .ck-hour {
            border: 1px solid var(--line); border-radius: 18px; padding: 20px;
            cursor: pointer; transition: all .25s ease; background: var(--bg); position: relative;
        }
        .ck-hour:hover { border-color: var(--blue); transform: translateY(-3px); box-shadow: 0 14px 30px rgba(59,130,246,.12); }
        .ck-hour.active { border-color: var(--blue); background: #f5f9ff; box-shadow: 0 0 0 3px rgba(59,130,246,.15); }
        .ck-hour-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
        .ck-hour-time { font-size: 24px; font-weight: 800; display: flex; align-items: center; gap: 8px; }
        .ck-hour-badges { display: flex; gap: 6px; }
        .ck-pill { font-size: 12px; font-weight: 700; padding: 5px 10px; border-radius: 999px; }
        .ck-pill-blue   { background: #eff6ff; color: #2563eb; }
        .ck-pill-purple { background: #f5f3ff; color: #7c3aed; }
        .ck-hour-bar { height: 8px; border-radius: 999px; background: var(--bg-soft); overflow: hidden; margin-bottom: 14px; }
        .ck-hour-names { display: flex; flex-direction: column; gap: 8px; }
        .ck-name-row { display: flex; align-items: center; justify-content: space-between; font-size: 14px; }
        .ck-avatar {
            width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; color: #fff; margin-right: 10px;
        }
        .ck-name-left { display: flex; align-items: center; min-width: 0; }
        .ck-name-left span { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .ck-name-time { color: var(--muted); font-weight: 600; font-size: 13px; }
        .ck-more { font-size: 13px; color: var(--blue); font-weight: 600; margin-top: 6px; }

        /* ---------- Order details ---------- */
        .ck-order { padding: 22px 26px; border-bottom: 1px solid var(--line); }
        .ck-order:last-child { border-bottom: none; }
        .ck-order-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; gap: 12px; }
        .ck-cust { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .ck-cust h4 { margin: 0; font-size: 17px; font-weight: 700; }
        .ck-cust p { margin: 2px 0 0; font-size: 13px; color: var(--muted); }
        .ck-status { font-size: 12px; font-weight: 700; padding: 7px 14px; border-radius: 999px; white-space: nowrap; }
        .ck-st-proc { background: #fffbeb; color: #b45309; }
        .ck-st-done { background: #ecfdf5; color: #047857; }
        .ck-st-pend { background: #f1f5f9; color: #475569; }
        .ck-prod-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 10px; }
        .ck-prod {
            display: flex; align-items: center; gap: 10px; padding: 10px 12px;
            background: var(--bg-soft); border: 1px solid var(--line); border-radius: 12px;
        }
        .ck-prod-emoji { font-size: 20px; }
        .ck-prod-name { font-size: 14px; font-weight: 600; flex: 1; }
        .ck-prod-qty {
            font-size: 13px; font-weight: 800; color: #fff; background: var(--blue);
            border-radius: 8px; padding: 2px 8px;
        }

        /* ---------- Empty ---------- */
        .ck-empty { text-align: center; padding: 70px 24px; }
        .ck-empty-emoji { font-size: 64px; }
        .ck-empty h3 { font-size: 20px; font-weight: 700; margin: 16px 0 6px; }
        .ck-empty p { color: var(--muted); margin: 0; }
        .ck-dates { margin-top: 24px; display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; }
        .ck-date-tag {
            background: var(--bg-soft); border: 1px solid var(--line); color: var(--ink);
            border-radius: 10px; padding: 8px 14px; font-size: 14px; font-weight: 600; cursor: pointer;
            transition: all .2s ease;
        }
        .ck-date-tag:hover { background: var(--blue); color: #fff; border-color: var(--blue); }

        @media (max-width: 768px) {
            .ck-header { padding: 24px 20px; }
            .ck-title { font-size: 26px; }
            .ck-card-body { padding: 18px; }
            .ck-hours { grid-template-columns: 1fr; }
        }
    </style>

    @php
        $statusColors = ['#6366f1','#0ea5e9','#10b981','#f59e0b','#ec4899','#8b5cf6','#14b8a6','#ef4444'];
        $avatarColor = fn($name) => $statusColors[abs(crc32($name)) % count($statusColors)];
    @endphp

    <div class="ck">
        <!-- ===== Header ===== -->
        <div class="ck-header">
            <p class="ck-eyebrow">Operaciones · Cocina</p>
            <h1 class="ck-title">Vista de Cocina</h1>
            <p class="ck-sub">Todo lo que hay que preparar, organizado por hora de servicio.</p>
            <div class="ck-datebar">
                <div>
                    <label>📅 Día de servicio</label>
                    <input type="date" wire:model.live="selectedDate" class="ck-date-input" />
                </div>
                <span class="ck-daychip">
                    🗓️ {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l, d M Y') }}
                </span>
                <span class="ck-daychip">🍽️ {{ $totalPlatos }} platos en total</span>
            </div>
        </div>

        <!-- ===== Stats ===== -->
        <div class="ck-stats">
            <div class="ck-stat">
                <div class="ck-stat-ic ck-ic-blue">📋</div>
                <div><div class="ck-stat-num">{{ $totalReservas }}</div><div class="ck-stat-lbl">Reservas del día</div></div>
            </div>
            <div class="ck-stat">
                <div class="ck-stat-ic ck-ic-amber">⏳</div>
                <div><div class="ck-stat-num">{{ $totalEnPrep }}</div><div class="ck-stat-lbl">En preparación</div></div>
            </div>
            <div class="ck-stat">
                <div class="ck-stat-ic ck-ic-green">✅</div>
                <div><div class="ck-stat-num">{{ $totalListas }}</div><div class="ck-stat-lbl">Completadas</div></div>
            </div>
            <div class="ck-stat">
                <div class="ck-stat-ic ck-ic-purple">🍽️</div>
                <div><div class="ck-stat-num">{{ $totalPlatos }}</div><div class="ck-stat-lbl">Platos a preparar</div></div>
            </div>
        </div>

        @if($ordersByHour->isEmpty())
            <!-- ===== Empty state ===== -->
            <div class="ck-card">
                <div class="ck-card-body">
                    <div class="ck-empty">
                        <div class="ck-empty-emoji">🍽️</div>
                        <h3>No hay reservas para este día</h3>
                        <p>No se encontraron reservas para el <strong>{{ $selectedDate }}</strong>.</p>
                        @php
                            $availableDates = [];
                            $today = date('Y-m-d');
                            foreach(\App\Models\Order::whereNotNull('booking_start')->whereNotIn('status',['pending','draft','failed','cancelled'])->get() as $o) {
                                $d = date('Y-m-d', strtotime($o->booking_start));
                                if($d >= $today && !in_array($d, $availableDates)) $availableDates[] = $d;
                            }
                            sort($availableDates);
                        @endphp
                        @if($availableDates)
                            <div class="ck-dates">
                                @foreach(array_slice($availableDates, 0, 10) as $d)
                                    <div class="ck-date-tag" wire:click="$set('selectedDate', '{{ $d }}')">
                                        {{ \Carbon\Carbon::parse($d)->translatedFormat('D d M') }}
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <!-- ===== Línea de tiempo del día ===== -->
            <div class="ck-card">
                <div class="ck-card-head">
                    <h2 class="ck-card-title">📊 Carga del día</h2>
                    <p class="ck-card-desc">Cantidad de platos por franja horaria. Toca una hora para ver el detalle.</p>
                </div>
                <div class="ck-card-body">
                    <div class="ck-timeline">
                        @foreach($hourLoads as $hour => $load)
                            @php
                                $ratio = $maxHourLoad > 0 ? $load['platos'] / $maxHourLoad : 0;
                                $h = max(18, round($ratio * 100));
                                $loadClass = $ratio >= 0.66 ? 'ck-load-high' : ($ratio >= 0.33 ? 'ck-load-mid' : 'ck-load-low');
                            @endphp
                            <div class="ck-tl-col {{ $selectedHour === $hour ? 'active' : '' }}" wire:click="$set('selectedHour', '{{ $hour }}')">
                                <div class="ck-tl-track">
                                    <div class="ck-tl-fill {{ $loadClass }}" style="height: {{ $h }}%;">{{ $load['platos'] }}</div>
                                </div>
                                <div class="ck-tl-hour">{{ $hour }}</div>
                                <div class="ck-tl-meta">{{ $load['reservas'] }} reservas<br>{{ $load['platos'] }} platos</div>
                            </div>
                        @endforeach
                    </div>
                    <div class="ck-legend">
                        <div class="ck-legend-item"><span class="ck-dot ck-load-low"></span> Carga baja</div>
                        <div class="ck-legend-item"><span class="ck-dot ck-load-mid"></span> Carga media</div>
                        <div class="ck-legend-item"><span class="ck-dot ck-load-high"></span> Carga alta</div>
                    </div>
                </div>
            </div>



            <!-- ===== Horarios del día ===== -->
            <div class="ck-card">
                <div class="ck-card-head">
                    <h2 class="ck-card-title">⏰ Reservas por hora</h2>
                    <p class="ck-card-desc">Toca una hora para ver clientes y productos a preparar.</p>
                </div>
                <div class="ck-card-body">
                    <div class="ck-hours">
                        @foreach($ordersByHour as $hour => $orders)
                            @php
                                $platos = $hourLoads[$hour]['platos'];
                                $ratio = $maxHourLoad > 0 ? $platos / $maxHourLoad : 0;
                                $barClass = $ratio >= 0.66 ? 'ck-load-high' : ($ratio >= 0.33 ? 'ck-load-mid' : 'ck-load-low');
                            @endphp
                            <div class="ck-hour {{ $selectedHour === $hour ? 'active' : '' }}" wire:click="$set('selectedHour', '{{ $hour }}')">
                                <div class="ck-hour-top">
                                    <div class="ck-hour-time">🕐 {{ $hour }}</div>
                                    <div class="ck-hour-badges">
                                        <span class="ck-pill ck-pill-blue">{{ $orders->count() }} res.</span>
                                        <span class="ck-pill ck-pill-purple">{{ $platos }} platos</span>
                                    </div>
                                </div>
                                <div class="ck-hour-bar"><div class="ck-bar-fill {{ $barClass }}" style="width: {{ max(6, round($ratio*100)) }}%;"></div></div>
                                <div class="ck-hour-names">
                                    @foreach($orders->take(3) as $order)
                                        <div class="ck-name-row">
                                            <div class="ck-name-left">
                                                <span class="ck-avatar" style="background: {{ $avatarColor($order->customer_name ?? '?') }};">
                                                    {{ Str::upper(Str::substr($order->customer_name ?? '?', 0, 1)) }}
                                                </span>
                                                <span>{{ $order->customer_name }}</span>
                                            </div>
                                            <span class="ck-name-time">{{ date('H:i', strtotime($order->booking_start)) }}</span>
                                        </div>
                                    @endforeach
                                    @if($orders->count() > 3)
                                        <div class="ck-more">+{{ $orders->count() - 3 }} reservas más…</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- ===== Detalle de la hora seleccionada ===== -->
            @if($selectedHour)
                @php
                    $selectedOrders = $this->getOrdersForHour($selectedHour);
                    $hourSummary = $this->getProductSummary($selectedOrders);
                    $maxHourDish = $hourSummary ? max($hourSummary) : 0;
                @endphp

                <!-- Resumen de la hora -->
                <div class="ck-card">
                    <div class="ck-card-head">
                        <h2 class="ck-card-title">🔥 A preparar para las {{ $selectedHour }}</h2>
                        <p class="ck-card-desc">{{ $selectedOrders->count() }} reservas · {{ array_sum($hourSummary) }} platos en total</p>
                    </div>
                    <div class="ck-card-body">
                        @foreach($hourSummary as $name => $qty)
                            <div class="ck-dish-row">
                                <div class="ck-dish-emoji">{{ $this->dishIcon($name) }}</div>
                                <div>
                                    <div class="ck-dish-name">{{ $name }}</div>
                                    <div class="ck-bar">
                                        <div class="ck-bar-fill" style="width: {{ $maxHourDish > 0 ? round(($qty / $maxHourDish) * 100) : 0 }}%;"></div>
                                    </div>
                                </div>
                                <div class="ck-dish-qty">{{ $qty }}<small> uds</small></div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Reservas de la hora -->
                <div class="ck-card">
                    <div class="ck-card-head">
                        <h2 class="ck-card-title">👥 Reservas — {{ $selectedHour }}</h2>
                        <p class="ck-card-desc">Detalle por cliente con sus platos.</p>
                    </div>
                    <div>
                        @foreach($selectedOrders as $order)
                            @php $productos = $this->extractProducts($order); @endphp
                            <div class="ck-order">
                                <div class="ck-order-head">
                                    <div class="ck-cust">
                                        <span class="ck-avatar" style="width:40px;height:40px;font-size:16px;background: {{ $avatarColor($order->customer_name ?? '?') }};">
                                            {{ Str::upper(Str::substr($order->customer_name ?? '?', 0, 1)) }}
                                        </span>
                                        <div>
                                            <h4>{{ $order->customer_name }}</h4>
                                            <p>🕐 {{ date('H:i', strtotime($order->booking_start)) }} – {{ date('H:i', strtotime($order->booking_end)) }}</p>
                                        </div>
                                    </div>
                                    <span class="ck-status {{ $order->status == 'processing' ? 'ck-st-proc' : ($order->status == 'completed' ? 'ck-st-done' : 'ck-st-pend') }}">
                                        {{ $order->status == 'processing' ? '⏳ Preparando' : ($order->status == 'completed' ? '✅ Listo' : '• Pendiente') }}
                                    </span>
                                </div>
                                <div class="ck-prod-grid">
                                    @forelse($productos as $p)
                                        <div class="ck-prod">
                                            <span class="ck-prod-emoji">{{ $this->dishIcon($p['name']) }}</span>
                                            <span class="ck-prod-name">{{ $p['name'] }}</span>
                                            <span class="ck-prod-qty">×{{ $p['quantity'] }}</span>
                                        </div>
                                    @empty
                                        <span style="color: var(--muted); font-size: 14px;">Sin productos registrados</span>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>
</x-filament-panels::page>
