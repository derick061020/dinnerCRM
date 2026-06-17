@php
    $fmt = fn ($n) => '$' . number_format((float) $n, 2);
    $pendingTotal = $attention->where('paid', false)->count();
    $noDateTotal = $attention->whereNull('date')->count();
    $viatorTotal = $attention->where('channel', 'viator')->count();
@endphp

<x-filament-panels::page>
<div class="dits">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">

<style>
  .dits{
    --ink:#0E1526; --ink-2:#172139; --paper:#F6F7F9; --card:#FFFFFF; --line:#E6E9EF;
    --text:#1B2434; --muted:#69748A; --gold:#E8B544; --gold-deep:#C28F1E; --coral:#FF6B57;
    --teal:#1FA98C; --sky:#3D7BFA; --viator:#7B5CD6; --r:14px;
    font-size:15px; font-family:'Inter',sans-serif; color:var(--text); -webkit-font-smoothing:antialiased;
  }
  .dits *{box-sizing:border-box}
  .dits .mono{font-family:'IBM Plex Mono',monospace}

  /* ===== Top bar ===== */
  .dits .topbar{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:22px;flex-wrap:wrap}
  .dits .topbar h1{font-family:'Space Grotesk',sans-serif;font-size:30px;font-weight:700;letter-spacing:-.02em;margin:0}
  .dits .topbar .date{color:var(--muted);font-size:13.5px;margin-top:4px;text-transform:capitalize}
  .dits .wind{display:flex;align-items:center;gap:14px;background:var(--card);border:1px solid var(--line);border-radius:var(--r);padding:12px 18px}
  .dits .wind .val{font-family:'Space Grotesk';font-size:22px;font-weight:700}
  .dits .wind .lbl{font-size:11.5px;color:var(--muted);line-height:1.35}
  .dits .wind .ok{color:var(--teal);font-weight:600}
  .dits .wind .limit{font-size:11px;color:var(--muted)}

  /* ===== Flight board ===== */
  .dits .board{background:linear-gradient(165deg,var(--ink) 0%,var(--ink-2) 100%);border-radius:18px;padding:26px 28px 22px;color:#EAF0FB;margin-bottom:26px;position:relative;overflow:hidden}
  .dits .board::after{content:"";position:absolute;inset:0;background:radial-gradient(620px 140px at 85% -20%,rgba(232,181,68,.18),transparent 70%);pointer-events:none}
  .dits .board-head{display:flex;justify-content:space-between;align-items:baseline;margin-bottom:18px;gap:16px;flex-wrap:wrap}
  .dits .board-head h2{font-family:'Space Grotesk';font-size:13px;font-weight:600;letter-spacing:.22em;text-transform:uppercase;color:#9FB0CC;margin:0}
  .dits .board-head .sum{font-size:13px;color:#9FB0CC}
  .dits .board-head .sum b{color:var(--gold);font-family:'Space Grotesk';font-size:16px}
  .dits .flight{display:grid;grid-template-columns:86px 1.2fr 1.6fr auto;gap:20px;align-items:center;padding:16px 4px;border-top:1px solid rgba(255,255,255,.08)}
  .dits .flight:first-of-type{border-top:none}
  .dits .f-time{font-family:'IBM Plex Mono';font-size:21px;font-weight:600;letter-spacing:.02em}
  .dits .f-time small{display:block;font-size:10.5px;color:#8E9FBE;letter-spacing:.14em;margin-top:3px}
  .dits .f-name{font-family:'Space Grotesk';font-size:16.5px;font-weight:600}
  .dits .f-tags{display:flex;gap:8px;margin-top:7px;flex-wrap:wrap}
  .dits .tag{font-size:11px;padding:3px 9px;border-radius:99px;background:rgba(255,255,255,.08);color:#C9D5EA;border:1px solid rgba(255,255,255,.1)}
  .dits .tag.warn{background:rgba(255,107,87,.16);color:#FFAB9E;border-color:rgba(255,107,87,.3)}
  .dits .tag.cake{background:rgba(232,181,68,.14);color:#F3CE7E;border-color:rgba(232,181,68,.3)}
  .dits .seats{display:grid;grid-template-columns:repeat(11,16px);grid-auto-rows:16px;gap:5px;justify-content:start}
  .dits .seat{width:16px;height:16px;border-radius:5px 5px 8px 8px;background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.14);transition:transform .15s}
  .dits .seat.sold{background:var(--gold);border-color:var(--gold-deep);box-shadow:0 0 8px rgba(232,181,68,.35)}
  .dits .seat:hover{transform:translateY(-2px)}
  .dits .f-occ{text-align:right;min-width:120px}
  .dits .f-occ .pct{font-family:'Space Grotesk';font-size:24px;font-weight:700}
  .dits .f-occ .pax{font-size:12px;color:#9FB0CC;margin-top:2px}
  .dits .chip{display:inline-block;margin-top:8px;font-size:11px;font-weight:600;padding:4px 11px;border-radius:99px;letter-spacing:.04em}
  .dits .chip.sale{background:rgba(31,169,140,.18);color:#5BD6BB}
  .dits .chip.full{background:rgba(232,181,68,.2);color:#F3CE7E}
  .dits .chip.risk{background:rgba(255,107,87,.2);color:#FFAB9E}
  .dits .board-empty{padding:20px 4px;color:#9FB0CC;font-size:14px}

  /* ===== Alertas ===== */
  .dits .alerts{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:14px;margin-bottom:26px}
  .dits .alert{background:var(--card);border:1px solid var(--line);border-left:4px solid var(--coral);border-radius:var(--r);padding:16px 18px;transition:box-shadow .15s}
  .dits .alert:hover{box-shadow:0 6px 18px rgba(20,30,55,.08)}
  .dits .alert.gold{border-left-color:var(--gold)}
  .dits .alert.teal{border-left-color:var(--teal)}
  .dits .alert .n{font-family:'Space Grotesk';font-size:26px;font-weight:700}
  .dits .alert .t{font-size:13px;font-weight:600;margin-top:2px}
  .dits .alert .d{font-size:12px;color:var(--muted);margin-top:4px;line-height:1.45}
  .dits .alert .act{font-size:12px;font-weight:600;color:var(--sky);margin-top:9px;display:inline-block;text-decoration:none}

  /* ===== KPIs ===== */
  .dits .kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:14px;margin-bottom:30px}
  .dits .kpi{background:var(--card);border:1px solid var(--line);border-radius:var(--r);padding:18px 20px}
  .dits .kpi .lbl{font-size:12px;color:var(--muted);font-weight:500;letter-spacing:.02em;text-transform:capitalize}
  .dits .kpi .val{font-family:'Space Grotesk';font-size:27px;font-weight:700;margin-top:6px}
  .dits .kpi .sub{font-size:12px;margin-top:5px;color:var(--muted)}
  .dits .up{color:var(--teal);font-weight:600}
  .dits .down{color:var(--coral);font-weight:600}
  .dits .mixbar{display:flex;height:8px;border-radius:99px;overflow:hidden;margin-top:10px;background:var(--line)}
  .dits .mixbar i{display:block}
  .dits .legend{display:flex;gap:14px;font-size:11.5px;color:var(--muted);margin-top:8px}
  .dits .dot{display:inline-block;width:8px;height:8px;border-radius:99px;margin-right:5px;vertical-align:1px}

  /* ===== Tabla ===== */
  .dits .panel{background:var(--card);border:1px solid var(--line);border-radius:var(--r);overflow:hidden}
  .dits .panel-head{display:flex;justify-content:space-between;align-items:center;padding:18px 22px;border-bottom:1px solid var(--line);flex-wrap:wrap;gap:12px}
  .dits .panel-head h3{font-family:'Space Grotesk';font-size:16.5px;font-weight:600;margin:0}
  .dits .panel-head p{font-size:12.5px;color:var(--muted);margin-top:3px}
  .dits .filters{display:flex;gap:8px;flex-wrap:wrap}
  .dits .fbtn{font-size:12px;font-weight:600;padding:7px 13px;border-radius:99px;border:1px solid var(--line);background:var(--paper);color:var(--muted);cursor:pointer}
  .dits .fbtn.on{background:var(--ink);color:#fff;border-color:var(--ink)}
  .dits table{width:100%;border-collapse:collapse;font-size:13.5px}
  .dits th{font-size:11px;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);font-weight:600;text-align:left;padding:12px 14px;border-bottom:1px solid var(--line);background:#FBFCFD}
  .dits td{padding:14px;border-bottom:1px solid var(--line);vertical-align:middle}
  .dits tbody tr:hover td{background:#FAFBFD}
  .dits .cl-name{font-weight:600}
  .dits .cl-sub{font-size:11.5px;color:var(--muted);margin-top:2px}
  .dits .b{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;padding:3px 9px;border-radius:99px}
  .dits .b-dir{background:#EBF1FF;color:var(--sky)}
  .dits .b-via{background:#F1EDFB;color:var(--viator)}
  .dits .b-hot{background:#FBF3E0;color:var(--gold-deep)}
  .dits .b-paid{background:#E7F6F1;color:var(--teal)}
  .dits .b-pend{background:#FFF0ED;color:#D6492F}
  .dits .slot{font-family:'IBM Plex Mono';font-size:12.5px;font-weight:500}
  .dits .slot small{display:block;color:var(--muted);font-size:10.5px;text-transform:capitalize}
  .dits .nodate{color:#D6492F;font-weight:600;font-size:12px}
  .dits .ver{font-size:12.5px;font-weight:600;color:var(--sky);text-decoration:none}
  .dits td.pax{font-family:'Space Grotesk';font-weight:600}
  .dits .note{padding:13px 22px;font-size:12px;color:var(--muted);background:#FBFCFD}
  .dits .empty-row td{text-align:center;color:var(--muted);padding:30px 14px}

  @media (max-width:880px){
    .dits .flight{grid-template-columns:auto 1fr;gap:6px 14px;padding:18px 0}
    .dits .f-time{grid-column:1;grid-row:1}
    .dits .flight>div:nth-child(2){grid-column:2;grid-row:1}
    .dits .seats{grid-column:1/-1;margin-top:6px;grid-template-columns:repeat(11,14px);grid-auto-rows:14px}
    .dits .seat{width:14px;height:14px}
    .dits .f-occ{grid-column:1/-1;text-align:left;min-width:0;display:flex;align-items:center;gap:8px 14px;flex-wrap:wrap;margin-top:2px}
    .dits .f-occ .pct{font-size:22px}
    .dits .f-occ .pax{margin-top:0}
    .dits .f-occ .chip{margin-top:0}
    .dits table{font-size:12.5px}
  }
  @media (max-width:640px){
    .dits{font-size:14px}
    .dits .topbar{align-items:stretch}
    .dits .topbar h1{font-size:24px}
    .dits .wind{width:100%;justify-content:space-around}
    .dits .board{padding:20px 16px 18px}
    .dits .kpi .val{font-size:23px}
    .dits .alert .n{font-size:23px}
    .dits .panel{overflow-x:auto;-webkit-overflow-scrolling:touch}
    .dits table{min-width:560px}
  }
</style>

  {{-- BARRA SUPERIOR --}}
  <div class="topbar">
    <div>
      <h1>Escritorio</h1>
      <div class="date">{{ $todayLabel }} · Downtown Punta Cana</div>
    </div>
    <div class="wind">
      <div>
        <div class="val mono">{{ $weather['available'] ? $weather['wind'] : '—' }} <span style="font-size:13px;font-weight:500">km/h</span></div>
        <div class="lbl">
          @if(!$weather['available'])
            <span class="limit">Clima no disponible</span><br>
          @elseif($weather['operational'])
            <span class="ok">✓ Viento operativo</span><br>
          @else
            <span style="color:#e07a5f;font-weight:600">⚠ Viento sobre el límite</span><br>
          @endif
          <span class="limit">Límite operativo: {{ $weather['wind_limit'] }} km/h</span>
        </div>
      </div>
      <div style="width:1px;height:36px;background:var(--line)"></div>
      <div>
        <div class="val">{{ $weather['available'] ? $weather['temp'].'°' : '—' }}</div>
        <div class="lbl">{{ $weather['condition'] }}<br><span class="limit">{{ $weather['rain'] ? 'Posible lluvia hoy' : 'Sin lluvia prevista hoy' }}</span></div>
      </div>
    </div>
  </div>

  {{-- TABLERO DE RESERVAS DE HOY --}}
  <section class="board">
    <div class="board-head">
      <h2>✈ Reservas de hoy</h2>
      <div class="sum">Ocupación del día: <b>{{ $dayPct }}%</b> · {{ $daySold }} de {{ $dayCap }} asientos vendidos · ingreso proyectado <b>{{ $fmt($dayRevenue) }}</b></div>
    </div>

    @forelse ($flights as $f)
      <div class="flight">
        <div class="f-time">{{ $f['time'] }}<small>{{ $f['slot'] }}</small></div>
        <div>
          <div class="f-name">{{ $f['name'] }}</div>
          <div class="f-tags">
            @if ($f['unpaid'] > 0)
              <span class="tag warn">⚠ {{ $f['unpaid'] }} asientos sin pagar</span>
            @else
              <span class="tag">Sin requerimientos especiales</span>
            @endif
          </div>
        </div>
        <div class="seats">
          @for ($i = 0; $i < $f['cap']; $i++)
            <div class="seat {{ $i < $f['sold'] ? 'sold' : '' }}" title="Asiento {{ $i + 1 }} · {{ $i < $f['sold'] ? 'vendido' : 'libre' }}"></div>
          @endfor
        </div>
        <div class="f-occ">
          <div class="pct">{{ $f['pct'] }}%</div>
          <div class="pax">{{ $f['sold'] }} / {{ $f['cap'] }} pax</div>
          @if ($f['pct'] >= 100)
            <span class="chip full">COMPLETO ✓</span>
          @elseif ($f['pct'] < 50)
            <span class="chip risk">{{ $f['cap'] - $f['sold'] }} LIBRES · ACTIVAR PROMO</span>
          @else
            <span class="chip sale">EN VENTA · {{ $f['cap'] - $f['sold'] }} LIBRES</span>
          @endif
        </div>
      </div>
    @empty
      <div class="board-empty">No hay reservas programadas para hoy.</div>
    @endforelse
  </section>

  {{-- ALERTAS ACCIONABLES --}}
  <section class="alerts">
    <div class="alert">
      <div class="n">{{ $alertNoDate['count'] }}</div>
      <div class="t">Reservas pagadas sin fecha</div>
      <div class="d">{{ $fmt($alertNoDate['sum']) }} cobrados sin asiento asignado. No reciben recordatorios ni entran en cocina.</div>
      <a class="act" href="{{ $alertNoDate['url'] }}">Asignar fecha →</a>
    </div>
    <div class="alert gold">
      <div class="n">{{ $alertPendingSoon['count'] }}</div>
      <div class="t">Pagos pendientes en reservas &lt;72h</div>
      <div class="d">Riesgo de no-show. Enviar link de pago por WhatsApp antes de liberar el asiento.</div>
      <a class="act" href="{{ $alertPendingSoon['url'] }}">Cobrar ahora →</a>
    </div>
    {{-- Reseñas por solicitar — pendiente de integración de envío, oculto de momento
    <div class="alert teal">
      <div class="n">{{ $alertReviews }}</div>
      <div class="t">Reseñas por solicitar</div>
      <div class="d">Clientes que vivieron la experiencia ayer y la completaron.</div>
      <a class="act" href="#">Enviar solicitud →</a>
    </div>
    --}}
  </section>

  {{-- KPIs COMERCIALES --}}
  <section class="kpis">
    <div class="kpi">
      <div class="lbl">Ingresos · {{ $monthName }}</div>
      <div class="val">{{ $fmt($revMonth) }}</div>
      <div class="sub">
        @if ($revDelta === null)
          <span class="up">—</span> sin mes previo
        @elseif ($revDelta >= 0)
          <span class="up">▲ {{ $revDelta }}%</span> vs mes anterior
        @else
          <span class="down">▼ {{ abs($revDelta) }}%</span> vs mes anterior
        @endif
      </div>
    </div>
    <div class="kpi">
      <div class="lbl">Ocupación · últimos 7 días</div>
      <div class="val">{{ $occ7 }}%</div>
      <div class="sub">promedio de asientos vendidos</div>
    </div>
    <div class="kpi">
      <div class="lbl">Mix de canales · {{ $monthName }}</div>
      <div class="val" style="font-size:20px">{{ $mix['web'] }}% directo</div>
      <div class="mixbar">
        <i style="width:{{ $mix['web'] }}%;background:var(--sky)"></i>
        <i style="width:{{ $mix['viator'] }}%;background:var(--viator)"></i>
        <i style="width:{{ $mix['hoteles'] }}%;background:var(--gold)"></i>
      </div>
      <div class="legend">
        <span><i class="dot" style="background:var(--sky)"></i>Web {{ $mix['web'] }}%</span>
        <span><i class="dot" style="background:var(--viator)"></i>Viator {{ $mix['viator'] }}%</span>
        <span><i class="dot" style="background:var(--gold)"></i>Hoteles {{ $mix['hoteles'] }}%</span>
      </div>
    </div>
    <div class="kpi">
      <div class="lbl">Attach rate · packs premium</div>
      <div class="val">{{ $attach }}%</div>
      <div class="sub">Meta: 20% · sobre reservas del mes</div>
    </div>
  </section>

  {{-- ORDENES QUE REQUIEREN ATENCION --}}
  <section class="panel" x-data="{ f: 'all' }">
    <div class="panel-head">
      <div>
        <h3>Atención requerida · próximos 7 días</h3>
        <p>Solo órdenes accionables. El histórico completo vive en Órdenes.</p>
      </div>
      <div class="filters">
        <button class="fbtn" :class="{ on: f==='all' }" @click="f='all'">Todas ({{ $attention->count() }})</button>
        <button class="fbtn" :class="{ on: f==='unpaid' }" @click="f='unpaid'">Sin pagar ({{ $pendingTotal }})</button>
        <button class="fbtn" :class="{ on: f==='nodate' }" @click="f='nodate'">Sin fecha ({{ $noDateTotal }})</button>
        <button class="fbtn" :class="{ on: f==='viator' }" @click="f='viator'">Viator ({{ $viatorTotal }})</button>
      </div>
    </div>
    <table>
      <thead>
        <tr>
          <th>Reserva</th>
          <th>Cliente</th>
          <th>Pax</th>
          <th>Experiencia</th>
          <th>Canal</th>
          <th>Pago</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse ($attention as $row)
          <tr
            x-show="f==='all'
              || (f==='unpaid' && {{ $row['paid'] ? 'false' : 'true' }})
              || (f==='nodate' && {{ $row['date'] ? 'false' : 'true' }})
              || (f==='viator' && {{ $row['channel'] === 'viator' ? 'true' : 'false' }})"
          >
            <td>
              @if ($row['date'])
                <div class="slot">{{ $row['date']->isToday() ? 'HOY' : $row['date']->format('d/m') }} {{ $row['date']->format('H:i') }}<small>{{ ucfirst(strtolower($row['slot'])) }}</small></div>
              @else
                <span class="nodate">⚠ SIN FECHA</span>
              @endif
            </td>
            <td>
              <div class="cl-name">{{ $row['name'] }}</div>
              <div class="cl-sub">#{{ $row['id'] }} · {{ $row['contact'] }}</div>
            </td>
            <td class="pax">{{ $row['pax'] }}</td>
            <td>{{ $row['product'] }}</td>
            <td>
              @if ($row['channel'] === 'viator')
                <span class="b b-via">VIATOR</span>
              @elseif ($row['channel'] === 'hoteles')
                <span class="b b-hot">HOTELES</span>
              @else
                <span class="b b-dir">WEB</span>
              @endif
            </td>
            <td>
              @if ($row['paid'])
                <span class="b b-paid">PAGADO {{ $fmt($row['total']) }}</span>
              @else
                <span class="b b-pend">PENDIENTE {{ $fmt($row['total']) }}</span>
              @endif
            </td>
            <td><a class="ver" href="{{ $row['url'] }}">Ver →</a></td>
          </tr>
        @empty
          <tr class="empty-row"><td colspan="7">No hay órdenes que requieran atención en los próximos 7 días.</td></tr>
        @endforelse
      </tbody>
    </table>
    <div class="note">Cancelar una reserva ahora se hace desde el detalle de la orden, con motivo obligatorio y confirmación — evita cancelaciones por misclick.</div>
  </section>
</div>
</x-filament-panels::page>
