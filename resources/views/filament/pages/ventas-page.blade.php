@php
    use Illuminate\Support\Str;
    use Carbon\Carbon;
    Carbon::setLocale('es');
    $money = fn ($n) => '$' . number_format((float) $n, 2);
    $counts = $this->getCounts();

    $CHB = [
        'web' => '<span class="b b-web">WEB</span>',
        'viator' => '<span class="b b-via">VIATOR</span>',
        'ig' => '<span class="b b-ig">INSTAGRAM</span>',
        'hotel' => '<span class="b b-hotel">HOTEL</span>',
    ];
    $PAYB = [
        'paid' => '<span class="b b-paid">PAGADO</span>',
        'pend' => '<span class="b b-pend">SIN PAGAR</span>',
    ];
    $STB = [
        'conf' => '<span class="b b-conf">CONFIRMADA</span>',
        'proc' => '<span class="b b-proc">EN PROCESO</span>',
        'vol' => '<span class="b b-vol">VOLADA</span>',
        'can' => '<span class="b b-can">CANCELADA</span>',
    ];
@endphp

<x-filament-panels::page>
<div class="vt">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">

<style>
  .vt{
    --ink:#0E1526; --ink-2:#172139; --paper:#F6F7F9; --card:#FFFFFF; --line:#E6E9EF;
    --text:#1B2434; --muted:#69748A; --gold:#E8B544; --gold-deep:#C28F1E; --coral:#FF6B57;
    --coral-deep:#D6492F; --teal:#1FA98C; --sky:#3D7BFA; --viator:#7B5CD6; --insta:#D6308A; --r:14px;
    font-family:'Inter',sans-serif; color:var(--text); -webkit-font-smoothing:antialiased; font-size:15px;
  }
  .vt *{box-sizing:border-box}
  .vt .mono{font-family:'IBM Plex Mono',monospace}
  .vt h1{font-family:'Space Grotesk';font-size:28px;font-weight:700;letter-spacing:-.02em;margin:0}
  .vt .crumb{font-size:12.5px;color:var(--muted);margin-bottom:6px}
  .vt .crumb a{color:var(--sky);text-decoration:none;cursor:pointer}
  .vt .topbar{display:flex;justify-content:space-between;align-items:flex-end;gap:14px;flex-wrap:wrap;margin-bottom:20px}
  .vt .newbtn{font-family:'Space Grotesk';font-size:14px;font-weight:600;background:var(--ink);color:#fff;border:none;border-radius:11px;padding:12px 22px;cursor:pointer}

  .vt .b{display:inline-flex;align-items:center;gap:4px;font-size:10.5px;font-weight:600;padding:3px 9px;border-radius:99px;white-space:nowrap}
  .vt .b-web{background:#EBF1FF;color:var(--sky)} .vt .b-via{background:#F1EDFB;color:var(--viator)}
  .vt .b-ig{background:#FCEAF4;color:var(--insta)} .vt .b-hotel{background:#FBF3E0;color:var(--gold-deep)}
  .vt .b-paid{background:#E7F6F1;color:var(--teal)} .vt .b-pend{background:#FFF0ED;color:var(--coral-deep)}
  .vt .b-dep{background:#FBF3E0;color:var(--gold-deep)}
  .vt .b-conf{background:#E7F6F1;color:var(--teal)} .vt .b-proc{background:#FBF3E0;color:var(--gold-deep)}
  .vt .b-vol{background:#EBF1FF;color:var(--sky)} .vt .b-can{background:#F1F3F7;color:var(--muted)}

  .vt .panel{background:var(--card);border:1px solid var(--line);border-radius:var(--r);overflow:hidden}
  .vt .toolbar{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:16px 20px;border-bottom:1px solid var(--line);flex-wrap:wrap}
  .vt .chips{display:flex;gap:8px;flex-wrap:wrap}
  .vt .chip{font-size:12px;font-weight:600;padding:7px 13px;border-radius:99px;border:1px solid var(--line);background:var(--paper);color:var(--muted);cursor:pointer}
  .vt .chip.on{background:var(--ink);color:#fff;border-color:var(--ink)}
  .vt .search{display:flex;align-items:center;gap:8px;border:1px solid var(--line);border-radius:10px;padding:9px 13px;font-size:13px;color:var(--muted);min-width:230px}
  .vt .search input{border:none;outline:none;font-size:13px;width:100%;font-family:'Inter';background:transparent}
  .vt [x-cloak]{display:none!important}
  .vt .msel-wrap{display:inline-flex;align-items:center;gap:5px}
  .vt .msel-nav{width:36px;height:40px;border:1px solid var(--line);background:var(--card);border-radius:11px;cursor:pointer;color:var(--muted);font-size:17px;line-height:1;display:flex;align-items:center;justify-content:center;transition:transform .15s,border-color .15s,color .15s}
  .vt .msel-nav:hover{color:var(--ink);border-color:var(--ink);transform:translateY(-1px)}
  .vt .msel-nav:active{transform:translateY(0)}
  .vt .msel{position:relative}
  .vt .msel-trigger{display:inline-flex;align-items:center;gap:9px;height:40px;padding:0 16px;min-width:168px;justify-content:center;border:1px solid var(--line);background:var(--card);border-radius:11px;cursor:pointer;font-family:'Space Grotesk';font-size:13.5px;font-weight:600;color:var(--text);transition:border-color .15s,box-shadow .15s}
  .vt .msel-trigger:hover{border-color:var(--ink)}
  .vt .msel-trigger.active{border-color:var(--ink);box-shadow:0 0 0 3px rgba(14,21,38,.07)}
  .vt .msel-trigger .ic{font-size:14px;line-height:1}
  .vt .msel-trigger .chev{margin-left:2px;font-size:10px;color:var(--muted);transition:transform .22s ease}
  .vt .msel-trigger .chev.up{transform:rotate(180deg)}
  .vt .msel-pop{position:fixed;z-index:70;width:266px;background:var(--card);border:1px solid var(--line);border-radius:16px;box-shadow:0 20px 48px rgba(14,21,38,.18);padding:15px}
  .vt .msel-yr{display:flex;align-items:center;justify-content:space-between;margin-bottom:13px}
  .vt .msel-yr .y{font-family:'Space Grotesk';font-weight:700;font-size:17px;letter-spacing:-.01em}
  .vt .msel-yr button{width:34px;height:34px;border:1px solid var(--line);background:var(--paper);border-radius:10px;cursor:pointer;color:var(--muted);font-size:16px;line-height:1;transition:transform .15s,border-color .15s,color .15s}
  .vt .msel-yr button:hover{color:var(--ink);border-color:var(--ink);transform:translateY(-1px)}
  .vt .msel-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:7px}
  .vt .msel-m{padding:10px 0;border:1.5px solid transparent;background:var(--paper);border-radius:10px;cursor:pointer;font-size:12.5px;font-weight:600;color:var(--text);transition:transform .13s,background .13s,color .13s,border-color .13s}
  .vt .msel-m:hover{background:#EAEEF5;transform:translateY(-1px)}
  .vt .msel-m.now{border-color:var(--gold)}
  .vt .msel-m.sel{background:var(--ink);color:#fff;border-color:var(--ink)}
  .vt .msel-m.sel:hover{background:var(--ink-2)}
  .vt .msel-all{width:100%;margin-top:13px;padding-top:12px;border:none;border-top:1px solid var(--line);background:transparent;color:var(--sky);font-size:12.5px;font-weight:600;cursor:pointer;transition:color .15s}
  .vt .msel-all:hover{color:var(--ink)}

  .vt table{width:100%;border-collapse:collapse;font-size:13.5px}
  .vt th{font-size:10.5px;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);font-weight:600;text-align:left;padding:11px 14px;border-bottom:1px solid var(--line);background:#FBFCFD}
  .vt td{padding:13px 14px;border-bottom:1px solid var(--line);vertical-align:middle}
  .vt tbody tr{cursor:pointer}
  .vt tbody tr:hover td{background:#FAFBFD}
  .vt .cl b{font-weight:600;display:block}
  .vt .cl small{color:var(--muted);font-size:11.5px}
  .vt .slot{font-family:'IBM Plex Mono';font-size:12.5px;font-weight:500}
  .vt .slot small{display:block;color:var(--muted);font-size:10.5px;font-family:'Inter'}
  .vt .nodate{color:var(--coral-deep);font-weight:600;font-size:11.5px}
  .vt .tot{font-family:'Space Grotesk';font-weight:600}
  .vt .pax{font-family:'Space Grotesk';font-weight:600;text-align:center}
  .vt .peek{font-size:11.5px;font-weight:600;color:var(--sky);background:#EBF1FF;border:none;border-radius:8px;padding:6px 11px;cursor:pointer}
  .vt .foot{display:flex;justify-content:space-between;align-items:center;padding:14px 20px;font-size:12.5px;color:var(--muted);flex-wrap:wrap;gap:10px}
  .vt .empty{padding:34px;text-align:center;color:var(--muted)}

  /* detalle */
  .vt .det-head{background:linear-gradient(135deg,var(--ink),var(--ink-2));border-radius:18px;color:#fff;padding:24px 28px;display:flex;justify-content:space-between;gap:20px;flex-wrap:wrap;margin-bottom:18px}
  .vt .det-head .nm{font-family:'Space Grotesk';font-size:22px;font-weight:700}
  .vt .det-head .mt{font-size:13px;color:#9FB0CC;margin-top:5px;line-height:1.7}
  .vt .det-head .tot{font-family:'Space Grotesk';font-size:30px;font-weight:700;color:var(--gold)}
  .vt .det-acts{display:flex;gap:9px;flex-wrap:wrap;margin-top:14px}
  .vt .abtn{font-size:12.5px;font-weight:600;padding:9px 15px;border-radius:10px;border:1px solid rgba(255,255,255,.22);background:rgba(255,255,255,.08);color:#fff;cursor:pointer;text-decoration:none;display:inline-block}
  .vt .abtn.wa{background:rgba(31,169,140,.25);border-color:rgba(31,169,140,.5)}
  .vt .abtn.danger{background:rgba(255,107,87,.18);border-color:rgba(255,107,87,.4);color:#FFAB9E}
  .vt .det-grid{display:grid;grid-template-columns:1.4fr 1fr;gap:16px}
  .vt .card{background:var(--card);border:1px solid var(--line);border-radius:var(--r);padding:18px 20px}
  .vt .card h3{font-family:'Space Grotesk';font-size:14px;font-weight:600;margin-bottom:12px}
  .vt .guest{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:10px 0;border-top:1px solid var(--line);font-size:13px;flex-wrap:wrap}
  .vt .guest:first-of-type{border-top:none}
  .vt .guest .gn b{font-weight:600}
  .vt .guest .gn small{display:block;color:var(--muted);font-size:11.5px;margin-top:2px}
  .vt .seatchip{font-family:'IBM Plex Mono';font-size:11px;background:var(--paper);border:1px solid var(--line);border-radius:7px;padding:3px 8px}
  .vt .pay-row{display:flex;justify-content:space-between;font-size:13px;padding:6px 0;color:var(--muted)}
  .vt .pay-row b{color:var(--text);font-weight:600}
  .vt .pay-row.total{border-top:1px solid var(--line);margin-top:6px;padding-top:11px;font-family:'Space Grotesk';font-size:16px}
  .vt .tl{position:relative;padding-left:20px;font-size:12.5px}
  .vt .tl::before{content:"";position:absolute;left:5px;top:6px;bottom:6px;width:2px;background:var(--line)}
  .vt .tl-i{position:relative;padding:7px 0 7px 6px;color:var(--muted)}
  .vt .tl-i::before{content:"";position:absolute;left:-19px;top:12px;width:10px;height:10px;border-radius:99px;background:var(--card);border:2.5px solid var(--sky)}
  .vt .tl-i b{color:var(--text);font-weight:600}

  /* crear */
  .vt .steps{display:flex;flex-direction:column;gap:16px;max-width:900px;margin-left:auto;margin-right:auto}
  .vt .step{background:var(--card);border:1px solid var(--line);border-radius:var(--r);padding:20px 22px}
  .vt .step h3{font-family:'Space Grotesk';font-size:15px;font-weight:600;display:flex;align-items:center;gap:10px;margin-bottom:6px}
  .vt .stn{width:24px;height:24px;border-radius:99px;background:var(--ink);color:#fff;font-size:12px;display:inline-flex;align-items:center;justify-content:center;font-family:'Space Grotesk'}
  .vt .hint{font-size:12px;color:var(--muted);margin-bottom:14px}
  .vt .frow{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px}
  .vt .frow.three{grid-template-columns:1fr 1fr 1fr}
  .vt .f label{display:block;font-size:11.5px;font-weight:600;color:var(--muted);margin-bottom:5px}
  .vt .f input,.vt .f select,.vt .f textarea{width:100%;border:1px solid var(--line);border-radius:9px;padding:10px 11px;font-size:13px;font-family:'Inter';background:var(--card)}
  .vt .gst{display:grid;grid-template-columns:1.2fr 1fr 1fr;gap:10px;margin-bottom:10px;align-items:end}
  .vt .packs{display:flex;gap:10px;flex-wrap:wrap}
  .vt .pk{border:1.5px solid var(--line);border-radius:12px;padding:12px 15px;cursor:pointer;min-width:200px;background:var(--paper)}
  .vt .pk.on{border-color:var(--gold);background:#FBF3E0}
  .vt .pk b{font-family:'Space Grotesk';font-size:13px;display:block}
  .vt .pk small{font-size:11px;color:var(--muted);display:block;margin-top:3px;line-height:1.5}
  .vt .pk .pp{font-family:'Space Grotesk';font-weight:700;color:var(--gold-deep);margin-top:6px;display:block}
  .vt .summary{position:sticky;bottom:0;background:var(--ink);border-radius:16px;color:#fff;padding:18px 24px;display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-top:8px}
  .vt .summary .bk{font-size:12px;color:#9FB0CC;line-height:1.7}
  .vt .summary .tt{font-family:'Space Grotesk';font-size:26px;font-weight:700;color:var(--gold);text-align:right}
  .vt .summary .tt small{font-size:12px;color:#9FB0CC;display:block;font-family:'Inter';font-weight:500}
  .vt .crear{font-family:'Space Grotesk';font-size:14px;font-weight:600;background:var(--gold);color:#3A2B05;border:none;border-radius:11px;padding:13px 26px;cursor:pointer}
  .vt .stepper{display:inline-flex;align-items:center;border:1px solid var(--line);border-radius:9px;overflow:hidden}
  .vt .stepper button{width:34px;height:38px;border:none;background:var(--paper);cursor:pointer;font-size:16px;color:var(--muted)}
  .vt .stepper span{width:44px;text-align:center;font-family:'Space Grotesk';font-weight:700;font-size:15px}

  /* overlay / modales */
  .vt .ovl{position:fixed;inset:0;background:rgba(14,21,38,.55);display:flex;align-items:center;justify-content:center;z-index:90;padding:20px}
  .vt .modal{background:var(--card);border-radius:16px;width:420px;max-width:100%;overflow:hidden;box-shadow:0 18px 50px rgba(14,21,38,.25)}
  .vt .modal .mh{background:linear-gradient(135deg,var(--ink),var(--ink-2));color:#fff;padding:16px 20px}
  .vt .modal .mh .nm{font-family:'Space Grotesk';font-weight:700;font-size:15px}
  .vt .modal .mh .fl{font-family:'IBM Plex Mono';font-size:12px;color:#C9D5EA;margin-top:3px}
  .vt .modal .mb{padding:16px 20px}
  .vt .prow{display:flex;justify-content:space-between;font-size:12.5px;padding:5px 0}
  .vt .prow span{color:var(--muted)} .vt .prow b{font-weight:600;text-align:right}
  .vt .pop-menus{margin-top:9px;background:var(--paper);border:1px solid var(--line);border-radius:10px;padding:9px 12px;font-size:12px;line-height:1.7}
  .vt .cta{display:block;width:100%;margin-top:13px;font-family:'Space Grotesk';font-size:13px;font-weight:600;background:var(--ink);color:#fff;border:none;border-radius:10px;padding:11px;cursor:pointer}
  .vt .mclose{float:right;background:rgba(255,255,255,.15);border:none;color:#fff;width:26px;height:26px;border-radius:99px;cursor:pointer}
  .vt .mfield label{display:block;font-size:11.5px;font-weight:600;color:var(--muted);margin:10px 0 5px}
  .vt .mfield input,.vt .mfield select{width:100%;border:1px solid var(--line);border-radius:9px;padding:10px 11px;font-size:13px}
  .vt .mbtn{margin-top:14px;width:100%;font-family:'Space Grotesk';font-weight:600;border:none;border-radius:10px;padding:12px;cursor:pointer}
  .vt .mbtn.gold{background:var(--gold);color:#3A2B05} .vt .mbtn.danger{background:var(--coral-deep);color:#fff}

  @media (max-width:900px){.vt .det-grid{grid-template-columns:1fr}.vt .frow,.vt .frow.three,.vt .gst{grid-template-columns:1fr}}
  @media (max-width:640px){
    .vt{font-size:14px}
    .vt .topbar h1{font-size:23px}
    .vt .toolbar{align-items:stretch}
    .vt .search{min-width:0;width:100%}
    .vt .panel{overflow-x:auto;-webkit-overflow-scrolling:touch}
    .vt table{min-width:560px}
    .vt .det-head{padding:20px 18px}
    .vt .det-head .tot{font-size:25px}
    .vt .step{padding:16px 14px}
    .vt .pk{min-width:0;width:100%}
  }
</style>

{{-- ============================= LISTADO ============================= --}}
@if ($screen === 'list')
  <div class="topbar">
    <div>
      <div class="crumb">Gestión / Ventas</div>
      <h1>Ventas</h1>
    </div>
    <button class="newbtn" wire:click="go('create')">+ Nueva venta manual</button>
  </div>

  <div class="panel">
    <div class="toolbar">
      <div class="chips">
        <button class="chip {{ $filter==='all'?'on':'' }}" wire:click="setFilter('all')">Todas ({{ $counts['all'] }})</button>
        <button class="chip {{ $filter==='conf'?'on':'' }}" wire:click="setFilter('conf')">Confirmadas ({{ $counts['conf'] }})</button>
        <button class="chip {{ $filter==='unpaid'?'on':'' }}" wire:click="setFilter('unpaid')">Sin pagar ({{ $counts['unpaid'] }})</button>
        <button class="chip {{ $filter==='nodate'?'on':'' }}" wire:click="setFilter('nodate')">Sin fecha ({{ $counts['nodate'] }})</button>
        <button class="chip {{ $filter==='vol'?'on':'' }}" wire:click="setFilter('vol')">Voladas ({{ $counts['vol'] }})</button>
        <button class="chip {{ $filter==='can'?'on':'' }}" wire:click="setFilter('can')">Canceladas ({{ $counts['can'] }})</button>
        <button class="chip {{ $filter==='today'?'on':'' }}" wire:click="setFilter('today')">Hoy ✈ ({{ $counts['today'] }})</button>
      </div>
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        @php $selYear = $month !== '' ? Carbon::createFromFormat('Y-m-d', $month.'-01')->year : now()->year; @endphp
        <div class="msel-wrap">
          <button type="button" class="msel-nav" wire:click="stepMonth(-1)" title="Mes anterior">‹</button>
          <div class="msel"
               x-data="{
                 open:false,
                 year: {{ $selYear }},
                 x:0, y:0,
                 place(){ const r=$refs.trig.getBoundingClientRect(); this.y=r.bottom+8; let x=r.right-266; this.x=Math.max(12, Math.min(x, window.innerWidth-278)); }
               }"
               @keydown.escape="open=false"
               @scroll.window="open=false"
               @resize.window="open=false">
            <button type="button" class="msel-trigger" x-ref="trig" :class="{ active: open }"
                    @click="open = !open; if (open) { year = {{ $selYear }}; place(); }">
              <span class="ic">📅</span>
              <span>{{ $this->monthLabel() }}</span>
              <span class="chev" :class="{ up: open }">▾</span>
            </button>

            <div class="msel-pop" x-show="open" x-transition.origin.top :style="`top:${y}px; left:${x}px`" @click.outside="open=false" x-cloak>
              <div class="msel-yr">
                <button type="button" @click="year--">‹</button>
                <span class="y" x-text="year"></span>
                <button type="button" @click="year++">›</button>
              </div>
              <div class="msel-grid">
                @foreach (['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'] as $i => $mLbl)
                  @php $mNum = $i + 1; @endphp
                  <button type="button" class="msel-m"
                          :class="{
                            sel: '{{ $month }}' === (year + '-' + String({{ $mNum }}).padStart(2,'0')),
                            now: '{{ now()->format('Y-m') }}' === (year + '-' + String({{ $mNum }}).padStart(2,'0'))
                          }"
                          @click="$wire.setMonth(year, {{ $mNum }}); open = false">{{ $mLbl }}</button>
                @endforeach
              </div>
              @if ($month !== '')
                <button type="button" class="msel-all" @click="$wire.clearMonth(); open = false">Ver todos los meses</button>
              @endif
            </div>
          </div>
          <button type="button" class="msel-nav" wire:click="stepMonth(1)" title="Mes siguiente">›</button>
        </div>
        <div class="search">🔎 <input placeholder="Cliente, email, # de venta…" wire:model.live.debounce.400ms="search"></div>
      </div>
    </div>

    @if (in_array($filter, ['nodate-paid', 'unpaid-soon'], true))
      <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;padding:11px 16px;background:#FFF7E6;border-bottom:1px solid var(--line);font-size:12.5px;color:var(--text)">
        <span>
          @if ($filter === 'nodate-paid')
            Mostrando solo <b>reservas pagadas sin fecha</b> — la alerta del Escritorio.
          @else
            Mostrando solo <b>pagos pendientes con reserva en las próximas 72h</b> — la alerta del Escritorio.
          @endif
        </span>
        <a href="#" wire:click.prevent="setFilter('{{ $filter === 'nodate-paid' ? 'nodate' : 'unpaid' }}')" style="color:var(--sky);font-weight:600;white-space:nowrap">Ver todas →</a>
      </div>
    @endif

    @php $orders = $this->getOrders(); @endphp
    <table>
      <thead><tr>
        <th>Reserva</th><th>Cliente</th><th style="text-align:center">Pax</th><th>Experiencia</th>
        <th>Canal</th><th>Hotel / Pickup</th><th>Pago</th><th>Total</th><th>Estado</th><th></th>
      </tr></thead>
      <tbody>
        @forelse ($orders as $o)
          @php [$pay,$st] = $this->statusOf($o); $ch = $this->channelOf($o); @endphp
          <tr wire:click="go('detail', {{ $o->id }})">
            <td>
              @if ($o->booking_start)
                <div class="slot">{{ $o->booking_start->format('d/m · H:i') }}<small>{{ $this->slotLabel($o->booking_start) }}</small></div>
              @else
                <span class="nodate">⚠ SIN FECHA</span>
              @endif
            </td>
            <td class="cl"><b>{{ $this->cleanName($o->customer_name) }}</b><small>#{{ $o->woocommerce_order_id ?: $o->id }} · {{ $o->customer_email ?: '—' }}</small></td>
            <td class="pax">{{ $this->paxOf($o) }}</td>
            <td>{{ $o->product?->name ?? '—' }}</td>
            <td>{!! $CHB[$ch] !!}</td>
            <td style="font-size:12.5px;color:var(--muted)">{{ Str::limit($this->hotelOf($o), 26) }}</td>
            <td>{!! $PAYB[$pay] !!}</td>
            <td class="tot">{{ $money($o->total) }}</td>
            <td>{!! $STB[$st] !!}</td>
            <td><button class="peek" wire:click.stop="showQuick({{ $o->id }})">Vista rápida</button></td>
          </tr>
        @empty
          <tr><td colspan="10" class="empty">
            @if ($month !== '')
              No hay ventas en <b>{{ $this->monthLabel() }}</b>{{ $filter !== 'all' ? ' con este filtro' : '' }}.
              <a href="#" wire:click.prevent="clearMonth" style="color:var(--sky)">Ver todos los meses</a>
            @else
              No hay ventas que coincidan con el filtro.
            @endif
          </td></tr>
        @endforelse
      </tbody>
    </table>

    <div class="foot">
      <span>Mostrando {{ $orders->count() }} de {{ $orders->total() }} ventas · <b style="color:var(--text)">{{ $money($counts['periodTotal']) }}</b> {{ $month !== '' ? 'en '.$this->monthLabel() : 'acumulado' }}</span>
      <div>{{ $orders->links() }}</div>
    </div>
  </div>

  {{-- vista rápida --}}
  @php $q = $this->getQuick(); @endphp
  @if ($q)
    @php [$qpay,$qst] = $this->statusOf($q); $qch = $this->channelOf($q); $qdishes = $this->dishesOf($q); @endphp
    <div class="ovl" wire:click.self="closeQuick">
      <div class="modal">
        <div class="mh">
          <button class="mclose" wire:click="closeQuick">✕</button>
          <div class="nm">{{ $this->cleanName($q->customer_name) }} · #{{ $q->woocommerce_order_id ?: $q->id }}</div>
          <div class="fl">{{ $q->booking_start ? '✈ '.($q->product?->name).' · '.$q->booking_start->format('d/m H:i') : '⚠ '.($q->product?->name).' · SIN FECHA' }}</div>
        </div>
        <div class="mb">
          <div class="prow"><span>Pax</span><b>{{ $this->paxOf($q) }} personas</b></div>
          <div class="prow"><span>Canal</span><b>{!! $CHB[$qch] !!}</b></div>
          <div class="prow"><span>Pago</span><b>{!! $PAYB[$qpay] !!} · {{ $money($q->total) }}</b></div>
          <div class="prow"><span>Hotel / pickup</span><b>{{ Str::limit($this->hotelOf($q), 28) }}</b></div>
          <div class="pop-menus">🍽 <b>Menús:</b>
            @forelse ($qdishes as $d) {{ $d['qty'] }}× {{ $d['name'] }}@if(!$loop->last) · @endif @empty Sin menús detallados @endforelse
          </div>
          <button class="cta" wire:click="go('detail', {{ $q->id }})">Abrir venta completa →</button>
        </div>
      </div>
    </div>
  @endif
@endif

{{-- ============================= DETALLE ============================= --}}
@if ($screen === 'detail')
  @php $o = $this->getSelected(); @endphp
  @if (! $o)
    <div class="panel"><div class="empty">Venta no encontrada. <a href="#" wire:click.prevent="go('list')">Volver</a></div></div>
  @else
    @php
      [$pay,$st] = $this->statusOf($o);
      $ch = $this->channelOf($o); $pax = $this->paxOf($o); $dishes = $this->dishesOf($o);
      $phone = $this->phoneOf($o);
    @endphp
    <div class="topbar">
      <div>
        <div class="crumb"><a wire:click="go('list')">← Ventas</a> / Detalle</div>
        <h1>Venta #{{ $o->woocommerce_order_id ?: $o->id }}</h1>
      </div>
    </div>

    <div class="det-head">
      <div>
        <div class="nm">{{ $this->cleanName($o->customer_name) }} · {{ $pax }} pax</div>
        <div class="mt">
          ✈ {{ $o->product?->name ?? 'Experiencia' }} · {{ $o->booking_start ? $o->booking_start->isoFormat('ddd D MMM · HH:mm') : 'SIN FECHA' }}<br>
          📱 {{ $phone ?: 'sin teléfono' }} · {{ $o->customer_email ?: 'sin email' }} · Canal: {{ ucfirst($ch) }}
        </div>
        <div class="det-acts">
          @if ($phone)
            <a class="abtn wa" target="_blank" href="https://wa.me/{{ preg_replace('/[^0-9]/','',$phone) }}">💬 WhatsApp</a>
          @else
            <button class="abtn wa" wire:click="$dispatch('notify')" disabled style="opacity:.5">💬 Sin teléfono</button>
          @endif
          <button class="abtn" wire:click="resendConfirmation">📧 Reenviar confirmación</button>
          <button class="abtn" wire:click="sendPaymentLink">💳 Enviar link de pago</button>
          <button class="abtn" wire:click="openReschedule">📅 Reagendar</button>
          @if ($st !== 'can')
            <button class="abtn danger" wire:click="openCancel">✕ Cancelar venta</button>
          @endif
        </div>
      </div>
      <div style="text-align:right">
        {!! $STB[$st] !!}
        <div class="tot">{{ $money($o->total) }}</div>
        <div style="font-size:12px;color:#9FB0CC;margin-top:4px">{!! $PAYB[$pay] !!}</div>
      </div>
    </div>

    <div class="det-grid">
      <div>
        <div class="card">
          <h3>Comensales y menús · {{ $pax }} {{ $pax === 1 ? 'persona' : 'personas' }}</h3>
          @php $comensales = $this->comensalesOf($o); @endphp
          @foreach ($comensales as $i => $menu)
            <div class="guest">
              <div class="gn">
                <b>{{ $i===0 ? $this->cleanName($o->customer_name).' (titular)' : 'Comensal '.($i+1) }}</b>
                @if (count($menu))
                  @foreach ($menu as $dish)<small>🍽 {{ $dish }}</small>@endforeach
                @else
                  <small style="color:var(--coral-deep)">Menú por confirmar</small>
                @endif
              </div>
              <span class="seatchip">ASIENTO {{ $i+1 }}</span>
            </div>
          @endforeach
        </div>

        @php $extras = $this->extrasOf($o); @endphp
        @if (count($extras))
          <div class="card" style="margin-top:16px">
            <h3>Extras y ocasión</h3>
            @foreach ($extras as $e)
              <div class="pay-row"><span>🎁 {{ $e['name'] }}</span><b>× {{ $e['qty'] }}</b></div>
            @endforeach
          </div>
        @endif

        <div class="card" style="margin-top:16px">
          <h3>Transporte / pickup</h3>
          @php $addons = $this->addOnsOf($o); @endphp
          @forelse ($addons as $a)
            <div class="pay-row"><span>🚐 {{ Str::limit($a['name'], 60) }}</span><b>× {{ $a['qty'] }}</b></div>
          @empty
            <div class="pay-row"><span>Hotel de recogida</span><b>{{ $this->hotelOf($o) }}</b></div>
            <div class="pay-row"><span>Estado</span><b style="color:var(--teal)">{{ $this->hotelOf($o) === 'Pickup propio' ? 'Llega por su cuenta' : 'Shuttle por asignar' }}</b></div>
          @endforelse
        </div>
      </div>

      <div>
        <div class="card">
          <h3>Pago</h3>
          <div class="pay-row"><span>{{ $o->product?->name ?? 'Experiencia' }} × {{ $pax }} pax</span><b>{{ $money($o->total) }}</b></div>
          @if ((float)$o->discount_total > 0)
            <div class="pay-row"><span>Descuento</span><b style="color:var(--coral-deep)">−{{ $money($o->discount_total) }}</b></div>
          @endif
          <div class="pay-row total"><span>Total</span><b>{{ $money($o->total) }}</b></div>
          <div class="pay-row" style="margin-top:6px"><span>Estado</span><b>{!! $PAYB[$pay] !!}</b></div>
        </div>

        <div class="card" style="margin-top:16px">
          <h3>Notas internas</h3>
          <p style="font-size:12.5px;color:var(--muted);line-height:1.7">{{ data_get($o->data, 'data.internal_note') ?: 'Sin notas internas.' }}</p>
        </div>

        <div class="card" style="margin-top:16px">
          <h3>Actividad</h3>
          <div class="tl">
            @foreach ($this->activityOf($o) as $a)
              <div class="tl-i"><b>{{ $a['t'] }}</b> · {{ $a['s'] }}</div>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    {{-- modal reagendar --}}
    @if ($showReschedule)
      <div class="ovl" wire:click.self="$set('showReschedule', false)">
        <div class="modal"><div class="mh"><button class="mclose" wire:click="$set('showReschedule', false)">✕</button><div class="nm">Reagendar reserva</div></div>
          <div class="mb">
            <div class="mfield"><label>Nueva fecha y hora</label><input type="datetime-local" wire:model="rescheduleDate"></div>
            <button class="mbtn gold" wire:click="reschedule">Confirmar nueva fecha</button>
          </div>
        </div>
      </div>
    @endif

    {{-- modal cancelar --}}
    @if ($showCancel)
      <div class="ovl" wire:click.self="$set('showCancel', false)">
        <div class="modal"><div class="mh"><button class="mclose" wire:click="$set('showCancel', false)">✕</button><div class="nm">Cancelar venta</div></div>
          <div class="mb">
            <div class="mfield"><label>Motivo (obligatorio)</label>
              <select wire:model="cancelReason">
                <option>Solicitud del cliente</option>
                <option>Clima</option>
                <option>No pago</option>
                <option>Otro</option>
              </select>
            </div>
            <button class="mbtn danger" wire:click="cancelSale">Confirmar cancelación</button>
          </div>
        </div>
      </div>
    @endif
  @endif
@endif

{{-- ============================= CREAR ============================= --}}
@if ($screen === 'create')
  @php $totals = $this->getCreateTotal(); @endphp
  <div class="topbar">
    <div>
      <div class="crumb"><a wire:click="go('list')">← Ventas</a> / Nueva venta</div>
      <h1>Nueva venta manual</h1>
    </div>
  </div>

  <div class="steps">
    <div class="step">
      <h3><span class="stn">1</span> Reserva</h3>
      <p class="hint">El precio lo define la experiencia — no se escribe a mano.</p>
      <div class="frow three">
        <div class="f"><label>Experiencia</label>
          <select wire:model.live="cProductId">
            @foreach (\App\Models\Product::all() as $p)
              <option value="{{ $p->wordpress_product_id }}">{{ $p->name }} — ${{ \App\Filament\Pages\VentasPage::PRICES[$p->name] ?? 120 }}/pax</option>
            @endforeach
          </select></div>
        <div class="f"><label>Fecha</label><input type="date" wire:model="cDate"></div>
        <div class="f"><label>Hora</label><input type="time" wire:model="cTime"></div>
      </div>
    </div>

    <div class="step">
      <h3><span class="stn">2</span> Comensales</h3>
      <p class="hint">Menú por persona — esto alimenta cocina y la comanda.</p>
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px">
        <label style="font-size:12px;font-weight:600;color:var(--muted)">Cantidad de pax</label>
        <div class="stepper"><button wire:click="stepPax(-1)">−</button><span>{{ $cPax }}</span><button wire:click="stepPax(1)">+</button></div>
        <span style="font-size:12px;color:var(--teal);font-weight:600">✓ Capacidad por reserva: 22</span>
      </div>
      @foreach ($cGuests as $i => $g)
        <div class="gst">
          <div class="f"><label>Comensal {{ $i+1 }}{{ $i===0 ? ' (titular)' : '' }}</label><input wire:model="cGuests.{{ $i }}.name" placeholder="Nombre"></div>
          <div class="f"><label>Menú</label>
            <select wire:model="cGuests.{{ $i }}.menu">
              @foreach (\App\Filament\Pages\CocinaPage::ITEMS as $it)<option>{{ $it }}</option>@endforeach
            </select></div>
          <div class="f"><label>Restricción / alergia</label><input wire:model="cGuests.{{ $i }}.restriction" placeholder="Ej: maní, gluten…"></div>
        </div>
      @endforeach
    </div>

    <div class="step">
      <h3><span class="stn">3</span> Extras y ocasión</h3>
      <div class="packs">
        @foreach (\App\Filament\Pages\VentasPage::PACKS as $key => $pk)
          <div class="pk {{ in_array($key,$cPacks)?'on':'' }}" wire:click="togglePack('{{ $key }}')">
            <b>{{ $pk['name'] }}</b><small>{{ $pk['desc'] }}</small><span class="pp">+${{ $pk['price'] }}</span>
          </div>
        @endforeach
      </div>
      <div class="frow" style="margin-top:14px">
        <div class="f"><label>Hotel de recogida (shuttle)</label><input wire:model="cHotel" placeholder="Ej: Dreams Flora Resort"></div>
        <div class="f"><label>Ocasión especial</label>
          <select wire:model="cOccasion"><option>Ninguna</option><option>Cumpleaños 🎂</option><option>Aniversario 💍</option><option>Pedida de mano 💎</option><option>Corporativo 🏢</option></select></div>
      </div>
    </div>

    <div class="step">
      <h3><span class="stn">4</span> Cliente y canal</h3>
      <div class="frow three">
        <div class="f"><label>Nombre completo *</label><input wire:model="cName" placeholder="Nombre del titular"></div>
        <div class="f"><label>WhatsApp</label><input wire:model="cPhone" placeholder="+1 ___ ___ ____"></div>
        <div class="f"><label>Email</label><input wire:model="cEmail" placeholder="para la confirmación"></div>
      </div>
      <div class="frow">
        <div class="f"><label>Canal de la venta *</label>
          <select wire:model="cChannel"><option>WhatsApp directo</option><option>Teléfono</option><option>Walk-in</option><option>Hotel / concierge</option><option>Viator (manual)</option><option>Instagram DM</option></select></div>
      </div>
    </div>

    <div class="step">
      <h3><span class="stn">5</span> Pago</h3>
      <div class="frow">
        <div class="f"><label>Modalidad</label>
          <select wire:model.live="cMode"><option value="full">Pago total</option><option value="deposit">Depósito 50%</option></select></div>
        <div class="f"><label>Descuento autorizado (%)</label>
          <select wire:model.live="cDiscount"><option value="0">Sin descuento</option><option value="10">−10% · gerencia</option><option value="15">−15% · promo flash</option></select></div>
      </div>
      <div class="f"><label>Nota interna</label><textarea rows="2" wire:model="cNote" placeholder="Visible solo para el staff"></textarea></div>
    </div>

    <div class="summary">
      <div class="bk">
        Experiencia × {{ $cPax }} pax: <b style="color:#fff">{{ $money($totals['base']) }}</b>
        @if ($totals['packTotal']) · Packs: <b style="color:#fff">{{ $money($totals['packTotal']) }}</b> @endif
        @if ($totals['disc']) · Descuento: <b style="color:#FFAB9E">−{{ $money($totals['disc']) }}</b> @endif
        @if ($cMode==='deposit') · <b style="color:#F3CE7E">Depósito 50%</b> @endif
      </div>
      <div style="display:flex;align-items:center;gap:20px">
        <div class="tt">{{ $money($totals['total']) }}<small>calculado automáticamente</small></div>
        <button class="crear" wire:click="createSale">Crear venta</button>
      </div>
    </div>
  </div>
@endif
</div>
</x-filament-panels::page>
