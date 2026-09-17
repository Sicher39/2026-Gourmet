<x-filament-panels::page>
    <style>
        .g-dashboard {
            --g-bg: #ffffff;
            --g-bg-soft: #f8fafc;
            --g-border: #e5e7eb;
            --g-text: #172033;
            --g-muted: #64748b;
            --g-shadow: 0 18px 45px -28px rgba(15, 23, 42, .38);
            display: grid;
            gap: 1.5rem;
            color: var(--g-text);
        }

        .dark .g-dashboard {
            --g-bg: #111827;
            --g-bg-soft: #182132;
            --g-border: rgba(255, 255, 255, .1);
            --g-text: #f8fafc;
            --g-muted: #94a3b8;
            --g-shadow: 0 20px 45px -28px rgba(0, 0, 0, .8);
        }

        .g-top-grid {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(19rem, 1fr);
            gap: 1.5rem;
        }

        .g-hero {
            position: relative;
            min-height: 15rem;
            overflow: hidden;
            border-radius: 1.5rem;
            padding: 2.25rem;
            color: #fff;
            background:
                radial-gradient(circle at 88% 18%, rgba(255, 255, 255, .28) 0 8%, transparent 8.5%),
                radial-gradient(circle at 78% 95%, rgba(255, 255, 255, .13) 0 22%, transparent 22.5%),
                linear-gradient(135deg, #166534 0%, #15803d 48%, #22c55e 100%);
            box-shadow: 0 24px 55px -28px rgba(22, 163, 74, .75);
        }

        .g-hero::after {
            content: '';
            position: absolute;
            right: 7%;
            top: 20%;
            width: 8.5rem;
            height: 8.5rem;
            border: 1px solid rgba(255, 255, 255, .22);
            border-radius: 2.25rem;
            transform: rotate(18deg);
        }

        .g-hero__content {
            position: relative;
            z-index: 2;
            max-width: 39rem;
        }

        .g-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            margin: 0;
            font-size: .79rem;
            font-weight: 800;
            letter-spacing: .085em;
            text-transform: uppercase;
        }

        .g-hero .g-eyebrow { color: rgba(255, 255, 255, .78); }

        .g-hero__title {
            margin: .9rem 0 0;
            max-width: 34rem;
            font-size: clamp(2rem, 4vw, 3.25rem);
            line-height: 1.04;
            font-weight: 850;
            letter-spacing: -.045em;
        }

        .g-hero__text {
            max-width: 35rem;
            margin: 1rem 0 0;
            color: rgba(255, 255, 255, .82);
            font-size: .98rem;
            line-height: 1.65;
        }

        .g-card {
            border: 1px solid var(--g-border);
            border-radius: 1.5rem;
            background: var(--g-bg);
            box-shadow: var(--g-shadow);
        }

        .g-plan {
            position: relative;
            display: flex;
            min-height: 15rem;
            flex-direction: column;
            overflow: hidden;
            padding: 1.55rem;
        }

        .g-plan::before {
            content: '';
            position: absolute;
            inset: 0 auto 0 0;
            width: .28rem;
            background: #ef4444;
        }

        .g-plan.is-success::before { background: #22c55e; }
        .g-plan.is-warning::before { background: #f59e0b; }

        .g-plan__top,
        .g-section-heading,
        .g-branch-heading,
        .g-item,
        .g-catalog-card {
            display: flex;
            align-items: center;
        }

        .g-plan__top {
            justify-content: space-between;
            gap: 1rem;
        }

        .g-plan__period {
            margin: .3rem 0 0;
            color: var(--g-muted);
            font-size: .82rem;
        }

        .g-status-icon,
        .g-heading-icon,
        .g-catalog-icon,
        .g-empty-icon {
            display: grid;
            flex: 0 0 auto;
            place-items: center;
            border-radius: 1rem;
        }

        .g-status-icon {
            width: 3rem;
            height: 3rem;
            color: #dc2626;
            background: #fef2f2;
        }

        .g-plan.is-success .g-status-icon { color: #16a34a; background: #f0fdf4; }
        .g-plan.is-warning .g-status-icon { color: #d97706; background: #fffbeb; }
        .dark .g-status-icon { background: rgba(239, 68, 68, .13); color: #fca5a5; }
        .dark .g-plan.is-success .g-status-icon { background: rgba(34, 197, 94, .13); color: #86efac; }
        .dark .g-plan.is-warning .g-status-icon { background: rgba(245, 158, 11, .13); color: #fcd34d; }

        .g-icon { width: 1.35rem; height: 1.35rem; }
        .g-icon--small { width: 1rem; height: 1rem; }

        .g-plan__title {
            margin: 1.4rem 0 0;
            color: var(--g-text);
            font-size: 1.22rem;
            line-height: 1.25;
            font-weight: 800;
        }

        .g-plan__text {
            margin: .65rem 0 1.25rem;
            color: var(--g-muted);
            font-size: .9rem;
            line-height: 1.55;
        }

        .g-button {
            display: inline-flex;
            width: fit-content;
            align-items: center;
            justify-content: center;
            gap: .55rem;
            margin-top: auto;
            border-radius: .8rem;
            padding: .72rem 1rem;
            color: #fff;
            background: #16a34a;
            font-size: .86rem;
            font-weight: 750;
            text-decoration: none;
            box-shadow: 0 8px 20px -12px rgba(22, 163, 74, .9);
            transition: transform .16s ease, background-color .16s ease;
        }

        .g-button:hover { background: #15803d; transform: translateY(-1px); }
        .g-button--light { color: #15803d; background: #fff; }
        .g-button--light:hover { color: #166534; background: #f0fdf4; }

        .g-menu { overflow: hidden; }

        .g-menu__header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1.25rem;
            padding: 1.45rem 1.6rem;
            border-bottom: 1px solid var(--g-border);
        }

        .g-section-heading { gap: .8rem; }

        .g-heading-icon {
            width: 2.65rem;
            height: 2.65rem;
            color: #16a34a;
            background: #f0fdf4;
        }

        .dark .g-heading-icon { color: #4ade80; background: rgba(34, 197, 94, .12); }

        .g-section-title {
            margin: 0;
            color: var(--g-text);
            font-size: 1.18rem;
            font-weight: 800;
            letter-spacing: -.015em;
        }

        .g-section-subtitle {
            margin: .18rem 0 0;
            color: var(--g-muted);
            font-size: .84rem;
        }

        .g-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
            padding: .32rem;
            border-radius: .9rem;
            background: var(--g-bg-soft);
        }

        .g-tab {
            border: 0;
            border-radius: .66rem;
            padding: .58rem .9rem;
            color: var(--g-muted);
            background: transparent;
            cursor: pointer;
            font-size: .82rem;
            font-weight: 750;
            transition: color .16s ease, background-color .16s ease, box-shadow .16s ease;
        }

        .g-tab.is-active {
            color: #fff;
            background: #16a34a;
            box-shadow: 0 7px 18px -12px rgba(22, 163, 74, .9);
        }

        .g-branch { padding: 1.6rem; }
        .g-branch-heading { justify-content: space-between; gap: 1.25rem; }

        .g-branch-name-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .6rem;
        }

        .g-branch-name {
            margin: 0;
            color: var(--g-text);
            font-size: 1.08rem;
            font-weight: 800;
        }

        .g-status-pill,
        .g-type-pill,
        .g-flag {
            display: inline-flex;
            width: fit-content;
            align-items: center;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 750;
        }

        .g-status-pill { padding: .25rem .58rem; color: #475569; background: #f1f5f9; }
        .dark .g-status-pill { color: #cbd5e1; background: rgba(255, 255, 255, .08); }

        .g-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(7rem, 1fr));
            gap: .65rem;
            margin-top: 1.15rem;
        }

        .g-summary__item {
            display: flex;
            align-items: center;
            gap: .7rem;
            min-width: 0;
            padding: .75rem .85rem;
            border: 1px solid var(--g-border);
            border-radius: .85rem;
            background: var(--g-bg-soft);
        }

        .g-summary__icon { color: #16a34a; }
        .g-summary__value { color: var(--g-text); font-size: 1rem; font-weight: 850; }
        .g-summary__label { color: var(--g-muted); font-size: .74rem; }

        .g-notice {
            display: flex;
            align-items: flex-start;
            gap: .75rem;
            margin-top: 1.2rem;
            padding: 1rem;
            border: 1px dashed var(--g-border);
            border-radius: 1rem;
            color: var(--g-muted);
            background: var(--g-bg-soft);
            font-size: .88rem;
            line-height: 1.5;
        }

        .g-items {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .8rem;
            margin-top: 1.2rem;
        }

        .g-item {
            position: relative;
            min-width: 0;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.05rem 1rem 1.2rem;
            border: 1px solid var(--g-border);
            border-radius: 1rem;
            background: var(--g-bg-soft);
        }

        .g-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: .8rem;
            bottom: .8rem;
            width: .22rem;
            border-radius: 999px;
            background: #22c55e;
        }

        .g-item.is-soup::before { background: #65a30d; }
        .g-item.is-special::before { background: #059669; }
        .g-item.is-unavailable { opacity: .62; }
        .g-item.is-editable {
            cursor: pointer;
            transition: border-color .16s ease, background-color .16s ease, transform .16s ease, box-shadow .16s ease;
        }
        .g-item.is-editable:hover,
        .g-item.is-editable:focus-visible {
            border-color: #22c55e;
            background: rgba(34, 197, 94, .07);
            outline: none;
            transform: translateY(-1px);
            box-shadow: 0 12px 28px -22px rgba(22, 163, 74, .8);
        }
        .g-item__main { min-width: 0; }

        .g-item__badges { display: flex; flex-wrap: wrap; gap: .38rem; }
        .g-type-pill { padding: .22rem .55rem; color: #15803d; background: #dcfce7; }
        .g-item.is-soup .g-type-pill { color: #3f6212; background: #ecfccb; }
        .g-item.is-special .g-type-pill { color: #047857; background: #d1fae5; }
        .dark .g-type-pill { color: #86efac; background: rgba(34, 197, 94, .13); }
        .dark .g-item.is-soup .g-type-pill { color: #bef264; background: rgba(101, 163, 13, .16); }
        .dark .g-item.is-special .g-type-pill { color: #6ee7b7; background: rgba(5, 150, 105, .16); }

        .g-flag { padding: .22rem .55rem; color: #b91c1c; background: #fee2e2; }
        .g-flag.is-muted { color: #475569; background: #e2e8f0; }
        .dark .g-flag { color: #fca5a5; background: rgba(239, 68, 68, .14); }
        .dark .g-flag.is-muted { color: #cbd5e1; background: rgba(148, 163, 184, .14); }

        .g-item__name {
            margin: .6rem 0 0;
            overflow: hidden;
            color: var(--g-text);
            font-size: .94rem;
            font-weight: 750;
            text-overflow: ellipsis;
        }

        .g-item__components {
            display: flex;
            align-items: flex-start;
            gap: .4rem;
            margin: .32rem 0 0;
            overflow: hidden;
            color: var(--g-muted);
            font-size: .78rem;
            line-height: 1.4;
            text-overflow: ellipsis;
        }
        .g-item__components strong { flex: 0 0 auto; color: #16a34a; font-weight: 750; }
        .g-item__edit {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            margin-top: .48rem;
            color: #16a34a;
            font-size: .72rem;
            font-weight: 750;
        }
        .dark .g-item__components strong,
        .dark .g-item__edit { color: #4ade80; }

        .g-item__meta { flex: 0 0 auto; text-align: right; }
        .g-item__amount { display: block; color: var(--g-muted); font-size: .76rem; }
        .g-item__price { display: block; margin-top: .25rem; color: var(--g-text); font-size: 1rem; font-weight: 850; }

        .g-empty { padding: 3.25rem 1.5rem; text-align: center; }
        .g-empty-icon { width: 3.5rem; height: 3.5rem; margin: 0 auto; color: var(--g-muted); background: var(--g-bg-soft); }
        .g-empty__title { margin: 1rem 0 0; color: var(--g-text); font-weight: 800; }
        .g-empty__text { margin: .35rem 0 0; color: var(--g-muted); font-size: .88rem; }

        .g-catalog-header { margin-bottom: .85rem; }
        .g-catalog-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .85rem; }
        .g-catalog-card { gap: .9rem; padding: 1rem 1.1rem; }
        .g-catalog-icon { width: 2.8rem; height: 2.8rem; color: #16a34a; background: #f0fdf4; }
        .dark .g-catalog-icon { color: #4ade80; background: rgba(34, 197, 94, .12); }
        .g-catalog-name { margin: 0; color: var(--g-muted); font-size: .78rem; }
        .g-catalog-count { margin: .12rem 0 0; color: var(--g-text); font-size: 1.45rem; line-height: 1; font-weight: 850; }

        [x-cloak] { display: none !important; }

        @media (max-width: 1050px) {
            .g-top-grid { grid-template-columns: 1fr; }
            .g-plan { min-height: auto; }
            .g-catalog-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 760px) {
            .g-dashboard { gap: 1rem; }
            .g-hero { min-height: 13rem; padding: 1.5rem; border-radius: 1.2rem; }
            .g-card { border-radius: 1.2rem; }
            .g-menu__header, .g-branch-heading { align-items: stretch; flex-direction: column; }
            .g-branch, .g-menu__header { padding: 1.1rem; }
            .g-summary, .g-items { grid-template-columns: 1fr; }
            .g-button { width: 100%; }
        }

        @media (max-width: 480px) {
            .g-catalog-grid { grid-template-columns: 1fr; }
            .g-summary__item { padding: .65rem; }
            .g-item { align-items: flex-start; }
        }
    </style>

    <div class="g-dashboard">
        <div class="g-top-grid">
            <section class="g-hero">
                <div class="g-hero__content">
                    <p class="g-eyebrow">
                        <x-filament::icon icon="heroicon-o-calendar" class="g-icon--small" />
                        {{ $todayLabel }}
                    </p>
                    <h2 class="g-hero__title">{{ $greeting }}</h2>
                    <p class="g-hero__text">
                        Dnešní nabídka, důležité počty i plánování příštího týdne přehledně na jednom místě.
                    </p>
                </div>
            </section>

            <section class="g-card g-plan is-{{ $nextWeekMenu['color'] ?? 'danger' }}">
                <div class="g-plan__top">
                    <div>
                        <p class="g-eyebrow" style="color: var(--g-muted)">Příští týden</p>
                        <p class="g-plan__period">{{ $nextWeekMenu['period'] ?? '' }}</p>
                    </div>
                    <div class="g-status-icon">
                        <x-filament::icon
                            :icon="match ($nextWeekMenu['color'] ?? null) {
                                'success' => 'heroicon-o-check-circle',
                                'warning' => 'heroicon-o-clock',
                                default => 'heroicon-o-exclamation-circle',
                            }"
                            class="g-icon"
                        />
                    </div>
                </div>

                <h3 class="g-plan__title">{{ $nextWeekMenu['title'] ?? 'Stav není dostupný' }}</h3>
                <p class="g-plan__text">{{ $nextWeekMenu['description'] ?? '' }}</p>

                @if (filled($nextWeekMenu['actionUrl'] ?? null))
                    <a href="{{ $nextWeekMenu['actionUrl'] }}" class="g-button">
                        {{ $nextWeekMenu['actionLabel'] }}
                        <x-filament::icon icon="heroicon-o-arrow-right" class="g-icon--small" />
                    </a>
                @endif
            </section>
        </div>

        <section
            class="g-card g-menu"
            @if ($todayMenus !== [])
                x-data="{ activeRestaurant: @js($todayMenus[0]['restaurantId']) }"
            @endif
        >
            <header class="g-menu__header">
                <div class="g-section-heading">
                    <div class="g-heading-icon">
                        <x-filament::icon icon="heroicon-o-calendar-days" class="g-icon" />
                    </div>
                    <div>
                        <h2 class="g-section-title">Dnešní jídelní lístek</h2>
                        <p class="g-section-subtitle">{{ $todayLabel }}</p>
                    </div>
                </div>

                @if (count($todayMenus) > 1)
                    <div class="g-tabs" role="tablist" aria-label="Provozovna">
                        @foreach ($todayMenus as $branch)
                            <button
                                type="button"
                                role="tab"
                                x-on:click="activeRestaurant = {{ $branch['restaurantId'] }}"
                                x-bind:aria-selected="activeRestaurant === {{ $branch['restaurantId'] }}"
                                x-bind:class="{ 'is-active': activeRestaurant === {{ $branch['restaurantId'] }} }"
                                class="g-tab"
                            >
                                {{ $branch['restaurantName'] }}
                            </button>
                        @endforeach
                    </div>
                @endif
            </header>

            @forelse ($todayMenus as $branch)
                <div
                    @if (count($todayMenus) > 1)
                        x-show="activeRestaurant === {{ $branch['restaurantId'] }}"
                        x-cloak
                    @endif
                    class="g-branch"
                >
                    <div class="g-branch-heading">
                        <div>
                            <div class="g-branch-name-row">
                                <h3 class="g-branch-name">{{ $branch['restaurantName'] }}</h3>
                                @if (filled($branch['status'] ?? null))
                                    <span class="g-status-pill">{{ $branch['status'] }}</span>
                                @endif
                            </div>

                            @if ($branch['menuExists'])
                                <div class="g-summary">
                                    <div class="g-summary__item">
                                        <x-filament::icon icon="heroicon-o-fire" class="g-icon--small g-summary__icon" />
                                        <div><div class="g-summary__value">{{ $branch['summary']['soups'] }}</div><div class="g-summary__label">Polévky</div></div>
                                    </div>
                                    <div class="g-summary__item">
                                        <x-filament::icon icon="heroicon-o-sparkles" class="g-icon--small g-summary__icon" />
                                        <div><div class="g-summary__value">{{ $branch['summary']['mains'] }}</div><div class="g-summary__label">Hlavní jídla</div></div>
                                    </div>
                                    <div class="g-summary__item">
                                        <x-filament::icon icon="heroicon-o-eye" class="g-icon--small g-summary__icon" />
                                        <div><div class="g-summary__value">{{ $branch['summary']['available'] }}</div><div class="g-summary__label">V nabídce</div></div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if (filled($branch['actionUrl'] ?? null))
                            <a href="{{ $branch['actionUrl'] }}" class="g-button">
                                <x-filament::icon icon="heroicon-o-pencil-square" class="g-icon--small" />
                                {{ $branch['actionLabel'] }}
                            </a>
                        @endif
                    </div>

                    @if (filled($branch['message'] ?? null))
                        <div class="g-notice">
                            <x-filament::icon
                                :icon="($branch['isNonCookingDay'] ?? false) ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-information-circle'"
                                class="g-icon"
                            />
                            <span>{{ $branch['message'] }}</span>
                        </div>
                    @endif

                    @if (($branch['items'] ?? []) !== [])
                        <div class="g-items">
                            @foreach ($branch['items'] as $item)
                                <article
                                    class="g-item {{ $item['type'] === 'soup' ? 'is-soup' : (in_array($item['type'], ['pizza', 'grill'], true) ? 'is-special' : '') }} {{ ! $item['isAvailable'] ? 'is-unavailable' : '' }} {{ ($item['canEdit'] ?? false) ? 'is-editable' : '' }}"
                                    @if ($item['canEdit'] ?? false)
                                        wire:click="mountAction('editMenuItem', { item: {{ $item['id'] }} })"
                                        wire:key="dashboard-menu-item-{{ $item['id'] }}"
                                        role="button"
                                        tabindex="0"
                                        aria-label="Rychle upravit {{ $item['name'] }}"
                                        x-on:keydown.enter.prevent="$el.click()"
                                    @endif
                                >
                                    <div class="g-item__main">
                                        <div class="g-item__badges">
                                            <span class="g-type-pill">{{ $item['typeLabel'] }}</span>
                                            @if (! $item['isAvailable'])
                                                <span class="g-flag">Není v nabídce</span>
                                            @endif
                                            @if (! $item['showOnWeb'])
                                                <span class="g-flag is-muted">Skryto na webu</span>
                                            @endif
                                        </div>
                                        <h4 class="g-item__name">{{ filled($item['name']) ? $item['name'] : 'Položka bez názvu' }}</h4>
                                        @if (($item['sideItems'] ?? []) !== [])
                                            <p class="g-item__components">
                                                <strong>Příloha:</strong>
                                                <span>{{ implode(' · ', $item['sideItems'] ?? []) }}</span>
                                            </p>
                                        @endif
                                        @if (($item['otherItems'] ?? []) !== [])
                                            <p class="g-item__components">
                                                <strong>Ostatní:</strong>
                                                <span>{{ implode(' · ', $item['otherItems'] ?? []) }}</span>
                                            </p>
                                        @endif
                                        @if ($item['canEdit'] ?? false)
                                            <span class="g-item__edit">
                                                <x-filament::icon icon="heroicon-o-pencil-square" class="g-icon--small" />
                                                Kliknutím rychle upravit
                                            </span>
                                        @endif
                                    </div>

                                    <div class="g-item__meta">
                                        @if (filled($item['amount']))
                                            <span class="g-item__amount">{{ $item['amount'] }}</span>
                                        @endif
                                        @if (filled($item['price']))
                                            <strong class="g-item__price">{{ $item['price'] }}</strong>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="g-empty">
                    <div class="g-empty-icon">
                        <x-filament::icon icon="heroicon-o-building-storefront" class="g-icon" />
                    </div>
                    <h3 class="g-empty__title">Nemáte přiřazenou provozovnu</h3>
                    <p class="g-empty__text">Po přiřazení provozovny se zde zobrazí její dnešní jídelní lístek.</p>
                </div>
            @endforelse
        </section>

        @if ($catalogStats !== [])
            <section>
                <div class="g-catalog-header">
                    <h2 class="g-section-title">Přehled katalogu</h2>
                    <p class="g-section-subtitle">Aktivní položky připravené pro sestavování menu</p>
                </div>

                <div class="g-catalog-grid">
                    @foreach ($catalogStats as $stat)
                        <div class="g-card g-catalog-card">
                            <div class="g-catalog-icon">
                                <x-filament::icon :icon="$stat['icon']" class="g-icon" />
                            </div>
                            <div>
                                <p class="g-catalog-name">{{ $stat['name'] }}</p>
                                <p class="g-catalog-count">{{ $stat['count'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-filament-panels::page>
