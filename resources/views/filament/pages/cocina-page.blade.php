@php
    use Illuminate\Support\Str;
    use Carbon\Carbon;
    Carbon::setLocale('es');
    $turnos = $this->getTurnos();
    $turno = $this->getCurrentTurno();
    [$wkFrom, $wkTo] = $this->weekRange();
    $money = fn ($n) => '$' . number_format((float) $n, 2);
@endphp

<x-filament-panels::page>
<div class="ck">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">

<style>
  .ck{
    --ink:#0E1526; --ink-2:#172139; --paper:#F6F7F9; --card:#FFFFFF; --line:#E6E9EF;
    --text:#1B2434; --muted:#69748A; --gold:#E8B544; --gold-deep:#C28F1E; --coral:#FF6B57;
    --coral-deep:#D6492F; --teal:#1FA98C; --sky:#3D7BFA; --r:14px;
    font-family:'Inter',sans-serif; color:var(--text); -webkit-font-smoothing:antialiased; font-size:15px;
  }
  .ck *{box-sizing:border-box}
  .ck .mono{font-family:'IBM Plex Mono',monospace}

  .ck .hero{background:linear-gradient(150deg,var(--ink),var(--ink-2));border-radius:18px;color:#fff;padding:24px 28px;margin-bottom:18px;display:flex;justify-content:space-between;align-items:center;gap:18px;flex-wrap:wrap}
  .ck .hero .eyebrow{font-size:11px;letter-spacing:.22em;color:#8E9FBE;font-weight:600}
  .ck .hero h1{font-family:'Space Grotesk';font-size:28px;font-weight:700;margin-top:4px}
  .ck .hero .d{font-size:13px;color:#9FB0CC;margin-top:6px;max-width:520px}
  .ck .hero .right{display:flex;gap:12px;align-items:center;flex-wrap:wrap}
  .ck .datebox{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.16);border-radius:11px;padding:8px 14px;font-family:'IBM Plex Mono';font-size:13px;color:#fff}
  .ck .datebox input{background:transparent;border:none;color:#fff;font-family:'IBM Plex Mono';font-size:13px;outline:none}
  .ck .datebox input::-webkit-calendar-picker-indicator{filter:invert(1)}

  .ck .maintabs{display:flex;gap:8px;margin-bottom:18px;flex-wrap:wrap}
  .ck .mt{font-family:'Space Grotesk';font-size:14px;font-weight:600;padding:11px 20px;border-radius:99px;border:1px solid var(--line);background:var(--card);color:var(--muted);cursor:pointer}
  .ck .mt.on{background:var(--ink);color:#fff;border-color:var(--ink)}

  .ck .turnos{display:flex;gap:12px;margin-bottom:18px;flex-wrap:wrap}
  .ck .turno{flex:1;min-width:200px;border:1.5px solid var(--line);background:var(--card);border-radius:14px;padding:14px 18px;cursor:pointer;text-align:left}
  .ck .turno .h{font-family:'IBM Plex Mono';font-size:20px;font-weight:600}
  .ck .turno .n{font-size:12.5px;color:var(--muted);margin-top:2px}
  .ck .turno .p{font-family:'Space Grotesk';font-size:13px;font-weight:600;margin-top:8px;color:var(--teal)}
  .ck .turno.on{border-color:var(--ink);background:var(--ink);color:#fff}
  .ck .turno.on .n{color:#9FB0CC}.ck .turno.on .p{color:#F3CE7E}

  .ck .check{border-radius:13px;padding:14px 20px;font-size:15px;font-weight:600;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
  .ck .check.ok{background:#E7F6F1;border:1.5px solid #BFE7DB;color:#0E6E58}
  .ck .check .big{font-family:'Space Grotesk';font-size:20px}

  .ck .panel{background:var(--card);border:1px solid var(--line);border-radius:var(--r);padding:20px 22px;margin-bottom:16px}
  .ck .panel h3{font-family:'Space Grotesk';font-size:16px;font-weight:600;margin-bottom:14px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px}
  .ck .panel h3 small{font-size:12px;color:var(--muted);font-weight:500;font-family:'Inter'}
  .ck .mixbar{display:flex;height:30px;border-radius:10px;overflow:hidden;margin-bottom:16px;background:var(--line)}
  .ck .mixbar i{display:flex;align-items:center;justify-content:center;color:#fff;font-family:'Space Grotesk';font-weight:700;font-size:14px}
  .ck .platos{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px}
  .ck .plato{border-radius:13px;padding:16px;color:#fff;position:relative;overflow:hidden}
  .ck .plato .ic{font-size:26px}
  .ck .plato .ct{font-family:'Space Grotesk';font-size:38px;font-weight:700;line-height:1;margin-top:6px}
  .ck .plato .nm{font-size:13px;font-weight:600;margin-top:4px;opacity:.92}
  .ck .plato .done{font-size:11.5px;margin-top:8px;background:rgba(255,255,255,.22);border-radius:99px;padding:3px 10px;display:inline-block;font-weight:600}

  .ck table{width:100%;border-collapse:collapse;font-size:14px}
  .ck th{font-size:11px;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);font-weight:600;text-align:left;padding:10px 12px;border-bottom:1px solid var(--line)}
  .ck td{padding:12px;border-bottom:1px solid var(--line);vertical-align:middle}
  .ck .seatcell{font-family:'IBM Plex Mono';font-weight:600;font-size:15px}
  .ck .dishchip{display:inline-flex;align-items:center;gap:7px;font-weight:700;font-size:13px;padding:6px 13px;border-radius:99px;color:#fff}
  .ck .stbtn{font-size:12.5px;font-weight:700;border:none;border-radius:99px;padding:8px 16px;cursor:pointer;min-width:140px}
  .ck .st-pend{background:#F1F3F7;color:var(--muted)}
  .ck .st-prep{background:#FBF3E0;color:var(--gold-deep)}
  .ck .st-listo{background:#E7F6F1;color:var(--teal)}

  .ck .invgrid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:14px;margin-bottom:18px}
  .ck .inv{background:var(--card);border:1px solid var(--line);border-radius:var(--r);padding:18px 20px}
  .ck .inv .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
  .ck .inv .nm{font-family:'Space Grotesk';font-weight:700;font-size:15px;display:flex;align-items:center;gap:8px}
  .ck .inv .row{display:flex;justify-content:space-between;font-size:13px;padding:4px 0;color:var(--muted)}
  .ck .inv .row b{color:var(--text);font-family:'Space Grotesk'}
  .ck .stockbar{height:12px;border-radius:99px;background:#EEF1F6;overflow:hidden;margin:10px 0 6px}
  .ck .stockbar i{display:block;height:100%;border-radius:99px}
  .ck .inv .status{font-size:12px;font-weight:700;margin-top:6px}
  .ck .inv .status.ok{color:var(--teal)} .ck .inv .status.warn{color:var(--coral-deep)}
  .ck .okb{display:inline-block;font-size:11px;font-weight:700;padding:4px 11px;border-radius:99px}
  .ck .okb.si{background:#E7F6F1;color:var(--teal)} .ck .okb.no{background:#FFF0ED;color:var(--coral-deep)}
  .ck .compra-form{display:grid;grid-template-columns:repeat(5,1fr) auto;gap:10px;align-items:end}
  .ck .f label{display:block;font-size:11.5px;font-weight:600;color:var(--muted);margin-bottom:5px}
  .ck .f input,.ck .f select{width:100%;border:1px solid var(--line);border-radius:9px;padding:10px 11px;font-size:13.5px;font-family:'Inter';background:var(--card)}
  .ck .addbtn{font-family:'Space Grotesk';font-size:13.5px;font-weight:600;background:var(--teal);color:#fff;border:none;border-radius:10px;padding:12px 20px;cursor:pointer;white-space:nowrap}
  .ck .ledger td,.ck .ledger th{font-size:13px;padding:10px 12px}

  .ck .rep-sum{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:18px}
  .ck .rs{background:var(--card);border:1px solid var(--line);border-radius:var(--r);padding:18px 20px}
  .ck .rs .v{font-family:'Space Grotesk';font-size:30px;font-weight:700;margin-top:4px}
  .ck .rs .l{font-size:12px;color:var(--muted);font-weight:600}
  .ck .rs .s{font-size:12px;margin-top:4px;color:var(--muted)}
  .ck .conteo{width:80px;border:1.5px solid var(--line);border-radius:8px;padding:7px;font-family:'IBM Plex Mono';font-size:14px;text-align:center}
  .ck .repnote{background:#EBF1FF;border:1px solid #C7D8FB;border-radius:12px;padding:14px 18px;font-size:13px;color:#1D4FB8;line-height:1.65;margin-bottom:16px}
  .ck .empty{padding:26px;text-align:center;color:var(--muted);font-size:14px}

  @media (max-width:840px){.ck .compra-form{grid-template-columns:1fr 1fr}}
  @media (max-width:640px){
    .ck{font-size:14px}
    .ck .hero{padding:20px 18px}
    .ck .hero h1{font-size:23px}
    .ck .turno{min-width:140px}
    .ck .compra-form{grid-template-columns:1fr}
    .ck .panel{padding:16px 14px;overflow-x:auto;-webkit-overflow-scrolling:touch}
    .ck table{min-width:520px}
    .ck .stbtn{min-width:0}
  }
</style>

  {{-- ===== CABECERA ===== --}}
  <div class="hero">
    <div>
      <div class="eyebrow">OPERACIONES · COCINA</div>
      <h1>🔥 Cocina</h1>
      <div class="d">Todo lo que hay que preparar, por reserva. 1 comensal = 1 plato principal; los menús se bloquean 24 h antes del despegue.</div>
    </div>
    <div class="right">
      <div class="datebox">📅 <input type="date" wire:model.live="selectedDate"></div>
    </div>
  </div>

  {{-- ===== TABS ===== --}}
  <div class="maintabs">
    <button class="mt {{ $activeTab === 'servicio' ? 'on' : '' }}" wire:click="setTab('servicio')">🍽 Servicio del día</button>
    <button class="mt {{ $activeTab === 'inventario' ? 'on' : '' }}" wire:click="setTab('inventario')">📦 Inventario semanal</button>
    <button class="mt {{ $activeTab === 'reporte' ? 'on' : '' }}" wire:click="setTab('reporte')">📊 Reporte compras vs ventas</button>
  </div>

  {{-- ============ SERVICIO DEL DÍA ============ --}}
  @if ($activeTab === 'servicio')
    @if (empty($turnos))
      <div class="panel"><div class="empty">No hay reservas confirmadas para el {{ Carbon::parse($selectedDate)->isoFormat('dddd D [de] MMMM') }}.</div></div>
    @else
      <div class="turnos">
        @foreach ($turnos as $t)
          <button class="turno {{ $turno['hour'] === $t['hour'] ? 'on' : '' }}" wire:click="selectHour('{{ $t['hour'] }}')">
            <div class="h">{{ $t['hour'] }}</div>
            <div class="n">{{ $t['name'] }}</div>
            <div class="p">{{ $t['pax'] }} pax · {{ $t['pax'] }} platos</div>
          </button>
        @endforeach
      </div>

      @php $total = $turno['pax']; @endphp
      <div class="check ok">
        <span>✓ Comanda cuadrada: <b>{{ $total }} comensales = {{ $total }} platos</b></span>
        <span class="big">@foreach ($turno['mix'] as $cat => $v){{ $this->dishIcon($cat) }} {{ $v }}@if (!$loop->last) · @endif @endforeach</span>
      </div>

      <div class="panel">
        <h3>Platos del turno {{ $turno['hour'] }} <small>la barra suma siempre el total de pax</small></h3>
        <div class="mixbar">
          @foreach ($turno['mix'] as $cat => $v)
            <i style="width:{{ $total > 0 ? $v / $total * 100 : 0 }}%;background:{{ $this->dishColor($cat) }}">{{ $v }}</i>
          @endforeach
        </div>
        <div class="platos">
          @foreach ($turno['mix'] as $cat => $v)
            @php
              $done = 0;
              foreach ($turno['guests'] as $idx => $g) {
                  if ($g['dish'] === $cat && ($dishStates[$turno['hour'] . '-' . $idx] ?? 0) === 2) $done++;
              }
            @endphp
            <div class="plato" style="background:{{ $this->dishColor($cat) }}">
              <span class="ic">{{ $this->dishIcon($cat) }}</span>
              <div class="ct">{{ $v }}</div>
              <div class="nm">{{ Str::upper($cat) }}</div>
              <span class="done">{{ $done }} / {{ $v }} listos</span>
            </div>
          @endforeach
        </div>
      </div>

      <div class="panel">
        <h3>Comanda por comensal <small>toca el estado para avanzar: Pendiente → En preparación → Listo</small></h3>
        <table>
          <thead><tr><th>Asiento</th><th>Comensal</th><th>Plato principal</th><th>Menú original</th><th>Estado</th></tr></thead>
          <tbody>
            @foreach ($turno['guests'] as $idx => $g)
              @php
                $key = $turno['hour'] . '-' . $idx;
                $st = $dishStates[$key] ?? 0;
                $lbl = ['PENDIENTE', 'EN PREPARACIÓN', '✓ LISTO'][$st];
                $cls = ['st-pend', 'st-prep', 'st-listo'][$st];
              @endphp
              <tr>
                <td class="seatcell">{{ $g['seat'] }}</td>
                <td><b>{{ $g['name'] }}</b></td>
                <td><span class="dishchip" style="background:{{ $this->dishColor($g['dish']) }}">{{ $this->dishIcon($g['dish']) }} {{ $g['dish'] }}</span></td>
                <td style="font-size:12.5px;color:var(--muted)">{{ $g['raw'] }}</td>
                <td><button class="stbtn {{ $cls }}" wire:click="advanceDish('{{ $key }}')">{{ $lbl }}</button></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  @endif

  {{-- ============ INVENTARIO ============ --}}
  @if ($activeTab === 'inventario')
    <div class="repnote">📦 <b>Cómo funciona:</b> cada semana se dan de alta las compras en <b>porciones</b> (1 porción = 1 plato). El sistema descuenta automáticamente cada plato vendido y reserva las porciones de las reservas confirmadas. Si lo disponible no cubre lo reservado, salta la alerta roja.</div>

    <div class="invgrid">
      @foreach ($this->getInventoryCards() as $c)
        <div class="inv">
          <div class="top">
            <span class="nm"><span style="width:12px;height:12px;border-radius:4px;display:inline-block;background:{{ $c['color'] }}"></span>{{ $c['icon'] }} {{ $c['item'] }}</span>
            <span class="okb {{ $c['ok'] ? 'si' : 'no' }}">{{ $c['ok'] ? 'CUBIERTO' : 'COMPRAR' }}</span>
          </div>
          <div class="row"><span>Compradas (semana)</span><b>{{ $c['comp'] }}</b></div>
          <div class="row"><span>Consumidas por ventas</span><b>−{{ $c['vend'] }}</b></div>
          <div class="row"><span>Reservadas próx. reservas</span><b>{{ $c['res'] }}</b></div>
          <div class="stockbar"><i style="width:{{ $c['pct'] }}%;background:{{ $c['ok'] ? $c['color'] : 'var(--coral)' }}"></i></div>
          <div class="row"><span>Disponibles</span><b style="font-size:18px">{{ $c['disp'] }}</b></div>
          <div class="status {{ $c['ok'] ? 'ok' : 'warn' }}">{{ $c['ok'] ? '✓ Cubre las reservas confirmadas' : '⚠ Faltan ' . max(0, $c['res'] - $c['disp']) . ' porciones — comprar pronto' }}</div>
        </div>
      @endforeach
    </div>

    <div class="panel">
      <h3>＋ Dar de alta compra semanal</h3>
      <div class="compra-form">
        <div class="f"><label>Fecha</label><input type="date" wire:model="pDate"></div>
        <div class="f"><label>Proveedor</label>
          <select wire:model="pSupplier"><option>Mercarne PC</option><option>Pesquera del Este</option><option>AgroBávaro</option><option>Otro</option></select></div>
        <div class="f"><label>Ítem</label>
          <select wire:model="pItem">@foreach (\App\Filament\Pages\CocinaPage::ITEMS as $it)<option>{{ $it }}</option>@endforeach</select></div>
        <div class="f"><label>Porciones</label><input type="number" wire:model="pPortions"></div>
        <div class="f"><label>Costo total US$</label><input type="number" wire:model="pCost"></div>
        <button class="addbtn" wire:click="addPurchase">Registrar compra</button>
      </div>
    </div>

    <div class="panel">
      <h3>Libro de compras de la semana <small>{{ $wkFrom->isoFormat('D MMM') }} — {{ $wkTo->isoFormat('D MMM') }}</small></h3>
      <table class="ledger">
        <thead><tr><th>Fecha</th><th>Proveedor</th><th>Ítem</th><th>Porciones</th><th>Costo</th><th>$/porción</th></tr></thead>
        <tbody>
          @forelse ($this->getLedger() as $p)
            <tr>
              <td class="mono">{{ $p->date->format('d/m') }}</td>
              <td>{{ $p->supplier ?? '—' }}</td>
              <td>{{ $p->item }}</td>
              <td><b>{{ $p->portions }}</b></td>
              <td>{{ $money($p->cost_total) }}</td>
              <td class="mono">{{ $money($p->cost_per_portion) }}</td>
            </tr>
          @empty
            <tr><td colspan="6" class="empty">Sin compras registradas esta semana.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  @endif

  {{-- ============ REPORTE ============ --}}
  @if ($activeTab === 'reporte')
    @php $rep = $this->getReconciliation(); @endphp
    <div class="repnote">📊 Este reporte cruza automáticamente <b>compras registradas</b> contra <b>platos vendidos</b> (de las ventas reales) en el mismo período. El conteo físico cierra la semana: si compras − ventas ≠ conteo, la diferencia es <b>merma</b> y se valora al costo por porción.</div>

    <div class="rep-sum">
      <div class="rs"><div class="l">Porciones compradas</div><div class="v">{{ $rep['totalPurchased'] }}</div><div class="s">{{ $money($rep['totalCost']) }} invertidos</div></div>
      <div class="rs"><div class="l">Platos vendidos (semana)</div><div class="v">{{ $rep['totalSold'] }}</div><div class="s">consumo real de reservas</div></div>
      <div class="rs"><div class="l">Conciliación</div><div class="v" style="color:var(--teal)">{{ $rep['conciliacion'] }}%</div><div class="s">objetivo ≥ 97%</div></div>
      <div class="rs"><div class="l">Merma valorada</div><div class="v" style="color:var(--coral-deep)">{{ $money($rep['mermaValue']) }}</div><div class="s">{{ $rep['mermaPortions'] }} porciones perdidas</div></div>
    </div>

    <div class="panel">
      <h3>Conciliación por ítem <small>semana {{ $wkFrom->isoFormat('D') }}–{{ $wkTo->isoFormat('D MMM') }} · ingresa el conteo físico para cerrar</small></h3>
      <table>
        <thead><tr><th>Ítem</th><th>Compradas</th><th>Vendidas</th><th>Reservadas</th><th>Stock teórico</th><th>Conteo físico</th><th>Estado</th></tr></thead>
        <tbody>
          @foreach ($rep['rows'] as $r)
            <tr>
              <td><b>{{ $r['icon'] }} {{ $r['item'] }}</b></td>
              <td>{{ $r['comp'] }}</td>
              <td>{{ $r['vend'] }}</td>
              <td>{{ $r['res'] }}</td>
              <td class="mono">{{ $r['theoretical'] }}</td>
              <td><input class="conteo" type="number" wire:model="physicalCounts.{{ $r['item'] }}"></td>
              <td>
                @if ($r['physical'] === null)
                  <span class="okb" style="background:#F1F3F7;color:var(--muted)">SIN CONTAR</span>
                @elseif ($r['ok'])
                  <span class="okb si">✓ COINCIDE</span>
                @else
                  <span class="okb no">⚠ MERMA {{ $r['merma'] }} ({{ $money($r['mermaCost']) }})</span>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
      <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px">
        <button class="addbtn" wire:click="closeWeek">Cerrar semana</button>
      </div>
    </div>
  @endif
</div>
</x-filament-panels::page>
