@php
    use Illuminate\Support\Str;
    use Carbon\Carbon;
    Carbon::setLocale('es');
    $money = fn ($n) => '$' . number_format((float) $n, 0);
    $CHB = [
        'web' => '<span class="b b-web">WEB</span>', 'via' => '<span class="b b-via">VIATOR</span>',
        'ig' => '<span class="b b-ig">INSTAGRAM</span>', 'hot' => '<span class="b b-hot">HOTEL</span>',
    ];
    $SEGB = [
        'vip' => '<span class="b b-vip">⭐ VIP</span>', 'rep' => '<span class="b b-rep">REPETIDOR</span>',
        'new' => '<span class="b b-new">NUEVO</span>', 'ina' => '<span class="b b-ina">INACTIVO</span>',
        'reg' => '<span class="b b-ina" style="background:#EBF1FF;color:#3D7BFA">CLIENTE</span>',
    ];
    $segCounts = $this->segmentCounts();
@endphp

<x-filament-panels::page>
<div class="rp">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">

<style>
  .rp{
    --ink:#0E1526; --ink-2:#172139; --paper:#F6F7F9; --card:#FFFFFF; --line:#E6E9EF;
    --text:#1B2434; --muted:#69748A; --gold:#E8B544; --gold-deep:#C28F1E; --coral:#FF6B57;
    --coral-deep:#D6492F; --teal:#1FA98C; --sky:#3D7BFA; --viator:#7B5CD6; --insta:#D6308A; --r:14px;
    font-family:'Inter',sans-serif; color:var(--text); -webkit-font-smoothing:antialiased; font-size:15px;
  }
  .rp *{box-sizing:border-box}
  .rp .mono{font-family:'IBM Plex Mono',monospace}
  .rp h1{font-family:'Space Grotesk';font-size:26px;font-weight:700;letter-spacing:-.02em;margin:0}
  .rp .crumb{font-size:12.5px;color:var(--muted);margin-bottom:6px}

  .rp .repnav{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:12px;margin:18px 0 20px}
  .rp .rn{background:var(--card);border:1.5px solid var(--line);border-radius:var(--r);padding:15px 17px;cursor:pointer;text-align:left}
  .rp .rn:hover{border-color:#C9D2E2}
  .rp .rn.on{border-color:var(--ink);background:var(--ink);color:#fff}
  .rp .rn .ic{font-size:20px}
  .rp .rn b{font-family:'Space Grotesk';font-size:13.5px;display:block;margin-top:6px}
  .rp .rn small{font-size:11px;color:var(--muted);display:block;margin-top:3px;line-height:1.45}
  .rp .rn.on small{color:#9FB0CC}
  .rp .rn.lock{border-style:dashed}
  .rp .rn .lk{float:right;font-size:13px}

  .rp .xbar{background:var(--card);border:1px solid var(--line);border-radius:var(--r);padding:14px 18px;display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:16px}
  .rp .xbar .left{display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap}
  .rp .xbar label{font-size:11.5px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px}
  .rp .xbar input{border:1px solid var(--line);border-radius:9px;padding:8px 10px;font-size:13px;font-family:'Inter'}
  .rp .xbtns{display:flex;gap:8px;flex-wrap:wrap}
  .rp .xb{font-size:12px;font-weight:700;padding:9px 15px;border-radius:9px;border:1px solid var(--line);background:var(--paper);cursor:pointer;display:flex;align-items:center;gap:6px}
  .rp .xb.csv{color:var(--teal)}

  .rp .kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:13px;margin-bottom:16px}
  .rp .kpi{background:var(--card);border:1px solid var(--line);border-radius:var(--r);padding:16px 18px}
  .rp .kpi .l{font-size:11.5px;color:var(--muted);font-weight:600}
  .rp .kpi .v{font-family:'Space Grotesk';font-size:26px;font-weight:700;margin-top:5px}
  .rp .kpi .s{font-size:11.5px;color:var(--muted);margin-top:4px}
  .rp .up{color:var(--teal);font-weight:700}.rp .warn{color:var(--coral-deep);font-weight:700}

  .rp .segs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px}
  .rp .seg{font-size:12px;font-weight:600;padding:8px 14px;border-radius:99px;border:1px solid var(--line);background:var(--card);color:var(--muted);cursor:pointer}
  .rp .seg.on{background:var(--ink);color:#fff;border-color:var(--ink)}
  .rp .seg em{font-style:normal;opacity:.7;margin-left:4px}

  .rp .panel{background:var(--card);border:1px solid var(--line);border-radius:var(--r);overflow:auto}
  .rp table{width:100%;border-collapse:collapse;font-size:13px}
  .rp th{font-size:10.5px;letter-spacing:.07em;text-transform:uppercase;color:var(--muted);font-weight:600;text-align:left;padding:11px 12px;border-bottom:1px solid var(--line);background:#FBFCFD;white-space:nowrap}
  .rp td{padding:12px;border-bottom:1px solid var(--line);vertical-align:middle;white-space:nowrap}
  .rp tbody tr:hover td{background:#FAFBFD}
  .rp .cl b{font-weight:600;display:block}.rp .cl small{color:var(--muted);font-size:11px}
  .rp .b{display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:700;padding:3px 9px;border-radius:99px}
  .rp .b-web{background:#EBF1FF;color:var(--sky)} .rp .b-via{background:#F1EDFB;color:var(--viator)}
  .rp .b-ig{background:#FCEAF4;color:var(--insta)} .rp .b-hot{background:#FBF3E0;color:var(--gold-deep)}
  .rp .b-vip{background:var(--ink);color:var(--gold)} .rp .b-rep{background:#E7F6F1;color:var(--teal)}
  .rp .b-new{background:#EBF1FF;color:var(--sky)} .rp .b-ina{background:#F1F3F7;color:var(--muted)}
  .rp .gasto{font-family:'Space Grotesk';font-weight:700}
  .rp .ver{font-size:11.5px;font-weight:700;color:var(--sky);background:#EBF1FF;border:none;border-radius:8px;padding:6px 12px;cursor:pointer}
  .rp .foot{display:flex;justify-content:space-between;padding:13px 18px;font-size:12px;color:var(--muted);flex-wrap:wrap;gap:8px}

  .rp .chbar{display:flex;height:26px;border-radius:9px;overflow:hidden;margin-top:10px}
  .rp .chbar i{display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:700}
  .rp .legend{display:flex;gap:14px;font-size:11.5px;color:var(--muted);margin-top:8px;flex-wrap:wrap}
  .rp .dot{width:8px;height:8px;border-radius:99px;display:inline-block;margin-right:5px}

  .rp .gate{background:var(--card);border:1.5px dashed var(--line);border-radius:18px;padding:50px 30px;text-align:center}
  .rp .gate .ic{font-size:40px}
  .rp .gate h2{font-family:'Space Grotesk';font-size:20px;margin-top:12px}
  .rp .gate p{font-size:13px;color:var(--muted);margin-top:8px;line-height:1.7;max-width:480px;margin:8px auto 0}
  .rp .gate button{margin-top:18px;font-family:'Space Grotesk';font-weight:600;font-size:14px;background:var(--ink);color:#fff;border:none;border-radius:11px;padding:13px 28px;cursor:pointer}
  .rp .gerhead{background:linear-gradient(150deg,var(--ink),var(--ink-2));border-radius:18px;color:#fff;padding:22px 26px;margin-bottom:16px}
  .rp .gerhead .t{font-family:'Space Grotesk';font-size:18px;font-weight:700}
  .rp .gerhead .t small{display:block;font-size:12px;color:#9FB0CC;font-family:'Inter';font-weight:500;margin-top:3px}

  /* modal cliente */
  .rp .ovl{position:fixed;inset:0;background:rgba(14,21,38,.55);display:flex;align-items:center;justify-content:center;z-index:90;padding:20px}
  .rp .modal{background:var(--card);border-radius:18px;width:640px;max-width:100%;max-height:92vh;overflow:auto}
  .rp .m-h{background:linear-gradient(135deg,var(--ink),var(--ink-2));color:#fff;padding:22px 26px;position:relative}
  .rp .m-h .nm{font-family:'Space Grotesk';font-size:20px;font-weight:700}
  .rp .m-h .em{font-size:12.5px;color:#9FB0CC;margin-top:4px}
  .rp .m-h .x{position:absolute;top:16px;right:18px;background:rgba(255,255,255,.12);border:none;color:#fff;width:30px;height:30px;border-radius:99px;cursor:pointer}
  .rp .m-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:16px}
  .rp .ms{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.14);border-radius:11px;padding:10px 13px}
  .rp .ms .l{font-size:10px;letter-spacing:.1em;color:#8E9FBE;font-weight:700}
  .rp .ms .v{font-family:'Space Grotesk';font-size:17px;font-weight:700;margin-top:3px}
  .rp .m-b{padding:20px 26px}
  .rp .m-b h4{font-family:'Space Grotesk';font-size:13.5px;font-weight:700;margin:14px 0 9px}
  .rp .m-b h4:first-child{margin-top:0}
  .rp .hist{border:1px solid var(--line);border-radius:11px;padding:11px 14px;display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;font-size:12.5px;gap:10px;flex-wrap:wrap}
  .rp .hist small{color:var(--muted);display:block;margin-top:2px}
  .rp .pref{display:flex;gap:8px;flex-wrap:wrap}
  .rp .pf{font-size:11.5px;font-weight:600;background:var(--paper);border:1px solid var(--line);border-radius:99px;padding:5px 12px}
  .rp .empty{padding:30px;text-align:center;color:var(--muted)}
</style>

  <div class="crumb">Reportes</div>
  <h1>Centro de Reportes</h1>

  {{-- selector --}}
  <div class="repnav">
    <button class="rn {{ $report==='clientes'?'on':'' }}" wire:click="setReport('clientes')"><span class="ic">👥</span><b>Clientes y remarketing</b><small>Segmentos, repetidores, ocasiones</small></button>
    <button class="rn {{ $report==='canales'?'on':'' }}" wire:click="setReport('canales')"><span class="ic">📣</span><b>Ventas por canal</b><small>Web vs Viator vs Instagram vs hoteles</small></button>
    <button class="rn lock {{ $report==='gerencia'?'on':'' }}" wire:click="setReport('gerencia')"><span class="lk">🔒</span><span class="ic">💼</span><b>Gerencia</b><small>Facturación, comisiones y márgenes — solo admin</small></button>
  </div>

  {{-- toolbar export --}}
  <div class="xbar">
    <div class="left">
      <div><label>Desde</label><input type="date" wire:model.live="from"></div>
      <div><label>Hasta</label><input type="date" wire:model.live="to"></div>
    </div>
    <div class="xbtns">
      <button class="xb csv" wire:click="exportCsv">⬇ CSV</button>
    </div>
  </div>

  {{-- ================= CLIENTES ================= --}}
  @if ($report === 'clientes')
    @php $st = $this->getClientStats(); @endphp
    <div class="kpis">
      <div class="kpi"><div class="l">Total clientes</div><div class="v">{{ $st['total'] }}</div><div class="s"><span class="up">+{{ $st['new'] }}</span> nuevos 30d</div></div>
      <div class="kpi"><div class="l">Repetidores</div><div class="v">{{ $st['rep'] }}%</div><div class="s">{{ $st['repCount'] }} con 2+ visitas</div></div>
      <div class="kpi"><div class="l">Ticket promedio</div><div class="v">{{ $money($st['ticket']) }}</div><div class="s">por reserva</div></div>
      <div class="kpi"><div class="l">⭐ Clientes VIP</div><div class="v">{{ $st['vip'] }}</div><div class="s"><span class="up">oro para remarketing</span></div></div>
      <div class="kpi"><div class="l">Inactivos +6m</div><div class="v">{{ $st['ina'] }}</div><div class="s">campaña de reactivación</div></div>
    </div>

    <div class="segs">
      <button class="seg {{ $segment==='all'?'on':'' }}" wire:click="setSegment('all')">Todos <em>({{ $segCounts['all'] }})</em></button>
      <button class="seg {{ $segment==='vip'?'on':'' }}" wire:click="setSegment('vip')">⭐ VIP <em>({{ $segCounts['vip'] }})</em></button>
      <button class="seg {{ $segment==='rep'?'on':'' }}" wire:click="setSegment('rep')">Repetidores <em>({{ $segCounts['rep'] }})</em></button>
      <button class="seg {{ $segment==='new'?'on':'' }}" wire:click="setSegment('new')">Nuevos 30d <em>({{ $segCounts['new'] }})</em></button>
      <button class="seg {{ $segment==='ina'?'on':'' }}" wire:click="setSegment('ina')">Inactivos +6m <em>({{ $segCounts['ina'] }})</em></button>
    </div>

    <div style="margin-bottom:12px">
      <input type="text" wire:model.live.debounce.400ms="search" placeholder="🔎 Buscar cliente por nombre o email" style="width:100%;max-width:360px;border:1px solid var(--line);border-radius:10px;padding:10px 13px;font-size:13px">
    </div>

    @php $customers = $this->getCustomers(); @endphp
    <div class="panel">
      <table>
        <thead><tr><th>Cliente</th><th>Canal origen</th><th>Visitas</th><th>Última visita</th><th>Gasto total</th><th>Segmento</th><th></th></tr></thead>
        <tbody>
          @forelse ($customers as $c)
            <tr>
              <td class="cl"><b>{{ $this->cleanName($c->customer_name) }}</b><small>{{ $c->customer_email }}</small></td>
              <td>{!! $CHB[$this->channelOf($c->customer_name)] !!}</td>
              <td style="text-align:center;font-family:'Space Grotesk';font-weight:700">{{ $c->total_orders }}</td>
              <td class="mono" style="font-size:12px">{{ $c->last_visit ? Carbon::parse($c->last_visit)->format('d/m/Y') : '—' }}</td>
              <td class="gasto">{{ $money($c->total_amount) }}</td>
              <td>{!! $SEGB[$this->segmentOf($c)] !!}</td>
              <td><button class="ver" wire:click="viewCustomer('{{ $c->customer_email }}')">Ver</button></td>
            </tr>
          @empty
            <tr><td colspan="7" class="empty">No hay clientes en este segmento.</td></tr>
          @endforelse
        </tbody>
      </table>
      <div class="foot"><span>Mostrando {{ $customers->count() }} de {{ $segCounts['all'] }} clientes · el export respeta el segmento activo</span></div>
    </div>

    {{-- modal cliente --}}
    @php $sel = $this->getSelectedCustomer(); @endphp
    @if ($sel)
      <div class="ovl" wire:click.self="closeCustomer">
        <div class="modal">
          <div class="m-h">
            <button class="x" wire:click="closeCustomer">✕</button>
            <div class="nm">{{ $this->cleanName($sel->customer_name) }}</div>
            <div class="em">{{ $sel->customer_email }} · canal {{ Str::upper($this->channelOf($sel->customer_name)) }}</div>
            <div class="m-stats">
              <div class="ms"><div class="l">VISITAS</div><div class="v">{{ $sel->total_orders }}</div></div>
              <div class="ms"><div class="l">GASTO TOTAL</div><div class="v">{{ $money($sel->total_amount) }}</div></div>
              <div class="ms"><div class="l">TICKET PROM.</div><div class="v">{{ $money($sel->total_amount / max(1,$sel->total_orders)) }}</div></div>
            </div>
          </div>
          <div class="m-b">
            <h4>Historial de reservas</h4>
            @foreach ($this->getSelectedOrders() as $o)
              <div class="hist">
                <div><b>#{{ $o->woocommerce_order_id ?: $o->id }}</b><small>{{ $o->booking_start ? $o->booking_start->format('d/m/Y') : 'sin fecha' }} · {{ $o->product?->name ?? '—' }}</small></div>
                <div style="text-align:right"><b>{{ $money($o->total) }}</b><small>{{ ucfirst($o->status) }}</small></div>
              </div>
            @endforeach
            <h4>Preferencias detectadas</h4>
            <div class="pref">
              @forelse ($this->getSelectedPreferences() as $p)<span class="pf">{{ $p }}</span>@empty <span class="pf" style="color:var(--muted)">Sin preferencias registradas</span> @endforelse
            </div>
          </div>
        </div>
      </div>
    @endif
  @endif

  {{-- ================= CANALES ================= --}}
  @if ($report === 'canales')
    @php $ch = $this->getChannelReport(); @endphp
    <div class="kpis">
      <div class="kpi"><div class="l">Ventas del período</div><div class="v">{{ $ch['sales'] }}</div><div class="s">reservas no canceladas</div></div>
      <div class="kpi"><div class="l">Mejor canal</div><div class="v">Web {{ $ch['mix']['web'] }}%</div><div class="s">{{ $ch['chan']['web'] }} ventas directas</div></div>
      <div class="kpi"><div class="l">Dependencia OTA</div><div class="v warn">{{ $ch['ota'] }}%</div><div class="s">objetivo: bajar a 25%</div></div>
      <div class="kpi"><div class="l">Vía Instagram</div><div class="v">{{ $ch['chan']['ig'] }}</div><div class="s">{{ $ch['mix']['ig'] }}% del mix</div></div>
    </div>
    <div class="panel" style="padding:20px 22px;overflow:visible">
      <b style="font-family:'Space Grotesk'">Mix de canales (ventas)</b>
      <div class="chbar">
        @if ($ch['mix']['web'])<i style="width:{{ $ch['mix']['web'] }}%;background:var(--sky)">WEB {{ $ch['mix']['web'] }}%</i>@endif
        @if ($ch['mix']['via'])<i style="width:{{ $ch['mix']['via'] }}%;background:var(--viator)">VIATOR {{ $ch['mix']['via'] }}%</i>@endif
        @if ($ch['mix']['ig'])<i style="width:{{ $ch['mix']['ig'] }}%;background:var(--insta)">IG {{ $ch['mix']['ig'] }}%</i>@endif
        @if ($ch['mix']['hot'])<i style="width:{{ $ch['mix']['hot'] }}%;background:var(--gold)">HOT</i>@endif
      </div>
      <div class="legend">
        <span><i class="dot" style="background:var(--sky)"></i>Web directa · {{ $ch['chan']['web'] }} ventas · sin comisión</span>
        <span><i class="dot" style="background:var(--viator)"></i>Viator · {{ $ch['chan']['via'] }} ventas · <b class="warn">comisión en Gerencia 🔒</b></span>
        <span><i class="dot" style="background:var(--insta)"></i>Instagram · {{ $ch['chan']['ig'] }}</span>
        <span><i class="dot" style="background:var(--gold)"></i>Hoteles · {{ $ch['chan']['hot'] }}</span>
      </div>
      <p style="font-size:12.5px;color:var(--muted);margin-top:14px;line-height:1.7">Este reporte muestra <b>volumen y mix</b> para el equipo comercial. Los montos de comisión y facturación por canal se consultan en el reporte de Gerencia.</p>
    </div>
  @endif

  {{-- ================= GERENCIA ================= --}}
  @if ($report === 'gerencia')
    @if (! $this->isAdmin() && ! $gerenciaUnlocked)
      <div class="gate">
        <div class="ic">🔒</div>
        <h2>Reporte de Gerencia</h2>
        <p>Facturación total, ingresos por canal con comisiones, descuentos otorgados y merma valorada. Acceso restringido por rol — operación y cocina no ven esta sección.</p>
        <button wire:click="unlockGerencia">Acceder como administrador</button>
      </div>
    @else
      @php $g = $this->getGerencia(); @endphp
      <div class="gerhead">
        <div class="t">💼 Gerencia — Financiero<small>{{ Carbon::parse($from)->format('d/m') }} – {{ Carbon::parse($to)->format('d/m/Y') }} · visible solo para rol Admin</small></div>
      </div>
      <div class="kpis">
        <div class="kpi"><div class="l">Facturación total</div><div class="v">{{ $money($g['total']) }}</div><div class="s">período seleccionado</div></div>
        <div class="kpi"><div class="l">Comisiones OTA estimadas</div><div class="v warn">{{ $money($g['commission']) }}</div><div class="s">Viator · {{ $g['commissionPct'] }}% de la facturación</div></div>
        <div class="kpi"><div class="l">Descuentos otorgados</div><div class="v">{{ $money($g['discounts']) }}</div><div class="s">autorizaciones del período</div></div>
        <div class="kpi"><div class="l">Merma de cocina</div><div class="v warn">{{ $money($g['merma']) }}</div><div class="s">ver módulo Cocina</div></div>
      </div>
      <div class="panel" style="padding:18px 22px;overflow:visible">
        <b style="font-family:'Space Grotesk'">Facturación por canal</b>
        @php $rev = $g['revByChannel']; $tot = max(1, array_sum($rev)); @endphp
        <div class="chbar">
          @foreach (['web'=>'var(--sky)','via'=>'var(--viator)','ig'=>'var(--insta)','hot'=>'var(--gold)'] as $k=>$col)
            @if ($rev[$k] > 0)<i style="width:{{ round($rev[$k]/$tot*100) }}%;background:{{ $col }}">{{ $money($rev[$k]) }}</i>@endif
          @endforeach
        </div>
        <p style="font-size:12.5px;color:var(--muted);margin-top:12px;line-height:1.7">💡 Cada punto que migra de Viator a venta directa ahorra ~25% de comisión.</p>
      </div>
    @endif
  @endif
</div>
</x-filament-panels::page>
