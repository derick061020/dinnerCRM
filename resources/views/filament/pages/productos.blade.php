@php
    use Illuminate\Support\Str;
    use Carbon\Carbon;
    Carbon::setLocale('es');
    $money = fn ($n) => '$' . number_format((float) $n, 0);
    $catalog = $this->getCatalog();
    $product = $this->getProduct();
    $flights = $this->getFlights();
    $flight = $this->getCurrentFlight();
    $date = Carbon::parse($selectedDate);

    // Mesa rectangular: 8 arriba, 3 derecha, 8 abajo, 3 izquierda
    $POS = [];
    for ($c = 2; $c <= 9; $c++) $POS[] = [$c, 1];
    for ($r = 2; $r <= 4; $r++) $POS[] = [10, $r];
    for ($c = 9; $c >= 2; $c--) $POS[] = [$c, 5];
    for ($r = 4; $r >= 2; $r--) $POS[] = [1, $r];
@endphp

<x-filament-panels::page>
<div class="rv">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">

<style>
  .rv{
    --ink:#0E1526; --ink-2:#172139; --paper:#F6F7F9; --card:#FFFFFF; --line:#E6E9EF;
    --text:#1B2434; --muted:#69748A; --gold:#E8B544; --gold-deep:#C28F1E; --coral:#FF6B57;
    --coral-deep:#D6492F; --teal:#1FA98C; --sky:#3D7BFA; --viator:#7B5CD6; --r:14px;
    font-family:'Inter',sans-serif; color:var(--text); -webkit-font-smoothing:antialiased; font-size:15px;
  }
  .rv *{box-sizing:border-box}
  .rv .mono{font-family:'IBM Plex Mono',monospace}
  .rv h1{font-family:'Space Grotesk';font-size:26px;font-weight:700;letter-spacing:-.02em;margin:0}
  .rv .sub{color:var(--muted);font-size:13px;margin-top:4px}
  .rv .layout{display:grid;grid-template-columns:280px 1fr;gap:20px;margin-top:18px}

  .rv .cat{display:flex;flex-direction:column;gap:10px}
  .rv .cat-head{font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--muted);font-weight:600;margin-bottom:2px}
  .rv .exp{background:var(--card);border:1px solid var(--line);border-radius:var(--r);padding:14px 16px;cursor:pointer;transition:border-color .15s}
  .rv .exp:hover{border-color:#C9D2E2}
  .rv .exp.on{border-color:var(--gold);box-shadow:0 0 0 3px rgba(232,181,68,.18)}
  .rv .exp .nm{font-family:'Space Grotesk';font-weight:600;font-size:14.5px}
  .rv .exp .pr{font-size:12.5px;color:var(--muted);margin-top:3px}
  .rv .exp .pr b{color:var(--text)}
  .rv .exp .meta{display:flex;gap:6px;margin-top:8px;flex-wrap:wrap}
  .rv .pill{font-size:10.5px;font-weight:600;padding:3px 8px;border-radius:99px}
  .rv .pill.day{background:#FBF3E0;color:var(--gold-deep)}
  .rv .pill.night{background:#EDEAFB;color:var(--viator)}
  .rv .pill.live{background:#E7F6F1;color:var(--teal)}
  .rv .pill.off{background:#F1F3F7;color:var(--muted)}

  .rv .main{background:var(--card);border:1px solid var(--line);border-radius:18px;overflow:hidden}
  .rv .main-head{display:flex;justify-content:space-between;align-items:center;padding:18px 24px;border-bottom:1px solid var(--line);flex-wrap:wrap;gap:12px}
  .rv .main-head .t{font-family:'Space Grotesk';font-size:18px;font-weight:700}
  .rv .main-head .t small{display:block;font-size:12px;color:var(--muted);font-weight:500;font-family:'Inter';margin-top:2px}
  .rv .tabs{display:flex;background:var(--paper);border:1px solid var(--line);border-radius:99px;padding:4px}
  .rv .tab{font-size:13px;font-weight:600;padding:8px 18px;border-radius:99px;cursor:pointer;color:var(--muted);border:none;background:transparent}
  .rv .tab.on{background:var(--ink);color:#fff}

  .rv .datebar{display:flex;align-items:center;justify-content:space-between;padding:16px 24px;border-bottom:1px solid var(--line);flex-wrap:wrap;gap:12px}
  .rv .datenav{display:flex;align-items:center;gap:10px}
  .rv .datenav button{width:32px;height:32px;border-radius:9px;border:1px solid var(--line);background:var(--card);cursor:pointer;font-size:15px;color:var(--muted)}
  .rv .datenav .d{font-family:'Space Grotesk';font-weight:600;font-size:15px;text-transform:capitalize}
  .rv .datenav input{border:1px solid var(--line);border-radius:9px;padding:7px 10px;font-size:13px}

  .rv .slots{display:flex;gap:10px;padding:16px 24px 0;flex-wrap:wrap}
  .rv .slot-tab{border:1px solid var(--line);background:var(--paper);border-radius:12px;padding:10px 16px;cursor:pointer;text-align:left;min-width:150px}
  .rv .slot-tab .h{font-family:'IBM Plex Mono';font-size:15px;font-weight:600}
  .rv .slot-tab .n{font-size:11.5px;color:var(--muted);margin-top:2px}
  .rv .slot-tab .o{font-size:11px;font-weight:600;margin-top:6px}
  .rv .slot-tab.on{border-color:var(--ink);background:var(--ink);color:#fff}
  .rv .slot-tab.on .n{color:#9FB0CC}
  .rv .o.ok{color:var(--teal)} .rv .o.full{color:var(--gold-deep)} .rv .o.low{color:var(--coral-deep)}
  .rv .slot-tab.on .o.ok{color:#5BD6BB} .rv .slot-tab.on .o.full{color:#F3CE7E} .rv .slot-tab.on .o.low{color:#FFAB9E}

  .rv .flightbar{display:flex;gap:26px;padding:18px 24px;flex-wrap:wrap;border-bottom:1px solid var(--line)}
  .rv .fb{font-size:12px;color:var(--muted)}
  .rv .fb b{display:block;font-family:'Space Grotesk';font-size:16px;color:var(--text);margin-top:2px}
  .rv .fb b.warn{color:var(--coral-deep)} .rv .fb b.ok{color:var(--teal)}

  .rv .minwarn{margin:16px 24px 0;background:#FFF0ED;border:1px solid #F6C7BE;border-radius:12px;padding:12px 16px;font-size:12.5px;color:#9C2F1B;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}

  .rv .stage{padding:26px 24px 10px;display:flex;flex-direction:column;align-items:center}
  .rv .platform{background:linear-gradient(165deg,var(--ink),var(--ink-2));border-radius:16px;padding:26px 30px 20px;width:100%;max-width:760px}
  .rv .seatgrid{display:grid;grid-template-columns:repeat(10,50px);grid-template-rows:repeat(5,50px);gap:10px;justify-content:center}
  .rv .table-strip{grid-column:2/10;grid-row:2/5;border-radius:14px;background:linear-gradient(135deg,rgba(232,181,68,.22),rgba(232,181,68,.42),rgba(232,181,68,.22));border:1px solid rgba(232,181,68,.4);display:flex;align-items:center;justify-content:center;font-family:'Space Grotesk';font-size:11px;letter-spacing:.24em;color:#F3CE7E;font-weight:600;text-align:center;line-height:1.8}
  .rv .st{border-radius:9px 9px 13px 13px;display:flex;align-items:center;justify-content:center;font-family:'Space Grotesk';font-size:12px;font-weight:700;cursor:pointer;position:relative;border:1.5px solid rgba(255,255,255,.16);background:rgba(255,255,255,.07);color:#7E8DA9;transition:transform .12s}
  .rv .st:hover{transform:translateY(-3px)}
  .rv .st.paid{background:var(--gold);border-color:var(--gold-deep);color:#3A2B05}
  .rv .st.unpaid{background:rgba(232,181,68,.14);border-color:var(--gold);border-style:dashed;color:#F3CE7E}
  .rv .legend{display:flex;gap:18px;margin:16px 0 6px;font-size:12px;color:var(--muted);flex-wrap:wrap;justify-content:center}
  .rv .lg{display:flex;align-items:center;gap:7px}
  .rv .sw{width:14px;height:14px;border-radius:5px;border:1.5px solid var(--line)}

  .rv .quick{display:flex;gap:10px;padding:8px 24px 22px;flex-wrap:wrap;justify-content:center}
  .rv .qbtn{font-size:12.5px;font-weight:600;padding:9px 16px;border-radius:10px;border:1px solid var(--line);background:var(--card);cursor:pointer;color:var(--text)}
  .rv .qbtn.coral{background:#FFF0ED;border-color:#F6C7BE;color:var(--coral-deep)}

  .rv .emptyflights{padding:46px 24px;text-align:center;color:var(--muted)}

  /* popover asiento */
  .rv .ovl{position:fixed;inset:0;background:rgba(14,21,38,.5);display:flex;align-items:center;justify-content:center;z-index:90;padding:20px}
  .rv .pop{width:300px;background:var(--card);border:1px solid var(--line);border-radius:14px;box-shadow:0 16px 44px rgba(14,21,38,.22);padding:18px}
  .rv .pop .pn{font-family:'Space Grotesk';font-weight:700;font-size:15px}
  .rv .pop .pd{font-size:12px;color:var(--muted);margin-top:3px;line-height:1.55}
  .rv .pop .badges{display:flex;gap:6px;margin-top:9px;flex-wrap:wrap}
  .rv .b{display:inline-flex;align-items:center;gap:4px;font-size:10.5px;font-weight:600;padding:3px 8px;border-radius:99px}
  .rv .b-paid{background:#E7F6F1;color:var(--teal)} .rv .b-pend{background:#FFF0ED;color:var(--coral-deep)}
  .rv .b-via{background:#F1EDFB;color:var(--viator)} .rv .b-web{background:#EBF1FF;color:var(--sky)}
  .rv .pop .acts{display:flex;gap:8px;margin-top:13px}
  .rv .pbtn{flex:1;font-size:12px;font-weight:600;padding:8px;border-radius:9px;border:1px solid var(--line);background:var(--paper);cursor:pointer;text-decoration:none;text-align:center;color:var(--text)}

  /* configuración */
  .rv .conf{padding:22px 24px 26px}
  .rv .impact{background:#FFF7ED;border:1px solid #F4DFB8;border-radius:12px;padding:13px 16px;font-size:12.5px;color:#8A6512;margin-bottom:18px;line-height:1.55}
  .rv .grid-h,.rv .grid-r{display:grid;grid-template-columns:1.1fr .9fr .7fr .8fr 90px;gap:10px;align-items:center}
  .rv .grid-h{font-size:10.5px;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);font-weight:600;padding:0 4px 10px}
  .rv .grid-r{background:var(--paper);border:1px solid var(--line);border-radius:12px;padding:12px;margin-bottom:10px;font-size:13px}
  .rv .grid-r input,.rv .grid-r select{width:100%;border:1px solid var(--line);border-radius:8px;padding:8px 9px;font-size:13px;background:var(--card)}
  .rv .toggle{display:inline-flex;align-items:center;gap:7px;font-size:12px;font-weight:600;cursor:pointer}
  .rv .tg{width:36px;height:20px;border-radius:99px;background:#D6DCE7;position:relative;transition:background .15s}
  .rv .tg::after{content:"";position:absolute;top:2.5px;left:3px;width:15px;height:15px;border-radius:99px;background:#fff;transition:left .15s}
  .rv .toggle.on .tg{background:var(--teal)} .rv .toggle.on .tg::after{left:18px}
  .rv .del{font-size:11.5px;font-weight:600;color:var(--coral-deep);background:#FFF0ED;border:1px solid #F6C7BE;border-radius:8px;padding:7px 0;width:100%;cursor:pointer}
  .rv .conf-foot{display:flex;justify-content:space-between;align-items:center;margin-top:18px;flex-wrap:wrap;gap:12px}
  .rv .save{font-family:'Space Grotesk';font-size:14px;font-weight:600;background:var(--ink);color:#fff;border:none;border-radius:11px;padding:13px 26px;cursor:pointer}

  @media (max-width:980px){.rv .layout{grid-template-columns:1fr}.rv .grid-h{display:none}.rv .grid-r{grid-template-columns:1fr 1fr;gap:8px}}
  @media (max-width:700px){.rv .seatgrid{grid-template-columns:repeat(10,34px);grid-template-rows:repeat(5,34px);gap:6px}.rv .st{font-size:10px}}
  @media (max-width:520px){
    .rv{font-size:14px}
    .rv h1{font-size:22px}
    .rv .platform{padding:18px 12px 16px}
    .rv .seatgrid{grid-template-columns:repeat(10,26px);grid-template-rows:repeat(5,26px);gap:4px}
    .rv .st{font-size:8.5px;border-radius:6px 6px 9px 9px}
    .rv .main-head,.rv .datebar,.rv .slots{padding-left:14px;padding-right:14px}
    .rv .grid-r{grid-template-columns:1fr}
  }
</style>

  <h1>Reservas e Inventario</h1>
  <div class="sub">La capacidad vive aquí (22 asientos por reserva) y todo el sistema la hereda: escritorio, cocina y web.</div>

  <div class="layout">
    {{-- ===== CATÁLOGO ===== --}}
    <aside class="cat">
      <div class="cat-head">Experiencias</div>
      @foreach ($catalog as $c)
        <div class="exp {{ $selectedProductId === $c['id'] ? 'on' : '' }}" wire:click="selectProduct({{ $c['id'] }})">
          <div class="nm">{{ $c['name'] }}</div>
          <div class="pr">Desde <b>${{ $c['price'] }}</b> · {{ $c['capacity'] }} asientos</div>
          <div class="meta">
            <span class="pill {{ $c['night'] ? 'night' : 'day' }}">{{ $c['night'] ? 'NOCHE' : 'DÍA' }}</span>
            <span class="pill {{ $c['active'] ? 'live' : 'off' }}">{{ $c['active'] ? 'EN VENTA' : 'PAUSADO' }}</span>
          </div>
        </div>
      @endforeach
    </aside>

    {{-- ===== PANEL PRINCIPAL ===== --}}
    <section class="main">
      <div class="main-head">
        <div class="t">{{ $product?->name ?? 'Selecciona una experiencia' }}<small>Plataforma de {{ $product?->default_capacity ?? 22 }} asientos · check-in 45 min antes del despegue</small></div>
        <div class="tabs">
          <button class="tab {{ $activeTab==='map'?'on':'' }}" wire:click="setTab('map')">Mapa de la reserva</button>
          <button class="tab {{ $activeTab==='config'?'on':'' }}" wire:click="setTab('config')">Configuración</button>
        </div>
      </div>

      {{-- ============ MAPA ============ --}}
      @if ($activeTab === 'map')
        <div class="datebar">
          <div class="datenav">
            <button wire:click="shiftDate(-1)">←</button>
            <div class="d">{{ $date->isoFormat('dddd D MMM') }}{{ $date->isToday() ? ' · HOY' : '' }}</div>
            <button wire:click="shiftDate(1)">→</button>
            <input type="date" wire:model.live="selectedDate">
          </div>
        </div>

        @if (empty($flights))
          <div class="emptyflights">Selecciona una experiencia para ver su plataforma.</div>
        @else
          <div class="slots">
            @foreach ($flights as $f)
              @php $total = $f['sold']; $isEmpty = !empty($f['empty']); $cls = $isEmpty?'ok':($total>=$f['cap']?'full':($total<$f['min']?'low':'ok')); @endphp
              <button class="slot-tab {{ $flight['hour']===$f['hour']?'on':'' }}" wire:click="selectSlot('{{ $f['hour'] }}')">
                <div class="h">{{ $f['hour'] }}</div>
                <div class="n">{{ $f['name'] }}</div>
                <div class="o {{ $cls }}">{{ $isEmpty?'0/'.$f['cap'].' · libre':($total>=$f['cap']?'COMPLETO ★':($total<$f['min']?$total.'/'.$f['cap'].' · BAJO MÍNIMO':$total.'/'.$f['cap'].' vendidos')) }}</div>
              </button>
            @endforeach
          </div>

          @php $total = $flight['sold']; @endphp

          @if (!empty($flight['empty']))
            <div class="emptyflights" style="margin-bottom:16px">Sin reservas para <b>{{ $product?->name }}</b> el {{ $date->isoFormat('D MMM') }}{{ $flight['hour']!=='—' ? ' · '.$flight['hour'].'h' : '' }}. Plataforma libre — crea una venta o walk-in para ocupar asientos.</div>
          @endif

          <div class="flightbar">
            <div class="fb">Check-in<b class="mono">{{ $flight['hour'] }}</b></div>
            <div class="fb">Ocupación<b>{{ $total }} / {{ $flight['cap'] }}</b></div>
            <div class="fb">Ingreso de la reserva<b>{{ $money($flight['revenue']) }}</b></div>
            <div class="fb">Pendiente de cobro<b class="{{ $flight['pending']>0?'warn':'ok' }}">{{ $money($flight['pending']) }}</b></div>
            <div class="fb">Mínimo operativo<b class="{{ $total<$flight['min']&&empty($flight['empty'])?'warn':'ok' }}">{{ $flight['min'] }} pax {{ empty($flight['empty']) ? ($total<$flight['min']?'· NO ALCANZADO':'· ✓') : '· libre' }}</b></div>
          </div>

          @if ($total < $flight['min'] && empty($flight['empty']))
            <div class="minwarn">
              <div>⚠ <b>Reserva por debajo del mínimo operativo ({{ $flight['min'] }} pax).</b> Decide antes del cierre: promo flash, consolidar o reagendar.</div>
            </div>
          @endif

          <div class="stage">
            <div class="platform">
              <div class="seatgrid">
                <div class="table-strip">DINNER IN THE SKY<br>50 M · {{ $flight['cap'] }} PAX</div>
                @foreach ($flight['seats'] as $i => $seat)
                  @php [$col,$row] = $POS[$i] ?? [1,1]; @endphp
                  <div class="st {{ $seat ? ($seat['paid']?'paid':'unpaid') : '' }}" style="grid-column:{{ $col }};grid-row:{{ $row }}"
                       wire:click="selectSeat({{ $i }})">
                    @if ($seat)
                      {{ Str::of($seat['name'])->explode(' ')->map(fn($w)=>Str::substr($w,0,1))->take(2)->implode('') }}
                    @else
                      {{ $i+1 }}
                    @endif
                  </div>
                @endforeach
              </div>
            </div>
            <div class="legend">
              <span class="lg"><span class="sw" style="background:var(--gold);border-color:var(--gold-deep)"></span>Pagado</span>
              <span class="lg"><span class="sw" style="border:1.5px dashed var(--gold);background:#FBF3E0"></span>Reservado sin pagar</span>
              <span class="lg"><span class="sw"></span>Libre</span>
            </div>
          </div>

          <div class="quick">
            <a class="qbtn" href="/ventas-page?screen=create">+ Nueva venta / walk-in</a>
            <a class="qbtn" href="/cocina-page">📋 Ver comanda en cocina</a>
          </div>
        @endif
      @endif

      {{-- ============ CONFIGURACIÓN ============ --}}
      @if ($activeTab === 'config')
        <div class="conf">
          <div class="impact">⚠ <b>La capacidad y los horarios afectan todo el sistema.</b> Al guardar, los nuevos horarios se aplican a escritorio, cocina y web. La capacidad por defecto es {{ $product?->default_capacity ?? 22 }} asientos.</div>

          <div class="grid-h"><span>Día</span><span>Despegue</span><span>Capacidad</span><span>Estado</span><span></span></div>

          @forelse ($slots as $i => $slot)
            <div class="grid-r">
              <select wire:model="slots.{{ $i }}.weekday">
                @foreach (\App\Filament\Pages\ManageProducts::WEEKDAYS as $wd => $name)
                  <option value="{{ $wd }}">{{ $name }}</option>
                @endforeach
              </select>
              <input class="mono" type="time" wire:model="slots.{{ $i }}.start_time">
              <input type="number" value="{{ $slot['capacity'] }}" disabled title="La capacidad es del producto y la heredan todos los horarios" style="background:var(--paper);color:var(--muted);cursor:not-allowed">
              <span class="toggle {{ $slot['active'] ? 'on' : '' }}" wire:click="toggleSlot({{ $i }})"><span class="tg"></span>{{ $slot['active'] ? 'Abierto' : 'Cerrado' }}</span>
              <button class="del" wire:click="removeSlot({{ $i }})">Eliminar</button>
            </div>
          @empty
            <div class="impact" style="background:var(--paper);border-color:var(--line);color:var(--muted)">Sin horarios configurados para esta experiencia.</div>
          @endforelse

          <div class="conf-foot">
            <button class="qbtn" wire:click="addSlot">+ Agregar horario</button>
            <button class="save" wire:click="saveSlots">Guardar horarios</button>
          </div>
        </div>
      @endif
    </section>
  </div>

  {{-- ===== popover de asiento ===== --}}
  @if ($seatPopup !== null && $flight && isset($flight['seats'][$seatPopup]))
    @php $seat = $flight['seats'][$seatPopup]; @endphp
    <div class="ovl" wire:click.self="selectSeat(null)">
      <div class="pop">
        @if ($seat)
          <div class="pn">{{ $seat['name'] }}</div>
          <div class="pd">Asiento {{ $seatPopup+1 }} · {{ $flight['name'] }} {{ $flight['hour'] }}</div>
          <div class="badges">
            {!! $seat['paid'] ? '<span class="b b-paid">PAGADO</span>' : '<span class="b b-pend">SIN PAGAR</span>' !!}
            {!! $seat['channel']==='viator' ? '<span class="b b-via">VIATOR</span>' : '<span class="b b-web">WEB</span>' !!}
          </div>
          <div class="acts">
            <a class="pbtn" href="/ventas-page?screen=detail&selectedId={{ $seat['order_id'] }}">Ver venta →</a>
            <button class="pbtn" wire:click="selectSeat(null)">Cerrar</button>
          </div>
        @else
          <div class="pn">Asiento {{ $seatPopup+1 }} · libre</div>
          <div class="pd">Disponible. Asigna un walk-in desde una nueva venta o muévelo desde otra reserva.</div>
          <div class="acts">
            <a class="pbtn" href="/ventas-page?screen=create">Asignar walk-in →</a>
            <button class="pbtn" wire:click="selectSeat(null)">Cerrar</button>
          </div>
        @endif
      </div>
    </div>
  @endif
</div>
</x-filament-panels::page>
